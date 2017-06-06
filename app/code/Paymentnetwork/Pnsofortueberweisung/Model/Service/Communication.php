<?php

namespace Paymentnetwork\Pnsofortueberweisung\Model\Service;

use Magento\Framework\App\ObjectManager;

use Magento\Framework\Exception\LocalizedException;

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category PayIntelligent
 * @package Paintelligent_Sofort
 * @copyright Copyright (c) 2014 PayIntelligent GmbH (http://www.payintelligent.de)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
require_once(dirname(__FILE__) . '/../../../../../../lib/Sofort/payment/sofortLibSofortueberweisung.inc.php');
require_once(dirname(__FILE__) . '/../../../../../../lib/Sofort/core/sofortLibNotification.inc.php');
require_once(dirname(__FILE__) . '/../../../../../../lib/Sofort/core/sofortLibTransactionData.inc.php');

class Communication
{

    /**
     * Sofort sdk instance
     * 
     * @var SofortLib_SofortueberweisungClassic 
     */
    protected $_sofortSdk;

    /**
     * Magento quote instance
     * 
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote;
    
    /**
     * Sofort transaction id
     * 
     * @var string
     */
    protected $_transactionId;

    /**
     * Initialize dependecys (sofort sdk)
     */
    public function __construct()
    {
        $this->_sofortSdk = new \Sofortueberweisung(
            ObjectManager::getInstance()->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue(
                'payment/paymentnetwork_pnsofortueberweisung/cofiguration_key',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
    }

    /**
     * Get payment url
     * 
     * @throws Mage_Core_Exception
     * @return string
     */
    public function getUrl()
    {
        if ($this->_sofortSdk->isError()) {
            throw new LocalizedException(__($this->_sofortSdk->getError()));
        }
        
        return $this->_sofortSdk->getPaymentUrl();
    }
    
    /**
     * Get sofort transaction id
     * 
     * @return string
     */
    public function getTransactionId()
    {
        if (is_null($this->_transactionId)) {
            $this->_transactionId = $this->_sofortSdk->getTransactionId();
        }
        
        return $this->_transactionId;
    }
    
    /**
     * Get sofort status with reason
     * 
     * @return array
     */
    public function getStatusData($rawBody)
    {
        $transactionData = array('status' => 'undefined', 'reason' => 'undefined');
        
        $notificationSdk = new \SofortLibNotification();
        $transactionId = $notificationSdk->getNotification($rawBody);
        if ($transactionId) {
            $transactionDataSdk = new \SofortLibTransactionData(            
                ObjectManager::getInstance()->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue(
                    'payment/paymentnetwork_pnsofortueberweisung/cofiguration_key',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            );
            
            $transactionDataSdk->addTransaction($transactionId)->sendRequest();
            
            $transactionData['status'] = $transactionDataSdk->getStatus();
            $transactionData['reason'] = $transactionDataSdk->getStatusReason();
            $transactionData['amount_refunded'] = $transactionDataSdk->getAmountRefunded();
            $transactionData['transaction_id'] = $transactionId;
        }
        
        return $transactionData;
    }

    /**
     * Executes the pay request to sofort
     */
    public function paymentRequest()
    {
        $orderId = $this->_getQuote()->getReservedOrderId();
        
        $this->_sofortSdk->setVersion('Magento2_3.0.5');
        $this->_sofortSdk->setAmount(
            $this->_getQuote()->getGrandTotal()
        );
        
        $this->_sofortSdk->setCurrencyCode($this->_getQuote()->getCurrency()->getQuoteCurrencyCode());
        $this->_sofortSdk->setReason($this->_getReasonOne(), $this->_getReasonTwo());

        $this->_sofortSdk->setSuccessUrl(
            ObjectManager::getInstance()->get('Magento\Framework\UrlInterface')->getUrl(
                'pisofort/payment/success',
                array(
                    'orderId' => $orderId
                )
            ), true
        );
        
        $this->_sofortSdk->setAbortUrl(
            ObjectManager::getInstance()->get('Magento\Framework\UrlInterface')->getUrl(
                'pisofort/payment/abort',
                array('orderId' => $orderId)
            )
        );
        
        $this->_sofortSdk->setNotificationUrl(
            ObjectManager::getInstance()->get('Magento\Framework\UrlInterface')->getUrl(
                'pisofort/payment/notification',
                array('orderId' => $orderId)
            )
        );
        
        $this->_sofortSdk->setCustomerprotection(
            (boolean) ObjectManager::getInstance()->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue(
                'payment/paymentnetwork_pnsofortueberweisung/customer_protection',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );

        $this->_sofortSdk->sendRequest();
    }
    
    /**
     * Get reason one
     * 
     * @retun string
     */
    private function _getReasonOne()
    {
        return $this->_replaceReasonPlaceHolder(
            ObjectManager::getInstance()->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue(
                'payment/paymentnetwork_pnsofortueberweisung/usage_text_one',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
    }
    
    /**
     * Get reason two
     * 
     * @return string
     */
    private function _getReasonTwo()
    {
        return $this->_replaceReasonPlaceHolder(
            ObjectManager::getInstance()->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue(
                'payment/paymentnetwork_pnsofortueberweisung/usage_text_two',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
    }
    
    /**
     * Replace the placeholders and get reason
     * 
     * @param string $reason
     * @return string
     */
    private function _replaceReasonPlaceHolder($reason)
    {
        $replaceData = array(
            '{{orderid}}' => $this->_getQuote()->getReservedOrderId(),
            '{{name}}' => $this->_getQuote()->getBillingAddress()->getFirstname() . ' ' . $this->_getQuote()->getBillingAddress()->getLastname(),
            '{{date}}' => date('d.m.Y H:i:s', time()),
            '{{shopname}}' => ObjectManager::getInstance()->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue(
                'general/store_information/name',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ),
            '{{transaction}}' => '-TRANSACTION-'
            
        ); 
        
        $reason = str_replace('{{orderid}}', $replaceData['{{orderid}}'], $reason);
        $reason = str_replace('{{name}}', $replaceData['{{name}}'], $reason);
        $reason = str_replace('{{date}}', $replaceData['{{date}}'], $reason);
        $reason = str_replace('{{shopname}}', $replaceData['{{shopname}}'], $reason);
        $reason = str_replace('{{transaction}}', $replaceData['{{transaction}}'], $reason);
        
        return $reason;
    }
    
    /**
     * Get quote instance
     * 
     * @return \Magento\Quote\Model\Quote
     */
    protected function _getQuote()
    {
        if (is_null($this->_quote)) {
            $this->_quote = ObjectManager::getInstance()->get(
                'Magento\Checkout\Model\Session'
            )->getQuote();
        }
        
        return $this->_quote;
    }

}
<?php

namespace Paymentnetwork\Pnsofortueberweisung\Model\Method;

use Magento\Framework\App\ObjectManager;


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
class Sofort extends \Magento\Payment\Model\Method\AbstractMethod
{

    /**
     * Is gateway
     * 
     * @var boolean 
     */
    protected $_isGateway = true;

    /**
     * Can use the Refund method to refund less than the full amount
     *
     * @var boolean
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * Can use the partial capture method
     *
     * @var boolean
     */
    protected $_canCapturePartial = false;

    /**
     * Can this method use for checkout
     *
     * @var boolean
     */
    protected $_canUseCheckout = true;

    /**
     * Can this method use for multishipping
     *
     * @var boolean
     */
    protected $_canUseForMultishipping = false;

    /**
     * Can use for internal payments
     * 
     * @var boolean
     */
    protected $_canUseInternal = false;
    
    /**
     *
     * @var type 
     */
    protected $_isInitializeNeeded = true;

    /**
     * Magento method code
     *
     * @var string
     */
    protected $_code = 'paymentnetwork_pnsofortueberweisung';
    
    /**
     * Info block identifier
     *
     * @var string
     */
    protected $_infoBlockType = 'Paymentnetwork\Pnsofortueberweisung\Block\Info\Sofort';
    
    /**
     * Initulaize the sofort payment and set the redirect url
     * 
     * @param string $paymentAction
     * @param Varien_Object $stateObject
     * @return \Paymentnetwork_Pnsofortueberweisung_Model_Method_Sofort
     */
    public function initialize($paymentAction, $stateObject)
    {

        parent::initialize($paymentAction, $stateObject);

        $communication = ObjectManager::getInstance()->get(
            'Paymentnetwork\Pnsofortueberweisung\Model\Service\Communication'
        );
        
        $communication->paymentRequest();
        
        $url = $communication->getUrl();

        ObjectManager::getInstance()->get('Magento\Customer\Model\Session')->setPaymentUrl($url);
        
        $this->getInfoInstance()->setAdditionalInformation(
            'sofort_transaction_id', 
            $communication->getTransactionId()
        );
        
        return $this;
    }

    /**
     * Get payment title
     * 
     * @return string
     */
    public function getTitle()
    {
        return __(parent::getTitle());
    }
    
    /**
     * @return boolean
     */
    private function _getSendOrderEmail()
    {
        return (boolean) ObjectManager::getInstance()->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue(
            'payment/paymentnetwork_pnsofortueberweisung/send_order_confirmation',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * @return boolean
     */
    public function getOrderPlaceRedirectUrl()
    {
        return !$this->_getSendOrderEmail();
    } 
}

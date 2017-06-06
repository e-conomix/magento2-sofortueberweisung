<?php

namespace Paymentnetwork\Pnsofortueberweisung\Controller;

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
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class PaymentAbstract extends \Magento\Framework\App\Action\Action
{
    /**
     * Order instance
     * @var Mage_Sales_Model_Order
     */
    private $_order;
    
    /**
     * @var string
     */
    private $_orderId;


    /**
     * Order comments
     * @var string
     */
    private $_commentMessages = array(
        'redirect' => 'Redirection to SOFORT Banking. Transaction not finished. Transaction ID: [[transaction_id]]. Time: [[date]]',
        'abort' => 'Payment aborted. Time: %s',
        'pending_not_credited_yet' => 'SOFORT Banking has been completed successfully - Transaction ID: [[transaction_id]]. Time: [[date]]',
        'untraceable_sofort_bank_account_needed' => 'SOFORT Banking has been completed successfully - Transaction ID: [[transaction_id]]. Time: [[date]]',
        'loss_not_credited' => 'The payment has not been received on your SOFORT Bank account. Please verify the payment. Time: [[date]]',
        'received_credited' => 'The payment has been received on your SOFORT Bank account. Time: [[date]]',
        'received_partially_credited' => 'An amount differing from the order has been received on your SOFORT Bank account. Please verify the payment. Time: [[date]]',
        'received_overpayment' => 'An amount differing from the order has been received on your SOFORT Bank account. Please verify the payment. Time: [[date]]',
        'refunded_compensation' => 'Partial amount will be refunded - [[refunded_amount]]. Time: [[date]]',
        'refunded_refunded' => 'Amount will be refunded. Time: [[date]]'
    );

    protected function _redirectAction()
    {
        $comment = __($this->_commentMessages['redirect']);
        $comment = str_replace('[[date]]', date('d.m.Y H:i:s'), $comment);
        $comment = str_replace(
            '[[transaction_id]]', 
            $this->_getOrder()->getPayment()->getAdditionalInformation('sofort_transaction_id'), 
            $comment
        );
        
        $this->_getOrder()->addStatusHistoryComment($comment);
        
        $this->_getOrder()->save();

        $this->_redirect(
            $this->_objectManager->get('Magento\Customer\Model\Session')->getPaymentUrl()
        );
    }
    
    /**
     * Sofort success url
     */
    protected function _successAction()
    {   
        $order = $this->_getOrder();
                
        $this->_objectManager->get('Magento\Checkout\Model\Session')
            ->setLastQuoteId($order->getQuoteId())
            ->setLastSuccessQuoteId($order->getQuoteId());
        
        $this->_objectManager->get('Magento\Checkout\Model\Session')
            ->setLastOrderId($order->getId())
            ->setLastRealOrderId($order->getIncrementId());
            
        $this->_redirect('checkout/onepage/success/');
    }

    /**
     * Sofort abort url
     */
    protected function _abortAction()
    {
        $this->_getOrder()->cancel();
        $this->_getOrder()->addStatusHistoryComment(
            sprintf(__($this->_commentMessages['abort']), date('d.m.Y H:i:s'))
        );
        
        $this->_getOrder()->save();
                
        /* @var $cart \Magento\Checkout\Model\Cart */
        $cart = $this->_objectManager->get('Magento\Checkout\Model\Cart');
                
        foreach ($this->_getOrder()->getItemsCollection() as $item) {
            try {
                $cart->addOrderItem($item);
            } catch (\Exception $e) {
                $this->_objectManager->get('Magento\Framework\Logger\Monolog')->critical($e);
            }
        }

        $cart->save();
        
        $this->_redirect('checkout/cart/');
    }

    /**
     * Sofort notification url
     */
    protected function _notificationAction()
    {
        if (!is_null($this->getRequest()->getParam('orderId'))) {
            $communication = $this->_objectManager->get(
                'Paymentnetwork\Pnsofortueberweisung\Model\Service\Communication'
            );
        
            $this->_orderId = $this->getRequest()->getParam('orderId');

            $statusData = $communication->getStatusData(file_get_contents('php://input'));
            
            $this->_handleSofortStatusUpdate($statusData);
        }
    }
    
    /**
     * Get create invoice option
     * @return string
     */
    private function _getCreateInvoiceOption()
    {
        return $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue(
            'payment/paymentnetwork_pnsofortueberweisung/create_invoice',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get send order confirmation option
     * @return boolean
     */
    private function _getSendOrderConfirmationOption()
    {
        return (boolean)$this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue(
            'payment/paymentnetwork_pnsofortueberweisung/send_order_confirmation',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get create creditmemo flag
     * 
     * @return boolean
     */
    private function _getCreateCreditmemoOption()
    {
        return (boolean) $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue(
            'payment/paymentnetwork_pnsofortueberweisung/create_creditmemo',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * @return boolean
     */
    private function _getSendReceiptEmails()
    {
        return (boolean) $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue(
            'payment/paymentnetwork_pnsofortueberweisung/send_mail',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Generates the invoice for the current order
     */
    private function _generateInvoice()
    {
        $order = $this->_getOrder();
        if ($order->canInvoice()) {
            $invoice = $order->prepareInvoice();

            $invoice->register();

            $invoice->pay()->save();

            $order->save();
            
            if ($this->_getSendReceiptEmails()) {
                $invoiceSender = $this->_objectManager->get('Magento\Sales\Model\Order\Email\Sender\InvoiceSender');

                $invoiceSender->send($invoice);
            }
        }
    }
    
    /**
     * Generates the creditmemo for the current order
     */
    private function _generateCreditmemo()
    {
        $order = $this->_getOrder();
        
        $this->_generateInvoice();
        
        if ($order->canCreditmemo()) {
            
            $creditmemo = $this->_objectManager->get('Magento\Sales\Model\Order\CreditmemoFactory')->createByOrder($order);

            $creditmemoManagement = $this->_objectManager->create(
                'Magento\Sales\Api\CreditmemoManagementInterface'
            );
            
            $creditmemoManagement->refund($creditmemo, true);
            
            if ($this->_getSendReceiptEmails()) {
                $creditmemoSender = $this->_objectManager->get('Magento\Sales\Model\Order\Email\Sender\CreditmemoSender');

                $creditmemoSender->send($creditmemo);
            }
        }
    }
    
    /**
     * Create the invoice when configured
     * 
     * @param array $statusData
     */
    private function _handleInvoiceCreation(array $statusData)
    {
        if ($statusData['status'] === 'pending' 
                && $statusData['reason'] === 'not_credited_yet' 
                && $this->_getCreateInvoiceOption() === 'after_order') {
            $this->_generateInvoice();
        }

        if ($statusData['status'] === 'received' 
                && $statusData['reason'] === 'credited' 
                && $this->_getCreateInvoiceOption() === 'after_credited') {
            $this->_generateInvoice();
        }
    }
    
    /**
     * Create the creditmemo when configured
     * 
     * @param array $statusData
     */
    private function _handleCreditmemoCreation(array $statusData)
    {
        if ($statusData['status'] === 'refunded' 
                && $statusData['reason'] === 'refunded' 
                && $this->_getCreateCreditmemoOption()) {
            $this->_generateCreditmemo();
        }
    }
    
    /**
     * Create the creditmemo or invoice when configured
     * 
     * @param array $statusData
     */
    private function _handleDocumentCreation(array $statusData)
    {
        $this->_handleInvoiceCreation($statusData);
        $this->_handleCreditmemoCreation($statusData);
    }


    /**
     * Update magento order status
     * 
     * @param array $statusData
     */
    private function _handleSofortStatusUpdate(array $statusData)
    {
        $allowedStates = array(
            'loss' => array('not_credited'), 
            'pending' => array('not_credited_yet'), 
            'received' => array('credited'), 
            'refunded' => array('refunded'), 
            'untraceable' => array('sofort_bank_account_needed')
        );
        
        if (array_key_exists($statusData['status'], $allowedStates) 
            && in_array($statusData['reason'], $allowedStates[$statusData['status']])) {
            
            $status = $statusData['status'];
            $reason = $statusData['reason'];
            
            if ($status === 'untraceable' && $reason === 'sofort_bank_account_needed') {
                $status = 'pending';
                $reason = 'not_credited_yet';
            }
            
            $magentoStatus = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue(
                'payment/paymentnetwork_pnsofortueberweisung/order_status_' . $status . '_' . $reason,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            
            if (!empty($magentoStatus)) {
                $this->_handleMagentoStatusUpdate($statusData, $magentoStatus);
            } else {
                $this->_getOrder()->addStatusHistoryComment($this->_getHistoryComment($statusData))->save();
            }
            
            $this->_handleDocumentCreation($statusData);
        } else {
            $this->_getOrder()->addStatusHistoryComment($this->_getHistoryComment($statusData))->save();
        }
    }
    
    /**
     * Set magento state status and history comment
     * 
     * @param array $statusData
     * @param string $status
     */
    private function _handleMagentoStatusUpdate(array $statusData, $status)
    {
        $comment = $this->_getHistoryComment($statusData);
        
        $state = $this->_objectManager->get('Magento\Sales\Model\ResourceModel\Status\Collection')
                            ->addAttributeToFilter("state_table.status", $status)
                            ->getFirstItem()
                            ->getData('state');
        
        $this->_getOrder()->setState($state);
        $this->_getOrder()->setStatus($status);
        
        $this->_getOrder()->addStatusHistoryComment($comment, false);
        $this->_getOrder()->save();
    }
    
    /**
     * Get dynamic sofort history comment
     * 
     * @param array $statusData
     * @return string
     */
    private function _getHistoryComment(array $statusData)
    {
        $comment = 'Status message not defined.';
        $commentKey = $statusData['status'] . '_' . $statusData['reason'];
        
        if (array_key_exists($commentKey, $this->_commentMessages)) {
            $comment = __($this->_commentMessages[$commentKey]);
        }
        
        $comment = str_replace('[[date]]', date('d.m.Y H:i:s'), $comment);
        $comment = str_replace(
            '[[transaction_id]]', 
            array_key_exists('transaction_id', $statusData) ? $statusData['transaction_id'] : 'Not available', 
            $comment
        );
        
        $comment = str_replace(
            '[[refunded_amount]]', 
            array_key_exists('amount_refunded', $statusData) ? $statusData['amount_refunded'] : 'Not available', 
            $comment
        );
        
        return $comment;
    }
    
    /**
     * Get the magento order
     * 
     * @return \Magento\Sales\Model\Order
     * @throws Mage_Core_Exception
     */
    private function _getOrder()
    {
        if (!is_null($this->_order)) {
            return $this->_order;
        }
        
        if (is_null($this->_orderId)) {
            $order = $this->_objectManager->get('Magento\Checkout\Model\Session')->getLastRealOrder();
        } else {
            $order = $this->_objectManager->get('Magento\Sales\Model\Order')->loadByIncrementId($this->_orderId);
        } 
        
        if (!is_null($order)) {
            $this->_order = $order;
            return $order;
        }
        
        throw new LocalizedException(__('Forbidden'));
    }

}

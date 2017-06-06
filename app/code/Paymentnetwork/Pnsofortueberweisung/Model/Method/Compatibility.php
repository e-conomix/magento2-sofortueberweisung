<?php

namespace Paymentnetwork\Pnsofortueberweisung\Model\Method;

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
class Compatibility extends \Magento\Payment\Model\Method\AbstractMethod
{
    
    protected $_canUseInternal = false;
    
    protected $_canAuthorize = false;
    
    protected $_canRefund = false;
    
    protected $_canRefundInvoicePartial = false;
    
    protected $_canCapture = false;
    
    protected $_canCapturePartial = false;
    
    protected $_canUseCheckout = false;

    protected $_canUseForMultishipping = false;
    
    protected $_code = 'paymentnetwork_pnsofortueberweisung_compatibility';
    
    /**
     * Avoid that the old payment methods are visible in the checkout
     * @param type $quote
     * @return boolean
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return false;
    }
}
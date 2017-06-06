<?php

namespace Paymentnetwork\Pnsofortueberweisung\Model\Source\Checkout;

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
class Presentation implements \Magento\Framework\Option\ArrayInterface
{   
    /**
     * Define which Creditcard Logos are shown for payment
     *
     * @return array
     */
    public function toOptionArray()
    {
        $presentationMethods = array(
            array(
                'label' => __('Banner'),
                'value' => 'banner'
            ),
            array(
                'label' => __('Logo & Text'),
                'value' => 'logo'
            ),
        );
        
        return $presentationMethods;
    }
}
<?php

namespace Paymentnetwork\Pnsofortueberweisung\Model\Source\Order;

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
class Status extends \Magento\Sales\Model\Config\Source\Order\Status
{

    /**
     * Enable all states
     * 
     * @var array 
     */
    protected $_stateStatuses = null;

    /**
     * Get the status option array
     * 
     * @return array
     */
    public function toOptionArray()
    {
        $optionsToBeUnset = array(
            'pending_paypal',
            'paypal_canceled_reversal',
            'paypal_reversed',
            'complete',
            'unchanged',
            'closed',
        );

        $options = parent::toOptionArray();
        foreach ($options as $key => $option) {
            if (empty($option['value'])) {
                $options[$key]['label'] = __('Not update status');
            }

            if (in_array($option['value'], $optionsToBeUnset, true)) {
                unset($options[$key]);
            }
        }

        return $options;
    }

}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Paymentnetwork\Pnsofortueberweisung\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\ObjectManager;

class ConfigProvider implements ConfigProviderInterface
{
    
    
    /**
     * @return array
     */
    public function getConfig()
    {   
        /**
         * @var Magento\Framework\App\Config\ScopeConfigInterface
         */
        $config = ObjectManager::getInstance()->get('Magento\Framework\App\Config\ScopeConfigInterface');
        
        if (!$config->getValue('payment/paymentnetwork_pnsofortueberweisung/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return [];
        }
        
        return [
            'payment' => [
                'sofort' => [
                    'isBanner' => $config->getValue(
                        'payment/paymentnetwork_pnsofortueberweisung/checkout_presentation',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    ) === 'banner',
                    'isCustomerProtectionEnabled' => (boolean) $config->getValue(
                        'payment/paymentnetwork_pnsofortueberweisung/customer_protection',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                ]
            ]
        ];
    }
}

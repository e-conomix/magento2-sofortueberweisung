define(
    [
        'Magento_Payment/js/view/payment/cc-form',
        'mage/url'
    ],
    function (Component, url) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Paymentnetwork_Pnsofortueberweisung/payment/sofort'
            },

            getCode: function() {
                return 'paymentnetwork_pnsofortueberweisung';
            },

            isActive: function() {
                return true;
            },
            
            isBanner: function() {
                return window.checkoutConfig.payment.sofort.isBanner;
            },
            
            isCustomerProtectionEnabled: function() {
                return window.checkoutConfig.payment.sofort.isCustomerProtectionEnabled;
            }, 
            
            afterPlaceOrder: function () {
                window.location.replace(url.build('pisofort/payment/redirect/'));
            }
        });
    }
);
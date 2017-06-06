/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'paymentnetwork_pnsofortueberweisung',
                component: 'Paymentnetwork_Pnsofortueberweisung/js/view/payment/method-renderer/sofort-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
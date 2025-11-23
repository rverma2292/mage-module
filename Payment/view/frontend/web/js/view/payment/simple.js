define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (Component,
              rendererList) {
        'use strict';
        rendererList.push(
            {
                type: 'simple',
                component: 'Advik_Payment/js/view/payment/method-renderer/simple-method'
            }
        );
        console.log("Renderer count:", rendererList().length);
        console.log("Renderer list:", rendererList());
        return Component.extend({});
    }
);

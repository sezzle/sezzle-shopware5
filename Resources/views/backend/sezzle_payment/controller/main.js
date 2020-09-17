// This is the controller


Ext.define('Shopware.apps.SezzlePayment.controller.Main', {
    /**
     * Override the customer main controller
     * @string
     */
    override: 'Shopware.apps.Order.controller.Main',

    init: function () {
        var me = this;

        me.control({
            'order-detail-window order-sezzle-panel': {
                capture: me.onCaptureEvent,
                refund: me.onRefundEvent,
                release: me.onReleaseEvent
            },
        });

        // me.callParent will execute the init function of the overridden controller
        me.callParent(arguments);
    },

    onCaptureEvent: function(record, panel, options) {
        console.log("capture")
    },

    onRefundEvent: function(record, panel, options) {
        console.log("refund")
    },

    onReleaseEvent: function(record, panel, options) {
        console.log("release")
    }
});

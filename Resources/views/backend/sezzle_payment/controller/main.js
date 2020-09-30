// This is the controller


Ext.define('Shopware.apps.SezzlePayment.controller.Main', {
    /**
     * Override the customer main controller
     * @string
     */
    override: 'Shopware.apps.Order.controller.Main',

    /**
     * @type { Shopware.apps.SezzlePayment.view.detail.Window }
     */
    window: null,

    urls: {
        captureOrder: '{url module=backend controller=Sezzle action=captureOrder}',
        refundOrder: '{url module=backend controller=Sezzle action=refundOrder}',
        releaseOrder: '{url module=backend controller=Sezzle action=releaseOrder}',
    },

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
        var me = this,
            sezzleOrderUUID = record.get('sezzleOrderUUID'),
            currency = record.get('currency'),
            authAmount = record.get('authAmount'),
            amount = record.get('amount'),
            isPartial = authAmount === amount;

        Ext.Ajax.request({
            url: this.urls.captureOrder,
            params: {
                id: sezzleOrderUUID,
                currency: currency,
                amount: amount,
                isPartial: isPartial
            },
            success: function(responseData, request) {
                var captureAmount = Ext.get("capture_amount");
                captureAmount.dom.innerHTML = amount;
            },
            callback: Ext.bind(me.captureCallback, me)
        });
    },

    onRefundEvent: function(record, panel, options) {
        var me = this,
            sezzleOrderUUID = record.get('sezzleOrderUUID'),
            currency = record.get('currency'),
            amount = record.get('amount');

        Ext.Ajax.request({
            url: this.urls.refundOrder,
            params: {
                id: sezzleOrderUUID,
                currency: currency,
                amount: amount
            },
            success: function(responseData, request) {
                var refundAmount = Ext.get("refund_amount");
                refundAmount.dom.innerHTML = amount;
            },
            callback: Ext.bind(me.refundCallback, me)
        });
    },

    onReleaseEvent: function(record, panel, options) {
        var me = this,
            sezzleOrderUUID = record.get('sezzleOrderUUID'),
            currency = record.get('currency'),
            amount = record.get('amount');

        Ext.Ajax.request({
            url: this.urls.releaseOrder,
            params: {
                id: sezzleOrderUUID,
                currency: currency,
                amount: amount
            },
            success: function(responseData, request) {
                var releaseAmount = Ext.get("release_amount");
                releaseAmount.dom.innerHTML = amount;
            },
            callback: Ext.bind(me.releaseCallback, me)
        });
    },

    captureCallback: function(options, success, response) {
        var me = this,
            responseObject = Ext.JSON.decode(response.responseText);

        if (Ext.isDefined(responseObject) && responseObject.success) {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}Sezzle{/s}', '{s name=growl/captureSuccess}The payment has been captured successfully{/s}', '{s name=title}Sezzle - Payment{/s}');
            me.subApplication.getStore('Order').reload();
        } else {
            Shopware.Notification.createStickyGrowlMessage({ title: '{s name=growl/title}Sezzle{/s}', text: responseObject.message }, '{s name=title}Sezzle - Payment{/s}');
        }
    },

    refundCallback: function(options, success, response) {
        var me = this,
            responseObject = Ext.JSON.decode(response.responseText);

        if (Ext.isDefined(responseObject) && responseObject.success) {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}Sezzle{/s}', '{s name=growl/refundSuccess}The payment has been refunded successfully{/s}', '{s name=title}Sezzle - Payment{/s}');
            me.subApplication.getStore('Order').reload();
        } else {
            Shopware.Notification.createStickyGrowlMessage({ title: '{s name=growl/title}Sezzle{/s}', text: responseObject.message }, '{s name=title}Sezzle - Payment{/s}');
        }
    },

    releaseCallback: function(options, success, response) {
        var me = this,
            responseObject = Ext.JSON.decode(response.responseText);

        if (Ext.isDefined(responseObject) && responseObject.success) {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}Sezzle{/s}', '{s name=growl/releaseSuccess}The payment has been released successfully{/s}', '{s name=title}Sezzle - Payment{/s}');
            me.subApplication.getStore('Order').reload();
        } else {
            Shopware.Notification.createStickyGrowlMessage({ title: '{s name=growl/title}Sezzle{/s}', text: responseObject.message }, '{s name=title}Sezzle - Payment{/s}');
        }
    },
});

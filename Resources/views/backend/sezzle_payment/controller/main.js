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

    refs: [
        { ref: 'detailWindow', selector: 'order-detail-window' }
    ],

    urls: {
        captureOrder: '{url module=backend controller=Sezzle action=captureOrder}',
        refundOrder: '{url module=backend controller=Sezzle action=refundOrder}',
        releaseOrder: '{url module=backend controller=Sezzle action=releaseOrder}',
    },

    sezzleRecord: null,
    panel: null,

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
            amount = parseFloat(record.get('amount')),
            isPartial = authAmount !== amount;

        me.sezzleRecord = record;
        me.panel = panel;

        panel.setLoading(true);
        Ext.Ajax.request({
            url: this.urls.captureOrder,
            params: {
                id: sezzleOrderUUID,
                currency: currency,
                amount: amount,
                isPartial: isPartial
            },
            callback: Ext.bind(me.captureCallback, me)
        });
    },

    onRefundEvent: function(record, panel, options) {
        var me = this,
            sezzleOrderUUID = record.get('sezzleOrderUUID'),
            currency = record.get('currency'),
            amount = parseFloat(record.get('amount'));

        me.sezzleRecord = record;
        me.panel = panel;

        panel.setLoading(true);
        Ext.Ajax.request({
            url: this.urls.refundOrder,
            params: {
                id: sezzleOrderUUID,
                currency: currency,
                amount: amount
            },
            callback: Ext.bind(me.refundCallback, me)
        });
    },

    onReleaseEvent: function(record, panel, options) {
        var me = this,
            sezzleOrderUUID = record.get('sezzleOrderUUID'),
            currency = record.get('currency'),
            amount = parseFloat(record.get('amount'));

        me.sezzleRecord = record;
        me.panel = panel;

        panel.setLoading(true);
        Ext.Ajax.request({
            url: this.urls.releaseOrder,
            params: {
                id: sezzleOrderUUID,
                currency: currency,
                amount: amount
            },
            callback: Ext.bind(me.releaseCallback, me)
        });
    },

    captureCallback: function(options, success, response) {
        var me = this,
            responseObject = Ext.JSON.decode(response.responseText);

        me.panel.setLoading(false);

        if (Ext.isDefined(responseObject) && responseObject.success) {
            me.sezzleRecord.set('amount', '');
            Shopware.Notification.createGrowlMessage('{s name=growl/title}Sezzle{/s}', '{s name=growl/captureSuccess}The payment has been captured successfully{/s}', '{s name=title}Sezzle - Payment{/s}');
            me.gridReload();
        } else {
            Shopware.Notification.createStickyGrowlMessage({ title: '{s name=growl/title}Sezzle{/s}', text: responseObject.message }, '{s name=title}Sezzle - Payment{/s}');
        }
    },

    refundCallback: function(options, success, response) {
        var me = this,
            responseObject = Ext.JSON.decode(response.responseText);

        me.panel.setLoading(false);
        if (Ext.isDefined(responseObject) && responseObject.success) {
            me.sezzleRecord.set('amount', '');
            Shopware.Notification.createGrowlMessage('{s name=growl/title}Sezzle{/s}', '{s name=growl/refundSuccess}The payment has been refunded successfully{/s}', '{s name=title}Sezzle - Payment{/s}');
            me.gridReload();
        } else {
            Shopware.Notification.createStickyGrowlMessage({ title: '{s name=growl/title}Sezzle{/s}', text: responseObject.message }, '{s name=title}Sezzle - Payment{/s}');
        }
    },

    releaseCallback: function(options, success, response) {
        var me = this,
            responseObject = Ext.JSON.decode(response.responseText);

        me.panel.setLoading(false);
        if (Ext.isDefined(responseObject) && responseObject.success) {
            me.sezzleRecord.set('amount', '');
            Shopware.Notification.createGrowlMessage('{s name=growl/title}Sezzle{/s}', '{s name=growl/releaseSuccess}The payment has been released successfully{/s}', '{s name=title}Sezzle - Payment{/s}');
            me.gridReload();
        } else {
            Shopware.Notification.createStickyGrowlMessage({ title: '{s name=growl/title}Sezzle{/s}', text: responseObject.message }, '{s name=title}Sezzle - Payment{/s}');
        }
    },

    gridReload: function () {
        var me = this;
            mainController = me.subApplication.getController('Main');
        mainController.showOrder(me.sezzleRecord);
        me.subApplication.getStore('Order').reload();
        me.getDetailWindow().destroy();
    }
});

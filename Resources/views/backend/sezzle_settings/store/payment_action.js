// {namespace name="backend/sezzle_settings/store/payment_action"}
Ext.define('Shopware.apps.SezzleSettings.store.PaymentAction', {
    extend: 'Ext.data.Store',

    storeId: 'SezzlePaymentAction',

    fields: [
        { name: 'type', type: 'string' },
        { name: 'text', type: 'string' }
    ],

    data: [
        { type: 'authorize_capture', text: '{s name="authorize_capture"}Authorize and Capture{/s}' },
        { type: 'authorize', text: '{s name="authorize"}Authorize Only{/s}' }
    ]
});

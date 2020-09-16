// {namespace name="backend/sezzle_settings/store/merchant_location"}
Ext.define('Shopware.apps.SezzleSettings.store.MerchantLocation', {
    extend: 'Ext.data.Store',

    storeId: 'SwagPaymentSezzleMerchantLocation',

    fields: [
        { name: 'type', type: 'string' },
        { name: 'text', type: 'string' }
    ],

    data: [
        { type: 'germany', text: '{s name="germany"}Germany{/s}' },
        { type: 'other', text: '{s name="other"}Other merchant location{/s}' }
    ]
});

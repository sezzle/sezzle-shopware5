// {namespace name="backend/sezzle_settings/store/log_level"}
Ext.define('Shopware.apps.SezzleSettings.store.LogLevel', {
    extend: 'Ext.data.Store',

    storeId: 'SwagPaymentSezzleLogLevel',

    fields: [
        { name: 'id', type: 'int' },
        { name: 'text', type: 'string' }
    ],

    data: [
        { id: '0', text: '{s name="normal"}Normal{/s}' },
        { id: '1', text: '{s name="extended"}Extended{/s}' }
    ]
});

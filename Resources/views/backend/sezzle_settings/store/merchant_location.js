// {namespace name="backend/sezzle_settings/store/merchant_location"}
Ext.define('Shopware.apps.SezzleSettings.store.MerchantLocation', {
    extend: 'Ext.data.Store',

    storeId: 'SwagPaymentSezzleMerchantLocation',

    fields: [
        { name: 'type', type: 'string' },
        { name: 'text', type: 'string' }
    ],

    data: [
        { type: 'us', text: '{s name="us"}United Stated of America{/s}' },
        { type: 'ca', text: '{s name="ca"}Canada{/s}' },
        { type: 'in', text: '{s name="in"}India{/s}' },
        { type: 'au', text: '{s name="au"}Australia{/s}' },
        { type: 'at', text: '{s name="at"}Austria{/s}' },
        { type: 'be', text: '{s name="be"}Belgium{/s}' },
        { type: 'de', text: '{s name="de"}Germany{/s}' },
        { type: 'hk', text: '{s name="hk"}Honk Kong{/s}' },
        { type: 'id', text: '{s name="id"}Indonesia{/s}' },
        { type: 'il', text: '{s name="il"}Israel{/s}' },
        { type: 'mx', text: '{s name="mx"}Mexico{/s}' },
        { type: 'nl', text: '{s name="nl"}Netherlands{/s}' },
        { type: 'nz', text: '{s name="nz"}New Zealand{/s}' },
        { type: 'uk', text: '{s name="uk"}United Kingdom{/s}' }
    ]
});

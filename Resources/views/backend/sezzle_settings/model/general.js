// {block name="backend/sezzle_settings/model/general"}
Ext.define('Shopware.apps.SezzleSettings.model.General', {
    extend: 'Shopware.data.Model',

    configure: function() {
        return {
            controller: 'SezzleSettings'
        };
    },

    fields: [
        // {block name="backend/sezzle_settings/model/general/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'shopId', type: 'int' },
        { name: 'active', type: 'bool' },
        { name: 'merchantUuid', type: 'string' },
        { name: 'publicKey', type: 'string' },
        { name: 'privateKey', type: 'string' },
        { name: 'sandbox', type: 'bool' },
        { name: 'tokenize', type: 'bool'},
        { name: 'paymentAction', type: 'string', defaultValue: 'authorize_capture' },
        { name: 'logLevel', type: 'int', defaultValue: 1 },
        { name: 'displayErrors', type: 'bool' },
        { name: 'merchantLocation', type: 'string', defaultValue: 'de' },
        { name: 'enableWidgetPdp', type: 'bool', defaultValue: false },
        { name: 'enableWidgetCart', type: 'bool', defaultValue: false },
        { name: 'gatewayRegion', type: 'string' },
    ]
});
// {/block}

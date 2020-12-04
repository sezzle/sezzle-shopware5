//{block name="backend/order/view/detail/window"}
// {$smarty.block.parent}
Ext.define('Shopware.apps.SezzlePayment.view.detail.Window', {
    override: 'Shopware.apps.Order.view.detail.Window',
    alias:'widget.sezzle-window',

    createTabPanel: function() {
        var me = this, paymentMethod,
            result = me.callParent(arguments);

        if (!me.isSezzle()) {
            return result;
        }

        Ext.Ajax.request({
            url: '{url controller=AttributeData action=loadData}',
            params: {
                _foreignKey: me.record.get('id'),
                _table: 's_order_attributes'
            },
            success: function (responseData, request) {
                var response = Ext.JSON.decode(responseData.responseText);
                var sezzleRecord = {};

                sezzleRecord.sezzleOrderUUID = response.data['__attribute_swag_sezzle_order_uuid'] || null;

                sezzleRecord.authAmount = parseFloat(response.data['__attribute_swag_sezzle_auth_amount']) || 0.00;
                sezzleRecord.capturedAmount = parseFloat(response.data['__attribute_swag_sezzle_captured_amount']) || 0.00;
                sezzleRecord.refundedAmount = parseFloat(response.data['__attribute_swag_sezzle_refunded_amount']) || 0.00;
                sezzleRecord.releasedAmount = parseFloat(response.data['__attribute_swag_sezzle_released_amount']) || 0.00;

                sezzleRecord.paymentAction = response.data['__attribute_swag_sezzle_payment_action'];
                sezzleRecord.authExpiry = response.data['__attribute_swag_sezzle_auth_expiry'];
                sezzleRecord.currency = me.record.get('currency');

                result.insert(6, Ext.create('Shopware.apps.Order.view.detail.tabs.Sezzle',{
                    title: 'Sezzle',
                    record: me.record,
                    sezzleRecord: sezzleRecord
                }));
            }
        });

        return result;
    },

    isSezzle: function () {
        var me = this;
        if (me.record && me.record.getPayment() instanceof Ext.data.Store && me.record.getPayment().first() instanceof Ext.data.Model) {
            if (me.record.getPayment().first().raw.description === 'Sezzle') {
                return true;
            }
        }
        return false;
    }
});
//{/block}

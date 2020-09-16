// {namespace name="backend/sezzle_settings/top_toolbar"}
// {block name="backend/sezzle_settings/top_toolbar"}
Ext.define('Shopware.apps.SezzleSettings.view.TopToolbar', {
    extend: 'Ext.toolbar.Toolbar',
    alias: 'widget.sezzle-settings-top-toolbar',

    ui: 'shopware-ui',
    padding: '5',
    width: '100%',
    dock: 'top',

    initComponent: function() {
        var me = this;

        me.items = me.createItems();

        me.callParent(arguments);
    },

    registerEvents: function() {
        var me = this;

        me.addEvents(
            /**
             * Will be fired if the user changed the selected shop.
             *
             * @param { Shopware.apps.Base.model.Shop } data
             */
            'changeShop'
        );
    },

    /**
     * @returns { Array }
     */
    createItems: function() {
        var me = this,
            items = [];

        items.push('->'); // Right align the selection.
        items.push(me.createShopSelection());

        return items;
    },

    /**
     * @returns { Ext.Container }
     */
    createShopSelection: function() {
        var me = this,
            attribute = {
                get: function() {
                    return 'Shopware\\Models\\Shop\\Shop';
                }
            },
            factory = Ext.create('Shopware.attribute.SelectionFactory'),
            store = factory.createDynamicSearchStore(attribute),
            selection;

        selection = Ext.create('Shopware.form.field.SingleSelection', {
            store: store,
            name: 'shopId',
            fieldLabel: '{s name=label/shop}Select shop{/s}',
            width: '33%',
            listeners: {
                select: Ext.bind(me.onSelectShop, me)
            },
            style: {
                float: 'right'
            }
        });

        store.load({
            callback: function(records) {
                Ext.Function.defer(function() {
                    selection.combo.clearValue();
                    selection.setValue(records[0]);
                    me.fireEvent('changeShop', records[0]);
                }, 1, this);
            }
        });

        return Ext.create('Ext.Container', {
            width: '100%',
            items: [
                selection
            ]
        });
    },

    /**
     * @param { Ext.form.field.ComboBox } element
     * @param { Shopware.apps.Base.model.Shop[] } record
     */
    onSelectShop: function(element, record) {
        var me = this;

        if (record[0]) {
            me.fireEvent('changeShop', record[0]);
        }
    }
});
// {/block}

// {namespace name="backend/sezzle_settings/window"}
// {block name="backend/sezzle_settings/window"}
Ext.define('Shopware.apps.SezzleSettings.view.Window', {
    extend: 'Enlight.app.Window',
    title: '{s name=title}Sezzle - Settings{/s}',
    alias: 'widget.sezzle-settings-window',

    height: '70%',
    width: '45%',
    layout: 'anchor',
    autoScroll: true,

    /**
     * @type { Shopware.apps.SezzleSettings.view.Toolbar }
     */
    toolbar: null,

    /**
     * @type { Shopware.apps.SezzleSettings.view.TopToolbar }
     */
    topToolbar: null,

    /**
     * @type { Ext.tab.Panel }
     */
    tabContainer: null,

    /**
     * @type { Shopware.apps.SezzleSettings.view.tabs.General }
     */
    generalTab: null,

    /**
     * @type { Shopware.data.Model }
     */
    record: null,

    initComponent: function() {
        var me = this;

        me.dockedItems = [me.createToolbar(), me.createTopToolbar()];
        me.items = me.createItems();

        me.callParent(arguments);

        // Manually set the background color of the window body.
        me.setBodyStyle({
            background: '#EBEDEF'
        });
    },

    /**
     * @returns { Array }
     */
    createItems: function() {
        var me = this,
            items = [];

        items.push(me.createTabElement());

        return items;
    },

    /**
     * @returns { Shopware.apps.SezzleSettings.view.Toolbar }
     */
    createToolbar: function() {
        var me = this;

        me.toolbar = Ext.create('Shopware.apps.SezzleSettings.view.Toolbar');

        return me.toolbar;
    },

    /**
     * @returns { Ext.tab.Panel }
     */
    createTabElement: function() {
        var me = this;

        me.generalTab = Ext.create('Shopware.apps.SezzleSettings.view.tabs.General');

        me.tabContainer = Ext.create('Ext.tab.Panel', {
            border: false,
            style: {
                background: '#EBEDEF'
            },

            items: [
                me.generalTab,
            ]
        });

        return me.tabContainer;
    },

    /**
     * @returns { Shopware.apps.SezzleSettings.view.TopToolbar }
     */
    createTopToolbar: function() {
        var me = this;

        me.topToolbar = Ext.create('Shopware.apps.SezzleSettings.view.TopToolbar');

        return me.topToolbar;
    }
});
// {/block}

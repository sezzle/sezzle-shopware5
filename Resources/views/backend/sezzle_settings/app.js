// {block name="backend/sezzle_settings/app"}
Ext.define('Shopware.apps.SezzleSettings', {
    extend: 'Enlight.app.SubApplication',
    name: 'Shopware.apps.SezzleSettings',

    /**
     * Enable bulk loading
     *
     * @type { Boolean }
     */
    bulkLoad: true,

    /**
     * Sets the loading path for the sub-application.
     *
     * @type { String }
     */
    loadPath: '{url action="load"}',

    /**
     * @type { Array }
     */
    controllers: [
        'Main'
    ],

    /**
     * @type { Array }
     */
    models: [
        'General',
    ],

    /**
     * @type { Array }
     */
    stores: [
        'LogLevel',
    ],

    /**
     * @type { Array }
     */
    views: [
        'Window',
        'Toolbar',
        'TopToolbar',
        'tabs.General',
    ],

    /**
     * @returns { Shopware.apps.SezzleSettings.view.Window }
     */
    launch: function() {
        var me = this,
            settingsController = me.getController('Main');

        return settingsController.mainWindow;
    }
});
// {/block}

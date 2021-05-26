// {namespace name="backend/sezzle_settings/main"}
// {block name="backend/sezzle_settings/controller/main"}
Ext.define('Shopware.apps.SezzleSettings.controller.Main', {
    extend: 'Enlight.app.Controller',

    /**
     * @type { Shopware.apps.SezzleSettings.view.Window }
     */
    window: null,

    /**
     * @type { Boolean }
     */
    settingsSaved: false,

    /**
     * @type { String }
     */
    //detailUrl: '{url controller=SezzleSettings action=detail}',

    /**
     * @type { String }
     */
    generalDetailUrl: '{url controller=SezzleSettings action=detail}',

    /**
     * @type { String }
     */
    validateAPIUrl: '{url controller=SezzleSettings action=validateAPI}',

    /**
     * @type { Shopware.apps.SezzleSettings.model.General }
     */
    generalRecord: null,

    /**
     * @type { Number }
     */
    shopId: null,

    refs: [
        { ref: 'generalTab', selector: 'sezzle-settings-tabs-general' }
    ],

    init: function() {
        var me = this;

        me.createMainWindow();
        me.createComponentControl();

        me.callParent(arguments);
    },

    createComponentControl: function() {
        var me = this;

        me.control({
            'sezzle-settings-top-toolbar': {
                changeShop: me.onChangeShop
            },
            'sezzle-settings-toolbar': {
                saveSettings: me.onSaveSettings
            },
            'sezzle-settings-tabs-general': {
                validateAPI: me.onValidateAPISettings,
                onChangeShopActivation: me.applyActivationState,
            }
        });
    },

    createMainWindow: function() {
        var me = this;
        me.window = me.getView('Window').create().show();
    },

    /**
     * @param { Number } shopId
     */
    loadDetails: function(shopId) {
        var me = this;

        me.shopId = shopId;
        me.prepareRecords();
        me.loadSetting(me.generalDetailUrl);
    },

    loadSetting: function(detailUrl) {
        var me = this;

        me.applyActivationState(false);

        Ext.Ajax.request({
            url: detailUrl,
            params: {
                shopId: me.shopId
            },
            callback: Ext.bind(me.onDetailAjaxCallback, me)
        });
    },

    saveRecords: function() {
        var me = this;

        me.generalRecord.save({
            callback: Ext.bind(me.onSaveSettingsCallback, me)
        });
    },

    prepareRecords: function() {
        var me = this,
            generalTab = me.getGeneralTab();

        me.generalRecord = Ext.create('Shopware.apps.SezzleSettings.model.General');
        me.generalRecord.set('shopId', me.shopId);
        generalTab.loadRecord(me.generalRecord);
    },

    /**
     * @param { Shopware.data.Model } record
     */
    onChangeShop: function(record) {
        var me = this,
            shopId = record.get('id');

        me.loadDetails(shopId);
    },

    onSaveSettings: function() {
        var me = this,
            generalTabForm = me.getGeneralTab().getForm(),
            generalSettings = generalTabForm.getValues();

        if (!generalTabForm.isValid()) {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}Sezzle{/s}', '{s name=growl/formValidationError}Please fill out all fields marked in red.{/s}', me.window.title);
            return;
        }

        me.window.setLoading('{s name="loading/saveSettings"}Saving settings...{/s}');
        me.generalRecord.set(generalSettings);
        me.saveRecords();
    },

    onValidateAPISettings: function() {
        var me = this,
            generalSettings = me.getGeneralTab().getForm().getValues();

        me.window.setLoading('{s name=loading/validatingAPI}Validating API settings...{/s}');

        Ext.Ajax.request({
            url: me.validateAPIUrl,
            headers: { 'Content-Type': 'application/json' },
            jsonData: {
                shopId: me.shopId,
                publicKey: generalSettings['publicKey'],
                privateKey: generalSettings['privateKey'],
                sandbox: generalSettings['sandbox']
            },
            callback: Ext.bind(me.onValidateAPIAjaxCallback, me)
        });
    },

    /**
     * @param { Object } request
     * @param { Object } response
     * @param { Object } args
     */
    onSaveSettingsCallback: function(request, response, args) {
        var me = this,
            responseObject = response.response,
            resultSetObject = response.resultSet,
            message = '';

        if (Ext.isDefined(responseObject) && response.success) {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}Sezzle{/s}', '{s name=growl/saveSettings}The settings has been saved{/s}', me.window.title);
            me.window.setLoading(false);
            return;
        }

        if (Ext.isDefined(resultSetObject) && Ext.isDefined(resultSetObject.message)) {
            message = responseObject.message;
        }

        Shopware.Notification.createStickyGrowlMessage(
            {
                title: '{s name=growl/title}Sezzle{/s}',
                text: '{s name=growl/saveSettingsError}Failed to save the settings.{/s} ' + '<br><u>' + message + '</u>'
            },
            me.window.title
        );

        me.window.setLoading(false);
    },

    /**
     * @param { Object } options
     * @param { Boolean } success
     * @param { Object } response
     */
    onValidateAPIAjaxCallback: function(options, success, response) {
        var me = this,
            responseObject = Ext.JSON.decode(response.responseText),
            message = '';

        if (Ext.isDefined(responseObject) && responseObject.success) {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}Sezzle{/s}', '{s name=growl/validateAPISuccess}The API settings are valid.{/s}', me.window.title);
            me.window.setLoading(false);

            return;
        }

        if (Ext.isDefined(responseObject) && Ext.isDefined(responseObject.message)) {
            message = responseObject.message;
        }

        Shopware.Notification.createStickyGrowlMessage(
            {
                title: '{s name=growl/title}Sezzle{/s}',
                text: '{s name=growl/validateAPIError}The API settings are invalid.{/s} ' + '<br><u>' + message + '</u>'
            },
            me.window.title
        );

        me.window.setLoading(false);
    },

    /**
     * @param { Object } options
     * @param { Boolean } success
     * @param { Object } response
     */
    onDetailAjaxCallback: function(options, success, response) {
        var me = this;

        if (!success) {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}Sezzle{/s}', '{s name=growl/loadSettingsError}Could not load settings due to an unknown error{/s}', me.window.title);
        }

        var generalTab = me.getGeneralTab(),
            settings = Ext.JSON.decode(response.responseText);

        if (settings.general) {
            me.generalRecord = Ext.create('Shopware.apps.SezzleSettings.model.General', settings.general);
            generalTab.loadRecord(me.generalRecord);
            me.applyActivationState(me.generalRecord.get('active'));
        }

        me.settingsSaved = false;
    },

    /**
     * A helper function that updates the UI depending on the activation state.
     *
     * @param { Boolean } active
     */
    applyActivationState: function(active) {
        var me = this,
            generalTab = me.getGeneralTab();


        generalTab.restContainer.setDisabled(!active);
        generalTab.merchantContainer.setDisabled(!active);
        generalTab.widgetContainer.setDisabled(!active);
        generalTab.errorHandlingContainer.setDisabled(!active);
    },
});
// {/block}

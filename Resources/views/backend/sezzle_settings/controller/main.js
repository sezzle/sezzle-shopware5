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
    detailUrl: '{url controller=SezzleSettings action=detail}',

    /**
     * @type { String }
     */
    generalDetailUrl: '{url controller=SezzleGeneralSettings action=detail}',

    /**
     * @type { String }
     */
    installmentsDetailUrl: '{url controller=SezzleInstallmentsSettings action=detail}',

    /**
     * @type { String }
     */
    expressDetailUrl: '{url controller=SezzleExpressSettings action=detail}',

    /**
     * @type { String }
     */
    plusDetailUrl: '{url controller=SezzlePlusSettings action=detail}',

    /**
     * @type { String }
     */
    registerWebhookUrl: '{url controller=SezzleSettings action=registerWebhook}',

    /**
     * @type { String }
     */
    validateAPIUrl: '{url controller=SezzleSettings action=validateAPI}',

    /**
     * @type { string }
     */
    testInstallmentsAvailabilityUrl: '{url controller=SezzleSettings action=testInstallmentsAvailability}',

    /**
     * @type { Shopware.apps.SezzleSettings.model.General }
     */
    generalRecord: null,

    /**
     * @type { Shopware.apps.SezzleSettings.model.Installments }
     */
    installmentsRecord: null,

    /**
     * @type { Shopware.apps.SezzleSettings.model.ExpressCheckout }
     */
    expressCheckoutRecord: null,

    /**
     * @type { Shopware.apps.SezzleSettings.model.Plus }
     */
    plusRecord: null,

    /**
     * @type { Number }
     */
    shopId: null,

    refs: [
        { ref: 'generalTab', selector: 'sezzle-settings-tabs-general' },
        { ref: 'plusTab', selector: 'sezzle-settings-tabs-sezzle-plus' },
        { ref: 'installmentsTab', selector: 'sezzle-settings-tabs-installments' },
        { ref: 'ecTab', selector: 'sezzle-settings-tabs-express-checkout' }
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
                registerWebhook: me.onRegisterWebhook,
                validateAPI: me.onValidateAPISettings,
                onChangeShopActivation: me.applyActivationState,
                onChangeMerchantLocation: me.applyMerchantLocationState
            },
            'sezzle-settings-tabs-installments': {
                testInstallmentsAvailability: me.onTestInstallmentsAvailability
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
        // me.loadSetting(me.expressDetailUrl);
        // me.loadSetting(me.installmentsDetailUrl);
        // me.loadSetting(me.plusDetailUrl);
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

        me.generalRecord.save();
        // me.expressCheckoutRecord.save();
        // me.installmentsRecord.save();
        // me.plusRecord.save();
    },

    prepareRecords: function() {
        var me = this,
            generalTab = me.getGeneralTab();
            // plusTab = me.getPlusTab(),
            // installmentsTab = me.getInstallmentsTab(),
            // ecTab = me.getEcTab();

        me.generalRecord = Ext.create('Shopware.apps.SezzleSettings.model.General');
        // me.expressCheckoutRecord = Ext.create('Shopware.apps.SezzleSettings.model.ExpressCheckout');
        // me.installmentsRecord = Ext.create('Shopware.apps.SezzleSettings.model.Installments');
        // me.plusRecord = Ext.create('Shopware.apps.SezzleSettings.model.Plus');

        me.generalRecord.set('shopId', me.shopId);
        // me.expressCheckoutRecord.set('shopId', me.shopId);
        // me.installmentsRecord.set('shopId', me.shopId);
        // me.plusRecord.set('shopId', me.shopId);

        // installmentsTab.loadRecord(me.installmentsRecord);
        generalTab.loadRecord(me.generalRecord);
        // plusTab.loadRecord(me.plusRecord);
        // ecTab.loadRecord(me.expressCheckoutRecord);
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
            // plusSettings = me.getPlusTab().getForm().getValues(),
            // installmentsSettings = me.getInstallmentsTab().getForm().getValues(),
            // ecTabForm = me.getEcTab().getForm(),
            // ecSettings = ecTabForm.getValues();

        if (!generalTabForm.isValid()) {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}Sezzle{/s}', '{s name=growl/formValidationError}Please fill out all fields marked in red.{/s}', me.window.title);
            return;
        }

        me.window.setLoading('{s name="loading/saveSettings"}Saving settings...{/s}');

        me.generalRecord.set(generalSettings);
        // me.expressCheckoutRecord.set(ecSettings);
        // me.installmentsRecord.set(installmentsSettings);
        // me.plusRecord.set(plusSettings);

        me.saveRecords();

        Shopware.Notification.createGrowlMessage('{s name=growl/title}Sezzle{/s}', '{s name=growl/saveSettings}The settings have been saved!{/s}', me.window.title);

        me.window.setLoading(false);
        //me.onRegisterWebhook();
    },

    onRegisterWebhook: function() {
        var me = this,
            generalSettings = me.getGeneralTab().getForm().getValues();

        me.window.setLoading('{s name="loading/registeringWebhook"}Registering webhook...{/s}');

        Ext.Ajax.request({
            url: me.registerWebhookUrl,
            params: {
                shopId: me.shopId,
                clientId: generalSettings['clientId'],
                clientSecret: generalSettings['clientSecret'],
                sandbox: generalSettings['sandbox']
            },
            callback: Ext.bind(me.onRegisterWebhookAjaxCallback, me)
        });
    },

    onValidateAPISettings: function() {
        var me = this,
            generalSettings = me.getGeneralTab().getForm().getValues();

        me.window.setLoading('{s name=loading/validatingAPI}Validating API settings...{/s}');

        Ext.Ajax.request({
            url: me.validateAPIUrl,
            params: {
                shopId: me.shopId,
                publicKey: generalSettings['publicKey'],
                privateKey: generalSettings['privateKey'],
                sandbox: generalSettings['sandbox']
            },
            callback: Ext.bind(me.onValidateAPIAjaxCallback, me)
        });
    },

    /**
     * @param { Object } options
     * @param { Boolean } success
     * @param { Object } response
     */
    onRegisterWebhookAjaxCallback: function(options, success, response) {
        var me = this,
            responseObject = Ext.JSON.decode(response.responseText),
            message = '';

        me.window.setLoading(false);

        if (Ext.isDefined(responseObject) && responseObject.success) {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}Sezzle{/s}', '{s name=growl/registerWebhookSuccess}The webhook has been successfully registered to:{/s} ' + responseObject.url, me.window.title);
            return;
        }

        if (Ext.isDefined(responseObject)) {
            message = responseObject.message;
        }

        Shopware.Notification.createStickyGrowlMessage(
            {
                title: '{s name=growl/title}Sezzle{/s}',
                text: '{s name=growl/registerWebhookError}Could not register webhook due this error:{/s}' + '<br><u>' + message + '</u>'
            },
            me.window.title
        );
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

        if (Ext.isDefined(responseObject)) {
            message = responseObject.message;
        }

        Shopware.Notification.createStickyGrowlMessage(
            {
                title: '{s name=growl/title}Sezzle{/s}',
                text: '{s name=growl/validateAPIError}The API settings are invalid:{/s} ' + '<br><u>' + message + '</u>'
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
            plusTab = me.getPlusTab(),
            installmentsTab = me.getInstallmentsTab(),
            ecTab = me.getEcTab(),
            settings = Ext.JSON.decode(response.responseText);

        if (settings.general) {
            me.generalRecord = Ext.create('Shopware.apps.SezzleSettings.model.General', settings.general);
            generalTab.loadRecord(me.generalRecord);
            me.applyActivationState(me.generalRecord.get('active'));
            if (me.generalRecord.get('merchantLocation') === 'other') {
                plusTab.setDisabled(true);
                installmentsTab.setDisabled(true);
            } else {
                generalTab.smartPaymentButtonsCheckbox.setVisible(false);
            }
        } else if (settings.installments) {
            me.installmentsRecord = Ext.create('Shopware.apps.SezzleSettings.model.Installments', settings.installments);
            installmentsTab.loadRecord(me.installmentsRecord);
        } else if (settings.express) {
            me.expressCheckoutRecord = Ext.create('Shopware.apps.SezzleSettings.model.ExpressCheckout', settings.express);
            ecTab.loadRecord(me.expressCheckoutRecord);
        } else if (settings.plus) {
            me.plusRecord = Ext.create('Shopware.apps.SezzleSettings.model.Plus', settings.plus);
            plusTab.loadRecord(me.plusRecord);
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

        //me.applyMerchantLocationState(generalTab.smartPaymentButtonsCheckbox);

        generalTab.restContainer.setDisabled(!active);
        generalTab.behaviourContainer.setDisabled(!active);
        generalTab.errorHandlingContainer.setDisabled(!active);

        // me.getPlusTab().setDisabled(!active);
        // me.getInstallmentsTab().setDisabled(!active);
        // me.getEcTab().setDisabled(!active);
    },

    applyMerchantLocationState: function(combobox) {
        var me = this,
            generalTab = me.getGeneralTab();
            // plusTab = me.getPlusTab(),
            // installmentsTab = me.getInstallmentsTab();

        generalTab.smartPaymentButtonsCheckbox.setVisible(true);

        // if (combobox.value === 'other') {
        //     // plusTab.setDisabled(true);
        //     // installmentsTab.setDisabled(true);
        //     me.plusRecord.set('active', false);
        //     me.installmentsRecord.set('active', false);
        //     generalTab.smartPaymentButtonsCheckbox.setVisible(true);
        // } else {
        //     // plusTab.setDisabled(false);
        //     // installmentsTab.setDisabled(false);
        //     generalTab.smartPaymentButtonsCheckbox.setVisible(false);
        // }
    },

    onTestInstallmentsAvailability: function() {
        var me = this,
            generalSettings = me.getGeneralTab().getForm().getValues();

        me.window.setLoading('{s name=loading/testInstallments}Test installments availability...{/s}');

        Ext.Ajax.request({
            url: me.testInstallmentsAvailabilityUrl,
            params: {
                shopId: me.shopId,
                clientId: generalSettings['clientId'],
                clientSecret: generalSettings['clientSecret'],
                sandbox: generalSettings['sandbox']
            },
            callback: Ext.bind(me.onTestInstallmentsAvailabilityCallback, me)
        });
    },

    /**
     * @param { Object } options
     * @param { Boolean } success
     * @param { Object } response
     */
    onTestInstallmentsAvailabilityCallback: function(options, success, response) {
        var me = this,
            responseObject = Ext.JSON.decode(response.responseText),
            errorMessageText = '{s name=growl/testInstallmentsAvailabilitySuccessError}Sezzle installments integration is currently not available for you. Please contact the Sezzle support.{/s} ';

        if (Ext.isDefined(responseObject) && responseObject.success) {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}Sezzle{/s}', '{s name=growl/testInstallmentsAvailabilitySuccess}Sezzle installments integration is working correct.{/s}', me.window.title);
        } else {
            if (Ext.isDefined(responseObject) && responseObject.message) {
                errorMessageText += '<br>ErrorMessage:<br><u>' + responseObject.message + '</u>';
            }

            Shopware.Notification.createGrowlMessage('{s name=growl/title}Sezzle{/s}', errorMessageText, me.window.title);
        }

        me.window.setLoading(false);
    }
});
// {/block}

// {namespace name="backend/sezzle_settings/tabs/general"}
// {block name="backend/sezzle_settings/tabs/general"}
Ext.define('Shopware.apps.SezzleSettings.view.tabs.General', {
    extend: 'Ext.form.Panel',
    alias: 'widget.sezzle-settings-tabs-general',
    title: '{s name="title"}General settings{/s}',

    anchor: '100%',
    border: false,
    bodyPadding: 10,

    style: {
        background: '#EBEDEF'
    },

    fieldDefaults: {
        anchor: '100%',
        labelWidth: 180
    },

    /**
     * @type { Ext.form.FieldSet }
     */
    restContainer: null,

    /**
     * @type { Ext.form.FieldSet }
     */
    behaviourContainer: null,

    /**
     * @type { Ext.form.FieldSet }
     */
    activationContainer: null,

    /**
     * @type { Ext.form.FieldSet }
     */
    errorHandlingContainer: null,

    initComponent: function () {
        var me = this;

        me.items = me.createItems();

        me.callParent(arguments);

        // Manually set the background color of the toolbar.
        me.toolbarContainer.setBodyStyle(me.style);
    },

    registerEvents: function () {
        var me = this;

        me.addEvents(
            /**
             * Will be fired when the user clicks on the register webhook button
             */
            'registerWebhook',

            /**
             * Will be fired when the user clicks on the Test API settings button
             */
            'validateAPI',

            /**
             * Will be fired when the user enables/disables the activation for the selected shop
             *
             * @param { Boolean }
             */
            'onChangeShopActivation',

            /**
             * Will be fired when the user changes the merchant location
             *
             * @param { String }
             */
            'onChangeMerchantLocation'
        );
    },

    /**
     * @returns { Array }
     */
    createItems: function () {
        var me = this;

        return [
            me.createNotice(),
            me.createActivationContainer(),
            me.createRestContainer(),
            me.createMerchantContainer(),
            me.createErrorHandlingContainer()
        ];
    },

    /**
     * @returns { Ext.form.Container }
     */
    createNotice: function () {
        var infoNotice = Shopware.Notification.createBlockMessage('{s name=description}Sezzle - Buy Now, Pay Later with 0% interest! Register for your Sezzle business account <a href="https://dashboard.sezzle.com/merchant/signup" title="Sezzle Merchant Dashboard SignUp" target="_blank">here</a>, if you have not done yet.{/s}', 'info');

        // There is no style defined for the type "info" in the shopware backend stylesheet, therefore we have to apply it manually
        infoNotice.style = {
            'color': 'white',
            'font-size': '14px',
            'background-color': '#4AA3DF',
            'text-shadow': '0 0 5px rgba(0, 0, 0, 0.3)'
        };

        return infoNotice;
    },

    /**
     * @returns { Ext.form.FieldSet }
     */
    createActivationContainer: function () {
        var me = this;

        me.activationContainer = Ext.create('Ext.form.FieldSet', {
            items: [
                {
                    xtype: 'checkbox',
                    name: 'active',
                    fieldLabel: '{s name="fieldset/activation/activate"}Enable for this shop{/s}',
                    boxLabel: '{s name="fieldset/activation/activate/help"}Enable this option to activate Sezzle for this shop.{/s}',
                    inputValue: true,
                    uncheckedValue: false,
                    handler: function(element, checked) {
                        me.fireEvent('onChangeShopActivation', checked);
                    }
                }
            ]
        });

        return me.activationContainer;
    },

    /**
     * @returns { Ext.form.FieldSet }
     */
    createRestContainer: function() {
        var me = this;

        me.toolbarContainer = me.createToolbar();

        me.restContainer = Ext.create('Ext.form.FieldSet', {
            title: '{s name="fieldset/rest/title"}API Settings{/s}',

            items: [
                {
                    xtype: 'textfield',
                    name: 'publicKey',
                    fieldLabel: '{s name="fieldset/rest/clientId"}Public Key{/s}',
                    helpText: '{s name="fieldset/rest/clientId/help"}The REST-API Public Key that is being used to authenticate this plugin to the Sezzle API.{/s}',
                    allowBlank: false
                },
                {
                    xtype: 'textfield',
                    name: 'privateKey',
                    fieldLabel: '{s name="fieldset/rest/clientSecret"}Private Key{/s}',
                    helpText: '{s name="fieldset/rest/clientSecret/help"}The REST-API Private Key that is being used to authenticate this plugin to the Sezzle API.{/s}',
                    allowBlank: false
                },
                {
                    xtype: 'checkbox',
                    name: 'sandbox',
                    inputValue: true,
                    uncheckedValue: false,
                    fieldLabel: '{s name="fieldset/rest/enableSandbox"}Enable sandbox{/s}',
                    boxLabel: '{s name="fieldset/rest/enableSandbox/help"}Enable this option to test the integration.{/s}'
                },
                me.toolbarContainer
            ]
        });

        return me.restContainer;
    },

    /**
     * @returns { Ext.form.FieldSet }
     */
    createMerchantContainer: function () {
        var me = this;


        me.behaviourContainer = Ext.create('Ext.form.FieldSet', {
            title: '{s name="fieldset/behaviour/title"}Merchant{/s}',
            items: [
                {
                    xtype: 'textfield',
                    name: 'merchantUuid',
                    fieldLabel: '{s name="fieldset/rest/merchantUuid"}Merchant UUID{/s}',
                    helpText: '{s name="fieldset/rest/merchantUuid/help"}The Merchant UUID that is being used to validate the merchant.{/s}',
                    allowBlank: false
                },
                {
                    xtype: 'combobox',
                    name: 'merchantLocation',
                    fieldLabel: '{s name="fieldset/behaviour/merchantLocation"}Merchant location{/s}',
                    helpText: '{s name="fieldset/behaviour/merchantLocation/help"}Choose your merchant location. Depending on this, different features are available to you.{/s}',
                    store: Ext.create('Shopware.apps.SezzleSettings.store.MerchantLocation'),
                    valueField: 'type',
                    value: 'us'
                },
                {
                    xtype: 'checkbox',
                    name: 'tokenize',
                    inputValue: true,
                    uncheckedValue: false,
                    fieldLabel: '{s name="fieldset/rest/enableTokenize"}Enable Tokenization{/s}',
                    boxLabel: '{s name="fieldset/rest/enableTokenize/help"}Enable this option to tokenize customer.{/s}'
                },
                {
                    xtype: 'combobox',
                    name: 'paymentAction',
                    fieldLabel: '{s name="fieldset/behaviour/paymentAction"}Payment Action{/s}',
                    helpText: '{s name="fieldset/behaviour/paymentAction/help"}Choose your payment action. Depending on this, payment will be captured instantly or in a delayed fashion.{/s}',
                    store: Ext.create('Shopware.apps.SezzleSettings.store.PaymentAction'),
                    valueField: 'type',
                    value: 'authorize_capture'
                }
            ]
        });

        return me.behaviourContainer;
    },

    /**
     * @returns { Ext.form.FieldSet }
     */
    createErrorHandlingContainer: function() {
        var me = this;

        me.errorHandlingContainer = Ext.create('Ext.form.FieldSet', {
            title: '{s name="fieldset/errorHandling/title"}Error handling{/s}',
            disabled: true,

            items: [{
                xtype: 'checkbox',
                name: 'displayErrors',
                helpText: '{s name=fieldset/errorHandling/displayErrors/help}If enabled, the communication error message will be displayed in the store front{/s}',
                fieldLabel: '{s name=fieldset/errorHandling/displayErrors}Display errors{/s}',
                inputValue: true,
                uncheckedValue: false
            }, {
                xtype: 'combobox',
                name: 'logLevel',
                helpText: '{s name=fieldset/errorHandling/logLevel/help}<u>Normal</u><br>Only errors will be logged to file.<br><br><u>Extended</u>Normal, Warning and Error messages will be logged to file. This is useful for debug environments.{/s}',
                fieldLabel: '{s name=fieldset/errorHandling/logLevel}Logging{/s}',
                store: Ext.create('Shopware.apps.SezzleSettings.store.LogLevel'),
                valueField: 'id',
                value: 0
            }]
        });

        return me.errorHandlingContainer;
    },

    /**
     * @returns { Ext.form.Panel }
     */
    createToolbar: function () {
        var me = this;

        return Ext.create('Ext.form.Panel', {
            dock: 'bottom',
            border: false,
            bodyPadding: 5,
            name: 'toolbarContainer',

            items: [{
                xtype: 'button',
                cls: 'primary',
                text: '{s name="fieldset/rest/testButton"}Test API settings{/s}',
                style: {
                    float: 'right'
                },
                handler: Ext.bind(me.onValidateAPIButtonClick, me)
            }]
        });
    },

    /**
     * @param { Shopware.apps.Base.view.element.Boolean } element
     * @param { Boolean } checked
     */
    onSendOrderNumberChecked: function (element, checked) {
        var me = this;

        me.orderNumberPrefix.setDisabled(!checked);
    },

    onValidateAPIButtonClick: function () {
        var me = this;

        me.fireEvent('validateAPI');
    },

    onRegisterWebhookButtonClick: function () {
        var me = this;

        me.fireEvent('registerWebhook');
    }
});
// {/block}

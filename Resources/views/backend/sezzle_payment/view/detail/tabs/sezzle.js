//{namespace name=backend/order/main}

/**
 * Shopware UI - Order detail page
 *
 * todo@all: Documentation
 */
//{block name="backend/order/view/detail/tabs/sezzle"}
Ext.define('Shopware.apps.Order.view.detail.tabs.Sezzle', {

    /**
     * Define that the additional information is an Ext.panel.Panel extension
     * @string
     */
    extend: 'Ext.form.Panel',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.order-sezzle-panel',

    /**
     * An optional extra CSS class that will be added to this component's Element.
     */
    cls: Ext.baseCSSPrefix + 'sezzle-panel shopware-form',

    /**
     * A shortcut for setting a padding style on the body element. The value can either be a number to be applied to all sides, or a normal css string describing padding.
     */
    bodyPadding: 10,

    /**
     * True to use overflow:'auto' on the components layout element and show scroll bars automatically when necessary, false to clip any overflowing content.
     */
    autoScroll: true,



    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets: {
        title: '{s name=communication/window_title}Communication{/s}',
        paymentPanel: {
            title: '{s name=sezzle/payment_panel/title}Payment Information{/s}',
        },
        paymentActionPanel: {
            title: '{s name=sezzle/payment_action_panel/title}Payment Action{/s}',
            text: '{s name=sezzle/payment_action_panel/text}Input a valid amount to capture/refund/release{/s}',
        },
        capture: {
            button: 'Capture'
        },
        refund: {
            button: 'Refund'
        },
        release: {
            button: 'Release'
        }
    },

    canCapture: false,
    canRefund: false,
    canRelease: false,

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     *
     * @return void
     */
    initComponent: function () {
        var me = this;

        me.registerEvents();
        me.determinePaymentAction();

        me.sezzleRecord.authStatus = me.isAuthValid() ? 'Not Expired' : 'Expired';

        me.items = [
            me.createPanel(),
            me.createPaymentActionFieldSet()
        ];
        me.callParent(arguments);
        me.loadRecord(me.record);


    },

    determinePaymentAction: function () {
        var me = this;

        if (me.sezzleRecord.authAmount > me.sezzleRecord.capturedAmount) {
            me.canCapture = true;
            me.canRelease = true;
        }

        if (me.sezzleRecord.paymentAction === 'authorize' && !me.isAuthValid()) {
            me.canCapture = false;
        }

        if (me.sezzleRecord.capturedAmount > me.sezzleRecord.refundedAmount) {
            me.canRefund = true;
        }
    },

    isAuthValid: function () {
        var me = this,
            authExpiry = new Date(me.sezzleRecord.authExpiry),
            currentDate = new Date();

        return currentDate < authExpiry;
    },

    createPanel: function () {
        var me = this;

        return Ext.create('Ext.form.FieldSet', {
            title: me.snippets.paymentPanel.title,
            defaults: {
                labelWidth: 155,
                labelStyle: 'font-weight: 700;'
            },
            layout: 'anchor',
            minWidth: 250,
            items: [
                {
                    xtype: 'container',
                    renderTpl: me.createPaymentTemplate(),
                    renderData: me.sezzleRecord
                }
            ]
        });
    },

    createPaymentTemplate: function () {
        return new Ext.XTemplate(
            `{literal}
            <tpl for=".">
                <div class="customer-info-pnl">
                    <div class="base-info">
                        <table>
                            <tr>
                                <td>Auth Amount : </td>
                                <td>{currency} <div id="auth_amount" style="float: right">&nbsp;{authAmount}</div></td>
                            </tr>
                            <tr>
                                <td>Captured Amount : </td>
                                <td>{currency} <div id="capture_amount" style="float: right">&nbsp;{capturedAmount}</div></td>
                            </tr>
                            <tr>
                                <td>Refunded Amount : </td>
                                <td>{currency} <div id="refund_amount" style="float: right">&nbsp;{refundedAmount}</div></td>
                            </tr>
                            <tr>
                                <td>Released Amount : </td>
                                <td>{currency} <div id="release_amount" style="float: right">&nbsp;{releasedAmount}</div></td>
                            </tr>
                        </table>
                    </div>
                    <tpl if="paymentAction == \'authorize\'">
                        <div>
                            <table>
                                <tr>
                                    <td>Auth Expiry : </td>
                                    <tpl if="authStatus == \'Expired\'">
                                        <td style="color: #ff0000">{authExpiry} ({authStatus})</td>
                                    <tpl else>
                                        <td>{authExpiry} ({authStatus})</td>
                                    </tpl>
                                </tr>
                            </table>
                        </div>
                    </tpl>
                </div>
            </tpl>
            {/literal}`
        );
    },

    /**
     * Registers the custom component events.
     * @return void
     */
    registerEvents: function () {
        this.addEvents(
            'capture',
            'refund',
            'release'
        );
    },

    /**
     * Creates the container for the internal communication fields
     * @return Ext.form.FieldSet
     */
    createPaymentActionFieldSet: function () {
        var me = this;

        return Ext.create('Ext.form.FieldSet', {
            title: me.snippets.paymentActionPanel.title,
            defaults: {
                labelWidth: 155,
                labelStyle: 'font-weight: 700;'
            },
            layout: 'anchor',
            minWidth: 250,
            items: me.createPaymentActionElements()
        });
    },

    isInputValid: function (amount, cap, method) {
        var me = this,
            paymentAction = me.sezzleRecord.paymentAction;
        if (method === 'DoCapture' && paymentAction === 'authorize' && !me.isAuthValid()) {
            Shopware.Notification.createStickyGrowlMessage({
                title: '{s name=growl/title}Sezzle{/s}',
                text: 'Auth expired. Cannot capture.'
            }, '{s name=title}Sezzle - Payment{/s}');
            return false;
        } else if (amount > cap) {
            Shopware.Notification.createStickyGrowlMessage({
                title: '{s name=growl/title}Sezzle{/s}',
                text: 'Invaild amount'
            }, '{s name=title}Sezzle - Payment{/s}');
            return false;
        }
        return true;
    },

    /**
     * Creates the elements for the internal communication field set which is displayed on
     * top of the communication tab panel.
     * @return Array - Contains the description container, the text area for the internal comment and the save button.
     */
    createPaymentActionElements: function () {
        var me = this;

        me.paymentActionDescriptionContainer = Ext.create('Ext.container.Container', {
            style: 'color: #999; font-style: italic; margin: 0 0 15px 0;',
            html: me.snippets.paymentActionPanel.text
        });

        me.paymentActionTextArea = Ext.create('Ext.form.field.Text', {
            columnWidth: 0.5,
            padding: 10,
            xtype: 'textfield',
            name: 'amount',
            layout: 'anchor',
            labelWidth: 155,
            fieldLabel: 'Amount'
        });

        if (me.canCapture) {
            me.captureButton = Ext.create('Ext.button.Button', {
                cls: 'primary',
                text: me.snippets.capture.button,
                handler: function () {
                    me.record.set('amount', me.paymentActionTextArea.getValue());
                    me.record.set('sezzleOrderUUID', me.sezzleRecord.sezzleOrderUUID);
                    me.record.set('authAmount', me.sezzleRecord.authAmount);
                    var amountAvailableForCapture = me.sezzleRecord.authAmount-me.sezzleRecord.capturedAmount;
                    if (me.isInputValid(me.paymentActionTextArea.getValue(), amountAvailableForCapture, 'DoCapture')) {
                        me.fireEvent('capture', me.record, me, {
                            callback: function (order) {
                                me.fireEvent('updateForms', order, me.up('window'));
                            },
                        });
                    }
                }
            });
        }

        if (me.canRefund) {
            me.refundButton = Ext.create('Ext.button.Button', {
                cls: 'primary',
                text: me.snippets.refund.button,
                handler: function () {
                    me.record.set('amount', me.paymentActionTextArea.getValue());
                    me.record.set('sezzleOrderUUID', me.sezzleRecord.sezzleOrderUUID);
                    me.record.set('authAmount', me.sezzleRecord.authAmount);
                    var amountAvailableForRefund = me.sezzleRecord.capturedAmount-me.sezzleRecord.refundedAmount;
                    if (me.isInputValid(me.paymentActionTextArea.getValue(), amountAvailableForRefund)) {
                        me.fireEvent('refund', me.record, me, {
                            callback: function (order) {
                                me.fireEvent('updateForms', order, me.up('window'));
                            },
                        });
                    }
                }
            });
        }

        if (me.canRelease) {
            me.releaseButton = Ext.create('Ext.button.Button', {
                cls: 'primary',
                text: me.snippets.release.button,
                handler: function () {
                    me.record.set('amount', me.paymentActionTextArea.getValue());
                    me.record.set('sezzleOrderUUID', me.sezzleRecord.sezzleOrderUUID);
                    me.record.set('authAmount', me.sezzleRecord.authAmount);
                    var amountAvailableForRelease = me.sezzleRecord.authAmount-me.sezzleRecord.capturedAmount;
                    if (me.isInputValid(me.paymentActionTextArea.getValue(), amountAvailableForRelease)) {
                        me.fireEvent('release', me.record, me, {
                            callback: function (order) {
                                me.fireEvent('updateForms', order, me.up('window'));
                            },
                        });
                    }
                }
            });
        }

        return [
            me.paymentActionDescriptionContainer,
            me.paymentActionTextArea,
            me.attributeForm,
            me.captureButton,
            me.refundButton,
            me.releaseButton,
        ];
    },

});
//{/block}

/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 *
 * @category   Shopware
 * @package    Order
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

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
    extend:'Ext.form.Panel',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.order-sezzle-panel',

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

    paymentData: {},

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        title: '{s name=communication/window_title}Communication{/s}',
        paymentPanel: {
            title: '{s name=sezzle/payment_panel/title}Payment Information{/s}',
        },
        paymentActionPanel: {
            title: '{s name=sezzle/payment_action_panel/title}Payment Action{/s}',
            text: '{s name=sezzle/payment_action_panel/text}Input a valid amount to capture/refund/release{/s}',
        },
        capture: {
            title: '{s name=communication/internal/title}Capture{/s}',
            text: '{s name=communication/internal/text}This comment box is for internal communication. The field is not visible in the frontend and for the customer at any given time.{/s}',
            label: '{s name=communication/internal/label}Internal comment{/s}',
            button: 'Capture'
        },
        refund: {
            title: '{s name=communication/external/title}Refund{/s}',
            text: '{s name=communication/external/text}This comment box is for internal communication. The field is not visible in the frontend and for the customer at any given time.{/s}',
            customerLabel: '{s name=communication/external/customer_label}Customer comment{/s}',
            externalLabel: '{s name=communication/external/external_label}Your comment{/s}',
            button: 'Refund'
        },
        release: {
            button: 'Release'
        }
    },

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
    initComponent:function () {
        var me = this;

        me.registerEvents();
        me.savePaymentPanelData();
        me.items = [
            me.createPanel(),
            me.createPaymentActionFieldSet()
        ];
        me.title = 'Sezzle';
        me.callParent(arguments);
        me.paymentActionTextArea.setValue(5);
        me.loadRecord(me.record);
    },

    savePaymentPanelData: function () {
        var me = this;
        me.paymentData = {
            authAmount : 1,
            capturedAmount : 2,
            refundedAmount : 3,
            releasedAmount : 5,
        };
    },

    createPanel:function () {
        var me = this;

        return Ext.create('Ext.form.FieldSet', {
            title: me.snippets.paymentPanel.title,
            defaults: {
                labelWidth: 155,
                labelStyle: 'font-weight: 700;'
            },
            layout: 'anchor',
            minWidth:250,
            items: [
                {
                    xtype: 'container',
                    renderTpl: me.createPaymentTemplate(),
                    renderData: me.paymentData
                }
            ]
        });

        // var me = this;
        //
        // return Ext.create('Ext.panel.Panel', {
        //     title: me.snippets.title,
        //     bodyPadding: 10,
        //     flex: 1,
        //     margin: 0,
        //     items: [
        //         {
        //             xtype: 'container',
        //             renderTpl: me.createPaymentTemplate(),
        //             renderData: pData
        //         }
        //     ]
        // });
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
                                <td>{authAmount}</td>
                            </tr>
                            <tr>
                                <td>Captured Amount : </td>
                                <td>{capturedAmount}</td>
                            </tr>
                            <tr>
                                <td>Refunded Amount : </td>
                                <td>{refundedAmount}</td>
                            </tr>
                            <tr>
                                <td>Released Amount : </td>
                                <td>{releasedAmount}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </tpl>
            {/literal}`
        );
    },

    /**
     * Registers the custom component events.
     * @return void
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the "Save internal comment" button
             * which is placed in the communication panel at the bottom of the internal field set.
             *
             * @event
             * @param [Ext.data.Model] record - The current form record
             * @param [Ext.form.Panel] form - The communication form panel
             */
            'capture',

            /**
             * Event will be fired when the user clicks the "Save external comment" button
             * which is placed in the communication panel at the bottom of the external field set.
             *
             * @event
             * @param [Ext.data.Model] record - The current form record
             * @param [Ext.form.Panel] form - The communication form panel
             */
            'refund',

            'release'
        );
    },

    /**
     * Creates the container for the internal communication fields
     * @return Ext.form.FieldSet
     */
    createPaymentActionFieldSet: function() {
        var me = this;

        return Ext.create('Ext.form.FieldSet', {
            title: me.snippets.paymentActionPanel.title,
            defaults: {
                labelWidth: 155,
                labelStyle: 'font-weight: 700;'
            },
            layout: 'anchor',
            minWidth:250,
            items: me.createPaymentActionElements()
        });
    },

    /**
     * Creates the elements for the internal communication field set which is displayed on
     * top of the communication tab panel.
     * @return Array - Contains the description container, the text area for the internal comment and the save button.
     */
    createPaymentActionElements: function() {
        var me = this;

        me.paymentActionDescriptionContainer = Ext.create('Ext.container.Container', {
            style: 'color: #999; font-style: italic; margin: 0 0 15px 0;',
            html: me.snippets.paymentActionPanel.text
        });

        // me.paymentActionTextArea = Ext.create('Ext.container.Container', {
        //     columnWidth: 0.5,
        //     layout: 'anchor',
        //     padding: 10,
        //     defaults: me.formDefaults,
        //     items: [
        //         {
        //             xtype: 'textfield',
        //             fieldLabel: 'Amount',
        //             name: 'amount'
        //         }
        //     ]
        // });

        me.paymentActionTextArea = Ext.create('Ext.form.field.Text', {
            columnWidth: 0.5,
            padding: 10,
            xtype: 'textfield',
            name: 'amount',
            layout: 'anchor',
            labelWidth: 155,
            fieldLabel: 'Amount'
        });

        // me.attributeForm = Ext.create('Shopware.attribute.Form', {
        //     table: 's_order_attributes',
        //     name: 'order-attributes',
        //     title: '{s name="attribute_title"}{/s}',
        //     border: true,
        //     margin: '10 0',
        //     bodyPadding: 10,
        //     listeners: {
        //         'hide-attribute-field-set': function () {
        //             me.attributeForm.hide();
        //         }
        //     }
        // });


        me.captureButton = Ext.create('Ext.button.Button', {
            cls: 'primary',
            text: me.snippets.capture.button,
            handler: function() {
                me.record.set('captureAmount', me.paymentActionTextArea.getValue());
                me.fireEvent('capture', me.record, me, {
                    callback: function (order) {
                        me.fireEvent('updateForms', order, me.up('window'));
                    },
                });
            }
        });

        me.refundButton = Ext.create('Ext.button.Button', {
            cls: 'primary',
            text: me.snippets.refund.button,
            handler: function() {
                me.record.set('refundAmount', me.getValues());
                me.fireEvent('refund', me.record, me, {
                    callback: function (order) {
                        me.fireEvent('updateForms', order, me.up('window'));
                    },
                });
            }
        });

        me.releaseButton = Ext.create('Ext.button.Button', {
            cls: 'primary',
            text: me.snippets.release.button,
            handler: function() {
                me.record.set('releaseAmount', me.getValues());
                me.fireEvent('release', me.record, me, {
                    callback: function (order) {
                        me.fireEvent('updateForms', order, me.up('window'));
                    },
                });
            }
        });

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

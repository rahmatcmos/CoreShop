/**
 * CoreShop
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015 Dominik Pfaffenbauer (http://dominik.pfaffenbauer.at)
 * @license    http://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 */


pimcore.registerNS("pimcore.plugin.coreshop.countries.item");
pimcore.plugin.coreshop.countries.item = Class.create(pimcore.plugin.coreshop.abstract.item, {

    iconCls : 'coreshop_icon_country',

    url : {
        save : '/plugin/CoreShop/admin_Country/save'
    },

    getItems : function() {
        return [this.getFormPanel()];
    },

    getFormPanel : function() {
        this.formPanel = new Ext.form.Panel({
            bodyStyle:'padding:20px 5px 20px 5px;',
            border: false,
            region : "center",
            autoScroll: true,
            forceLayout: true,
            defaults: {
                forceLayout: true
            },
            buttons: [
                {
                    text: t("save"),
                    handler: this.save.bind(this),
                    iconCls: "pimcore_icon_apply"
                }
            ],
            items: [
                {
                    xtype:'fieldset',
                    autoHeight:true,
                    labelWidth: 250,
                    defaultType: 'textfield',
                    defaults: {width: 300},
                    items :[
                        {
                            fieldLabel: t("coreshop_country_name"),
                            name: "name",
                            value: this.data.name
                        },
                        {
                            fieldLabel: t("coreshop_country_isoCode"),
                            name: "isoCode",
                            value: this.data.isoCode
                        },
                        {
                            xtype : 'checkbox',
                            fieldLabel: t("coreshop_country_active"),
                            name: "active",
                            checked: this.data.active === "1"
                        },
                        {
                            xtype:'combo',
                            fieldLabel:t('coreshop_country_currency'),
                            typeAhead:true,
                            value:this.data.currencyId,
                            mode:'local',
                            listWidth:100,
                            store:pimcore.globalmanager.get("coreshop_currencies"),
                            displayField:'name',
                            valueField:'id',
                            forceSelection:true,
                            triggerAction:'all',
                            name:'currencyId',
                            listeners: {
                                change: function () {
                                    this.forceReloadOnSave = true;
                                }.bind(this),
                                select: function () {
                                    this.forceReloadOnSave = true;
                                }.bind(this)
                            }
                        },
                        {
                            xtype:'combo',
                            fieldLabel:t('coreshop_country_zone'),
                            typeAhead:true,
                            value:this.data.zoneId,
                            mode:'local',
                            listWidth:100,
                            store:pimcore.globalmanager.get("coreshop_zones"),
                            displayField:'name',
                            valueField:'id',
                            forceSelection:true,
                            triggerAction:'all',
                            name:'zoneId',
                            listeners: {
                                change: function () {
                                    this.forceReloadOnSave = true;
                                }.bind(this),
                                select: function () {
                                    this.forceReloadOnSave = true;
                                }.bind(this)
                            }
                        }
                    ]
                }
            ]
        });

        return this.formPanel;
    },

    getSaveData : function() {
        return {
            data: Ext.encode(this.formPanel.getForm().getFieldValues())
        };
    }
});
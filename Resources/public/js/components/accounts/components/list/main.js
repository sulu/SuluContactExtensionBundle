/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define([
    'mvc/relationalstore',
    'app-config',
    'sulucontact/components/accounts/list/main',
    'widget-groups'
], function(RelationalStore, AppConfig, SuluBaseList, WidgetGroups) {

    'use strict';

    var constants = {
            datagridInstanceName: 'accounts'
        },

        BaseList = function() {},

        List = function() {},

        baseList,

        dataUrlAddition = '',

        /**
         * Generates the configs for the tabs in the header
         * @returns {object} tabs options
         */
        getTabConfigs = function() {
            var items, i, index, type,
                accountTypes,
                contactSection = AppConfig.getSection('sulu-contact-extension'),
                accountType,
                preselect;

            // check if accountTypes exist
            if (!contactSection || !contactSection.hasOwnProperty('accountTypes') ||
                contactSection.accountTypes.length < 1) {
                return false;
            }

            accountTypes = contactSection.accountTypes;
            // generate items
            items = [
                {
                    id: 'all',
                    name: this.sandbox.translate('public.all')
                }
            ];
            // parse accounts for tabs
            for (index in accountTypes) {
                if (index === 'basic') {
                    // exclude basic type from tabs
                    continue;
                }
                type = accountTypes[index];
                items.push({
                    id: parseInt(type.id, 10),
                    name: this.sandbox.translate(type.translation)
                });
            }

            if (!!this.options.accountType) {
                for (i in accountTypes) {
                    if (i.toLowerCase() === this.options.accountType.toLowerCase()) {
                        accountType = accountTypes[i];
                        break;
                    }
                }
                if (!accountType) {
                    throw 'accountType ' + accountType + ' does not exist!';
                }
                dataUrlAddition += '&type=' + accountType.id;
            }

            preselect = (!!accountType) ? parseInt(accountType.id, 10) + 1 : false;

            return {
                componentOptions: {
                    callback: selectFilter.bind(this),
                    preselector: 'position',
                    preselect: preselect,
                },
                data: items,
            };
        },

        selectFilter = function(item) {
            var type = null;

            if (item.id !== 'all') {
                type = item.id;
            }
            this.sandbox.emit('husky.datagrid.' + constants.datagridInstanceName + '.url.update', {'type': type});
            this.sandbox.emit('sulu.contacts.accounts.list', item.name, true); // change url, but do not reload
        },

        addNewAccount = function(type) {
            this.sandbox.emit('sulu.contacts.accounts.new', type);
        },

        clickCallback = function(id) {
            // show sidebar for selected item
            this.sandbox.emit(
                'sulu.sidebar.set-widget',
                '/admin/widget-groups/account-info?account=' + id
            );
        },

        actionCallback = function(id) {
            this.sandbox.emit('sulu.contacts.accounts.load', id);
        };

    BaseList.prototype = SuluBaseList;
    BaseList.prototype.constructor = BaseList;
    baseList = new BaseList();

    List.prototype = new BaseList();
    List.prototype.constructor = List;

    List.prototype.header = function() {
        var header = baseList.header;
        var tabs = false;
        var tabConfigs = getTabConfigs.call(this);

        if (!!tabConfigs) {
            tabs = {
                data: tabConfigs.data,
                componentOptions: tabConfigs.componentOptions
            };
        }
        header.tabs = tabs;

        header.toolbar.buttons.add = this.sandbox.util.extend(true, {}, header.toolbar.buttons.add, {
            options: {
                dropdownItems: [
                    {
                        id: 'add-basic',
                        title: this.sandbox.translate('contact.account.add-basic'),
                        callback: addNewAccount.bind(this, 'basic')
                    },
                    {
                        id: 'add-lead',
                        title: this.sandbox.translate('contact.account.add-lead'),
                        callback: addNewAccount.bind(this, 'lead')
                    },
                    {
                        id: 'add-customer',
                        title: this.sandbox.translate('contact.account.add-customer'),
                        callback: addNewAccount.bind(this, 'customer')
                    },
                    {
                        id: 'add-supplier',
                        title: this.sandbox.translate('contact.account.add-supplier'),
                        callback: addNewAccount.bind(this, 'supplier')
                    }
                ]
            }
        });

        return header;
    };

    List.prototype.render = function() {
        this.sandbox.dom.html(this.$el, this.renderTemplate('/admin/contact/template/account/list'));

        var i,
            dataUrlAddition = '',
            accountType,
        // get account types
            accountTypes = AppConfig.getSection('sulu-contact-extension').accountTypes,
            assocAccountTypes = {};

        // create LUT for accountTypes
        for (i in accountTypes) {
            assocAccountTypes[accountTypes[i].id] = accountTypes[i];
            // get current accountType
            if (!!this.options.accountType && i.toLowerCase() === this.options.accountType.toLowerCase()) {
                accountType = accountTypes[i];
            }
        }
        // define string urlAddition if accountType is set
        if (!!this.options.accountType) {
            if (!accountType) {
                throw 'accountType ' + accountType + ' does not exist!';
            }
            dataUrlAddition += '&type=' + accountType.id;
        }

        // init list-toolbar and datagrid
        this.sandbox.sulu.initListToolbarAndList.call(this, 'accounts', '/admin/api/accounts/fields',
            {
                el: this.$find('#list-toolbar-container'),
                instanceName: 'accounts',
                template: 'default'
            },
            {
                el: this.sandbox.dom.find('#companies-list', this.$el),
                url: '/admin/api/accounts?flat=true' + dataUrlAddition,
                resultKey: 'accounts',
                searchInstanceName: 'accounts',
                searchFields: ['name'],
                instanceName: constants.datagridInstanceName,
                contentFilters: {
                    // display account type name instead of type number
                    type: function(content) {
                        if (!!content) {
                            return this.sandbox.translate(assocAccountTypes[content].translation);
                        } else {
                            return '';
                        }
                    }.bind(this)
                },
                clickCallback: (WidgetGroups.exists('account-info')) ? clickCallback.bind(this) : null,
                actionCallback: actionCallback.bind(this)
            },
            'accounts',
            '#companies-list-info'
        );
    };

    return new List();
});

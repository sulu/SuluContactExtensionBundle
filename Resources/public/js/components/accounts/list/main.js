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
    'services/sulucontactextension/account-router',
    'services/sulucontactextension/account-header',
    'services/sulucontact/contact-router'
], function(RelationalStore, AppConfig, SuluBaseList, AccountRouter, AccountHeader, ContactRouter) {

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
                    name: this.sandbox.translate(type.translation),
                    key: type.name
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
                    preselect: preselect
                },
                data: items
            };
        },

        selectFilter = function(item) {
            var type = null,
                url = 'contacts/accounts';

            if (item.id !== 'all') {
                type = item.id;
                url += '/type:' + item.key.toLowerCase();
            }
            this.sandbox.emit('husky.datagrid.' + constants.datagridInstanceName + '.url.update', {'type': type});
            this.sandbox.emit('sulu.router.navigate', url, false);
        },

        addNewAccount = function(type) {
            AccountRouter.toAdd({type: type});
        },

        clickCallback = function(id) {
            // show sidebar for selected item
            this.sandbox.emit(
                'sulu.sidebar.set-widget',
                '/admin/widget-groups/account-info?account=' + id
            );
        };

    BaseList.prototype = SuluBaseList;
    BaseList.prototype.constructor = BaseList;
    baseList = new BaseList();

    List.prototype = new BaseList();
    List.prototype.constructor = List;

    List.prototype.layout = function() {
        var layout = baseList.layout;
        layout.sidebar = {
            width: 'fixed',
            cssClasses: 'sidebar-padding-50'
        };
        return layout;
    };

    List.prototype.header = function() {
        var header = baseList.header;
        var tabs = false;
        var tabConfigs = getTabConfigs.call(this);

        header.title = 'contact.accounts.title';

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
                        callback: addNewAccount.bind(this, AccountHeader.getAccountTypeIdByTypeName('basic'))
                    },
                    {
                        id: 'add-lead',
                        title: this.sandbox.translate('contact.account.add-lead'),
                        callback: addNewAccount.bind(this, AccountHeader.getAccountTypeIdByTypeName('lead'))
                    },
                    {
                        id: 'add-customer',
                        title: this.sandbox.translate('contact.account.add-customer'),
                        callback: addNewAccount.bind(this, AccountHeader.getAccountTypeIdByTypeName('customer'))
                    },
                    {
                        id: 'add-supplier',
                        title: this.sandbox.translate('contact.account.add-supplier'),
                        callback: addNewAccount.bind(this, AccountHeader.getAccountTypeIdByTypeName('supplier'))
                    }
                ]
            }
        });

        return header;
    };

    List.prototype.initialize = function() {
        baseList.initialize.call(this);
        this.sandbox.dom.off('#sidebar');

        this.sandbox.dom.on('#sidebar', 'click', function(event) {
            var id = this.sandbox.dom.data(event.currentTarget, 'id');
            AccountRouter.toEdit(id);
        }.bind(this), '#sidebar-accounts-list');

        this.sandbox.dom.on('#sidebar', 'click', function(event) {
            var id = this.sandbox.dom.data(event.currentTarget, 'id');
            ContactRouter.toEdit(id);
        }.bind(this), '#main-contact');

        this.sandbox.dom.on('#sidebar', 'click', function(event) {
            var id = this.sandbox.dom.data(event.currentTarget, 'id');
            AccountRouter.toEdit(id);
        }.bind(this), '.subsidiary-account');
    };

    List.prototype.getDatagridConfig = function() {
        var config = baseList.getDatagridConfig.call(this),
            dataUrlAddition = '',
            accountType,
            accountTypes = AppConfig.getSection('sulu-contact-extension').accountTypes,
            assocAccountTypes = {};

        // create LUT for accountTypes
        for (var i in accountTypes) {
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

        config.url = config.url + dataUrlAddition;
        config.clickCallback = clickCallback.bind(this);
        config.contentFilters = {
            // display account type name instead of type number
            type: function(content) {
                if (!!content) {
                    return this.sandbox.translate(assocAccountTypes[content].translation);
                } else {
                    return '';
                }
            }.bind(this)
        };

        return config;
    };

    return new List();
});

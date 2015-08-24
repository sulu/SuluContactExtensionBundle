/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define(['app-config'], function(AppConfig) {

    'use strict';

    // enables tabs based on account type
    var enableTabsByType = function(accountType) {
            var index;

            if (!accountType && !accountType.hasownProperty('tabs')) { // no account type specified
                return;
            }

            for (index in accountType.tabs) {
                if (accountType.tabs[index] === true) {
                    this.sandbox.emit('husky.tabs.header.item.show', index);
                }
            }
        },

    // get account type based on information given
        getAccountType = function(data, accountTypeName) {
            var typeInfo, compareAttribute, i, type,
                accountType = 0,
                accountTypes,
                section = AppConfig.getSection('sulu-contact-extension'); // get account types

            if (!section || section.length > 0 || !section.hasOwnProperty('accountTypes')) {
                return false;
            } else {
                accountTypes = section.accountTypes;
            }

            if (!!data && data.hasOwnProperty('id') && data.hasOwnProperty('type')) {
                typeInfo = data.type;
                compareAttribute = 'id';
            } else if (accountTypeName) {
                typeInfo = accountTypeName;
                compareAttribute = 'name';
            } else {
                typeInfo = 0;
                compareAttribute = 'id';
            }

            // get account type information
            for (i in accountTypes) {
                type = accountTypes[i];
                if (type[compareAttribute] === typeInfo) {
                    accountType = type;
                    break;
                }
            }

            return accountType;
        },

        /**
         * Returns items for header
         * @param accountTypes
         * @param key
         * @returns {Object}
         */
        getHeaderItem = function(accountTypes, key) {

            var item;
            this.sandbox.util.each(accountTypes, function(name, el) {
                if (el.name === key) {
                    item = {
                        title: this.sandbox.translate(el.translation + '.conversion'),
                        callback: function() {
                            this.sandbox.emit('sulu.contacts.account.convert', el);
                        }.bind(this)
                    };
                    return false;
                }
            }.bind(this));

            return item;
        },

        /**
         * Sets header toolbar with conversion options according to configuration
         */
        setHeaderToolbar = function(accountType) {
            if (!accountType.convertableTo || !Object.keys(accountType.convertableTo).length) {
                this.sandbox.emit('sulu.header.toolbar.item.hide', 'settings');
            }
        };

    return {

        /**
         * sets header data: breadcrumb, headline and content tabs for account
         * @param {Object} account Backbone-Entity
         * @param {String} [accountTypeName] Name of account entity
         */
        setHeader: function(account) {
            var accountType = getAccountType.call(this, {id: account.type, type: account.type});

            // enable tabs based on type
            enableTabsByType.call(this, accountType);

            setHeaderToolbar.call(this, accountType);
        },

        /**
         * Generates array of conversion options for a specific account type
         * @param account the account data
         * @returns {Array}
         */
        getItemsForConvertOperation: function(account) {
            var accountType, items = [],
                accountTypes = AppConfig.getSection('sulu-contact-extension').accountTypes;

            // get account type
            accountType = getAccountType.call(this, {id: account.type, type: account.type});
            this.sandbox.util.each(accountType.convertableTo, function(key, enabled) {
                if (!!enabled) {
                    var item = getHeaderItem.call(this, accountTypes, key);
                    items.push(item);
                }
            }.bind(this));

            return items;
        },

        /**
         * returns account Type-Object of a given account
         * @param account account to get type from
         * @param accountTypeName if just name of type is given
         * @returns {Object}
         */
        getAccountType: function(account, accountTypeName) {
            return getAccountType.call(this, account, accountTypeName);
        },

        /**
         * returns account-type–ID based on account-type-name
         * @param accountTypeName
         * @returns {Number}
         */
        getAccountTypeIdByTypeName: function(accountTypeName) {
            return getAccountType.call(this, null, accountTypeName).id;
        },

        /**
         * returns account-type–name based on account-type-id
         * @param accountTypeId
         * @returns {String}
         */
        getAccountTypeNameById: function(accountTypeId) {
            return this.getAccountTypeById(accountTypeId).name;
        },

        getAccountTypeById: function(id) {
            return getAccountType.call(this, {id: id, type: id});
        }
    };
});

/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define([
    'accountsutil/header',
    'sulucontact/components/accounts/main'
], function(AccountsUtilHeader, SuluBaseAccount) {

    'use strict';


    var BaseAccount = function() {
        },

        Account = function() {
        },

        baseAccount,

        convertAccount = function(data) {
            confirmConversionDialog.call(this, function(wasConfirmed) {
                if (wasConfirmed) {
                    this.account.set({type: data.id});
                    this.sandbox.emit('sulu.header.toolbar.item.loading', 'options-button');
                    this.sandbox.util.ajax('/admin/api/accounts/' + this.account.id + '?action=convertAccountType&type=' + data.name, {

                        type: 'POST',

                        success: function(response) {
                            var model = response;
                            this.sandbox.emit('sulu.header.toolbar.item.enable', 'options-button');

                            // update tabs and breadcrumb
                            this.sandbox.emit('sulu.contacts.accounts.saved', model);
                            this.setHeader();

                            // update toolbar
                            this.sandbox.emit('sulu.account.type.converted');
                        }.bind(this),

                        error: function() {
                            this.sandbox.logger.log("error while saving profile");
                        }.bind(this)
                    });
                }
            }.bind(this));
        },

        confirmConversionDialog = function(callbackFunction) {
            // check if callback is a function
            if (!!callbackFunction && typeof(callbackFunction) !== 'function') {
                throw 'callback is not a function';
            }

            // show dialog
            this.sandbox.emit('sulu.overlay.show-warning',
                'sulu.overlay.be-careful',
                'contact.accounts.type.conversion.message',
                callbackFunction.bind(this, false),
                callbackFunction.bind(this, true)
            );
        };

    BaseAccount.prototype = SuluBaseAccount;
    BaseAccount.prototype.constructor = BaseAccount;
    baseAccount = new BaseAccount();

    Account.prototype = new BaseAccount();
    Account.prototype.constructor = Account;

    Account.prototype.accountType = null;
    Account.prototype.accountTypes = null;

    Account.prototype.bindCustomEvents = function() {
        baseAccount.bindCustomEvents.call(this);

        this.sandbox.on('sulu.contacts.account.types', function(data) {
            this.accountType = data.accountType;
            this.accountTypes = data.accountTypes;
        }.bind(this));

        this.sandbox.on('sulu.contacts.account.get.types', function(callback) {
            if (typeof callback === 'function') {
                callback(this.accountType, this.accountTypes);
            }
        }.bind(this));

        this.sandbox.on('sulu.contacts.account.convert', function(data) {
            convertAccount.call(this, data);
        }.bind(this));
    };

    Account.prototype.renderList = function() {
        var $list = this.sandbox.dom.createElement('<div id="accounts-list-container"/>');
        this.html($list);
        this.sandbox.start([
            {
                name: 'accounts/components/list@sulucontact',
                options: {
                    el: $list,
                    accountType: this.options.accountType ? this.options.accountType : null
                }
            }
        ]);
    };

    Account.prototype.setHeader = function() {
        AccountsUtilHeader.setHeader.call(this, this.account, this.options.accountType);
    };

    Account.prototype.renderCreateForm = function(dfd, $form) {
        var accTypeId = AccountsUtilHeader.getAccountTypeIdByTypeName.call(this, this.options.accountType);
        this.account.set({type: accTypeId});
        this.sandbox.start([
            {name: 'accounts/components/form@sulucontact', options: {el: $form, data: this.account.toJSON()}}
        ]);

        dfd.resolve();
    };

    Account.prototype.add = function(type) {
        // TODO: show loading icon
        this.sandbox.emit('sulu.router.navigate', 'contacts/accounts/add/type:' + type);
    };

    return new Account();
});

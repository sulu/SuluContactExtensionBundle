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
    'sulucontactextension/model/termsOfPayment',
    'sulucontactextension/model/termsOfDelivery',
    'sulucontact/components/accounts/main'
], function(AccountsUtilHeader,
            TermsOfPayment,
            TermsOfDelivery,
            SuluBaseAccount) {

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
        },

        deleteTerms = function(termsKey, ids) {
            var condition, clazz, instanceName;
            if (!!ids && ids.length > 0) {

                if (termsKey === 'delivery') {
                    clazz = TermsOfDelivery;
                    instanceName = 'terms-of-delivery';
                } else if (termsKey === 'payment') {
                    clazz = TermsOfPayment;
                    instanceName = 'terms-of-payment';
                }

                this.sandbox.util.each(ids, function(index, id) {
                    condition = clazz.findOrCreate({id: id});
                    condition.destroy({
                        error: function() {
                            this.sandbox.emit(
                                'husky.select.' + instanceName + '.revert'
                            );
                        }.bind(this)
                    });
                }.bind(this));
            }
        },

        saveTerms = function(termsKey, data) {
            var instanceName, urlSuffix;

            if (!!data && data.length > 0) {
                if (termsKey === 'delivery') {
                    urlSuffix = 'termsofdeliveries';
                    instanceName = 'terms-of-delivery';
                } else if (termsKey === 'payment') {
                    urlSuffix = 'termsofpayments';
                    instanceName = 'terms-of-payment';
                }

                this.sandbox.util.save(
                    '/admin/api/' + urlSuffix,
                    'PATCH',
                    data)
                    .then(function(response) {
                        this.sandbox.emit('husky.select.' + instanceName + '.update',
                            response,
                            null,
                            true);
                    }.bind(this)).fail(function(status, error) {
                        this.sandbox.emit(
                            'husky.select.' + instanceName + '.save.revert'
                        );
                        this.sandbox.logger.error(status, error);
                    }.bind(this));
            }
        },

        /**
         * saves financial infos
         */
        saveFinancials = function(data) {
            this.sandbox.emit('sulu.header.toolbar.item.loading', 'save-button');

            this.account.set(data);

            // set correct backbone models
            if (!!data.termsOfPayment) {
                this.account.set(
                    'termsOfPayment',
                    TermsOfPayment.findOrCreate({id: data.termsOfPayment})
                );
            }
            if (!!data.termsOfDelivery) {
                this.account.set(
                    'termsOfDelivery',
                    TermsOfDelivery.findOrCreate({id: data.termsOfDelivery})
                );
            }

            this.account.save(null, {
                patch: true,
                success: function(response) {
                    var model = response.toJSON();
                    this.sandbox.emit('sulu.contacts.accounts.financials.saved', model);
                }.bind(this),
                error: function() {
                    this.sandbox.logger.log("error while saving profile");
                }.bind(this)
            });
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

        // handling of terms of delivery/payment eventlistener
        this.sandbox.on('husky.select.terms-of-delivery.delete', deleteTerms.bind(this, 'delivery'));
        this.sandbox.on('husky.select.terms-of-payment.delete', deleteTerms.bind(this, 'payment'));
        this.sandbox.on('husky.select.terms-of-delivery.save', saveTerms.bind(this, 'delivery'));
        this.sandbox.on('husky.select.terms-of-payment.save', saveTerms.bind(this, 'payment'));

        // saves financial infos
        this.sandbox.on('sulu.contacts.accounts.financials.save', saveFinancials.bind(this));
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

    Account.prototype.renderByDisplay = function() {
        if (this.options.display === 'financials') {
            this.renderComponent(
                'accounts/components/',
                this.options.display,
                'accounts-form-container',
                {},
                'sulucontactextension'
            ).then(this.setHeader.bind(this));
        } else {
            baseAccount.renderByDisplay.call(this);
        }
    };

    Account.prototype.goToList = function(account, noReload) {
        var typeString = '';
        if (!!account.type) {
            for (var i in this.accountTypes) {
                if (this.accountTypes[i].id === account.type) {
                    typeString = '/type:' + i;
                    break;
                }
            }
        }

        this.sandbox.emit(
            'sulu.router.navigate',
            'contacts/accounts' + typeString,
            !noReload,
            true,
            true
        );
    };

    return new Account();
});

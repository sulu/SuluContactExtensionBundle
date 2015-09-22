/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define([
    'services/sulucontact/account-manager',
    'services/husky/util',
    'services/husky/mediator',
    'sulucontactextension/models/termsOfPayment',
    'sulucontactextension/models/termsOfDelivery',
    'sulucontact/models/account',
], function(BaseManager, Util, Mediator, TermsOfPayment, TermsOfDelivery, Account) {

    'use strict';

    var instance = null,

        confirmConversionDialog = function(callbackFunction) {
            // check if callback is a function
            if (!!callbackFunction && typeof(callbackFunction) !== 'function') {
                throw 'callback is not a function';
            }

            // show dialog
            Mediator.emit('sulu.overlay.show-warning',
                'sulu.overlay.be-careful',
                'contact.accounts.type.conversion.message',
                callbackFunction.bind(this, false),
                callbackFunction.bind(this, true)
            );
        };

    /** @constructor **/
    function AccountManager() {
    }

    AccountManager.prototype = BaseManager;
    AccountManager.prototype.constructor = BaseManager;

    AccountManager.prototype.deleteTerms = function(termsKey, ids) {
        var condition, clazz, instanceName;
        if (!!ids && ids.length > 0) {

            if (termsKey === 'delivery') {
                clazz = TermsOfDelivery;
                instanceName = 'terms-of-delivery';
            } else if (termsKey === 'payment') {
                clazz = TermsOfPayment;
                instanceName = 'terms-of-payment';
            }

            Util.each(ids, function(index, id) {
                condition = clazz.findOrCreate({id: id});
                condition.destroy({
                    error: function() {
                        Mediator.emit('husky.select.' + instanceName + '.revert');
                    }.bind(this)
                });
            }.bind(this));
        }
    };

    AccountManager.prototype.saveTerms = function(termsKey, data) {
        var instanceName, urlSuffix;

        if (!!data && data.length > 0) {
            if (termsKey === 'delivery') {
                urlSuffix = 'termsofdeliveries';
                instanceName = 'terms-of-delivery';
            } else if (termsKey === 'payment') {
                urlSuffix = 'termsofpayments';
                instanceName = 'terms-of-payment';
            }

            Util.save(
                '/admin/api/' + urlSuffix,
                'PATCH',
                data)
                .then(function(response) {
                    Mediator.emit('husky.select.' + instanceName + '.update',
                        response,
                        null,
                        true);
                }.bind(this)).fail(function() {
                    Mediator.emit(
                        'husky.select.' + instanceName + '.save.revert'
                    );
                }.bind(this));
        }
    };

    AccountManager.prototype.saveFinancials = function(data) {
        var account = Account.findOrCreate({id: data.id}),
            promise = $.Deferred();
        account.set(data);

        // set correct backbone models
        if (!!data.termsOfPayment) {
            account.set(
                'termsOfPayment',
                TermsOfPayment.findOrCreate({id: data.termsOfPayment})
            );
        }
        if (!!data.termsOfDelivery) {
            account.set(
                'termsOfDelivery',
                TermsOfDelivery.findOrCreate({id: data.termsOfDelivery})
            );
        }

        account.save(null, {
            patch: true,
            success: function(response) {
                var model = response.toJSON();
                promise.resolve(response);
                Mediator.emit('sulu.contacts.accounts.financials.saved', model);
            }.bind(this)
        });

        return promise;
    };

    AccountManager.prototype.convertAccount = function(accountId, data) {
        var promise = $.Deferred();
        confirmConversionDialog.call(this, function(wasConfirmed) {
            if (wasConfirmed) {
                var account = Account.findOrCreate({id: accountId});
                account.set({type: data.id});

                Util.ajax('/admin/api/accounts/' + accountId + '?action=convertAccountType&type=' + data.name, {
                    type: 'POST',
                    success: function(response) {
                        var model = response;
                        // update tabs and breadcrumb
                        Mediator.emit('sulu.contacts.accounts.saved', model);
                        promise.resolve(model);
                    }.bind(this)
                });
            } else {
                promise.resolve(null);
            }
        }.bind(this));

        return promise;
    };

    AccountManager.getInstance = function() {
        if (instance === null) {
            instance = new AccountManager();
        }
        return instance;
    };

    return AccountManager.getInstance();
});

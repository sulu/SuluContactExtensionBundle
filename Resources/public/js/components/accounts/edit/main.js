/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define([
    'sulucontact/components/accounts/edit/main',
    'services/sulucontactextension/account-header',
    'services/sulucontactextension/account-router',
    'services/sulucontactextension/account-manager'
], function(SuluBaseEdit, AccountHeader, AccountRouter, AccountManager) {

    'use strict';

    var BaseEdit = function() {},
        Edit = function() {},
        baseEdit,

        convertAccount = function(data) {
            this.sandbox.emit('sulu.header.toolbar.item.loading', 'settings');
            AccountManager.convertAccount(this.data.id, data).then(function(model) {
                this.data = model;
                this.sandbox.emit('sulu.header.toolbar.item.enable', 'settings', true);
                AccountHeader.setHeader.call(this, this.data);
                this.sandbox.emit(
                    'sulu.header.toolbar.items.set', 'settings',
                    AccountHeader.getItemsForConvertOperation.call(this, this.data)
                );
            }.bind(this));
        };

    BaseEdit.prototype = SuluBaseEdit;
    BaseEdit.prototype.constructor = BaseEdit;
    baseEdit = new BaseEdit();

    Edit.prototype = new BaseEdit();
    Edit.prototype.constructor = Edit;

    Edit.prototype.header = function() {
        var header = baseEdit.header.call(this);
        if (!!this.data.id) {
            header.toolbar.buttons.settings = {
                options: {
                    dropdownItems: AccountHeader.getItemsForConvertOperation.call(this, this.data)
                }
            };
        }
        return header;
    };

    Edit.prototype.loadComponentData = function() {
        var promise = $.Deferred();
        baseEdit.loadComponentData.call(this).then(function(data) {
            if (!!this.options.accountType) {
                data.type = AccountHeader.getAccountTypeIdByTypeName(this.options.accountType);
            }
            promise.resolve(data);
        }.bind(this));
        return promise;
    };

    Edit.prototype.initialize = function() {
        baseEdit.initialize.call(this);
        AccountHeader.setHeader.call(this, this.data);
    };

    Edit.prototype.toList = function() {
        var accountType = AccountHeader.getAccountType(this.data);
        AccountRouter.toList(accountType.name);
    };

    Edit.prototype.bindCustomEvents = function() {
        baseEdit.bindCustomEvents.call(this);
        this.sandbox.on('sulu.contacts.account.convert', convertAccount.bind(this));
    };

    return new Edit();
});

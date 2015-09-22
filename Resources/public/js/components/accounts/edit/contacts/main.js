/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define([
    'sulucontact/components/accounts/edit/contacts/main'
], function(SuluBaseContacts) {

    'use strict';

    var BaseContacts = function() {},
        Contacts = function() {},
        baseContacts;

    BaseContacts.prototype = SuluBaseContacts;
    BaseContacts.prototype.constructor = BaseContacts;
    baseContacts = new BaseContacts();

    Contacts.prototype = new BaseContacts();
    Contacts.prototype.constructor = Contacts;

    Contacts.prototype.layout = function() {
        return {
            content: {
                width: 'fixed'
            },
            sidebar: {
                width: 'max',
                cssClasses: 'sidebar-padding-50'
            }
        };
    };

    Contacts.prototype.initialize = function() {
        baseContacts.initialize.call(this);

        if (!!this.data && !!this.data.id) {
            this.sandbox.emit('sulu.sidebar.set-widget', '/admin/widget-groups/account-detail?account=' + this.data.id);
        }
    };

    return new Contacts();
});

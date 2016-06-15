/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define([
    'config',
    'sulucontact/components/contacts/edit/main',
    'services/sulucontactextension/contact-header',
    'services/sulucontactextension/contact-router'
], function(Config, SuluBaseEdit, ContactHeader, ContactRouter) {

    'use strict';

    var BaseEdit = function() {},
        Edit = function() {},
        baseEdit;

    BaseEdit.prototype = SuluBaseEdit;
    BaseEdit.prototype.constructor = BaseEdit;
    baseEdit = new BaseEdit();

    Edit.prototype = new BaseEdit();
    Edit.prototype.constructor = Edit;
    Edit.prototype.loadComponentData = function() {
        var promise = $.Deferred();
        baseEdit.loadComponentData.call(this).then(function(data) {
            if (!!this.options.contactType) {
                data.type = ContactHeader.getContactTypeIdByTypeName(this.options.contactType);
            }
            promise.resolve(data);
        }.bind(this));
        
        return promise;
    };

    Edit.prototype.initialize = function() {
        baseEdit.initialize.call(this);
        ContactHeader.setHeader.call(this, this.data);

        this.sandbox.dom.off('#sidebar');

        this.sandbox.dom.on('#sidebar', 'click', function(event) {
            var id = this.sandbox.dom.data(event.currentTarget, 'id');
            ContactRouter.toEdit(id);
        }.bind(this), '#main-contact');
    };

    Edit.prototype.toList = function() {
        var contactType = ContactHeader.getContactType(this.data);
        ContactRouter.toList(contactType.name);
    };

    return new Edit();
});

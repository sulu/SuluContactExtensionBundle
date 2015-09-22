/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define([
    'sulucontact/components/contacts/edit/details/main',
    'services/sulucontact/account-router'
], function(SuluBaseForm, AccountRouter) {

    'use strict';

    var BaseForm = function() {},
        Form = function() {},
        baseForm;

    BaseForm.prototype = SuluBaseForm;
    BaseForm.prototype.constructor = BaseForm;
    baseForm = new BaseForm();

    Form.prototype = new BaseForm();
    Form.prototype.constructor = Form;

    Form.prototype.initialize = function() {
        baseForm.initialize.call(this);

        if (!!this.data && !!this.data.id) {
            this.sandbox.emit('sulu.sidebar.set-widget', '/admin/widget-groups/contact-detail?contact=' + this.data.id);
        }
        this.sandbox.dom.off('#sidebar');
        this.sandbox.dom.on('#sidebar', 'click', function(event) {
            var id = this.sandbox.dom.data(event.currentTarget,'id');
            AccountRouter.toEdit(id);
        }.bind(this), '#main-account');
    };

    Form.prototype.layout = function() {
        return {
            content: {
                width: 'fixed',
                leftSpace: false,
                rightSpace: false
            },
            sidebar: {
                width: 'max',
                cssClasses: 'sidebar-padding-50'
            }
        };
    };

    return new Form();
});

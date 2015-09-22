/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define([
    'sulucontact/components/contacts/list/main',
    'services/sulucontact/account-router',
    'services/sulucontact/contact-router'
], function(SuluBaseList, AccountRouter, ContactRouter) {

    'use strict';

    var BaseList = function() {},
        List = function() {},
        baseList,

    clickCallback = function(item) {
        // show sidebar for selected item
        this.sandbox.emit('sulu.sidebar.set-widget', '/admin/widget-groups/contact-info?contact=' + item);
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

    List.prototype.initialize = function() {
        baseList.initialize.call(this);
        this.sandbox.dom.off('#sidebar');
        this.sandbox.dom.on('#sidebar', 'click', function(event) {
            var id = this.sandbox.dom.data(event.currentTarget,'id');
            ContactRouter.toEdit(id);
        }.bind(this), '#sidebar-contact-list');
        this.sandbox.dom.on('#sidebar', 'click', function(event) {
            var id = this.sandbox.dom.data(event.currentTarget,'id');
            AccountRouter.toEdit(id);
        }.bind(this), '#main-account');
    };

    List.prototype.getDatagridConfig = function() {
        var config = baseList.getDatagridConfig.call(this);
        config.clickCallback = clickCallback.bind(this);
        return config;
    };

    return new List();
});

/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define([
    'sulucontact/components/documents-tab/main',
], function(SuluBaseTab) {

    'use strict';

    var BaseTab = function() {},
        Tab = function() {},
        baseTab;

    BaseTab.prototype = SuluBaseTab;
    BaseTab.prototype.constructor = BaseTab;
    baseTab = new BaseTab();

    Tab.prototype = new BaseTab();
    Tab.prototype.constructor = Tab;

    Tab.prototype.layout = function() {
        var layout = baseTab.layout;
        layout.sidebar = {
            width: 'max',
            cssClasses: 'sidebar-padding-50'
        };
        return layout;
    };

    Tab.prototype.initialize = function() {
        baseTab.initialize.call(this);

        if (!!this.data.id) {
            if (this.options.type === 'contact') {
                this.sandbox.emit('sulu.sidebar.set-widget', '/admin/widget-groups/contact-detail?contact=' + this.data.id);
            } else if (this.options.type === 'account') {
                this.sandbox.emit('sulu.sidebar.set-widget', '/admin/widget-groups/account-detail?account=' + this.data.id);
            }
        }
    };

    return new Tab();
});

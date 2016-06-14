/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define([
    'services/husky/mediator',
    'services/sulucontactextension/contact-header',
    'services/sulucontact/contact-router'
], function(Mediator, ContactHeader, BaseRouter) {

    'use strict';

    var instance = null;

    /** @constructor **/
    function ContactRouter() {}

    ContactRouter.prototype = BaseRouter;
    ContactRouter.prototype.constructor = ContactRouter;

    ContactRouter.prototype.toAdd = function(data) {
        Mediator.emit('sulu.router.navigate', 'contacts/contacts/add/type:' + data.type, true, true);
    };

    ContactRouter.prototype.toList = function(type) {
        var url = '/contacts/contacts';
        type = (type === 'all') ? 'basic' : type;
        if (!!type) {
            url += '/type:' + type;
        }
        Mediator.emit('sulu.router.navigate', url);
    };

    ContactRouter.getInstance = function() {
        if (instance === null) {
            instance = new ContactRouter();
        }
        return instance;
    };

    return ContactRouter.getInstance();
});

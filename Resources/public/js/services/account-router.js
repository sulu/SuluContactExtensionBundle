/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define(['services/husky/mediator', 'services/sulucontact/account-router'], function(mediator, BaseRouter) {

    'use strict';

    var instance = null;

    /** @constructor **/
    function AccountRouter() {}

    AccountRouter.prototype = BaseRouter;
    AccountRouter.prototype.constructor = AccountRouter;

    AccountRouter.prototype.toAdd = function(type) {
        mediator.emit('sulu.router.navigate', 'contacts/accounts/add/type:' + type, true, true);
    };

    AccountRouter.getInstance = function() {
        if (instance === null) {
            instance = new AccountRouter();
        }
        return instance;
    };

    return AccountRouter.getInstance();
});

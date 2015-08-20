/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

require.config({
    paths: {
        sulucontactextension: '../../sulucontactextension/js',
        '__component__$accounts/components/form@sulucontact': '/bundles/sulucontactextension/js/components/accounts/components/form/main',
        '__component__$accounts/components/list@sulucontact': '/bundles/sulucontactextension/js/components/accounts/components/list/main',
        '__component__$accounts@sulucontact': '/bundles/sulucontactextension/js/components/accounts/main',
        'accountsutil/header': '/bundles/sulucontactextension/js/components/accounts/util/header'
    }
});

define(function() {

    'use strict';

    return {

        name: 'Sulu Contact Extension Bundle',

        initialize: function(app) {
            app.components.addSource('sulucontactextension', '/bundles/sulucontactextension/js/components');

            // list all accounts
            app.sandbox.mvc.routes.push({
                route: 'contacts/accounts/type::typeid',
                callback: function(accountType) {
                    return '<div data-aura-component="accounts@sulucontact" data-aura-display="list" data-aura-account-type="' + accountType + '" />';
                }
            });

            //show for a new account
            app.sandbox.mvc.routes.push({
                route: 'contacts/accounts/add/type::id',
                callback: function(accountType) {
                    return '<div data-aura-component="accounts/components/content@sulucontact" data-aura-account-type="' + accountType + '" />';
                }
            });

        }
    };
});

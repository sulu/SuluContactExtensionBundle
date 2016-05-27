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
        '__component__$documents-tab@sulucontact': '/bundles/sulucontactextension/js/components/documents-tab/main',
        '__component__$accounts/edit@sulucontact': '/bundles/sulucontactextension/js/components/accounts/edit/main',
        '__component__$accounts/edit/details@sulucontact': '/bundles/sulucontactextension/js/components/accounts/edit/details/main',
        '__component__$contacts/edit/details@sulucontact': '/bundles/sulucontactextension/js/components/contacts/edit/details/main',
        '__component__$accounts/edit/contacts@sulucontact': '/bundles/sulucontactextension/js/components/accounts/edit/contacts/main',
        '__component__$accounts/list@sulucontact': '/bundles/sulucontactextension/js/components/accounts/list/main',
        '__component__$contacts/list@sulucontact': '/bundles/sulucontactextension/js/components/contacts/list/main',
        'services/sulucontactextension/account-router': '../../sulucontactextension/js/services/account-router',
        'services/sulucontactextension/account-manager': '../../sulucontactextension/js/services/account-manager',
        'services/sulucontactextension/account-header': '../../sulucontactextension/js/services/account-header'
    }
});

define(function() {

    'use strict';

    return {

        name: 'Sulu Contact Extension Bundle',

        initialize: function(app) {
            app.components.addSource('sulucontactextension', '/bundles/sulucontactextension/js/components');

            // List all accounts.
            app.sandbox.mvc.routes.push({
                route: 'contacts/accounts',
                callback: function() {
                    return '<div data-aura-component="accounts/list@sulucontactextension" />';
                }
            });

            // List all accounts with type filter.
            app.sandbox.mvc.routes.push({
                route: 'contacts/accounts/type::typeid',
                callback: function(accountType) {
                    return '<div data-aura-component="accounts/list@sulucontactextension" data-aura-account-type="' + accountType + '"/>';
                }
            });

            // Show for a new account.
            app.sandbox.mvc.routes.push({
                route: 'contacts/accounts/add/type::id',
                callback: function(accountType) {
                    return '<div data-aura-component="accounts/edit@sulucontact" data-aura-account-type="' + accountType + '"/>';
                }
            });
        }
    };
});

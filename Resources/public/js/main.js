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
        massivecontact: '../../massivecontact/js',
        '__component__$accounts/components/form@sulucontact': '/bundles/massivecontact/js/components/accounts/components/form/main'
    }
});

define(function() {

    'use strict';

    return {

        name: 'Massive Contact Bundle',

        initialize: function(app) {
            app.components.addSource('massivecontact', '/bundles/massivecontact/js/components');
        }
    };
});

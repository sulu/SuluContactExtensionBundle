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
    'services/sulucontactextension/contact-router',
    'app-config'
], function(SuluBaseList, ContactRouter, AppConfig) {

    'use strict';

    var BaseList = function() {},
        List = function() {},
        baseList,
        dataUrlAddition = '',

        constants = {
            datagridInstanceName: 'contacts'
        },

        /**
         * Generates the configs for the tabs in the header.
         *
         * @returns {object} tabs options
         */
        getTabConfigs = function() {
            var items, i, index, type,
                contactTypes,
                contactSection = AppConfig.getSection('sulu-contact-extension'),
                contactType,
                preselect;

            // Check if contactTypes exist.
            if (!contactSection || !contactSection.hasOwnProperty('contactTypes') ||
                contactSection.contactTypes.length < 1) {
                return false;
            }

            contactTypes = contactSection.contactTypes;
            // Generate items.
            items = [
                {
                    id: 'all',
                    name: this.sandbox.translate('public.all')
                }
            ];
            // Parse accounts for tabs.
            for (index in contactTypes) {
                if (index === 'basic') {
                    // exclude basic type from tabs
                    continue;
                }
                type = contactTypes[index];
                items.push({
                    id: parseInt(type.id, 10),
                    name: this.sandbox.translate(type.translation),
                    key: type.name
                });
            }

            if (!!this.options.contactType) {
                for (i in contactTypes) {
                    if (i.toLowerCase() === this.options.contactType.toLowerCase()) {
                        contactType = contactTypes[i];
                        break;
                    }
                }
                if (!contactType) {
                    throw 'contactType ' + contactType + ' does not exist!';
                }
                dataUrlAddition += '&type=' + contactType.id;
            }

            preselect = (!!contactType) ? parseInt(contactType.id, 10) + 1 : false;

            return {
                componentOptions: {
                    callback: selectFilter.bind(this),
                    preselector: 'position',
                    preselect: preselect
                },
                data: items
            };
        },

        addNewContact = function(typeName) {
            ContactRouter.toAdd({type: typeName});
        },

        selectFilter = function(item) {
            var type = null,
                url = 'contacts/contacts';

            if (item.id !== 'all') {
                type = item.id;
                url += '/type:' + item.key.toLowerCase();
            }
            this.sandbox.emit('husky.datagrid.' + constants.datagridInstanceName + '.url.update', {'type': type});
            this.sandbox.emit('sulu.router.navigate', url, false);
        },

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
            ContactRouter.toEdit(id);
        }.bind(this), '#main-account');
    };

    List.prototype.getDatagridConfig = function() {
        var config = baseList.getDatagridConfig.call(this);
        config.clickCallback = clickCallback.bind(this);
        return config;
    };

    List.prototype.header = function() {
        var header = baseList.header;
        var tabs = false;
        var tabConfigs = getTabConfigs.call(this);

        header.title = 'contact.contacts.title';

        if (!!tabConfigs) {
            tabs = {
                data: tabConfigs.data,
                componentOptions: tabConfigs.componentOptions
            };
        }
        header.tabs = tabs;

        var dropdownItems = [];
        var contactTypes = AppConfig.getSection('sulu-contact-extension').contactTypes;

        this.sandbox.util.each(contactTypes, function (index, value) {
            dropdownItems.push({
                id: value.id,
                title: this.sandbox.translate(value.addTranslation),
                callback: addNewContact.bind(this, value.name)
            });
        }.bind(this));

        if (dropdownItems.length > 0) {
            header.toolbar.buttons.add = this.sandbox.util.extend(true, {}, header.toolbar.buttons.add, {
                options: {
                    dropdownItems: dropdownItems
                }
            });
        }

        return header;
    };

    List.prototype.getDatagridConfig = function() {
        var config = baseList.getDatagridConfig.call(this),
            dataUrlAddition = '',
            contactType,
            contactTypes = AppConfig.getSection('sulu-contact-extension').contactTypes,
            assocContactTypes = {};

        // create LUT for contactTypes
        for (var i in contactTypes) {
            assocContactTypes[contactTypes[i].id] = contactTypes[i];
            // get current contactType
            if (!!this.options.contactType && i.toLowerCase() === this.options.contactType.toLowerCase()) {
                contactType = contactTypes[i];
            }
        }
        // define string urlAddition if contactType is set
        if (!!this.options.contactType) {
            if (!contactType) {
                throw 'contactType ' + contactType + ' does not exist!';
            }
            dataUrlAddition += '&type=' + contactType.id;
        }

        config.url = config.url + dataUrlAddition;
        config.clickCallback = clickCallback.bind(this);
        config.contentFilters = {
            // display account type name instead of type number
            type: function(content) {
                if (!!content) {
                    return this.sandbox.translate(assocContactTypes[content].translation);
                } else {
                    return '';
                }
            }.bind(this)
        };

        return config;
    };
    
    return new List();
});

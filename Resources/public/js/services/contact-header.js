/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define(['app-config'], function(AppConfig) {

    'use strict';

    // get contact type based on information given
    var getContactType = function(data, contactTypeName) {
            var typeInfo, compareAttribute, i, type,
                contactType = 0,
                contactTypes,
                section = AppConfig.getSection('sulu-contact-extension'); // get contact types

            if (!section || section.length > 0 || !section.hasOwnProperty('contactTypes')) {
                return false;
            } else {
                contactTypes = section.contactTypes;
            }

            if (!!data && data.hasOwnProperty('id') && data.hasOwnProperty('type')) {
                typeInfo = data.type;
                compareAttribute = 'id';
            } else if (contactTypeName) {
                typeInfo = contactTypeName;
                compareAttribute = 'name';
            } else {
                typeInfo = 0;
                compareAttribute = 'id';
            }

            // get contact type information
            for (i in contactTypes) {
                type = contactTypes[i];
                if (type[compareAttribute] === typeInfo) {
                    contactType = type;
                    break;
                }
            }

            return contactType;
        },

        /**
         * Returns items for header.
         *
         * @param {Object} contactTypes
         * @param {String} key
         *
         * @returns {Object}
         */
        getHeaderItem = function(contactTypes, key) {

            var item;
            this.sandbox.util.each(contactTypes, function(name, el) {
                if (el.name === key) {
                    item = {
                        title: this.sandbox.translate(el.translation + '.conversion'),
                        callback: function() {
                            this.sandbox.emit('sulu.contacts.contact.convert', el);
                        }.bind(this)
                    };
                    return false;
                }
            }.bind(this));

            return item;
        },

        /**
         * Sets header toolbar with conversion options according to configuration.
         */
        setHeaderToolbar = function(contactType) {
            if (!contactType.convertableTo || !Object.keys(contactType.convertableTo).length) {
                this.sandbox.emit('sulu.header.toolbar.item.hide', 'settings');
            }
        };

    return {

        /**
         * Sets header data: breadcrumb, headline and content tabs for contact.
         *
         * @param {Object} contact Backbone-Entity
         * @param {String} [contactTypeName] Name of contact entity
         */
        setHeader: function(contact) {
            var contactType = getContactType.call(this, {id: contact.type, type: contact.type});

            setHeaderToolbar.call(this, contactType);
        },

        /**
         * Generates array of conversion options for a specific contact type.
         *
         * @param {Object} contact the contact data
         *
         * @returns {Array}
         */
        getItemsForConvertOperation: function(contact) {
            var contactType, items = [],
                contactTypes = AppConfig.getSection('sulu-contact-extension').contactTypes;

            // get contact type
            contactType = getContactType.call(this, {id: contact.type, type: contact.type});
            this.sandbox.util.each(contactType.convertableTo, function(key, enabled) {
                if (!!enabled) {
                    var item = getHeaderItem.call(this, contactTypes, key);
                    items.push(item);
                }
            }.bind(this));

            return items;
        },

        /**
         * Returns contact Type-Object of a given contact.
         *
         * @param {Object} contact contact to get type from
         * @param {String} contactTypeName if just name of type is given
         *
         * @returns {Object}
         */
        getContactType: function(contact, contactTypeName) {
            return getContactType.call(this, contact, contactTypeName);
        },

        /**
         * Returns contact-type–ID based on contact-type-name.
         *
         * @param {String} contactTypeName
         *
         * @returns {Number}
         */
        getContactTypeIdByTypeName: function(contactTypeName) {
            return getContactType.call(this, null, contactTypeName).id;
        },

        /**
         * Returns contact-type–name based on contact-type-id.
         *
         * @param {Number} contactTypeId
         *
         * @returns {String}
         */
        getContactTypeNameById: function(contactTypeId) {
            return this.getContactTypeById(contactTypeId).name;
        },

        /**
         * Returns contact type by id.
         *
         * @param {Number} id
         *
         * @returns {String}
         */
        getContactTypeById: function(id) {
            return getContactType.call(this, {id: id, type: id});
        }
    };
});

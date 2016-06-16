/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define(['config', 'sulucontact/components/accounts/edit/details/main'], function(Config, SuluBaseForm) {

    'use strict';


    var BaseForm = function() {},
        Form = function() {},
        baseForm,

        initResponsibleContactSelect = function(formData) {
            var preselectedResponsibleContactId = !!formData.responsiblePerson ? formData.responsiblePerson.id : null;
            this.responsiblePersons = null;

            this.sandbox.util.load('api/contacts?bySystem=true')
                .then(function(response) {
                    this.responsiblePersons = response._embedded.contacts;
                    this.sandbox.start([
                        {
                            name: 'select@husky',
                            options: {
                                el: '#responsiblePerson',
                                instanceName: 'responsible-person',
                                multipleSelect: false,
                                defaultLabel: this.sandbox.translate('dropdown.please-choose'),
                                valueName: 'fullName',
                                repeatSelect: false,
                                preSelectedElements: [preselectedResponsibleContactId],
                                data: this.responsiblePersons
                            }
                        }
                    ]);
                }.bind(this))
                .fail(function(textStatus, error) {
                    this.sandbox.logger.error(textStatus, error);
                }.bind(this));
        };

    BaseForm.prototype = SuluBaseForm;
    BaseForm.prototype.constructor = BaseForm;
    baseForm = new BaseForm();

    Form.prototype = new BaseForm();
    Form.prototype.constructor = Form;

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

    Form.prototype.initialize = function() {
        baseForm.initialize.call(this);

        if (!!this.data && !!this.data.id) {
            this.sandbox.emit('sulu.sidebar.set-widget', '/admin/widget-groups/account-detail?account=' + this.data.id);
        }

        // Show toggler.
        this.sandbox.emit('sulu.header.toolbar.item.show', 'isActive');
    };

    Form.prototype.destroy = function() {
        this.sandbox.emit('sulu.header.toolbar.item.hide', 'isActive');
    },

    Form.prototype.formInitializedHandler = function(data) {
        baseForm.formInitializedHandler.call(this, data);

        initResponsibleContactSelect.call(this, data);
    };

    Form.prototype.listenForChange = function() {
        baseForm.listenForChange.call(this);

        this.sandbox.on('husky.select.responsible-person.selected.item', function(id) {
            if (id > 0) {
                this.sandbox.emit('sulu.tab.dirty');
            }
        }.bind(this));

        this.sandbox.on('husky.toggler.sulu-toolbar.changed', function(value) {
            this.data.isActive = value;
            this.sandbox.emit('sulu.tab.dirty');
        }.bind(this));
    };

    return new Form();
});

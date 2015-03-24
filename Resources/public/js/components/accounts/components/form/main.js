/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define(['config', 'widget-groups', 'sulucontact/components/accounts/components/form/main'], function(Config, WidgetGroups, SuluBaseForm) {

    'use strict';


    var BaseForm = function() {
        },

        Form = function() {
        },

        initResponsibleContactSelect = function(formData) {
            var preselectedResponsibleContactId = !!formData.responsiblePerson ? formData.responsiblePerson.id : null;
            this.responsiblePersons = null;

            this.sandbox.util.load(this.contactBySystemURL)
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
        },
        baseForm;

    BaseForm.prototype = SuluBaseForm;
    BaseForm.prototype.constructor = BaseForm;
    baseForm = new BaseForm();

    Form.prototype = new BaseForm();
    Form.prototype.constructor = Form;

    Form.prototype.formInitializedHandler = function(data) {
        baseForm.formInitializedHandler.call(this, data);

        initResponsibleContactSelect.call(this, data);
    };

    Form.prototype.listenForChange = function() {
        baseForm.listenForChange.call(this);

        this.sandbox.on('husky.select.responsible-person.selected.item', function(id) {
            if (id > 0) {
                this.setHeaderBar(false);
            }
        }.bind(this));
    };

    return new Form();
});

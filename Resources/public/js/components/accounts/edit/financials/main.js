/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define([
    'widget-groups',
    'services/sulucontactextension/account-manager'
], function(WidgetGroups, AccountManager) {

    'use strict';

    var constants = {
            overlayIdTermsOfPayment: 'overlayContainerTermsOfPayment',
            overlayIdTermsOfDelivery: 'overlayContainerTermsOfDelivery',
            overlaySelectorTermsOfPayment: '#overlayContainerTermsOfPayment',
            overlaySelectorTermsOfDelivery: '#overlayContainerTermsOfDelivery',

            cgetTermsOfDeliveryURL: 'api/termsofdeliveries',
            cgetTermsOfPaymentURL: 'api/termsofpayments',

            financialsFormSelector: '#financials-form'
        };

    return {

        view: true,

        layout: function() {
            return {
                content: {
                    width: 'fixed'
                },
                sidebar: {
                    width: 'max',
                    cssClasses: 'sidebar-padding-50'
                }
            };
        },

        templates: ['/admin/contact/template/account/financials'],

        /**
         * Constructor.
         */
        initialize: function() {
            this.options = this.sandbox.util.extend(true, {}, this.options);
            this.options.data = this.options.data();

            this.form = constants.financialsFormSelector;
            this.termsOfDeliveryInstanceName = 'terms-of-delivery';
            this.termsOfPaymentInstanceName = 'terms-of-payment';

            this.render();

            this.listenForChange();

            if (!!this.options.data && !!this.options.data.id && WidgetGroups.exists('account-detail')) {
                this.initSidebar('/admin/widget-groups/account-detail?account=', this.options.data.id);
            }
        },

        /**
         * Cleanups on exit.
         */
        destroy: function() {
            this.cleanUp();
        },

        /**
         * Initializes sidebar.
         *
         * @param {String} url
         * @param {Number} id
         */
        initSidebar: function(url, id) {
            this.sandbox.emit('sulu.sidebar.set-widget', url + id);
        },

        /**
         * Render function.
         */
        render: function() {
            var data = this.options.data;

            this.html(this.renderTemplate(this.templates[0]));

            this.initForm(data);

            this.bindDomEvents();
            this.bindCustomEvents();
        },

        /**
         * Initializes husky-selects for displaying terms.
         *
         * @param {Object} formData
         */
        initTermsSelect: function(formData) {
            this.preselectedTermsOfPaymentId =
                !!formData.termsOfPayment ? [formData.termsOfPayment.id] : '';
            this.preselectedTermsOfDeliveryId =
                !!formData.termsOfDelivery ? [formData.termsOfDelivery.id] : '';

            this.sandbox.start([
                {
                    name: 'select@husky',
                    options: {
                        el: '#termsOfPayment',
                        instanceName: this.termsOfPaymentInstanceName,
                        multipleSelect: false,
                        defaultLabel: this.sandbox.translate('public.please-choose'),
                        valueName: 'terms',
                        repeatSelect: false,
                        direction: 'bottom',
                        editable: true,
                        resultKey: 'termsOfPayments',
                        preSelectedElements: this.preselectedTermsOfPaymentId,
                        url: constants.cgetTermsOfPaymentURL
                    }
                },
                {
                    name: 'select@husky',
                    options: {
                        el: '#termsOfDelivery',
                        instanceName: this.termsOfDeliveryInstanceName,
                        multipleSelect: false,
                        defaultLabel: this.sandbox.translate('public.please-choose'),
                        valueName: 'terms',
                        repeatSelect: false,
                        direction: 'bottom',
                        editable: true,
                        resultKey: 'termsOfDeliveries',
                        preSelectedElements: this.preselectedTermsOfDeliveryId,
                        url: constants.cgetTermsOfDeliveryURL
                    }
                }
            ]);
        },

        /**
         * Initializes form.
         *
         * @param {Object} data
         */
        initForm: function(data) {
            var formObject = this.sandbox.form.create(this.form);

            formObject.initialized.then(function() {
                this.setFormData(data);
                this.initTermsSelect(data);
            }.bind(this));
        },

        /**
         * Sets data to form.
         *
         * @param data
         */
        setFormData: function(data) {
            // Add collection filters to form.
            this.sandbox.form.setData(this.form, data).then(function() {
                this.sandbox.start(this.form);
            }.bind(this)).fail(function(error) {
                this.sandbox.logger.error("An error occured when setting data!", error);
            }.bind(this));
        },

        /**
         * Bind dom events.
         */
        bindDomEvents: function() {
            // Submit on enter.
            this.sandbox.dom.keypress(this.form, function(event) {
                if (event.which === 13) {
                    event.preventDefault();
                    this.submit();
                }
            }.bind(this));
        },

        /**
         * Bind custom events.
         */
        bindCustomEvents: function() {
            this.sandbox.on('husky.select.terms-of-delivery.save', AccountManager.saveTerms.bind(this, 'delivery'));
            this.sandbox.on('husky.select.terms-of-payment.save', AccountManager.saveTerms.bind(this, 'payment'));
            this.sandbox.on('husky.select.terms-of-delivery.delete', AccountManager.deleteTerms.bind(this, 'delivery'));
            this.sandbox.on('husky.select.terms-of-payment.delete', AccountManager.deleteTerms.bind(this, 'payment'));

            // Account saved.
            this.sandbox.on('sulu.tab.save', this.submit.bind(this));
        },

        /**
         * Does some cleanup with aura components
         */
        cleanUp: function() {
            // Stop contact form before leaving.
            this.sandbox.stop(constants.financialsFormSelector);
        },

        /**
         * Submits data (save account).
         */
        submit: function() {
            if (this.sandbox.form.validate(this.form)) {
                var data = this.sandbox.form.getData(this.form);
                this.sandbox.emit('sulu.contacts.accounts.financials.save', data);
                this.sandbox.emit('sulu.tab.saving');

                AccountManager.saveFinancials(data).then(function(savedData) {
                    var savedData = savedData.toJSON();
                    this.sandbox.emit('sulu.tab.saved', savedData, true);
                    this.options.data = savedData;
                }.bind(this));
            }
        },

        /**
         * Listens for changes in form and activates save button.
         */
        listenForChange: function() {
            this.sandbox.dom.on(this.form, 'change', function() {
                this.sandbox.emit('sulu.tab.dirty');
            }.bind(this), '.changeListener select, ' +
                '.changeListener input, ' +
                '.changeListener textarea');

            this.sandbox.dom.on(this.form, 'keyup', function() {
                this.sandbox.emit('sulu.tab.dirty');
            }.bind(this), '.changeListener select, ' +
                '.changeListener input, ' +
                '.changeListener textarea');

            // If a field-type gets changed or a field gets deleted.
            this.sandbox.on('sulu.contact-form.changed', function() {
                this.sandbox.emit('sulu.tab.dirty');
            }.bind(this));

            this.sandbox.on('husky.select.' + this.termsOfDeliveryInstanceName + '.selected.item', function(id) {
                if (id > 0) {
                    this.sandbox.emit('sulu.tab.dirty');
                }
            }.bind(this));

            this.sandbox.on('husky.select.' + this.termsOfPaymentInstanceName + '.selected.item', function(id) {
                if (id > 0) {
                    this.sandbox.emit('sulu.tab.dirty');
                }
            }.bind(this));
        }
    };
});

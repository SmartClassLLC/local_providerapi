/*
 * This file is part of Moodle - http://moodle.org/
 *
 * Moodle is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Moodle is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  @package     local_providerapi
 *  @copyright   2019 Çağlar MERSİNLİ
 *  @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/str', 'core/modal_factory', 'core/modal_events', 'core/fragment', 'core/ajax', 'core/yui'],
    function($, Str, ModalFactory, ModalEvents, Fragment, Ajax, Y) {

        var SELECTORS = {
            SAVE_BUTTON: '[data-action="save"]'
        };

        /**
         *
         * @param {integer} contextid
         * @param {integer} batchid
         * @param {integer} institutionid
         * @constructor
         */
        var AddAssignCourse = function(contextid, batchid, institutionid) {
            this.contextid = contextid;
            this.batchid = batchid;
            this.institutionid = institutionid;
            this.init();
        };

        /**
         * @var {Modal} modal
         * @private
         */
        AddAssignCourse.prototype.modal = null;

        /**
         * @var {int} contextid
         * @private
         */
        AddAssignCourse.prototype.contextid = -1;

        /**
         * Initialise the class.
         *
         *
         * @private
         * @return {Promise}
         */
        AddAssignCourse.prototype.init = function() {
            var triggers = $("#courseassign");
            return Str.get_string('assigncourse', 'local_providerapi', '', '').then(function(title) {
                // Create the modal.
                return ModalFactory.create({
                    type: ModalFactory.types.SAVE_CANCEL,
                    title: title,
                    body: this.getBody(),
                }, triggers);
            }.bind(this)).then(function(modal) {
                // Keep a reference to the modal.
                this.modal = modal;
                // Forms are big, we want a big modal.
                this.modal.setLarge();

                // We want to reset the form every time it is opened.
                this.modal.getRoot().on(ModalEvents.hidden, function() {
                    this.modal.destroy();
                    document.location.reload();
                }.bind(this));

                this.modal.getRoot().on(ModalEvents.cancel, function() {
                    this.modal.destroy();
                    document.location.reload();
                }.bind(this));

                // We want to hide the submit buttons every time it is opened.
                this.modal.getRoot().on(ModalEvents.shown, function() {
                    this.modal.getRoot().append('<style>[data-fieldtype=submit] { display: none ! important; }</style>');
                }.bind(this));
                // We catch the modal save event, and use it to submit the form inside the modal.
                // Triggering a form submission will give JS validation scripts a chance to check for errors.
                this.modal.getRoot().on(ModalEvents.save, this.submitForm.bind(this));
                // We also catch the form submit event and use it to submit the form with ajax.
                this.modal.getRoot().on('submit', 'form', this.submitFormAjax.bind(this));

                return this.modal.show();
            }.bind(this));
        };

        /**
         *
         * @param {object} formdata
         * @returns {*|Deferred}
         */
        AddAssignCourse.prototype.getBody = function(formdata) {
            if (typeof formdata === "undefined") {
                formdata = {};
            }
            // Get the content of the modal.
            var params = {jsonformdata: JSON.stringify(formdata), batchid: this.batchid};
            return Fragment.loadFragment('local_providerapi', 'batchassigncourse_form', this.contextid, params);

        };

        /**
         *
         */
        AddAssignCourse.prototype.handleFormSubmissionResponse = function() {
            this.modal.hide();
            // We could trigger an event instead.
            // Yuk.
            Y.use('moodle-core-formchangechecker', function() {
                M.core_formchangechecker.reset_form_dirty_state();
            });
            document.location.reload();
        };

        /**
         *
         * @param {object} data
         */
        AddAssignCourse.prototype.handleFormSubmissionFailure = function(data) {
            // Oh noes! Epic fail :(
            // Ah wait - this is normal. We need to re-display the form with errors!
            this.modal.setBody(this.getBody(data));
            this.enableButtons();
        };

        /**
         * Private method
         *
         * @method submitFormAjax
         * @private
         * @param {Event} e Form submission event.
         */
        AddAssignCourse.prototype.submitFormAjax = function(e) {
            // We don't want to do a real form submission.
            e.preventDefault();
            // Convert all the form elements values to a serialised string.
            var formData = this.modal.getRoot().find('form').serialize();

            // Now we can continue...
            Ajax.call([{
                methodname: 'local_providerapi_assigncourseweb',
                args: {contextid: this.contextid, batchid: this.batchid, jsonformdata: JSON.stringify(formData)},
                done: this.handleFormSubmissionResponse.bind(this),
                fail: this.handleFormSubmissionFailure.bind(this, formData)
            }]);
        };

        /**
         * This triggers a form submission, so that any mform elements can do final tricks before the form submission is processed.
         *
         * @method submitForm
         * @param {Event} e Form submission event.
         * @private
         */
        AddAssignCourse.prototype.submitForm = function(e) {
            e.preventDefault();
            this.disableButtons();
            this.modal.getRoot().find('form').submit();

        };

        /**
         * Disable the buttons in the footer.
         *
         * @method disableButtons
         */
        AddAssignCourse.prototype.disableButtons = function() {
            this.modal.getFooter().find(SELECTORS.SAVE_BUTTON).prop('disabled', true);

        };

        /**
         * Enable the buttons in the footer.
         *
         * @method enableButtons
         */
        AddAssignCourse.prototype.enableButtons = function() {
            this.modal.getFooter().find(SELECTORS.SAVE_BUTTON).prop('disabled', false);
        };

        return /** @alias module:local_providerapi/addassigncourse */ {
            init: function(contextid, batchid, institutionid) {
                return new AddAssignCourse(contextid, batchid, institutionid);
            }
        };
    });
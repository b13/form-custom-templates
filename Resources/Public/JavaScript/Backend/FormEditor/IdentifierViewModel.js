/**
 * Module: TYPO3/CMS/FormCustomTemplates/Backend/FormEditor/IdentifierViewModel
 */
define(['jquery',
    'TYPO3/CMS/Form/Backend/FormEditor/Helper',
], function($, Helper) {
    'use strict';

    return (function($, Helper) {

        /**
         * @private
         *
         * @var object
         */
        let _formEditorApp = null;

        /**
         * @private
         *
         * @return object
         */
        function getFormEditorApp() {
            return _formEditorApp;
        }

        /**
         * @private
         *
         * @return object
         */
        function getPublisherSubscriber() {
            return getFormEditorApp().getPublisherSubscriber();
        }

        /**
         * @private
         *
         * @param object
         * @return object
         */
        function getHelper() {
            return Helper;
        }

        /**
         * @private
         *
         * @return object
         */
        function getUtility() {
            return getFormEditorApp().getUtility();
        }

        /**
         * @private
         *
         * @return object
         */
        function getCurrentlySelectedFormElement() {
            return getFormEditorApp().getCurrentlySelectedFormElement();
        }

        /**
         * @private
         *
         * @return object
         */
        function getViewModel() {
            return getFormEditorApp().getViewModel();
        }

        function getTemplatePropertyDomElement(templatePropertyName, templateDomElement) {
            return getHelper().getTemplatePropertyDomElement(templatePropertyName, templateDomElement);
        }

        /**
         * @private
         *
         * @return void
         * @param test
         * @param message
         * @param messageCode
         */
        function assert(test, message, messageCode) {
            return getFormEditorApp().assert(test, message, messageCode);
        }

        /**
         * @private
         *
         * @return void
         * @throws 1491643380
         */
        function _helperSetup() {
            assert('function' === $.type(Helper.bootstrap),
                'The view model helper does not implement the method "bootstrap"',
                1491643380
            );
            Helper.bootstrap(getFormEditorApp());
        }

        function _subscribeEvents() {
            getPublisherSubscriber().subscribe('view/inspector/editor/insert/perform', function (topic, args) {
                if (args[0].templateName === 'Inspector-IdentifierEditor') {
                    renderTextEditor(args[0], args[1], args[2], args[3]);
                }
            });
        }

        function renderTextEditor(editorConfiguration, editorHtml, collectionElementIdentifier, collectionName) {
            let controlsWrapper = getHelper().getDomElementDataIdentifierSelector('formElementSelectorControlsWrapper')
            $(editorHtml).find('.input-group').removeClass('input-group');
            $(controlsWrapper).remove();

            let propertyPath = getFormEditorApp().buildPropertyPath(
                editorConfiguration['propertyPath'],
                collectionElementIdentifier,
                collectionName
            );

            let formElement = getCurrentlySelectedFormElement();
            let propertyData = formElement.get(propertyPath);
            getTemplatePropertyDomElement('label', editorHtml).append(editorConfiguration['label']);

            // Set initial form values
            if (getUtility().isNonEmptyString(editorConfiguration['fieldExplanationText'])) {
                getTemplatePropertyDomElement('fieldExplanationText', editorHtml).text(editorConfiguration['fieldExplanationText']);
            } else {
                getTemplatePropertyDomElement('fieldExplanationText', editorHtml).remove();
            }
            getTemplatePropertyDomElement('propertyPath', editorHtml).val(propertyData)

            getFormEditorApp().validateCurrentlySelectedFormElementProperty(propertyPath)
            maintainIdentifierValidator(formElement.get(propertyPath), formElement.get(propertyPath), editorHtml)

            let debounce;
            let previousValue = getCurrentlySelectedFormElement().get(propertyPath);

            // Validate and update identifier on "keyup"
            getTemplatePropertyDomElement('propertyPath', editorHtml).on('keyup', function(e) {
                let identifierUsed = getFormEditorApp().isFormElementIdentifierUsed(e.currentTarget.value);

                // Do not update stage if identifier is already in use
                // because duplicated identifiers on a stage break the GUI.
                if(identifierUsed && previousValue !== e.currentTarget.value) {
                    clearTimeout(debounce);

                    debounce = setTimeout(
                        function () {
                            maintainInUseValidator(identifierUsed, editorHtml, previousValue)
                        }, 300);
                } else {
                    let formElement = getCurrentlySelectedFormElement();
                    let newFormELe = formElement.clone()

                    newFormELe.set('identifier', e.currentTarget.value, true)

                    // Wait for the validator and void firing too many events
                    // which will cause the validation to fail
                    clearTimeout(debounce);

                    debounce = setTimeout(
                        function () {
                            updateStage(newFormELe, formElement, propertyPath, editorHtml)
                        }, 300);

                    previousValue = e.currentTarget.value;
                }
            });
        }

        /**
         * Add new (cloned) element with changed identifier to
         * the stage and remove the old one.
         *
         * @param newElement
         * @param oldElement
         * @param propertyPath
         * @param editorHtml
         */
        function updateStage(newElement, oldElement, propertyPath, editorHtml) {
            getFormEditorApp().addFormElement(newElement, oldElement)
            getFormEditorApp().removeFormElement(oldElement)
            getFormEditorApp().setCurrentlySelectedFormElement(newElement);
            getViewModel().renderAbstractStageArea();
            getViewModel().renewStructure();
            getFormEditorApp().validateCurrentlySelectedFormElementProperty(propertyPath)
            maintainIdentifierValidator(newElement.get(propertyPath), oldElement.get(propertyPath), editorHtml)
        }

        /**
         * @private
         *
         * @return void
         */
        function _addPropertyValidators() {
            getFormEditorApp().addPropertyValidationValidator('FormElementIdentifierWithOutCurlyBraces', function(formElement, propertyPath) {
                if (getUtility().isUndefinedOrNull(formElement.get(propertyPath))) {
                    return;
                }

                if (!isValid(formElement.get(propertyPath))) {
                    return getFormEditorApp().getFormElementPropertyValidatorDefinition('FormElementIdentifierWithOutCurlyBraces')['errorMessage'] || 'Not a valid identifier';
                }
            });
        }

        /**
         * Validate the given identifier
         * for a given set of allowed chars/number
         * without curly braces
         *
         * @param identifier
         * @returns {boolean}
         */
        function isValid(identifier) {
            let regex = /^[a-z0-9-_]+$/gi;
            let match = regex.exec(identifier);
            return !!match;
        }

        function maintainIdentifierValidator(identifier, oldIdentifier, editorHtml) {
            if (!isValid(identifier)) {
                getTemplatePropertyDomElement('validationErrors', editorHtml)
                    .text(getFormEditorApp().getFormElementPropertyValidatorDefinition('FormElementIdentifierWithOutCurlyBraces')['errorMessage'] || 'Not a valid identifier');
                getViewModel().setElementValidationErrorClass(
                    getTemplatePropertyDomElement('validationErrors', editorHtml)
                );
                getViewModel().setElementValidationErrorClass(
                    $(getHelper().getDomElementDataIdentifierSelector('editorControlsWrapper'), $(editorHtml)),
                    'hasError'
                );
            } else {
                getTemplatePropertyDomElement('validationErrors', editorHtml).text('');
                getViewModel().removeElementValidationErrorClass(
                    getTemplatePropertyDomElement('validationErrors', editorHtml)
                );
                getViewModel().removeElementValidationErrorClass(
                    $(getHelper().getDomElementDataIdentifierSelector('editorControlsWrapper'), $(editorHtml)),
                    'hasError'
                );
            }
        }

        function maintainInUseValidator(identifierUsed, editorHtml, previousValue) {
            if (identifierUsed) {
                getTemplatePropertyDomElement('validationErrors', editorHtml)
                    .text((getFormEditorApp().getFormElementPropertyValidatorDefinition('FormElementIdentifierIsInUse')['errorMessage'] || 'Reset to {previousValue} coz it is already in use').replace('{previousValue}', previousValue));
                getViewModel().setElementValidationErrorClass(
                    getTemplatePropertyDomElement('validationErrors', editorHtml)
                );
                getViewModel().setElementValidationErrorClass(
                    $(getHelper().getDomElementDataIdentifierSelector('editorControlsWrapper'), $(editorHtml)),
                    'hasError'
                );
            } else {
                getTemplatePropertyDomElement('validationErrors', editorHtml).text('');
                getViewModel().removeElementValidationErrorClass(
                    getTemplatePropertyDomElement('validationErrors', editorHtml)
                );
                getViewModel().removeElementValidationErrorClass(
                    $(getHelper().getDomElementDataIdentifierSelector('editorControlsWrapper'), $(editorHtml)),
                    'hasError'
                );
            }
        }

        /**
         * @public
         *
         * @param object formEditorApp
         * @return void
         */
        function bootstrap(formEditorApp) {
            _formEditorApp = formEditorApp;
            _helperSetup();
            _subscribeEvents();
            _addPropertyValidators();
        }

        /**
         * Publish the public methods.
         * Implements the "Revealing Module Pattern".
         */
        return {
            bootstrap: bootstrap
        };
    })($, Helper);
});
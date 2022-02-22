/**
 * Module: TYPO3/CMS/FormCustomTemplates/Backend/FormEditor/ViewModel
 */
define(['jquery',
    'TYPO3/CMS/Form/Backend/FormEditor/Helper',
    'TYPO3/CMS/Form/Backend/FormEditor/InspectorComponent',
], function($, Helper, InspectorComponent) {
    'use strict';

    return (function($, Helper) {

        /**
         * @private
         *
         * @var object
         */
        var _formEditorApp = null;

        /**
         * @private
         *
         * @return object
         */
        function getFormEditorApp() {
            return _formEditorApp;
        };

        /**
         * @private
         *
         * @return object
         */
        function getPublisherSubscriber() {
            return getFormEditorApp().getPublisherSubscriber();
        };

        /**
         * @private
         *
         * @param object
         * @return object
         */
        function getHelper() {
            return Helper;
        };

        /**
         * @private
         *
         * @return object
         */
        function getUtility() {
            return getFormEditorApp().getUtility();
        };

        /**
         * @private
         *
         * @return object
         */
        function getCurrentlySelectedFormElement() {
            return getFormEditorApp().getCurrentlySelectedFormElement();
        };

        /**
         * @private
         *
         * @param mixed test
         * @param string message
         * @param int messageCode
         * @return void
         */
        function assert(test, message, messageCode) {
            return getFormEditorApp().assert(test, message, messageCode);
        };

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
        };

        function _subscribeEvents() {
            getPublisherSubscriber().subscribe('view/inspector/editor/insert/perform', function (topic, args) {
                if (args[0].templateName === 'Inspector-TextTemplateEditor') {
                    renderSingleSelectTemplateEditor(
                        args[0],
                        args[1],
                        args[2],
                        args[3]
                    );
                }
            });

            /**
             * @public
             *
             * @param object editorConfiguration
             * @param object editorHtml
             * @param string collectionElementIdentifier
             * @param string collectionName
             * @return void
             * @throws 1475421048
             * @throws 1475421049
             * @throws 1475421050
             * @throws 1475421051
             * @throws 1475421052
             */
            function renderSingleSelectTemplateEditor(editorConfiguration, editorHtml, collectionElementIdentifier, collectionName) {
                let propertyData, propertyPath, selectElement;

                propertyPath = getFormEditorApp().buildPropertyPath(
                    editorConfiguration['propertyPath'],
                    collectionElementIdentifier,
                    collectionName
                );
                propertyData = getCurrentlySelectedFormElement().get(propertyPath);
                console.log(propertyData);

                getHelper()
                    .getTemplatePropertyDomElement('label', editorHtml)
                    .append(editorConfiguration['label']);

                selectElement = getHelper()
                    .getTemplatePropertyDomElement('propertyPath', editorHtml);

                propertyData = getCurrentlySelectedFormElement().get(propertyPath);
                getCurrentlySelectedFormElement().set(propertyPath, propertyData);
                selectElement[0].value = propertyData

                getHelper().getTemplatePropertyDomElement('propertyPath', editorHtml).on('change', function(e) {
                    getCurrentlySelectedFormElement().set(propertyPath, e.currentTarget.selectedOptions[0].value);
                });

                // _validateCollectionElement(propertyPath, editorHtml);
                if (getUtility().isNonEmptyString(editorConfiguration['fieldExplanationText'])) {
                    getHelper()
                        .getTemplatePropertyDomElement('fieldExplanationText', editorHtml)
                        .text(editorConfiguration['fieldExplanationText']);
                } else {
                    getHelper()
                        .getTemplatePropertyDomElement('fieldExplanationText', editorHtml)
                        .remove();
                }
            };
        };

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
        };

        /**
         * Publish the public methods.
         * Implements the "Revealing Module Pattern".
         */
        return {
            bootstrap: bootstrap
        };
    })($, Helper);
});
<?php

defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

call_user_func(function () {
    // Allow backend users to drag and drop the new page type:
    ExtensionManagementUtility::addUserTSConfig(
        'options.pageTree.doktypesToShowInNewPageDragArea := addToList(125)'
    );

    // Include CE and BackendLayouts
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        "@import 'EXT:form_custom_templates/Configuration/PageTSConfig/*'"
    );
});
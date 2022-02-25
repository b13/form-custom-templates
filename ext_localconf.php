<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use B13\FormCustomTemplates\Hooks\DataStructureIdentifierHook;

defined('TYPO3') or die();

call_user_func(function () {
    // Allow backend users to drag and drop the new page type:
    ExtensionManagementUtility::addUserTSConfig(
        'options.pageTree.doktypesToShowInNewPageDragArea := addToList(125)'
    );

    // Include CE
    ExtensionManagementUtility::addPageTSConfig(
        "@import 'EXT:form_custom_templates/Configuration/PageTSConfig/*'"
    );

    // Add selectable templates to finisher override
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][FlexFormTools::class]['flexParsing'][DataStructureIdentifierHook::class] = DataStructureIdentifierHook::class;
});
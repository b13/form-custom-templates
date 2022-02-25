<?php

use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use B13\FormCustomTemplates\Hooks\DataStructureIdentifierHook;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') or die();

call_user_func(function () {
    // Allow backend users to drag and drop the new page doktype:
    ExtensionManagementUtility::addUserTSConfig(
        'options.pageTree.doktypesToShowInNewPageDragArea := addToList(125)'
    );

    // Include CE
    ExtensionManagementUtility::addPageTSConfig(
        "@import 'EXT:form_custom_templates/Configuration/PageTSConfig/*'"
    );

    // Add selectable templates to plugin settings override
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][FlexFormTools::class]['flexParsing'][DataStructureIdentifierHook::class] = DataStructureIdentifierHook::class;

    // Register icon
    $iconRegistry = GeneralUtility::makeInstance(
        IconRegistry::class
    );

    $iconRegistry->registerIcon(
        'page-email',
        SvgIconProvider::class,
        ['source' => 'EXT:form_custom_templates/Resources/Public/Icons/page-email.svg']
    );
});
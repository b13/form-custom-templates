<?php

declare(strict_types=1);

defined('TYPO3') or die();

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

(function ($extensionKey = 'form_custom_templates', $table = 'pages') {
    // Add page type
    $emailDoktype = (string)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('form_custom_templates', 'doktype');

    ExtensionManagementUtility::addTcaSelectItem(
        $table,
        'doktype',
        [
            'label' => 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/Database:form_custom_templates.pageType',
            'value' => $emailDoktype,
            'icon' => 'apps-pagetree-page-email',
            'group' => 'special',
        ],
        '254',
        'before'
    );

    ArrayUtility::mergeRecursiveWithOverrule(
        $GLOBALS['TCA'][$table],
        [
            // add icon for new page type:
            'ctrl' => [
                'typeicon_classes' => [
                    $emailDoktype => 'apps-pagetree-page-email',
                    $emailDoktype . '-hideinmenu' => 'apps-pagetree-page-email-hideinmenu',
                ],
            ],
            // Show only fields useful in email context
            'palettes' => [
                'visibilityEmailTemplate' => [
                    'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.visibility',
                    'showitem' => 'hidden',
                ],
            ],
            'types' => [
                $emailDoktype => [
                    'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, --palette--;;standard, --palette--;;title, --div--;LLL:EXT:seo/Resources/Private/Language/locallang_tca.xlf:pages.tabs.seo, --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.appearance, --palette--;;layout, --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.resources, --palette--;;media, --palette--;;config, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, --palette--;;language, --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.access, --palette--;;visibilityEmailTemplate, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended',
                ],
            ],
        ]
    );
})();

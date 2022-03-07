<?php

defined('TYPO3') or die();

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

(function ($extensionKey = 'form_custom_templates', $table='pages') {

    // Add page type
    $emailDoktype = (int)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('form_custom_templates', 'doktype');
    ExtensionManagementUtility::addTcaSelectItem(
        $table,
        'doktype',
        [
            'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/Database:form_custom_templates.pageType',
            $emailDoktype,
            'page-email'
        ],
        '1',
        'after'
    );

    ArrayUtility::mergeRecursiveWithOverrule(
        $GLOBALS['TCA'][$table],
        [
            // add icon for new page type:
            'ctrl' => [
                'typeicon_classes' => [
                    $emailDoktype => 'page-email',
                    $emailDoktype . '-contentFromPid' => "page-email-contentFromPid",
                    $emailDoktype . '-root' => "page-email-root",
                    $emailDoktype . '-hideinmenu' => "page-email-hideinmenu",
                ],
            ],
            // add all page standard fields and tabs to your new page type
            'types' => [
                $emailDoktype => [
                    //'showitem' => $GLOBALS['TCA'][$table]['types'][PageRepository::DOKTYPE_DEFAULT]['showitem']
                    'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, --palette--;;standard, --palette--;;title, --div--;LLL:EXT:seo/Resources/Private/Language/locallang_tca.xlf:pages.tabs.seo, --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.appearance, --palette--;;layout, --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.behaviour, --palette--;;miscellaneous, --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.resources, --palette--;;media, --palette--;;config, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, --palette--;;language, --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.access, --palette--;;visibility, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended',
                ]
            ],
            'palettes' => [
                'layout' => [
                    'showitem' => 'layout, --linebreak--,backend_layout, backend_layout_next_level'
                ],
                'miscellaneous' => [
                    'showitem' => 'is_siteroot;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.is_siteroot_formlabel, no_search;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.no_search_formlabel'
                ],
                'visibility' => [
                    'showitem' => 'hidden;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:pages.hidden_toggle_formlabel'
                ]
            ]
        ]
    );
})();
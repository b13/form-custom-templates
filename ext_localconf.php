<?php

use B13\FormCustomTemplates\Hooks\DataStructureEmailOptionsHook;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') or die();

call_user_func(function () {
    $doktype = (int)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('form_custom_templates', 'doktype');
    $typeNum = (int)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('form_custom_templates', 'typeNum');

    // Allow backend users to drag and drop the new page doktype:
    ExtensionManagementUtility::addUserTSConfig(
        'options.pageTree.doktypesToShowInNewPageDragArea := addToList(' . $doktype . ')'
    );

    ExtensionManagementUtility::addTypoScriptConstants('
        plugin.tx_form_custom_templates {
            typeNum = ' . $typeNum . '
            doktype = ' . $doktype . '
        }
    ');

    ExtensionManagementUtility::addPageTSConfig(
        '
        [traverse(page, "doktype") == ' . $doktype . ']
            TCEFORM.tt_content {
                CType {
                    removeItems = list, shortcut, form_formframework, textmedia, image,header,textpic,bullets,uploads,table,menu_abstract,menu_categorized_content,menu_categorized_pages,menu_pages,menu_subpages,menu_recently_updated,menu_related_pages,menu_section,menu_section_pages,menu_sitemap,menu_sitemap_pages,felogin_login,div,html
                }
            }
        [GLOBAL]

        @import "EXT:form_custom_templates/Configuration/PageTsConfig/main.tsconfig"
    '
    );

    // Add selectable templates to plugin settings override
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][FlexFormTools::class]['flexParsing'][DataStructureEmailOptionsHook::class] = DataStructureEmailOptionsHook::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['Backend\Template\Components\ButtonBar']['getButtonsHook'][] = 'B13\FormCustomTemplates\Hooks\PlaintextPreviewHook->previewButton';
});

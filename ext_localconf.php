<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') or die();

call_user_func(function () {
    $doktype = (int)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('form_custom_templates', 'doktype');
    $typeNum = (int)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('form_custom_templates', 'typeNum');


    ExtensionManagementUtility::addTypoScriptConstants('
        plugin.tx_form_custom_templates {
            typeNum = ' . $typeNum . '
            doktype = ' . $doktype . '
        }
    ');

    $userTs = 'options.pageTree.doktypesToShowInNewPageDragArea := addToList(' . $doktype . ')';
    $pageTs = '
        [traverse(page, "doktype") == ' . $doktype . ']
            TCEFORM.tt_content {
                CType {
                    removeItems = list, shortcut, form_formframework, textmedia, image,header,textpic,bullets,uploads,table,menu_abstract,menu_categorized_content,menu_categorized_pages,menu_pages,menu_subpages,menu_recently_updated,menu_related_pages,menu_section,menu_section_pages,menu_sitemap,menu_sitemap_pages,felogin_login,div,html
                }
            }
        [GLOBAL]
    ';

    if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() < 13) {
        // Allow backend users to drag and drop the new page doktype:
        ExtensionManagementUtility::addUserTSConfig($userTs);
        ExtensionManagementUtility::addPageTSConfig($pageTs);
        ExtensionManagementUtility::addPageTSConfig(
            '@import "EXT:form_custom_templates/Configuration/PageTsConfig/main.tsconfig"'
        );
    } else {
        $GLOBALS['TYPO3_CONF_VARS']['BE']['defaultUserTSconfig'] .= chr(10) . $userTs;
        $GLOBALS['TYPO3_CONF_VARS']['BE']['defaultPageTSconfig'] .= chr(10) . $pageTs;
    }

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
        '
            module.tx_form {
                settings {
                    yamlConfigurations {
                        500 = EXT:form_custom_templates/Configuration/Yaml/FormSetup.yaml
                    }
                }
            }
    '
    );

});

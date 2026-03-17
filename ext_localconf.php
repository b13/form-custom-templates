<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
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

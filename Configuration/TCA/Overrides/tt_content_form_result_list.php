<?php

declare(strict_types=1);

defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

call_user_func(function () {

    ExtensionManagementUtility::addTcaSelectItem(
        'tt_content',
        'CType',
        [
            'label' => 'LLL:EXT:form_custom_templates/Resources/Private/Language/Database.xlf:form_custom_templates.cType',
            'value' => 'form_result_list',
            'icon' => 'content-elements-mailform',
            'description' => 'LLL:EXT:form_custom_templates/Resources/Private/Language/Database.xlf:form_custom_templates.cType.description',
            'group' => 'forms',
        ],
    );


    // Set content overview icon
    $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes']['form_result_list'] = 'content-elements-mailform';

    ArrayUtility::mergeRecursiveWithOverrule(
        $GLOBALS['TCA']['tt_content'],
        [
            'types' => [
                'form_result_list' => [
                    'showitem' => $GLOBALS['TCA']['tt_content']['types']['header']['showitem'],
                ],
            ],
        ]
    );
});

<?php

defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

call_user_func(function () {
    ExtensionManagementUtility::addPlugin(
        [
            'LLL:EXT:form_custom_templates/Resources/Private/Language/Database.xlf:form_custom_templates.cType',
            'form_result_list',
            'page-email',
        ],
        'CType',
        'form_custom_templates',
    );

    // Set content overview icon
    $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes']['form_result_list'] = 'page-email';

    ArrayUtility::mergeRecursiveWithOverrule(
        $GLOBALS['TCA']['tt_content'],
        [
            'types' => [
                'form_result_list' => [
                    'showitem' => $GLOBALS['TCA']['tt_content']['types']['header']['showitem'],
                ]
            ],
        ]
    );
});

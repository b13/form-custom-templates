<?php

defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

call_user_func(function()
{
    $extensionKey = 'form_custom_templates';

    ExtensionManagementUtility::addStaticFile(
        $extensionKey,
        'Configuration/TypoScript',
        'Form Custom Template'
    );

    // Provide basic configuration to get easily started
    ExtensionManagementUtility::addStaticFile(
        $extensionKey,
        'Configuration/TypoScript/Email',
        'Form Custom Template Email Frontend'
    );
});

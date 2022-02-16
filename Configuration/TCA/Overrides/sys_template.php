<?php
defined('TYPO3') or die();

call_user_func(function()
{
    $extensionKey = 'form_custom_templates';

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        $extensionKey,
        'Configuration/TypoScript',
        'Form Custom Template'
    );
});

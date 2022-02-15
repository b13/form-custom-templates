<?php
defined('TYPO3') or die();

call_user_func(function()
{
    $extensionKey = 'form_email_template';

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        $extensionKey,
        'Configuration/TypoScript',
        'B13 Form Email Template'
    );
});

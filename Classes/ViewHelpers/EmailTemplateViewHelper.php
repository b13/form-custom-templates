<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates\ViewHelpers;

use B13\FormCustomTemplates\Service\EmailTemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Retrieve all pages of 'doktype' Email (most likely 125)
 */
class EmailTemplateViewHelper extends AbstractViewHelper
{
    public function render(): array
    {
        $emailTemplateService = GeneralUtility::makeInstance(EmailTemplateService::class);
        $options = array_reduce($emailTemplateService->getEmailTemplatePages(), static function ($options, $item) {
            $index = $item['uid'];
            $options[$index] = $item['title'];

            return $options;
        }, []);

        return $options;
    }
}

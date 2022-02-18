<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates\Domain\Finisher;

use TYPO3\CMS\Form\Domain\Finishers\EmailFinisher;

class EmailTemplateFinisher extends EmailFinisher
{
    protected function executeInternal(): void
    {
        // For v10 compatibility reasons we check for [Empty] value
        if (!empty($this->options['emailTemplate']) && $this->options['emailTemplate'] !== '[Empty]') {
            $this->setOption('templateName', $this->options['emailTemplate']);
        }
        parent::executeInternal();
    }
}

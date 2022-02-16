<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates\Domain\Finisher;

use TYPO3\CMS\Form\Domain\Finishers\EmailFinisher;

class EmailTemplateFinisher extends EmailFinisher
{
    protected function executeInternal(): void
    {
        if (isset($this->options['emailTemplate'])) {
            $this->setOption('templateName', $this->options['emailTemplate']);
        }
        parent::executeInternal();
    }
}

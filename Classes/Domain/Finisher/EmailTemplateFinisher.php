<?php

declare(strict_types=1);

namespace B13\FormEmailTemplate\Domain\Finisher;

use TYPO3\CMS\Form\Domain\Finishers\EmailFinisher;

class EmailTemplateFinisher extends EmailFinisher
{
    protected function executeInternal(): void
    {
        $this->setOption('templateName', '../' . $this->options['emailTemplate']);
        parent::executeInternal();
    }
}

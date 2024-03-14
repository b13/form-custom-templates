<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates\Backend\EventListener;

use B13\FormCustomTemplates\Service\EmailTemplateService;
use TYPO3\CMS\Core\Configuration\Event\AfterFlexFormDataStructureParsedEvent;

final class FlexFormParsingModifyEventListener
{
    public function __construct(private readonly EmailTemplateService $emailTemplateService)
    {
    }

    public function modifyDataStructure(AfterFlexFormDataStructureParsedEvent $event): void
    {
        $identifier = $event->getIdentifier();
        if (($identifier['ext-form-overrideFinishers'] ?? '') === 'enabled') {
            $parsedDataStructure = $event->getDataStructure();
            foreach ($parsedDataStructure['sheets'] as $sheetIdentifier => $sheet) {
                $addToFinishers = ['EmailToSender', 'EmailToReceiver'];
                foreach ($addToFinishers as $finisherIdentifier) {
                    if ($parsedDataStructure['sheets'][$sheetIdentifier]['ROOT']['el']['settings.finishers.' . $finisherIdentifier . '.emailTemplateUid'] ?? false) {
                        $options = $parsedDataStructure['sheets'][$sheetIdentifier]['ROOT']['el']['settings.finishers.' . $finisherIdentifier . '.emailTemplateUid']['config']['items'];
                        $parsedDataStructure['sheets'][$sheetIdentifier]['ROOT']['el']['settings.finishers.' . $finisherIdentifier . '.emailTemplateUid']['config']['items'] = array_merge($options, $this->emailTemplateService->getOptions());
                    }
                }
            }
            $event->setDataStructure($parsedDataStructure);
        }
    }
}

<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates\Backend\EventListener;

use B13\FormCustomTemplates\Service\EmailTemplateService;
use TYPO3\CMS\Core\Configuration\Event\AfterFlexFormDataStructureParsedEvent;

final class FlexFormParsingModifyEventListener
{
    public function __construct(private readonly EmailTemplateService $emailTemplateService) {}

    public function modifyDataStructure(AfterFlexFormDataStructureParsedEvent $event): void
    {
        $identifier = $event->getIdentifier();

        if (($identifier['ext-form-overrideFinishers'] ?? '') === 'enabled') {
            $addToFinishers = ['EmailToSender', 'EmailToReceiver'];
            $options = $this->emailTemplateService->getOptions();
            $dataStructure = $event->getDataStructure();

            // Search for finishers and add items
            foreach ($dataStructure['sheets'] as $sheetIdentifier => $sheet) {
                foreach ($addToFinishers as $finisherIdentifier) {
                    if ($dataStructure['sheets'][$sheetIdentifier]['ROOT']['el']['settings.finishers.' . $finisherIdentifier . '.emailTemplateUid'] ?? false) {
                        $allOptions = array_merge(
                            $dataStructure['sheets'][$sheetIdentifier]['ROOT']['el']['settings.finishers.' . $finisherIdentifier . '.emailTemplateUid']['TCEforms']['config']['items'],
                            $options
                        );
                        $dataStructure['sheets'][$sheetIdentifier]['ROOT']['el']['settings.finishers.' . $finisherIdentifier . '.emailTemplateUid']['TCEforms']['config']['items'] = $allOptions;
                    }
                }
            }

            $event->setDataStructure($dataStructure);
        }
    }
}

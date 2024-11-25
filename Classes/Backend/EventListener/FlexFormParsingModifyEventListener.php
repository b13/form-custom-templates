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

        if (($identifier['ext-form-overrideFinishers'] ?? '') !== 'enabled') {
            return;
        }

        $addToFinishers = ['EmailToSender', 'EmailToReceiver'];
        $options = $this->emailTemplateService->getOptions();
        $dataStructure = $event->getDataStructure();

        // Search for finishers and add items
        foreach ($dataStructure['sheets'] as $sheetIdentifier => $sheet) {
            foreach ($addToFinishers as $finisherIdentifier) {
                $fieldName = 'settings.finishers.' . $finisherIdentifier . '.emailTemplateUid';

                // Field name not found? Continue.
                if (!($dataStructure['sheets'][$sheetIdentifier]['ROOT']['el'][$fieldName] ?? false)) {
                    continue;
                }

                // @deprecated - this code block can be removed when support for TYPO3 v11 is dropped
                if (is_array($dataStructure['sheets'][$sheetIdentifier]['ROOT']['el'][$fieldName]['TCEforms']['config']['items'] ?? null)) {
                    $dataStructure['sheets'][$sheetIdentifier]['ROOT']['el'][$fieldName]['TCEforms']['config']['items'] =
                        array_merge(
                            $dataStructure['sheets'][$sheetIdentifier]['ROOT']['el'][$fieldName]['TCEforms']['config']['items'],
                            $options
                        );
                    continue;
                }

                // V12 - Add options to select and fix default value
                if (is_array($dataStructure['sheets'][$sheetIdentifier]['ROOT']['el'][$fieldName]['config']['items'] ?? null)) {
                    if ((int)($dataStructure['sheets'][$sheetIdentifier]['ROOT']['el'][$fieldName]['config']['default'] ?? 0) > 0) {
                        // Replacing the Page ID with the page Title in the field label
                        foreach ($options as $option) {
                            if ((int)($option['value'] ?? -1) !== (int)($dataStructure['sheets'][$sheetIdentifier]['ROOT']['el'][$fieldName]['config']['default'] ?? 0)) {
                                continue;
                            }
                            if (empty($option['label'])) {
                                continue;
                            }
                            $dataStructure['sheets'][$sheetIdentifier]['ROOT']['el'][$fieldName]['label'] =
                                str_replace(
                                    (string)$dataStructure['sheets'][$sheetIdentifier]['ROOT']['el'][$fieldName]['config']['default'],
                                    $option['label'],
                                    $dataStructure['sheets'][$sheetIdentifier]['ROOT']['el'][$fieldName]['label']
                                );
                            break;
                        }
                    }

                    $dataStructure['sheets'][$sheetIdentifier]['ROOT']['el'][$fieldName]['config']['items'] = $options;
                }
            }
        }

        $event->setDataStructure($dataStructure);
    }
}

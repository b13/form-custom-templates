<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates\Backend\EventListener;

use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Configuration\Event\AfterFlexFormDataStructureParsedEvent;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

final class FlexFormParsingModifyEventListener
{
    protected function getOptions(): array
    {
        $options = array_merge(
            [
                [
                    'title' => LocalizationUtility::translate(
                        'LLL:EXT:form_custom_templates/Resources/Private/Language/Database.xlf:form_custom_templates.select.default'
                    ),
                    'uid' => 'default'
                ],
            ],
            $this->getEmailTemplatePages()
        );
        array_walk($options, static function (&$item) {
            $item = ['label' => $item['title'], 'value' => $item['uid']];
        }, []);
        return $options;
    }

    protected function getEmailTemplatePages(): array
    {
        $doktype = (int)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('form_custom_templates', 'doktype');
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $queryBuilder->select('*')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq(
                    'doktype',
                    $queryBuilder->createNamedParameter(
                        $doktype,
                        ParameterType::INTEGER
                    )
                )
            );

        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }

    public function modifyDataStructure(AfterFlexFormDataStructureParsedEvent $event): void
    {
        $identifier = $event->getIdentifier();

        if (($identifier['ext-form-overrideFinishers'] ?? '') !== 'enabled') {
            return;
        }

        $addToFinishers = ['EmailToSender', 'EmailToReceiver'];
        $options = $this->getOptions();
        $dataStructure = $event->getDataStructure();

        if ($options === []) {
            return;
        }

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

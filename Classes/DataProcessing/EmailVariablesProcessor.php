<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates\DataProcessing;

use TYPO3\CMS\Core\Information\Typo3Information;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

class EmailVariablesProcessor implements DataProcessorInterface
{
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ) {
        $processedData['typo3'] = [
            'sitename' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'],
            'formats' => [
                'date' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'],
                'time' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'],
            ],
            'systemConfiguration' => $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS'],
            'information' => GeneralUtility::makeInstance(Typo3Information::class),
        ];
        $request = $cObj->getRequest();
        $processedData['normalizedParams'] = $request->getAttribute('normalizedParams');

        return $processedData;
    }
}

<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates\Service;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\Client\GuzzleClientFactory;
use TYPO3\CMS\Core\Service\MarkerBasedTemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

class EmailTemplateService
{
    public static function create(int $uid, FormRuntime $formRuntime, string $resultTable = '', int $type = 101): string
    {
        $markerService = GeneralUtility::makeInstance(MarkerBasedTemplateService::class);

        // @todo change plaintext type
        $uri = self::getUri($uid, $type);
        $factory = GuzzleClientFactory::getClient();

        $template = $factory->request('GET', $uri)->getBody();
        $templateContent = $markerService->substituteMarker($template->getContents(), '{formCustomTemplate.results}', $resultTable);

        // Replace fluid markers with given form values
        foreach ($formRuntime->getFormDefinition()->getElements() as $identifier => $element) {
            $value = $formRuntime->getElementValue($identifier);
            $templateContent = $markerService->substituteMarker($templateContent, '{' . $identifier . '}', $value);
        }

        return $templateContent;
    }

    public static function getOptions(): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $queryBuilder->select('*')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('doktype', 125)
            );

        $emailTemplatePages = $queryBuilder->execute()->fetchAllAssociative();

        $options = array_reduce($emailTemplatePages, static function($options, $item){
            $options[] = [$item['title'], $item['uid']];

            return $options;
        }, []);

        return $options;
    }

    protected static function getUri(int $pageId, int $type = 0): string
    {
        $typolinkConfiguration = [
            'parameter' => $pageId . ',' . $type,
            'forceAbsoluteUrl' => 1,
        ];

        return $GLOBALS['TSFE']->cObj->typoLink_URL($typolinkConfiguration);
    }
}

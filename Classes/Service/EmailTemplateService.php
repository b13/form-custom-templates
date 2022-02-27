<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates\Service;

use Psr\Http\Message\StreamInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\Client\GuzzleClientFactory;
use TYPO3\CMS\Core\Service\MarkerBasedTemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class EmailTemplateService
{
    public static function create(int $uid, FormRuntime $formRuntime, string $resultTable = ''): array
    {
        $markerService = GeneralUtility::makeInstance(MarkerBasedTemplateService::class);

        $htmlTemplate = self::getHtml($uid);
        $htmlContent = $markerService->substituteMarker($htmlTemplate->getContents(), '{formCustomTemplate.results}', $resultTable);

        $plaintextTemplate = self::getPlaintext($uid);
        $plaintextContent = $markerService->substituteMarker($plaintextTemplate->getContents(), '{formCustomTemplate.results}', $resultTable);

        // Replace fluid markers with given form values
        foreach ($formRuntime->getFormDefinition()->getElements() as $identifier => $element) {
            $value = $formRuntime->getElementValue($identifier);
            $htmlContent = $markerService->substituteMarker($htmlContent, '{' . $identifier . '}', $value);
            $plaintextContent = $markerService->substituteMarker($plaintextContent, '{' . $identifier . '}', $value);
        }

        return [
            'html' => $htmlContent,
            'plaintext' => $plaintextContent,
        ];
    }

    protected static function getHtml(int $pageId): StreamInterface
    {
        $uri = self::getUri($pageId, 0);
        $factory = GuzzleClientFactory::getClient();

        return $factory->request('GET', $uri)->getBody();
    }

    protected static function getPlaintext(int $pageId): StreamInterface
    {
        $uri = self::getUri($pageId, 99);
        $factory = GuzzleClientFactory::getClient();

        return $factory->request('GET', $uri)->getBody();
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

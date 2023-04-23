<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates\Service;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\Client\GuzzleClientFactory;
use TYPO3\CMS\Core\Service\MarkerBasedTemplateService;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

class EmailTemplateService
{
    public static function create(int $uid, FormRuntime $formRuntime, string $resultTable = '', int $type = 101): string
    {
        $markerService = GeneralUtility::makeInstance(MarkerBasedTemplateService::class);
        $uri = self::getUri($uid, $type);

        $guzzleFactory = GeneralUtility::makeInstance(GuzzleClientFactory::class);
        $template = $guzzleFactory->getClient()->request('GET', $uri)->getBody();
        $templateContent = $markerService->substituteMarker($template->getContents(), '{formCustomTemplate.results}', $resultTable);

        // Replace fluid markers with given form values
        foreach ($formRuntime->getFormDefinition()->getElements() as $identifier => $element) {
            $value = $formRuntime->getElementValue($identifier);
            if ($value === null) {
                $value = '';
            } elseif (is_array($value)) {
                $value = $value[0];
            }
            $templateContent = $markerService->substituteMarker($templateContent, '{' . $identifier . '}', $value);
        }

        return $templateContent;
    }

    public static function getOptions(): array
    {
        $options = array_reduce(self::getEmailTemplatePages(), static function ($options, $item) {
            $options[] = [$item['title'], $item['uid']];

            return $options;
        }, []);

        return $options;
    }

    public static function getEmailTemplatePages(): array
    {
        $doktype = (int)self::getTypoScript()['doktype'];
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $queryBuilder->select('*')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('doktype', $queryBuilder->createNamedParameter($doktype, \PDO::PARAM_INT))
            );

        return $queryBuilder->execute()->fetchAllAssociative();
    }

    protected static function getUri(int $pageId, int $type = 0): string
    {
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        $site = $siteFinder->getSiteByPageId($pageId);
        return (string)$site->getRouter()->generateUri($pageId, ['type' => $type]);
    }

    public static function getTypoScript(): array
    {
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
        $typoScript = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        return $typoScript['plugin.']['tx_form_custom_templates.'] ?? [];
    }
}

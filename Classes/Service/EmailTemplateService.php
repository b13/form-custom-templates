<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates\Service;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\Client\GuzzleClientFactory;
use TYPO3\CMS\Core\Service\MarkerBasedTemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

class EmailTemplateService
{
    public static function create(int $uid, FormRuntime $formRuntime, string $resultTable = '', int $type = 101): string
    {
        $markerService = GeneralUtility::makeInstance(MarkerBasedTemplateService::class);
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
        $options = array_reduce(self::getEmailTemplatePages() ?? [], static function ($options, $item) {
            $options[] = [$item['title'], $item['uid']];

            return $options;
        }, []);

        return $options;
    }

    public static function getEmailTemplatePages()
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
        $typolinkConfiguration = [
            'parameter' => $pageId . ',' . $type,
            'forceAbsoluteUrl' => 1,
        ];

        return $GLOBALS['TSFE']->cObj->typoLink_URL($typolinkConfiguration);
    }

    public static function getTypoScript(): array
    {
        // @todo: use makeInstance once v10 support was dropped
        //$configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
        $configurationManager = GeneralUtility::makeInstance(ObjectManager::class)
            ->get(ConfigurationManagerInterface::class);
        $typoScript = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        return $typoScript['plugin.']['tx_form_custom_templates.'] ?? [];
    }
}

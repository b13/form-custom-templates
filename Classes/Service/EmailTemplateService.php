<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates\Service;

use Html2Text\Html2Text;
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
        $htmlTemplate = self::getPageHtml($uid);
        $markerService = GeneralUtility::makeInstance(MarkerBasedTemplateService::class);
        $modContent = $markerService->substituteMarker($htmlTemplate->getContents(), '{formCustomTemplate.results}', $resultTable);

        // Replace fluid markers with given form values
        foreach ($formRuntime->getFormDefinition()->getElements() as $identifier => $element) {
            $value = $formRuntime->getElementValue($identifier);
            $modContent = $markerService->substituteMarker($modContent, '{' . $identifier . '}', $value);
        }

        $toText = GeneralUtility::makeInstance(Html2Text::class, $modContent);
        $plainText = $toText->getText();

        return [
            'html' => $modContent,
            'plaintext' => $plainText,
        ];
    }

    protected static function getPageHtml(int $pageId): StreamInterface
    {
        $typolinkConfiguration = [
            'parameter' => $pageId,
            'forceAbsoluteUrl' => 1,
        ];
        $uri = self::getTypoScriptFrontendController()->cObj->typoLink_URL($typolinkConfiguration);
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

    /**
     * @return TypoScriptFrontendController
     */
    protected static function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}

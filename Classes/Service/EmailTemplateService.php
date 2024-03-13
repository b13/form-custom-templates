<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates\Service;

use B13\FormCustomTemplates\Configuration;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Country\CountryProvider;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Service\MarkerBasedTemplateService;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Form\Domain\Model\FormElements\AbstractFormElement;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\CMS\Frontend\Http\Application;

class EmailTemplateService
{
    public const FORM_ELEMENT_TYPES_SELECTABLES = [
        'RadioButton',
        'SingleSelect',
        'CountrySelect',
        'MultiCheckbox',
        'MultiSelect',
    ];

    private Application $application;

    public function __construct(
        protected readonly SiteFinder $siteFinder,
        protected readonly MarkerBasedTemplateService $markerBasedTemplateService,
        protected readonly Configuration $configuration,
        protected readonly CountryProvider $countryProvider
    ) {
        $container = GeneralUtility::getContainer();
        $this->application = $container->get(Application::class);
    }

    public function create(int $uid, FormRuntime $formRuntime, string $resultTable = '', int $type = 101): string
    {
        $subResponse = $this->stashEnvironment(
            fn(): ResponseInterface => $this->sendSubRequest($uid, $type, $GLOBALS['TYPO3_REQUEST'])
        );
        $templateContent = $this->markerBasedTemplateService->substituteMarker(
            (string)$subResponse->getBody(),
            '{formCustomTemplate.results}',
            $resultTable
        );

        // Replace fluid markers with given form values
        /** @var AbstractFormElement $element */
        foreach ($formRuntime->getFormDefinition()->getElements() as $identifier => $element) {
            $value = $formRuntime->getElementValue($identifier);
            if ($value === null) {
                $value = '';
            } elseif (is_array($value)) {
                $value = implode(',', $value);
            }
            $templateContent = $this->markerBasedTemplateService->substituteMarker($templateContent, '{' . $identifier . '}', $value);

            if (in_array($element->getType(), self::FORM_ELEMENT_TYPES_SELECTABLES)) {
                switch ($element->getType()) {
                    case 'CountrySelect':
                        $additionalValue = LocalizationUtility::translate($this->countryProvider->getByAlpha2IsoCode($value)->getLocalizedNameLabel());
                        break;

                    case 'MultiCheckbox':
                    case 'MultiSelect':
                        $valuesArray = GeneralUtility::trimExplode(',', $value);
                        $additionalValue = [];
                        foreach ($valuesArray as $singleValue) {
                            $additionalValue[] = $element->getProperties()['options'][$singleValue];
                        }
                        $additionalValue = implode(',', $additionalValue);
                        break;

                    default:
                        $additionalValue = $element->getProperties()['options'][$value];
                }

                $templateContent = $this->markerBasedTemplateService->substituteMarker($templateContent, '{' . $identifier . '_labeled}', $additionalValue);
            }
        }

        return $templateContent;
    }

    protected function stashEnvironment(callable $fetcher): ResponseInterface
    {
        $parkedTsfe = $GLOBALS['TSFE'] ?? null;
        $GLOBALS['TSFE'] = null;

        $result = $fetcher();

        $GLOBALS['TSFE'] = $parkedTsfe;

        return $result;
    }

    protected function sendSubRequest(int $pageId, int $type, ServerRequestInterface $originalRequest): ResponseInterface
    {
        $request = $originalRequest->withQueryParams(['type' => $type])
            ->withUri(
                new Uri($this->getUri($pageId))
            )
            ->withMethod('GET');

        $site = $request->getAttribute('site', null);
        if (!$site instanceof Site) {
            $site = $this->siteFinder->getSiteByPageId($pageId);
            $request = $request->withAttribute('site', $site);
        }

        $request = $request->withAttribute('originalRequest', $originalRequest);

        return $this->application->handle($request);
    }

    public function getOptions(): array
    {
        $options = array_reduce($this->getEmailTemplatePages(), static function ($options, $item) {
            $options[] = [$item['title'], $item['uid']];
            return $options;
        }, []);

        return $options;
    }

    public function getEmailTemplatePages(): array
    {
        $doktype = $this->configuration->getDokType();
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $queryBuilder->select('*')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('doktype', $queryBuilder->createNamedParameter($doktype, \PDO::PARAM_INT))
            );

        return $queryBuilder->execute()->fetchAllAssociative();
    }

    protected function getUri(int $pageId): string
    {
        $site = $this->siteFinder->getSiteByPageId($pageId);
        return (string)$site->getRouter()->generateUri($pageId);
    }
}

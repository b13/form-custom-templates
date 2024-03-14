<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates\Service;

use B13\FormCustomTemplates\Configuration;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Service\MarkerBasedTemplateService;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\CMS\Frontend\Http\Application;

class EmailTemplateService
{
    private Application $application;

    public function __construct(
        protected readonly SiteFinder $siteFinder,
        protected readonly MarkerBasedTemplateService $markerBasedTemplateService,
        protected readonly Configuration $configuration
    ) {
        $container = GeneralUtility::getContainer();
        $this->application = $container->get(Application::class);
    }

    public function create(int $uid, FormRuntime $formRuntime, string $resultTable = '', int $type = 101): string
    {
        $subResponse = $this->stashEnvironment(
            fn (): ResponseInterface => $this->sendSubRequest($uid, $type, $GLOBALS['TYPO3_REQUEST'])
        );
        $templateContent = $this->markerBasedTemplateService->substituteMarker(
            (string)$subResponse->getBody(),
            '{formCustomTemplate.results}',
            $resultTable
        );

        // Replace fluid markers with given form values
        foreach ($formRuntime->getFormDefinition()->getElements() as $identifier => $element) {
            $value = $formRuntime->getElementValue($identifier);
            if ($value === null) {
                $value = '';
            } elseif (is_array($value)) {
                $value = $value[0];
            }
            $templateContent = $this->markerBasedTemplateService->substituteMarker($templateContent, '{' . $identifier . '}', $value);
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
        $typo3Version = (new Typo3Version())->getMajorVersion();
        $options = array_reduce($this->getEmailTemplatePages(), static function ($options, $item) use ($typo3Version) {
            if($typo3Version > 11) {
                $options[] = ['label' => $item['title'], 'value' => $item['uid']];
            } else {
                $options[] = [$item['title'], $item['uid']];
            }

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

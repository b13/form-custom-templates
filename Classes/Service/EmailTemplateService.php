<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates\Service;

use B13\FormCustomTemplates\Configuration;
use Doctrine\DBAL\ParameterType;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Service\MarkerBasedTemplateService;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
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
            fn(): ResponseInterface => $this->sendSubRequest($uid, $type, $GLOBALS['TYPO3_REQUEST'])
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
        $site = $this->siteFinder->getSiteByPageId($pageId);
        $request = $originalRequest->withQueryParams(['type' => $type])
            ->withUri(
                $site->getRouter()->generateUri($pageId)
            )
            ->withMethod('GET');

        $request = $request->withAttribute('site', $site);
        $request = $request->withAttribute('originalRequest', $originalRequest);

        return $this->application->handle($request);
    }

    public function getOptions(): array
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

    public function getEmailTemplatePages(): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $queryBuilder->select('*')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq(
                    'doktype',
                    $queryBuilder->createNamedParameter(
                        $this->configuration->getDokType(),
                        ParameterType::INTEGER
                    )
                )
            );

        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }
}

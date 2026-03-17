<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\ErrorController;
use TYPO3\CMS\Frontend\Page\PageAccessFailureReasons;
use TYPO3\CMS\Frontend\Page\PageInformation;

class EmailPagePreviewGuard implements MiddlewareInterface
{
    public function __construct(
        protected readonly Context $context,
        protected readonly ExtensionConfiguration $extensionConfiguration
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getAttribute('originalRequest') !== null) {
            return $handler->handle($request);
        }

        /** @var ?PageInformation $pageInformation */
        $pageInformation = $request->getAttribute('frontend.page.information');
        if (!$pageInformation instanceof PageInformation) {
            return $handler->handle($request);
        }

        $dokType = (int)$this->extensionConfiguration->get('form_custom_templates', 'doktype');
        if (($pageInformation->getPageRecord()['doktype'] ?? 0) !== $dokType) {
            return $handler->handle($request);
        }

        if ($this->context->getAspect('backend.user')->isLoggedIn()) {
            return $handler->handle($request);
        }

        return GeneralUtility::makeInstance(ErrorController::class)->pageNotFoundAction(
            $request,
            'The requested page does not exist',
            ['code' => PageAccessFailureReasons::PAGE_NOT_FOUND]
        );
    }
}

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
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageAccessFailureReasons;

class EmailPagePreviewGuard implements MiddlewareInterface
{
    public function __construct(
        protected readonly Context $context,
        protected readonly ExtensionConfiguration $extensionConfiguration
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getAttribute('originalRequest') !== null) {
            return $handler->handle($request);
        }

        $frontendController = $request->getAttribute('frontend.controller');
        if (!$frontendController instanceof TypoScriptFrontendController) {
            return $handler->handle($request);
        }

        $dokType = (int)$this->extensionConfiguration->get('form_custom_templates', 'doktype');
        if (($frontendController->page['doktype'] ?? 0) !== $dokType) {
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

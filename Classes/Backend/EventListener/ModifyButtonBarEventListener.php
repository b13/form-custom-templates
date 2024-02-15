<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates\Backend\EventListener;

use B13\FormCustomTemplates\Configuration;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\ModifyButtonBarEvent;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class ModifyButtonBarEventListener
{
    public function __construct(private readonly PageRepository $pageRepository, private readonly Configuration $configuration)
    {
    }

    public function __invoke(ModifyButtonBarEvent $event): void
    {
        // @todo: remove when v11 support was dropped
        if (!class_exists(ModifyButtonBarEvent::class)) {
            return;
        }

        $buttons = $event->getButtons();
        $request = $this->getRequest();
        $pageId = $request->getQueryParams()['id'] ?? 0;
        $page = $this->pageRepository->getPage($pageId);
        if (empty($page)) {
            return;
        }

        if ((int)($page['doktype'] ?? 0) === $this->configuration->getDokType()) {
            $plaintextTypeNum = $this->configuration->getTypeNum();
            $buttonBar = GeneralUtility::makeInstance(ButtonBar::class);
            $iconFactory = GeneralUtility::makeInstance(IconFactory::class);

            try {
                $previewDataAttributes = BackendUtility::getPreviewUrl($pageId, '', BackendUtility::BEgetRootLine($pageId), '', '', 'type=' . $plaintextTypeNum);
                $viewButton = $buttonBar->makeLinkButton()
                    ->setTitle($this->getLanguageService()->sL('LLL:EXT:form_custom_templates/Resources/Private/Language/Database.xlf:form_custom_templates.buttonBar.showPagePlaintext'))
                    ->setShowLabelText(true)
                    ->setDataAttributes(['dispatch-action' => 'TYPO3.WindowManager.localOpen', 'dispatch-args' => GeneralUtility::jsonEncodeForHtmlAttribute([
                        $previewDataAttributes,
                        true, // Focus new window
                    ])])
                    ->setIcon($iconFactory->getIcon('actions-file-view', Icon::SIZE_SMALL))
                    ->setHref('#');

                $buttons[ButtonBar::BUTTON_POSITION_LEFT][3][] = $viewButton;
            } catch (Exception $exception) {
                // Do not add preview in case no site exists
            }
        }

        $event->setButtons($buttons);
    }

    protected function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}

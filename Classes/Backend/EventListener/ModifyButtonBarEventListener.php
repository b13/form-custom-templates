<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates\Backend\EventListener;

use B13\FormCustomTemplates\Configuration;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\PreviewUriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\ComponentFactory;
use TYPO3\CMS\Backend\Template\Components\ModifyButtonBarEvent;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[AsEventListener(identifier: 'tx_form_custom_templates/modifyButtonBarEventListener')]
final class ModifyButtonBarEventListener
{
    public function __construct(private readonly PageRepository $pageRepository, private readonly Configuration $configuration, private readonly ComponentFactory $componentFactory) {}

    public function __invoke(ModifyButtonBarEvent $event): void
    {
        $buttons = $event->getButtons();
        $request = $this->getRequest();
        $pageId = (int)($request->getQueryParams()['id'] ?? 0);
        $page = $this->pageRepository->getPage($pageId);
        if (empty($page)) {
            return;
        }

        if ((int)($page['doktype'] ?? 0) === $this->configuration->getDokType()) {
            $plaintextTypeNum = $this->configuration->getTypeNum();
            $buttonBar = GeneralUtility::makeInstance(ButtonBar::class);
            $iconFactory = GeneralUtility::makeInstance(IconFactory::class);

            try {
                $previewUriBuilder = PreviewUriBuilder::create($pageId);
                $previewDataAttributes = (string)$previewUriBuilder->withRootLine(BackendUtility::BEgetRootLine($pageId))->withAdditionalQueryParameters(['type' => $plaintextTypeNum])->buildUri();
                $viewButton = $this->componentFactory->createLinkButton();
                $viewButton->setTitle($this->getLanguageService()->sL('LLL:EXT:form_custom_templates/Resources/Private/Language/Database.xlf:form_custom_templates.buttonBar.showPagePlaintext'))
                    ->setShowLabelText(true)
                    ->setDataAttributes(['dispatch-action' => 'TYPO3.WindowManager.localOpen', 'dispatch-args' => GeneralUtility::jsonEncodeForHtmlAttribute([
                        $previewDataAttributes,
                        true, // Focus new window
                    ])])
                    ->setIcon($iconFactory->getIcon('actions-file-view', IconSize::SMALL))
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

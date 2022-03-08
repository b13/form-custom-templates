<?php

namespace B13\FormCustomTemplates\Hooks;

use B13\FormCustomTemplates\Service\EmailTemplateService;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PlaintextPreviewHook
{
    /**
     * @param array $params
     * @param object|null $ref
     * @return array
     */
    public function previewButton(array $params, ?object $ref)
    {
        $buttons = $params['buttons'];
        $pageId = GeneralUtility::_GET('edit') ? array_search('edit', GeneralUtility::_GET('edit')['pages'] ?? []) : GeneralUtility::_GET('id');

        if (!$pageId) {
            return $buttons;
        }

        $page = GeneralUtility::makeInstance(PageRepository::class)->getPage($pageId);

        if ((int)$page['doktype'] === (int)EmailTemplateService::getTypoScript()['doktype']) {
            $plaintextTypeNum = (int)EmailTemplateService::getTypoScript()['typeNum'];
            $buttonBar = GeneralUtility::makeInstance(ButtonBar::class);
            $iconFactory = GeneralUtility::makeInstance(IconFactory::class);

            $previewDataAttributes = BackendUtility::getPreviewUrl($pageId, '', BackendUtility::BEgetRootLine($pageId), '', '', 'type=' . $plaintextTypeNum);
            $viewButton = $buttonBar->makeLinkButton()
                ->setOnClick('window.open(' . GeneralUtility::quoteJSvalue($previewDataAttributes) . ',\'newTYPO3frontendWindow\');)')
                ->setTitle($this->getLanguageService()->sL('LLL:EXT:form_custom_templates/Resources/Private/Language/Database.xlf:form_custom_templates.buttonBar.showPagePlaintext'))
                ->setShowLabelText(true)
                ->setIcon($iconFactory->getIcon('actions-file-view', Icon::SIZE_SMALL))
                ->setHref('#');

            $buttons[ButtonBar::BUTTON_POSITION_LEFT][3][] = $viewButton;
        }

        return $buttons;
    }

    /**
     * Shorthand functionality for fetching the language service
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}

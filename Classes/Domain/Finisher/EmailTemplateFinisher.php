<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates\Domain\Finisher;

use B13\FormCustomTemplates\Service\EmailTemplateService;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Form\Domain\Finishers\EmailFinisher;
use TYPO3\CMS\Form\Domain\Finishers\Exception\FinisherException;
use TYPO3\CMS\Form\Domain\Model\FormElements\FileUpload;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\CMS\Form\Service\TranslationService;
use TYPO3\CMS\Form\ViewHelpers\RenderRenderableViewHelper;

class EmailTemplateFinisher extends EmailFinisher
{
    protected function executeInternal()
    {
        $emailTemplateUid = $this->options['emailTemplateUid'];
        // For v10 compatibility reasons we check for [Empty] value
        if (empty($emailTemplateUid) || $emailTemplateUid === '[Empty]') {
            parent::executeInternal();
            return;
        }

        // Fallback to default in case doktype changed and the selected page
        // is no longer an email template
        $page = GeneralUtility::makeInstance(PageRepository::class)->getPage($emailTemplateUid);
        if ((int)$page['doktype'] !== (int)EmailTemplateService::getTypoScript()['doktype']) {
            parent::executeInternal();
        }

        $languageBackup = null;
        // Flexform overrides write strings instead of integers so
        // we need to cast the string '0' to false.
        if (
            isset($this->options['addHtmlPart'])
            && $this->options['addHtmlPart'] === '0'
        ) {
            $this->options['addHtmlPart'] = false;
        }

        $subject = $this->parseOption('subject');
        $recipients = $this->getRecipientsForTemplate('recipients');
        $senderAddress = $this->parseOption('senderAddress');
        $senderAddress = is_string($senderAddress) ? $senderAddress : '';
        $senderName = $this->parseOption('senderName');
        $senderName = is_string($senderName) ? $senderName : '';
        $replyToRecipients = $this->getRecipientsForTemplate('replyToRecipients');
        $carbonCopyRecipients = $this->getRecipientsForTemplate('carbonCopyRecipients');
        $blindCarbonCopyRecipients = $this->getRecipientsForTemplate('blindCarbonCopyRecipients');
        $attachUploads = $this->parseOption('attachUploads');
        $addHtmlPart = $this->parseOption('addHtmlPart') ? true : false;
        $title = $this->parseOption('title');
        $title = is_string($title) && $title !== '' ? $title : $subject;

        if (empty($subject)) {
            throw new FinisherException('The option "subject" must be set for the EmailFinisher.', 1327060320);
        }
        if (empty($recipients)) {
            throw new FinisherException('The option "recipients" must be set for the EmailFinisher.', 1327060200);
        }
        if (empty($senderAddress)) {
            throw new FinisherException('The option "senderAddress" must be set for the EmailFinisher.', 1327060210);
        }

        $formRuntime = $this->finisherContext->getFormRuntime();

        $translationService = GeneralUtility::makeInstance(TranslationService::class);
        if (is_string($this->options['translation']['language'] ?? null) && $this->options['translation']['language'] !== '') {
            $languageBackup = $translationService->getLanguage();
            $translationService->setLanguage($this->options['translation']['language']);
        }

        $mail = GeneralUtility::makeInstance(MailMessage::class);

        $mail
            ->from(new Address($senderAddress, $senderName))
            ->to(...$recipients)
            ->subject($subject);

        if (!empty($replyToRecipients)) {
            $mail->replyTo(...$replyToRecipients);
        }

        if (!empty($carbonCopyRecipients)) {
            $mail->cc(...$carbonCopyRecipients);
        }

        if (!empty($blindCarbonCopyRecipients)) {
            $mail->bcc(...$blindCarbonCopyRecipients);
        }

        $plaintextTypeNum = (int)EmailTemplateService::getTypoScript()['typeNum'];
        $parts = [
            [
                'format' => 'Plaintext',
                'contentType' => 'text/plain',
                'content' => EmailTemplateService::create((int)$emailTemplateUid, $formRuntime, $this->getStandaloneView($title, $formRuntime, 'txt')->render(), $plaintextTypeNum)
            ],
        ];

        if ($addHtmlPart) {
            $parts[] = [
                'format' => 'Html',
                'contentType' => 'text/html',
                'content' => EmailTemplateService::create((int)$emailTemplateUid, $formRuntime, $this->getStandaloneView($title, $formRuntime, 'html')->render(), 0)
            ];
        }

        foreach ($parts as $part) {
            if ($part['contentType'] === 'text/plain') {
                $mail->text($part['content']);
            } else {
                $mail->html($part['content']);
            }
        }

        if (!empty($languageBackup)) {
            $translationService->setLanguage($languageBackup);
        }

        if ($attachUploads) {
            foreach ($formRuntime->getFormDefinition()->getRenderablesRecursively() as $element) {
                if (!$element instanceof FileUpload) {
                    continue;
                }
                $file = $formRuntime[$element->getIdentifier()];
                if ($file) {
                    if ($file instanceof FileReference) {
                        $file = $file->getOriginalResource();
                    }
                    $mail->attach($file->getContents(), $file->getName(), $file->getMimeType());
                }
            }
        }

        $mail->send();

        return null;
    }

    protected function getStandaloneView(string $title, FormRuntime $formRuntime, string $format = 'txt'): StandaloneView
    {
        $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
        $templatePathAndFilename = EmailTemplateService::getTypoScript()['resultList.']['templatePath'];

        $standaloneView->setTemplatePathAndFilename($templatePathAndFilename . '.' . $format);
        $standaloneView->assign('title', $title);
        $standaloneView->assign('finisherVariableProvider', $this->finisherContext->getFinisherVariableProvider());

        $standaloneView->assign('form', $formRuntime);
        $standaloneView->getRenderingContext()
            ->getViewHelperVariableContainer()
            ->addOrUpdate(RenderRenderableViewHelper::class, 'formRuntime', $formRuntime);
        return $standaloneView;
    }

    /**
     * Get recipients
     * Using this for compatibility between v10 and v11
     * @todo: use getRecipients() once v10 support was dropped
     * @param string $listOption List option name
     * @return array
     */
    protected function getRecipientsForTemplate(string $listOption): array
    {
        $recipients = $this->parseOption($listOption) ?? [];
        $addresses = [];
        foreach ($recipients as $address => $name) {
            if (!GeneralUtility::validEmail($address)) {
                // Drop entries without valid address
                continue;
            }
            $addresses[] = new Address($address, $name);
        }
        return $addresses;
    }
}

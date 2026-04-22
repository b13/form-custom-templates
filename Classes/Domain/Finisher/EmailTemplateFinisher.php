<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates\Domain\Finisher;

use B13\FormCustomTemplates\Configuration;
use B13\FormCustomTemplates\Service\EmailTemplateService;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Mail\TemplatedEmailFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface as ExtbaseConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Fluid\View\FluidViewAdapter;
use TYPO3\CMS\Form\Domain\Finishers\EmailFinisher;
use TYPO3\CMS\Form\Domain\Finishers\Exception\FinisherException;
use TYPO3\CMS\Form\Domain\Model\FormElements\FileUpload;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\CMS\Form\Mvc\Configuration\ConfigurationManagerInterface as ExtFormConfigurationManagerInterface;
use TYPO3\CMS\Form\Mvc\Persistence\FormPersistenceManager;
use TYPO3\CMS\Form\ViewHelpers\RenderRenderableViewHelper;

class EmailTemplateFinisher extends EmailFinisher
{
    public function __construct(
        protected readonly EmailTemplateService $emailTemplateService,
        protected readonly Configuration $configuration,
        protected readonly FormPersistenceManager $formPersistenceManager,
        protected ExtFormConfigurationManagerInterface $extFormConfigurationManager,
        protected ExtbaseConfigurationManagerInterface $extbaseConfigurationManager,
        protected readonly ViewFactoryInterface $viewFactory,
        EventDispatcherInterface $eventDispatcher,
        TemplatedEmailFactory $templatedEmailFactory,
        MailerInterface $mailer
    ) {
        parent::__construct($eventDispatcher, $templatedEmailFactory, $mailer);
    }

    protected function executeInternal(): void
    {
        $emailTemplateUid = $this->options['emailTemplateUid'] ?? null;

        // For v10 compatibility reasons we check for [Empty] value
        if (empty($emailTemplateUid) || $emailTemplateUid === '[Empty]') {
            parent::executeInternal();
            return;
        }

        // In case the override is explicitly set to "default" we need to
        // check the default form definition for the email template uid
        if ($emailTemplateUid === 'default') {
            $this->extbaseConfigurationManager->setRequest($this->finisherContext->getRequest());
            $typoScriptSettings = $this->extbaseConfigurationManager->getConfiguration(ExtbaseConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, 'form');
            $formSettings = $this->extFormConfigurationManager->getYamlConfiguration($typoScriptSettings, true);
            $defaultFormDefinition = $this->formPersistenceManager->load(
                $this->finisherContext->getFormRuntime()->getFormDefinition()->getPersistenceIdentifier(),
                $formSettings,
                $this->finisherContext->getRequest()
            );
            foreach ($defaultFormDefinition['finishers'] ?? [] as $finisher) {
                if ($finisher['identifier'] !== 'EmailToReceiver') {
                    continue;
                }
                if (isset($finisher['options']['emailTemplateUid'])) {
                    $emailTemplateUid = $finisher['options']['emailTemplateUid'];
                    break;
                }
            }
        }

        if (!MathUtility::canBeInterpretedAsInteger($emailTemplateUid)) {
            parent::executeInternal();
            return;
        }

        // Fallback to default in case doktype changed and the selected page
        // is no longer an email template
        $page = GeneralUtility::makeInstance(PageRepository::class)->getPage((int)$emailTemplateUid);
        if ((int)$page['doktype'] !== $this->configuration->getDokType()) {
            parent::executeInternal();
            return;
        }

        // Flexform overrides write strings instead of integers, so
        // we need to cast the string '0' to false.
        if (
            isset($this->options['addHtmlPart'])
            && $this->options['addHtmlPart'] === '0'
        ) {
            $this->options['addHtmlPart'] = false;
        }

        $subject = (string)$this->parseOption('subject');
        $recipients = $this->getRecipients('recipients');
        $senderAddress = $this->parseOption('senderAddress');
        $senderAddress = is_string($senderAddress) ? $senderAddress : '';
        $senderName = $this->parseOption('senderName');
        $senderName = is_string($senderName) ? $senderName : '';
        $replyToRecipients = $this->getRecipients('replyToRecipients');
        $carbonCopyRecipients = $this->getRecipients('carbonCopyRecipients');
        $blindCarbonCopyRecipients = $this->getRecipients('blindCarbonCopyRecipients');
        $addHtmlPart = $this->parseOption('addHtmlPart') ? true : false;
        $attachUploads = $this->parseOption('attachUploads');
        $title = (string)$this->parseOption('title') ?: $subject;

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

        $plaintextTypeNum = $this->configuration->getTypeNum();
        $parts = [
            [
                'format' => 'Plaintext',
                'contentType' => 'text/plain',
                'content' => $this->emailTemplateService->create((int)$emailTemplateUid, $formRuntime, $this->getView($title, $formRuntime, 'txt')->render(), $plaintextTypeNum),
            ],
        ];

        if ($addHtmlPart) {
            $parts[] = [
                'format' => 'Html',
                'contentType' => 'text/html',
                'content' => $this->emailTemplateService->create((int)$emailTemplateUid, $formRuntime, $this->getView($title, $formRuntime, 'html')->render(), 0),
            ];
        }

        foreach ($parts as $part) {
            if ($part['contentType'] === 'text/plain') {
                $mail->text($part['content']);
            } else {
                $mail->html($part['content']);
            }
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
        $this->mailer->send($mail);
    }

    protected function getView(string $title, FormRuntime $formRuntime, string $format = 'txt'): ViewInterface
    {
        $request = $formRuntime->getRequest();
        $templatePathAndFilename = $this->configuration->getTemplatePath();
        $view = $this->viewFactory->create(new ViewFactoryData(
            templatePathAndFilename: $templatePathAndFilename . '.' . $format,
            request: $request
        ));
        if ($view instanceof FluidViewAdapter) {
            $view->getRenderingContext()
                ->getViewHelperVariableContainer()
                ->addOrUpdate(RenderRenderableViewHelper::class, 'formRuntime', $formRuntime);
        }
        $view->assign('title', $title);
        $view->assign('finisherVariableProvider', $this->finisherContext->getFinisherVariableProvider());

        $view->assign('form', $formRuntime);
        return $view;
    }
}

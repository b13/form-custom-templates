<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class Configuration implements SingletonInterface
{
    public const DEFAULT_DOKTYPE = 125;
    public const DEFAULT_PAGE_TYPE = 101;
    private array $typoScript = [];

    public function __construct(protected readonly ConfigurationManagerInterface $configurationManager)
    {
        $this->typoScript = $this->getTypoScript();
    }

    protected function getServerRequest(): ?ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'] ?? null;
    }

    protected function getTypoScript(): array
    {
        if ((GeneralUtility::makeInstance(Typo3Version::class))->getVersion() > 12) {
            $request = $this->getServerRequest();
            if ($request === null) {
                return [];
            }
            $this->configurationManager->setRequest($request);
        }
        $typoScript = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        return $typoScript['plugin.']['tx_form_custom_templates.'] ?? [];
    }

    public function getDokType(): int
    {
        return (int)($this->typoScript['doktype'] ?? self::DEFAULT_DOKTYPE);
    }

    public function getTypeNum(): int
    {
        return (int)($this->typoScript['typeNum'] ?? self::DEFAULT_PAGE_TYPE);
    }

    public function getTemplatePath(): string
    {
        return $this->typoScript['resultList.']['templatePath'] ?? '';
    }
}

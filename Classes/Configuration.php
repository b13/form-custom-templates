<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class Configuration implements SingletonInterface
{
    const DEFAULT_DOKTYPE = 125;
    const DEFAULT_PAGE_TYPE = 101;

    private array $typoScript = [];

    public function __construct(protected readonly ConfigurationManagerInterface $configurationManager)
    {
        $this->typoScript = $this->getTypoScript();
    }

    protected function getTypoScript(): array
    {
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

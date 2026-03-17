<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;

class Configuration
{
    public const DEFAULT_DOKTYPE = 125;
    public const DEFAULT_PAGE_TYPE = 101;
    private array $typoScript = [];

    public function __construct()
    {
        $this->typoScript = $this->getTypoScript();
    }

    protected function getServerRequest(): ?ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'] ?? null;
    }

    protected function getTypoScript(): array
    {
        $request = $this->getServerRequest();
        if ($request === null) {
            return [];
        }

        /** @var FrontendTypoScript $typoScript */
        $typoScript = $request->getAttribute('frontend.typoscript');
        if ($typoScript !== null) {
            $setup = $typoScript->getSetupArray();
        }
        return $setup['plugin.']['tx_form_custom_templates.'] ?? [];
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

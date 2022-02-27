<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates\ViewHelpers;

use League\HTMLToMarkdown\Converter\TableConverter;
use League\HTMLToMarkdown\HtmlConverter;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Retrieve all pages of 'doktype' 125 (Email)
 */
class PlaintextViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @param array $arguments
     * @param \Closure $childClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $childClosure, RenderingContextInterface $renderingContext)
    {
        $converter = GeneralUtility::makeInstance(HtmlConverter::class);
        $converter->getEnvironment()->addConverter(new TableConverter());
        $converter->getConfig()->setOption('strip_tags', 'div');

        return $converter->convert((string)$childClosure());
    }
}
<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates\ViewHelpers;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class EmailTemplateViewHelper extends AbstractViewHelper
{
    public function render(): array
    {
        $queryBuilder = $this->getQueryBuilderForTable('pages');
        $queryBuilder->select('*')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('doktype', 125)
            );

        $emailTemplatePages = $queryBuilder->execute()->fetchAllAssociative();

        return array_reduce($emailTemplatePages, static function($options, $item){
            $index = $item['uid'];
            $options[$index] = $item['title'];

            return $options;
        }, []);
    }

    protected function getQueryBuilderForTable(string $table): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
    }
}
<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates\ViewHelpers;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Retrieve all pages of 'doktype' 125 (Email)
 */
class EmailTemplateViewHelper extends AbstractViewHelper
{
    public function render()
    {
        $queryBuilder = $this->getQueryBuilderForTable('pages');
        $queryBuilder->select('*')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('doktype', 125)
            );

        $emailTemplatePages = $queryBuilder->execute()->fetchAllAssociative();

        $options = array_reduce($emailTemplatePages, static function($options, $item){
            $index = $item['uid'];
            $options[$index] = $item['title'];

            return $options;
        }, []);

        return $options;
    }

    protected function getQueryBuilderForTable(string $table): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
    }
}
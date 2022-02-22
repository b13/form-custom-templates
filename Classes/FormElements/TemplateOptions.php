<?php
namespace B13\FormCustomTemplates\FormElements;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime\Lifecycle\AfterFormStateInitializedInterface;

class TemplateOptions extends AbstractFormElement
{

    protected $valueField = '';

    protected $labelField = '';

    /**
     * @param \TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface $renderable
     * @return void
     */
    public function initializeFormElement(\TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface $renderable)
    {
        if ($renderable->getUniqueIdentifier() === 'contactForm-text-1') {
            $renderable->setDefaultValue('foo');
        }
    }

    public function setProperty(string $key, $value)
    {
        // see form element config for the static country column to be used for the select option value
        if ($key === 'valueField') {
            $this->valueField = $value;
        }
        // see form element config for the static country column to be used for the select option label
        if ($key === 'labelField') {
            $this->labelField = $value;
            $this->setProperty('options', $this->getOptions());
            return;
        }
        parent::setProperty($key, $value);
    }

    protected function getOptions() : array
    {
        //$options = [];
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

        asort($options);
        return $options;
    }

    protected function getQueryBuilderForTable(string $table): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
    }

    public function render()
    {
        DebuggerUtility::var_dump('dasdasd');
        // TODO: Implement render() method.
    }
}
<?php

namespace Spryker\Zed\PropelOrm\Business\Builder;

use Propel\Generator\Model\Column;

trait CommonQueryBuilderTrait
{
    protected abstract function executeAddFilterByCol(string &$script, Column $col): void;
    protected abstract function executeAddClassBody(string &$script): void;
    protected abstract function executeAddFindPk(string &$script): void;
    protected abstract function executeAddFindPks(string &$script): void;
}

if (class_exists('Propel\Runtime\ActiveQuery\Criterion\ExistsQueryCriterion')) {
    trait QueryBuilderTrait
    {
        use CommonQueryBuilderTrait;

        protected function addFilterByCol(string &$script, Column $col): void
        {
            $this->executeAddFilterByCol($script, $col);
        }
        protected function addClassBody(string &$script): void
        {
            $this->executeAddClassBody($script);
        }
        protected function addFindPk(string &$script): void
        {
            $this->executeAddFindPk($script);
        }
        protected function addFindPks(string &$script): void
        {
            $this->executeAddFindPks($script);
        }
    }

} else {
    trait QueryBuilderTrait
    {
        use CommonQueryBuilderTrait;

        protected function addFilterByCol(&$script, Column $col)
        {
            $this->executeAddFilterByCol($script, $col);
        }
        protected function addClassBody(&$script)
        {
            $this->executeAddClassBody($script);
        }
        protected function addFindPk(&$script)
        {
            $this->executeAddFindPk($script);
        }
        protected function addFindPks(&$script)
        {
            $this->executeAddFindPks($script);
        }
    }
}

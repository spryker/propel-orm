<?php

namespace Spryker\Zed\PropelOrm\Business\Builder;

use Propel\Generator\Model\Column;

trait CommonObjectBuilderWithLoggerTrait
{
    protected abstract function executeAddBooleanMutator(string &$script, Column $col): void;

    protected abstract function executeAddDefaultMutator(string &$script, Column $col): void;

    protected abstract function executeAddClassBody(string &$script): void;

    protected abstract function executeAddHookMethods(string &$script): void;

    protected abstract function executeAddSaveClose(string &$script): void;

    protected abstract function executeAddDeleteClose(string &$script): void;
}

if (class_exists('Propel\Runtime\ActiveQuery\Criterion\ExistsQueryCriterion')) {
    trait ObjectBuilderWithLoggerTrait
    {
        use CommonObjectBuilderWithLoggerTrait;

        public function addBooleanMutator(string &$script, Column $col): void
        {
            $this->executeAddBooleanMutator($script, $col);
        }

        protected function addDefaultMutator(string &$script, Column $col): void
        {
            $this->executeAddDefaultMutator($script, $col);
        }

        protected function addClassBody(string &$script): void
        {
            $this->executeAddClassBody($script);
        }


        protected function addHookMethods(string &$script): void
        {
            $this->executeAddHookMethods($script);
        }

        protected function executeAddSaveClose(string &$script): void
        {
            $this->executeAddSaveClose($script);
        }

        protected function addDeleteClose(string &$script): void
        {
            $this->executeAddDeleteClose($script);
        }
    }

} else {
    trait ObjectBuilderWithLoggerTrait
    {
        use CommonObjectBuilderWithLoggerTrait;

        protected function addBooleanMutator(&$script, Column $col)
        {
            return $this->executeAddBooleanMutator($script, $col);
        }

        protected function addDefaultMutator(&$script, Column $col)
        {
            $this->executeAddDefaultMutator($script, $col);
        }

        protected function addClassBody(&$script)
        {
            $this->executeAddClassBody($script);
        }


        protected function addHookMethods(&$script)
        {
            $this->executeAddHookMethods($script);
        }

        protected function executeAddSaveClose(&$script)
        {
            $this->executeAddSaveClose($script);
        }

        protected function addDeleteClose(&$script)
        {
            $this->executeAddDeleteClose($script);
        }
    }
}

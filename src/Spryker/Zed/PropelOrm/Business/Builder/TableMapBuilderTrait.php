<?php

namespace Spryker\Zed\PropelOrm\Business\Builder;

use Propel\Generator\Model\Column;

trait TableMapBuilderTrait
{
    public abstract function executeBuildObjectInstanceCreationCode(string $objName, string $clsName): string;
    protected abstract function executeAddPopulateObject(string &$script): void;
}

if (class_exists('Propel\Runtime\ActiveQuery\Criterion\ExistsQueryCriterion')) {
    trait QueryBuilderTrait
    {
        use TableMapBuilderTrait;

        public function buildObjectInstanceCreationCode(string $objName, string $clsName): string
        {
            return $this->executeBuildObjectInstanceCreationCode($objName, $clsName);
        }

        protected function addPopulateObject(string &$script): void
        {
            $this->executeAddPopulateObject($script);
        }
    }

} else {
    trait QueryBuilderTrait
    {
        use TableMapBuilderTrait;


        public function buildObjectInstanceCreationCode($objName,$clsName)
        {
            return $this->executeBuildObjectInstanceCreationCode($objName, $clsName);
        }

        protected function addPopulateObject(&$script)
        {
            $this->executeAddPopulateObject($script);
        }
    }
}

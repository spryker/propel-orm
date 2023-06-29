<?php

namespace Spryker\Zed\PropelOrm\Business\Builder;

use Propel\Generator\Model\Column;

trait CommonExtensionQueryBuilderTrait
{
    protected abstract function executeGetUseStatements(?string $ignoredNamespace = null): string;
}

if (class_exists('Propel\Runtime\ActiveQuery\Criterion\ExistsQueryCriterion')) {
    trait ExtensionQueryBuilderTrait
    {
        use CommonExtensionQueryBuilderTrait;

        public function getUseStatements(?string $ignoredNamespace = null): string
        {
            $this->executeGetUseStatements($ignoredNamespace);
        }
    }

} else {
    trait ExtensionQueryBuilderTrait
    {
        use CommonExtensionQueryBuilderTrait;

        public function getUseStatements($ignoredNamespace = null)
        {
            $this->executeGetUseStatements($ignoredNamespace);
        }
    }
}

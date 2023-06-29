<?php

namespace Spryker\Zed\PropelOrm\Business\Builder;

use Propel\Generator\Model\Column;

trait CommonExtensionObjectBuilderTrait
{
    protected abstract function executeGetUseStatements(?string $ignoredNamespace = null): string;
}

if (class_exists('Propel\Runtime\ActiveQuery\Criterion\ExistsQueryCriterion')) {
    trait ExtensionObjectBuilderTrait
    {
        use CommonExtensionObjectBuilderTrait;

        public function getUseStatements(?string $ignoredNamespace = null): string
        {
            $this->executeGetUseStatements($ignoredNamespace);
        }
    }

} else {
    trait ExtensionObjectBuilderTrait
    {
        use CommonExtensionObjectBuilderTrait;

        public function getUseStatements($ignoredNamespace = null)
        {
            $this->executeGetUseStatements($ignoredNamespace);
        }
    }
}

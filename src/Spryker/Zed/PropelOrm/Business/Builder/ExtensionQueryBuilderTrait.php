<?php

/**
 * This file is part of the Propel package - modified by Spryker Systems GmbH.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with the source code of the extended class.
 *
 * @license MIT License
 * @see https://github.com/propelorm/Propel2
 */

namespace Spryker\Zed\PropelOrm\Business\Builder;

/**
 * @deprecated Will be removed without replacement. Exists only for BC reasons.
 */
trait CommonExtensionQueryBuilderTrait
{
    protected abstract function executeGetUseStatements(?string $ignoredNamespace = null): string;
}

if (class_exists('Propel\Runtime\ActiveQuery\Criterion\ExistsQueryCriterion')) {
    /**
     * @deprecated Will be removed without replacement. Exists only for BC reasons.
     */
    trait ExtensionQueryBuilderTrait
    {
        use CommonExtensionQueryBuilderTrait;

        /**
         * @param string|null $ignoredNamespace
         *
         * @return string
         */
        public function getUseStatements(?string $ignoredNamespace = null): string
        {
            return $this->executeGetUseStatements($ignoredNamespace);
        }
    }
} else {
    /**
     * @deprecated Will be removed without replacement. Exists only for BC reasons.
     */
    trait ExtensionQueryBuilderTrait
    {
        use CommonExtensionQueryBuilderTrait;

        /**
         * @param $ignoredNamespace
         *
         * @return void
         */
        public function getUseStatements($ignoredNamespace = null)
        {
            $this->executeGetUseStatements($ignoredNamespace);
        }
    }
}

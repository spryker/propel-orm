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
 * @deprecated Will be removed in the next major. Exists only for "propel/propel": "2.0.0-beta1" version support.
 */
trait ExtensionQueryBuilderTraitCommon
{
    protected abstract function executeGetUseStatements(?string $ignoredNamespace = null): string;
}

// propel/propel > 2.0.0-beta1
if (trait_exists('Propel\Common\Util\PathTrait')) {
    /**
     * @deprecated Will be removed in the next major. Methods will be moved to the class that uses them.
     */
    trait ExtensionQueryBuilderTrait
    {
        use ExtensionQueryBuilderTraitCommon;

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
        use ExtensionQueryBuilderTraitCommon;

        /**
         * @param $ignoredNamespace
         *
         * @return string
         */
        public function getUseStatements($ignoredNamespace = null)
        {
            return $this->executeGetUseStatements($ignoredNamespace);
        }
    }
}

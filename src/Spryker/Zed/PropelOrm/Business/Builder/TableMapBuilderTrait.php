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
trait TableMapBuilderTraitCommon
{
    /**
     * @param string $objName
     * @param string $clsName
     *
     * @return string
     */
    public abstract function executeBuildObjectInstanceCreationCode(string $objName, string $clsName): string;

    /**
     * @param string $script
     *
     * @return void
     */
    protected abstract function executeAddPopulateObject(string &$script): void;
}

// propel/propel > 2.0.0-beta1
if (class_exists('Propel\Common\Util\PathTrait')) {
    /**
     * @deprecated Will be removed in the next major. Methods will be moved to the class that uses them.
     */
    trait TableMapBuilderTrait
    {
        use TableMapBuilderTraitCommon;

        /**
         * @param string $objName
         * @param string $clsName
         *
         * @return string
         */
        public function buildObjectInstanceCreationCode(string $objName, string $clsName): string
        {
            return $this->executeBuildObjectInstanceCreationCode($objName, $clsName);
        }

        /**
         * @param string $script
         *
         * @return void
         */
        protected function addPopulateObject(string &$script): void
        {
            $this->executeAddPopulateObject($script);
        }
    }
} else {
    /**
     * @deprecated Will be removed without replacement. Exists only for BC reasons.
     */
    trait TableMapBuilderTrait
    {
        use TableMapBuilderTraitCommon;

        /**
         * @param $objName
         * @param $clsName
         *
         * @return string
         */
        public function buildObjectInstanceCreationCode($objName,$clsName)
        {
            return $this->executeBuildObjectInstanceCreationCode($objName, $clsName);
        }

        /**
         * @param $script
         *
         * @return void
         */
        protected function addPopulateObject(&$script)
        {
            $this->executeAddPopulateObject($script);
        }
    }
}

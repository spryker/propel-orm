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
trait TableMapBuilderTrait
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

if (class_exists('Propel\Runtime\ActiveQuery\Criterion\ExistsQueryCriterion')) {
    /**
     * @deprecated Will be removed without replacement. Exists only for BC reasons.
     */
    trait QueryBuilderTrait
    {
        use TableMapBuilderTrait;

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
    trait QueryBuilderTrait
    {
        use TableMapBuilderTrait;

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

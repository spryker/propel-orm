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

use Propel\Generator\Model\Column;

/**
 * @deprecated Will be removed without replacement. Exists only for BC reasons.
 */
trait CommonObjectBuilderTrait
{
    /**
     * @param string $script
     * @param \Propel\Generator\Model\Column $col
     *
     * @return void
     */
    protected abstract function executeAddBooleanMutator(string &$script, Column $col): void;

    /**
     * @param string $script
     * @param \Propel\Generator\Model\Column $col
     *
     * @return void
     */
    protected abstract function executeAddDefaultMutator(string &$script, Column $col): void;

    /**
     * @param string $script
     *
     * @return void
     */
    protected abstract function executeAddClassBody(string &$script): void;

    /**
     * @param string $script
     *
     * @return void
     */
    protected abstract function executeAddHookMethods(string &$script): void;

    /**
     * @param string $script
     *
     * @return void
     */
    protected abstract function executeAddSaveClose(string &$script): void;
}

if (class_exists('Propel\Runtime\ActiveQuery\Criterion\ExistsQueryCriterion')) {
    /**
     * @deprecated Will be removed without replacement. Exists only for BC reasons.
     */
    trait ObjectBuilderTrait
    {
        use CommonObjectBuilderTrait;

        /**
         * @param string $script
         * @param \Propel\Generator\Model\Column $col
         *
         * @return void
         */
        public function addBooleanMutator(string &$script, Column $col): void
        {
            $this->executeAddBooleanMutator($script, $col);
        }

        /**
         * @param string $script
         * @param \Propel\Generator\Model\Column $col
         *
         * @return void
         */
        protected function addDefaultMutator(string &$script, Column $col): void
        {
            $this->executeAddDefaultMutator($script, $col);
        }

        /**
         * @param string $script
         *
         * @return void
         */
        protected function addClassBody(string &$script): void
        {
            $this->executeAddClassBody($script);
        }

        /**
         * @param string $script
         *
         * @return void
         */
        protected function addHookMethods(string &$script): void
        {
            $this->executeAddHookMethods($script);
        }

        /**
         * @param string $script
         *
         * @return void
         */
        protected function executeAddSaveClose(string &$script): void
        {
            $this->executeAddSaveClose($script);
        }
    }
} else {
    /**
     * @deprecated Will be removed without replacement. Exists only for BC reasons.
     */
    trait ObjectBuilderTrait
    {
        use CommonObjectBuilderTrait;

        /**
         * @param $script
         * @param \Propel\Generator\Model\Column $col
         *
         * @return void
         */
        protected function addBooleanMutator(&$script, Column $col)
        {
            return $this->executeAddBooleanMutator($script, $col);
        }

        /**
         * @param $script
         * @param \Propel\Generator\Model\Column $col
         *
         * @return void
         */
        protected function addDefaultMutator(&$script, Column $col)
        {
            $this->executeAddDefaultMutator($script, $col);
        }

        /**
         * @param $script
         *
         * @return void
         */
        protected function addClassBody(&$script)
        {
            $this->executeAddClassBody($script);
        }

        /**
         * @param $script
         *
         * @return void
         */
        protected function addHookMethods(&$script)
        {
            $this->executeAddHookMethods($script);
        }

        /**
         * @param $script
         *
         * @return void
         */
        protected function executeAddSaveClose(&$script)
        {
            $this->executeAddSaveClose($script);
        }
    }
}

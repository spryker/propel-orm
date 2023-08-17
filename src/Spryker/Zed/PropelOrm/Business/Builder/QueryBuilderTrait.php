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
 * @deprecated Will be removed in the next major. Exists only for "propel/propel": "2.0.0-beta1" version support.
 */
trait QueryBuilderTraitCommon
{
    /**
     * @param \Propel\Generator\Model\Column $col
     *
     * @return string
     */
    protected abstract function executeAddFilterByCol(Column $col): string;

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
    protected abstract function executeAddFindPk(string &$script): void;

    /**
     * @param string $script
     *
     * @return void
     */
    protected abstract function executeAddFindPks(string &$script): void;
}

// propel/propel > 2.0.0-beta1
if (class_exists('Propel\Common\Util\PathTrait')) {
    /**
     * @deprecated Will be removed in the next major. Methods will be moved to the class that uses them.
     */
    trait QueryBuilderTrait
    {
        use QueryBuilderTraitCommon;

        /**
         * @param \Propel\Generator\Model\Column $col
         *
         * @return string
         */
        protected function addFilterByCol(Column $col): string
        {
            return $this->executeAddFilterByCol($col);
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
        protected function addFindPk(string &$script): void
        {
            $this->executeAddFindPk($script);
        }

        /**
         * @param string $script
         *
         * @return void
         */
        protected function addFindPks(string &$script): void
        {
            $this->executeAddFindPks($script);
        }
    }
} else {
    /**
     * @deprecated Will be removed without replacement. Exists only for BC reasons.
     */
    trait QueryBuilderTrait
    {
        use QueryBuilderTraitCommon;

        /**
         * @param $script
         * @param \Propel\Generator\Model\Column $col
         *
         * @return void
         */
        protected function addFilterByCol(&$script, Column $col)
        {
            $this->executeAddFilterByCol($script, $col);
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
        protected function addFindPk(&$script)
        {
            $this->executeAddFindPk($script);
        }

        /**
         * @param $script
         *
         * @return void
         */
        protected function addFindPks(&$script)
        {
            $this->executeAddFindPks($script);
        }
    }
}

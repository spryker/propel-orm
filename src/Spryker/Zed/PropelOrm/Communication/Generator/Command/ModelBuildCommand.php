<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PropelOrm\Communication\Generator\Command;

use Propel\Generator\Command\ModelBuildCommand as PropelModelBuildCommand;
use Spryker\Zed\PropelOrm\Communication\Generator\ConfigurablePropelCommandInterface;
use Spryker\Zed\PropelOrm\Communication\Generator\PropelConfiguratorTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ModelBuildCommand extends PropelModelBuildCommand implements ConfigurablePropelCommandInterface
{
    use PropelConfiguratorTrait;

    public function run(InputInterface $input, OutputInterface $output): int
    {
        return (int)$this->execute($input, $output);
    }
}

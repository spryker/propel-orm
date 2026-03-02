<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Shared\PropelOrm\Helper;

use Codeception\Module;
use Codeception\TestInterface;
use Propel\Generator\Builder\Om\AbstractOMBuilder;
use Propel\Generator\Config\QuickGeneratorConfig;
use Propel\Generator\Model\Table;

class PropelFileHelper extends Module
{
    /**
     * @var array<string>
     */
    protected $filesNames = [];

    public function writePropelFiles(array $filesToGenerate, Table $table): void
    {
        $config = new QuickGeneratorConfig();

        foreach ($filesToGenerate as $fileName => $builderClass) {
            $builder = new $builderClass($table);
            $builder->setGeneratorConfig($config);
            $this->writePropelFile($builder, $fileName);
        }
    }

    public function _after(TestInterface $test): void
    {
        parent::_after($test);

        $this->deletePropelFiles();
    }

    protected function writePropelFile(AbstractOMBuilder $objectBuilder, string $fileName): void
    {
        $fileContent = $objectBuilder->build();
        $directory = dirname($fileName);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        file_put_contents($fileName, $fileContent);

        $this->filesNames[] = $fileName;
    }

    protected function deletePropelFiles(): void
    {
        foreach ($this->filesNames as $fileName) {
            if (file_exists($fileName)) {
                unlink($fileName);
            }
        }
    }
}

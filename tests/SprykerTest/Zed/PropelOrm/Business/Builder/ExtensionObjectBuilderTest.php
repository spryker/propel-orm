<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\PropelOrm\Business\Builder;

use Codeception\Test\Unit;
use Propel\Generator\Model\PropelTypes;
use Spryker\Shared\Kernel\KernelConstants;
use Spryker\Zed\PropelOrm\Business\Builder\ExtensionObjectBuilder;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group PropelOrm
 * @group Business
 * @group Builder
 * @group ExtensionObjectBuilderTest
 * Add your own group annotations below this line
 */
class ExtensionObjectBuilderTest extends Unit
{
    protected const string TABLE_NAME = 'foo';

    protected const string TABLE_NAMESPACE = 'Orm\Zed\FooModule\Persistence';

    protected const string CUSTOM_VENDOR_NAMESPACE = 'SprykerTest';

    /**
     * @var \SprykerTest\Zed\PropelOrm\PropelOrmBusinessTester
     */
    protected $tester;

    public function testGeneratedUseStatementUsesAbstractClassFromConfiguredVendorNamespace(): void
    {
        // Arrange
        $this->tester->setConfig(KernelConstants::CORE_NAMESPACES, [static::CUSTOM_VENDOR_NAMESPACE, 'Spryker']);

        $table = $this->tester->createTable(static::TABLE_NAME, [
            ['name' => 'id_foo', 'type' => PropelTypes::INTEGER],
        ], static::TABLE_NAMESPACE);

        $extensionFile = __DIR__ . '/Fixtures/ExtensionFoo.php';

        $this->tester->writePropelFiles([$extensionFile => ExtensionObjectBuilder::class], $table);

        // Assert
        $this->assertStringContainsString(
            'use SprykerTest\Zed\FooModule\Persistence\Propel\AbstractFoo as BaseFoo;',
            (string)file_get_contents($extensionFile),
        );
    }

    public function testGeneratedUseStatementFallsBackToBaseClassWhenNoAbstractExists(): void
    {
        // Arrange — use a namespace for which no abstract class fixture exists
        $this->tester->setConfig(KernelConstants::CORE_NAMESPACES, ['Spryker', 'SprykerEco']);

        $table = $this->tester->createTable(static::TABLE_NAME, [
            ['name' => 'id_foo', 'type' => PropelTypes::INTEGER],
        ], static::TABLE_NAMESPACE);

        $extensionFile = __DIR__ . '/Fixtures/ExtensionFoo.php';

        $this->tester->writePropelFiles([$extensionFile => ExtensionObjectBuilder::class], $table);

        // Assert — falls back to Propel-generated base class
        $this->assertStringContainsString(
            'use Orm\Zed\FooModule\Persistence\Base\Foo as BaseFoo;',
            (string)file_get_contents($extensionFile),
        );
    }
}

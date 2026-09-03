<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\PropelOrm\Communication\Generator\Diff;

use Codeception\Test\Unit;
use Propel\Generator\Model\Database;
use Propel\Generator\Model\Diff\DatabaseComparator;
use Propel\Generator\Model\Diff\DatabaseDiff;
use Propel\Generator\Model\ForeignKey;
use Propel\Generator\Model\Index;
use Propel\Generator\Model\Table;
use Propel\Generator\Platform\MysqlPlatform;
use Spryker\Zed\PropelOrm\Communication\Generator\Diff\ForeignKeySupportIndexDiffFilter;
use Spryker\Zed\PropelOrm\Communication\Generator\Diff\ForeignKeySupportIndexDiffFilterInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group PropelOrm
 * @group Communication
 * @group Generator
 * @group Diff
 * @group ForeignKeySupportIndexDiffFilterTest
 * Add your own group annotations below this line
 */
class ForeignKeySupportIndexDiffFilterTest extends Unit
{
    /**
     * @var string
     */
    protected const TABLE_NAME_CROSS_REFERENCE = 'spy_acl_user_has_group';

    /**
     * @var string
     */
    protected const COLUMN_NAME_FK_USER = 'fk_user';

    /**
     * @var string
     */
    protected const COLUMN_NAME_FK_ACL_GROUP = 'fk_acl_group';

    /**
     * @var string
     */
    protected const INDEX_NAME_FI_USER = 'spy_acl_user_has_group-fi_user';

    /**
     * @var string
     */
    protected const INDEX_NAME_FI_ACL_GROUP = 'spy_acl_user_has_group-fi_acl_group';

    public function testFilterRemovesForeignKeySupportIndexNoiseWhenPrimaryKeyColumnOrderDiffers(): void
    {
        // Arrange
        $fromDatabase = $this->createDatabaseWithCrossReferenceTable([static::COLUMN_NAME_FK_USER, static::COLUMN_NAME_FK_ACL_GROUP]);
        $toDatabase = $this->createDatabaseWithCrossReferenceTable([static::COLUMN_NAME_FK_ACL_GROUP, static::COLUMN_NAME_FK_USER]);
        $databaseDiff = $this->computeDatabaseDiff($fromDatabase, $toDatabase);

        $tableDiff = $databaseDiff->getModifiedTables()[static::TABLE_NAME_CROSS_REFERENCE];
        $this->assertArrayHasKey(static::INDEX_NAME_FI_ACL_GROUP, $tableDiff->getRemovedIndices(), 'Precondition failed: the comparator must propose dropping the support index of the previous primary key order.');
        $this->assertArrayHasKey(static::INDEX_NAME_FI_USER, $tableDiff->getAddedIndices(), 'Precondition failed: the comparator must propose adding the support index of the new primary key order.');

        // Act
        $filteredDatabaseDiff = $this->createForeignKeySupportIndexDiffFilter()->filter($databaseDiff);

        // Assert
        $this->assertNull($filteredDatabaseDiff, 'A diff consisting only of foreign key support index noise must be filtered out completely.');
    }

    public function testFilterKeepsModifiedTablesWithRemainingChanges(): void
    {
        // Arrange
        $fromDatabase = $this->createDatabaseWithCrossReferenceTable([static::COLUMN_NAME_FK_USER, static::COLUMN_NAME_FK_ACL_GROUP]);
        $toDatabase = $this->createDatabaseWithCrossReferenceTable([static::COLUMN_NAME_FK_ACL_GROUP, static::COLUMN_NAME_FK_USER]);
        $this->addTableWithSingleColumnPrimaryKey($fromDatabase, 'spy_other');
        $toOtherTable = $this->addTableWithSingleColumnPrimaryKey($toDatabase, 'spy_other');
        $toOtherTable->addColumn(['name' => 'new_column', 'type' => 'VARCHAR', 'size' => 255]);
        $databaseDiff = $this->computeDatabaseDiff($fromDatabase, $toDatabase);

        // Act
        $filteredDatabaseDiff = $this->createForeignKeySupportIndexDiffFilter()->filter($databaseDiff);

        // Assert
        $this->assertInstanceOf(DatabaseDiff::class, $filteredDatabaseDiff);
        $this->assertArrayNotHasKey(static::TABLE_NAME_CROSS_REFERENCE, $filteredDatabaseDiff->getModifiedTables());
        $this->assertArrayHasKey('spy_other', $filteredDatabaseDiff->getModifiedTables());
    }

    public function testFilterKeepsIndexDropWhenForeignKeyIsRemoved(): void
    {
        // Arrange
        $fromDatabase = $this->createDatabaseWithCrossReferenceTable([static::COLUMN_NAME_FK_USER, static::COLUMN_NAME_FK_ACL_GROUP]);
        $toDatabase = $this->createDatabaseWithCrossReferenceTable(
            [static::COLUMN_NAME_FK_USER, static::COLUMN_NAME_FK_ACL_GROUP],
            [static::COLUMN_NAME_FK_USER],
        );
        $databaseDiff = $this->computeDatabaseDiff($fromDatabase, $toDatabase);

        // Act
        $filteredDatabaseDiff = $this->createForeignKeySupportIndexDiffFilter()->filter($databaseDiff);

        // Assert
        $this->assertInstanceOf(DatabaseDiff::class, $filteredDatabaseDiff);
        $tableDiff = $filteredDatabaseDiff->getModifiedTables()[static::TABLE_NAME_CROSS_REFERENCE];
        $this->assertArrayHasKey(static::INDEX_NAME_FI_ACL_GROUP, $tableDiff->getRemovedIndices(), 'The support index of a removed foreign key must still be dropped.');
    }

    public function testFilterKeepsIndexAddWhenForeignKeyIsAdded(): void
    {
        // Arrange
        $fromDatabase = $this->createDatabaseWithCrossReferenceTable(
            [static::COLUMN_NAME_FK_USER, static::COLUMN_NAME_FK_ACL_GROUP],
            [static::COLUMN_NAME_FK_USER],
        );
        $toDatabase = $this->createDatabaseWithCrossReferenceTable([static::COLUMN_NAME_FK_USER, static::COLUMN_NAME_FK_ACL_GROUP]);
        $databaseDiff = $this->computeDatabaseDiff($fromDatabase, $toDatabase);

        // Act
        $filteredDatabaseDiff = $this->createForeignKeySupportIndexDiffFilter()->filter($databaseDiff);

        // Assert
        $this->assertInstanceOf(DatabaseDiff::class, $filteredDatabaseDiff);
        $tableDiff = $filteredDatabaseDiff->getModifiedTables()[static::TABLE_NAME_CROSS_REFERENCE];
        $this->assertArrayHasKey(static::INDEX_NAME_FI_ACL_GROUP, $tableDiff->getAddedIndices(), 'The support index of a newly added foreign key must still be created.');
    }

    public function testFilterKeepsIndexChangesForSingleColumnPrimaryKeyTable(): void
    {
        // Arrange
        $fromDatabase = new Database('zed', new MysqlPlatform());
        $fromTable = $this->addTableWithSingleColumnPrimaryKey($fromDatabase, 'spy_single');
        $fromTable->addColumn(['name' => static::COLUMN_NAME_FK_USER, 'type' => 'INTEGER']);
        $this->addForeignKey($fromTable, static::COLUMN_NAME_FK_USER);
        $this->addReferencedTable($fromDatabase, 'spy_user');
        $this->addSingleColumnIndex($fromTable, 'spy_single-custom_index', static::COLUMN_NAME_FK_USER);

        $toDatabase = new Database('zed', new MysqlPlatform());
        $toTable = $this->addTableWithSingleColumnPrimaryKey($toDatabase, 'spy_single');
        $toTable->addColumn(['name' => static::COLUMN_NAME_FK_USER, 'type' => 'INTEGER']);
        $this->addForeignKey($toTable, static::COLUMN_NAME_FK_USER);
        $this->addReferencedTable($toDatabase, 'spy_user');

        $databaseDiff = $this->computeDatabaseDiff($fromDatabase, $toDatabase);

        // Act
        $filteredDatabaseDiff = $this->createForeignKeySupportIndexDiffFilter()->filter($databaseDiff);

        // Assert
        $this->assertInstanceOf(DatabaseDiff::class, $filteredDatabaseDiff);
        $tableDiff = $filteredDatabaseDiff->getModifiedTables()['spy_single'];
        $this->assertArrayHasKey('spy_single-custom_index', $tableDiff->getRemovedIndices(), 'Index changes on tables without a composite primary key must not be filtered.');
    }

    public function testFilterKeepsIndexChangesForNonForeignKeyColumns(): void
    {
        // Arrange
        $fromDatabase = $this->createDatabaseWithCrossReferenceTable([static::COLUMN_NAME_FK_USER, static::COLUMN_NAME_FK_ACL_GROUP]);
        $fromTable = $fromDatabase->getTable(static::TABLE_NAME_CROSS_REFERENCE);
        $fromTable->addColumn(['name' => 'note', 'type' => 'VARCHAR', 'size' => 255]);
        $this->addSingleColumnIndex($fromTable, 'spy_acl_user_has_group-note_index', 'note');
        $toDatabase = $this->createDatabaseWithCrossReferenceTable([static::COLUMN_NAME_FK_USER, static::COLUMN_NAME_FK_ACL_GROUP]);
        $toTable = $toDatabase->getTable(static::TABLE_NAME_CROSS_REFERENCE);
        $toTable->addColumn(['name' => 'note', 'type' => 'VARCHAR', 'size' => 255]);

        $databaseDiff = $this->computeDatabaseDiff($fromDatabase, $toDatabase);

        // Act
        $filteredDatabaseDiff = $this->createForeignKeySupportIndexDiffFilter()->filter($databaseDiff);

        // Assert
        $this->assertInstanceOf(DatabaseDiff::class, $filteredDatabaseDiff);
        $tableDiff = $filteredDatabaseDiff->getModifiedTables()[static::TABLE_NAME_CROSS_REFERENCE];
        $this->assertArrayHasKey('spy_acl_user_has_group-note_index', $tableDiff->getRemovedIndices(), 'Index changes on non foreign key columns must not be filtered.');
    }

    /**
     * @param array<string> $primaryKeyColumnNames
     * @param array<string>|null $foreignKeyColumnNames
     */
    protected function createDatabaseWithCrossReferenceTable(array $primaryKeyColumnNames, ?array $foreignKeyColumnNames = null): Database
    {
        $database = new Database('zed', new MysqlPlatform());
        $table = new Table(static::TABLE_NAME_CROSS_REFERENCE);
        $database->addTable($table);

        foreach ($primaryKeyColumnNames as $columnName) {
            $table->addColumn(['name' => $columnName, 'type' => 'INTEGER', 'primaryKey' => 'true', 'required' => 'true']);
        }

        foreach ($foreignKeyColumnNames ?? $primaryKeyColumnNames as $columnName) {
            $this->addForeignKey($table, $columnName);
            $this->addReferencedTable($database, str_replace('fk_', 'spy_', $columnName));
        }

        return $database;
    }

    protected function addForeignKey(Table $table, string $columnName): void
    {
        $foreignKey = new ForeignKey(sprintf('%s-%s', $table->getName(), $columnName));
        $foreignKey->setForeignTableCommonName(str_replace('fk_', 'spy_', $columnName));
        $foreignKey->addReference(['local' => $columnName, 'foreign' => 'id']);
        $table->addForeignKey($foreignKey);
    }

    protected function addReferencedTable(Database $database, string $tableName): Table
    {
        $table = new Table($tableName);
        $database->addTable($table);
        $table->addColumn(['name' => 'id', 'type' => 'INTEGER', 'primaryKey' => 'true', 'required' => 'true']);

        return $table;
    }

    protected function addTableWithSingleColumnPrimaryKey(Database $database, string $tableName): Table
    {
        $table = new Table($tableName);
        $database->addTable($table);
        $table->addColumn(['name' => 'id', 'type' => 'INTEGER', 'primaryKey' => 'true', 'required' => 'true']);

        return $table;
    }

    protected function addSingleColumnIndex(Table $table, string $indexName, string $columnName): void
    {
        $index = new Index($indexName);
        $index->addColumn($table->getColumn($columnName));
        $table->addIndex($index);
    }

    protected function computeDatabaseDiff(Database $fromDatabase, Database $toDatabase): DatabaseDiff
    {
        $fromDatabase->doFinalInitialization();
        $toDatabase->doFinalInitialization();

        /** @var \Propel\Generator\Model\Diff\DatabaseDiff|false $databaseDiff */
        $databaseDiff = DatabaseComparator::computeDiff($fromDatabase, $toDatabase);
        $this->assertInstanceOf(DatabaseDiff::class, $databaseDiff, 'Precondition failed: the comparator must detect a difference.');

        return $databaseDiff;
    }

    protected function createForeignKeySupportIndexDiffFilter(): ForeignKeySupportIndexDiffFilterInterface
    {
        return new ForeignKeySupportIndexDiffFilter();
    }
}

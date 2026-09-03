<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PropelOrm\Communication\Generator\Diff;

use Propel\Generator\Model\Diff\DatabaseDiff;
use Propel\Generator\Model\Diff\TableDiff;
use Propel\Generator\Model\Index;
use Propel\Generator\Model\Table;

/**
 * Removes foreign key support index (`fi_*`) additions/removals from a database diff when they are caused solely by
 * the composite primary key column order (changed by the schema merger) while the foreign key itself exists on both
 * the database and the schema side. Such diffs are order artifacts, not schema changes.
 */
class ForeignKeySupportIndexDiffFilter implements ForeignKeySupportIndexDiffFilterInterface
{
    public function filter(DatabaseDiff $databaseDiff): ?DatabaseDiff
    {
        $tableDiffs = $databaseDiff->getModifiedTables();

        foreach ($tableDiffs as $tableName => $tableDiff) {
            $this->filterTableDiff($tableDiff);

            if (!$this->hasChanges($tableDiff)) {
                unset($tableDiffs[$tableName]);
            }
        }

        $databaseDiff->setModifiedTables($tableDiffs);

        if ($this->hasTableChanges($databaseDiff)) {
            return $databaseDiff;
        }

        return null;
    }

    protected function filterTableDiff(TableDiff $tableDiff): void
    {
        $fromTable = $tableDiff->getFromTable();
        $toTable = $tableDiff->getToTable();

        if ($fromTable === null || $toTable === null || count($toTable->getPrimaryKey()) < 2) {
            return;
        }

        $filterableColumnNames = $this->getFilterableColumnNames($fromTable, $toTable);

        if ($filterableColumnNames === []) {
            return;
        }

        $tableDiff->setAddedIndices($this->filterIndexes($tableDiff->getAddedIndices(), $filterableColumnNames));
        $tableDiff->setRemovedIndices($this->filterIndexes($tableDiff->getRemovedIndices(), $filterableColumnNames));
    }

    /**
     * Returns the names of all columns that are part of the composite primary key of the target table and are the only
     * local column of a foreign key existing on both the source and the target table.
     *
     * @return array<string>
     */
    protected function getFilterableColumnNames(Table $fromTable, Table $toTable): array
    {
        $primaryKeyColumnNames = [];
        foreach ($toTable->getPrimaryKey() as $primaryKeyColumn) {
            $primaryKeyColumnNames[] = $primaryKeyColumn->getName();
        }

        return array_intersect(
            $primaryKeyColumnNames,
            $this->getSingleColumnForeignKeyColumnNames($fromTable),
            $this->getSingleColumnForeignKeyColumnNames($toTable),
        );
    }

    /**
     * @return array<string>
     */
    protected function getSingleColumnForeignKeyColumnNames(Table $table): array
    {
        $columnNames = [];
        foreach ($table->getForeignKeys() as $foreignKey) {
            $localColumnNames = $foreignKey->getLocalColumns();

            if (count($localColumnNames) !== 1) {
                continue;
            }

            $columnNames[] = (string)current($localColumnNames);
        }

        return $columnNames;
    }

    /**
     * @param array<string, \Propel\Generator\Model\Index> $indexes
     * @param array<string> $filterableColumnNames
     *
     * @return array<string, \Propel\Generator\Model\Index>
     */
    protected function filterIndexes(array $indexes, array $filterableColumnNames): array
    {
        return array_filter($indexes, function (Index $index) use ($filterableColumnNames): bool {
            return !$this->isForeignKeySupportIndex($index, $filterableColumnNames);
        });
    }

    /**
     * @param array<string> $filterableColumnNames
     */
    protected function isForeignKeySupportIndex(Index $index, array $filterableColumnNames): bool
    {
        $indexColumnNames = $index->getColumns();

        return count($indexColumnNames) === 1 && in_array(current($indexColumnNames), $filterableColumnNames, true);
    }

    protected function hasChanges(TableDiff $tableDiff): bool
    {
        return $tableDiff->hasAddedColumns()
            || $tableDiff->hasRemovedColumns()
            || $tableDiff->hasModifiedColumns()
            || $tableDiff->hasRenamedColumns()
            || $tableDiff->hasModifiedPk()
            || $tableDiff->hasAddedIndices()
            || $tableDiff->hasRemovedIndices()
            || $tableDiff->hasModifiedIndices()
            || $tableDiff->hasAddedFks()
            || $tableDiff->hasRemovedFks()
            || $tableDiff->hasModifiedFks();
    }

    protected function hasTableChanges(DatabaseDiff $databaseDiff): bool
    {
        return $databaseDiff->countAddedTables() > 0
            || $databaseDiff->countRemovedTables() > 0
            || $databaseDiff->countModifiedTables() > 0
            || $databaseDiff->countRenamedTables() > 0;
    }
}

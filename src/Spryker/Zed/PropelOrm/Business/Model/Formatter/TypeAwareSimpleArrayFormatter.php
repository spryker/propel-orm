<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PropelOrm\Business\Model\Formatter;

use Propel\Generator\Model\PropelTypes;
use Propel\Runtime\ActiveQuery\BaseModelCriteria;
use Propel\Runtime\DataFetcher\DataFetcherInterface;
use Propel\Runtime\Formatter\SimpleArrayFormatter;
use Propel\Runtime\Map\DatabaseMap;
use Propel\Runtime\Propel;

/**
 * Array formatter for Propel select query
 * format() returns a ArrayCollection of associative arrays, a string,
 * or an array and ensures that boolean type column values are returned as booleans.
 */
class TypeAwareSimpleArrayFormatter extends SimpleArrayFormatter
{
    /**
     * @var array<string, bool>
     */
    protected array $canFindColumnCache = [];

    /**
     * @var array<string, bool>
     */
    protected array $isBooleanColumnCache = [];

    /**
     * @var array<string, string>|null
     */
    protected ?array $normalizedAsColumnsArray = null;

    /**
     * {@inheritDoc}
     *
     * @return $this
     */
    public function init(BaseModelCriteria $criteria, ?DataFetcherInterface $dataFetcher = null)
    {
        $this->normalizedAsColumnsArray = null;
        $this->canFindColumnCache = [];
        $this->isBooleanColumnCache = [];

        return parent::init($criteria, $dataFetcher);
    }

    /**
     * @param array $row
     *
     * @return array|string|false
     */
    public function getStructuredArrayFromRow(array $row)
    {
        $rowFromParent = parent::getStructuredArrayFromRow($row);

        if (!$rowFromParent || !is_array($rowFromParent)) {
            return $rowFromParent;
        }

        $normalizedAsColumnsArray = $this->getNormalizedAsColumnsArray();

        foreach ($rowFromParent as $key => $value) {
            $tableColumnArray = $this->getTableColumnArray($normalizedAsColumnsArray[$key]);
            $cacheKey = $this->getCacheKey($tableColumnArray);

            if (!$this->canFindColumnInTableMap($tableColumnArray, $cacheKey)) {
                continue;
            }

            if ($this->isBooleanColumnType($tableColumnArray, $cacheKey)) {
                $rowFromParent[$key] = (bool)$value;
            }
        }

        return $rowFromParent;
    }

    protected function getNormalizedAsColumnsArray(): array
    {
        if ($this->normalizedAsColumnsArray !== null) {
            return $this->normalizedAsColumnsArray;
        }

        $fullySpecifiedQueryColumnNames = $this->getAsColumns();

        $normalizedValues = $this->normalizeArray($fullySpecifiedQueryColumnNames);
        $normalizedKeys = $this->normalizeArray(array_keys($fullySpecifiedQueryColumnNames));

        return $this->normalizedAsColumnsArray = array_combine($normalizedKeys, $normalizedValues);
    }

    protected function canFindColumnInTableMap(array $tableColumnArray, string $cacheKey): bool
    {
        if (array_key_exists($cacheKey, $this->canFindColumnCache)) {
            return $this->canFindColumnCache[$cacheKey];
        }

        return $this->canFindColumnCache[$cacheKey] = $this->resolveCanFindColumnInTableMap($tableColumnArray);
    }

    protected function resolveCanFindColumnInTableMap(array $tableColumnArray): bool
    {
        if (!$this->isArrayOfTwoItems($tableColumnArray)) {
            return false;
        }

        $tableExists = $this->getDatabaseMap()->hasTable($this->getTableName($tableColumnArray));

        if (!$tableExists) {
            return false;
        }

        return $this->getDatabaseMap()
            ->getTable($this->getTableName($tableColumnArray))
            ->hasColumn($this->getColumnName($tableColumnArray));
    }

    protected function isArrayOfTwoItems(array $tableColumnArray): bool
    {
        return count($tableColumnArray) === 2 && is_string($tableColumnArray[0]) && is_string($tableColumnArray[1]);
    }

    protected function getDatabaseMap(): DatabaseMap
    {
        return Propel::getServiceContainer()->getDatabaseMap($this->dbName);
    }

    /**
     * @param string $fullyQualifiedColumnName
     *
     * @return array<string>
     */
    protected function getTableColumnArray(string $fullyQualifiedColumnName): array
    {
        return explode('.', $fullyQualifiedColumnName);
    }

    protected function isBooleanColumnType(array $tableColumnArray, string $cacheKey): bool
    {
        if (array_key_exists($cacheKey, $this->isBooleanColumnCache)) {
            return $this->isBooleanColumnCache[$cacheKey];
        }

        return $this->isBooleanColumnCache[$cacheKey] = $this->resolveIsBooleanColumnType($tableColumnArray);
    }

    protected function resolveIsBooleanColumnType(array $tableColumnArray): bool
    {
        $columnType = $this->getDatabaseMap()
            ->getTable($this->getTableName($tableColumnArray))
            ->getColumn($this->getColumnName($tableColumnArray))
            ->getType();

        return $columnType === PropelTypes::BOOLEAN;
    }

    protected function getCacheKey(array $tableColumnArray): string
    {
        return sprintf(
            '%s.%s.%s',
            $this->dbName,
            $this->getTableName($tableColumnArray),
            $this->getColumnName($tableColumnArray),
        );
    }

    protected function getTableName(array $tableColumnArray): string
    {
        return $tableColumnArray[0] ?? '';
    }

    protected function getColumnName(array $tableColumnArray): string
    {
        return $tableColumnArray[1] ?? '';
    }

    protected function normalizeArray(array $array): array
    {
        return array_map(function ($arrayElement) {
            return str_replace(['`', '"'], '', $arrayElement);
        }, $array);
    }
}

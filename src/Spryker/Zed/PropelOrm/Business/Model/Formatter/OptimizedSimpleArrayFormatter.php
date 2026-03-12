<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\PropelOrm\Business\Model\Formatter;

use Propel\Runtime\ActiveQuery\BaseModelCriteria;
use Propel\Runtime\DataFetcher\DataFetcherInterface;
use Propel\Runtime\Formatter\SimpleArrayFormatter;

/**
 * The original SimpleArrayFormatter is not optimized for large datasets because it prepares column names separately for each row.
 * This formatter is optimized for big amount of data, because it prepares column names only once and then combines them with values for each row.
 */
class OptimizedSimpleArrayFormatter extends SimpleArrayFormatter
{
    protected array $preparedAsColumns = [];

    /**
     * @param \Propel\Runtime\ActiveQuery\BaseModelCriteria $criteria
     * @param \Propel\Runtime\DataFetcher\DataFetcherInterface|null $dataFetcher
     *
     * @return $this
     */
    public function init(BaseModelCriteria $criteria, ?DataFetcherInterface $dataFetcher = null)
    {
        parent::init($criteria, $dataFetcher);

        $this->prepareAsColumn();

        return $this;
    }

    /**
     * @param array<mixed> $row
     *
     * @return array<mixed>
     */
    public function getStructuredArrayFromRow(array $row): array
    {
        if (count($this->preparedAsColumns) > 1 && count($row) > 1) {
            $finalRow = [];
            foreach ($row as $index => $value) {
                $finalRow[$this->preparedAsColumns[$index]] = $value;
            }
        } else {
            $finalRow = $row[0];
        }

        return $finalRow;
    }

    protected function prepareAsColumn(): void
    {
        $columnNames = array_keys($this->getAsColumns());
        $result = [];
        if (count($columnNames) > 1) {
            foreach ($columnNames as $index => $column) {
                $result[$index] = str_replace('"', '', $column);
            }
        }

        $this->preparedAsColumns = $result;
    }
}

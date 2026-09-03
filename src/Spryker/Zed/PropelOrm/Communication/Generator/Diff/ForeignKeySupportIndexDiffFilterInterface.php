<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PropelOrm\Communication\Generator\Diff;

use Propel\Generator\Model\Diff\DatabaseDiff;

interface ForeignKeySupportIndexDiffFilterInterface
{
    /**
     * Removes foreign key support index noise from the given diff.
     *
     * @return \Propel\Generator\Model\Diff\DatabaseDiff|null The filtered diff, or null when no differences remain.
     */
    public function filter(DatabaseDiff $databaseDiff): ?DatabaseDiff;
}

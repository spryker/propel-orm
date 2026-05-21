<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PropelOrm;

use Spryker\Shared\Kernel\KernelConstants;
use Spryker\Zed\Kernel\AbstractBundleConfig;

class PropelOrmConfig extends AbstractBundleConfig
{
    /**
     * Specification:
     * - Defines if boolean casting is enabled for data formatting.
     *
     * @api
     *
     * @return bool
     */
    public function isBooleanCastingEnabled(): bool
    {
        return false;
    }

    /**
     * Specification:
     * - Returns the list of core vendor namespaces used when resolving Propel abstract base classes.
     *
     * @api
     *
     * @return array<string>
     */
    public function getCoreNamespaces(): array
    {
        return $this->get(KernelConstants::CORE_NAMESPACES);
    }
}

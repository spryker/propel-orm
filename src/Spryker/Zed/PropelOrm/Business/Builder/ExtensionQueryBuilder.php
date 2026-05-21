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

use Propel\Generator\Builder\Om\ExtensionQueryBuilder as PropelExtensionQueryObjectBuilder;
use Spryker\Zed\Kernel\BundleConfigResolverAwareTrait;

class ExtensionQueryBuilder extends PropelExtensionQueryObjectBuilder
{
    use BundleConfigResolverAwareTrait;

    /**
     * @var int
     */
    public const POSITION_OF_ORM = 0;

    /**
     * @var int
     */
    public const POSITION_OF_BASE = 4;

    protected const string DEFAULT_VENDOR_NAMESPACE = 'Spryker';

    /**
     * @param string|null $ignoredNamespace the ignored namespace
     *
     * @return string
     */
    public function getUseStatements(?string $ignoredNamespace = null): string
    {
        $script = '';
        $declaredClasses = $this->declaredClasses;

        unset($declaredClasses[$ignoredNamespace]);
        ksort($declaredClasses);

        foreach ($declaredClasses as $namespace => $classes) {
            asort($classes);
            $script = $this->addUseStatementsToScript($classes, $namespace, $script);
        }

        return $script;
    }

    /**
     * @param array<string, string> $classes
     * @param string $namespace
     * @param string $script
     *
     * @return string
     */
    protected function addUseStatementsToScript(array $classes, string $namespace, string $script): string
    {
        foreach ($classes as $class => $alias) {
            if ($this->isOwnClass($namespace, $class)) {
                continue;
            }

            $script = $this->addUseStatementToScript($namespace, $class, $alias, $script);
        }

        return $script;
    }

    protected function addUseStatementToScript(string $namespace, string $class, string $alias, string $script): string
    {
        if ($this->isBaseClass($namespace, $class) && $this->sprykerBaseClassExists($class)) {
            $class = 'Abstract' . $class;
            $namespace = $this->getSprykerNamespace();
        }

        if ($class === $alias) {
            return $this->addUseStatementWithoutAlias($namespace, $class, $script);
        }

        return $this->addUseStatementWithAlias($namespace, $class, $alias, $script);
    }

    protected function addUseStatementWithoutAlias(string $namespace, string $class, string $script): string
    {
        $script .= sprintf("use %s\\%s;
", $namespace, $class);

        return $script;
    }

    protected function addUseStatementWithAlias(string $namespace, string $class, string $alias, string $script): string
    {
        $script .= sprintf("use %s\\%s as %s;
", $namespace, $class, $alias);

        return $script;
    }

    protected function isOwnClass(string $namespace, string $class): bool
    {
        return ($class === $this->getUnqualifiedClassName() && $namespace === $this->getNamespace());
    }

    protected function isBaseClass(string $namespace, string $class): bool
    {
        return ($namespace === $this->getNamespace() . '\\Base' && $class == $this->getUnqualifiedClassName());
    }

    protected function sprykerBaseClassExists(string $class): bool
    {
        return class_exists($this->getSprykerClassName($this->getSprykerNamespace(), $class));
    }

    protected function getSprykerNamespace(): string
    {
        $namespaceFragments = explode('\\', $this->getNamespaceOrFail());
        $namespaceFragments[static::POSITION_OF_BASE] = 'Propel';

        foreach ($this->getSupportedVendorNamespaces() as $vendorNamespace) {
            $namespaceFragments[static::POSITION_OF_ORM] = $vendorNamespace;
            $namespace = implode('\\', $namespaceFragments);

            if (class_exists($this->getSprykerClassName($namespace, $this->getUnqualifiedClassName()))) {
                return $namespace;
            }
        }

        $namespaceFragments[static::POSITION_OF_ORM] = static::DEFAULT_VENDOR_NAMESPACE;

        return implode('\\', $namespaceFragments);
    }

    /**
     * @return array<string>
     */
    protected function getSupportedVendorNamespaces(): array
    {
        /** @var \Spryker\Zed\PropelOrm\PropelOrmConfig $config */
        $config = $this->getConfig();

        return $config->getCoreNamespaces();
    }

    protected function getSprykerClassName(string $sprykerNamespace, string $class): string
    {
        return $sprykerNamespace . '\\Abstract' . $class;
    }
}

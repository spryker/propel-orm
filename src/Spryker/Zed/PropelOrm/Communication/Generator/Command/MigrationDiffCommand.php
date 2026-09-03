<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PropelOrm\Communication\Generator\Command;

use Propel\Common\Config\ConfigurationManager;
use Propel\Generator\Command\MigrationDiffCommand as PropelMigrationDiffCommand;
use Propel\Generator\Config\GeneratorConfig;
use Propel\Generator\Exception\RuntimeException;
use Propel\Generator\Manager\MigrationManager;
use Propel\Generator\Model\Database;
use Propel\Generator\Model\Diff\DatabaseComparator;
use Propel\Generator\Model\IdMethod;
use Propel\Generator\Model\Schema;
use Spryker\Zed\PropelOrm\Communication\Generator\ConfigurablePropelCommandInterface;
use Spryker\Zed\PropelOrm\Communication\Generator\Diff\ForeignKeySupportIndexDiffFilter;
use Spryker\Zed\PropelOrm\Communication\Generator\PropelConfiguratorTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationDiffCommand extends PropelMigrationDiffCommand implements ConfigurablePropelCommandInterface
{
    use PropelConfiguratorTrait;

    public function run(InputInterface $input, OutputInterface $output): int
    {
        return (int)$this->execute($input, $output);
    }

    /**
     * Mirrors the flow of {@link \Propel\Generator\Command\MigrationDiffCommand::execute()} (propel/propel 2.0.1),
     * decomposed into steps, with one addition: every computed database diff is passed through
     * {@link \Spryker\Zed\PropelOrm\Communication\Generator\Diff\ForeignKeySupportIndexDiffFilter} in `computeMigrations()`.
     *
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $generatorConfig = $this->getGeneratorConfig($this->buildConfigOptions($input), $input);
        $paths = (array)$generatorConfig->getSection('paths');
        $this->createDirectory($paths['migrationDir']);

        $migrationManager = $this->createMigrationManager($generatorConfig, $input);
        $reversedSchema = $this->readDatabaseSchema($migrationManager, $generatorConfig, $input, $output);
        [$migrationsUp, $migrationsDown] = $this->computeMigrations($reversedSchema, $migrationManager, $generatorConfig, $input, $output);

        if (!$migrationsUp) {
            $output->writeln('Same XML and database structures for all datasource - no diff to generate');

            return static::CODE_SUCCESS;
        }

        return $this->writeMigrationFile($migrationManager, $generatorConfig, $migrationsUp, $migrationsDown, $input, $output);
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildConfigOptions(InputInterface $input): array
    {
        $configOptions = [];

        if ($this->hasInputOption('connection', $input)) {
            foreach ($input->getOption('connection') as $connection) {
                $configOptions += $this->connectionToProperties($connection);
            }
        }

        if ($this->hasInputOption('migration-table', $input)) {
            $configOptions['propel']['migrations']['tableName'] = $input->getOption('migration-table');
        }

        if ($this->hasInputOption('schema-dir', $input)) {
            $configOptions['propel']['paths']['schemaDir'] = $input->getOption('schema-dir');
        }

        if ($this->hasInputOption('output-dir', $input)) {
            $configOptions['propel']['paths']['migrationDir'] = $input->getOption('output-dir');
        }

        return $configOptions;
    }

    /**
     * @throws \Propel\Generator\Exception\RuntimeException
     */
    protected function createMigrationManager(GeneratorConfig $generatorConfig, InputInterface $input): MigrationManager
    {
        $paths = (array)$generatorConfig->getSection('paths');
        $generatorSection = (array)$generatorConfig->getSection('generator');

        $migrationManager = new MigrationManager();
        $migrationManager->setGeneratorConfig($generatorConfig);
        $migrationManager->setSchemas($this->getSchemas($paths['schemaDir'], $generatorSection['recursive']));

        $connections = [];
        $optionConnections = $input->getOption('connection');
        if (!$optionConnections) {
            $connections = $generatorConfig->getBuildConnections();
        }

        foreach ($optionConnections as $connection) {
            [$name, $dsn, $infos] = $this->parseConnection($connection);
            $connections[$name] = array_merge(['dsn' => $dsn], $infos);
        }

        $migrationManager->setConnections($connections);
        $migrationManager->setMigrationTable($generatorConfig->getConfigProperty('migrations.tableName'));
        $migrationManager->setWorkingDirectory($paths['migrationDir']);

        if ($migrationManager->hasPendingMigrations()) {
            throw new RuntimeException(sprintf(
                'Uncommitted migrations have been found ; you should either execute or delete them before rerunning the \'diff\' task. %s',
                "\n" . implode("\n", $migrationManager->getValidMigrationTimestamps()),
            ));
        }

        return $migrationManager;
    }

    /**
     * Reverse-engineers the live database structure of every configured connection into a schema model.
     *
     * @throws \Propel\Generator\Exception\RuntimeException
     */
    protected function readDatabaseSchema(
        MigrationManager $migrationManager,
        GeneratorConfig $generatorConfig,
        InputInterface $input,
        OutputInterface $output
    ): Schema {
        $totalNbTables = 0;
        $reversedSchema = new Schema();
        $connections = $migrationManager->getConnections();

        foreach ($migrationManager->getDatabases() as $appDatabase) {
            $name = (string)$appDatabase->getName();
            $params = $connections[$name] ?? [];
            if (!$params) {
                $output->writeln(sprintf('<info>No connection configured for database "%s"</info>', $name));
            }

            if ($input->getOption('verbose')) {
                $output->writeln(sprintf('Connecting to database "%s" using DSN "%s"', $name, $params['dsn']));
            }

            $connection = $migrationManager->getAdapterConnection($name);
            $platform = $generatorConfig->getConfiguredPlatform($connection, $name);

            $appDatabase->setPlatform($platform);

            if ($platform && !$platform->supportsMigrations()) {
                $output->writeln(sprintf('Skipping database "%s" since vendor "%s" does not support migrations', $name, $platform->getDatabaseType()));

                continue;
            }

            $additionalTables = [];
            foreach ($appDatabase->getTables() as $table) {
                if ($table->getSchema() && $table->getSchema() != $appDatabase->getSchema()) {
                    $additionalTables[] = $table;
                }
            }

            if ($platform && $input->getOption('disable-identifier-quoting')) {
                $platform->setIdentifierQuoting(false);
            }

            $database = new Database($name);
            $database->setPlatform($platform);
            $database->setSchema($appDatabase->getSchema());
            $database->setDefaultIdMethod(IdMethod::NATIVE);

            $parser = $generatorConfig->getConfiguredSchemaParser($connection, $name);
            if ($parser === null) {
                throw new RuntimeException(sprintf('No schema parser configured for database "%s".', $name));
            }

            $nbTables = $parser->parse($database, $additionalTables);

            $reversedSchema->addDatabase($database);
            $totalNbTables += $nbTables;

            if ($input->getOption('verbose')) {
                $output->writeln(sprintf('%d tables found in database "%s"', $nbTables, $name), Output::VERBOSITY_VERBOSE);
            }
        }

        if ($totalNbTables) {
            $output->writeln(sprintf('%d tables found in all databases.', $totalNbTables));
        } else {
            $output->writeln('No table found in all databases');
        }

        return $reversedSchema;
    }

    /**
     * Compares the reverse-engineered schema against the application schema and renders up/down DDL per datasource.
     * Every computed diff is passed through the foreign key support index diff filter first, so index differences
     * caused solely by the primary key column order do not end up in generated migrations.
     *
     * @return array{0: array<string, string>, 1: array<string, string>}
     */
    protected function computeMigrations(
        Schema $reversedSchema,
        MigrationManager $migrationManager,
        GeneratorConfig $generatorConfig,
        InputInterface $input,
        OutputInterface $output
    ): array {
        $output->writeln('Comparing models...');
        $tableRenaming = $input->getOption('table-renaming');

        $migrationsUp = [];
        $migrationsDown = [];
        $removeTable = !$input->getOption('skip-removed-table');
        $excludedTables = $input->getOption('skip-tables');
        $configManager = new ConfigurationManager($input->getOption('config-dir'));
        $excludedTables = array_merge((array)$excludedTables, (array)$configManager->getSection('exclude_tables'));
        $foreignKeySupportIndexDiffFilter = new ForeignKeySupportIndexDiffFilter();

        foreach ($reversedSchema->getDatabases() as $database) {
            $name = (string)$database->getName();

            if ($input->getOption('verbose')) {
                $output->writeln(sprintf('Comparing database "%s"', $name));
            }

            $appDataDatabase = $migrationManager->getDatabase($name);
            if (!$appDataDatabase) {
                $output->writeln(sprintf('<error>Database "%s" does not exist in schema.xml. Skipped.</error>', $name));

                continue;
            }

            $databaseDiff = DatabaseComparator::computeDiff($database, $appDataDatabase, false, $tableRenaming, $removeTable, $excludedTables);

            if ($databaseDiff) {
                $databaseDiff = $foreignKeySupportIndexDiffFilter->filter($databaseDiff);
            }

            if (!$databaseDiff) {
                if ($input->getOption('verbose')) {
                    $output->writeln(sprintf('Same XML and database structures for datasource "%s" - no diff to generate', $name));
                }

                continue;
            }

            $output->writeln(sprintf('Structure of database was modified in datasource "%s": %s', $name, $databaseDiff->getDescription()));

            foreach ($databaseDiff->getPossibleRenamedTables() as $fromTableName => $toTableName) {
                $output->writeln(sprintf(
                    '<info>Possible table renaming detected: "%s" to "%s". It will be deleted and recreated. Use --table-renaming to only rename it.</info>',
                    $fromTableName,
                    $toTableName,
                ));
            }

            $connection = $migrationManager->getAdapterConnection($name);
            /** @var \Propel\Generator\Platform\DefaultPlatform $platform */
            $platform = $generatorConfig->getConfiguredPlatform($connection, $name);
            if ($input->getOption('disable-identifier-quoting')) {
                $platform->setIdentifierQuoting(false);
            }
            $migrationsUp[$name] = $platform->getModifyDatabaseDDL($databaseDiff);
            $migrationsDown[$name] = $platform->getModifyDatabaseDDL($databaseDiff->getReverseDiff());
        }

        return [$migrationsUp, $migrationsDown];
    }

    /**
     * @param array<string, string> $migrationsUp
     * @param array<string, string> $migrationsDown
     */
    protected function writeMigrationFile(
        MigrationManager $migrationManager,
        GeneratorConfig $generatorConfig,
        array $migrationsUp,
        array $migrationsDown,
        InputInterface $input,
        OutputInterface $output
    ): int {
        $timestamp = time();
        $migrationFileName = $migrationManager->getMigrationFileName($timestamp, $input->getOption('suffix'));
        $migrationClassBody = $migrationManager->getMigrationClassBody($migrationsUp, $migrationsDown, $timestamp, $input->getOption('comment'), $input->getOption('suffix'));

        $paths = (array)$generatorConfig->getSection('paths');
        $file = $paths['migrationDir'] . DIRECTORY_SEPARATOR . $migrationFileName;
        file_put_contents($file, $migrationClassBody);

        $output->writeln(sprintf('"%s" file successfully created.', $file));

        $editorCmd = $input->getOption('editor');
        if ($editorCmd !== null) {
            $output->writeln(sprintf('Using "%s" as text editor', $editorCmd));
            shell_exec($editorCmd . ' ' . escapeshellarg($file));
        } else {
            $output->writeln('Please review the generated SQL statements, and add data migration code if necessary.');
            $output->writeln('Once the migration class is valid, call the "migrate" task to execute it.');
        }

        return static::CODE_SUCCESS;
    }
}

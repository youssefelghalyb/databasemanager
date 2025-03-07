<?php

namespace YoussefElghaly\DatabaseManager\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Finder\Finder;

/**
 * Class ModuleMigrationStatus
 *
 * This command shows the status of migrations for a specific module.
 *
 * @package YoussefElghaly\DatabaseManager\Console\Commands
 */
class ModuleMigrationStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:migrate-custom-status {module} '
                         . '{--database= : The database connection to use}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show the status of migrations for a specific module';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $module = $this->argument('module');
        $database = $this->option('database');
        
        if (empty($database)) {
            $this->error('Database connection is required.');
            return 1;
        }

        // Get the path to the module migrations
        $path = base_path("Modules/{$module}/database/migrations");

        if (!is_dir($path)) {
            $this->error("Migration path for module {$module} not found.");
            return 1;
        }

        try {
            // Check if migrations table exists
            if (!$this->checkMigrationsTableExists($database)) {
                $this->error("Migrations table does not exist in the '{$database}' database.");
                return 1;
            }

            // Get migrations that have been run from the database
            $ran = $this->getRanMigrations($database);

            // Get migrations in the module path
            $migrations = $this->getMigrationFiles($path);

            // Display the status
            $this->displayMigrationStatus($migrations, $ran, $database);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->error("Database Error: " . $e->getMessage());
            return 1;
        } catch (\InvalidArgumentException $e) {
            $this->error("Invalid Argument: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Check if migrations table exists in the database.
     *
     * @param string $database
     * @return bool
     */
    protected function checkMigrationsTableExists(string $database): bool
    {
        try {
            // Try to query the migrations table - if it exists, this will work
            DB::connection($database)->table('migrations')->first();
            return true;
        } catch (\Exception $e) {
            // If the query fails, the table doesn't exist
            return false;
        }
    }

    /**
     * Get all migration files from the specified path.
     *
     * @param string $path
     * @return array
     */
    protected function getMigrationFiles(string $path): array
    {
        $files = Finder::create()->files()->name('*.php')->in($path)->sortByName();

        $migrations = [];
        foreach ($files as $file) {
            $migrations[] = $this->getMigrationName($file->getPathname());
        }

        return $migrations;
    }

    /**
     * Get the migration name from the file path.
     *
     * @param string $path
     * @return string
     */
    protected function getMigrationName(string $path): string
    {
        return basename($path, '.php');
    }

    /**
     * Get all migrations that have been run.
     *
     * @param string $database
     * @return array
     */
    protected function getRanMigrations(string $database): array
    {
        return DB::connection($database)
            ->table('migrations')
            ->orderBy('batch')
            ->orderBy('migration')
            ->pluck('migration')
            ->toArray();
    }

    /**
     * Display the migration status.
     *
     * @param array $migrations
     * @param array $ran
     * @param string $database
     * @return void
     */
    protected function displayMigrationStatus(array $migrations, array $ran, string $database): void
    {
        if (count($migrations) > 0) {
            $rows = [];

            foreach ($migrations as $migration) {
                $isRan = in_array($migration, $ran);
                $status = $isRan 
                    ? $this->getOutput()->getFormatter()->format('<info>Ran</info>') 
                    : $this->getOutput()->getFormatter()->format('<error>Pending</error>');

                if ($isRan) {
                    $batch = DB::connection($database)
                        ->table('migrations')
                        ->where('migration', $migration)
                        ->value('batch');

                    $status .= " [Batch: {$batch}]";
                }

                $rows[] = [$migration, $status];
            }

            $this->table(['Migration', 'Status'], $rows);
            $this->info(sprintf(
                '%d pending migrations and %d ran migrations for module.',
                count(array_diff($migrations, $ran)),
                count(array_intersect($migrations, $ran))
            ));
        } else {
            $this->warn('No migrations found for this module.');
        }
    }
}
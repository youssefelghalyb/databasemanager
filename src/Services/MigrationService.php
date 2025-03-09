<?php

namespace YoussefElghaly\DatabaseManager\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use YoussefElghaly\DatabaseManager\Models\DatabaseConnection;
use YoussefElghaly\DatabaseManager\Console\Commands\ModuleMigrationStatus;

class MigrationService 
{
    /**
     * Migrate a specific module
     *
     * @param string $moduleName
     * @return string Output from the migration command
     */
    public static function migrateModule($moduleName)
    {
        try {
            $connection = DatabaseConnection::where('module_name', $moduleName)->first();
            
            if (!$connection) {
                throw new \Exception("No database connection found for module: $moduleName");
            }
            
            Artisan::call('module:migrate', [
                'module' => $connection->module_name, 
                '--database' => $connection->connection_name
            ]);
            
            return Artisan::output();
        } catch (\Exception $e) {
            Log::error("Migration Error for $moduleName: " . $e->getMessage());
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Rollback migrations for a specific module
     *
     * @param string $moduleName
     * @return string Output from the rollback command
     */
    public static function rollbackModule($moduleName)
    {
        try {
            $connection = DatabaseConnection::where('module_name', $moduleName)->first();
            
            if (!$connection) {
                throw new \Exception("No database connection found for module: $moduleName");
            }
            
            Artisan::call('module:migrate-rollback', [
                'module' => $connection->module_name, 
                '--database' => $connection->connection_name
            ]);
            
            return Artisan::output();
        } catch (\Exception $e) {
            Log::error("Rollback Error for $moduleName: " . $e->getMessage());
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Get migration status for a specific module
     *
     * @param string $moduleName
     * @param string $connection
     * @return string Output from the status command
     */
    public static function listMigrationStatus($moduleName, $connection)
    {
        try {
            // Try to use the command directly first
            try {
                Artisan::call('module:migrate-custom-status', [
                    'module' => $moduleName, 
                    '--database' => $connection
                ]);
            } catch (\Symfony\Component\Console\Exception\CommandNotFoundException $e) {
                // If command not found, manually register and run it
                $command = new ModuleMigrationStatus();
                app()['Illuminate\Contracts\Console\Kernel']->registerCommand($command);
                
                Artisan::call('module:migrate-custom-status', [
                    'module' => $moduleName, 
                    '--database' => $connection
                ]);
            }
            
            return Artisan::output();
        } catch (\Exception $e) {
            Log::error("Status Error for $moduleName: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return "Error: " . $e->getMessage();
        }
    }
}
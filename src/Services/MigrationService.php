<?php

namespace YoussefElghaly\DatabaseManager\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use YoussefElghaly\DatabaseManager\Models\DatabaseConnection;

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
            
            // You could dynamically configure the connection here if needed
            // This is commented out as it's not needed if the connection is already configured in config/database.php
            /*
            config(["database.connections.{$connection->connection_name}" => [
                'driver' => 'mysql',
                'host' => $connection->host,
                'database' => $connection->database,
                'username' => $connection->username,
                'password' => $connection->password,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]]);
            */
            
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
            Artisan::call('module:migrate-custom-status', [
                'module' => $moduleName, 
                '--database' => $connection
            ]);
            
            return Artisan::output();
        } catch (\Exception $e) {
            Log::error("Status Error for $moduleName: " . $e->getMessage());
            return "Error: " . $e->getMessage();
        }
    }
}
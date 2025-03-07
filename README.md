# Laravel Database Manager

A Laravel package that helps manage database migrations and seeders for modular applications.

## Features

- Manage database connections for different modules
- Run and track migrations for specific modules
- Create migrations through a builder interface
- Generate and run seeders with various data types

## Installation

You can install the package via composer:

```bash
composer require youssefElghaly/database-manager
```

Then publish the package assets:

```bash
php artisan vendor:publish --provider="YoussefElghaly\DatabaseManager\DatabaseManagerServiceProvider" --tag="databasemanager-config"
```

Optionally, you can publish the views and migrations if you want to customize them:

```bash
php artisan vendor:publish --provider="YoussefElghaly\DatabaseManager\DatabaseManagerServiceProvider" --tag="databasemanager-views"
php artisan vendor:publish --provider="YoussefElghaly\DatabaseManager\DatabaseManagerServiceProvider" --tag="databasemanager-migrations"
```

## Configuration

After publishing the configuration file, you can customize it in `config/databasemanager.php`:

```php
// config/databasemanager.php
return [
    'connection' => env('DB_MANAGER_CONNECTION', 'database_manager'),
    'routes' => [
        'prefix' => 'database-designer',
        'middleware' => ['web'], // Add your auth middleware here
    ],
    // Other configurations...
];
```

Add the database connection to your `.env` file:

```
DB_MANAGER_CONNECTION=database_manager
```

And add the connection to your `config/database.php` file:

```php
'connections' => [
    // ...
    'database_manager' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'forge'),
        'username' => env('DB_USERNAME', 'forge'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ],
    // ...
]
```

## Run Migrations

Run the package migrations to set up the required tables:

```bash
php artisan migrate
```

## Usage

### Accessing the Dashboard

Visit `/database-designer` in your browser to access the database management dashboard.

### Creating Migrations

1. Navigate to `/database-designer/create-migration`
2. Select a module, database connection, and table name
3. Add columns and their properties
4. Submit the form to create and run the migration

### Managing Seeders

1. Navigate to `/database-designer/seeder`
2. Select a module and database table
3. Configure seeder settings
4. Create and run the seeder

## Commands

The package provides custom Artisan commands:

```bash
# Check migration status for a specific module
php artisan module:migrate-custom-status {module} --database={connection}
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
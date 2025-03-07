<?php


namespace YoussefElghaly\DatabaseManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Str;
use YoussefElghaly\DatabaseManager\Models\DatabaseConnection;
use YoussefElghaly\DatabaseManager\Services\MigrationService;

class DatabaseController extends Controller
{

    protected $availableColumnTypes = [
        'bigIncrements',
        'bigInteger',
        'binary',
        'boolean',
        'char',
        'date',
        'dateTime',
        'decimal',
        'double',
        'enum',
        'float',
        'integer',
        'json',
        'jsonb',
        'longText',
        'mediumText',
        'string',
        'text',
        'time',
        'timestamp',
        'uuid',
    ];
    public function index()
    {
        $databases = DatabaseConnection::all();
        return view('databasemanager::migrations.index', compact('databases'));
    }

    public function migrate($moduleName)
    {
        $output = MigrationService::migrateModule($moduleName);
        return back()->with('message', $output);
    }

    public function rollback($moduleName)
    {
        $output = MigrationService::rollbackModule($moduleName);
        return back()->with('message', $output);
    }

    public function status($moduleName)
    {
        $connection = DatabaseConnection::where('module_name', $moduleName)->first();
        $output = MigrationService::listMigrationStatus($connection->module_name, $connection->connection_name);
        return view('databasemanager::migrations.status', compact('moduleName', 'output'));
    }

    // MIGRATIONS 


    public function createMigration()
    {
        $modules = Module::all();
        $databases = array_keys(config('database.connections'));
        $columnTypes = $this->availableColumnTypes;

        return view('databasemanager::migration-builder.index', compact('modules', 'databases', 'columnTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'module' => 'required|string',
            'database' => 'required|string',
            'table_name' => 'required|string',
            'columns' => 'required|array',
            'columns.*.name' => 'required|string',
            'columns.*.type' => 'required|string|in:' . implode(',', $this->availableColumnTypes),
            'columns.*.length' => 'nullable|string',
            'columns.*.nullable' => 'nullable|in:on',
            'columns.*.default' => 'nullable|string',
        ]);

        // Generate migration name
        $migrationName = 'create_' . $request->table_name . '_table';
        
        // Generate migration content
        $migrationContent = $this->generateMigrationContent(
            $request->table_name,
            $request->columns,
            $request->database,
            $request->module
        );

        // Get module path
        $module = Module::find($request->module);
        $migrationPath = $module->getPath() . '/database/migrations';

        // Create migrations directory if it doesn't exist
        if (!File::isDirectory($migrationPath)) {
            File::makeDirectory($migrationPath, 0755, true);
        }

        // Create migration file
        $fileName = date('Y_m_d_His_') . Str::snake($migrationName) . '.php';
        File::put($migrationPath . '/' . $fileName, $migrationContent);

        // Run the migration
        Artisan::call('module:migrate', ['module' => $request->module, '--database' => $request->database]);


        return redirect()->route('migration-builder.index')
            ->with('success', 'Migration created and executed successfully: ' . $fileName);
    }


    protected function generateMigrationContent($tableName, $columns, $database , $module)
    {
        $stub = File::get(__DIR__ . '/../../stubs/migration.stub');
        
        // Replace table name and connection
        $stub = str_replace(
            ['{{table}}', '{{connection}}' , '{{module}}'],
            [$tableName, $database , $module],
            $stub
        );

        // Generate columns
        $columnsCode = [];
        foreach ($columns as $column) {
            $columnCode = "\$table->{$column['type']}('{$column['name']}'";
            
            if (!empty($column['length'])) {
                // Handle enum type specially
                if ($column['type'] === 'enum') {
                    $values = array_map(function($value) {
                        return "'".trim($value)."'";
                    }, explode(',', $column['length']));
                    $columnCode .= ", [".implode(', ', $values)."]";
                } else {
                    $columnCode .= ", {$column['length']}";
                }
            }
            
            $columnCode .= ')';

            if (!empty($column['nullable'])) {
                $columnCode .= '->nullable()';
            }

            if (isset($column['default']) && $column['default'] !== '') {
                $value = $this->formatDefaultValue($column['default'], $column['type']);
                $columnCode .= "->default({$value})";
            }

            $columnsCode[] = $columnCode . ';';
        }

        // Replace columns placeholder
        $stub = str_replace('{{columns}}', implode("\n            ", $columnsCode), $stub);

        return $stub;
    }

    protected function formatDefaultValue($value, $type)
    {
        if (in_array($type, ['integer', 'bigInteger', 'float', 'double', 'decimal'])) {
            return $value;
        }

        if ($type === 'boolean') {
            return $value === 'true' ? 'true' : 'false';
        }

        return "'{$value}'";
    }

}

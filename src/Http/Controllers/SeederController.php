<?php

namespace YoussefElghaly\DatabaseManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use YoussefElghaly\DatabaseManager\Models\DatabaseConnection;
use YoussefElghaly\DatabaseManager\Models\Seeder;

class SeederController extends Controller
{
    public function index()
    {
        $modules = Module::all();
        return view('databasemanager::seeder.index', compact('modules'));
    }


    public function listSeeders(Request $request)
    {
        $modules = Module::all();
        $selectedModule = $request->module;
        $seeders = Seeder::where('module', $selectedModule)->get();
    

        return view('databasemanager::seeder.list', compact('modules', 'selectedModule', 'seeders'));
    }

    public function getTables($moduleName)
    {
        $module = Module::find($moduleName);
        if (!$module) {
            return response()->json(['error' => 'Module not found'], 404);
        }
        $module = DatabaseConnection::where('module_name', $moduleName)->first();
        $moduleConnection = $module->connection_name;


        $tables = DB::connection($moduleConnection)->getSchemaBuilder()->getTables();
        
        return response()->json($tables);
    }

    public function getColumns($moduleName, $tableName)
    {
        $module = DatabaseConnection::where('module_name', $moduleName)->first();
        $moduleConnection = $module->connection_name;

        // Get detailed information for each column
        $columnDetails = [];

        // Get column information directly from the database
        $columnsInfo = DB::connection($moduleConnection)
            ->select("SHOW COLUMNS FROM `$tableName`");

        // Get foreign key information
        $foreignKeys = DB::connection($moduleConnection)
            ->select("
                SELECT 
                    COLUMN_NAME as column_name,
                    REFERENCED_TABLE_NAME as referenced_table,
                    REFERENCED_COLUMN_NAME as referenced_column
                FROM 
                    INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE 
                    TABLE_SCHEMA = DATABASE() AND
                    TABLE_NAME = ? AND
                    REFERENCED_TABLE_NAME IS NOT NULL
            ", [$tableName]);

        // Create a lookup array for foreign keys
        $foreignKeyLookup = [];
        foreach ($foreignKeys as $fk) {
            $foreignKeyLookup[$fk->column_name] = [
                'referenced_table' => $fk->referenced_table,
                'referenced_column' => $fk->referenced_column
            ];
        }

        // Build the column details
        foreach ($columnsInfo as $column) {
            $columnName = $column->Field;

            $columnDetails[$columnName] = [
                'name' => $columnName,
                'type' => $column->Type,
                'is_nullable' => $column->Null === 'YES',
                'default' => $column->Default,
                'extra' => $column->Extra,
                'foreign_key' => $foreignKeyLookup[$columnName] ?? null
            ];
        }

        return response()->json($columnDetails);
    }

    public function store(Request $request)
    {
        $request->validate([
            'module' => 'required|string',
            'table' => 'required|string',
            'seeder_name' => 'required|string',
            'columns' => 'required|array',
            'rows' => 'required|integer|min:1'
        ]);

        $module = Module::find($request->module);
        if (!$module) {
            return back()->with('error', 'Module not found');
        }

        $seederClass = Str::studly($request->seeder_name) . 'Seeder';
        $seederPath = $module->getPath() . "/database/seeders/{$seederClass}.php";

        if (File::exists($seederPath)) {
            return back()->with('error', 'Seeder already exists');
        }

        $columnDefinitions = "";
        foreach ($request->columns as $columnName => $settings) {
            if ($columnName === 'id') {
                continue; // Skip auto-increment ID fields
            }

            if ($settings['type'] === 'faker') {
                $columnDefinitions .= "'$columnName' => \$faker->{$settings['value']},\n                ";
            } elseif ($settings['type'] === 'custom') {
                $columnDefinitions .= "'$columnName' => '{$settings['value']}',\n                ";
            } elseif ($settings['type'] === 'random_number') {
                $columnDefinitions .= "'$columnName' => rand({$settings['min']}, {$settings['max']}),\n                ";
            } elseif ($settings['type'] === 'foreign_key' && isset($settings['module']) && isset($settings['table']) && isset($settings['column'])) {
                $foreignConnectionModel = DatabaseConnection::where('module_name', $settings['module'])->first();

                $foreignConnection = $foreignConnectionModel->connection_name;
                $columnDefinitions .= "'$columnName' => DB::connection('{$foreignConnection}')->table('{$settings['table']}')->inRandomOrder()->value('{$settings['column']}'),\n                ";
            }
        }

        $stub = File::get(__DIR__ . '/../../stubs/seeder.stub');
        $stub = str_replace(
            ['{{seederClass}}', '{{table}}', '{{columns}}', '{{rows}}', '{{module}}'],
            [$seederClass, $request->table, trim($columnDefinitions, ",\n"), $request->rows, $request->module],
            $stub
        );

        File::put($seederPath, $stub);

        Seeder::create([
            'module' => $request->module,
            'seeder_name' => $seederClass,
        ]);

        return back()->with('success', 'Seeder created successfully: ' . $seederClass);
    }


    public function runSeeder($id)
    {
        try {
            $seeder = Seeder::findOrFail($id);
            $module = $seeder->module;
            $seederClass = $seeder->seeder_name;
            $connection = DatabaseConnection::where('module_name', $module)->first();
            Artisan::call('module:seed', [
                'module' => $module,
                '--class' => $seederClass,
                '--database' => $connection->connection_name,
            ]);
            return back()->with('success', "{$seederClass} executed successfully for {$module}!");
        } catch (\Exception $e) {
            dd($e);
            return back()->with('error', "Failed to execute seeder: " . $e->getMessage());
        }
    }

    public function getModules()
    {
        $modules = array_map(function ($module) {
            return $module->getName();
        }, Module::all());

        $modules = array_values($modules);

        return response()->json($modules);
    }

    public function previewSeeder(Request $request)
    {
        $request->validate([
            'module' => 'required|string',
            'table' => 'required|string',
            'seeder_name' => 'required|string',
            'columns' => 'required|array',
            'rows' => 'required|integer|min:1'
        ]);

        $seederClass = Str::studly($request->seeder_name) . 'Seeder';

        $columnDefinitions = "";
        foreach ($request->columns as $columnName => $settings) {
            if ($columnName === 'id') continue; // Skip ID columns

            if ($settings['type'] === 'faker') {
                $columnDefinitions .= "'$columnName' => \$faker->{$settings['value']},\n                ";
            } elseif ($settings['type'] === 'custom') {
                $columnDefinitions .= "'$columnName' => '{$settings['value']}',\n                ";
            } elseif ($settings['type'] === 'random_number') {
                $columnDefinitions .= "'$columnName' => rand({$settings['min']}, {$settings['max']}),\n                ";
            } elseif ($settings['type'] === 'foreign_key' && isset($settings['module']) && isset($settings['table']) && isset($settings['column'])) {
                $foreignConnection = $settings['module'];
                $columnDefinitions .= "'$columnName' => DB::connection('{$foreignConnection}')->table('{$settings['table']}')->inRandomOrder()->value('{$settings['column']}'),\n                ";
            }
        }

        // Create a more realistic preview with multiple rows based on the user input
        $rowsCode = '';
        for ($i = 0; $i < min(3, $request->rows); $i++) {
            $rowsCode .= "            [\n                $columnDefinitions],\n";
        }
        if ($request->rows > 3) {
            $rowsCode .= "            // ... more rows up to {$request->rows} total\n";
        }

        $seederTemplate = <<<PHP
    <?php
    
    namespace Modules\\{$request->module}\\Database\\Seeders;
    
    use Illuminate\Database\Seeder;
    use Illuminate\Support\Facades\DB;
    use Faker\Factory as Faker;
    
    class {$seederClass} extends Seeder
    {
        public function run()
        {
            \$faker = Faker::create();
            
            // Insert {$request->rows} records
            DB::connection('{$request->module}')->table('{$request->table}')->insert([
    $rowsCode
            ]);
        }
    }
    PHP;

        return response()->json(['preview' => $seederTemplate]);
    }

    public function previewSeederList($module, $seederName)
    {
        $filePath = base_path("Modules/{$module}/database/seeders/{$seederName}.php");
    
        if (!file_exists($filePath)) {
            return response("Seeder file not found.", 404);
        }
    
        return response()->file($filePath);
    }
    public function deleteSeeder($module, $seederName)
{
    $filePath = base_path("Modules/{$module}/database/seeders/{$seederName}.php");
    Seeder::where('module', $module)->where('seeder_name', $seederName)->delete();
    if (!file_exists($filePath)) {
        return response()->json(['message' => 'Seeder file not found.'], 404);
    }

    if (unlink($filePath)) {
        return response()->json(['message' => 'Seeder deleted successfully.']);
    } else {
        return response()->json(['message' => 'Failed to delete seeder.'], 500);
    }
}


}

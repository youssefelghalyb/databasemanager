<?php

use Illuminate\Support\Facades\Route;
use YoussefElghaly\DatabaseManager\Http\Controllers\DatabaseController;
use YoussefElghaly\DatabaseManager\Http\Controllers\SeederController;

// The prefix and middleware are now applied in the service provider
// We're just defining the routes here

Route::get('/', [DatabaseController::class, 'index'])->name('database.index');
Route::get('/migrate/{module}', [DatabaseController::class, 'migrate'])->name('database.migrate');
Route::get('/rollback/{module}', [DatabaseController::class, 'rollback'])->name('database.rollback');
Route::get('/status/{module}', [DatabaseController::class, 'status'])->name('database.status');

Route::get('/create-migration', [DatabaseController::class, 'createMigration'])->name('migration-builder.index');
Route::post('/create-migration', [DatabaseController::class, 'store'])->name('migration-builder.create');

Route::prefix('seeder')->group(function () {
    Route::get('/', [SeederController::class, 'index'])->name('seeder.index');
    Route::get('/tables/{moduleName}', [SeederController::class, 'getTables'])->name('seeder.tables');
    Route::get('/columns/{moduleName}/{table}', [SeederController::class, 'getColumns'])->name('seeder.columns');
    
    Route::get('/modules', [SeederController::class, 'getModules']);
    Route::post('/preview', [SeederController::class, 'previewSeeder']);
    
    Route::get('/list', [SeederController::class, 'listSeeders'])->name('seeder.list');
    Route::get('/preview/{module}/{seederName}', [SeederController::class, 'previewSeederList']);
    Route::delete('/delete/{module}/{seederName}', [SeederController::class, 'deleteSeeder']);
    
    Route::post('/store', [SeederController::class, 'store'])->name('seeder.store');
    Route::post('/run/{id}', [SeederController::class, 'runSeeder'])->name('seeder.run');
});
<?php

namespace YoussefElghaly\DatabaseManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DatabaseConnection extends Model 
{
    use HasFactory;

    protected $fillable = [
        'module_name', 
        'connection_name', 
        'host', 
        'database', 
        'username', 
        'password'
    ];

    protected $hidden = ['password'];
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = config('databasemanager.connection');
    }
}
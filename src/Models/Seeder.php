<?php

namespace YoussefElghaly\DatabaseManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seeder extends Model 
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'seeders';
    protected $primaryKey = 'id';
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = config('databasemanager.connection');
    }
}
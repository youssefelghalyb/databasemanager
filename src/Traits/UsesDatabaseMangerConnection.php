<?php

namespace YoussefElghaly\DatabaseManager\Traits;

trait UsesDatabaseMangerConnection 
{
    protected static function bootUsesDatabaseMangerConnection()
    {
        static::creating(function ($model) {
            $model->setConnection(config('databasemanager.connection'));
        });
    }

    public function getConnectionName()
    {
        return config('databasemanager.connection');
    }
}
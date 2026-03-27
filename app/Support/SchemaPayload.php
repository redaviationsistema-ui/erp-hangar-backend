<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

class SchemaPayload
{
    private static array $columnsCache = [];

    public static function forModel(Model $model, array $payload): array
    {
        $table = $model->getTable();

        if (!isset(self::$columnsCache[$table])) {
            self::$columnsCache[$table] = array_flip(Schema::getColumnListing($table));
        }

        return Arr::only($payload, array_keys(self::$columnsCache[$table]));
    }
}

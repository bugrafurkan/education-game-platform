<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'key',
        'value',
    ];

    // Boolean değerleri otomatik dönüştür
    protected function castAttribute($key, $value)
    {
        if ($key === 'value') {
            // 'true' ve 'false' string'lerini boolean'a dönüştür
            if ($value === 'true') return true;
            if ($value === 'false') return false;

            // JSON verisi ise diziye dönüştür
            if ($this->isJson($value)) {
                return json_decode($value, true);
            }
        }

        return parent::castAttribute($key, $value);
    }

    private function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}

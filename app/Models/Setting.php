<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function getValue($key)
    {
        return self::where('key', $key)->first()->value ?? null;
    }

    public static function setValue($key, $value)
    {
        $setting = self::updateOrCreate(['key' => $key], ['value' => $value]);
        return $setting;
    }
}

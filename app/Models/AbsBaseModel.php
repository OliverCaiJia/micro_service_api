<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

abstract class AbsBaseModel extends Eloquent
{

    public $timestamps = false;
    public $incrementing = false;

    public function scopeMultiwhere($query, $arr)
    {
        if (!is_array($arr))
        {
            return $query;
        }

        foreach ($arr as $key => $value) 
        {
            $query = $query->where($key, $value);
        }
        return $query;
    }

    public static function createModel(array $attributes)
    {
        return new static($attributes);
    }

}

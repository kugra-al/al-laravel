<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $fillable = [];

    public static function getKeyCacheName() {
        return 'item_d-itm-keys';
    }

    public static function getValueCacheName() {
        return 'item_d-itm-values';
    }
}

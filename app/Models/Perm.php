<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PermItem;
use App\Models\PermLog;

class Perm extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(PermItem::class);
    }

    public function logs()
    {
        return $this->hasMany(PermLog::class);
    }
}

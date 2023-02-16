<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Perm;

class PermItem extends Model
{
    use HasFactory;

    public function perm()
    {
        return $this->belongsTo(Perm::class);
    }
}

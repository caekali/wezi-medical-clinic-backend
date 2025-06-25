<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = [
        'name',
        'description'
    ];

    public function doctors(): HasMany
    {
        $this->hasMany(Doctor::class);
    }

    public function services(): HasMany
    {
        $this->hasMany(Service::class);
    }
}

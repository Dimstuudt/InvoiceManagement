<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = ['name', 'code', 'description'];

    public function projectCategories()
    {
        return $this->hasMany(ProjectCategory::class);
    }
}

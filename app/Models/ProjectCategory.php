<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectCategory extends Model
{
    protected $fillable = ['name', 'code', 'description', 'company_id'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

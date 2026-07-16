<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectCategory extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'code', 'description', 'company_id'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

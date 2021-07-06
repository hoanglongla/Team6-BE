<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceCategory extends Model
{
    use HasFactory, SoftDeletes;
    public $table = "service_categories";

    protected $fillable = [
        "name" , "parent_category", "note" , "user_id"
    ];
}

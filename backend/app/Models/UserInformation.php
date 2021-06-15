<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserInformation extends Model
{
    use HasFactory, SoftDeletes;

    public $table = "user_informations";
    public $timestamps = true;

    protected $fillable = [
        "tail_id",
        'fullname',
        'company',
        'phone',
        'address',
        'bio',
        'note'
    ];

}
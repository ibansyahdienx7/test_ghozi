<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDelete extends Model
{
    use HasFactory;
    protected $table = 'user_deletes';
    protected $guarded = ['id'];
}

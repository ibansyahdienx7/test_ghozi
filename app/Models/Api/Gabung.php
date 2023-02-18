<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gabung extends Model
{
    use HasFactory;

    protected $table = 'gabungs';
    protected $guarded = ['id'];
}
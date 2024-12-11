<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aseguradora extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'aseguradora';
    protected $primaryKey = 'ASEGURADORA';

}

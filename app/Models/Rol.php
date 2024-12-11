<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    public $timestamps = false;
    protected $table = 'roles';

    public function usuarios(){

        return $this->hasMany(User::class, 'roles_id');
    }
}

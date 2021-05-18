<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{

    use SoftDeletes;

    protected $table = 'customer';

    public function user() {
        return $this->hasMany(User::class);
    }

}

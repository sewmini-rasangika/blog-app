<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Activity;

class Profile extends Model
{
    protected $guarded = [];

public function user()
{
    return $this->belongsTo(User::class);
}
}

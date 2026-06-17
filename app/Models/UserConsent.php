<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserConsent extends Model
{
    protected $table = 'user_consents';

    public $timestamps = false;

    protected $fillable = ['user_id', 'terms_version', 'agreed_at'];
}

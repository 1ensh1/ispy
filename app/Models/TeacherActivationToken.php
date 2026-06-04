<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherActivationToken extends Model
{
    protected $table = 'teacher_activation_tokens';

    public $timestamps = false;

    protected $fillable = ['teacher_id', 'token'];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}

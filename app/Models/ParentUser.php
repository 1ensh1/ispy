<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentUser extends Model
{
    protected $table = 'parents';

    protected $fillable = ['user_id', 'name', 'contact_number'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'parent_id');
    }
}

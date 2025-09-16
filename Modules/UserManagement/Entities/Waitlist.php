<?php

namespace Modules\UserManagement\Entities;

use Illuminate\Database\Eloquent\Model;

class Waitlist extends Model
{
    protected $table = 'waitlist';

    protected $fillable = [
        'fullName',
        'email',
        'phone',
        'address',
        'vehicle',
        'year',
        'model',
        'role',
        'createdAt',
    ];
}

<?php

namespace Modules\UserManagement\Entities;

use Illuminate\Database\Eloquent\Model;

class Waitlist extends Model
{
    protected $table = 'waitlists';

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

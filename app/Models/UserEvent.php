<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class UserEvent extends Model
{
    use HasUuid;

    protected $fillable = ['user_id', 'event_id'];
}

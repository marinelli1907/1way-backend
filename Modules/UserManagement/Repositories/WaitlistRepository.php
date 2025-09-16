<?php

namespace Modules\UserManagement\Repositories;

use App\Repository\Eloquent\BaseRepository;
use Modules\UserManagement\Entities\Waitlist;
use Modules\UserManagement\Repository\WaitlistRepositoryInterface;

class WaitlistRepository extends BaseRepository implements WaitlistRepositoryInterface
{
    public function __construct(Waitlist $model)
    {
        parent::__construct($model);
    }
}

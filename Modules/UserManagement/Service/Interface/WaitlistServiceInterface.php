<?php

namespace Modules\UserManagement\Service\Interface;

use App\Service\BaseServiceInterface;

interface WaitlistServiceInterface extends BaseServiceInterface
{
    // No need to declare index() — we inherit getAll() from BaseServiceInterface
}

<?php

namespace App\Contracts\Repositories;

use App\Models\User;

interface CartRepositoryInterface
{
    public function getForUser(User $user);
}

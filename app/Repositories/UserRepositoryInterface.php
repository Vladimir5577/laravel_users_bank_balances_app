<?php

namespace App\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    public function getById($id): User|null;
}

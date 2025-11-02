<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function getById($id): User|null
    {
        return User::query()->find($id);
    }
}

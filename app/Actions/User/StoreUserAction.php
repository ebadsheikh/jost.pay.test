<?php

namespace App\Actions\User;

use App\Models\User;

class StoreUserAction
{
    /**
     * Handle the incoming request.
     * @param array $data
     * @return User
     */
    public function handle(array $data): User
    {
        $user = User::create($data);

        return $user;
    }
}

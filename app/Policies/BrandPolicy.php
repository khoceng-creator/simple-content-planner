<?php

namespace App\Policies;

use App\Models\Brand;
use App\Models\User;

class BrandPolicy
{
    public function view(User $user, Brand $brand): bool
    {
        return $brand->user_id === $user->id;
    }

    public function update(User $user, Brand $brand): bool
    {
        return $this->view($user, $brand);
    }

    public function delete(User $user, Brand $brand): bool
    {
        return $this->view($user, $brand);
    }
}

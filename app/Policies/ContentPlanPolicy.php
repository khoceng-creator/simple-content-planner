<?php

namespace App\Policies;

use App\Models\Brand;
use App\Models\ContentPlan;
use App\Models\User;

class ContentPlanPolicy
{
    public function create(User $user, Brand $brand): bool
    {
        return $brand->user_id === $user->id;
    }

    public function view(User $user, ContentPlan $contentPlan): bool
    {
        return $contentPlan->brand->user_id === $user->id;
    }

    public function update(User $user, ContentPlan $contentPlan): bool
    {
        return $this->view($user, $contentPlan);
    }

    public function delete(User $user, ContentPlan $contentPlan): bool
    {
        return $this->view($user, $contentPlan);
    }

    public function toggleStatus(User $user, ContentPlan $contentPlan): bool
    {
        return $this->view($user, $contentPlan);
    }
}

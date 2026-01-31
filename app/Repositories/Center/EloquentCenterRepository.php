<?php

namespace App\Repositories\Center;

use App\Models\Center;
use App\Models\User;
use Illuminate\Support\Collection;

class EloquentCenterRepository implements CenterRepositoryInterface
{
    public function getUserCenters(int $userId): Collection
    {
        $user = User::find($userId);
        if (!$user) {
            \Log::error("User not found for ID: $userId");
            return collect([]);
        }

        return $user->centers()->get();
    }

    public function findById(int $id): ?Center
    {
        $center = Center::find($id);
        if (!$center) {
            \Log::error("Center not found for ID: $id");
        }
        return $center;
    }
}
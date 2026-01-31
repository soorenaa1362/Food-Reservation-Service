<?php

namespace App\Repositories\Center;

use App\Models\Center;
use Illuminate\Support\Collection;

interface CenterRepositoryInterface
{
    public function getUserCenters(int $userId): Collection;
    public function findById(int $id): ?Center;
}
<?php

namespace App\Services\Menu; 

use App\Repositories\Menu\MenuRepositoryInterface;
use Illuminate\Support\Facades\Log;

class MenuService
{
    protected $menuRepository;

    public function __construct(MenuRepositoryInterface $menuRepository)
    {
        $this->menuRepository = $menuRepository;
    }

    public function loadAndGetMenus(string $centerId, \DateTime $startDate, \DateTime $endDate): array
    {
        // بارگذاری منوها از JSON
        $loaded = $this->menuRepository->loadMenusFromJson($centerId);
        if (!$loaded) {
            Log::error("Failed to load menus for center_id: $centerId");
            return [];
        }

        // دریافت منوها از دیتابیس
        $days = $this->menuRepository->getMenusForCenter($centerId, $startDate, $endDate);
        if (empty($days)) {
            Log::error("No menu data available for center_id: $centerId");
            return [];
        }

        Log::info("Menus retrieved successfully for center_id: $centerId");
        return $days;
    }
}
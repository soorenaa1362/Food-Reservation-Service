<?php

namespace App\Services\Center;

use App\Repositories\Center\CenterRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class CenterService
{
    protected $centerRepository;

    public function __construct(CenterRepositoryInterface $centerRepository)
    {
        $this->centerRepository = $centerRepository;
    }

    public function getUserCenters(): Collection
    {
        $user = Auth::user();
        if (!$user) {
            \Log::error("No authenticated user found");
            return collect([]);
        }
        return $this->centerRepository->getUserCenters($user->id);
    }

    public function selectCenter(int $centerId): bool
    {
        $center = $this->centerRepository->findById($centerId);
        if (!$center) {
            \Log::error("Center not found: $centerId");
            return false;
        }

        $user = Auth::user();
        if (!$user) {
            \Log::error("No authenticated user found");
            return false;
        }

        if (!$user->centers()->where('center_id', $centerId)->exists()) {
            \Log::error("Center $centerId is not associated with user {$user->id}");
            return false;
        }

        // ذخیره کل اطلاعات مرکز در سشن — کاملاً درست و حرفه‌ای
        session([
            'selected_center' => [
                'id'          => $center->id,
                'name'        => $center->name,
                'address'     => $center->address,
                'description' => $center->description,
                // هر فیلد دیگه‌ای که نیاز داری (مثل logo, phone, ...)
            ]
        ]);

        \Log::info("User {$user->id} selected center $centerId");
        return true;
    }

    // متد کمکی برای دسترسی راحت‌تر
    // public function getSelectedCenter(): ?array
    // {
    //     return session('selected_center');
    // }

    // public function getSelectedCenter()
    // {
    //     $selected = session('selected_center');
        
    //     // اگر آبجکت مدل است
    //     if ($selected instanceof \App\Models\Center) {
    //         return [
    //             'id' => $selected->id,
    //             'name' => $selected->name,
    //             // دیگر فیلدهای مورد نیاز
    //         ];
    //     }
        
    //     // اگر آرایه است یا null
    //     return $selected;
    // }

    public function getSelectedCenter(): array
    {
        $centerId = session('selected_center_id');
        
        if (!$centerId) {
            throw new \Exception('هیچ مرکزی انتخاب نشده است.');
        }
        
        $center = session('selected_center');
        
        if (!$center) {
            $center = Center::findOrFail($centerId);
            session(['selected_center' => $center]);
        }
        
        return [
            'id'            => $center->id,
            'name'          => $center->name,
            'type'          => $center->type,
            'address'       => $center->address,
            'description'   => $center->description,
        ];
    }
}
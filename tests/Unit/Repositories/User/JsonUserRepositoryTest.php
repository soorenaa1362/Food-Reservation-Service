<?php

namespace Tests\Unit\Repositories\User;

use App\Repositories\User\JsonUserRepository;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class JsonUserRepositoryTest extends TestCase
{
    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::disk('local')->put('users.json', json_encode([
            [
                'name' => 'مجید',
                'family' => 'حیدری نسب',
                'national_code' => '0941086690',
                'phone_number' => '09366021860',
            ],
            [
                'name' => 'سید حسین',
                'family' => 'غیرتمند',
                'national_code' => '5229710081',
                'phone_number' => '09123456790',
            ],
        ]));

        $this->repository = new JsonUserRepository();
    }

    #[Test]
    public function it_can_find_user_by_national_code()
    {
        $nationalCode = '0941086690';
        $user = $this->repository->findByNationalCode($nationalCode);

        $this->assertNotNull($user);
        $this->assertIsArray($user);
        $this->assertEquals('مجید', $user['name']);
        $this->assertEquals('حیدری نسب', $user['family']);
        $this->assertEquals('0941086690', $user['national_code']);
        $this->assertEquals('09366021860', $user['phone_number']);
    }

    #[Test]
    public function it_returns_null_for_non_existent_national_code()
    {
        $nationalCode = '9999999999';
        $user = $this->repository->findByNationalCode($nationalCode);

        $this->assertNull($user);
    }
}
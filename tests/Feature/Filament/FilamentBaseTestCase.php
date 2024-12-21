<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\User;
use Tests\TestCase;

abstract class FilamentBaseTestCase extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->actingAs($this->user);
    }
}

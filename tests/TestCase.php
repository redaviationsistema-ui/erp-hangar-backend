<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    private const DEFAULT_API_USER_EMAIL = 'administradoror@redaviation.com';

    /**
     * Seed the database and authenticate a default API user for Sanctum-protected routes.
     *
     * @param  list<string>|class-string<\Illuminate\Database\Seeder>|string  $class
     * @return $this
     */
    public function seed($class = 'Database\\Seeders\\DatabaseSeeder')
    {
        parent::seed($class);

        $this->authenticateAsUser(self::DEFAULT_API_USER_EMAIL);

        return $this;
    }

    protected function authenticateAsUser(User|string $user): User
    {
        if (is_string($user)) {
            $user = User::query()
                ->where('email', $user)
                ->firstOrFail();
        }

        Sanctum::actingAs($user);

        return $user;
    }
}

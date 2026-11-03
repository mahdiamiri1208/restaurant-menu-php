<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    public function test_logged_out_visitors_cannot_access_admin_resources(): void
    {
        $this->get('/admin/products')
            ->assertRedirect('/admin/login');
    }

    public function test_registration_is_not_available(): void
    {
        $this->get('/admin/register')->assertNotFound();
    }
}

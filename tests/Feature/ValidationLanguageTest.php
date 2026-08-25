<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationLanguageTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_validation_errors_are_in_arabic(): void
    {
        $this->from(route('login'))
            ->post('/login', [])
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertSame('البريد الإلكتروني مطلوب.', session('errors')->first('email'));
        $this->assertSame('كلمة المرور مطلوبة.', session('errors')->first('password'));
    }

    public function test_generic_required_rule_uses_arabic_attribute_names(): void
    {
        $this->from(route('register.student'))
            ->post(route('register.student'), [])
            ->assertSessionHasErrors('name');

        $this->assertSame('الاسم الكامل مطلوب.', session('errors')->first('name'));
    }
}

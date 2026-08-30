<?php

namespace Tests\Feature;

use Tests\TestCase;

class DesignSystemShowcaseTest extends TestCase
{
    public function test_design_system_showcase_is_public_and_presents_the_product(): void
    {
        $this->get(route('design-system'))
            ->assertOk()
            ->assertSee('تعلّم بطريقة أوضح', false)
            ->assertSee('أ. محمود طارق', false)
            ->assertSee('خطط الوصول', false)
            ->assertSee('غزة', false)
            ->assertSee('الضفة الغربية', false)
            ->assertSee('محتوى محمي للمشتركين', false)
            ->assertSee('images/nageeb/hero/hero-student-studying.png', false)
            ->assertSee('images/nageeb/courses/arabic.png', false)
            ->assertDontSee('Lorem ipsum', false)
            ->assertDontSee('العرض البصري لنظام تصميم نجيب', false);
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

class BrandingTest extends TestCase
{
    public function test_legacy_branding_is_normalized_case_insensitively(): void
    {
        $this->assertSame('PhysioSource', brand_text('Physio Academy'));
        $this->assertSame('PhysioSource', brand_text('PHYSIO ACADEMY'));
        $this->assertSame('PhysioSource', brand_text('physioacademy'));
        $this->assertSame(['PhysioSource', 'PhysioSource'], brand_text(['PhysioAcademy', 'PhysioSource']));
    }
}

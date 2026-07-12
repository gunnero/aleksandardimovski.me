<?php

namespace Tests\Feature;

use Tests\TestCase;

class ResumePrintTest extends TestCase
{
    public function test_resume_print_styles_are_theme_independent(): void
    {
        $css = file_get_contents(resource_path('css/print.css'));

        $this->assertStringContainsString('@media print', $css);
        $this->assertStringContainsString('size: A4', $css);
        $this->assertStringContainsString('background: #fff !important', $css);
        $this->assertStringContainsString('color: #111 !important', $css);
        $this->assertStringContainsString("html[data-theme='dark']", $css);
        $this->assertStringContainsString("html[data-theme='light']", $css);
        $this->assertStringContainsString('font-family: Arial, Helvetica, sans-serif !important', $css);
        $this->assertStringContainsString('body:has(.resume-final) main > :not(.resume-final)', $css);
    }

    public function test_print_stylesheet_is_loaded_after_screen_styles(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));
        $layout = file_get_contents(resource_path('views/components/layout.blade.php'));

        $this->assertStringNotContainsString("@import './print.css';", $css);
        $this->assertSame(0, substr_count($css, '@media print'));
        $this->assertStringContainsString("'resources/css/app.css', 'resources/css/print.css', 'resources/js/app.js'", $layout);
    }
}

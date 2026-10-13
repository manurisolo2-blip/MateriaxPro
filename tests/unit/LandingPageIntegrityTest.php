<?php

use CodeIgniter\Test\CIUnitTestCase;

final class LandingPageIntegrityTest extends CIUnitTestCase
{
    public function testWelcomeMessageRendersCorrectly(): void
    {
        $html = view('welcome_message', ['pageTitle' => 'MateriaX']);
        $this->assertNotEmpty($html, 'La vista no debe estar vacía.');
        $this->assertStringContainsString('<!DOCTYPE html>', $html);
    }

    public function testCriticalSectionsExist(): void
    {
        $html = view('welcome_message');
        $sections = ['id="inicio"', 'id="feed"', 'id="categorias"', 'id="ecosistema"', 'id="metricas"', 'id="contacto"'];

        foreach ($sections as $sec) {
            $this->assertStringContainsString($sec, $html, "Falta la sección {$sec} en la página.");
        }
        $this->assertStringNotContainsString('id="entregables"', $html, 'La sección entregables no debe estar presente.');
    }

    public function testPolymerLotsAndMetricsExist(): void
    {
        $html = view('welcome_message');
        $expectedTokens = [
            '#FIL-108',
            '#ROT-204',
            '#MOD-019',
            '#FIL-092',
            '24.800 kg',
            '42',
            'PEAD',
            'PETG',
        ];

        foreach ($expectedTokens as $token) {
            $this->assertStringContainsString($token, $html, "Falta el dato de polímero/métrica '{$token}'");
        }
    }

    public function testInstitutionalContentAndLinksExist(): void
    {
        $html = view('welcome_message');
        $this->assertStringContainsString('Instituto Técnico Río Tercero', $html);
        $this->assertStringContainsString('docs/DER.md', $html);
    }

    public function testNoB2BMentionsInRenderedHtml(): void
    {
        $html = view('welcome_message');
        $this->assertDoesNotMatchRegularExpression('/\bb2b\b/i', $html, 'No debe haber menciones de B2B en el HTML renderizado.');
    }

    public function testStylesheetExistsAndHasCoreClasses(): void
    {
        $cssPath = ROOTPATH . 'public/css/materiax.css';
        $this->assertFileExists($cssPath);
        $css = file_get_contents($cssPath);

        $coreClasses = [
            '.marketplace-layout',
            '.marketplace-sidebar',
            '.marketplace-feed-area',
            '.marketplace-social-rail',
            '.hero-wrapper',
            '.exchange-feed-grid',
            '.btn-primary',
            '.btn-secondary',
        ];

        foreach ($coreClasses as $cls) {
            $this->assertStringContainsString($cls, $css, "La clase CSS {$cls} debe existir en materiax.css");
        }
    }
}

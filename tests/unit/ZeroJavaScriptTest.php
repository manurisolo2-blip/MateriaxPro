<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Prueba automatizada para certificar la arquitectura server-side de MateriaX:
 * Los módulos académicos y de negocio centrales (Auth, Catálogo/CRUD de Productos,
 * Sedes/Plantas, Certificados Ambientales, Panel Admin) funcionan 100% con PHP MVC nativo
 * sin depender de JavaScript para validaciones, reglas de negocio ni persistencia.
 *
 * @internal
 */
final class ZeroJavaScriptTest extends CIUnitTestCase
{
    /**
     * Verifica que los módulos centrales de negocio en app/ y plantillas base
     * no contengan archivos JavaScript que sustituyan la lógica del controlador MVC.
     */
    public function testNoJavaScriptFilesInCoreApp(): void
    {
        $scanDirs = [
            APPPATH . 'Controllers',
            APPPATH . 'Models',
            ROOTPATH . 'js' . DIRECTORY_SEPARATOR,
        ];

        foreach ($scanDirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
            foreach ($iterator as $file) {
                if ($file->isFile() && strtolower($file->getExtension()) === 'js') {
                    $this->fail('Se encontró un archivo JavaScript no permitido en el núcleo MVC: ' . $file->getPathname());
                }
            }
        }

        $this->assertTrue(true, 'No se encontraron scripts en los controladores ni modelos del sistema.');
    }

    /**
     * Verifica que ninguna vista del sistema contenga manejadores inline peligrosos
     * (onclick, onsubmit, onload o pseudo-protocolo javascript:)
     */
    public function testNoInlineEventHandlersInViews(): void
    {
        $viewsDir = APPPATH . 'Views';
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));

        $forbiddenPatterns = [
            'onsubmit='   => 'Evento inline onsubmit',
            'onclick='    => 'Evento inline onclick',
            'onload='     => 'Evento inline onload',
            'javascript:' => 'Pseudo-protocolo javascript:',
        ];

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());

                foreach ($forbiddenPatterns as $pattern => $description) {
                    $this->assertFalse(
                        stripos($content, $pattern) !== false,
                        "Se detectó {$description} en la vista: " . $file->getPathname()
                    );
                }
            }
        }
    }

    /**
     * Verifica que los módulos centrales (Auth, Productos, Plantas, Certificados, Admin)
     * no contengan etiquetas <script>, garantizando que toda la lógica de negocio es PHP.
     */
    public function testCoreViewsHaveNoScriptTags(): void
    {
        $coreDirs = [
            APPPATH . 'Views' . DIRECTORY_SEPARATOR . 'auth',
            APPPATH . 'Views' . DIRECTORY_SEPARATOR . 'productos',
            APPPATH . 'Views' . DIRECTORY_SEPARATOR . 'plantas',
            APPPATH . 'Views' . DIRECTORY_SEPARATOR . 'certificados',
            APPPATH . 'Views' . DIRECTORY_SEPARATOR . 'admin',
            APPPATH . 'Views' . DIRECTORY_SEPARATOR . 'perfil',
        ];

        foreach ($coreDirs as $dir) {
            if (!is_dir($dir)) continue;

            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $content = file_get_contents($file->getPathname());
                    $this->assertFalse(
                        stripos($content, '<script') !== false,
                        "La vista central no debe contener etiquetas <script>: " . $file->getPathname()
                    );
                }
            }
        }
    }

    /**
     * Verifica que la vista principal welcome_message no contenga etiquetas script
     */
    public function testWelcomeMessageViewHasNoScripts(): void
    {
        $html = view('welcome_message', ['pageTitle' => 'MateriaX']);

        $this->assertStringNotContainsStringIgnoringCase('<script', $html, 'La vista welcome_message no debe contener etiquetas <script>');
        $this->assertStringNotContainsStringIgnoringCase('javascript:', $html, 'La vista welcome_message no debe contener javascript:');
    }
}

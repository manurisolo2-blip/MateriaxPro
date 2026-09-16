<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Prueba automatizada para certificar que el proyecto MateriaX
 * no contiene ningún archivo, etiqueta ni rastro de JavaScript.
 *
 * @internal
 */
final class ZeroJavaScriptTest extends CIUnitTestCase
{
    /**
     * Verifica que no existan archivos con extensión .js en app/, public/ o la raíz
     */
    public function testNoJavaScriptFilesExistInAppOrPublic(): void
    {
        $scanDirs = [
            APPPATH,
            ROOTPATH . 'public' . DIRECTORY_SEPARATOR,
            ROOTPATH . 'js' . DIRECTORY_SEPARATOR,
        ];

        foreach ($scanDirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
            foreach ($iterator as $file) {
                if ($file->isFile() && strtolower($file->getExtension()) === 'js') {
                    $this->fail('Se encontró un archivo JavaScript no permitido: ' . $file->getPathname());
                }
            }
        }

        $this->assertTrue(true, 'No se encontraron archivos .js en las rutas del sistema.');
    }

    /**
     * Verifica que ninguna vista en app/Views contenga etiquetas <script> o manejadores inline
     */
    public function testNoScriptTagsOrInlineEventsInViews(): void
    {
        $viewsDir = APPPATH . 'Views';
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));

        $forbiddenPatterns = [
            '<script'    => 'Etiqueta <script>',
            'onsubmit='  => 'Evento inline onsubmit',
            'onclick='   => 'Evento inline onclick',
            'onload='    => 'Evento inline onload',
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
     * Verifica que la vista principal welcome_message no contenga etiquetas script
     */
    public function testWelcomeMessageViewHasNoScripts(): void
    {
        $html = view('welcome_message', ['pageTitle' => 'MateriaX']);

        $this->assertStringNotContainsStringIgnoringCase('<script', $html, 'La vista welcome_message no debe contener etiquetas <script>');
        $this->assertStringNotContainsStringIgnoringCase('javascript:', $html, 'La vista welcome_message no debe contener javascript:');
    }
}

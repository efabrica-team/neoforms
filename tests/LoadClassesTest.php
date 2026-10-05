<?php

namespace Tests\Efabrica\NeoForms;

use Efabrica\NeoForms\DI\NeoFormLatteExtension;
use Latte\Engine;
use Latte\Loaders\StringLoader;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Signature mismatches with nette/forms only fatal when a class is loaded, so load them all.
 * Run with `composer update --prefer-lowest` to catch too-loose version constraints.
 */
class LoadClassesTest extends TestCase
{
    public function testAllClassesLoad(): void
    {
        $src = dirname(__DIR__) . '/src/';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src)) as $file) {
            // Latte 2 macro set, its parent classes no longer exist
            if ($file->getExtension() !== 'php' || $file->getFilename() === 'NeoFormMacroSet.php') {
                continue;
            }
            $class = 'Efabrica\\NeoForms\\' . strtr(substr($file->getPathname(), strlen($src), -4), '/', '\\');
            $this->assertTrue(class_exists($class) || trait_exists($class) || interface_exists($class), $class);
        }
    }

    public function testNeoFormTagCompiles(): void
    {
        $latte = new Engine();
        $latte->setLoader(new StringLoader());
        $latte->addExtension(new NeoFormLatteExtension());
        $this->assertStringContainsString('neoFormRenderer', $latte->compile("{neoForm foo}\n{formRow bar}\n{/neoForm}"));
    }
}

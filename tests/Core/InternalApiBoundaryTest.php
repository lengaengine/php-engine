<?php

declare(strict_types=1);

namespace Lenga\Engine\Tests\Core;

use PHPUnit\Framework\TestCase;

final class InternalApiBoundaryTest extends TestCase
{
    public function testEngineHeaderIsNotShippedInPackageSource(): void
    {
        self::assertFileDoesNotExist(__DIR__ . '/../../src/EngineHeader.php');
    }

    public function testPackageSourceDoesNotDeclareInternalNativeFunctions(): void
    {
        $sourceRoot = realpath(__DIR__ . '/../../src');
        self::assertIsString($sourceRoot);

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceRoot, \FilesystemIterator::SKIP_DOTS)
        );

        $violations = [];
        foreach ($iterator as $file) {
            if (!$file instanceof \SplFileInfo || $file->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());
            if ($contents === false) {
                continue;
            }

            if (preg_match('/\bfunction\s+lenga_internal_[A-Za-z0-9_]*\s*\(/', $contents) === 1) {
                $violations[] = substr($file->getPathname(), strlen($sourceRoot) + 1);
            }
        }

        self::assertSame([], $violations, 'Internal native functions must not be declared in package source.');
    }
}

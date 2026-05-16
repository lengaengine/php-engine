<?php

declare(strict_types=1);

namespace Lenga\Engine\Tests\Core;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class InternalApiBoundaryTest extends TestCase
{
    public function testEngineHeaderIsNotShippedInPackageSource(): void
    {
        self::assertFileDoesNotExist(__DIR__ . '/../../src/EngineHeader.php');
    }

    public function testPackageSourceDoesNotDeclareInternalNativeFunctions(): void
    {
        $sourceRoot = $this->sourceRoot();
        $violations = [];
        $pattern = '/\bfunction\s+' . preg_quote($this->nativePrefix(), '/') . '[A-Za-z0-9_]*\s*\(/';

        foreach ($this->sourceFiles($sourceRoot) as $file) {
            $contents = file_get_contents($file->getPathname());
            if ($contents === false) {
                continue;
            }

            if (preg_match($pattern, $contents) === 1) {
                $violations[] = $this->relativeSourcePath($file, $sourceRoot);
            }
        }

        self::assertSame([], $violations, 'Internal native functions must not be declared in package source.');
    }

    public function testPackageSourceDoesNotInvokeInternalNativeFunctionsDirectly(): void
    {
        $sourceRoot = $this->sourceRoot();
        $violations = [];

        foreach ($this->sourceFiles($sourceRoot) as $file) {
            $contents = file_get_contents($file->getPathname());
            if ($contents === false) {
                continue;
            }

            foreach ($this->findDirectInternalNativeCalls($contents) as $functionName) {
                $violations[] = $this->relativeSourcePath($file, $sourceRoot) . ':' . $functionName;
            }
        }

        self::assertSame(
            [],
            $violations,
            'Internal native functions must be invoked through NativeEngine::call() so unavailable-runtime errors stay clean.'
        );
    }

    public function testNativePrefixIsPrivateToNativeEngine(): void
    {
        $sourceRoot = $this->sourceRoot();
        $violations = [];

        foreach ($this->sourceFiles($sourceRoot) as $file) {
            $relativePath = $this->relativeSourcePath($file, $sourceRoot);
            if ($relativePath === 'Core/NativeEngine.php') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());
            if ($contents === false) {
                continue;
            }

            if (str_contains($contents, $this->nativePrefix())) {
                $violations[] = $relativePath;
            }
        }

        self::assertSame([], $violations, 'Only NativeEngine should know the physical native bridge prefix.');
    }

    private function sourceRoot(): string
    {
        $sourceRoot = realpath(__DIR__ . '/../../src');
        self::assertIsString($sourceRoot);

        return $sourceRoot;
    }

    private function relativeSourcePath(SplFileInfo $file, string $sourceRoot): string
    {
        return str_replace('\\', '/', substr($file->getPathname(), strlen($sourceRoot) + 1));
    }

    /**
     * @return iterable<SplFileInfo>
     */
    private function sourceFiles(string $sourceRoot): iterable
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceRoot, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file instanceof SplFileInfo || $file->getExtension() !== 'php') {
                continue;
            }

            yield $file;
        }
    }

    /**
     * @return list<string>
     */
    private function findDirectInternalNativeCalls(string $contents): array
    {
        $tokens = token_get_all($contents);
        $calls = [];
        $prefix = $this->nativePrefix();
        $count = count($tokens);

        for ($index = 0; $index < $count; ++$index) {
            $token = $tokens[$index];
            if (!is_array($token)) {
                continue;
            }

            $functionName = null;
            if ($token[0] === T_NAME_FULLY_QUALIFIED && str_starts_with($token[1], '\\' . $prefix)) {
                $functionName = substr($token[1], 1);
            } elseif ($token[0] === T_STRING && str_starts_with($token[1], $prefix)) {
                $functionName = $token[1];
            }

            if ($functionName === null) {
                continue;
            }

            $next = $this->nextNonTriviaIndex($tokens, $index + 1);
            if ($next !== null && $tokens[$next] === '(') {
                $calls[] = $functionName;
            }
        }

        return $calls;
    }

    /**
     * @param list<mixed> $tokens
     */
    private function nextNonTriviaIndex(array $tokens, int $start): ?int
    {
        $count = count($tokens);
        for ($index = $start; $index < $count; ++$index) {
            $token = $tokens[$index];
            if (is_array($token) && in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }

            return $index;
        }

        return null;
    }

    private function nativePrefix(): string
    {
        return 'lenga' . '_internal_';
    }
}

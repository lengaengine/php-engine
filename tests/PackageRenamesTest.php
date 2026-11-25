<?php

declare(strict_types=1);

namespace Lenga\Engine\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The renames the package lists for the editor's project upgrade must
 * describe the package as it is: the new name exists and the old one is gone.
 */
final class PackageRenamesTest extends TestCase
{
    private const string NAMESPACE_PREFIX = 'Lenga\\Engine\\';

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function renames(): array
    {
        $manifest = json_decode(
            (string) file_get_contents(dirname(__DIR__) . '/composer.json'),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $cases = [];
        foreach ($manifest['extra']['lenga']['renames'] ?? [] as $line => $list) {
            foreach ($list as $from => $to) {
                $cases["$line: $from"] = [(string) $line, (string) $from, (string) $to];
            }
        }

        return $cases;
    }

    public function testThePackageListsItsRenames(): void
    {
        self::assertNotEmpty(self::renames());
    }

    #[DataProvider('renames')]
    public function testARenameDescribesThePackage(string $line, string $from, string $to): void
    {
        self::assertMatchesRegularExpression('/^\d+\.\d+$/', $line, 'A list of renames is filed under a Lenga line.');
        self::assertFileExists(self::sourceFileOf($to), "The new name $to has no class file.");
        self::assertFileDoesNotExist(self::sourceFileOf($from), "The old name $from still has a class file.");
    }

    private static function sourceFileOf(string $className): string
    {
        self::assertStringStartsWith(self::NAMESPACE_PREFIX, $className);

        return dirname(__DIR__) . '/src/'
            . str_replace('\\', '/', substr($className, strlen(self::NAMESPACE_PREFIX))) . '.php';
    }
}

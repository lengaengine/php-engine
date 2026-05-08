<?php

declare(strict_types=1);

namespace Lenga\Engine\Audio;

use function basename;
use function is_array;
use function is_string;
use function pathinfo;
use const PATHINFO_FILENAME;

/**
 * A reference to an AudioMixer asset.
 *
 * Audio mixers route AudioSource output through named channels so projects can
 * group music, sound effects, UI sounds, ambience, and voice separately.
 */
final class AudioMixer
{
    private const string REFERENCE_KIND_KEY = '__lengaAssetKind';
    private const string REFERENCE_KIND = 'AudioMixer';

    private string $assetPathValue;

    private function __construct(string $assetPath)
    {
        $this->assetPathValue = $assetPath;
    }

    /**
     * The mixer display name, derived from the assigned asset.
     */
    public string $name {
        get {
            $name = pathinfo($this->assetPathValue, PATHINFO_FILENAME);

            return is_string($name) && $name !== '' ? $name : basename($this->assetPathValue);
        }
    }

    /**
     * The project-relative asset path represented by this mixer handle.
     */
    public string $assetPath {
        get {
            return $this->assetPathValue;
        }
    }

    /**
     * @return array{__lengaAssetKind: string, assetPath: string}
     */
    public function __serialize(): array
    {
        return [
            self::REFERENCE_KIND_KEY => self::REFERENCE_KIND,
            'assetPath' => $this->assetPathValue,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function __unserialize(array $data): void
    {
        $this->assetPathValue = self::readAssetPath($data);
    }

    /**
     * Rehydrates a mixer reference from scene or inspector data.
     */
    public static function fromSerializedReference(mixed $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }

        $assetPath = self::readAssetPath($value);

        return $assetPath === '' ? null : new self($assetPath);
    }

    /**
     * Returns true when both handles point to the same mixer asset.
     */
    public function matches(?self $other): bool
    {
        return $other !== null && $this->assetPathValue === $other->assetPath;
    }

    private static function readAssetPath(mixed $value): string
    {
        if ($value instanceof self) {
            return $value->assetPathValue;
        }

        if (is_string($value)) {
            return $value;
        }

        if (!is_array($value)) {
            return '';
        }

        $kind = $value[self::REFERENCE_KIND_KEY] ?? null;
        if ($kind !== null && $kind !== self::REFERENCE_KIND) {
            return '';
        }

        foreach (['assetPath', 'path', 'mixerPath'] as $key) {
            if (isset($value[$key]) && is_string($value[$key])) {
                return $value[$key];
            }
        }

        return '';
    }
}

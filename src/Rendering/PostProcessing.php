<?php

declare(strict_types=1);

namespace Lenga\Engine\Rendering;

use Lenga\Engine\Core\NativeEngine;

/** Manages temporary post-processing layers on the active scene. */
final class PostProcessing
{
    /**
     * Adds or replaces a keyed layer on the active scene. Layers are not saved
     * and are discarded when the scene ends. Use a unique key for each effect.
     *
     * @param string $key A nonempty key for this effect.
     * @param PostProcessingOverride $override The settings to apply.
     * @return bool Whether the active scene accepted the layer.
     */
    public static function setOverride(string $key, PostProcessingOverride $override): bool
    {
        return NativeEngine::call('scene_set_post_processing_override', $key, $override->toNativeArray());
    }

    /** Returns whether the active scene contains the given layer. */
    public static function hasOverride(string $key): bool
    {
        return NativeEngine::call('scene_has_post_processing_override', $key);
    }

    /** Removes one layer. Returns false when the active scene did not contain it. */
    public static function clearOverride(string $key): bool
    {
        return NativeEngine::call('scene_clear_post_processing_override', $key);
    }

    /**
     * Clears all temporary layers and keeps the authored profile.
     *
     * @return bool Whether there was an active scene to clear.
     */
    public static function clearOverrides(): bool
    {
        return NativeEngine::call('scene_clear_post_processing_overrides');
    }
}

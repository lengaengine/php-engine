<?php

declare(strict_types=1);

namespace Lenga\Engine\Rendering;

use Lenga\Engine\Core\NativeEngine;

/**
 * Manages temporary post-processing layers on the active scene.
 *
 * Use it for short gameplay effects such as a damage flash, a slow-motion tint or
 * a dizziness warp. Layers sit on top of the scene's authored post-processing
 * profile and never change it. The camera must have Post Processing enabled for
 * a layer to affect the image.
 */
final class PostProcessing
{
    /**
     * Adds or replaces a keyed layer on the active scene. Layers are not saved
     * and are discarded when the scene ends. Use a unique key for each effect.
     *
     * Replacing a key swaps the whole layer and keeps its place among layers
     * with the same priority. Call it again each frame with new values to
     * animate an effect.
     *
     * @param string $key A nonempty key for this effect.
     * @param PostProcessingOverride $override The settings to apply.
     * @return bool Whether the active scene accepted the layer. Returns false for an empty key or when no scene is running.
     */
    public static function setOverride(string $key, PostProcessingOverride $override): bool
    {
        return NativeEngine::call('scene_set_post_processing_override', $key, $override->toNativeArray());
    }

    /**
     * Returns whether the active scene contains the given layer.
     *
     * A true result does not mean the effect is visible: the camera may have
     * Post Processing turned off, or the layer may have a weight of 0.
     *
     * @param string $key The key the layer was added with.
     */
    public static function hasOverride(string $key): bool
    {
        return NativeEngine::call('scene_has_post_processing_override', $key);
    }

    /**
     * Removes one layer. Returns false when the active scene did not contain it.
     *
     * Use it to end your own effect without removing layers that other scripts added.
     *
     * @param string $key The key the layer was added with.
     */
    public static function clearOverride(string $key): bool
    {
        return NativeEngine::call('scene_clear_post_processing_override', $key);
    }

    /**
     * Clears all temporary layers and keeps the authored profile.
     *
     * This also removes layers that other scripts added. Use
     * {@see PostProcessing::clearOverride()} to remove only your own.
     *
     * @return bool Whether there was an active scene to clear.
     */
    public static function clearOverrides(): bool
    {
        return NativeEngine::call('scene_clear_post_processing_overrides');
    }
}

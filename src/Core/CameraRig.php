<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;
use Lenga\Engine\Attributes\Tooltip;

/**
 * Moves the camera on its GameObject: follows a target with damping, a dead
 * zone and lookahead, keeps the view inside bounds, eases the camera's Size
 * toward a target Size, and shakes the view.
 *
 * In a 2D scene the rig follows on X and Y and leaves the camera's Z as
 * authored; in a 3D scene it follows on X, Y and Z. Shake moves only the view
 * the frame is drawn with: the Transform stays where the follow put it. The
 * rig runs after every script's update, so it follows where the target ended
 * up this frame.
 */
final class CameraRig extends Component
{
    /**
     * Wraps an existing camera rig on a GameObject.
     *
     * @internal The engine creates component wrappers. Get one with
     *     {@see GameObject::getComponent()} or add one with {@see GameObject::addComponent()}.
     *
     * @param GameObject $gameObject The object that owns the rig and its camera.
     * @param int $componentId The engine ID of the existing rig component.
     */
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'CameraRig');
    }

    /** The Transform the camera follows, or null to follow nothing. */
    #[Tooltip('The object the camera follows.')]
    public ?Transform $target {
        get {
            $targetId = $this->targetId;
            return $targetId === '' ? null : GameObject::findBySceneId($targetId)?->transform;
        }

        set(?Transform $value) {
            $this->targetId = $value?->gameObject->sceneObjectId ?? '';
        }
    }

    /** The scene ID of the object the camera follows; empty follows nothing. */
    public string $targetId {
        get {
            return (string) ($this->getState()['targetId'] ?? '');
        }

        set(string $value) {
            NativeEngine::call('camera_rig_set_target_id', $this->componentId, $value);
        }
    }

    /** Offset from the target in world units. Defaults to zero on each axis. */
    #[Tooltip('Added to the target\'s position: where the camera looks, in world units.')]
    public Vector3 $offset {
        get {
            return self::vector3($this->getState()['offset'] ?? [], new Vector3(0.0, 0.0, 0.0));
        }

        set(Vector3 $value) {
            NativeEngine::call('camera_rig_set_offset', $this->componentId, $value->x, $value->y, $value->z);
        }
    }

    /** Follow damping per axis in seconds. Defaults to 0.15 per axis; zero follows immediately. */
    #[Tooltip('Seconds the camera takes to catch up, per axis. 0 follows at once.')]
    public Vector3 $dampingTime {
        get {
            return self::vector3($this->getState()['dampingTime'] ?? [], new Vector3(0.15, 0.15, 0.15));
        }

        set(Vector3 $value) {
            NativeEngine::call('camera_rig_set_damping_time', $this->componentId, $value->x, $value->y, $value->z);
        }
    }

    /** Follow speed cap per axis in world units per second. Zero, the default, is unlimited. */
    #[Tooltip('Fastest the camera moves on an axis, in world units a second. 0 leaves it unlimited.')]
    #[Min(0)]
    public float $maxSpeed {
        get {
            return (float) ($this->getState()['maxSpeed'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('camera_rig_set_max_speed', $this->componentId, $value);
        }
    }

    /** Dead-zone size in world units. Target motion inside it does not move the camera; zero disables it on an axis. */
    #[Tooltip('A box around the followed point, in world units, inside which the target moves without moving the camera. 0 turns it off on an axis.')]
    public Vector2 $deadZone {
        get {
            return self::vector2($this->getState()['deadZone'] ?? [], new Vector2(0.0, 0.0));
        }

        set(Vector2 $value) {
            NativeEngine::call('camera_rig_set_dead_zone', $this->componentId, $value->x, $value->y);
        }
    }

    /** Lead distance along the target's last direction of travel, in world units. Defaults to zero. */
    #[Tooltip('How far ahead of the target the camera looks, in the direction it last moved, in world units.')]
    #[Min(0)]
    public float $lookaheadDistance {
        get {
            return (float) ($this->getState()['lookaheadDistance'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('camera_rig_set_lookahead_distance', $this->componentId, $value);
        }
    }

    /** Time in seconds for the lead direction to settle after a turn. Defaults to 0.3. */
    #[Tooltip('Seconds the lookahead takes to settle when the target turns.')]
    #[Min(0)]
    public float $lookaheadDampingTime {
        get {
            return (float) ($this->getState()['lookaheadDampingTime'] ?? 0.3);
        }

        set(float $value) {
            NativeEngine::call('camera_rig_set_lookahead_damping_time', $this->componentId, $value);
        }
    }

    /** Constrains a 2D view to the configured bounds. Defaults to false. */
    #[Tooltip('Keep a 2D view inside Bounds Min and Bounds Max. A view larger than the bounds is centred on them.')]
    public bool $boundsEnabled {
        get {
            return (bool) ($this->getState()['boundsEnabled'] ?? false);
        }

        set(bool $value) {
            NativeEngine::call('camera_rig_set_bounds_enabled', $this->componentId, $value);
        }
    }

    /** One corner of the 2D follow bounds in world units. Defaults to (0, 0). */
    #[Tooltip('One corner of the bounds, in world units.')]
    public Vector2 $boundsMin {
        get {
            return self::vector2($this->getState()['boundsMin'] ?? [], new Vector2(0.0, 0.0));
        }

        set(Vector2 $value) {
            NativeEngine::call('camera_rig_set_bounds_min', $this->componentId, $value->x, $value->y);
        }
    }

    /** The opposite corner of the 2D follow bounds in world units. Defaults to (0, 0). */
    #[Tooltip('The opposite corner of the bounds, in world units.')]
    public Vector2 $boundsMax {
        get {
            return self::vector2($this->getState()['boundsMax'] ?? [], new Vector2(0.0, 0.0));
        }

        set(Vector2 $value) {
            NativeEngine::call('camera_rig_set_bounds_max', $this->componentId, $value->x, $value->y);
        }
    }

    /** Target camera Size in world units. Zero leaves Size unchanged. Pixel Perfect cameras keep their calculated Size. */
    #[Tooltip('The Size the camera eases toward. 0 leaves Size alone; a Pixel Perfect camera keeps the Size its settings give.')]
    #[Min(0)]
    public float $targetSize {
        get {
            return (float) ($this->getState()['targetSize'] ?? 0.0);
        }

        set(float $value) {
            NativeEngine::call('camera_rig_set_target_size', $this->componentId, $value);
        }
    }

    /** Zoom damping in seconds. Defaults to 0.25. */
    #[Tooltip('Seconds the camera takes to reach the target Size.')]
    #[Min(0)]
    public float $zoomDampingTime {
        get {
            return (float) ($this->getState()['zoomDampingTime'] ?? 0.25);
        }

        set(float $value) {
            NativeEngine::call('camera_rig_set_zoom_damping_time', $this->componentId, $value);
        }
    }

    /**
     * Shakes the view, fading to nothing over the duration. Amplitude is the
     * largest offset per axis in world units, frequency how many times a
     * second the shake changes direction, and roll the largest rotation in
     * degrees. While another shake is still running, the stronger value of
     * each kind and the longer remaining time are kept, so repeated small
     * hits do not stack into a large shake.
     *
     * @param Vector2 $amplitude Largest offset of the view on X and Y, in world units.
     * @param float $duration How long the shake lasts, in seconds.
     * @param float $frequency How many times a second the shake changes direction.
     * @param float $rollDegrees Largest rotation of the view, in degrees. Zero shakes without rotating.
     * @return bool True when the shake started.
     */
    public function shake(
        Vector2 $amplitude,
        float $duration,
        float $frequency = 18.0,
        float $rollDegrees = 0.0,
    ): bool {
        return (bool) NativeEngine::call(
            'camera_rig_shake',
            $this->componentId,
            $amplitude->x,
            $amplitude->y,
            $duration,
            $frequency,
            $rollDegrees,
        );
    }

    /** Jumps to where the camera would settle, without damping. */
    public function snapToTarget(): bool
    {
        return (bool) NativeEngine::call('camera_rig_snap_to_target', $this->componentId);
    }

    /**
     * @return array<string, mixed>
     */
    private function getState(): array
    {
        $state = NativeEngine::call('camera_rig_get_state', $this->componentId);

        return \is_array($state) ? $state : [];
    }

    /**
     * @param array{x?: float, y?: float}|mixed $value
     */
    private static function vector2(mixed $value, Vector2 $fallback): Vector2
    {
        if (!\is_array($value)) {
            return $fallback;
        }

        return new Vector2((float) ($value['x'] ?? $fallback->x), (float) ($value['y'] ?? $fallback->y));
    }

    /**
     * @param array{x?: float, y?: float, z?: float}|mixed $value
     */
    private static function vector3(mixed $value, Vector3 $fallback): Vector3
    {
        if (!\is_array($value)) {
            return $fallback;
        }

        return new Vector3(
            (float) ($value['x'] ?? $fallback->x),
            (float) ($value['y'] ?? $fallback->y),
            (float) ($value['z'] ?? $fallback->z),
        );
    }
}

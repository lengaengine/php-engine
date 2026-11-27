<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;
use Lenga\Engine\Attributes\Range;

/**
 * Draws the scene from the position and rotation of its GameObject.
 *
 * Add a Camera to a GameObject to view the scene through it. The game is
 * drawn with the main camera, which {@see Camera::getMain()} returns: the
 * first enabled camera marked {@see Camera::$primary} on an active
 * GameObject, otherwise the first enabled camera on an active GameObject.
 *
 * Move or rotate the camera through its GameObject's {@see Transform}. Use
 * {@see Camera::$projection} to choose between a perspective view, where
 * distant objects look smaller, and an orthographic view, where they keep
 * their size. To follow a target, keep the view inside bounds or shake the
 * view, add a {@see CameraRig} to the same GameObject.
 *
 * @example examples/Core/Camera.php Zoom the main camera with the keyboard
 */
final class Camera extends Component
{
    /**
     * Wraps the native component with the given instance ID.
     *
     * @internal The engine creates component wrappers. Get one with
     *     {@see Camera::getMain()}, {@see GameObject::getComponent()} or
     *     {@see GameObject::addComponent()}.
     *
     * @param GameObject $gameObject GameObject the component is attached to.
     * @param int $componentId Native instance ID of the component.
     */
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'Camera');
    }

    // TODO: Implement screen-space related methods e.g.

    /**
     * Returns the camera the game is drawn with.
     *
     * That is the first enabled camera marked {@see Camera::$primary} on an
     * active GameObject, otherwise the first enabled camera on an active
     * GameObject, in hierarchy order.
     *
     * The engine chooses the camera on every call, so the result follows
     * cameras enabled, disabled or made primary since the previous call.
     *
     * @return Camera|null The main camera, or null when no camera qualifies.
     */
    public static function getMain(): ?Camera
    {
        $camera = GameObject::wrapNativeComponentLookupData(NativeEngine::call('camera_get_main'));

        return $camera instanceof self ? $camera : null;
    }

    /**
     * Whether this camera is the one the game is drawn with.
     *
     * When several enabled cameras on active GameObjects are marked primary,
     * the first in hierarchy order is used. Matches the Primary checkbox in
     * the Inspector. Defaults to `false`.
     */
    public bool $primary {
        get {
            return (bool) ($this->getState()['primary'] ?? false);
        }

        set(bool $value) {
            NativeEngine::call('camera_set_primary', $this->componentId, $value);
        }
    }

    /**
     * How the camera projects the scene: `Perspective` or `Orthographic`.
     *
     * A perspective camera draws distant objects smaller and uses
     * {@see Camera::$fieldOfView}. An orthographic camera draws objects at the
     * same size at any distance and uses {@see Camera::$orthographicSize}.
     * Any value other than `Orthographic` or `orthographic` sets
     * `Perspective`. Defaults to `Perspective`.
     */
    public string $projection {
        get {
            return (string) ($this->getState()['projection'] ?? 'Perspective');
        }

        set(string $value) {
            NativeEngine::call('camera_set_projection', $this->componentId, $value);
        }
    }

    /**
     * Vertical viewing angle of a perspective camera, in degrees.
     *
     * Smaller values zoom in and larger values show more of the scene. The
     * Inspector accepts 1 to 179. Defaults to `45`.
     */
    #[Range(1, 179)]
    public float $fieldOfView {
        get {
            return (float) ($this->getState()['fieldOfView'] ?? 45.0);
        }

        set(float $value) {
            NativeEngine::call('camera_set_field_of_view', $this->componentId, $value);
        }
    }

    /**
     * How much of the world an orthographic view shows, in world units.
     *
     * In a 2D scene it is half the height of the view, so larger values zoom
     * out. A 2D perspective camera also uses it to frame the view. A
     * {@see CameraRig} can ease it toward {@see CameraRig::$targetSize}.
     * Defaults to `10`.
     */
    #[Min(0.001)]
    public float $orthographicSize {
        get {
            return (float) ($this->getState()['orthographicSize'] ?? 10.0);
        }

        set(float $value) {
            NativeEngine::call('camera_set_orthographic_size', $this->componentId, $value);
        }
    }

    /**
     * @return array{
     *     primary?: bool,
     *     projection?: string,
     *     fieldOfView?: float,
     *     orthographicSize?: float,
     *     enabled?: bool
     * }
     */
    private function getState(): array
    {
        /** @var array{
         *     primary?: bool,
         *     projection?: string,
         *     fieldOfView?: float,
         *     orthographicSize?: float,
         *     enabled?: bool
         * } $state
         */
        $state = NativeEngine::call('camera_get_state', $this->componentId);

        return \is_array($state) ? $state : [];
    }
}

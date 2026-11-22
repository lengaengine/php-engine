<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

use Lenga\Engine\Attributes\Min;
use Lenga\Engine\Attributes\Range;

final class Camera extends Component
{
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'Camera');
    }

    // TODO: Implement screen-space related methods e.g.

    /**
     * Returns the camera the engine draws the active scene with: the first
     * enabled camera marked primary on an active GameObject, otherwise the
     * first enabled camera on an active GameObject, in hierarchy order.
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

    public bool $primary {
        get {
            return (bool) ($this->getState()['primary'] ?? false);
        }

        set(bool $value) {
            NativeEngine::call('camera_set_primary', $this->componentId, $value);
        }
    }

    public string $projection {
        get {
            return (string) ($this->getState()['projection'] ?? 'Perspective');
        }

        set(string $value) {
            NativeEngine::call('camera_set_projection', $this->componentId, $value);
        }
    }

    #[Range(1, 179)]
    public float $fieldOfView {
        get {
            return (float) ($this->getState()['fieldOfView'] ?? 45.0);
        }

        set(float $value) {
            NativeEngine::call('camera_set_field_of_view', $this->componentId, $value);
        }
    }

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

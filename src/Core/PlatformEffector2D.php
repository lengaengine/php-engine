<?php

declare(strict_types=1);

namespace Lenga\Engine\Core;

/**
 * Turns a 2D collider into a one-way platform that bodies can jump up through and land on from above.
 *
 * Add it to a platform that has a 2D collider, usually a
 * {@see BoxCollider2D}. A body moving up passes through the platform. A body
 * coming down collides with the top and can stand on it. The platform owns
 * the rule, so the player's script needs no special code for it.
 */
final class PlatformEffector2D extends Component
{
    /**
     * Wraps an existing PlatformEffector2D component.
     *
     * The engine creates PlatformEffector2D wrappers. Use
     * {@see GameObject::addComponent()} or {@see GameObject::getComponent()}
     * in scripts.
     *
     * @internal
     *
     * @param GameObject $gameObject The GameObject that owns the component.
     * @param int $componentId The engine's identifier for the component.
     */
    public function __construct(GameObject $gameObject, int $componentId)
    {
        parent::__construct($gameObject, $componentId, 'PlatformEffector2D');
    }

    /**
     * Whether the platform lets bodies pass through from below.
     *
     * Turn it off to make the platform solid from every side. Defaults to `true`.
     */
    public bool $useOneWay {
        get {
            return (bool) ($this->getState()['useOneWay'] ?? true);
        }

        set(bool $value) {
            NativeEngine::call('platform_effector2d_set_use_one_way', $this->componentId, $value);
        }
    }

    /**
     * How far below the platform's top surface a body's centre can be and still land on it, in world units.
     *
     * A body collides with the platform when its centre is no lower than the
     * top surface plus this distance and it is not moving up. Raise the value
     * if bodies fall through when they land; lower it if they catch on the
     * platform while jumping up through it. Negative values count as `0`.
     * Defaults to `4.0`.
     */
    public float $surfaceBuffer {
        get {
            return (float) ($this->getState()['surfaceBuffer'] ?? 4.0);
        }

        set(float $value) {
            NativeEngine::call('platform_effector2d_set_surface_buffer', $this->componentId, $value);
        }
    }

    /** @return array{useOneWay?: bool, surfaceBuffer?: float|int, enabled?: bool} */
    private function getState(): array
    {
        /** @var array{useOneWay?: bool, surfaceBuffer?: float|int, enabled?: bool} $state */
        $state = NativeEngine::call('platform_effector2d_get_state', $this->componentId);

        return \is_array($state) ? $state : [];
    }
}

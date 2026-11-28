<?php

declare(strict_types=1);

namespace Game\Scripts;

use Lenga\Engine\Attributes\Tooltip;
use Lenga\Engine\Core\Behaviour;
use Lenga\Engine\Core\Collision2D;
use Lenga\Engine\Core\Debug;
use Lenga\Engine\Core\Input;
use Lenga\Engine\Enumerations\KeyCode;
use Lenga\Engine\SceneManagement\SceneManager;

/**
 * Sits on a level's exit trigger. Touching it loads the next level, R reloads
 * the current level, and Escape returns to the main menu.
 *
 * Every scene named here must be listed and enabled under Scenes in Build
 * (Project > Build & Export).
 */
final class LevelExit extends Behaviour
{
    // Public fields appear in the Inspector, so each level can name the scene that follows it.
    #[Tooltip('Scene to load when the player reaches this exit.')]
    public string $nextScene = 'Level2';

    #[Tooltip('Scene to return to when the player presses Escape.')]
    public string $menuScene = 'Main Menu';

    private bool $leaving = false;

    public function update(): void
    {
        if (Input::getKeyDown(KeyCode::R)) {
            $this->reloadLevel();
        }

        if (Input::getKeyDown(KeyCode::ESCAPE)) {
            // loadScene() throws a RuntimeException when the scene is missing,
            // which is right for a scene the game cannot work without.
            SceneManager::loadScene($this->menuScene);
        }
    }

    public function onTriggerEnter2D(Collision2D $collision): void
    {
        if ($this->leaving || $collision->otherGameObject?->compareTag('Player') !== true) {
            return;
        }

        $this->leaving = true;

        // tryLoadScene() returns false instead of throwing, so the last level
        // can fall back to the menu when there is no next scene.
        if (!SceneManager::tryLoadScene($this->nextScene)) {
            Debug::log("No scene called '{$this->nextScene}'. Returning to the menu.");
            SceneManager::loadScene($this->menuScene);
        }

        // The new scene replaces this one at the end of the frame, so code
        // after a load request still runs in the current scene.
    }

    private function reloadLevel(): void
    {
        $scene = SceneManager::getActiveScene();
        if ($scene !== null) {
            SceneManager::loadScene($scene->name);
        }
    }
}

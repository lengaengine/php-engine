<?php

declare(strict_types=1);

namespace Game\Scripts;

use Generator;
use Lenga\Engine\Attributes\Tooltip;
use Lenga\Engine\Core\Behaviour;
use Lenga\Engine\Core\Debug;
use Lenga\Engine\Enumerations\WebRequestMethod;
use Lenga\Engine\Enumerations\WebRequestResult;
use Lenga\Engine\Networking\WebRequest;

/**
 * Loads the top ten scores when the scene starts and submits the player's
 * score at the end of a run, from the "leaderboard" host listed under
 * Project > Project Settings... > Network.
 */
final class OnlineLeaderboard extends Behaviour
{
    #[Tooltip('The token the leaderboard service gave this player.')]
    public string $playerToken = '';

    /** @var list<array{name: string, score: int}> */
    private array $topScores = [];

    public function start(): void
    {
        // Without a coroutine: the listener runs when the request ends.
        $request = new WebRequest('leaderboard', '/v1/scores?' . http_build_query(['limit' => 10]));
        $request->timeout = 10.0;
        $request->completed->add(function (WebRequest $request): void {
            if ($request->result !== WebRequestResult::Success) {
                Debug::warn("Scores did not load: {$request->error}");
                return;
            }
            $this->topScores = $request->decodeJsonResponse()['scores'] ?? [];
        });
        $request->send();
    }

    public function submitScore(int $score): void
    {
        $this->startCoroutine($this->postScore($score));
    }

    private function postScore(int $score): Generator
    {
        $request = new WebRequest('leaderboard', '/v1/scores', WebRequestMethod::Post);
        $request->headers['Authorization'] = 'Bearer ' . $this->playerToken;
        $request->encodeJsonBody(['score' => $score]);
        $request->timeout = 10.0;

        // With a coroutine: wait for the answer, then read it.
        yield $request->send();

        match ($request->result) {
            WebRequestResult::Success => Debug::log('Score saved, rank ' . ($request->decodeJsonResponse()['rank'] ?? '?')),
            WebRequestResult::ProtocolError => Debug::warn("The leaderboard refused the score: {$request->error}"),
            default => Debug::warn("The leaderboard could not be reached: {$request->error}"),
        };
    }
}

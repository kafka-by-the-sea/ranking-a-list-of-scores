<?php

use Illuminate\Support\Collection;

require_once 'vendor/autoload.php';

Collection::macro('then', function ($callback) {
    return $callback($this);
});

function rankScores($scores)
{
    return collect($scores)->then('assignInitialRankings')
        ->then('adjustRankingsForTies')
        ->sortBy('rank');
}

function assignInitialRankings($scores)
{
    return $scores->sortByDesc('score')
        ->zip(range(1, $scores->count()))
        ->map(function ($scoreAndRank) {
            list($score, $rank) = $scoreAndRank;
            return array_merge($score, [
                'rank' => $rank
            ]);
        });
}

function adjustRankingsForTies($scores)
{
    return $scores->groupBy('score')->map(function ($tiedScores) {
        return applyMinRank($tiedScores);
    })->collapse();
}

function applyMinRank($tiedScores)
{
    $lowestRank = $tiedScores->pluck('rank')->min();
    return $tiedScores->map(function ($rankedScore) use ($lowestRank) {
        return array_merge($rankedScore, [
            'rank' => $lowestRank
        ]);
    });
}


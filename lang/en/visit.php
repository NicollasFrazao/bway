<?php

declare(strict_types=1);

return [
    'home' => [
        'title' => 'Shop by the shortest walk',
        'intro' => 'bway turns your shopping list into a walk through the store, sector by sector.',
        'step_list' => 'Write your shopping list.',
        'step_store' => 'Choose the store.',
        'step_walk' => 'Follow the route and tick each item as you pick it up.',
    ],
    'choose' => 'Choose a store',
    'title' => 'Your visit',
    'summary' => ':stops sectors to visit from the :start, :steps steps in all.',
    'nothing' => 'Nothing on your list is sold in this store.',
    'unsold' => 'Not sold in this store',
    'unavailable' => 'The map of this store is not ready yet, so its visit cannot be planned.',
    'actions' => [
        'plan' => 'Plan my visit',
        'map' => 'See the route on the map',
        'acquire' => 'Got it',
        'acquire_named' => 'Got :product',
    ],
    'status' => [
        'acquired' => ':product is off your list.',
    ],
    'map' => [
        'title' => 'Route map',
        'leg' => 'Walk :number of :total: from :from to :to, :steps steps.',
        'previous' => 'Previous walk',
        'next' => 'Next walk',
        'alt' => 'Floor plan of :store with the route of your visit',
        'caption' => 'Sectors in blue, the entrance in green, shelves in grey; the walk you are on is the dark line, the numbers are the order of the stops.',
    ],
];

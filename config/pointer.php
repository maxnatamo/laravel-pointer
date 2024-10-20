<?php

return [

    'table_names' => [

        /*
         * Defines which table should be used to store tours.
         */

        'tours' => 'tours',

        /*
         * Defines which table should be used to store tour steps.
         */

        'tour_steps' => 'tour_steps',
    ],

    'prune' => [

        /**
         * When pruning tours, this value decides whether all completed tours should be pruned (full),
         * or whether only the steps of the completed tours should be pruned (shallow).
         *
         * Supported values: 'shallow', 'full'
         */

        'mode' => 'shallow',

        /**
         * When pruning tours, this value decides at least how long has passed since the
         * tour was completed, before it is considered for pruning.
         * This value can also be `null`, to prune all completed tours.
         */

        'min_age' => '1 week',
    ],
];

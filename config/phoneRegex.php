<?php

return [
    'phone' => [
        'domestic' => '/^\+?0*-*(?:91-)?\K(?:91)?[6-9][0-9]{9}$/m', //india /^\+?0*(?:91-)?\K(?:91)?[6-9][0-9]{9}$/m
        // 'international' => '/^\+?0*\K[0-9]{7,15}$/m' // international
    ],
];

<?php

return [
    'fe_set_password_url' => env('DMC_FE_SET_PASSWORD_URL', 'https://www.djakarta-miningclub.com/login'),
    'set_password_link_expiry_hours' => env('DMC_SET_PASSWORD_LINK_EXPIRY_HOURS', 24),
    'post_reset_password_redirect_url' => env('DMC_POST_RESET_PASSWORD_REDIRECT_URL', 'https://www.djakarta-miningclub.com?modalloginopen=true'),
    'active_booth_event_slug' => env('ACTIVE_BOOTH_EVENT_SLUG', 'international-critical-minerals-metals-summit-indonesia-2026'),
    'active_booth_event_id' => env('ACTIVE_BOOTH_EVENT_ID', 70),
    'booth_giveaway_enabled' => env('BOOTH_GIVEAWAY_ENABLED', true),
];

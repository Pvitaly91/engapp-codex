<?php

return [
    'route_prefix' => 'admin/pages/manage',
    'route_name_prefix' => 'pages.manage.',
    'middleware' => ['web', 'auth.admin'],
];

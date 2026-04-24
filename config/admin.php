<?php

return [
    'username' => env('ADMIN_USERNAME', 'admin'),
    'user_email' => env('ADMIN_USER_EMAIL', env('ADMIN_USERNAME', 'admin')),
    'password_hash' => env('ADMIN_PASSWORD_HASH', '$2y$12$iTF0hutm35wjYhkKUqU3fuJCLR.boRWIVjnH4R4mho8OSh1QIke/.'),
];

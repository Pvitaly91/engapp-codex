<?php

return array (
  'route_prefix' => 'admin/database-structure',
  'connection' => NULL,
  'manual_relations' => 
  array (
  ),
  'content_management' => 
  array (
    'menu' => 
    array (
      0 => 
      array (
        'table' => 'pages',
        'label' => 'pages',
      ),
      1 => 
      array (
        'table' => 'question_hints',
        'label' => 'question_hints',
      ),
    ),
    'table_settings' =>
    array (
      /*
      'posts' =>
      array (
        'relations' =>
        array (
          'user_id' => 'users.name',
        ),
      ),
      */
    ),
  ),
);

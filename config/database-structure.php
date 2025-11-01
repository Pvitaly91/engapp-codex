<?php

return array (
  'route_prefix' => 'admin/database-structure',
  'connection' => NULL,
  'manual_relations' => 
  array (
    'test_parent' => 
    array (
      'test_id' => 
      array (
        'table' => 'test',
        'column' => 'id',
        'display_column' => 'id',
      ),
    ),
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
      2 => 
      array (
        'table' => 'test_parent',
        'label' => 'test_parent',
      ),
    ),
    'table_settings' => 
    array (
    ),
    'default_table' => 'question_hints',
  ),
);

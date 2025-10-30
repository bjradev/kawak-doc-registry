<?php

return [
  'driver'    => 'mysql',
  'host'      => $_ENV['DB_HOST'],
  'port'      => $_ENV['DB_PORT'],
  'database'  => $_ENV['DB_NAME'],
  'username'  => $_ENV['DB_USER'],
  'password'  => $_ENV['DB_PASS'],
  'charset'   => 'utf8mb4',
  'collation' => 'utf8mb4_unicode_ci',
];
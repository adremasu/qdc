<?php
// Require Composer's autoloader.
require 'vendor/autoload.php';
require 'config.php';
// Using Medoo namespace.
use Medoo\Medoo;
$database = new Medoo([
  // [required]
  'type' => 'mysql',
  'host' => $host,
  'database' => $database,
  'username' => $username,
  'password' => $password,

  // [optional]
  'charset' => 'utf8mb4',
  'collation' => 'utf8mb4_general_ci',
  'port' => 3306,

  // [optional] The table prefix. All table names will be prefixed as PREFIX_table.
  'prefix' => 'PREFIX_',

  // [optional] To enable logging. It is disabled by default for better performance.
  'logging' => true,

  // [optional]
  // Error mode
  // Error handling strategies when the error is occurred.
  // PDO::ERRMODE_SILENT (default) | PDO::ERRMODE_WARNING | PDO::ERRMODE_EXCEPTION
  // Read more from https://www.php.net/manual/en/pdo.error-handling.php.
  'error' => PDO::ERRMODE_SILENT,

  // [optional]
  // The driver_option for connection.
  // Read more from http://www.php.net/manual/en/pdo.setattribute.php.
  'option' => [
    PDO::ATTR_CASE => PDO::CASE_NATURAL
  ],

  // [optional] Medoo will execute those commands after the database is connected.
  'command' => [
    'SET SQL_MODE=ANSI_QUOTES'
  ]
]);

// Define all paths for your custom commands in this array (leave as empty array if not used)
$commands_paths = [
    __DIR__ . '/Commands',
];
try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);
    $telegram->addCommandsPaths($commands_paths);

    // Handle telegram webhook request
    $telegram->handle();

} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // Silence is golden!
    // log telegram errors
    // echo $e->getMessage();
}



?>

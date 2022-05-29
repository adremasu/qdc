<?php
// Require Composer's autoloader.
require 'vendor/autoload.php';
$config = require __DIR__ . '/config.php';

// Define all paths for your custom commands in this array (leave as empty array if not used)
$commands_paths = [
    __DIR__ . '/Commands',
];
try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($config['api_key'], $config['bot_username']);
    $telegram->addCommandsPaths($commands_paths);
    $telegram->enableMySql($config['mysql']);
    // Handle telegram webhook request
    $telegram->handle();

} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // Silence is golden!
    // log telegram errors
    // echo $e->getMessage();
}



?>

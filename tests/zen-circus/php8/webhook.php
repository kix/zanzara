<?php

use Zanzara\Config;
use Zanzara\Context;
use Zanzara\Zanzara;

require __DIR__ . '/../../../vendor/autoload.php';

$dotenv = new Symfony\Component\Dotenv\Dotenv();
$dotenv->load(__DIR__ . '/../../../.env');
$config = new Config();
$config->setUpdateMode(Config::REACTPHP_WEBHOOK_MODE);
//$config->setWebhookTokenCheck(true);
$bot = new Zanzara($_ENV['BOT_TOKEN'], $config);

$bot->onUpdate(function (Context $ctx) {
    $ctx->sendMessage('Hello');
});

$bot->run();

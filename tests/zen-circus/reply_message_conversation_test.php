<?php

use Zanzara\Context;
use Zanzara\Zanzara;

require __DIR__ . '/../bootstrap.php';

$bot = new Zanzara($_ENV['BOT_TOKEN']);

$bot->onCommand('start', function (Context $ctx) {
    $ctx->sendMessage('send contact', ['reply_markup' => ['keyboard' => [
        [['text' => 'send', 'request_contact' => true]]
    ]]]);
    $ctx->nextStep('sayThankYou');
});

function sayThankYou(Context $ctx) {
    $ctx->sendMessage('Thank you');
}

$bot->run();

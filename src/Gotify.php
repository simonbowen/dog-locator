<?php

namespace Simonbowen\Rocky;

use Gotify\Auth\Token;
use Gotify\Endpoint\Message;
use Gotify\Server;

class Gotify implements Channel
{
    public function __construct(private readonly Server $server, private readonly Token $token)
    {
    }

    public function send(string $title, string $body)
    {
        $message = new Message($this->server, $this->token);

        return $message->create(
            title: $title,
            message: $body,
            priority: Message::PRIORITY_HIGH,
        );
    }
}
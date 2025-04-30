<?php

namespace Simonbowen\Rocky;

class Notifier
{
    /**
     * @var Channel[]
     */
    private array $channels = [];
    
    public function addChannel(Channel $channel): void
    {
        $this->channels[] = $channel;
    }

    public function send(string $title, string $body): void
    {
        foreach ($this->channels as $channel) {
            $channel->send($title, $body);
        }
    }
}
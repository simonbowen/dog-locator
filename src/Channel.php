<?php

namespace Simonbowen\Rocky;

interface Channel
{
    public function send(string $title, string $body);
}
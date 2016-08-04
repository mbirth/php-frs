<?php

namespace Frs\Output\Transport;

interface TransportInterface
{
    public function setParam($key, $value);

    public function setContent($content);

    public function transmit();
}

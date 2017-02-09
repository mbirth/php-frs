<?php

namespace Frs\Output\Transport;

class NullTransport implements TransportInterface
{
    public function setParam($key, $value)
    {
    }

    public function setContent($content)
    {
    }

    public function transmit()
    {
        return true;
    }
}

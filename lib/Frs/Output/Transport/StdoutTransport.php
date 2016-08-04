<?php

namespace Frs\Output\Transport;

class StdoutTransport implements TransportInterface
{
    private $content;

    public function setParam($key, $value)
    {
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function transmit()
    {
        echo $this->content;
        return true;
    }
}

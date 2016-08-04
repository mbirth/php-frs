<?php

namespace Frs\Output\Transport;

class StdoutTransport implements TransportInterface
{
    private $content;

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function transmit()
    {
        echo $this->content;
    }
}

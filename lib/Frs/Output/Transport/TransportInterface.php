<?php

namespace Frs\Output\Transport;

interface TransportInterface
{
    public function setContent($content);

    public function transmit();
}

<?php

namespace Frs\Output;

use \Frs\Output\GenericOutput;

class HtmlOutput extends GenericOutput
{
    public function sendOutputToStdout()
    {
        echo $this->getRenderedOutput();
    }
}

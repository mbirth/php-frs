<?php

namespace Frs\Output\Transport;

class GScriptTransport implements TransportInterface
{
    private $content;
    private $subject;
    private $headers = array();

    public function __construct()
    {
        $this->post_url = 'https://script.google.com/macros/s/AKfycbxVcugiTBTvWx8DK_HhuQh_vXdteir6GTXE_Anir3rfovatjQM/exec';
    }

    public function setParam($key, $value)
    {
        switch ($key) {
            case 'to':
                // ignored
                break;

            case 'subject':
                $this->subject = $value;
                break;

            case 'headers':
                $this->headers = $value;
                break;
        }
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function transmit()
    {
        echo '<html><body><form method="post" action="' . $this->post_url . '">';
        echo '<input type="text" name="subject" value="' . $this->subject . '"/>';
        echo '<textarea name="body">' . $this->content . '</textarea>';
        echo '<input type="submit" value="Send"/>';
        echo '</form></body></html>';
        return true;
    }
}

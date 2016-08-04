<?php

namespace Frs\Output\Transport;

class MailTransport implements TransportInterface
{
    private $recipients;
    private $subject;
    private $headers;
    private $content;

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function setParam($key, $value)
    {
        switch ($key) {
            case 'to':
                $this->recipients = $value;
                break;

            case 'subject':
                $this->subject = $value;
                break;

            case 'headers':
                $this->setHeaders($value);
                break;
        }
    }

    private function setHeaders($headers)
    {
        $this->headers = '';
        foreach ($headers as $key=>$value) {
            $this->headers .= $key . ': ' . $value . "\r\n";
        }
    }

    public function transmit()
    {
        return mail($this->recipients, $this->subject, $this->content, $this->headers);
    }
}

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

    public function setRecipients($recipients)
    {
        $this->recipients = $recipients;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    public function transmit()
    {
        return mail($this->recipients, $this->subject, $this->content, $this->headers);
    }
}

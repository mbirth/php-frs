<?php

namespace Frs\Output\Transport;

class GmailTransport implements TransportInterface
{
    private $gms;
    private $recipients;
    private $subject;
    private $headers;
    private $content;

    public function _construct(\Frs\SessionManager $sm)
    {
        $this->gms = new Google_Service_Gmail($sm->getGoogleClient());
    }

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
        $optParams = array();
        $postBody = new Google_Service_Gmail_Message();
        $this->gms->users_messages->send('me', $postBody, $optParams);
    }
}

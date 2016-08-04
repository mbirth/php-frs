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

    /**
     * Base64 encoding with URL/filename safe characters.
     * Taken from http://php.net/manual/en/function.base64-encode.php#103849
     *
     * @param string $data Date to encode
     * @return string Encoded data
     */
    private function b64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public function transmit()
    {
        $mime = new Mail_mime();
        $mime->addTo($this->recipients);
        $mime->setHTMLBody($this->content);
        $mime->setSubject($this->subject);

        $message_body = $mime->getMessage(null, null, $this->headers);
        $encoded_message = $this->b64url_encode($message_body);

        $postBody = new Google_Service_Gmail_Message();
        $postBody->setRaw($encoded_message);

        $msg = $this->gms->users_messages->send('me', $postBody);
        if ($msg->getId()) {
            return true;
        }
        return false;
    }
}

<?php

namespace Frs\Output\Transport;

class GmailTransport implements TransportInterface
{
    private $gms;
    private $recipients;
    private $subject;
    private $headers;
    private $content;

    public function __construct(\Frs\SessionManager $sm)
    {
        $this->gms = new \Google_Service_Gmail($sm->getGoogleClient());
    }

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
                $this->headers = $value;
                break;
        }
    }

    /**
     * Base64 encoding with URL/filename safe characters.
     * Taken from http://php.net/manual/en/function.base64-encode.php#103849
     *
     * @param string $data Date to encode
     * @return string Encoded data
     */
    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public function transmit()
    {
        $mime = new \Mail_mime();
        $mime->setParam('html_charset', 'utf-8');
        $mime->setParam('html_encoding', '8bit');
        $mime->addTo($this->recipients);
        $mime->setHTMLBody($this->content);
        $mime->setSubject($this->subject);

        $message_body = $mime->getMessage(null, null, $this->headers);
        $encoded_message = $this->base64UrlEncode($message_body);

        $postBody = new \Google_Service_Gmail_Message();
        $postBody->setRaw($encoded_message);

        $msg = $this->gms->users_messages->send('me', $postBody);
        if ($msg->getId()) {
            return true;
        }
        return false;
    }
}

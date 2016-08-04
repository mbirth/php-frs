<?php

namespace Frs\Output;

use \Frs\Output\GenericOutput;

class MailOutput extends GenericOutput
{
    private $recipients = array();
    private $headers = array();
    private $subject = '';

    /**
     * Adds a recipient to the list.
     *
     * @param string $address The email address to add.
     * @param string $name The name to display.
     */
    public function addRecipient($address, $name = '')
    {
        // TODO: Check $address for validity
        $fullAddress = '<' . $address . '>';
        if (!empty($name)) {
            $fullAddress = $name . ' ' . $fullAddress;
        }
        $this->recipients[] = $fullAddress;
    }

    /**
     * Sets the default subject for the mail. Might be
     * overwritten by subject line from template.
     *
     * @param string $newSubject Subject to use.
     */
    public function setSubject($newSubject)
    {
        $this->subject = $newSubject;
    }

    /**
     * Sets the given header $key to $value. A Subject: header sets
     * the subject via $this->setSubject(). A To: header is ignored.
     *
     * @param string $key Header name
     * @param string $value Header contents
     */
    public function setHeader($key, $value)
    {
        if (strtolower($key) == 'subject') {
            $this->setSubject($value);
            return;
        }
        if (strtolower($key) == 'to') {
            // ignore for now (maybe set as recipient?)
            return;
        }
        $this->headers[$key] = $value;
    }

    /**
     * Sets mail headers. If headers contain a Subject: line, the subject is set from that.
     *
     * @param string $headers All mail headers separated by newlines (CRLF or LF).
     */
    public function setHeadersFromString($headers)
    {
        $header_lines = preg_split('/\r?\n/', $headers);

        foreach ($header_lines as $header_line) {
            list($key, $value) = preg_split('/: /', $header_line, 2);
            $this->setHeader($key, $value);
        }
    }

    /**
     * Sends the prepared mail
     *
     * @return bool TRUE if mail was sent, FALSE if not.
     */
    public function send()
    {
        $mail_html = $this->getRenderedOutput();  // contains headers + body
        list($headers, $mailbody) = preg_split('/\r?\n\r?\n/', $mail_html, 2);
        $this->setHeadersFromString($headers);
        $recipients = implode(', ', $this->recipients);
        // TODO: Check if any recipients in the first place
        $this->transport->setParam('to', $recipients);
        $this->transport->setParam('subject', $this->subject);
        $this->transport->setParam('headers', $this->headers);
        $this->transport->setContent($mailbody);
        return $this->transport->transmit();
    }
}

<?php

namespace Frs\Output;

use \Frs\Output\GenericOutput;

class MailOutput extends GenericOutput
{
    private $recipients = array();
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
     * Sends the prepared mail
     *
     * @return bool TRUE if mail was sent, FALSE if not.
     */
    public function send()
    {
        $mail_html = $this->getRenderedOutput();  // contains headers + body
        list($headers, $mailbody) = preg_split('/\r?\n\r?\n/', $mail_html, 2);

        $header_lines = preg_split('/\r?\n/', $headers);
        $header_filtered = '';

        foreach ($header_lines as $header_line) {
            list($key, $value) = preg_split('/: /', $header_line, 2);
            if (in_array(strtolower($key), array('subject', 'to'))) {
                // Skip Subject and To headers as they're added by PHP
                if (strtolower($key) == 'subject') {
                    $this->setSubject($value);
                }
                continue;
            }
            $header_filtered .= $header_line . "\r\n";
        }
        $recipients = implode(', ', $this->recipients);
        // TODO: Check if any recipients in the first place
        $mail_sent  = mail($recipients, $this->subject, $mailbody, $header_filtered);
        return $mail_sent;
    }
}

<?php

namespace Rubricate\Mailer;

abstract class AbstractSmtpMailer
{
    protected $host;
    protected $port;
    protected $username;
    protected $password;
    protected $from;
    protected $fromName;
    protected $debug;

    private $socket;

    abstract protected function send($to, $subject, $message, $isHtml = false, $cc = [], $bcc = []);

    protected function openSocketConn()
    {
        $this->socket = fsockopen($this->host, $this->port, $errno, $errstr, 10);

        if (!$this->socket) {
            throw new \Exception("Connection error: $errstr ($errno)");
        }

        $this->read();
        $this->ehlo();

    }

    protected function startTLS()
    {
        $this->write("STARTTLS");
        $this->read();
        stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

        $this->ehlo();
    }

    protected function authLogin()
    {
        $this->write("AUTH LOGIN");
        $this->expect(334);
        $this->write(base64_encode($this->username));
        $this->expect(334);
        $this->write(base64_encode($this->password));

        $response = $this->read();

        if (strpos($response, '535') === 0) {
            throw new \Exception("SMTP authentication failed: Invalid username or password.");
        }
    }

    protected function headers($to, $subject, $message, $isHtml = false, $cc = [], $bcc = [])
    {
        $this->write("MAIL FROM:<{$this->from}>");
        $this->read();
        $this->write("RCPT TO:<{$to}>");
        $this->read();

        foreach ($cc as $ccEmail) {
            $this->write("RCPT TO:<{$ccEmail}>");
            $this->read();
        }

        foreach ($bcc as $bccEmail) {
            $this->write("RCPT TO:<{$bccEmail}>");
            $this->read();
        }

        $this->write("DATA");
        $this->read();

        $htmlPlain = ($isHtml ? "text/html": "text/plain");
        $headers = "From: {$this->fromName} <{$this->from}>\r\n";
        $headers .= "To: <$to>\r\n";

        if (!empty($cc)) {
            $headers .= "Cc: " . implode(', ', $cc) . "\r\n";
        }

        $headers .= "Subject: $subject\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: " . $htmlPlain . "; charset=UTF-8\r\n";
        $headers .= "\r\n";

        $this->write($headers . $message . "\r\n.");
        $this->read();
    }

    public function ehlo()
    {
        $this->write("EHLO localhost");
        $this->read();
    }

    public function quit()
    {
        $this->write("QUIT");
        $this->read();
        fclose($this->socket);
    }

    private function write($msg)
    {
        if ($this->debug){
            echo ">> $msg\n";
        }

        fwrite($this->socket, $msg . "\r\n");
    }

    private function read()
    {
        $response = '';

        while ($line = fgets($this->socket, 515)) {

            $response .= $line;
            if (substr($line, 3, 1) == ' '){
                break;
            }
        }

        if ($this->debug){
            echo "<< $response\n";
        }

        return $response;
    }

    private function expect($expectedCode)
    {
        $response = $this->read($this->socket);

        if (substr($response, 0, 3) != $expectedCode) {
            throw new \Exception("Unexpected response from SMTP server: $response");
        }
    }


}


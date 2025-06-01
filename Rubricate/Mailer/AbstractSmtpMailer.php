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

    abstract protected function send($to, $subject, $message, $isHtml = false);

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
        $this->read();
        $this->write(base64_encode($this->username));
        $this->read();
        $this->write(base64_encode($this->password));
        $this->read();
    }

    protected function headers($to, $subject, $message, $isHtml = false)
    {
        $this->write("MAIL FROM:<{$this->from}>");
        $this->read();
        $this->write("RCPT TO:<{$to}>");
        $this->read();
        $this->write("DATA");
        $this->read();

        $htmlPlain = ($isHtml ? "text/html": "text/plain");
        $headers = "From: {$this->fromName} <{$this->from}>\r\n";
        $headers .= "To: <$to>\r\n";
        $headers .= "Subject: $subject\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: " . $htmlPlain . "; charset=UTF-8\r\n";
        $headers .= "\r\n";

        $this->write($headers . $message . "\r\n.");
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

    public function ehlo()
    {
        $this->write("EHLO localhost");
        $this->read();
    }

}


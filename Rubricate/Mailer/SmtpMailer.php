<?php

namespace Rubricate\Mailer;

class SmtpMailer
{
    private $host;
    private $port;
    private $username;
    private $password;
    private $from;
    private $fromName;
    private $debug;

    public function __construct(

        $host, 
        $port, 
        $username, 
        $password, 
        $from, 
        $fromName = '', 
        $debug = false
    ){
        $this->host     = $host;
        $this->port     = $port;
        $this->username = $username;
        $this->password = $password;
        $this->from     = $from;
        $this->fromName = $fromName;
        $this->debug    = $debug;
    }

    public function send($to, $subject, $message, $isHtml = false)
    {
        $socket = fsockopen($this->host, $this->port, $errno, $errstr, 10);

        if (!$socket) {
            throw new \Exception("Connection error: $errstr ($errno)");
        }

        $this->read($socket);
        $this->write($socket, "EHLO localhost");
        $this->read($socket);

        $this->write($socket, "STARTTLS");
        $this->read($socket);
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

        $this->write($socket, "EHLO localhost");
        $this->read($socket);

        $this->write($socket, "AUTH LOGIN");
        $this->read($socket);
        $this->write($socket, base64_encode($this->username));
        $this->read($socket);
        $this->write($socket, base64_encode($this->password));
        $this->read($socket);

        $this->write($socket, "MAIL FROM:<{$this->from}>");
        $this->read($socket);
        $this->write($socket, "RCPT TO:<{$to}>");
        $this->read($socket);
        $this->write($socket, "DATA");
        $this->read($socket);

        $htmlPlain = ($isHtml ? "text/html": "text/plain");
        $headers = "From: {$this->fromName} <{$this->from}>\r\n";
        $headers .= "To: <$to>\r\n";
        $headers .= "Subject: $subject\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: " . $htmlPlain . "; charset=UTF-8\r\n";
        $headers .= "\r\n";

        $this->write($socket, $headers . $message . "\r\n.");
        $this->read($socket);

        $this->write($socket, "QUIT");
        $this->read($socket);
        fclose($socket);

        return true;
    }

    private function write($socket, $msg)
    {
        if ($this->debug){ echo ">> $msg\n"; }

        fwrite($socket, $msg . "\r\n");
    }

    private function read($socket)
    {
        $response = '';

        while ($line = fgets($socket, 515)) {

            $response .= $line;
            if (substr($line, 3, 1) == ' '){ break; }
        }

        if ($this->debug){ echo "<< $response\n"; }

        return $response;
    }
}


<?php

namespace Rubricate\Mailer;

class SmtpMailer extends AbstractSmtpMailer
{
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

    public function send($to, $subject, $message, $isHtml = false, $cc = [], $bcc = [])
    {
        $this->openSocketConn();
        $this->startTLS();
        $this->authLogin();
        $this->headers($to, $subject, $message, $isHtml, $cc, $bcc);
        $this->quit();

        return true;
    }

}


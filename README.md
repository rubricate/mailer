# Rubricate Mailer

[![Maintainer](http://img.shields.io/badge/maintainer-@estefanionsantos-blue.svg?style=flat-square)](https://estefanionsantos.github.io/)
[![Source Code](http://img.shields.io/badge/source-rubricate/mailer-blue.svg?style=flat-square)](https://github.com/rubricate/mailer)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/rubricate/mailer.svg?style=flat-square)](https://packagist.org/packages/rubricate/mailer)
[![Latest Version](https://img.shields.io/github/release/rubricate/mailer.svg?style=flat-square)](https://github.com/rubricate/mailer/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/rubricate/mailer.svg?style=flat-square)](https://packagist.org/packages/rubricate/mailer)

#### Last Version
```
$ composer require rubricate/mailer
```

Documentation is at https://rubricate.github.io/components/mailer

#### Requirements
- PHP server with sockets and TLS/SSL support

Check if your PHP server supports sockets and TLS/SSL:

#### 1. Sockets support in PHP
If sockets appears, support is enabled.
```
php -m | grep sockets
```

or code:

```php
if (extension_loaded('sockets')) {
    echo "Sockets extension is enabled.";
} else {
    echo "Sockets extension is NOT enabled.";
}
```


#### 2. TLS/SSL support (via OpenSSL)
TLS is supported via the openssl extension. This extension is used for secure connections (SMTP with TLS/SSL, HTTPS, etc.).
```
php -m | grep openssl
```

or via code:

```php
if (extension_loaded('openssl')) {
    echo "OpenSSL is enabled (TLS/SSL support)";
} else {
    echo "OpenSSL is NOT enabled";
}
```


#### Usage example
```php

$mailer = new SmtpMailer(
    'smtp.gmail.com', // Host
    587, // Port
    'yourmail@gmail.com', // User
    'your_password_or_appkey', // Password or App Password
    'yourmail@gmail.com', // Sender
    'Your Name', // Sender's Name
    true // Enable debugging
);

try {
    $mailer->send(
        'destination@example.com', // Recipient
        'Pure SMTP email test', // Subject
        '<h1>Working!</h1><p>This is a test via Rubricate Mailer.</p>', // Body
        true // Send as HTML
    );

    echo "Email sent successfully!";

} catch (Exception $e) {
    echo "Error sending: " . $e->getMessage(); 
}

```



## Credits

- [Estefanio N Santos](https://github.com/estefanionsantos) (Developer)
- [All Contributors](https://github.com/rubricate/mailer/contributors) (Let's program)

## License

The MIT License (MIT). Please see [License File](https://github.com/rubricate/mailer/master/LICENSE) for more
information.



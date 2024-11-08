
# RotMailer

A simple PHP library that rotates email sending between multiple SMTP accounts to bypass sending limits.

## Features
- Randomized SMTP account selection for load balancing.
- Supports single and multiple recipients.
- Allows custom email content, including HTML and plain text.
- Easily configurable and extendable.

## Installation

Install the library using Composer:

```bash
composer require rotmailer/rotmailer
```

## Requirements
- PHP 7.0 or higher
- [PHPMailer](https://github.com/PHPMailer/PHPMailer)
- Enabled `ext-dom` PHP extension (required by PHPMailer)

## Usage

### 1. Create SMTP Configuration
Define your SMTP accounts as an array of configurations:

```php
$smtpConfigs = [
    [
        'host' => 'smtp.example.com',
        'username' => 'user1@example.com',
        'password' => 'password1',
        'encryption' => 'tls',
        'port' => 587,
        'from' => ['noreply@example.com', 'Example Support']
    ],
    [
        'host' => 'smtp.another.com',
        'username' => 'user2@another.com',
        'password' => 'password2',
        'encryption' => 'ssl',
        'port' => 465,
        'from' => ['noreply@another.com', 'Another Support']
    ]
];
```

### 2. Initialize `RotMailer`
Pass the SMTP configurations to the `RotMailer` class and start sending emails:

```php
require 'vendor/autoload.php';

use RotMailer\RotMailer;

$mailer = new RotMailer($smtpConfigs);

try {
    $mailer->randomizeSMTP()
           ->addRecipient('recipient@example.com')
           ->setSubject('Test Email')
           ->setBody('<h1>Hello, World!</h1>')
           ->send();

    echo "Email sent successfully!";
} catch (\RuntimeException $e) {
    echo "Error: " . $e->getMessage();
}
```

## API

### `__construct(array $smtpConfigs)`
Initializes the `RotMailer` with an array of SMTP configurations.

### `randomizeSMTP(int $debug = 0): self`
Selects a random SMTP configuration and sets it for the email. Optionally enable debugging with `$debug`.

### `setFrom(string $fromEmail, string $fromName): self`
Sets the "From" address.

### `addRecipient(string $to): self`
Adds a single recipient.

### `addRecipients(array $recipients): self`
Adds multiple recipients.

### `setSubject(string $subject): self`
Sets the email subject.

### `setBody(string $body, bool $isHTML = true): self`
Sets the email body. Supports both HTML and plain text.

### `addAttachment(string $filePath, string $fileName = ''): self`
Adds an attachment to the email.

### `send(): bool`
Sends the email. Returns `true` on success or throws a `RuntimeException` on failure.

## Testing

Run the tests using PHPUnit:

```bash
php vendor/bin/phpunit tests
```

## Contributing

Contributions are welcome! Please open an issue or submit a pull request with your improvements.

## License

RotMailer is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.



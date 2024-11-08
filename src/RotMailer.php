<?php

namespace RotMailer;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class RotMailer
{
    private array $smtpConfigs;
    private PHPMailer $mail;

    public function __construct(array $smtpConfigs)
    {
        $this->validateSMTPConfigs($smtpConfigs);
        $this->smtpConfigs = $smtpConfigs;
        $this->mail = new PHPMailer(true);
    }

    private function validateSMTPConfigs(array $configs): void
    {
        $requiredKeys = ['host', 'username', 'password', 'encryption', 'port', 'from'];
        foreach ($configs as $config) {
            foreach ($requiredKeys as $key) {
                if (!array_key_exists($key, $config)) {
                    throw new \InvalidArgumentException("Missing SMTP config key: $key");
                }
            }
        }
    }

    public function randomizeSMTP(int $debug = 0): self
    {
        $smtpConfig = $this->smtpConfigs[array_rand($this->smtpConfigs)];

        $this->mail->isSMTP();
        $this->mail->SMTPDebug = $debug;
        $this->mail->Host = $smtpConfig['host'];
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $smtpConfig['username'];
        $this->mail->Password = $smtpConfig['password'];
        $this->mail->SMTPSecure = $smtpConfig['encryption'];
        $this->mail->Port = $smtpConfig['port'];
        $this->mail->setFrom($smtpConfig['from'][0], $smtpConfig['from'][1]);

        return $this;
    }

    public function setFrom(string $fromEmail, string $fromName): self
    {
        $this->mail->setFrom($fromEmail, $fromName);
        return $this;
    }

    public function addRecipient(string $to): self
    {
        $this->mail->addAddress($to);
        return $this;
    }

    public function addRecipients(array $recipients): self
    {
        foreach ($recipients as $recipient) {
            $this->mail->addAddress($recipient);
        }
        return $this;
    }

    public function addAttachment(string $filePath, string $fileName = ''): self
    {
        $this->mail->addAttachment($filePath, $fileName);
        return $this;
    }

    public function setSubject(string $subject): self
    {
        $this->mail->Subject = $subject;
        return $this;
    }

    public function setBody(string $body, bool $isHTML = true): self
    {
        $this->mail->isHTML($isHTML);
        $this->mail->Body = $body;
        $this->mail->AltBody = strip_tags($body);
        return $this;
    }

    public function send(): bool
    {
        try {
            return $this->mail->send();
        } catch (Exception $e) {
            throw new \RuntimeException("Mail sending failed: " . $e->getMessage());
        }
    }
}

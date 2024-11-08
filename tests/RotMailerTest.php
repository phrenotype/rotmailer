<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use RotMailer\RotMailer;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class RotMailerTest extends TestCase
{
    private array $smtpConfigs;

    protected function setUp(): void
    {
        $this->smtpConfigs = [
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
    }

    public function testRandomizeSMTPConfig(): void
    {
        $mailer = new RotMailer($this->smtpConfigs);

        $mailer->randomizeSMTP();

        
        $reflection = new \ReflectionClass($mailer);
        $mailProperty = $reflection->getProperty('mail');
        $mailProperty->setAccessible(true);
        $mail = $mailProperty->getValue($mailer);

        // Assert that PHPMailer is configured with one of the SMTP configs
        $this->assertContains($mail->Host, array_column($this->smtpConfigs, 'host'));
    }

    public function testSetFrom(): void
    {
        $mailer = new RotMailer($this->smtpConfigs);
        $mailer->setFrom('customsender@example.com', 'Custom Sender');

        $reflection = new \ReflectionClass($mailer);
        $mailProperty = $reflection->getProperty('mail');
        $mailProperty->setAccessible(true);
        $mail = $mailProperty->getValue($mailer);

        $this->assertEquals('customsender@example.com', $mail->From);
        $this->assertEquals('Custom Sender', $mail->FromName);
    }

    public function testAddRecipient(): void
    {
        $mailer = new RotMailer($this->smtpConfigs);
        $mailer->addRecipient('recipient@example.com');

        $reflection = new \ReflectionClass($mailer);
        $mailProperty = $reflection->getProperty('mail');
        $mailProperty->setAccessible(true);
        $mail = $mailProperty->getValue($mailer);

        $this->assertCount(1, $mail->getToAddresses());
        $this->assertEquals('recipient@example.com', $mail->getToAddresses()[0][0]);
    }

    public function testAddRecipients(): void
    {
        $mailer = new RotMailer($this->smtpConfigs);
        $mailer->addRecipients(['recipient1@example.com', 'recipient2@example.com']);

        $reflection = new \ReflectionClass($mailer);
        $mailProperty = $reflection->getProperty('mail');
        $mailProperty->setAccessible(true);
        $mail = $mailProperty->getValue($mailer);

        $this->assertCount(2, $mail->getToAddresses());
        $this->assertEquals('recipient1@example.com', $mail->getToAddresses()[0][0]);
        $this->assertEquals('recipient2@example.com', $mail->getToAddresses()[1][0]);
    }

    public function testSetSubject(): void
    {
        $mailer = new RotMailer($this->smtpConfigs);
        $mailer->setSubject('Test Subject');

        $reflection = new \ReflectionClass($mailer);
        $mailProperty = $reflection->getProperty('mail');
        $mailProperty->setAccessible(true);
        $mail = $mailProperty->getValue($mailer);

        $this->assertEquals('Test Subject', $mail->Subject);
    }

    public function testSetBody(): void
    {
        $mailer = new RotMailer($this->smtpConfigs);
        $mailer->setBody('<h1>Test Body</h1>');

        $reflection = new \ReflectionClass($mailer);
        $mailProperty = $reflection->getProperty('mail');
        $mailProperty->setAccessible(true);
        $mail = $mailProperty->getValue($mailer);

        $this->assertEquals('<h1>Test Body</h1>', $mail->Body);
        $this->assertEquals('Test Body', $mail->AltBody);
    }

    public function testSendSuccess(): void
    {
        $mailerMock = $this->createPartialMock(PHPMailer::class, ['send']);
        $mailerMock->method('send')->willReturn(true);

        $mailer = new RotMailer($this->smtpConfigs);
        $reflection = new \ReflectionClass($mailer);
        $mailProperty = $reflection->getProperty('mail');
        $mailProperty->setAccessible(true);
        $mailProperty->setValue($mailer, $mailerMock);

        $this->assertTrue($mailer->send());
    }

    public function testSendFailure(): void
    {
        $this->expectException(\RuntimeException::class);

        $mailerMock = $this->createPartialMock(PHPMailer::class, ['send']);
        $mailerMock->method('send')->willThrowException(new Exception('SMTP Error'));

        $mailer = new RotMailer($this->smtpConfigs);
        $reflection = new \ReflectionClass($mailer);
        $mailProperty = $reflection->getProperty('mail');
        $mailProperty->setAccessible(true);
        $mailProperty->setValue($mailer, $mailerMock);

        $mailer->send();
    }
}

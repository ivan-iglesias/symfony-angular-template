<?php

namespace App\Shared\Infrastructure\Email;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class SymfonyEmailSender
{
    public function __construct(private MailerInterface $mailer) {}

    public function sendTemplate(string $to, string $subject, string $template, array $context): void
    {
        $email = (new TemplatedEmail())
            ->from('noreply@acme.com')
            ->to($to)
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($context);

        $this->mailer->send($email);
    }
}

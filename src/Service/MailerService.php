<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendWelcomeEmail(User $user)
    {
        $senderEmail = 'jo2024-studi@outlook.com';
        $senderName = 'JO2024-STUDI Support';

        $email = (new Email())
            ->from([$senderEmail => $senderName])
            ->to($user->getEmail())
            ->subject('Bienvenue sur l\'application JO2024 - STUDI')
            ->html('Vous avez créé un compte sur l\'application JO2024 - STUDI. Votre identifiant est : ' . $user->getUsername());

        $this->mailer->send($email);
    }
}
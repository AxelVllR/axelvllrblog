<?php

namespace App\Services;

use App\Renderer\Renderer;

class MailerManager {

    /**
     * @var Renderer
     */
    private $renderer;

    public function __construct()
    {
        $this->renderer = new Renderer();
    }

    public function sendMail($message) {
        // Create the Transport
        $transport = (new \Swift_SmtpTransport('0.0.0.0', 1025));
        // Create the Mailer using your created Transport
        $mailer = new \Swift_Mailer($transport);
        // Create a message

        // Send the message
        return $mailer->send($message);
    }

    public function sendForgottedPassword($user, $token) {
        $body = $this->renderer->display("mails/forgot_password.html.twig", ['user' => $user, 'token' => $token]);

        $message = (new \Swift_Message('Réinitialisation du mot de passe'))
            ->setFrom(['no-reply@axelvllr.com' => 'AxelVllR Blog'])
            ->setTo([$user->email])
            ->setBody($body, 'text/html')
        ;

        $this->sendMail($message);
    }

    public function sendSignInConfirm($user) {
        $body = $this->renderer->display("mails/sign_in_confirm.html.twig", ['user' => $user]);

        $message = (new \Swift_Message('Confirmation d\'inscription AxelVllR Blog !'))
            ->setFrom(['no-reply@axelvllr.com' => 'AxelVllR Blog'])
            ->setTo([$user['email']])
            ->setBody($body, 'text/html')
        ;

        $this->sendMail($message);
    }

    public function sendContact($values) {

        $body = $this->renderer->display("mails/contact.html.twig", ['contact' => $values]);

        $message = (new \Swift_Message('Nouvelle demande de contact !'))
            ->setFrom(['no-reply@axelvllr.com' => 'AxelVllR Blog'])
            ->setTo(['name@domain.org'])
            ->setBody($body, 'text/html')
        ;

        $this->sendMail($message);
    }
}
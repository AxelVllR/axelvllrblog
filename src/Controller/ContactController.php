<?php
namespace App\Controller;

use App\Forms\ContactForm;
use App\Globals\Session;
use App\Globals\Treatment;
use App\Renderer\Renderer;
use App\Services\MailerManager;
use Twig\Environment;

class ContactController {

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var MailerManager
     */
    private $mailer;

    public function __construct()
    {
        $this->mailer = new MailerManager();
    }

    public function show() {
        $contactForm = new ContactForm();
        $form = $contactForm->createForm((new Session())->getAll());
        $error = null;
        $success = null;
        if($contactForm->isSubmitted($contactForm)) {
            if($contactForm->isValid($contactForm)) {
                $this->mailer->sendContact((new Treatment())->getAllPosts());
                $success = 'Message envoyé !';
            } else {
                $error = $contactForm->getError();
                $form = $contactForm->createForm($_POST);
            }
        }

        $render = (new Renderer())->display("contact.html.twig", [
            "form" => $form,
            'error' => $error,
            'success' => $success
        ]);

        echo $render;

    }

}
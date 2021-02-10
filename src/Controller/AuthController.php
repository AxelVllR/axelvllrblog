<?php
namespace App\Controller;

use App\Forms\CommentForm;
use App\Forms\LoginForm;
use App\Forms\SignInForm;
use App\Globals\Session;
use App\Globals\Treatment;
use App\Renderer\Renderer;
use App\Repository\UserRepository;
use App\Services\MailerManager;

class AuthController {

    /**
     * @var Renderer
     */
    private Renderer $renderer;
    /**
     * @var UserRepository
     */
    private UserRepository $repository;
    /**
     * @var MailerManager
     */
    private MailerManager $mailer;

    public function __construct() {
        $this->renderer = new Renderer();
        $this->repository = new UserRepository();
        $this->mailer = new MailerManager();
    }

    public function signIn() {
        $signForm = new SignInForm();
        $form = $signForm->createForm(null, "S'inscrire");
        $error = null;
        $success = null;
        if($signForm->isSubmitted($signForm)) {
            if($signForm->isValid($signForm)) {
                $postValues = (new Treatment())->getAllPosts();
                $user = $this->repository->findOneBy([
                    "email" => $postValues['email']
                ]);
                if($user) {
                    $error = "Un utilisateur existe déjà avec cet Email";
                    $form = $signForm->createForm((new Treatment())->getAllPosts(), "Se connecter");
                } elseif($user = $this->repository->findOneBy(["username" => $postValues['username']])) {
                    $error = "Un utilisateur existe déjà avec ce pseudo";
                    $form = $signForm->createForm((new Treatment())->getAllPosts(), "Se connecter");
                } elseif($postValues['password'] !== $postValues['passwordConf']) {
                    $error = "Vos deux mots de passe ne correspondent pas..";
                    $form = $signForm->createForm((new Treatment())->getAllPosts(), "Se connecter");
                } else {
                    $user = $postValues;
                    unset($user['passwordConf']);
                    $user['password'] = password_hash($user['password'], PASSWORD_ARGON2ID);
                    $user['role'] = "ROLE_USER";
                    if($this->repository->post($user)) {
                        $this->mailer->sendSignInConfirm($user);
                        $success = "Vous êtes inscrit ! vous recevrez un mail dans les 5 minutes afin de confirmer votre inscription";
                    } else {
                        $error = 'Il y a eu une erreur dans votre inscription..';
                        $form = $signForm->createForm((new Treatment())->getAllPosts(), "Se connecter");
                    }
                }
            } else {
                $error = $signForm->getError();
                $form = $signForm->createForm((new Treatment())->getAllPosts(), "Se connecter");
            }
        }

        echo $this->renderer->display("auth/sign_in.html.twig", [
            'form' => $form,
            'error' => $error,
            'success' => $success
        ]);
    }
    
    public function login() {
        $loginForm = new LoginForm();
        $form = $loginForm->createForm(null, "Se connecter");
        $error = null;
        $success = null;
        if($loginForm->isSubmitted($loginForm)) {
            if($loginForm->isValid($loginForm)) {
                $postValues = (new Treatment())->getAllPosts();
                $user = $this->repository->findOneBy([
                    "email" => $postValues['email']
                ]);
                if(!empty($user)) {
                    if(password_verify($postValues['password'], $user->password)) {
                        $session = (new Session())
                            ->set([
                                'id' => $user->id,
                                'role' => $user->role,
                                'firstname' => $user->firstname,
                                'lastname' => $user->lastname,
                                'username' => $user->username,
                                'email' => $user->email
                            ]);
                        header('Location: /');
                    } else {
                        $error = "Il y a une erreur dans votre email ou mot de passe..";
                    }
                } else {
                    $error = "Il y a une erreur dans votre email ou mot de passe..";
                }
                // TODO : Verifs
            } else {
                $error = $loginForm->getError();
                $form = $loginForm->createForm((new Treatment())->getAllPosts(), "Se connecter");
            }
        }

        echo $this->renderer->display("auth/login.html.twig", [
            'form' => $form,
            'error' => $error,
            'success' => $success
        ]);
    }

    public function logout() {
        session_destroy();
        header('Location: /');
    }
}
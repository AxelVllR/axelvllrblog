<?php
namespace App\Controller;

use App\Forms\CommentForm;
use App\Forms\ForgotForm;
use App\Forms\LoginForm;
use App\Forms\PasswordUpdateForm;
use App\Forms\SignInForm;
use App\Forms\TokenForm;
use App\Forms\UserForm;
use App\Globals\Session;
use App\Globals\Treatment;
use App\Renderer\Renderer;
use App\Repository\UserRepository;
use App\Services\MailerManager;

class UserController {

    /**
     * @var Renderer
     */
    private Renderer $renderer;
    /**
     * @var UserRepository
     */
    private UserRepository $repository;
    private ?array $session;
    /**
     * @var MailerManager
     */
    private MailerManager $mailer;

    public function __construct() {
        $this->renderer = new Renderer();
        $this->repository = new UserRepository();
        $this->session = (new Session())->getAll();
        $this->mailer = new MailerManager();
    }

    public function forgotPassword() {
        $forgotForm = new ForgotForm();
        $form = $forgotForm->createForm(null, "Envoyer");
        $error = null;
        $success = null;
        if($forgotForm->isSubmitted($forgotForm)) {
            if ($forgotForm->isValid($forgotForm)) {
                $postValues = (new Treatment())->getAllPosts();
                $user = $this->repository->findOneBy([
                    "email" => $postValues['email']
                ]);
                if(!$user) {
                    $error = "Aucun utilisateur n'existe avec cet Email";
                    $form = $forgotForm->createForm((new Treatment())->getAllPosts(), "Envoyer");
                } else {
                    $token = rand(111111, 999999);
                    $user->token = $token;
                    $this->repository->update($user);
                    $this->mailer->sendForgottedPassword($user, $token);
                    header('Location: /forgot/u:' . $user->id);
                }

            } else {
                $error = $forgotForm->getError();
                $form = $forgotForm->createForm((new Treatment())->getAllPosts(), "Envoyer");
            }
        }

        echo $this->renderer->display("auth/forgot.html.twig", [
            'form' => $form,
            'error' => $error,
            'success' => $success
        ]);
    }

    public function sendToken($user) {
        $success = null;
        $error = null;
        $postValues = (new Treatment())->getAllPosts();

        $exp = explode(":", $user);
        $userId = $exp[1];
        $user = $this->repository->findOneBy(["id" => $userId]);

        if(empty($user->token) || empty($user)) {
            header('Location: /forgot');
        }

        $tokenForm = new TokenForm();
        $form = $tokenForm->createForm(null, "Envoyer", 'u:' . $user->id);
        if($tokenForm->isSubmitted($tokenForm)) {
            if($tokenForm->isValid($tokenForm)) {
                if($postValues['token'] == $user->token) {
                    $newPassword = $this->GeneratePassword(8);
                    $success = "Token Valide, votre nouveau mot de passe est $newPassword, vous pourrez le changer dans la section profil du blog";
                    $user->password = password_hash($newPassword, PASSWORD_ARGON2ID);
                    $user->token = null;
                    $this->repository->update($user);
                    $form = null;
                } else {
                    $error = "Vous avez entré un token qui ne correspond pas";
                }
            } else {
                $error = $tokenForm->getError();
                $form = $tokenForm->createForm((new Treatment())->getAllPosts(), "Envoyer", 'u:' . $user->id);
            }
        }

        echo $this->renderer->display("auth/forgot.html.twig", [
            'title' => 'Entrez la clé à 6 chiffres que vous venez de recevoir par mail',
            'form' => $form,
            'error' => $error,
            'success' => $success
        ]);
    }

    function GeneratePassword($size)
    {
        $characters = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z");

        for($i=0;$i<$size;$i++)
        {
            $password .= ($i%2) ? strtoupper($characters[array_rand($characters)]) : $characters[array_rand($characters)];
        }

        return $password;
    }

    public function profile() {
        $userForm = new UserForm();
        $form = $userForm->createForm((new Session())->getAll(), "Modifier");
        $error = null;
        $success = null;
        if($userForm->isSubmitted($userForm)) {
            if($userForm->isValid($userForm)) {
                $postValues = (new Treatment())->getAllPosts();
                $user = $this->repository->findOneBy([
                    "email" => $postValues['email']
                ]);
                if($user && $postValues['email'] !== (new Session())->get('email')) {
                    $error = "Un utilisateur existe déjà avec cet Email";
                    $form = $userForm->createForm((new Treatment())->getAllPosts(), "Modifier");
                } elseif($user = $this->repository->findOneBy(["username" => $postValues['username']]) && $postValues['username'] !== (new Session)->get('username')) {
                    $error = "Un utilisateur existe déjà avec ce pseudo";
                    $form = $userForm->createForm((new Treatment())->getAllPosts(), "Modifier");
                } else {
                    $user = $postValues;
                    $user['id'] = $this->session['id'];
                    if($this->repository->update((object) $user)) {
                        (new Session())->set($user);
                        $success = "Profil Modifié";
                        $form = $userForm->createForm((new Session())->getAll(), "Modifier");
                    } else {
                        $error = 'Il y a eu une erreur dans la modification de votre profil..';
                        $form = $userForm->createForm((new Treatment())->getAllPosts(), "Modifier");
                    }
                }
            } else {
                $error = $userForm->getError();
                $form = $userForm->createForm((new Treatment())->getAllPosts(), "Modifier");
            }
        }

        echo $this->renderer->display("profile.html.twig", [
            'form' => $form,
            'error' => $error,
            'success' => $success
        ]);
    }

    public function updatePassword() {
        $updateForm = new PasswordUpdateForm();
        $form = $updateForm->createForm((new Session())->getAll(), "Modifier");
        $error = null;
        $success = null;
        if($updateForm->isSubmitted($updateForm)) {
            if($updateForm->isValid($updateForm)) {
                $postValues = (new Treatment())->getAllPosts();
                $user = $this->repository->findOneBy(["id" => (new Session())->get('id')]);
                if($postValues['new_password'] !== $postValues['new_password_conf']) {
                    $error = "Votre nouveau mot de passe et sa confirmation ne correspondent pas..";
                } elseif (password_verify($postValues['old_password'], $user->password)) {
                    $user->password = password_hash($postValues['new_password'], PASSWORD_ARGON2ID);
                    $this->repository->update($user);
                    $success = "Mot de passe Modifié !";
                }
            } else {
                $error = $updateForm->getError();
                $form = $updateForm->createForm((new Treatment())->getAllPosts(), "Modifier");
            }
        }

        echo $this->renderer->display("update_password.html.twig", [
            'form' => $form,
            'error' => $error,
            'success' => $success
        ]);
    }
}
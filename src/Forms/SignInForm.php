<?php
namespace App\Forms;

class SignInForm extends Form {

    const FIELDS = [
        [
            "attr" => [
                'type' => 'text',
                'name' => 'lastname',
                'placeholder' => 'Nom',
            ],
            'translate' => 'Nom',
            'required' => true
        ],
        [
            'attr' => [
                'type' => 'text',
                'name' => 'firstname',
                'placeholder' => 'Prénom',
            ],
            'translate' => 'Prénom',
            'required' => true
        ],
        [
            "attr" => [
                'type' => 'text',
                'name' => 'email',
                'placeholder' => 'Votre Mail',
            ],
            'translate' => 'Email',
            'required' => true
        ],
        [
            'attr' => [
                'type' => 'password',
                'name' => 'password',
                'placeholder' => 'Mot de passe',
            ],
            'translate' => 'Mot de passe',
            'required' => true
        ],
        [
            'attr' => [
                'type' => 'password',
                'name' => 'passwordConf',
                'placeholder' => 'Confirmation du Mot de passe',
            ],
            'translate' => 'Confirmation du Mot de passe',
            'required' => true
        ],
        [
            'attr' => [
                'type' => 'text',
                'name' => 'username',
                'placeholder' => 'Pseudo (ex: JohnDoe)',
            ],
            'translate' => 'Pseudo',
            'required' => true
        ],
    ];

    public function createForm($value = null, $submit = 'Envoyer') {
        return $this->createField(self::FIELDS, 'POST', '/sign-in', $value, $submit);
    }


}
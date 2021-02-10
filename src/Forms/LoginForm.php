<?php
namespace App\Forms;

class LoginForm extends Form {

    const FIELDS = [
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
                'placeholder' => '*****',
            ],
            'translate' => 'Mot de passe',
            'required' => true
        ]
    ];

    public function createForm($value = null, $submit = 'Envoyer') {
        return $this->createField(self::FIELDS, 'POST', '/login', $value, $submit);
    }


}
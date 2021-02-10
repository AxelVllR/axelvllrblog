<?php
namespace App\Forms;

class PasswordUpdateForm extends Form {

    const FIELDS = [
        [
            "attr" => [
                'type' => 'password',
                'name' => 'old_password',
                'placeholder' => 'Votre mot de passe actuel',
            ],
            'translate' => 'Mot de passe actuel',
            'required' => true
        ],
        [
            "attr" => [
                'type' => 'password',
                'name' => 'new_password',
                'placeholder' => 'Nouveau mot de passe',
            ],
            'translate' => 'nouveau mot de passe',
            'required' => true
        ],
        [
            "attr" => [
                'type' => 'password',
                'name' => 'new_password_conf',
                'placeholder' => 'Confirmation nouveau mot de passe',
            ],
            'translate' => 'confirmation du nouveau mot de passe',
            'required' => true
        ]
    ];

    public function createForm($value = null, $submit = 'Envoyer') {
        return $this->createField(self::FIELDS, 'POST', '/profile/update_password', $value, $submit);
    }


}
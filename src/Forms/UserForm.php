<?php
namespace App\Forms;

class UserForm extends Form {

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
                'type' => 'text',
                'name' => 'username',
                'placeholder' => 'Pseudo (ex: JohnDoe)',
            ],
            'translate' => 'Pseudo',
            'required' => true
        ],
    ];

    public function createForm($value = null, $submit = 'Modifier') {
        return $this->createField(self::FIELDS, 'POST', '/profile', $value, $submit);
    }


}
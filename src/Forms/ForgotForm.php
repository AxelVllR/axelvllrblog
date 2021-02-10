<?php
namespace App\Forms;

class ForgotForm extends Form {

    const FIELDS = [
        [
            "attr" => [
                'type' => 'text',
                'name' => 'email',
                'placeholder' => 'Votre Mail',
            ],
            'translate' => 'Email',
            'required' => true
        ]
    ];

    public function createForm($value = null, $submit = 'Envoyer') {
        return $this->createField(self::FIELDS, 'POST', '/forgot', $value, $submit);
    }


}
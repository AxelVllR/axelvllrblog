<?php
namespace App\Forms;

class TokenForm extends Form {

    const FIELDS = [
        [
            "attr" => [
                'type' => 'text',
                'name' => 'token',
                'placeholder' => 'Clé a 6 chiffres que vous venez de recevoir par mail',
            ],
            'translate' => 'Clé reçue par mail',
            'required' => true
        ]
    ];

    public function createForm($value = null, $submit = 'Envoyer', $subRoute) {
        return $this->createField(self::FIELDS, 'POST', '/forgot/' . $subRoute, $value, $submit);
    }


}
<?php
namespace App\Forms;

class ContactForm extends Form {

    const FIELDS = [
        [
            "attr" => [
                'type' => 'text',
                'name' => 'lastname',
                'placeholder' => 'Votre nom',
            ],
            'translate' => 'Nom',
            'required' => true
        ],
        [
            'attr' => [
                'type' => 'text',
                'name' => 'firstname',
                'placeholder' => 'Votre Prénom',
            ],
            'translate' => 'Prénom',
            'required' => true
        ],
        [
            'attr' => [
                'type' => 'text',
                'name' => 'email',
                'placeholder' => 'Votre Mail',
            ],
            'translate' => 'Mail',
            'required' => true
        ],
        [
            'attr' => [
                'type' => 'textarea',
                'name' => 'message',
                'placeholder' => 'Votre Message',
                "rows" => "5",
            ],
            'translate' => 'Message',
            'required' => true
        ]
    ];

    public function createForm($value = null) {
        return $this->createField(self::FIELDS, 'POST', '/contact', $value);
    }


}
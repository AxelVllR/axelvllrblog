<?php
namespace App\Forms;

class CommentForm extends Form {

    const FIELDS = [
        [
            'attr' => [
                'type' => 'textarea',
                'name' => 'comment',
                'placeholder' => 'Votre commentaire',
                "rows" => "5",
            ],
            'translate' => 'Commentaire',
            'required' => true
        ]
    ];

    public function createForm($value = null, $subRoute) {
        return $this->createField(self::FIELDS, 'POST', "/blog/$subRoute", $value);
    }


}
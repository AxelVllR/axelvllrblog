<?php
namespace App\Forms;

use App\Repository\CategoryRepository;
use App\Repository\UserRepository;

class FiltersForm extends Form {

    const FIELDS = [
        [
            'attr' => [
                'type' => 'select',
                'name' => 'user_id',
                'placeholder' => 'Auteur',
                'options' => ''
            ],
            'translate' => 'Auteur',
            'required' => false
        ],
        [
            'attr' => [
                'type' => 'select',
                'name' => 'category_id',
                'placeholder' => 'Catégorie',
                'options' => ''
            ],
            'translate' => 'catégorie',
            'required' => false
        ]
    ];

    public function createForm($value = null, $submit = 'Envoyer') {
        $uRepo = new UserRepository();
        $cRepo = new CategoryRepository();
        $fields = self::FIELDS;
        $fields[0]['attr']['options'] = $uRepo->getUserSelect();
        $fields[1]['attr']['options'] = $cRepo->getCategoriesSelect();
        return $this->createField($fields, 'POST', '/blog', $value, $submit);
    }


}
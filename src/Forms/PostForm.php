<?php
namespace App\Forms;

use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;

class PostForm extends Form {

    const CATEGORIES = [
        "Sport" => "Sport",
        "Informatique" => "Informatique",
        "LifeStyle" => "LifeStyle",
        "Web Developpement" => "Web Developpement"
    ];

    const FIELDS = [
        [
            'attr' => [
                'type' => 'text',
                'name' => 'title',
                'placeholder' => 'Titre',
            ],
            'translate' => 'titre',
            'required' => true
        ],
        [
            'attr' => [
                'type' => 'select',
                'name' => 'user_id',
                'placeholder' => 'Auteur',
                'options' => ''
            ],
            'translate' => 'Auteur',
            'required' => true
        ],
        [
            'attr' => [
                'type' => 'select',
                'name' => 'category_id',
                'placeholder' => 'Catégorie',
                'options' => ''
            ],
            'translate' => 'catégorie',
            'required' => true
        ],
        [
            'attr' => [
                'type' => 'file',
                'name' => 'image',
                'placeholder' => 'Image',
            ],
            'translate' => 'Image',
            'required' => false
        ],
        [
            'attr' => [
                'type' => 'text',
                'name' => 'chapo',
                'placeholder' => 'Chapô',
            ],
            'translate' => 'Chapô',
            'required' => true
        ],
        [
            'attr' => [
                'type' => 'textarea',
                'name' => 'text',
                'placeholder' => 'Contenu de votre Article',
                "rows" => "25",
            ],
            'translate' => 'Contenu',
            'required' => true
        ]
    ];

    public function createForm($value = null, $subRoute = null, $route = "/admin/posts") {
        $uRepo = new UserRepository();
        $cRepo = new CategoryRepository();
        $fields = self::FIELDS;
        $fields[1]['attr']['options'] = $uRepo->getUserSelect();
        $fields[2]['attr']['options'] = $cRepo->getCategoriesSelect();
        return $this->createField($fields, 'POST', $route, $value, 'Publier');
    }


}
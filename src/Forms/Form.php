<?php
namespace App\Forms;

use App\Globals\Session;
use App\Globals\Treatment;

class Form {
    /**
     * @var string
     */
    private $error;

    public function createField($fields, $method, $url, $values = null, $submit = 'Envoyer') {
        $form = "<form  method='$method' action='$url' enctype='multipart/form-data' class='d-flex flex-column align-items-center justify-content-center'>";
        foreach($fields as $field) {
            if(!empty($values)) {
                $value = $this->setField($values[$field['attr']['name']]);
            } else {
                $value = '';
            }
            $list = $this->listItems($field['attr']);
            if($field['attr']['type'] == 'textarea') {
                $form .= '<textarea '. $list . ' style="width: 100%" class="form-control m-2">'. $value . '</textarea>';
                continue;
            } elseif ($field['attr']['type'] == 'select') {
                $form .= $this->createSelect($field['attr'], $value);
                continue;
            } elseif ($field['attr']['type'] == 'file') {
                $form .= $this->createFile($field['attr'], $value);
                continue;
            }
            $form .= '<input class="form-control m-2" '. $list .' value="'.$value.'">';
        }
        $form .= $this->createSubmit($submit);
        $form .= "<form>";
        return $form;
    }

    public function createFile($attrs, $value) {
        $statement = '';
        if($value) {
            $statement .= "<img src='/PostsImages/$value' class='img-fluid m-4'>
            <p>Vous souhaitez changer l'image ? veuillez en uploader une nouvelle</p>";
        }
        $statement .= '
            <div class="input-group mb-3">
              <div class="custom-file">
                <input accept="image/png, image/jpeg" type="file" class="custom-file-input" name="'. $attrs['name'] .'" id="exampleInputFile">
                <label class="custom-file-label" for="exampleInputFile">'. $attrs['placeholder'] .'</label>
              </div>
            </div>
        ';

        return $statement;
    }

    public function createSelect($attrs, $value) {
        $statement = '
            <div class="form-group w-100">
              <label for="'. $attrs['name'] .'">'. $attrs['placeholder'] .'</label>
                <select class="form-control" name="'. $attrs['name'] .'" style="width: 100%;">
                    <option value="">Choisissez une option</option>
            ';
        foreach ($attrs['options'] as $key => $option) {
            $selected = '';
            $sessionUser = (new Session())->get('id');
            if($attrs['name'] == 'user_id' && isset($sessionUser) && !empty($sessionUser)) {
                if((int) $sessionUser == $key) {
                    $selected = 'selected';
                }
            }

            if($key == $value) {
                $selected = 'selected';
            }

            $statement .= ' <option value="' . $key . '"' . $selected . '>'. $option .'</option>';
        }
        $statement .= '</select></div>';
        return $statement;
    }

    public function listItems($attributes) {
        $attrs = '';
        foreach($attributes as $key => $attribute) {
            $attrs .= "$key='$attribute'";
        }
        return $attrs;
    }

    public function setField($field) {
        if(isset($field) && !empty($field)) {
            return $field;
        } else {
            return '';
        }
    }

    public function createSubmit($submit) {
        return "<button class='btn btn-outline-info align-self-end mt-2' type='submit' value='send'>$submit</button>";
    }

    public function isValid($formEntity) {
        $treatment = new Treatment();
        // Récuperer la variable superglobale dans les params
        $fields = $formEntity::FIELDS;
        foreach($fields as $field) {
            if(isset($field['required']) && $field['required'] === true) {
                $value = $treatment->getPost($field['attr']['name']);
                if(isset($value) && !empty($value)) {
                    continue;
                } else {
                    $this->error = 'Le champs ' . $field['translate'] . ' est vide !';
                    return false;
                }
            }
        }

        return true;
    }

    public function isSubmitted($formEntity) {
        $treatment = new Treatment();
        $fields = $formEntity::FIELDS;
        foreach($fields as $field) {
            $value = $treatment->getPost($field['attr']['name']);
            if(isset($value)) {
                continue;
            } elseif($field['attr']['type'] == 'file') {
                continue;
            } else {
                return false;
            }
        }

        return true;
    }

    public function getError() {
        return $this->error;
    }

}
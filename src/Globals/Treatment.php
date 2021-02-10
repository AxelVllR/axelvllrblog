<?php
namespace App\Globals;

class Treatment {


    public function getGet($key) {
        if(isset($key) && isset($_GET[$key])) {
            return htmlspecialchars($_GET[$key]);
        }

        return null;
    }

    public function getPost($key) {
        if(isset($key) && isset($_POST[$key])) {
            return htmlspecialchars($_POST[$key]);
        }

        return null;
    }

    public function getAllPosts(): array
    {
        $return = [];
        foreach($_POST as $key => $value){
            $return[$key] = htmlspecialchars($value);
        }
        return $return;
    }

}
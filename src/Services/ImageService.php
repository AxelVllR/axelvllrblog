<?php
namespace App\Services;

class ImageService {

    private $error;

    public function getFile() {
        return $_FILES;
    }

    public function getError() {
        return $this->error;
    }

    public function isImageUploaded($name): bool
    {
        $files = $this->getFile();
        if(empty($files)) {
            return false;
        }

        if(empty($files[$name]['tmp_name'])) {
            return false;
        }

        return true;
    }

    public function deleteImage($filename, $publicDir) {
        $path = __DIR__ . '/../../public/' . $publicDir . '/' . $filename;
        unlink($path);
    }

    public function saveImage($filename, $publicDir) {
        $dir = __DIR__ . '/../../public/' . $publicDir . '/';
        $files = $this->getFile();
        if (is_uploaded_file($files[$filename]["tmp_name"])) {
            $rename = str_replace(" ", "_", $files[$filename]['name']);
            if(move_uploaded_file($_FILES[$filename]['tmp_name'], $dir . $rename))
            {
                return $rename;
            }
            else
            {
                $this->error = "Il y a eu un problème dans l'upload de votre image..";
                return false;
            }
        } else {
            $this->error = "Veuillez uploader une image !";
            return false;
        }
    }
}
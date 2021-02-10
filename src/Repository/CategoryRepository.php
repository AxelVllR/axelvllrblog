<?php
namespace App\Repository;

use App\Globals\Session;
use PDO;

class CategoryRepository extends Connexion
{
    public function __construct($file = __DIR__ . '/../../config.ini')
    {
        if (!$settings = parse_ini_file($file, TRUE)) throw new exception('Unable to open ' . $file . '.');

        $dns = $settings['database']['driver'] .
            ':host=' . $settings['database']['host'] .
            ((!empty($settings['database']['port'])) ? (';port=' . $settings['database']['port']) : '') .
            ';dbname=' . $settings['database']['schema'];

        parent::__construct($dns, $settings['database']['username'], $settings['database']['password']);
    }


    public function update($category) {
        $query = 'UPDATE categories SET ';
        $k = 0;
        foreach($category as $key => $value) {
            if($key !== 'id') {
                if($k !== 0) {
                    $query .= ", ";
                }
                $query .= $key . ' = "' . str_replace('"', "\\", $value) . '"';
                $k++;
            }
        }
        $query .= "WHERE id = '" . $category->id . "'";
        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        return true;
    }

    public function post($values) {
        $col = [];
        $val = [];
        foreach($values as $key => $value) {
            $col[] = $key;
            $val[] = '"'.str_replace('"', "\\", $value).'"';
        }

        $session = (new Session())->getAll();

        $query = "INSERT INTO categories (" . implode(', ', $col) . ", views) VALUES (" . implode(", ", $val) . ", 0)";
        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        return true;
    }

    public function getCategoriesSelect() {
        $query = "SELECT * FROM categories";
        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        $categories = $stmt->fetchAll();

        $fields = [];
        foreach($categories as $category) {
            $fields[$category->id] = $category->name;
        }
        return $fields;
    }

    public function delete($id) {
        $query = 'DELETE FROM categories WHERE id = :id';

        $stmt = $this->bdd->prepare($query);
        $this->bdd->bindParam('id', $id);
        $stmt->execute();
        return true;
    }

    public function findOneBy($params = []) {
        $query = "SELECT * FROM categories WHERE ";
        $k = 0;
        foreach ($params as $key => $param) {
            if($k !== 0) {
                $query .= 'AND ';
            }
            $query .= "$key = $param ";
            $k ++;
        }
        $query .= "LIMIT 1";
        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        $category = $stmt->fetch();
        return $category;
    }



    public function findAll($limit = null) {
        $query = "SELECT * FROM categories";
        if(!empty($limit)) {
            $query .= ' LIMIT ' . $limit;
        }
        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        $category = $stmt->fetchAll();
        return $category;
    }
}

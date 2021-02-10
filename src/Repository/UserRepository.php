<?php
namespace App\Repository;

use App\Globals\Session;
use PDO;

class UserRepository extends Connexion
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

    public function getUserSelect() {
        $query = "SELECT * FROM users WHERE role = 'ROLE_ADMIN'";
        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        $users = $stmt->fetchAll();

        $fields = [];
        foreach($users as $user) {
            $fields[$user->id] = $user->username;
        }
        return $fields;
    }

    public function update($user) {
        $query = 'UPDATE users SET ';
        $k = 0;
        foreach($user as $key => $value) {
            if($key !== 'id') {
                if($k !== 0) {
                    $query .= ", ";
                }
                $query .= "$key = '$value'";
                $k++;
            }
        }
        $query .= "WHERE id = '" . $user->id . "'";
        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        return true;
    }

    public function delete($id) {
        $query = 'DELETE FROM users WHERE id = ' . $id;
        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        return true;
    }

    public function fillEntities($users, $solo = false) {
        if($solo) {
            $user_id = $users->user_id;
        } else {
            foreach ($users as $post) {
                $user_id = $post->user_id;
            }
        }
    }

    public function post($values): bool {
        $col = [];
        $val = [];
        foreach($values as $key => $value) {
            $col[] = $key;
            $val[] = '"'.$value.'"';
        }

        $session = (new Session())->getAll();

        $query = "INSERT INTO users (" . implode(', ', $col) . ") VALUES (" . implode(", ", $val) . ")";
        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        return true;
    }

    public function findNewPosts() {
        $query = "SELECT * FROM users ORDER BY id DESC LIMIT 5";
        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        $users = $stmt->fetchAll();
        return $users;
    }

    public function findOneBy($params = []) {
        $query = "SELECT * FROM users WHERE ";
        $k = 0;
        foreach ($params as $key => $param) {
            if($k !== 0) {
                $query .= 'AND ';
            }
            $query .= $key . ' = "' . $param . '"';
            $k ++;
        }
        $query .= "LIMIT 1";
        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        $users = $stmt->fetch();
        return $users;
    }



    public function findAll($limit = null) {
        $query = "SELECT * FROM users";
        if(!empty($limit)) {
            $query .= ' LIMIT ' . $limit;
        }
        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        $users = $stmt->fetchAll();
        return $users;
    }
}

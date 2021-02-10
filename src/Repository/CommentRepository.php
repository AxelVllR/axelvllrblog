<?php
namespace App\Repository;

use App\Globals\Session;
use App\Globals\Treatment;
use PDO;

class CommentRepository extends Connexion
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

    public function update($post) {
        $query = 'UPDATE comments SET ';
        $k = 0;
        foreach($post as $key => $value) {
            if($key !== 'id') {
                if($k !== 0) {
                    $query .= ", ";
                }
                $query .= $key . ' = "' . $value . '"';
                $k++;
            }
        }
        $query .= "WHERE id = '" . $post->id . "'";
        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        return true;
    }

    public function post($values, $postId) {
        $col = [];
        $val = [];
        foreach($values as $key => $value) {
            $col[] = $key;
            $val[] = '"'.$value.'"';
        }

        $session = (new Session())->getAll();

        $query = "INSERT INTO comments (" . implode(', ', $col) . ", post_id, user_id) VALUES (" . implode(", ", $val) . ", ". $postId .", ". $session['id'] .")";

        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        return true;
    }

    public function fillEntities($comments, $solo = false) {
        $userRepo = new UserRepository();
        $postRepo = new PostRepository();
        if($solo) {
            $user_id = $comments->user_id;
            $user = $userRepo->findOneBy([
                "id" => $user_id
            ]);
            $comments->user = $user;

            $post_id = $comments->post_id;
            $post = $postRepo->findOneBy([
                "id" => $post_id
            ]);
            $comments->post = $post;
        } else {
            foreach ($comments as $comment) {
                $user_id = $comment->user_id;
                $user = $userRepo->findOneBy([
                    "id" => $user_id
                ]);
                $comment->user = $user;

                $post_id = $comment->post_id;
                $post = $postRepo->findOneBy([
                    "id" => $post_id
                ]);
                $comment->post = $post;

            }
        }
        return $comments;

    }

    public function findNewPosts() {
        $query = "SELECT * FROM posts ORDER BY id DESC LIMIT 5";
        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        $posts = $stmt->fetchAll();
        return $posts;
    }

    public function findOneBy($params = []) {
        $query = "SELECT * FROM comments WHERE ";
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
        $com = $stmt->fetch();
        return $com;
    }

    public function commentToValidate() {
        $query = "SELECT * FROM comments WHERE verified IS NULL";
        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        $coms = $stmt->fetchAll();
        return $coms;
    }

    public function findBy($params = []) {
        $query = "SELECT * FROM comments WHERE ";
        $k = 0;
        foreach ($params as $key => $param) {
            if($k !== 0) {
                $query .= 'AND ';
            }
            $query .= "$key = '$param' ";
            $k ++;
        }
        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        $coms = $stmt->fetchAll();
        return $coms;
    }


    public function findAll($limit = null) {
        $query = "SELECT * FROM comments";
        if(!empty($limit)) {
            $query .= ' LIMIT ' . $limit;
        }
        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        $posts = $stmt->fetchAll();
        return $posts;
    }
}

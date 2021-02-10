<?php
namespace App\Repository;

use App\Globals\Session;
use PDO;

class PostRepository extends Connexion
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

    public function filter($filters) {
        $query = 'SELECT * FROM posts ';
        $k = 0;
        if(isset($filters['user_id']) && !empty($filters['user_id'])) {
            $query .= 'WHERE user_id = ' . intval($filters['user_id']);
        }

        if(isset($filters['category_id']) && !empty($filters['category_id'])) {
            if(isset($filters['user_id']) && !empty($filters['user_id'])) {
                $query .= ' AND ';
            } else {
                $query .= ' WHERE ';
            }
            $query .= 'category_id = ' . intval($filters['category_id']);
        }

        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        $posts = $stmt->fetchAll();
        return $posts;
    }


    public function update($post) {
        $query = 'UPDATE posts SET ';
        $k = 0;
        foreach($post as $key => $value) {
            if($key !== 'id') {
                if($k !== 0) {
                    $query .= ", ";
                }
                $query .= $key . ' = "' . str_replace('"', "\\", $value) . '"';
                $k++;
            }
        }
        $query .= "WHERE id = '" . $post->id . "'";
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

        $query = "INSERT INTO posts (" . implode(', ', $col) . ", views) VALUES (" . implode(", ", $val) . ", 0)";
        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        return true;
    }

    public function delete($id) {
        $query = 'DELETE FROM posts WHERE id = :id';
        $stmt = $this->bdd->prepare($query);
        $stmt->bindParam('id', $id);
        $stmt->execute();
        return true;
    }

    public function findByAuthor($search) {
        $query = 'SELECT * FROM posts INNER JOIN users ON posts.user_id = users.id WHERE users.username LIKE :search';
        $stmt = $this->bdd->prepare($query);
        $stmt->bindParam('search', $search);
        $stmt->execute();
        $posts = $stmt->fetchAll();
        return $posts;
    }

    public function fillEntities($posts, $solo = false) {
        $userRepo = new UserRepository();
        $commentRepo = new CommentRepository();
        $catRepo = new CategoryRepository();
        if($solo) {
            $user_id = $posts->user_id;
            $user = $userRepo->findOneBy([
                "id" => $user_id
            ]);
            $category_id = $posts->category_id;
            $category = $catRepo->findOneBy(['id' => $category_id]);
            $posts->user = $user;
            $posts->category = $category;

            $post_id = $posts->id;
            $comments = $commentRepo->findBy([
                "post_id" => $post_id,
                "verified" => true
            ]);
            foreach($comments as $comment) {
                $id = $comment->user_id;
                $user = $userRepo->findOneBy([
                    "id" => $id
                ]);
                $comment->user = $user;
            }
            $posts->comments = $comments;
        } else {
            foreach ($posts as $post) {
                $user_id = $post->user_id;
                $user = $userRepo->findOneBy([
                    "id" => $user_id
                ]);
                $category_id = $post->category_id;
                $category = $catRepo->findOneBy(['id' => $category_id]);
                $post->user = $user;
                $post->category = $category;

                $post_id = $post->id;
                $comments = $commentRepo->findBy([
                    "post_id" => $post_id,
                    "verified" => true
                ]);

                foreach($comments as $comment) {
                    $id = $comment->user_id;
                    $user = $userRepo->findOneBy([
                        "id" => $id
                    ]);
                    $comment->user = $user;
                }

                $post->comments = $comments;
            }
        }
        return $posts;

    }

    public function findNewPosts() {
        $query = "SELECT * FROM posts ORDER BY id DESC LIMIT 5";
        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        $posts = $stmt->fetchAll();
        return $posts;
    }

    public function findOneBy($params = []) {
        $query = "SELECT * FROM posts WHERE ";
        $k = 0;
        foreach ($params as $key => $param) {
            if($k !== 0) {
                $query .= 'AND ';
            }
            $query .= "$key = :$key ";
            $k ++;
        }
        $query .= "LIMIT 1";
        $stmt = $this->bdd->prepare($query);
        foreach ($params as $key => $param) {
            $stmt->bindParam($key, $param);
        }
        $stmt->execute();
        $post = $stmt->fetch();
        return $post;
    }



    public function findAll($limit = null) {
        $query = "SELECT * FROM posts";
        if(!empty($limit)) {
            $query .= ' LIMIT ' . $limit;
        }
        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        $posts = $stmt->fetchAll();
        return $posts;
    }

    public function findMoreThanViews($nbOfViews) {
        $query = "SELECT * FROM posts WHERE views >= $nbOfViews";
        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        $posts = $stmt->fetchAll();
        return $posts;
    }

    public function findAllNotDeleted() {
        $query = "SELECT * FROM posts";
        $stmt = $this->bdd->prepare($query);
        $stmt->execute();
        $posts = $stmt->fetchAll();
        return $posts;
    }
}

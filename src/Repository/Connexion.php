<?php

namespace App\Repository;

use PDO;

class Connexion
{
    private $login;
    private $pass;
    private $dns;
    public $bdd;

    public function __construct($dns, $login = 'axel', $pass = '')
    {
        $this->login = $login;
        $this->pass = $pass;
        $this->dns = $dns;
        $this->connexion();
    }


    private function connexion()
    {
        try {
            $bdd = new \PDO(
                $this->dns,
                $this->login,
                $this->pass
            );
            $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $bdd->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            $this->bdd = $bdd;
        } catch (PDOException $e) {
            $msg = 'ERREUR PDO dans ' . $e->getFile() . ' L.' . $e->getLine() . ' : ' . $e->getMessage();
            die($msg);
        }
    }

}
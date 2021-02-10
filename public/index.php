<?php
session_start();
//session_destroy();
ini_set('display_errors', 1);
require '../vendor/autoload.php';
$request = $_SERVER['REQUEST_URI'];
$exp = explode("?", $request);
//var_dump($request);
$app = new \App\App;


// echo $twig->render('test.html.twig');

$app->run($exp[0]);
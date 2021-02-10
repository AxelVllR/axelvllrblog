<?php
namespace App\Renderer;

use App\Globals\Session;
use mysql_xdevapi\Exception;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extra\String\StringExtension;

class Renderer {

    /**
     * @var Environment
     */
    private $twig;

    public function __construct() {
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../../src/templates');
        $this->twig = new Environment($loader);
    }

    public function display($template, $values = []): string
    {
        try {
            $session = (new Session())->getAll();
            $values['session'] = $session;
            $this->twig->addExtension(new StringExtension());
            return $this->twig->render($template, $values);
        } catch (Exception | LoaderError | RuntimeError | SyntaxError $e) {
            return $this->twig->render('404.html.twig');
        }
    }
}
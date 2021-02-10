<?php
namespace App\Controller;

use App\Renderer\Renderer;
use App\Repository\PostRepository;
use Twig\Environment;

class LandingController {

    /**
     * @var Environment
     */
    private $twig;

    public function __construct() {
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../../src/templates');
        $this->twig = new \Twig\Environment($loader, [
            'app_url' => 'localhost:8000',
        ]);
    }

    public function index() {
        $postRepo = new PostRepository();
        $newPosts = $postRepo->findNewPosts();
        $newPosts = $postRepo->fillEntities($newPosts);
        $render = (new Renderer())->display("index.html.twig", [
            'app_url' => 'localhost:8000',
            'newPosts' => $newPosts
        ]);

        echo $render;
    }
}
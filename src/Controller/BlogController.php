<?php

namespace App\Controller;

use App\Forms\CommentForm;
use App\Forms\ContactForm;
use App\Forms\FiltersForm;
use App\Globals\Treatment;
use App\Renderer\Renderer;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;

class BlogController {
    /**
     * @var PostRepository
     */
    private $repository;

    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * @var CommentRepository
     */
    private $cRepository;

    public function __construct() {
        $this->repository = new PostRepository();
        $this->renderer = new Renderer();
        $this->cRepository = new CommentRepository();
    }

    public function show() {
        $posts = $this->repository->findAllNotDeleted();
        $posts = $this->repository->fillEntities($posts);
        $filtersForm = new FiltersForm();
        $form = $filtersForm->createForm(null, 'Rechercher');
        $error = null;
        $success = null;
        if($filtersForm->isSubmitted($filtersForm)) {
            if($filtersForm->isValid($filtersForm)) {
                $filters = (new Treatment())->getAllPosts();
                $posts = $this->repository->filter($filters);
                $posts = $this->repository->fillEntities($posts);
            } else {
                $error = $filtersForm->getError();
                $form = $filtersForm->createForm((new Treatment())->getAllPosts(), 'Rechercher');
            }
        }

        echo $this->renderer->display('blog.html.twig', [
            'posts' => array_reverse($posts),
            'form' => $form,
            'error' => $error,
            'success' => $success
        ]);
    }

    public function showPost($postNeeded) {
        $postId = str_replace("post-", "", $postNeeded);
        $post = $this->repository->findOneBy([
            "id" => $postId
        ]);

        if($post) {
            $views = $post->views;
            $views++;
            $post->views = $views;
            $this->repository->update($post);
            $latest = $this->repository->fillEntities($this->repository->findNewPosts());
            $postFilled = $this->repository->fillEntities($post, true);

            $commentForm = new CommentForm();
            $form = $commentForm->createForm(null, $postNeeded);
            $error = null;
            $success = null;
            if($commentForm->isSubmitted($commentForm)) {
                if($commentForm->isValid($commentForm)) {
                    $values = (new Treatment())->getAllPosts();
                    $values['created_at'] = date('Y/m/d');
                    $this->cRepository->post($values, $postId);
                    $success = 'Commentaire ajouté !';
                } else {
                    $error = $commentForm->getError();
                    $form = $commentForm->createForm((new Treatment())->getAllPosts(), $postNeeded);
                }
            }
            echo $this->renderer->display("post.html.twig", [
                'post' => $postFilled,
                'latestPosts' => $latest,
                'form' => $form,
                'error' => $error,
                'success' => $success
            ]);
        } else {
            $this->show();
        }
    }

}
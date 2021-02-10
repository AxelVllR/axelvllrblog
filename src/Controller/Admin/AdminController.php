<?php
namespace App\Controller\Admin;

use App\Forms\PostForm;
use App\Globals\Session;
use App\Globals\Treatment;
use App\Renderer\Renderer;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Services\ImageService;
use Symfony\Component\DependencyInjection\Compiler\ValidateEnvPlaceholdersPass;

class AdminController {

    /**
     * @var Renderer
     */
    private Renderer $renderer;
    /**
     * @var CommentRepository
     */
    private CommentRepository $cRepo;
    /**
     * @var PostRepository
     */
    private PostRepository $pRepo;
    /**
     * @var UserRepository
     */
    private UserRepository $uRepo;
    /**
     * @var ImageService
     */
    private ImageService $imageService;

    private ?array $session;

    public function __construct() {
        $this->renderer = new Renderer();
        $this->cRepo = new CommentRepository();
        $this->pRepo = new PostRepository();
        $this->uRepo = new UserRepository();
        $this->imageService = new ImageService();
        $this->session = (new Session())->getAll();
    }


    public function show() {
        $comments = $this->cRepo->findAll();
        $commentToValidate = $this->cRepo->commentToValidate();
        $commentToValidate = $this->cRepo->fillEntities($commentToValidate);

        $posts = $this->pRepo->findAllNotDeleted();

        $postsViews = $this->pRepo->findMoreThanViews(30);

        $users = $this->uRepo->findAll();

        echo $this->renderer->display("admin/index.html.twig", [
            'commentsToValidate' => $commentToValidate,
            'comments' => $comments,
            'posts' => $posts,
            'postsViews' => $postsViews,
            'users' => $users
        ]);
    }

    public function postShow() {
        $posts = $this->pRepo->findAllNotDeleted();

        $search = (new Treatment())->getGet("search");
        if(isset($search) && !empty($search)) {
            $posts = $this->pRepo->findByAuthor($search);
        }

        $posts = $this->pRepo->fillEntities($posts);

        $postForm = new PostForm();
        $addPostForm = $postForm->createForm();

        if($postForm->isSubmitted($postForm)) {
            if($postForm->isValid($postForm)) {
                $values = (new Treatment())->getAllPosts();
                // Set Image
                $imageSaved = $this->imageService->saveImage('image', 'PostsImages');
                if($imageSaved == false) {
                    $error = $this->imageService->getError();
                } else {
                    $values['image'] = $imageSaved;
                    $values['created_at'] = date('Y/m/d');
                    $values['updated_at'] = date('Y/m/d');
                    $this->pRepo->post($values);
                    $success = 'Article publié !';
                }
            } else {
                $error = $postForm->getError();
                $addPostForm = $postForm->createForm((new Treatment())->getAllPosts());
            }
        }



        echo $this->renderer->display("admin/posts.html.twig", [
            'posts' => array_reverse($posts),
            'form' => $addPostForm,
            'error' => $error,
            'success' => $success
        ]);
    }

    public function changeUserRole($get) {
        $exp = explode("-", $get);
        $thingToDo = $exp[0];
        $userId = $exp[1];
        $user = $this->uRepo->findOneBy(['id' => $userId]);

        if(!empty($user)) {
            if(preg_match('/role_\\w+/', $thingToDo)) {
                $user->role = strtoupper($thingToDo);
                $this->uRepo->update($user);
            } elseif($thingToDo == 'delete') {
                $this->uRepo->delete($userId);
            }
        }

        header('Location: /admin');

    }

    public function editPost($postId) {
        $post = $this->pRepo->findOneBy(["id" => $postId]);
        if(empty($post)) {
            header('Location: /admin/posts');
            exit;
        }

        $postForm = new PostForm();
        $addPostForm = $postForm->createForm(json_decode(json_encode($post),true), null, '/postEdit/' . $postId);

        if($postForm->isSubmitted($postForm)) {
            if($postForm->isValid($postForm)) {
                $values = (new Treatment())->getAllPosts();

                if($this->imageService->isImageUploaded('image')) {
                    if(!empty($post->image)) {
                        $this->imageService->deleteImage($post->image, 'PostsImages');
                    }
                    $imageSaved = $this->imageService->saveImage('image', 'PostsImages');
                    if($imageSaved == false) {
                        $error = $this->imageService->getError();
                    }
                }

                if(empty($error)) {
                    if(isset($imageSaved) && !empty($imageSaved)) {
                        $values['image'] = $imageSaved;
                    }
                    $values['updated_at'] = date('Y/m/d');
                    $values['id'] = $postId;
                    $postObj = (object) $values;
                    $this->pRepo->update($postObj);
                    $success = "Article Modifié ! (Si vous avez changé l'image de celui-ci, il est normal qu'elle ne s'affiche pas)";
                }
            } else {
                $error = $postForm->getError();
                $addPostForm = $postForm->createForm((new Treatment())->getAllPosts(), null, '/postEdit/' . $postId);
            }
        }

        echo $this->renderer->display("admin/post_edit.html.twig", [
            'form' => $addPostForm,
            'error' => $error,
            'success' => $success
        ]);
    }

    public function deletePost($postId) {
        $this->pRepo->delete($postId);
        header('Location: /admin/posts');
    }

    public function treatComment($value) {
        $exp = explode("-", $value);
        $id = $exp[0];
        $action = $exp[1];
        $comment = $this->cRepo->findOneBy(['id' => $id]);
        unset($comment->created_at);
        if($action == 1) {
            // validate
            $comment->verified = true;
            $this->cRepo->update($comment);
        } elseif($action == 0) {
            $comment->verified = 0;
            $this->cRepo->update($comment);
        }

        header('Location: /admin');
    }

}
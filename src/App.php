<?php
namespace App;

use App\Globals\Session;

class App {

    /**
     * @var array
     */
    private $routes;

    public function __construct() {
        $this->routes = json_decode(file_get_contents('../routing/routes.json'), true);
    }

    public function run($request) {
        $exp = explode("/", $request);
        if(empty($exp[0])) {
            unset($exp[0]);
        }

        if(count($exp) > 1) {
           $this->getMultiRoute($exp, $request);
        } else {
            $this->getRoute($request);
        }
    }

    public function getMultiRoute($exp, $request) {
        $main = "/" . $exp[1];
        $session = (new Session())->getAll();

        if(array_key_exists($request, $this->routes)) {
            $this->getRoute($request);

        } elseif(array_key_exists($main, $this->routes)) {
            if(isset($this->routes[$main]['sub_routes'])) {
                $need = $this->routes[$main]['class'];
                $sub = $this->routes[$main]['sub_routes'];
                if(isset($sub['regex'])) {
                    $regex = '/' . $sub['regex'] . '/';
                    if(!preg_match($regex, $exp[2])) {
                        $this->getRoute($main);
                    } else {
                        if(class_exists($need)) {
                            $class = new $need;
                            $methodToCall = $sub['method'];
                            if(method_exists($class, $methodToCall)) {
                                if(!empty($this->routes[$main]['role'])) {
                                    if(empty($session) || !isset($session['role'])) {
                                        header('Location: /');
                                        exit;
                                    } elseif($this->routes[$main]['role'] !== $session['role']) {
                                        header('Location: /');
                                        exit;
                                    }
                                }
                                $class->$methodToCall($exp[2]);
                            } else {
                                $this->getRoute($main);
                            }
                        }
                    }
                } else {
                    $this->getRoute($main);
                }
            } else {
                $this->getRoute($main);
            }
        }
    }

    public function getRoute($request) {
        $renderer = new Renderer\Renderer();
        $session = (new Session())->getAll();
        if(array_key_exists($request, $this->routes)) {
            if(isset($this->routes[$request]['session'])) {
                if(!$this->routes[$request]['session'] && !empty($session['id'])) {
                    header("Location: /");
                    exit;
                }elseif($this->routes[$request]['session'] == true && empty($session)) {
                    header('Location: /');
                    exit;
                }
            }

            if(isset($this->routes[$request]['role'])) {
                if(!empty($session)) {
                    if(empty($session['role']) || $session['role'] !== $this->routes[$request]['role']) {
                        header('Location: /');
                        exit;
                    }
                } else {
                    header('Location: /');
                    exit;
                }
            }

            $need = $this->routes[$request]['class'];
            if(class_exists($need)) {
                $class = new $need;
                $methodToCall = $this->routes[$request]['method'];
                if(method_exists($class, $methodToCall)) {
                    $class->$methodToCall();
                } else {
                    echo $renderer->display('404.html.twig');
                }

            } else {
                echo $renderer->display('404.html.twig');
            }
        } else {
            echo $renderer->display('404.html.twig');
        }
    }
}
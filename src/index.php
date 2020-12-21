<?php

class index
{

    private static  $router;

    public function __construct()
    {
        spl_autoload_register(function ($class) {
            require_once('./Core/' . $class . '.php');
        });

        self::$router = new Router();

        self::callController();
    }

    public static function callController()
    {
        $controllerFile = './Controllers/' . self::$router->getController() . ".php";

        if (file_exists($controllerFile)) {
            require_once($controllerFile);

            $controllerPath =  self::$router->getController();
            $controller = new $controllerPath(self::$router->getParameters(), self::$router->getHeaderParameters());
            if (method_exists($controller, self::$router->getRequest())) {
                $method = self::$router->getRequest();
                $controller->{$method}();
            } else {
                http_response_code(406);
                die('{
                    "message": "Invalid request."
                }');
            }
        } else {
            http_response_code(500);
            die('{
                "message": "Internal Server Error."
            }');
        }
    }
}

new index();

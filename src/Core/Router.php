<?php

// Allow from any origin
if (isset($_SERVER["HTTP_ORIGIN"])) {
    // You can decide if the origin in $_SERVER['HTTP_ORIGIN'] is something you want to allow, or as we do here, just allow all
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
} else {
    //No HTTP_ORIGIN set, so we allow any. You can disallow if needed here
    header("Access-Control-Allow-Origin: *");
}

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 600");    // cache for 10 minutes

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    if (isset($_SERVER["HTTP_ACCESS_CONTROL_REQUEST_METHOD"]))
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT, PATCH"); //Make sure you remove those you do not want to support

    if (isset($_SERVER["HTTP_ACCESS_CONTROL_REQUEST_HEADERS"]))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    //Just exit with 200 OK with the above headers for OPTIONS method
    exit(0);
}

header('content-type:text/html;charset=utf-8');

// Router
class Router
{
    private $Controller;
    private $Request;
    private $paramerters = [];
    private $headerParamerters = [];

    function __construct()
    {
        $this->setRequest();
        $this->setContoller($_SERVER['QUERY_STRING']);
        $this->setParameters($_SERVER['QUERY_STRING']);
        // $this->setHeaderParameters(getallheaders());
        $this->setHeaderParameters(json_decode(file_get_contents("php://input"), TRUE)); //Use for handle axios requests
    }

    private function setRequest()
    {
        $this->Request = strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function getRequest()
    {
        return $this->Request;
    }

    private function setContoller($request)
    {
        $this->Controller = $this->toStringCapitalize(explode('/', $request)[0]);
    }

    public function getController()
    {
        return $this->Controller;
    }

    private function setParameters($request)
    {
        $paramerters = [];
        $index = -1;
        foreach (explode('/', $request) as $value) {
            if ($index === -1) {
                $index++;
                continue;
            }
            $paramerters[$index] =  $value;
            $index++;
        }

        $this->paramerters = $paramerters;
    }

    private function setHeaderParameters($request)
    {
        // $excludeKeys = ['User-Agent', 'Accept', 'Postman-Token', 'Host', 'Accept-Encoding', 'Connection', 'Content-Length'];
        // $this->headerParamerters = array_diff_key($request, array_flip($excludeKeys));
        $this->headerParamerters = $request; //use for handle axios request
    }

    public function getParameters()
    {
        return $this->paramerters;
    }

    public function getHeaderParameters()
    {
        return $this->headerParamerters;
    }

    // Capitalize String
    protected function toStringCapitalize($string)
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }
}

<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get($uri, $action)
    {
        $this->routes['GET'][$uri] = $action;
    }

    public function post($uri, $action)
    {
        $this->routes['POST'][$uri] = $action;
    }

    public function resolve()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $action = $this->routes[$method][$uri] ?? null;

        if (!$action) {
            http_response_code(404);
            return view('errors/nopage');
        }

        [$controller, $method] = explode('@', $action);

        $controller = "App\\Controllers\\$controller";

        return (new $controller)->$method();
    }
}

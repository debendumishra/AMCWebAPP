<?php
/**
 * Core Front-Controller Router
 */

defined('APP_INIT') or define('APP_INIT', true);

class Router {
    private static array $routes = [];

    /**
     * Register a GET route
     */
    public static function get(string $path, string $handler, array $roles = []): void {
        self::addRoute('GET', $path, $handler, $roles);
    }

    /**
     * Register a POST route
     */
    public static function post(string $path, string $handler, array $roles = []): void {
        self::addRoute('POST', $path, $handler, $roles);
    }

    /**
     * Register any method route
     */
    public static function any(string $path, string $handler, array $roles = []): void {
        self::addRoute('ANY', $path, $handler, $roles);
    }

    private static function addRoute(string $method, string $path, string $handler, array $roles = []): void {
        self::$routes[] = [
            'method'  => $method,
            'path'    => trim($path, '/'),
            'handler' => $handler,
            'roles'   => $roles
        ];
    }

    /**
     * Dispatch the current HTTP request
     */
    public static function dispatch(): void {
        $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $requestUri = str_replace('\\', '/', (string)$requestUri);
        $scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        
        // Normalize URI relative to project base (case-insensitive for Windows)
        $path = $requestUri;
        if (!empty($scriptName) && $scriptName !== '/' && $scriptName !== '.') {
            if (stripos($path, $scriptName) === 0) {
                $path = substr($path, strlen($scriptName));
            }
        }
        $path = trim($path, '/');
        
        // Remove index.php prefix if present
        if (stripos($path, 'index.php') === 0) {
            $path = trim(substr($path, 9), '/');
        }

        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        foreach (self::$routes as $route) {
            if ($route['method'] !== 'ANY' && $route['method'] !== $requestMethod) {
                continue;
            }

            // Convert route pattern with dynamic parameters (e.g., /calls/{id})
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = "#^" . $pattern . "$#";

            if (preg_match($pattern, $path, $matches)) {
                // Role-based Access Control Check
                if (!empty($route['roles'])) {
                    if (!Auth::check()) {
                        Response::redirect('login');
                        return;
                    }
                    if (!Auth::hasAnyRole($route['roles'])) {
                        http_response_code(403);
                        require_once ROOT_PATH . '/views/errors/403.php';
                        return;
                    }
                }

                // Extract named parameters and pass positionally
                $params = array_values(array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));

                [$controllerName, $actionName] = explode('@', $route['handler']);
                $controllerFile = ROOT_PATH . '/controllers/' . $controllerName . '.php';

                if (file_exists($controllerFile)) {
                    require_once $controllerFile;
                    if (class_exists($controllerName)) {
                        $controller = new $controllerName();
                        if (method_exists($controller, $actionName)) {
                            call_user_func_array([$controller, $actionName], $params);
                            return;
                        }
                    }
                }

                http_response_code(500);
                echo "Handler method {$route['handler']} not found.";
                return;
            }
        }

        // 404 Not Found
        http_response_code(404);
        if (file_exists(ROOT_PATH . '/views/errors/404.php')) {
            require_once ROOT_PATH . '/views/errors/404.php';
        } else {
            echo "<h1>404 - Page Not Found</h1><p>The requested page '$path' could not be found.</p>";
        }
    }
}

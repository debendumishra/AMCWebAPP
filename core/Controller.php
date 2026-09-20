<?php
/**
 * Core Base Controller
 */

defined('APP_INIT') or define('APP_INIT', true);

class Controller {
    /**
     * Render a view file inside an appropriate role-based layout
     */
    protected function render(string $viewPath, array $data = [], ?string $layout = null): void {
        extract($data);

        // Calculate flash messages if any
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        // Determine default layout based on user role or custom request
        if ($layout === null) {
            if (!Auth::check()) {
                $layout = 'auth_layout';
            } else {
                $role = Auth::role();
                $layout = match($role) {
                    ROLE_ENGINEER => 'engineer_layout',
                    ROLE_CUSTOMER => 'customer_layout',
                    default       => 'admin_layout'
                };
            }
        }

        $viewFile = ROOT_PATH . '/views/' . trim($viewPath, '/') . '.php';
        if (!file_exists($viewFile)) {
            die("View file not found: {$viewFile}");
        }

        if ($layout === false || $layout === 'none') {
            // Render without layout wrapper
            require $viewFile;
        } else {
            // Capture view output buffer and inject into layout
            ob_start();
            require $viewFile;
            $content = ob_get_clean();

            $layoutFile = ROOT_PATH . '/views/layouts/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                require $layoutFile;
            } else {
                echo $content;
            }
        }
    }

    /**
     * Set flash message in session
     */
    protected function setFlash(string $type, string $message): void {
        Auth::init();
        $_SESSION['flash_' . $type] = $message;
    }

    /**
     * Ensure user is authenticated
     */
    protected function requireAuth(): void {
        if (!Auth::check()) {
            Response::redirect('login');
        }
    }

    /**
     * Ensure user has at least one of required roles
     */
    protected function requireRoles(array $roles): void {
        $this->requireAuth();
        if (!Auth::hasAnyRole($roles)) {
            http_response_code(403);
            $this->render('errors/403', [], 'none');
            exit;
        }
    }
}

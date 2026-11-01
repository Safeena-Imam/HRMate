<?php

namespace App\Controllers;

class ErrorController extends BaseController
{
    public function error403(): void
    {
        http_response_code(403);
        $this->view('errors.403', ['pageTitle' => '403 Forbidden - Access Denied'], 'auth');
    }

    public function error404(): void
    {
        http_response_code(404);
        $this->view('errors.404', ['pageTitle' => '404 - Page Not Found'], 'auth');
    }

    public function error500(): void
    {
        http_response_code(500);
        $this->view('errors.500', ['pageTitle' => '500 - Server Error'], 'auth');
    }
}

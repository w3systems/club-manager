<?php

namespace App\Controllers;

use App\Core\Controller;

class ErrorController extends Controller
{
    public function notFound()
    {
        // This method is often called for 404 errors
        $this->view('errors/404');
    }
}
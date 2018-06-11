<?php

namespace App\Controllers;

use Slim\Views\PhpRenderer as View;

class HomeController extends Controller {
    public function index($request, $response) {
        return $response->withStatus(403)->write('Forbidden.');
    }
}
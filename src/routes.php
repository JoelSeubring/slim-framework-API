<?php

use Slim\Http\Request;
use Slim\Http\Response;


// Root
$app->get('/', 'HomeController:index');


// Authentication
$app->group('/auth', function() {
    $this->post('/signup', 'AuthController:signUp');
    $this->post('/signin', 'AuthController:signIn');
});

// Task
$app->group('/tasks', function() {
    $this->post('', 'TaskController:createTask');
    $this->get('', 'TaskController:getAllTasks');
    $this->get('/{id}', 'TaskController:getTask');
    $this->put('/{id}', 'TaskController:updateTask');
    $this->delete('/{id}', 'TaskController:deleteTask');
});
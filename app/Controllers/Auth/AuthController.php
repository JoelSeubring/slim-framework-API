<?php

namespace App\Controllers\Auth;

use App\Models\User;
use App\Controllers\Controller;
use Respect\Validation\validator as v;


class AuthController extends Controller {

    public function signUp($request, $response, $args) {
        $result = [];

        $validation = $this->validator->validate($request, [
            'name' => v::notEmpty()->alpha(),
            'email' => v::noWhitespace()->notEmpty()->email(),
            'password' => v::noWhitespace()->notEmpty()
        ]);

        if($validation->failed()) {
            $result["error"] = true;
            $result["message"] = "Input cannot be empty!";
            return $response->withJson($result, 422);
        }

        $name = $request->getParam('name'); 
        $email = $request->getParam('email');
        $password = $request->getParam('password');

        if(!$this->auth->isUserExist($email)) {
            $res = $this->auth->createUser($name, $email, $password);
            
            if($res) {
                $result["error"] = false;
                $result["message"] = "Registration successful";
                return $response->withJson($result, 201);
            }else {
                $result["error"] = true;
                $result["message"] = "Oops! An error occurred while registering";
                return $response->withJson($result, 400);
            }
        }else {
            $result["error"] = true;
            $result["message"] = "Sorry, this email already exist";
            return $response->withJson($result, 400);
        }
    }

    public function signIn($request, $response, $args) {
        $email = $request->getParam('email');
        $password = $request->getParam('password');

        $result = [];
        
        if($this->auth->attempt($email, $password)) {
            
            $user = $this->auth->getUserByEmail($email);

            if($user != null) {
                $result["error"] = false;
                $result['name'] = $user['name'];
                $result['email'] = $user['email'];
                $result['apiKey'] = $user['api_key'];
                $result['createdAt'] = $user['created_at']->format('Y-m-d h:iA');
            }else {
                $result['error'] = true;
                $result['message'] = "An error occurred. Please try again";
            }
        }else {
            $result['error'] = true;
            $result['message'] = "Login failed. Incorrect credentials";
        }
        
        return $response->withJson($result);
    }
}
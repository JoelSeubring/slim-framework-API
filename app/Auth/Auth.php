<?php

namespace App\Auth;

use App\Models\User;

class Auth {

    //create new user
    public function createUser($name, $email, $password) {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'api_key' => $this->generateApiKey()
        ]);

        if($user) {
            return true;
        }
        return false;
    } 

    //user login attempt
    public function attempt($email, $password) {
        $user = User::where('email', $email)->first();

        if(!$user) {
            return false;
        }

        if(password_verify($password, $user->password)) {
            return true;
        }

        return false;
    }

    //check whether email already exist
    public function isUserExist($email) {
        $user = User::where('email', $email)->get();
        if($user->count() > 0) {
            return true;
        }
        return false;
    }

    //fetch user_id by api_key
    public function getUserIdByApi($api_key) {
        $user = User::where('api_key', $api_key)->first();
        return $user->id;
    }

    //fetch user by email
    public function getUserByEmail($email) {
        $user = User::where('email', $email)->first();
        if($user) {
            return $user;
        }
        return null;
    }

    //validate user api_key
    public function isValidApiKey($api_key) {
        $user = User::where('api_key', $api_key)->get();
        if($user->count() > 0) {
            return true;
        }
        return false;
    }

    //generate a unique api key
    private function generateApiKey(){
        return md5(uniqid(rand(), true));
    }
}
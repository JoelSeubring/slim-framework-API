<?php

namespace App\Controllers;

use App\Models\Task;

use Respect\Validation\validator as v;

class TaskController extends Controller {

    public function createTask($request, $response, $args) {
        
        $res = $this->authenticate();

        $result = [];

        if($res == 'denied') {
            $result["error"] = true;
            $result["message"] = "Access Denied. Invalid Api key";
            return $response->withJson($result, 400);
        }else if($res == 'missing') {
            $result["error"] = true;
            $result["message"] = "Api key is misssing";
            return $response->withJson($result, 400);
        }else {
            $validation = $this->validator->validate($request, [
                'task' => v::notEmpty()
            ]);

            if($validation->failed()) {
                $result["error"] = true;
                $result["message"] = "Task cannot be empty!";
                return $response->withJson($result, 422);
            }else {
                $task = $request->getParam('task');

                $t = Task::create([
                    'task' => $task, 
                    'user_id' => $res
                    ]);

                if($t) {
                    $result["error"] = false;
                    $result["message"] = "Task created successful";
                    return $response->withJson($result, 201);
                }else {
                    $result["error"] = true;
                    $result["message"] = "Error creating task";
                    return $response->withJson($result, 422);
                }
            }
        }
    }

    public function getTask($request, $response, $args) {
        $res = $this->authenticate();

        $result = [];

        if($res == 'denied') {
            $result["error"] = true;
            $result["message"] = "Access Denied. Invalid Api key";
            return $response->withJson($result, 400);
        }else if($res == 'missing') {
            $result["error"] = true;
            $result["message"] = "Api key is misssing";
            return $response->withJson($result, 400);
        }else {
            $condition = ['id' => $args['id'], 'user_id' => $res];
            $task = Task::where($condition)->first();
        
            if(!$task){
                $result["error"] = true;
                $result["message"] = "Task not found.";
                return $response->withJson($result, 404);
            }else {
                $result["error"] = false;
                $result["id"] = $task->id;
                $result["task"] = $task->task;
                $result["createdAt"] = $task->created_at->format('Y-m-d h:iA');
                return $response->withJson($result, 200);
            }
        }
    }

    public function getAllTasks($request, $response, $args) {
        $res = $this->authenticate();

        $result = [];

        if($res == 'denied') {
            $result["error"] = true;
            $result["message"] = "Access Denied. Invalid Api key";
            return $response->withJson($result, 400);
        }else if($res == 'missing') {
            $result["error"] = true;
            $result["message"] = "Api key is misssing";
            return $response->withJson($result, 400);
        }else {
            $condition = ['user_id' => $res];
            $tasks = Task::where($condition)->get();

            if($tasks->isEmpty()) {
                $result["error"] = true;
                $result["tasks"] = "You haven't create a task!!!";
                return $response->withJson($result, 200);
                
            }else {
                $result["error"] = false;
                $result["tasks"] = [];
                foreach($tasks as $task) {
                    $temp = [];
                    $temp["id"] = $task->id;
                    $temp["task"] = $task->task;
                    $temp["createdAt"] = $task->created_at->format('Y-m-d h:iA');

                    array_push($result["tasks"], $temp);
                }
                return $response->withJson($result, 200);
            }
        }
    }

    public function updateTask($request, $response, $args) {
        $res = $this->authenticate();

        $result = [];

        if($res == 'denied') {
            $result["error"] = true;
            $result["message"] = "Access Denied. Invalid Api key";
            return $response->withJson($result, 400);
        }else if($res == 'missing') {
            $result["error"] = true;
            $result["message"] = "Api key is misssing";
            return $response->withJson($result, 400);
        }else {
            $condition = ['id' => $args['id'], 'user_id' => $res];
            $task = Task::where($condition)->first();

            if(!$task) {
                $result["error"] = true;
                $result["message"] = "Task not found!";
                return $response->withJson($result, 404);
            }else {
                $validation = $this->validator->validate($request, [
                    'task' => v::notEmpty()
                ]);

                if($validation->failed()) {
                    $result["error"] = true;
                    $result["message"] = "Task cannot be empty!";
                    return $response->withJson($result, 422);
                }else {
                    $task->task = $request->getParam('task');
                    $res = $task->save();
                    
                    if($res) {
                        $result["error"] = false;
                        $result["message"] = "Task updated successfully";
                        return $response->withJson($result, 200);
                    }else {
                        $result["error"] = true;
                        $result["message"] = "Task failed to update. Please try again!";
                        return $response->withJson($result, 422);
                    }
                }
            }
        }
    }

    public function deleteTask($request, $response, $args) {
        $res = $this->authenticate();

        $result = [];

        if($res == 'denied') {
            $result["error"] = true;
            $result["message"] = "Access Denied. Invalid Api key";
            return $response->withJson($result, 400);
        }else if($res == 'missing') {
            $result["error"] = true;
            $result["message"] = "Api key is misssing";
            return $response->withJson($result, 400);
        }else {
            $condition = ['id' => $args['id'], 'user_id' => $res];
            $task = Task::where($condition)->first();
            
            if(!$task) {
                $result["error"] = true;
                $result["message"] = "Task not found!";
                return $response->withJson($result, 404);
            }else {
                $output = $task->delete();

                if($output) {
                    $result["error"] = false;
                    $result["message"] = "Task deleted successfully";
                    return $response->withJson($result, 200);
                }else {
                    $result["error"] = true;
                    $result["message"] = "Task failed to delete. Please try again!";
                    return $response->withJson($result, 422);
                }
            }
        }
    }

    //Checking if the request has valid api key in the 'Authorization' header
    public function authenticate() {
        $headers = apache_request_headers();
        if(isset($headers['Authorization']) && $headers['Authorization'] != null) {
            $api_key = $headers['Authorization'];

            if($this->auth->isValidApiKey($api_key)) {
                $user_id = $this->auth->getUserIdByApi($api_key);
                return $user_id;
            }else {
                return 'denied';
            }
        }else {
            return 'missing';
        }
    }
}
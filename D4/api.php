<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'user.php';
include_once 'database.php';

function showUserError($errorMessage, $errorCode){
	http_response_code($errorCode);
	$errorObject = array();
 	$errorObject["error"] = $errorMessage;
 	echo json_encode($errorObject);
}

const ROLES = array(
    "User",
    "Admin"
);

function validRole($role){
	return in_array($role, ROLES);
}

function cyrillic($string){
	return preg_match('/^[а-яА-Я]+$/u', $string);
}

function validEmail($email){
	return filter_var($email, FILTER_VALIDATE_EMAIL);
}

$database = new Database();
try{
	$db = $database->getConnection();
}catch(dbConnException $e){
	showUserError("Couldn't connect to database", 500);
	return;
}

$user = new User($db);

$request_uri = explode('/', $_SERVER['REQUEST_URI'], 4);

if ($_SERVER["REQUEST_METHOD"] == "GET" && $request_uri[2] == "users") {
	$stmt = $user->read();
	$num = $stmt->rowCount();

	if($num>0){
	 
	    $users=array();
	 
	    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	        extract($row);
	 
	        $user=array(
	            "email" => $email,
	            "fname" => $fname,
	            "lname" => $lname,
	            "role" => $role
	        );
	 
	        array_push($users, $user);
	    }

	    http_response_code(200);
	    echo json_encode($users);
	 } else {
	 	showUserError("No users found", 404);
	 }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && $request_uri[2] == "register") {
	$data = json_decode(file_get_contents("php://input"));

	if(!empty($data->email) &&
	   !empty($data->fname) &&
	   !empty($data->lname) &&
	   !empty($data->password) &&
	   !empty($data->role)){
 
	    $user->email = $data->email;
	    $user->fname = $data->fname;
	    $user->lname = $data->lname;
	    $user->password = password_hash($data->password, PASSWORD_DEFAULT);
	    $user->role = $data->role;

	    if(!validRole($user->role) || !cyrillic($user->fname) || !cyrillic($user->lname) || !validEmail($user->email)){
	    	showUserError("Invalid user data", 400);
	    	return;
	    }
	 
	    if($user->create()){
	        http_response_code(201);
	        unset($user->password);
	        echo json_encode($user);
	    } else {
	        showUserError("Unable to create user", 503);
	    }
	}
} elseif ($_SERVER["REQUEST_METHOD"] == "DELETE" && ($request_uri[2] == "users" || $request_uri[2] == "user" ) && !empty($request_uri[3]) ){

# users is much more inline with the principles of REST; https://restfulapi.net/resource-naming/

	$user->email = $request_uri[3];

	if($user->delete()){
		unset($user->password);
    	echo json_encode($user);
	}
	else{
		showUserError("Unable to delete user", 503);	
	}	
} else {
	showUserError("Nothing to see here", 404);
}

?>
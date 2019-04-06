<?php

	session_start();

	if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 300)) {
	    session_unset();     
	    session_destroy();  
	}
	$_SESSION['LAST_ACTIVITY'] = time(); 

	
	function clean_input($data) {
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	}

	function validate_name($name) {
		$err_message = "";
		if(empty($name))
			$err_message = "The entered name cannot be empty!";
		elseif(strlen($name) > 200)
			$err_message = "The entered name can be no longer than 200 characters!";
		elseif(!preg_match('/^[a-zA-Zа-яА-Я ]+$/u', $name))
			$err_message = "The entered name can contain only latin and cyrillic letters!";
		return $err_message;
	}

	function validate_number($number, $elem_name, $min, $max, $number_checker_function){
		$err_message = "";
		if(empty($number))
			$err_message = "The ".$elem_name." cannot be empty!";
		elseif(!call_user_func($number_checker_function, $number))
			$err_message = "The ".$elem_name." must be a valid number!";
		elseif($number < $min || $number > $max)
			$err_message = "The ".$elem_name." must be in the range ".$min." - ".$max."!";
		return $err_message;
	}

	$students = array();
	if(!empty($_SESSION['students']))
		$students = $_SESSION['students'];
	else
		$students = [
			[ 'name' => 'Мария Георгиева', 'fn' => 62543, 'mark' => 5.25 ],
			[ 'name' => 'Иван Иванов', 'fn' => 62555, 'mark' => 6.00 ],
			[ 'name' => 'Петър Петров', 'fn' => 62549, 'mark' => 5.00],
			[ 'name' => 'Петя Димитрова', 'fn' => 62559, 'mark' => 6.00]
		];

	$student = array("name" => "", "fn" => "", "mark" => "");
	$err_messages = array();

	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		
		$student["name"] = clean_input($_POST["name"]);
		$err_message = validate_name($student["name"]);
		if($err_message != "")
			$err_messages["name_error"] = $err_message;

		$student["fn"] = clean_input($_POST["fn"]);
		$err_message = validate_number($student["fn"], "faculty number", 62000, 62999, "ctype_digit");
		if($err_message != "")
			$err_messages["fn_error"] = $err_message;
		
		$student["mark"] = clean_input($_POST["mark"]);
		$err_message = validate_number($student["mark"], "student's mark", 2, 6, "is_numeric");
		if($err_message != "")
			$err_messages["mark_error"] = $err_message;
	}

	function compare_students($a, $b)
	{
		$retval = strnatcmp($b['mark'], $a['mark']);
		if(!$retval) $retval = strnatcmp($a['fn'], $b['fn']);
		return $retval;
	}

	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		if (!empty($err_messages)){
			$errors_json = json_encode($err_messages);
			echo $errors_json;

			$err_messages = array();
		} else {
			array_push($students, $student);

			usort($students, __NAMESPACE__ . '\compare_students');

			$_SESSION['students'] = $students;

			$students_json = json_encode($students);
			echo $students_json;
		}
		exit;
	}
	?>
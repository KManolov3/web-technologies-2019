<?php
	session_start();

	if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 300)) {
	    session_unset();     
	    session_destroy();  
	}
	$_SESSION['LAST_ACTIVITY'] = time(); 
?>

<!DOCTYPE html>
<html>
<head>
	<title>Student Grading Form</title>
</head>
<body>
	<?php
	function clean_input($data) {
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	}

	function validate_name($name) {
		$err_message = "";
		if(strlen($name) > 200)
			$err_message = "The entered name can be no longer than 200 characters!";
		elseif(!preg_match('/^[a-zA-Zа-яА-Я ]+$/u', $name))
			$err_message = "The entered name can contain only latin and cyrillic letters!";
		return $err_message;
	}

	function validate_faculty_number($fac_number) {
		$err_message = "";
		if(!ctype_digit($fac_number))
			$err_message = "The submitted faculty number must be a number!";
		elseif($fac_number < 62000 || $fac_number > 62999)
			$err_message = "The submitted faculty number must be in the range 62000 - 62999!";
		return $err_message;
	}

	function validate_student_mark($mark) {
		$err_message = "";
		if(!is_numeric($mark))
			$err_message = "The student's mark must be a number!";
		elseif($mark < 2.00 || $mark > 6.00)
			$err_message = "The student's mark must be in the range 2.00 - 6.00!";
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
		if(empty($_POST["name"]))
			$err_messages["name_err"] = "Student name cannot be empty!";
		else{
			$student["name"] = clean_input($_POST["name"]);
			if(($err_message = validate_name($student["name"])) != "")
				$err_messages["name_err"] = $err_message;
		}
		if(empty($_POST["fn"]))
			$err_messages["fn_err"] = "The student's faculty number cannot be empty!";
		else{
			$student["fn"] = clean_input($_POST["fn"]);
			if(($err_message = validate_faculty_number($student["fn"])) != "")
				$err_messages["fn_err"] = $err_message;
		}
		if(empty($_POST["mark"]))
			$err_messages["mark_err"] = "The student's mark cannot be empty!";
		else{
			$student["mark"] = clean_input($_POST["mark"]);
			if(($err_message = validate_student_mark($student["mark"])) != "")
				$err_messages["mark_err"] = $err_message;
		}
	}


	?>

	<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
		<label> Student Name: <input name="name"> </label><br>
		<label> Faculty Number: <input name="fn"> </label><br>
		<label> Mark: <input name="mark"> </label><br>
		<button> Submit </button>
	</form>

	<?php
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
	}
	?>
	</body>
</html>

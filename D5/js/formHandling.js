function validate(form, event){
	var validFormValues = true;

	var unameRegex = /^[\w]{3,10}$/;
	if(!unameRegex.test(form.uname.value)) {
		form.uname.focus();
		document.getElementById("error-uname").innerHTML = "Username must be between 3 and 10 characters long, and can contain only alphanumeric characters or _!";
		validFormValues = false;
	} else {
		document.getElementById("error-uname").innerHTML = "";
	}

	var passwordRegex = /^(?=.*[a-z])(?=.*[0-9])(?=.*[A-Z]).{6,}/;
	if(!passwordRegex.test(form.password.value)) {
		form.password.focus();
		document.getElementById("error-password").innerHTML = "Password must be at least 6 characters long and must contain at least 1 lowercase symbol, 1 uppercase symbol and 1 digit!";
		validFormValues = false;
	} else {
		document.getElementById("error-password").innerHTML = "";
	}

	if(form.password.value!=form.repeatPassword.value){
		form.repeatPassword.focus();
		document.getElementById("error-repeatPassword").innerHTML = "Passwords must match!";
		validFormValues = false;
	} else {
		document.getElementById("error-repeatPassword").innerHTML = "";
	}

	if(validFormValues){
		submit(form, "./register.php");
	}
	return false;
}

function submit(form, url){
	function postAsync(url, content, settings){
	    var req = new XMLHttpRequest();
	    req.onload = function(){
			settings[req.status == 200 ? 'success' : 'error'](req.responseText);
		};
	    req.open("POST", url, true);
	    req.send(content || null); 
	}

	var log = console.log.bind(console),
		err = console.error.bind(console),
		content = "uname=" + form.uname.value  + "&password=" + form.password.value + "&repeatPassword=" + form.repeatPassword.value;

	postAsync(url, content, {success: log, error: err});
}
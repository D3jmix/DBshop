let signUpButton = document.getElementById("signUpButton");
let signInButton = document.getElementById("signInButton");
let nameField = document.getElementById("nameField");
let emailField = document.getElementById("emailField");
let emailOrUserField = document.getElementById("emailOrUserField");
let title = document.getElementById("title");
let formAction = document.getElementById("formAction");

signInButton.onclick = () => {
	nameField.style.display = "none";
	emailField.style.display = "none";
	emailOrUserField.style.display = "block";
	title.innerHTML = "Sign in";
	formAction.value = "login";
	signUpButton.classList.add("disable");
	signInButton.classList.remove("disable");
};

signUpButton.onclick = () => {
	nameField.style.display = "block";
	emailField.style.display = "block";
	emailOrUserField.style.display = "none";
	title.innerHTML = "Sign up";
	formAction.value = "register";
	signUpButton.classList.remove("disable");
	signInButton.classList.add("disable");
};

let signUpButton = document.getElementById("signUpButton");
let signInButton = document.getElementById("signInButton");
let nameField = document.getElementById("nameField");
let emailField = document.getElementById("emailField");
let emailOrUserField = document.getElementById("emailOrUserField");
let title = document.getElementById("title");
let formAction = document.getElementById("formAction");
let zapomnialemButton = document.getElementById("zapomnialem");
let confirmPassword = document.getElementById("passwAgain");
let passwordField = document.getElementById("password");

function showSignIn() {
  nameField.style.display = "none";
  emailField.style.display = "none";
  emailOrUserField.style.display = "flex";
  title.innerHTML = "Zaloguj się";
  formAction.value = "login";
  signUpButton.classList.add("disable");
  signInButton.classList.remove("disable");
  zapomnialemButton.style.display = "block";
  confirmPassword.style.display = "none";
  passwordField.value = "";
};

function showSignUp() {
  nameField.style.display = "flex";
  emailField.style.display = "flex";
  emailOrUserField.style.display = "none";
  title.innerHTML = "Zarejestruj się";
  formAction.value = "register";
  signUpButton.classList.remove("disable");
  signInButton.classList.add("disable");
  zapomnialemButton.style.display = "none";
  confirmPassword.style.display = "flex";
  passwordField.value = "";
};

signInButton.onclick = showSignIn;
signUpButton.onclick = showSignUp;

document.addEventListener("DOMContentLoaded", showSignIn);





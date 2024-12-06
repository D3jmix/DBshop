<?php 
require_once 'db.php';
session_start();

if(isset($_POST["submit"])) {
    $action = $_POST['action'];
    $emailOrUser = $_POST['emailOrUser'];
    $password = $_POST['password'];

    if ($action === "register"){
        $username = $_POST['username'];
        $email = $_POST['email'];

        if (empty($username) || empty($email) || empty($password)) {
            echo "<p style='color:red;'>Wszystkie pola do rejestracji są wymagane!!</p>";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)){
            echo "<p style='color:red;'>Nie prawidłowy format E-mail!!</p>";
        } else {
            $checkEmail = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email=:email");
            $checkEmail->bindValue(':email', $email, PDO::PARAM_STR);
            $checkEmail->execute();
            $emailExists = $checkEmail->fetchColumn();

            $checkUser = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username=:username");
            $checkUser->bindValue(':username', $username, PDO::PARAM_STR);
            $checkUser->execute();
            $userExists = $checkUser->fetchColumn();

            if ($emailExists > 0) {
                echo "<p style='color:red;'>Ten email jest już przypisany do innego konta. Wybierz inny adres email.</p>";
            } elseif ($userExists > 0) {
                echo "<p style='color:red;'>Ta nazwa użytkownika jest już zajęta. Wybierz inną nazwę użytkownika.</p>";
            } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*?\/])[A-Za-z\d!@#$%^&*?\/]{8,}$/', $password)) {
                echo "<p style='color:red;'>Hasło musi być dłuższe niż 8 znaków. Musi zawierać conajmniej jedną dużą i jedną małą literę, jedną cyfrę i jeden znak specjalny.</p>";
            } else {
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $input = $pdo->prepare("INSERT INTO users (username, email, password) VALUES(:username, :email, :password)");
                $input->bindValue(':username', $username, PDO::PARAM_STR);
                $input->bindValue(':email', $email, PDO::PARAM_STR);
                $input->bindValue(':password', $hashed, PDO::PARAM_STR);
                $exec = $input->execute();
                if ($exec) {
                    echo "<p style='color:green;'>Pomyślnie zarejestrowano!!</p>";
                } else {
                    echo "<p style='color:red;'>Wystąpił błąd. Spróbuj ponownie.</p>";
                }
            }
        }
    } elseif ($action === "login") {
        if (empty($emailOrUser) || empty($password)) {
            echo "<p style='color:red;'>Wszystkie pola muszą być wypełnione!!</p>";
        } else {
            if (strpos($emailOrUser, '@') !== false) {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :emailOrUser");
            } else {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :emailOrUser");
            }

            $stmt->bindValue(':emailOrUser', $emailOrUser, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                echo "<p style='color:green;'>Udane logowanie. Witaj, " . htmlspecialchars($user['username']) . "!</p>";
            } else {
                echo "<p style='color:red;'>Błędny email/username lub hasło.</p>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>kep & zoś</title>
    <link rel="stylesheet" href="styles/login.css"/>
    <script src="https://kit.fontawesome.com/0811bb0147.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="background">
        <div class="form-box">
            <h1 id="title">Sign up</h1>
            <div class="buttons">
                <button type="button" id="signUpButton">Sign up</button>
                <button type="button" class="disable" id="signInButton">Sign in</button>
            </div>
            <form method="post">
                <input type="hidden" name="action" id="formAction" value="register"/>
                <div class="input-group">
                    <div class="input-field" id="nameField">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" name="username" placeholder="Username"/>
                    </div>
                    <div class="input-field" id="emailField">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" name="email" placeholder="E-mail"/>
                    </div>
                    <div class="input-field" id="emailOrUserField" style="display: none;">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" name="emailOrUser" placeholder="Email or Username"/>
                    </div>
                    <div class="input-field">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" placeholder="Password"/>
                    </div>
                    <button name="submit" class="submit-button">Submit</button>
                </div>
            </form>
        </div>
    </div>
    <script src="scripts/login1.js"></script>
</body>
</html>

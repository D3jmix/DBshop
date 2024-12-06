<?php 
require_once 'db.php';
session_start();

$error_mes = "";
$success_mes = "";

if(isset($_POST["submit"])) {
    $action = $_POST['action'];
    $emailOrUser = $_POST['emailOrUser'];
    $password = $_POST['password'];
    $confirm_password = $_POST["confirm_password"];

    if ($action === "register"){
        $username = $_POST['username'];
        $email = $_POST['email'];

        if (empty($username) || empty($email) || empty($password)) {
           $error_mes = "Wszystkie pola do rejestracji są wymagane!!";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)){
           $error_mes = "Nie prawidłowy format E-mail!!";
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
               $error_mes = "Ten email jest już przypisany do innego konta. Wybierz inny adres email.";
            } elseif ($userExists > 0) {
               $error_mes = "Ta nazwa użytkownika jest już zajęta. Wybierz inną nazwę użytkownika.";
            } elseif (empty($password) || empty($confirm_password)) {
               $error_mes = "Oba pola hasła są wymagane.";
            }elseif ($password !== $confirm_password){
               $error_mes = "Hasła muszą być identyczne.";
            }elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
               $error_mes = "Hasło musi mieć co najmniej 8 znaków, zawierać małą i dużą literę, cyfrę oraz znak specjalny.";
            } else {
               $hashed = password_hash($password, PASSWORD_BCRYPT);
               $input = $pdo->prepare("INSERT INTO users (username, email, password) VALUES(:username, :email, :password)");
               $input->bindValue(':username', $username, PDO::PARAM_STR);
               $input->bindValue(':email', $email, PDO::PARAM_STR);
               $input->bindValue(':password', $hashed, PDO::PARAM_STR);
               $exec = $input->execute();
               if ($exec) {
                   $success_mes = "Pomyślnie zarejestrowano!!";
               } else {
                   $error_mes = "Wystąpił błąd. Spróbuj ponownie.";
               }
            }
          }
    } elseif ($action === "login") {
        if (empty($emailOrUser) || empty($password)) {
           $error_mes = "Wszystkie pola muszą być wypełnione!!";
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
                $success_mes = "Udane logowanie. Witaj, " . htmlspecialchars($user['username']) . "!";
            } else {
               $error_mes = "Błędny email/nick lub hasło.";
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
            <?php if (!empty($error_mes)): ?>
                <div class="error-box">
                    <p><?php echo htmlspecialchars($error_mes); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success_mes_)): ?>
                <div class="success-box">
                    <p><?php echo htmlspecialchars($success_mes); ?></p>
                </div>
            <?php endif; ?>

            <h1 id="title">Rejestracja</h1>
            <div class="buttons">
                <button type="button" class="disable" id="signInButton">Logowanie</button>
                <button type="button" id="signUpButton">Rejestracja</button>
            </div>
                <form method="post">
                    <input type="hidden" name="action" id="formAction" value="login"/>
                    <div class="input-group">
                        <div class="input-field" id="nameField">
                            <i class="fa-solid fa-user"></i>
                            <input type="text" name="username" placeholder="Nick"/>
                        </div>
                        <div class="input-field" id="emailField">
                            <i class="fa-solid fa-envelope"></i>
                            <input type="email" name="email" placeholder="E-mail"/>
                        </div>
                        <div class="input-field" id="emailOrUserField" style="display: none;">
                            <i class="fa-solid fa-user"></i>
                            <input type="text" name="emailOrUser" placeholder="Email lub nick"/>
                        </div>
                        <div class="input-field">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" name="password" id="password" placeholder="Hasło"/>
                        </div>
                      <div class="input-field" id="passwAgain">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="confirm_password" placeholder="Potwierdź hasło">
                      </div>
                        <button name="submit" class="submit-button">Zatwierdź</button>
                    </div>
                </form>            
            <div id="LostPasw">
                <form action="LostPasw.php" method="get">
                    <button type="submit" id="zapomnialem" class="submit-button" style="display: none;"> Przypomnij hasło</button>
                </form>
            </div>
        </div>
    </div>
    <script src="scripts/login.js"></script>
</body>
</html>



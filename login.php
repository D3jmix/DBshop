<?php
require_once 'db.php';
session_start();

$error_mes = "";
$success_mes = "";
$zalogowany = isset($_SESSION['user_id']);
$czyAdmin = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['wyloguj'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}  
  
if (isset($_POST["submit"])) {
    $action = $_POST['action'];
    $emailOrUser = $_POST['emailOrUser'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST["confirm_password"] ?? '';

    if ($action === "register") {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $phone = $_POST['phone'] ?? '';

        if (empty($username) || empty($email) || empty($password) || empty($first_name) || empty($last_name) || empty($phone)) {
            $error_mes = "Wszystkie pola do rejestracji są wymagane!";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_mes = "Nieprawidłowy format e-mail!";
        } elseif (!preg_match('/^\d{9}$|^\+\d{11}$/', $phone)) {
            $error_mes = "Numer telefonu musi składać się z dokładnie 9 cyfr lub 12 znaków (zaczynając od '+').";
        } else {
            $checkEmail = $pdo->prepare("SELECT COUNT(*) FROM konta WHERE email = :email");
            $checkEmail->bindValue(':email', $email, PDO::PARAM_STR);
            $checkEmail->execute();
            $emailExists = $checkEmail->fetchColumn();

            $checkUser = $pdo->prepare("SELECT COUNT(*) FROM konta WHERE username = :username");
            $checkUser->bindValue(':username', $username, PDO::PARAM_STR);
            $checkUser->execute();
            $userExists = $checkUser->fetchColumn();

            if ($emailExists > 0) {
                $error_mes = "Ten email jest już przypisany do innego konta.";
            } elseif ($userExists > 0) {
                $error_mes = "Ta nazwa użytkownika jest już zajęta.";
            } elseif ($password !== $confirm_password) {
                $error_mes = "Hasła muszą być identyczne.";
            } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
                $error_mes = "Hasło musi mieć co najmniej 8 znaków, zawierać małą i dużą literę, cyfrę oraz znak specjalny.";
            } else {
                try {
                    $pdo->beginTransaction();

                    $insertClient = $pdo->prepare("INSERT INTO klienci (imie, nazwisko, nr_tel) VALUES (:imie, :nazwisko, :nr_tel)");
                    $insertClient->bindValue(':imie', $first_name, PDO::PARAM_STR);
                    $insertClient->bindValue(':nazwisko', $last_name, PDO::PARAM_STR);
                    $insertClient->bindValue(':nr_tel', $phone, PDO::PARAM_STR);
                    $insertClient->execute();

                    $client_id = $pdo->lastInsertId();

                    $hashed = password_hash($password, PASSWORD_BCRYPT);
                    $insertAccount = $pdo->prepare("INSERT INTO konta (username, email, password, id_klienta) VALUES (:username, :email, :password, :id_klienta)");
                    $insertAccount->bindValue(':username', $username, PDO::PARAM_STR);
                    $insertAccount->bindValue(':email', $email, PDO::PARAM_STR);
                    $insertAccount->bindValue(':password', $hashed, PDO::PARAM_STR);
                    $insertAccount->bindValue(':id_klienta', $client_id, PDO::PARAM_INT);
                    $insertAccount->execute();

                    $pdo->commit();
                    $success_mes = "Pomyślnie zarejestrowano!";
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error_mes = "Wystąpił błąd: " . $e->getMessage();
                }
            }
        }
    } elseif ($action === "login") {
        if (empty($emailOrUser) || empty($password)) {
            $error_mes = "Wszystkie pola muszą być wypełnione!";
        } else {
            if (strpos($emailOrUser, '@') !== false) {
                $stmt = $pdo->prepare("SELECT * FROM konta WHERE email = :emailOrUser");
            } else {
                $stmt = $pdo->prepare("SELECT * FROM konta WHERE username = :emailOrUser");
            }

            $stmt->bindValue(':emailOrUser', $emailOrUser, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $success_mes = "Udane logowanie. Witaj, " . htmlspecialchars($user['username']) . "!";
                header('Location: index.php');
                exit();
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
    <title>Login - SklepOnline</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/login.css">
    <script src="https://kit.fontawesome.com/0811bb0147.js" crossorigin="anonymous"></script>
    <style>
      .wyloguj-btn {
        background-color: orange;
        color: white;
        border: none;
      }

      .wyloguj-btn:hover {
        background-color: red;
        color: white;
      }

      .wyloguj-btn:focus, .wyloguj-btn:active {
        background-color: red;
        color: white;
      }
      </style>
</head>
<body>
    <header class="bg-dark text-white py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="index.php" class="text-white text-decoration-none fs-4"><img src="/photos/logo.png" alt="DB shop" width="70px" height="70px"></a>
            <nav class="mx-auto">
                <ul class="nav justify-content-center">
                    <li class="nav-item"><a href="kategoria_wybor.php" class="nav-link text-white">Kategorie</a></li>
                    <li class="nav-item"><a href="produkty_str.php" class="nav-link text-white">Produkty</a></li>
                    <li class="nav-item"><a href="kontakt.php" class="nav-link text-white">Kontakt</a></li>
                </ul>
            </nav>
            <div class="d-flex align-items-center">
              <a href="koszyk.php" class="text-decoration-none me-4">
                <i class="bi bi-cart fs-4"></i>
              </a>
              <a href="ulubione.php" class="text-decoration-none me-4">
                <i class="bi bi-bag-heart fs-4"></i>
              </a>
                <div class="dropdown">
                  <button class="btn btn-secondary dropdown-toggle" type="button" id="menuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    Menu
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuDropdown">
                    <?php if ($zalogowany): ?>
                    <li><a class="dropdown-item" href="panel_uzytkownika.php">Panel użytkownika</a></li>
                    <?php if ($czyAdmin): ?>
                    <li><a class="dropdown-item" href="panel_admina.php">Panel admina</a></li>
                    <?php endif; ?>
                    <li><a class="dropdown-item" href="twoje_zamowienia.php">Twoje zamówienia</a></li>
                    <?php else: ?>
                    <li><a class="dropdown-item" href="login.php">Zaloguj się</a></li>
                    <?php endif; ?>
                    <li><a class="dropdown-item" href="kategorie_strona.php">Kategorie</a></li>
                    <li><a class="dropdown-item" href="kontakt.php">Kontakt</a></li>
                    <li><a class="dropdown-item" href="polityka_prywatnosci.php">Polityka prywatności</a></li>
                    <li><a class="dropdown-item" href="regulamin.php">Regulamin</a></li>
                    <li><a class="dropdown-item" href="faq.php">FAQ</a></li>
                    <?php if ($zalogowany): ?>
                    <li>
                        <form method="POST" class="d-inline">
                            <button type="submit" name="wyloguj" class="dropdown-item wyloguj-btn">Wyloguj</button>
                        </form>
                    </li>
                    <?php endif; ?>
                  </ul>
                </div>
          </div>
      </div>
  </header>
  
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
                        <div class="input-field" id="firstNameField">
                            <i class="fa-solid fa-user"></i>
                            <input type="text" name="first_name" placeholder="Imię" pattern="[A-Za-zĄąĆćĘęŁłŃńÓóŚśŹźŻż]+">
                        </div>
                        <div class="input-field" id="lastNameField">
                            <i class="fa-solid fa-user"></i>
                            <input type="text" name="last_name" placeholder="Nazwisko" pattern="[A-Za-zĄąĆćĘęŁłŃńÓóŚśŹźŻż]+">
                        </div>
                        <div class="input-field" id="emailField">
                            <i class="fa-solid fa-envelope"></i>
                            <input type="email" name="email" placeholder="E-mail"/>
                        </div>
                        <div class="input-field" id="emailOrUserField" style="display: none;">
                            <i class="fa-solid fa-user"></i>
                            <input type="text" name="emailOrUser" placeholder="Email lub nick"/>
                        </div>
                        <div class="input-field" id="phoneField">
                            <i class="fa-solid fa-phone"></i>
                            <input type="text" name="phone" placeholder="Numer telefonu musi składać się z dokładnie 9 cyfr lub 12 znaków (zaczynając od '+')">
                        </div>
                        <div class="input-field">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" name="password" id="password" placeholder="Hasło"/>
                        </div>
                      <div class="input-field" id="passwAgain">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="confirm_password" placeholder="Potwierdź hasło">
                      </div>
                        <button name="submit" class="submit-button" id="zatwierdz">Zatwierdź</button>
                    </div>
                </form>            
            <div id="LostPasw">
                <form action="LostPasw.php" method="get">
                    <button type="submit" id="zapomnialem" class="submit-button" style="display: none;"> Przypomnij hasło</button>
                </form>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-4">
    <div class="container text-center">
        <p>&copy; 2024 DBShop. Wszystkie prawa zastrzeżone.</p>
        <div class="row">
            <div class="col-12">
                <a href="polityka_prywatnosci.php" class="text-white text-decoration-none me-3">Polityka prywatności</a>
                <a href="regulamin.php" class="text-white text-decoration-none">Regulamin</a>
            </div>
            <div class="col-12 mt-2">
                <a href="faq.php" class="text-white text-decoration-none">FAQ</a>
            </div>
        </div>
    </div>
</footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="scripts/login.js"></script>
</body>
</html>



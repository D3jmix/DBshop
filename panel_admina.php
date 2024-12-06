<?php
include('db.php');
session_start();

if (!isset($_SESSION['user_id'])) {
   header('Location: login.php');
   exit();
}

$stmt = $pdo->prepare("SELECT admin FROM konta WHERE id = :id");
$stmt->bindValue(':id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['admin'] != 1) {
    echo "<div class='alert alert-danger text-center'>Brak uprawnień do przeglądania tej strony.</div>";
    exit();
}
  
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['wyloguj'])) {
    session_destroy();
    header("Location: index.php");
    exit;
  }
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administracyjny</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Panel Administracyjny</a>
             <div class="d-flex">
               <a href="index.php" class="btn btn-light me-2">Wróć</a>
             </div>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="?page=produkty">Produkty</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=kategorie">Kategorie</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=klienci">Klienci</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=konta">Konta</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=zamowienia">Zamówienia</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=dostawa">Dostawa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=platnosc">Płatności</a>
                    </li>
                    <li class="nav-item">
                      <form method="POST">
                        <button type="submit" name="wyloguj" class="btn">Wyloguj</button>
                      </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php if (isset($_GET['page'])): ?>
            <div class="card shadow p-4 mb-4">
                <?php
                    switch ($_GET['page']) {
                        case 'produkty':
                            include 'panel/produkty.php';
                            break;
                        case 'kategorie':
                            include 'panel/kategorie.php';
                            break;
                        case 'klienci':
                            include 'panel/klienci.php';
                            break;
                        case 'konta':
                            include 'panel/konta.php';
                            break;
                        case 'zamowienia':
                            include 'panel/zamowienia.php';
                            break;
                        case 'dostawa':
                            include 'panel/dostawa.php';
                            break;
                        case 'platnosc':
                            include 'panel/platnosc.php';
                            break;
                        default:
                            echo "<p class='text-center text-muted'>Wybierz jedną z opcji z menu.</p>";
                    }
                ?>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">Wybierz jedną z opcji z menu.</p>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>













































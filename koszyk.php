<?php
session_start();
require_once 'db.php';

$zalogowany = isset($_SESSION['user_id']);
$czyAdmin = false;
$produkty_w_koszyku = array();
$koszt_calosci = 0;
$blad = '';

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT username FROM konta WHERE id=:id");
    $stmt->bindValue(':id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
  
    if ($user) {
        $stmt = $pdo->prepare("SELECT p.id, p.nazwa, p.cena, p.zdjecie, k.id_user, k.ilosc FROM koszyk k JOIN produkty p ON k.id_produkt = p.id WHERE k.id_user = :user_id");
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        $produkty_w_koszyku = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($produkty_w_koszyku as $produkt) {
            $koszt_calosci += $produkt['cena'] * $produkt['ilosc'];
        }
    }
}
  
if ($zalogowany) {
    $userId = $_SESSION['user_id'];
    $czyAdmin = false;
     try {
        $query = $pdo->prepare("SELECT admin FROM konta WHERE id = :id");
        $query->bindParam(':id', $userId, PDO::PARAM_INT);
        $query->execute();
        $admin = $query->fetchColumn();

        $czyAdmin = ($admin == 1);
    } catch (PDOException $e) {
        die("Błąd zapytania do bazy danych: " . $e->getMessage());
    }
}
  
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['wyloguj'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
  
if (isset($_POST['aktualizuj_ilosc'])) {
    $produkt_id = $_POST['produkt_id'];
    $nowa_ilosc = $_POST['ilosc'];

    $stmt = $pdo->prepare("SELECT stan FROM produkty WHERE id = :id");
    $stmt->bindValue(':id', $produkt_id, PDO::PARAM_INT);
    $stmt->execute();
    $produkt = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($produkt) {
        $stan_magazynowy = $produkt['stan'];

        if ($nowa_ilosc > $stan_magazynowy) {
            $blad = "Nie możesz dodać więcej produktów niż dostępny stan magazynowy (pozostało tylko {$stan_magazynowy}).";
        } else {
            $stmt = $pdo->prepare("UPDATE koszyk SET ilosc = :ilosc WHERE id_produkt = :id_produkt AND id_user = :user_id");
            $stmt->bindValue(':ilosc', $nowa_ilosc, PDO::PARAM_INT);
            $stmt->bindValue(':id_produkt', $produkt_id, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->execute();
            header("Location: koszyk.php");
            exit;
        }
    } else {
        $blad = "Nie znaleziono produktu o podanym ID.";
    }
}  

if (isset($_GET['usun'])) {
    $produkt_id = $_GET['usun'];
    if (is_numeric($produkt_id)) {
        $stmt = $pdo->prepare("DELETE FROM koszyk WHERE id_produkt = :id_produkt AND id_user = :user_id");
        $stmt->bindValue(':id_produkt', $produkt_id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();

        header("Location: koszyk.php");
        exit;
    } else {
        echo "Nieprawidłowy ID produktu.";
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koszyk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
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

    <section class="py-5">
        <div class="container">
            <h1 class="fw-bold text-center mb-4">Twój Koszyk</h1>
          
            <?php if ($blad): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($blad); ?></div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Produkt</th>
                            <th>Nazwa</th>
                            <th>Ilość</th>
                            <th>Cena</th>
                            <th>Razem</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($produkty_w_koszyku)): ?>
                            <tr>
                                <td colspan="6" class="text-center">Twój koszyk jest pusty.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($produkty_w_koszyku as $produkt): ?>
                                <tr>
                                    <td> <img src="<?= htmlspecialchars($produkt['zdjecie']) ?>" class="card-img-top" alt="<?= htmlspecialchars($produkt['nazwa']) ?>" style="width: 100px; height: 100px;"></td>
                                    <td><?php echo htmlspecialchars($produkt['nazwa']); ?></td>
                                    <td>
                                        <form action="koszyk.php" method="POST" class="d-inline">
                                          <input type="number" name="ilosc" value="<?php echo $produkt['ilosc']; ?>" min="1" class="form-control" style="width: 80px;">
                                          <input type="hidden" name="produkt_id" value="<?php echo $produkt['id']; ?>">
                                          <button type="submit" name="aktualizuj_ilosc" class="btn btn-primary btn-sm mt-2">Aktualizuj</button>
                                        </form>
                                    </td>
                                    <td><?php echo number_format($produkt['cena'], 2, ',', ' '); ?> zł</td>
                                    <td><?php echo number_format($produkt['cena'] * $produkt['ilosc'], 2, ',', ' '); ?> zł</td>
                                    <td><a href="koszyk.php?usun=<?php echo $produkt['id']; ?>" class="btn btn-danger btn-sm">Usuń</a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="text-center">
                <h3>Całkowity koszt: <?php echo number_format($koszt_calosci, 2, ',', ' '); ?> zł</h3>
                <a href="platnosc_strona.php" class="btn btn-success mt-4">Przejdź do płatności</a>
                <a href="index.php" class="btn btn-secondary mt-4">Kontynuuj zakupy</a> <!-- Przycisk kontynuuj zakupy -->
            </div>
        </div>
    </section>

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
</body>
</html>
























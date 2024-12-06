<?php
session_start();
require_once 'db.php';

$zalogowany = isset($_SESSION['user_id']);
$czyAdmin = false;
$koszyk = [];
$sposobyPlatnosci = [];
$sposobyDostawy = [];
  
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
try {
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];

        $stmt = $pdo->prepare("SELECT username FROM konta WHERE id = :id");
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $username = $user['username'];

            $stmt = $pdo->prepare("SELECT p.id AS id_produktu, p.nazwa, p.cena, k.ilosc FROM koszyk k JOIN produkty p ON k.id_produkt = p.id WHERE k.id_user = :id_user");
            $stmt->bindValue(':id_user', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $koszyk = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare("SELECT * FROM sposob_platnosci");
            $stmt->execute();
            $sposobyPlatnosci = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare("SELECT * FROM sposob_dostawy");
            $stmt->execute();
            $sposobyDostawy = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} catch (Exception $e) {
    echo "Błąd: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pl">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Płatność</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .content {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        h1, h2, h3 {
            color: #343a40;
        }
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
    <main class="container py-5">
        <h1 class="mb-4">Wybierz metodę płatności i sposób dostawy</h1>
        <form method="POST" action="platnosc_strona_dane.php">
            <div class="row">
                <div class="col-md-6">
                    <h3>Podsumowanie koszyka</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produkt</th>
                                <th>Cena</th>
                                <th>Ilość</th>
                                <th>Razem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $suma = 0; ?>
                            <?php foreach ($koszyk as $produkt): ?>
                                <tr>
                                    <td><?= htmlspecialchars($produkt['nazwa']) ?></td>
                                    <td><?= htmlspecialchars($produkt['cena']) ?> zł</td>
                                    <td><?= htmlspecialchars($produkt['ilosc']) ?></td>
                                    <td><?= htmlspecialchars($produkt['cena'] * $produkt['ilosc']) ?> zł</td>
                                </tr>
                                <?php $suma += $produkt['cena'] * $produkt['ilosc']; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <h4>Łączna kwota: <?= $suma ?> zł</h4>
                    <input type="hidden" name="koszyk" value="<?= htmlspecialchars(json_encode($koszyk)) ?>">
                </div>

                <div class="col-md-6">
                    <h3>Sposoby płatności</h3>
                    <div class="mb-3">
                        <?php foreach ($sposobyPlatnosci as $sposob): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="sposob_platnosci" value="<?= $sposob['id_platnosc'] ?>" id="sposob_<?= $sposob['id_platnosc'] ?>" required>
                                <label class="form-check-label" for="sposob_<?= $sposob['id_platnosc'] ?>">
                                    <?= htmlspecialchars($sposob['nazwa']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <h3>Sposoby dostawy</h3>
                    <div class="mb-3">
                        <?php foreach ($sposobyDostawy as $dostawa): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="sposob_dostawy" value="<?= $dostawa['id_dostawy'] ?>" id="dostawa_<?= $dostawa['id_dostawy'] ?>" required>
                                <label class="form-check-label" for="dostawa_<?= $dostawa['id_dostawy'] ?>">
                                    <?= htmlspecialchars($dostawa['nazwa']) ?> - <?= htmlspecialchars($dostawa['koszt']) ?> zł
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit" class="btn btn-success">Zatwierdź zamówienie</button>
                </div>
            </div>
        </form>
    </main>    
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>














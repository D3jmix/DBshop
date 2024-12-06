<?php
include('db.php');
session_start();
  
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT username, id_klienta FROM konta WHERE id=:id");
    $stmt->bindValue(':id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $zalogowany = true;
        $username = $user['username'];
        $id_klienta = $user['id_klienta'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['wyloguj'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}    

$status_filter = isset($_GET['status']) ? $_GET['status'] : 'oczekujace';

$stmt = $pdo->prepare("SELECT z.id_zamowienia, z.email_zamowienie, z.data_zamowienia, z.status, z.cena_calkowita, d.nazwa AS dostawa, d.koszt AS koszt_dostawy, p.nazwa AS platnosc, k.nr_tel, GROUP_CONCAT(
        CONCAT(pr.nazwa, '||', pr.zdjecie, '||', zp.ilosc, '||', zp.cena_jednostkowa) SEPARATOR ';;') AS produkty FROM zamowienia z JOIN klienci k ON z.id_klienta = k.id_klienta JOIN sposob_dostawy d 
        ON z.sposob_dostawy = d.id_dostawy JOIN sposob_platnosci p ON z.szczegoly_platnosci = p.id_platnosc LEFT JOIN zamowienie_produkty zp ON z.id_zamowienia = zp.id_zamowienia LEFT JOIN produkty pr 
        ON zp.id_produktu = pr.id WHERE z.status = :status AND z.id_klienta = :id_klienta GROUP BY z.id_zamowienia ORDER BY z.data_zamowienia DESC");
$stmt->bindValue(':status', $status_filter, PDO::PARAM_STR);
$stmt->bindValue(':id_klienta', $id_klienta, PDO::PARAM_INT);
$stmt->execute();
$zamowienia = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($zamowienia as &$zamowienie) {
    $produkty = explode(';;', $zamowienie['produkty']);
    $zamowienie['produkty_lista'] = array_map(function ($produkt) {
        list($nazwa, $zdjecie, $ilosc, $cena_jednostkowa) = explode('||', $produkt);
        return [
            'nazwa' => $nazwa,
            'zdjecie' => $zdjecie,
            'ilosc' => $ilosc,
            'cena_jednostkowa' => $cena_jednostkowa,
        ];
    }, $produkty);
}  
  
$statuses = ['oczekujace', 'zrealizowane', 'anulowane'];
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarządzanie zamówieniami</title>
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
                    <li><a class="dropdown-item" href="panel_admina.php">Panel admina</a></li>
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
<div class="container my-4">
    <h2 class="text-center mb-4">Zarządzanie zamówieniami</h2>
    <form method="GET" class="mb-4">
        <label for="status" class="form-label">Wybierz status zamówienia:</label>
        <select name="status" id="status" class="form-select" onchange="this.form.submit()">
            <?php foreach ($statuses as $status): ?>
                <option value="<?= $status ?>" <?= $status_filter == $status ? 'selected' : '' ?>>
                    <?= ucfirst($status) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    <table class="table table-hover">
        <thead class="table-dark">
            <tr>
                <th>Email</th>
                <th>Numer telefonu</th>
                <th>Sposób dostawy</th>
                <th>Koszt dostawy</th>
                <th>Sposób płatności</th>
                <th>Status</th>
                <th>Produkty</th>
                <th>Cena całkowita</th>
                <th>Data dodania</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($zamowienia as $zamowienie): ?>
                <tr>
                    <td><?= htmlspecialchars($zamowienie['email_zamowienie']) ?></td>
                    <td><?= htmlspecialchars($zamowienie['nr_tel']) ?></td>
                    <td><?= htmlspecialchars($zamowienie['dostawa']) ?></td>
                    <td><?= number_format($zamowienie['koszt_dostawy'], 2, ',', ' ') ?> zł</td>
                    <td><?= htmlspecialchars($zamowienie['platnosc']) ?></td>
                    <td><span class="badge bg-secondary"><?= htmlspecialchars($zamowienie['status']) ?></span></td>
                    <td>
                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#produktyModal<?= $zamowienie['id_zamowienia'] ?>">
                            <i class="bi bi-eye"></i> Zobacz
                        </button>
                        <div class="modal fade" id="produktyModal<?= $zamowienie['id_zamowienia'] ?>" tabindex="-1" aria-labelledby="produktyModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Produkty zamówienia</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <?php foreach ($zamowienie['produkty_lista'] as $produkt): ?>
                                            <div class="mb-3 text-center">
                                                <img src="<?= htmlspecialchars($produkt['zdjecie']) ?>" alt="<?= htmlspecialchars($produkt['nazwa']) ?>" class="img-fluid rounded">
                                                <p><strong>Nazwa:</strong> <?= htmlspecialchars($produkt['nazwa']) ?></p>
                                                <p><strong>Ilość:</strong> <?= htmlspecialchars($produkt['ilosc']) ?></p>
                                                <p><strong>Cena jednostkowa:</strong> <?= number_format($produkt['cena_jednostkowa'], 2, ',', ' ') ?> zł</p>
                                            </div>
                                            <hr>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zamknij</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td><?= number_format($zamowienie['cena_calkowita'], 2, ',', ' ') ?> zł</td>
                    <td><?= htmlspecialchars($zamowienie['data_zamowienia']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
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
</body>
</html>






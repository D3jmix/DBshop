<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $koszyk = isset($_POST['koszyk']) ? json_decode($_POST['koszyk'], true) : [];
    $imie = $_POST['imie'] ?? '';
    $nazwisko = $_POST['nazwisko'] ?? '';
    $telefon = $_POST['telefon'] ?? '';
    $email = $_POST['email'] ?? '';
    $sposob_platnosci = $_POST['sposob_platnosci'] ?? '';
    $sposob_dostawy = $_POST['sposob_dostawy'] ?? '';

    $ulica_dostawy = $_POST['ulica_dostawy'] ?? '';
    $numer_domu_dostawy = $_POST['numer_domu_dostawy'] ?? '';
    $miasto_dostawy = $_POST['miasto_dostawy'] ?? '';
    $kod_pocztowy_dostawy = $_POST['kod_pocztowy_dostawy'] ?? '';
    $kraj_dostawy = $_POST['kraj_dostawy'] ?? '';

    $ulica_rozliczeniowa = $_POST['ulica_rozliczeniowa'] ?? '';
    $numer_domu_rozliczeniowy = $_POST['numer_domu_rozliczeniowy'] ?? '';
    $miasto_rozliczeniowe = $_POST['miasto_rozliczeniowe'] ?? '';
    $kod_pocztowy_rozliczeniowy = $_POST['kod_pocztowy_rozliczeniowy'] ?? '';
    $kraj_rozliczeniowy = $_POST['kraj_rozliczeniowy'] ?? '';

    $numer_karty = $_POST['numer_karty'] ?? '';
    $cvv = $_POST['cvv'] ?? '';
    $data_waznosci = $_POST['data_waznosci'] ?? '';
    $nazwa_posiadacza = $_POST['nazwa_posiadacza'] ?? '';

    $numer_telefonu_blik = $_POST['numer_telefonu'] ?? '';
    $kod_blik = $_POST['kod_blik'] ?? '';

    $ulica_faktura = $_POST['ulica_faktura'] ?? '';
    $numer_budynku_faktura = $_POST['numer_budynku_faktura'] ?? '';
    $miasto_faktura = $_POST['miasto_faktura'] ?? '';
    $kod_pocztowy_faktura = $_POST['kod_pocztowy_faktura'] ?? '';
    $kraj_faktura = $_POST['kraj_faktura'] ?? '';
    $nip_faktura = $_POST['nip_faktura'] ?? '';
    $nazwa_firmy_faktura = $_POST['nazwa_firmy_faktura'] ?? '';
try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('SELECT id_klienta FROM klienci WHERE imie = ? AND nazwisko = ? AND nr_tel = ?');
        $stmt->execute([$imie, $nazwisko, $telefon]);
        $klient = $stmt->fetch();

        if (!$klient) {
            $stmt = $pdo->prepare('INSERT INTO klienci (imie, nazwisko, nr_tel, email) VALUES (?, ?, ?, ?)');
            $stmt->execute([$imie, $nazwisko, $telefon, $email]);
            $id_klienta = $pdo->lastInsertId();
        } else {
            $id_klienta = $klient['id_klienta'];
        }

        $id_metody = null;
        if ($sposob_platnosci) {
            $stmt = $pdo->prepare('SELECT id_platnosc, nazwa FROM sposob_platnosci WHERE id_platnosc = ?');
            $stmt->execute([$sposob_platnosci]);
            $row = $stmt->fetch();
    
        if ($row) {
              $id_platnosc = $row['id_platnosc'];
              $nazwa = $row['nazwa'];
      
              if ($nazwa == 'karta') {
                $stmt = $pdo->prepare('INSERT INTO metody_platnosci (id_klienta, id_platnosci, numer_karty, data_waznosci, cvv, nazwa_posiadacza, data_dodania) VALUES (?, ?, ?, ?, ?, ?, NOW())');
                $stmt->execute([$id_klienta, $id_platnosc, $numer_karty, $data_waznosci, $cvv, $nazwa_posiadacza]);
                $id_metody = $pdo->lastInsertId();
              } elseif ($nazwa == 'blik') {
                $stmt = $pdo->prepare('INSERT INTO metody_platnosci (id_klienta, id_platnosci, numer_telefonu, kod_blik, data_dodania) VALUES (?, ?, ?, ?, NOW())');
                $stmt->execute([$id_klienta, $id_platnosc, $numer_telefonu_blik, $kod_blik]);
                $id_metody = $pdo->lastInsertId();
              }
        } else {
              throw new Exception("Metoda płatności '$sposob_platnosci' nie istnieje w bazie danych.");
        }
        }
        if (!$id_metody) {
            $id_metody = null;
        }

        $stmt = $pdo->prepare('SELECT id_adresu_d FROM adres_dostawy WHERE ulica = ? AND numer_domu = ? AND miasto = ? AND kod_pocztowy = ? AND kraj = ?');
        $stmt->execute([$ulica_dostawy, $numer_domu_dostawy, $miasto_dostawy, $kod_pocztowy_dostawy, $kraj_dostawy]);
        $adres_dostawy = $stmt->fetch();

        if (!$adres_dostawy) {
            $stmt = $pdo->prepare('INSERT INTO adres_dostawy (ulica, numer_domu, miasto, kod_pocztowy, kraj) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$ulica_dostawy, $numer_domu_dostawy, $miasto_dostawy, $kod_pocztowy_dostawy, $kraj_dostawy]);
            $id_adres_dostawy = $pdo->lastInsertId();
        } else {
            $id_adres_dostawy = $adres_dostawy['id_adresu_d'];
        }
  
        $stmt = $pdo->prepare('SELECT id_adresu_r FROM adres_rozliczeniowy WHERE ulica = ? AND numer_domu = ? AND miasto = ? AND kod_pocztowy = ? AND kraj = ?');
        $stmt->execute([$ulica_rozliczeniowa, $numer_domu_rozliczeniowy, $miasto_rozliczeniowe, $kod_pocztowy_rozliczeniowy, $kraj_rozliczeniowy]);
        $adres_rozliczeniowy = $stmt->fetch();

        if (!$adres_rozliczeniowy) {
            $stmt = $pdo->prepare('INSERT INTO adres_rozliczeniowy (ulica, numer_domu, miasto, kod_pocztowy, kraj) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$ulica_rozliczeniowa, $numer_domu_rozliczeniowy, $miasto_rozliczeniowe, $kod_pocztowy_rozliczeniowy, $kraj_rozliczeniowy]);
            $id_adres_rozliczeniowy = $pdo->lastInsertId();
        } else {
            $id_adres_rozliczeniowy = $adres_rozliczeniowy['id_adresu_r'];
        }

       if ($nazwa_firmy_faktura) {
    try {
        $stmt = $pdo->prepare('INSERT INTO dane_faktura (id_klienta, ulica, numer_budynku, miasto, kod_pocztowy, kraj, nip, nazwa_firmy) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$id_klienta, $ulica_faktura, $numer_budynku_faktura, $miasto_faktura, $kod_pocztowy_faktura, $kraj_faktura, $nip_faktura, $nazwa_firmy_faktura]);

        $id_faktury = $pdo->lastInsertId();

    } catch (Exception $e) {
        echo "Błąd przy dodawaniu danych faktury: " . $e->getMessage();
        $pdo->rollBack();
        exit;
    }
} else {
    $id_faktury = null;
}   
        $stmt = $pdo->prepare("SELECT nazwa, koszt FROM sposob_dostawy WHERE id_dostawy = :id");
        $stmt->bindValue(':id', $sposob_dostawy, PDO::PARAM_INT);
        $stmt->execute();
        $sposob = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($sposob) {
            $sposob_dostawy_nazwa = $sposob['nazwa'];
            $sposob_dostawy_cena = $sposob['koszt'];
        }
         $cena_calkowita = 0;

        foreach ($koszyk as $produkt) {
            $wartosc_calkowita = ($produkt['ilosc'] * $produkt['cena']) + $sposob_dostawy_cena ;
            $cena_calkowita += $wartosc_calkowita;
        }

  $stmt = $pdo->prepare('INSERT INTO zamowienia (id_klienta, email_zamowienie, sposob_dostawy, id_adres_dostawy, id_adres_rozliczeniowy, szczegoly_platnosci, metoda_platnosci, cena_calkowita, id_faktury, data_zamowienia) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
  $stmt->execute([$id_klienta, $email, $sposob_dostawy, $id_adres_dostawy, $id_adres_rozliczeniowy, $sposob_platnosci, $id_metody, $cena_calkowita, $id_faktury]);
  $id_zamowienia = $pdo->lastInsertId();

        foreach ($koszyk as $produkt) {
          foreach ($row as $koszt_dostawa) {
            $wartosc_calkowita = ($produkt['ilosc'] * $produkt['cena']) + $sposob_dostawy_cena ;
          }

            $stmt = $pdo->prepare('INSERT INTO zamowienie_produkty (id_zamowienia, id_produktu, ilosc, cena_jednostkowa, wartosc_calkowita) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$id_zamowienia, $produkt['id_produktu'], $produkt['ilosc'], $produkt['cena'], $wartosc_calkowita]);
        
            $stmt = $pdo->prepare('UPDATE produkty SET stan = stan - ? WHERE id = ? AND stan >= ?');
            $stmt->execute([$produkt['ilosc'], $produkt['id_produktu'], $produkt['ilosc']]);

            if ($stmt->rowCount() === 0) {
            throw new Exception("Nie można zrealizować zamówienia dla produktu o ID: {$produkt['id_produktu']}, brak wystarczającej ilości w magazynie.");
    }
        }

        $pdo->commit();
    } catch (Exception $e) {
        echo "Błąd przy składaniu zamówienia: " . $e->getMessage();
        $pdo->rollBack();
    }

  try {
        $stmt = $pdo->prepare("SELECT nazwa FROM sposob_platnosci WHERE id_platnosc = :id");
        $stmt->bindValue(':id', $sposob_platnosci, PDO::PARAM_INT);
        $stmt->execute();
        $sposob = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($sposob) {
            $sposob_platnosci_nazwa = $sposob['nazwa'];
        }
    } catch (Exception $e) {
        echo "Błąd: " . $e->getMessage();
    }
} else {
    echo "<p>Niepoprawna metoda żądania. Proszę skorzystać z formularza zamówienia.</p>";
}
?>


<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Potwierdzenie zamówienia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
     <style>
        body {
            background-color: #f8f9fa;
        }
        header {
            background: linear-gradient(90deg, #343a40, #495057);
        }
        header h1 {
            font-size: 2rem;
            font-weight: bold;
        }
        .content-section {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .content-section h2 {
            color: #495057;
        }
        .btn-success {
            width: 50%;
            font-size: 1.2rem;
        }
       .btn-danger {
            width: 50%;
            font-size: 1.2rem;
        }
       form.d-flex {
         align-items: center;
       }
       form.d-flex button, 
       form.d-flex a {
         flex: 0 1 auto;
       }
    </style>
</head>
<body>
    <header class="bg-dark text-white py-3">
        <div class="container">
            <h1>Potwierdzenie zamówienia</h1>
        </div>
    </header>
    <main class="container py-5">
        <div class="content-section">
            <h2>Dane osobowe</h2>
            <ul class="list-group">
                <li class="list-group-item">Imię: <?= htmlspecialchars($imie) ?></li>
                <li class="list-group-item">Nazwisko: <?= htmlspecialchars($nazwisko) ?></li>
                <li class="list-group-item">Telefon: <?= htmlspecialchars($telefon) ?></li>
                <li class="list-group-item">Email: <?= htmlspecialchars($email) ?></li>
            </ul>
        </div>
        
        <div class="content-section">
            <h2>Podsumowanie koszyka</h2>
            <table class="table table-striped table-hover">
                <thead class="table-dark">
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
            <h4 class="text-end">Łączna wartość produktów: <strong><?= $suma ?> zł</strong></h4>
        </div>

        <div class="content-section">
            <h2>Sposób płatności</h2>
            <p class="fw-bold"><?= htmlspecialchars($sposob_platnosci_nazwa ?? 'Nieznany') ?></p>
        </div>

        <div class="content-section">
            <h2>Sposób dostawy</h2>
            <p class="fw-bold"><?= htmlspecialchars($sposob_dostawy_nazwa ?? 'Nieznany') ?> (<?= isset($sposob_dostawy_cena) ? htmlspecialchars($sposob_dostawy_cena) . ' zł' : 'Cena nieznana' ?>)</p>
        </div>

        <div class="content-section">
            <h2>Adres dostawy</h2>
            <ul class="list-group">
                <li class="list-group-item">Ulica: <?= htmlspecialchars($ulica_dostawy) ?></li>
                <li class="list-group-item">Numer domu: <?= htmlspecialchars($numer_domu_dostawy) ?></li>
                <li class="list-group-item">Miasto: <?= htmlspecialchars($miasto_dostawy) ?></li>
                <li class="list-group-item">Kod pocztowy: <?= htmlspecialchars($kod_pocztowy_dostawy) ?></li>
                <li class="list-group-item">Kraj: <?= htmlspecialchars($kraj_dostawy) ?></li>
            </ul>
        </div>

        <?php if (array_filter([$ulica_rozliczeniowa, $numer_domu_rozliczeniowy, $miasto_rozliczeniowe, $kod_pocztowy_rozliczeniowy, $kraj_rozliczeniowy])): ?>
            <div class="content-section">
                <h2>Adres rozliczeniowy</h2>
                <ul class="list-group">
                    <li class="list-group-item">Ulica: <?= htmlspecialchars($ulica_rozliczeniowa) ?></li>
                    <li class="list-group-item">Numer domu: <?= htmlspecialchars($numer_domu_rozliczeniowy) ?></li>
                    <li class="list-group-item">Miasto: <?= htmlspecialchars($miasto_rozliczeniowe) ?></li>
                    <li class="list-group-item">Kod pocztowy: <?= htmlspecialchars($kod_pocztowy_rozliczeniowy) ?></li>
                    <li class="list-group-item">Kraj: <?= htmlspecialchars($kraj_rozliczeniowy) ?></li>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (array_filter([$numer_karty, $cvv, $data_waznosci, $nazwa_posiadacza])): ?>
            <div class="content-section">
                <h2>Dane karty</h2>
                <ul class="list-group">
                    <li class="list-group-item">Numer karty: <?= htmlspecialchars($numer_karty) ?></li>
                    <li class="list-group-item">CVV: <?= htmlspecialchars($cvv) ?></li>
                    <li class="list-group-item">Data ważności: <?= htmlspecialchars($data_waznosci) ?></li>
                    <li class="list-group-item">Nazwa posiadacza: <?= htmlspecialchars($nazwa_posiadacza) ?></li>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (array_filter([$numer_telefonu_blik, $kod_blik])): ?>
            <div class="content-section">
                <h2>Dane BLIK</h2>
                <ul class="list-group">
                    <li class="list-group-item">Numer telefonu: <?= htmlspecialchars($numer_telefonu_blik) ?></li>
                    <li class="list-group-item">Kod BLIK: <?= htmlspecialchars($kod_blik) ?></li>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (array_filter([$ulica_faktura, $numer_budynku_faktura, $miasto_faktura, $kod_pocztowy_faktura, $kraj_faktura, $nip_faktura, $nazwa_firmy_faktura])): ?>
            <div class="content-section">
                <h2>Dane do faktury</h2>
                <ul class="list-group">
                    <li class="list-group-item">Ulica: <?= htmlspecialchars($ulica_faktura) ?></li>
                    <li class="list-group-item">Numer budynku: <?= htmlspecialchars($numer_budynku_faktura) ?></li>
                    <li class="list-group-item">Miasto: <?= htmlspecialchars($miasto_faktura) ?></li>
                    <li class="list-group-item">Kod pocztowy: <?= htmlspecialchars($kod_pocztowy_faktura) ?></li>
                    <li class="list-group-item">Kraj: <?= htmlspecialchars($kraj_faktura) ?></li>
                    <li class="list-group-item">NIP: <?= htmlspecialchars($nip_faktura) ?></li>
                    <li class="list-group-item">Nazwa firmy: <?= htmlspecialchars($nazwa_firmy_faktura) ?></li>
                </ul>
            </div>
        <?php endif; ?>
        <h4 class="text-end">Łączna wartość zamówienia:<strong><?= $suma + $sposob_dostawy_cena ?> zł</strong></h4>
        <div class="content-section text-center">
            <form method="POST" action="index.php" class="d-flex justify-content-center gap-2">
                <input type="hidden" name="action" value="dodaj_zamowienie">
                <button type="submit" class="btn btn-success">Potwierdź zamówienie</button>
                <a href="index.php" class="btn btn-danger">Anuluj zamówienie</a>
            </form>
        </div>
    </main>
</body>
</html>





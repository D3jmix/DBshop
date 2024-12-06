<?php
include('db.php');

$status_filter = isset($_GET['status']) ? $_GET['status'] : 'oczekujace';

$stmt = $pdo->prepare("SELECT k.imie, k.nazwisko, k.nr_tel, k.notatka, z.id_zamowienia, z.email_zamowienie, z.data_zamowienia, z.data_aktualizacji, z.status, z.id_faktury, z.cena_calkowita, d.nazwa AS dostawa, d.koszt
  AS koszt_dostawy, p.nazwa AS platnosc, ad.id_adresu_d, ad.ulica AS ulica_d, ad.numer_domu AS numer_domu_d, ad.miasto AS miasto_d, ad.kod_pocztowy AS kod_pocztowy_d, ad.kraj AS kraj_d, ar.id_adresu_r, ar.ulica 
  AS ulica_r, ar.numer_domu AS numer_domu_r, ar.miasto AS miasto_r, ar.kod_pocztowy AS kod_pocztowy_r, ar.kraj AS kraj_r, f.nip AS faktura_nip, f.nazwa_firmy AS faktura_nazwa_firmy, f.ulica AS faktura_ulica,
  f.numer_budynku AS faktura_numer_budynku, f.miasto AS faktura_miasto, f.kod_pocztowy AS faktura_kod_pocztowy, f.kraj AS faktura_kraj, pr.id, pr.nazwa AS produkt_nazwa, pr.zdjecie AS produkt_zdjecie, zp.id_produktu, 
  zp.ilosc, zp.cena_jednostkowa FROM zamowienia z JOIN klienci k ON z.id_klienta = k.id_klienta JOIN sposob_dostawy d ON z.sposob_dostawy = d.id_dostawy JOIN sposob_platnosci p ON z.szczegoly_platnosci = p.id_platnosc
  LEFT JOIN adres_dostawy ad ON z.id_adres_dostawy = ad.id_adresu_d LEFT JOIN adres_rozliczeniowy ar ON z.id_adres_rozliczeniowy= ar.id_adresu_r LEFT JOIN dane_faktura f ON z.id_faktury = f.id_faktury
  LEFT JOIN zamowienie_produkty zp ON z.id_zamowienia = zp.id_zamowienia LEFT JOIN produkty pr ON zp.id_produktu = pr.id WHERE z.status = :status ORDER BY z.data_zamowienia DESC");
$stmt->bindValue(':status', $status_filter, PDO::PARAM_STR);
$stmt->execute();
$zamowienia = $stmt->fetchAll(PDO::FETCH_ASSOC);

$zamowienia_grupowane = [];
foreach ($zamowienia as $wiersz) {
    $id_zamowienia = $wiersz['id_zamowienia'];

    if (!isset($zamowienia_grupowane[$id_zamowienia])) {
        $zamowienia_grupowane[$id_zamowienia] = [
            'info' => [
                'imie' => $wiersz['imie'],
                'nazwisko' => $wiersz['nazwisko'],
                'email_zamowienie' => $wiersz['email_zamowienie'],
                'status' => $wiersz['status'],
                'data_zamowienia' => $wiersz['data_zamowienia'],
                'id_zamowienia' => $id_zamowienia
            ],
            'produkty_lista' => [] 
        ];
    }

    $zamowienia_grupowane[$id_zamowienia]['produkty_lista'][] = [
        'nazwa' => $wiersz['produkt_nazwa'],
        'ilosc' => $wiersz['ilosc'],
        'cena_jednostkowa' => $wiersz['cena_jednostkowa'],
        'zdjecie' => $wiersz['produkt_zdjecie']
    ];
}
  
$statuses = ['oczekujace', 'zrealizowane', 'anulowane'];

$stmt_platnosci = $pdo->query("SELECT id_platnosc, nazwa FROM sposob_platnosci");
$platnosci = $stmt_platnosci->fetchAll(PDO::FETCH_ASSOC);

$stmt_dostawy = $pdo->query("SELECT id_dostawy, nazwa, koszt FROM sposob_dostawy");
$dostawy = $stmt_dostawy->fetchAll(PDO::FETCH_ASSOC);


 if (isset($_POST['action']) && $_POST['action'] == 'zmien_status') {
        $id_zamowienia = $_POST['id_zamowienia'];
        $new_status = $_POST['new_status'];

        $stmt_update_status = $pdo->prepare("UPDATE zamowienia SET status = :new_status WHERE id_zamowienia = :id_zamowienia");
        $stmt_update_status->execute([
            ':new_status' => $new_status,
            ':id_zamowienia' => $id_zamowienia
        ]);
        header("Location: ?page=zamowienia&status=" . urlencode($status_filter));
        exit();
    }  
  
if (isset($_POST['action']) && $_POST['action'] == 'edytuj_fakture') {
    $id_faktury = $_POST['id_faktury'];
    $faktura_nip = $_POST['faktura_nip'];
    $faktura_nazwa_firmy = $_POST['faktura_nazwa_firmy'];
    $faktura_ulica = $_POST['faktura_ulica'];
    $faktura_numer_budynku = $_POST['faktura_numer_budynku'];
    $faktura_miasto = $_POST['faktura_miasto'];
    $faktura_kod_pocztowy = $_POST['faktura_kod_pocztowy'];
    $faktura_kraj = $_POST['faktura_kraj'];

    $stmt = $pdo->prepare("UPDATE dane_faktura SET nip = :nip, nazwa_firmy = :nazwa_firmy, ulica = :ulica, numer_budynku = :numer_budynku, miasto = :miasto, kod_pocztowy = :kod_pocztowy, kraj = :kraj 
      WHERE id_faktury = :id_faktury");
    $stmt->execute([
        ':nip' => $faktura_nip,
        ':nazwa_firmy' => $faktura_nazwa_firmy,
        ':ulica' => $faktura_ulica,
        ':numer_budynku' => $faktura_numer_budynku,
        ':miasto' => $faktura_miasto,
        ':kod_pocztowy' => $faktura_kod_pocztowy,
        ':kraj' => $faktura_kraj,
        ':id_faktury' => $id_faktury
    ]);

    header("Location: ?page=zamowienia&status=" . urlencode($status_filter));
    exit();
}

?>
<div class="container">
    <h2 class="mb-4">Zarządzanie zamówieniami</h2>

    <form method="GET" class="mb-4">
        <input type="hidden" name="page" value="zamowienia">
        <label for="status" class="form-label">Wybierz status zamówienia:</label>
        <select name="status" id="status" class="form-select" onchange="this.form.submit()">
            <?php foreach ($statuses as $status): ?>
                <option value="<?= $status ?>" <?= $status_filter == $status ? 'selected' : '' ?>>
                    <?= ucfirst($status) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Imię</th>
                <th>Nazwisko</th>
                <th>Email</th>
                <th>Sposób dostawy</th>
                <th>Koszt dostawy</th>
                <th>Sposób płatności</th>
                <th>Status</th>
                <th>Produkty</th>
                <th>Cena całkowita</th>
                <th>Czy faktura</th>
                <th>Data aktualizacji</th>
                <th>Akcje</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($zamowienia as $zamowienie): ?>
                <tr>
                    <td><?= htmlspecialchars($zamowienie['imie']) ?></td>
                    <td><?= htmlspecialchars($zamowienie['nazwisko']) ?></td>
                    <td><?= htmlspecialchars($zamowienie['email_zamowienie']) ?></td>
                    <td><?= htmlspecialchars($zamowienie['dostawa']) ?></td>
                    <td><?= htmlspecialchars($zamowienie['koszt_dostawy']) ?></td>
                    <td><?= htmlspecialchars($zamowienie['platnosc']) ?></td>
                    <td><?= htmlspecialchars($zamowienie['status']) ?></td>
                    <td>
                      <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#produktyModal<?= $zamowienie['id_zamowienia'] ?>">
                        Zobacz
                      </button>
                      <?php foreach ($zamowienia_grupowane as $zamowienie1): ?>
                      <div class="modal fade" id="produktyModal<?= $zamowienie1['info']['id_zamowienia'] ?>" tabindex="-1" aria-labelledby="produktyModalLabel<?= $zamowienie1['info']['id_zamowienia'] ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title" id="produktyModalLabel<?= $zamowienie1['info']['id_zamowienia'] ?>">Produkty zamówienia</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                              <?php foreach ($zamowienie1['produkty_lista'] as $produkt): ?>
                              <div class="mb-3 text-center">
                                <img src="<?= htmlspecialchars($produkt['zdjecie']) ?>" alt="<?= htmlspecialchars($produkt['nazwa']) ?>" class="img-fluid rounded">
                                <p><strong>Nazwa:</strong> <?= htmlspecialchars($produkt['nazwa']) ?></p>
                                <p><strong>Ilość:</strong> <?= htmlspecialchars($produkt['ilosc']) ?></p>
                                <p><strong>Cena jednostkowa:</strong> <?= number_format($produkt['cena_jednostkowa'], 2, ',', ' ') ?> zł</p>
                              </div>
                              <hr>
                              <?php endforeach; ?>
                            </div>
                          </div>
                        </div>
                      </div>
                      <?php endforeach; ?>
                    </td>
                    <td><?= number_format($zamowienie['cena_calkowita'], 2, ',', ' ') . ' zł' ?></td>
                    <td>
                      <?php if ($zamowienie['id_faktury']): ?>
                      <span class="badge bg-success m-3">TAK</span>
                      <button type="button" class="btn btn-primary btn-sm m-1" data-bs-toggle="modal" data-bs-target="#fakturaModal<?= $zamowienie['id_zamowienia'] ?>">Dane</button>
                      <?php else: ?>
                      <span class="badge bg-danger m-3">NIE</span>
                      <?php endif; ?>
                      <div class="modal fade" id="fakturaModal<?= $zamowienie['id_zamowienia'] ?>" tabindex="-1" aria-labelledby="fakturaModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                          <div class="modal-content">
                            <form method="POST" action="">
                              <div class="modal-header">
                                <h5 class="modal-title" id="fakturaModalLabel">Edycja Danych Faktury</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              </div>
                              <div class="modal-body">
                                <input type="hidden" name="action" value="edytuj_fakture">
                                <input type="hidden" name="id_faktury" value="<?= htmlspecialchars($zamowienie['id_faktury']) ?>">
                                <div class="mb-3">
                                  <label for="faktura_nip" class="form-label">NIP:</label>
                                  <input type="text" class="form-control" name="faktura_nip" value="<?= htmlspecialchars($zamowienie['faktura_nip']) ?>">
                                </div>
                                <div class="mb-3">
                                  <label for="faktura_nazwa_firmy" class="form-label">Nazwa firmy:</label>
                                  <input type="text" class="form-control" name="faktura_nazwa_firmy" value="<?= htmlspecialchars($zamowienie['faktura_nazwa_firmy']) ?>">
                                </div>
                                <div class="mb-3">
                                  <label for="faktura_ulica" class="form-label">Ulica:</label>
                                  <input type="text" class="form-control" name="faktura_ulica" value="<?= htmlspecialchars($zamowienie['faktura_ulica']) ?>">
                                </div>
                                <div class="mb-3">
                                  <label for="faktura_numer_budynku" class="form-label">Numer budynku:</label>
                                  <input type="text" class="form-control" name="faktura_numer_budynku" value="<?= htmlspecialchars($zamowienie['faktura_numer_budynku']) ?>">
                                </div>
                                <div class="mb-3">
                                  <label for="faktura_miasto" class="form-label">Miasto:</label>
                                  <input type="text" class="form-control" name="faktura_miasto" value="<?= htmlspecialchars($zamowienie['faktura_miasto']) ?>">
                                </div>
                                <div class="mb-3">
                                  <label for="faktura_kod_pocztowy" class="form-label">Kod pocztowy:</label>
                                  <input type="text" class="form-control" name="faktura_kod_pocztowy" value="<?= htmlspecialchars($zamowienie['faktura_kod_pocztowy']) ?>">
                                </div>
                                <div class="mb-3">
                                  <label for="faktura_kraj" class="form-label">Kraj:</label>
                                  <input type="text" class="form-control" name="faktura_kraj" value="<?= htmlspecialchars($zamowienie['faktura_kraj']) ?>">
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button type="submit" name="edytuj_fakture" class="btn btn-primary">Zapisz zmiany</button>
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td><?= htmlspecialchars($zamowienie['data_aktualizacji']) ?></td>
                    <td>
                      <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#statusModal<?= $zamowienie['id_zamowienia'] ?>">
                            Zmień status
                        </button>
                        <div class="modal fade" id="statusModal<?= $zamowienie['id_zamowienia'] ?>" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title" id="statusModalLabel">Zmień status zamówienia</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              </div>
                              <div class="modal-body">
                                <form method="POST" action="">
                                  <input type="hidden" name="action" value="zmien_status">
                                  <input type="hidden" name="id_zamowienia" value="<?= $zamowienie['id_zamowienia'] ?>">
                                  
                                  <div class="mb-3">
                                    <label for="new_status" class="form-label">Nowy status zamówienia</label>
                                    <select name="new_status" class="form-select" id="new_status">
                                      <?php foreach ($statuses as $status): ?>
                                      <option value="<?= $status ?>" <?= $zamowienie['status'] == $status ? 'selected' : '' ?>>
                                        <?= ucfirst($status) ?>
                                      </option>
                                      <?php endforeach; ?>
                                    </select>
                                  </div>
                                  
                                  <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                                    <button type="submit" class="btn btn-primary">Zmień status</button>
                                  </div>
                                </form>
                              </div>
                            </div>
                          </div>
                      </div>
                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#infoModal<?= $zamowienie['id_zamowienia'] ?>">Info</button>
                        <div class="modal fade" id="infoModal<?= $zamowienie['id_zamowienia'] ?>" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title" id="infoModalLabel">Dane Klienta</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              </div>
                                <div class="modal-body">
                                    <h5><strong><u>Dodatkowe informacje</u></strong></h5>
                                      <p><strong>Numer telefonu:</strong> <?= htmlspecialchars($zamowienie['nr_tel']) ?> </p>
                                      <p><strong>Notatka:</strong> <?= htmlspecialchars($zamowienie['notatka']) ?> </p>
                                      <p><strong>Data złożenia:</strong> <?= htmlspecialchars($zamowienie['data_zamowienia']) ?></p>
                                    <h5><strong><u>Adres Dostawy:</u></strong></h5>
                                      <p><strong>Ulica:</strong> <?= htmlspecialchars($zamowienie['ulica_d']) ?></p>
                                      <p><strong>Numer domu:</strong> <?= htmlspecialchars($zamowienie['numer_domu_d']) ?></p>
                                      <p><strong>Miasto:</strong> <?= htmlspecialchars($zamowienie['miasto_d']) ?></p>
                                      <p><strong>Kod pocztowy:</strong> <?= htmlspecialchars($zamowienie['kod_pocztowy_d']) ?></p>
                                      <p><strong>Kraj:</strong><?= htmlspecialchars($zamowienie['kraj_d']) ?></p>
                                    <?php if ($zamowienie['id_adresu_r'] !== null): ?>
                                    <h5><strong><u>Adres Rozliczeniowy:</u></strong></h5>
                                      <p><strong>Ulica:</strong> <?= htmlspecialchars($zamowienie['ulica_r']) ?></p>
                                      <p><strong>Numer domu:</strong> <?= htmlspecialchars($zamowienie['numer_domu_r']) ?></p>
                                      <p><strong>Miasto:</strong> <?= htmlspecialchars($zamowienie['miasto_r']) ?></p>
                                      <p><strong>Kod pocztowy:</strong> <?= htmlspecialchars($zamowienie['kod_pocztowy_r']) ?></p>
                                      <p><strong>Kraj:</strong> <?= htmlspecialchars($zamowienie['kraj_r']) ?></p>
                                    <?php endif; ?>
                              </div>
                          </div>
                      </div>
                       </div>
                  </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script>
function confirmEdit() {
    return confirm("Czy na pewno chcesz zedytować to zamówienie?");
}
</script>

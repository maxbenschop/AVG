<?php
error_reporting(E_ALL);
ini_set("display_errors", true);

include('database.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $db = new database();
  $db->connect();
  $klant = $db->getKlant(intval($_POST['id']));
  if (isset($_POST['exportcvs'])) {
    $filename = "klantinfo.csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $data = array(
      array('Voornaam', 'Tussenvoegsel', 'Achternaam', 'Geboortedatum'),
      array(strip_tags($klant['voornaam']), strip_tags($klant['tussenvoegsel']), strip_tags($klant['achternaam']), strip_tags($klant['geboortedatum']))
    );
    $output = fopen('php://output', 'w');
    foreach ($data as $row) {
      fputcsv($output, $row);
    }
    exit();
  }

  if (isset($_POST['exportzip'])) {
    $filename = "klantfoto.zip";
    $zip = new ZipArchive();
    if ($zip->open($filename, ZipArchive::CREATE) === TRUE) {
      $zip->addFile('uploads/foto_' . $klant['id'] . '.jpg', 'foto_' . $klant['id'] . '.jpg');
      $zip->close();
      header('Content-Type: application/zip');
      header('Content-Disposition: attachment; filename="' . $filename . '"');
      readfile($filename);
      unlink($filename);
    }
    exit();
  }
}


?>
<!DOCTYPE html>
<html>

<head>
  <title>Overzicht</title>
  <link href="css/style.css" rel="stylesheet" type="text/css" />
</head>

<body>
  <?php
  if (isset($_GET['id'])) {
    $klant = $db->getKlant(intval($_GET['id']));
  ?>
    <h1>Gegevens van
      <?= $klant['voornaam'] . ' ' . $klant['tussenvoegsel'] . ' ' . $klant['achternaam'] ?>
    </h1>
    <table>
      <tr>
        <td>Voornaam:</td>
        <td>
          <?= $klant['voornaam'] ?>
        </td>
      </tr>
      <tr>
        <td>Achternaam:</td>
        <td>
          <?= $klant['achternaam'] ?>
          <?php if ($klant['tussenvoegsel']) {
            echo (', ' . $klant['tussenvoegsel']);
          } ?>
        </td>
      </tr>
    </table>
    <img src="uploads/foto_<?= $klant['id'] ?>.jpg" alt="<?= $klant['voornaam'] . ' ' . $klant['tussenvoegsel'] . ' ' . $klant['achternaam'] ?>" />

    <form action="" method="post">
      <input type="hidden" name="id" value="<?= $klant['id'] ?>">

      <input type="submit" name="exportcvs" value="Download Info">
      <input type="submit" name="exportzip" value="Download Foto">
    </form>

    <?php
    exit();
  } else {
    $klanten = $db->getKlanten();
    if (count($klanten) > 0) {
    ?>
      <h1>Klanten</h1>
      <table>
        <tr>
          <th></th>
        </tr>
        <?php
        foreach ($klanten as $klant) {
        ?>
          <tr>
            <td><a href="?id=<?= $klant['id'] ?>">
                <?= $klant['achternaam'] ?>
                <?= $klant['tussenvoegsel'] ?>,
                <?= $klant['voornaam'] ?>
              </a></td>
          </tr>
        <?php
        }
        ?>
      </table>
    <?php
    } else {
    ?>
      <p>Er zijn geen resulaten gevonden.</p>
  <?php
    }
  }
  ?>
</body>

</html>
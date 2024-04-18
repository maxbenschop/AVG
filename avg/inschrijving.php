<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$fouten = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  include('database.php');

  $voornaam = $_POST['voornaam'];
  $tussenvoegsel = $_POST['tussenvoegsel'];
  $achternaam = $_POST['achternaam'];
  $geboortedatum = $_POST['geboortedatum'];


  if (empty($voornaam)) {
    array_push($fouten, 'U moet een voornaam invullen');
  }
  if (empty($achternaam)) {
    array_push($fouten, 'U moet een achternaam invullen');
  }
  if (empty($geboortedatum)) {
    array_push($fouten, 'U moet een geboortedatum invullen');
  }


  if (!empty($_FILES['foto_paspoort']['name'])) {

    $types = ['image/jpg', 'image/jpeg'];
    if (in_array($_FILES['foto_paspoort']['type'], $types)) {

      if ($_FILES['foto_paspoort']['size'] < 500000) {

        if (getimagesize($_FILES["foto_paspoort"]["tmp_name"]) !== false) {
          $url = 'https://api.api-ninjas.com/v1/facedetect';


          $curl = curl_init($url);

          curl_setopt($curl, CURLOPT_POST, true);
          curl_setopt($curl, CURLOPT_POSTFIELDS, [
            'image' => new CURLFile($_FILES['foto_paspoort']['tmp_name'], $_FILES['foto_paspoort']['type'], $_FILES['foto_paspoort']['name'])
          ]);
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: multipart/form-data',
            'X-Api-Key: *******************'
          ]);

          $response = curl_exec($curl);

          if ($response === false) {
            echo 'cURL error: ' . curl_error($curl);
          } else {
            $responseData = json_decode($response, true);

            if ($responseData === null) {
              echo 'Error decoding JSON response';
            } else {
              $uploadedImage = imagecreatefromstring(file_get_contents($_FILES['foto_paspoort']['tmp_name']));

              $polygonColor = imagecolorallocate($uploadedImage, 255, 0, 0);

              foreach ($responseData as $faceData) {
                $x = $faceData['x'];
                $y = $faceData['y'];
                $width = $faceData['width'];
                $height = $faceData['height'];

                $faceImage = imagecrop($uploadedImage, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);

                for ($i = 0; $i < 10; $i++) {
                  imagefilter($faceImage, IMG_FILTER_PIXELATE, 15, true);
                }

                imagecopy($uploadedImage, $faceImage, $x, $y, 0, 0, $width, $height);

                imagedestroy($faceImage);
              }

              $klantId = $db->insertKlant($voornaam, $tussenvoegsel, $achternaam, $geboortedatum);
              if ($klantId) {
                $filename = 'uploads/foto_' . $klantId . '.jpg';
                imagejpeg($uploadedImage, $filename);

                header('Location: bedankt.php');
                exit;
              } else {
                array_push($fouten, 'Er ging iets mis bij het opslaan van uw gegevens');
              }
            }
          }
        }

        curl_close($curl);
      } else {
        array_push($fouten, 'De foto van uw paspoort mag maximaal 500 kilobyte zijn');
      }
    } else {
      array_push($fouten, 'De foto van uw paspoort moet een .jpg bestand zijn');
    }
  } else {
    array_push($fouten, 'U moet een foto van uw paspoort uploaden');
  }
}
?>

<!DOCTYPE html>
<html>
<title>Inschrijving</title>
<link href="css/style.css" rel="stylesheet" type="text/css" />

<body>
  <?php
  if (count($fouten) > 0) {
  ?>
    <ul class="foutmeldingen">
      <?php
      foreach ($fouten as $fout) {
      ?>
        <li>
          <?= $fout ?>
        </li>
      <?php
      }
      ?>
    </ul>
  <?php
  }
  ?>
  <form method="post" action="#" enctype="multipart/form-data">
    <label for="voornaam">Voornaam:</label>
    <input type="text" id="voornaam" name="voornaam" placeholder="Voornaam" value="<?php if (isset($voornaam)) {
                                                                                      echo ($voornaam);
                                                                                    } ?>">
    <label for="tussenvoegsel">Tussenvoegsel:</label>
    <input type="text" id="tussenvoegsel" name="tussenvoegsel" placeholder="Tussenvoegsel" value="<?php if (isset($tussenvoegsel)) {
                                                                                                    echo ($tussenvoegsel);
                                                                                                  } ?>">
    <label for="achternaam">Achternaam:</label>
    <input type="text" id="achternaam" name="achternaam" placeholder="Achternaam" value="<?php if (isset($achternaam)) {
                                                                                            echo ($achternaam);
                                                                                          } ?>">
    <label for="geboortedatum">Geboortedatum:</label>
    <input type="date" id="geboortedatum" name="geboortedatum" placeholder="Geboortedatum" value="<?php if (isset($geboortedatum)) {
                                                                                                    echo ($geboortedatum);
                                                                                                  } ?>">
    <label for="foto_paspoort">Paspoort:</label>
    <input type="file" id="foto_paspoort" name="foto_paspoort" placeholder="Foto van paspoort">
    <label for="opslaan">Opslaan:</label>
    <input type="submit" id="opslaan" value="Opslaan" />
  </form>

  <?php
  if (isset($imageData)) {
    echo '<img src="data:image/png;base64,' . base64_encode($imageData) . '" />';
  }
  ?>

</body>



</html>
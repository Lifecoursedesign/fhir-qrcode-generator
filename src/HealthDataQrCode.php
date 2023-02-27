<?php

namespace Saitama\QR;

class HealthDataQrCode
{

  /**
   * It takes a string and converts it to a base 10 number
   * 
   * @param id The id of the user you want to get the information of.
   * 
   * @return A string base 10 format of the id.
   */
  private function _getBase10format($id)
  {
    $value = "";
    foreach (str_split($id) as $letter) {
      $difference = ord($letter) - 45;
      if ($difference < 10) {
        $value = $value . "0" . $difference;
      } else {
        $value = $value . $difference;
      }
    }
    return $value;
  }

  /**
   * It takes a string of numbers, converts them to ASCII, and then adds 45 to each ASCII value
   * 
   * @param text The text to be encoded.
   * 
   * @return the base64 encoded text.
   */
  private function _revertText($text)
  {
    $base64text = "";
    for ($x = 0; $x < strlen($text); $x = $x + 2) {
      $ascii = $text[$x] . $text[$x + 1];
      $base64text = $base64text . chr((int)$ascii + 45);
    }
    return $base64text;
  }

  /**
   * It converts the base64 string to a token.
   * 
   * @param base_64_arr This is an array of base64 strings.
   * 
   * @return The decrypted text is being returned.
   */
  public function convertSHCSToToken($base_64_arr)
  {
    $len = count($base_64_arr);
    $decryptedText = "";
    for ($x = 0; $x < $len; $x++) {
      $base10text = explode("shcs://", $base_64_arr[$x])[1];
      if ($len > 1) {
        $base10text = preg_split("shcs:\/\/\d\/\d\/", $base_64_arr[$x])[1];
      }

      $decryptedText = $decryptedText . $this->_revertText($base10text);
    }
    return $decryptedText;
  }

  /**
   * It takes a string and returns a base64 encoded string with the + and / characters replaced with -
   * and _ respectively
   * 
   * @param data The data to be encoded.
   * @param encode Default to true, to encode the data pass.
   * 
   * @return The base64UrlEncode function is returning the base64 encoded string of the data passed in.
   */
  public function base64UrlEncode($data, $encode = true)
  {
    if ($encode) {
      return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    return rtrim(strtr($data, '+/', '-_'), '=');
  }


  /**
   * It generates a QR code from the given data and saves it to the given file path
   * 
   * @param data The data to be encoded in the QR code.
   * @param file_path The path to the file you want to save the QR code to.
   * 
   * @return Nothing.
   */
  public function generateQrCode($data, $file_path)
  {
    exec("qrcode -w 800 -e L -o {$file_path} {$data}");
    chmod($file_path, 0600);
    return;
  }

  /**
   * It takes a private key and converts it to base 10, then splits it into chunks of 1460 characters
   * and generates a QR code for each chunk
   * 
   * @param data The private key in PEM format
   * @param file_path The path where the QR code will be saved.
   * 
   * @return An array of file paths to the generated QR codes.
   */
  public function generatePrivateKeyQRCode($data, $file_path)
  {
    $batchID = uniqid();
    $divider = 1460;
    $base10 = strVal($this->_getBase10format($data));
    $max = ceil(strlen($base10) / $divider);

    $pem_base_10 = [];
    $file_paths = [];
    array_push($pem_base_10, "pem:/" . $base10);
    if ($max > 1) {
      $pem_base_10 = str_split($base10, $divider);
      foreach($pem_base_10 as $key=>&$value) {
        $id = $key + 1;
        $value = "pem:/" . $id . "/" . $max . "/" . $batchID . "/" .$value;
      }
      unset($value);
    }
    $dir_slash = stripos(PHP_OS, 'win') === 0 ? "\\" : "/";
    for ($x = 0; $x < count($pem_base_10); $x++) {
      $qr_path = $file_path . $dir_slash . 'private-key-' . $x . '.png';
      array_push($file_paths, $qr_path);
      $this->generateQrCode($pem_base_10[$x], $qr_path);
    }
    return $file_paths;
  }

  /**
   * It takes a JWE Token and converts it to base 10, then splits it into chunks of 1460 characters
   * and generates a QR code for each chunk
   * 
   * @param data The JWE Token.
   * @param file_path The path where the QR code will be saved.
   * 
   * @return An array of file paths to the generated QR codes.
   */
  public function generateFHIRQRCode($data, $file_path)
  {
    $batchID = uniqid();
    $divider = 1460;
    $base10 = strVal($this->_getBase10format($data));
    $max = ceil(strlen($base10) / $divider);

    $fhir_base_10 = [];
    $file_paths = [];
    array_push($fhir_base_10, "shcs:/" . $base10);
    if ($max > 1) {
      $fhir_base_10 = str_split($base10, $divider);
      foreach($fhir_base_10 as $key=>&$value) {
        $id = $key + 1;
        $value = "shcs:/" . $id . "/" . $max . "/" . $batchID . "/" .$value;
      }
      unset($value);
    }
    $dir_slash = stripos(PHP_OS, 'win') === 0 ? "\\" : "/";

    for ($x = 0; $x < count($fhir_base_10); $x++) {
      $qr_path = $file_path . $dir_slash . 'record-' . $x . '.png';
      array_push($file_paths, str_replace("_temp","",$qr_path));
      $this->generateQrCode($fhir_base_10[$x], $qr_path);
    }
    return $file_paths;
  }
}

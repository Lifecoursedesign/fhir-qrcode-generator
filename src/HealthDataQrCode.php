<?php

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

require dirname(__DIR__) . '/vendor/autoload.php';

class HealthDataQrCode {

  private function _getBase10format($id) {
    $value = "";
    foreach(str_split($id) as $letter) {
      $difference = ord($letter)-45;
      if ($difference < 10){
        $value = $value ."0".$difference;
      }else {
        $value = $value.$difference;
      }
    }
    return $value;
  }
  
  private function _revertText($text){
    $base64text = "";
    for ($x = 0; $x < strlen($text); $x = $x + 2){
     $ascii =$text[$x] . $text[$x+1];
     $base64text = $base64text . chr((int)$ascii+45);
    }
   return $base64text;
  }
  
  public function convertSHCSToToken($base_64_arr) {
    $len = count($base_64_arr);
    $decryptedText = "";
    for($x = 0; $x < $len; $x++){
      $base10text = explode("shcs://",$base_64_arr[$x])[1];
      if ($len > 1){
        $base10text = preg_split("shcs:\/\/\d\/\d\/",$base_64_arr[$x])[1];
      }
  
      $decryptedText = $decryptedText. $this->_revertText($base10text);
    }
     return $decryptedText;
  }

  function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
  }

  public function convertTokenToDecimalArray($id) {
    $divider = 1194;
    $base10 = $this->_getBase10format($id);
    $max = ceil(strlen($base10) / $divider);
    
    $arr = [];
    array_push($arr,"shcs://".$base10);
    if ($max > 1){
      $arr = [];
      for($x = 0; $x <  $max; $x++){
        $start = $x == 0 ? $x * $divider : ($x * $divider) + 1;
        $end = (($x+1) * $divider) > strlen($base10) ?  strlen($base10) :  (($x+1) * $divider);
        array_push($arr,"shcs://".($x+1)."/".$max."/".substr($base10,$start,$end));
      }
    }
    return $arr;
  }

  public function generateQrCode($data, $file_path) {
    $writer = new PngWriter();
    $qrCode = QrCode::create($data)
        ->setEncoding(new Encoding('UTF-8'))
        ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
        ->setSize(200)
        ->setMargin(0)
        ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
        ->setForegroundColor(new Color(0, 0, 0))
        ->setBackgroundColor(new Color(255, 255, 255));
    $result = $writer->write($qrCode);
    $result->saveToFile($file_path);
    return;
  }

  public function generatePrivateKeyQRCode($data, $file_path) {
    $divider = 2000;
    $base10 = $this->_getBase10format($data);
    $max = ceil(strlen($base10) / $divider);
    
    $pem_base_10 = [];
    $file_paths = [];
    array_push($pem_base_10,"pem:/".$base10);
    if ($max > 1){
      $pem_base_10 = [];
      for($x = 0; $x <  $max; $x++){
        $start = $x == 0 ? $x * $divider : ($x * $divider) + 1;
        $end = (($x+1) * $divider) > strlen($base10) ?  strlen($base10) :  (($x+1) * $divider);
        array_push($pem_base_10,"pem:/".($x+1)."/".$max."/".substr($base10,$start,$end));
      }
    }

    for($x = 0; $x < count($pem_base_10); $x++){
      $qr_path = $file_path.'/private-key-'.$x.'.png';
      array_push($file_paths, $qr_path);
      $this->generateQrCode($pem_base_10[$x], $qr_path);
    }
    
    return $file_paths;
  }
}
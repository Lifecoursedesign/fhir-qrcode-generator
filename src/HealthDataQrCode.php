<?php
declare(strict_types = 1);

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

require dirname(__DIR__) . '/vendor/autoload.php';

class HealthDataQrCode {

  private function get_base_10_format($id) {
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
  
  private function revert_text($text){
    $base64text = "";
    for ($x = 0; $x < strlen($text); $x = $x + 2){
     $ascii =$text[$x] . $text[$x+1];
     $base64text = $base64text . chr((int)$ascii+45);
    }
   return $base64text;
  }
  
  public function convert_SHCS_to_token($base64arr) {
    $len = count($base64arr);
    $decryptedText = "";
    for($x = 0; $x < $len; $x++){
      $base10text = explode("shcs://",$base64arr[$x])[1];
      if ($len > 1){
        $base10text = preg_split("shcs:\/\/\d\/\d\/",$base64arr[$x])[1];
      }
  
      $decryptedText = $decryptedText. $this->revert_text($base10text);
    }
     return $decryptedText;
  }
  
  public function convert_token_to_decimal_array($id) {
    $divider = 1194;
    $base10 = $this->get_base_10_format($id);
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

}
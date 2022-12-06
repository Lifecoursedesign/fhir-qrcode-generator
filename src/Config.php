<?php

class Config {
  /**
   * A function that returns the endpoint for our backend.  
   * 
   * @return a String or URL.
  */
  public function getConfig() {
    $development = "http://localhost:3333/api/v1";
    $production = "https://mlink-api.lcd.or.jp/api/v1";
    $testing = "https://api.linkgdm.lanex.co.jp/api/v1";
    return $testing;
  }
}
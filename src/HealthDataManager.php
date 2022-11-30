<?php

require dirname(__DIR__) . '/vendor/autoload.php';

require "HealthDataToken.php";
require "Config.php";

class HealthDataManager {
  private $base_path;
  private $signature_path;
  private $enc_path;
  private $qr_path;
  private $token_instance;
  private $qrcode_instance;
  private $validator_instance;
  private $endpoint;

  /**
   * The constructor function initializes the class variables and creates instances of the other classes
   * 
   * @param storage The folder path to store the files.
   */
  public function __construct($storage) {
    if (!is_object($storage)) {
      throw new Exception('Invalid specified storage');
    }
    if(!property_exists($storage, 'path')) {
      throw new Exception('Storage path is required');
    }
    $config  = new Config();
    $this->endpoint = $config->getConfig();
    $this->token_instance = new HealthDataToken();
    $this->qrcode_instance = new HealthDataQrCode();
    $this->validator_instance = new Validator();
    $this->base_path = $storage->path;
    $this->signature_path = $this->base_path."/signature_key";
    $this->enc_path = $this->base_path."/enc_keys";
    $this->qr_path = $this->base_path."/qr";

    $folder_not_exists = !is_dir($this->base_path);
    if ($folder_not_exists) {
      mkdir($this->signature_path, 0755, true);
      mkdir($this->enc_path, 0755, true);
      mkdir($this->qr_path, 0755, true);
    }
  }

  /**
   * Remove subdirectories and files.
   * 
   * @param folderName The folder to clean up.
   * 
   * @return Nothing
   */
  private function _removeFolder($folderName) {
    if (is_dir($folderName)){
      $folderHandle = opendir($folderName);
      if(!$folderHandle) {
        return;
      }

      while(($file = readdir($folderHandle)) !== false) {
        if ($file != "." && $file != "..") {
          if (!is_dir($folderName."/".$file)) {
              unlink($folderName."/".$file);
          } else {
            $this->_removeFolder($folderName.'/'.$file);
          }     
        }
      }
      closedir($folderHandle);
      rmdir($folderName);
    }
    return;
  }

  /**
   * Generate a dummy JWS private (and public) key pair for testing purpose only to replicate prod behavior.
   * 
   * @return An array of the private and public keys.
   */
  public function simulateJWSKeys() {
    $user_path = $this->base_path."/simulate_jws";
    if(!is_dir($user_path)) {
      mkdir($user_path, 0755, true);
    }
    $private_key_file = $user_path."/private_key.pem";
    $public_key_file = $user_path."/public_key.pem";
    exec("openssl genrsa -out {$private_key_file} 2048");
    exec("openssl rsa -in {$private_key_file} -pubout -out {$public_key_file}");
    $res = array(
      "private_key" => file_get_contents($private_key_file, true),
      "public_key" => file_get_contents($public_key_file, true),
    );

    /**
     * Test JWS Token at https://jwt.io/.
     * 
     * • Select RSA Algorithm from the site and paste the JWS token.
     * • Verify the signature by converting the public key to JWK 
     *   format at https://irrte.ch/jwt-js-decode/pem2jwk.html.
     *   Then copy and paste the JWK into the verify signature box. 
     */
    // $jws_token = $this->token_instance->createJWSToken($keys["private_key"], json_encode(array("data"=>"test")));
    // echo $jws_token;

    return $res;
  }

  /**
   * It sets the private keys issued by the CA.
   * 
   * @param kid Key ID of the key pair for signature
   * @param private_pem Private Key for Signature（PEM format）
   * 
   * @return Nothing.
   */
  public function setSigPrivateKey($kid, $private_pem) {
    try {
      # Validate arguments.
      $validKid = $this->validator_instance->isValidKID($kid);
      $validPem = $this->validator_instance->isValidPrivatePEM($private_pem);
      if (!$validKid) {
        throw new Exception('Invalid kid argument');
      }
      if (!$validPem) {
        throw new Exception('Invalid private pem argument');
      }
     
      $sig_path = $this->signature_path;
      if(!is_dir($sig_path)) {
        mkdir($sig_path, 0755, true);
      } 
      $contents = scandir($sig_path);

      # Validate private key is already registered.
      $private_key_file = $sig_path."/private_key.pem";
      $kid_file = $sig_path."/kid.txt";
      if(count($contents) > 0 && file_exists($private_key_file)) {
        $data = file_get_contents($private_key_file, true);
        if($data === $private_pem) {
         throw new Exception("Private key already exists.");
        }
      } 
      file_put_contents($private_key_file, $private_pem);
      file_put_contents($kid_file, $kid);
      return;
    } catch (Exception $error) {
      throw new Exception($error->getMessage());
    }
  }

  /**
   * Get signature private key that was set in setSigPrivateKey.
   * 
   * @return An array of the kid and private key.
   */
  public function getSigPrivateKey() {
    try {
      $result = array();
      $sig_path = $this->signature_path;
      if(!is_dir($sig_path)) {
        return $result;
      }

      $scanDIR = scandir($sig_path);
      if(count($scanDIR) > 0) {
        if(file_exists($sig_path."/kid.txt")) {
          $result["kid"] = file_get_contents($sig_path."/kid.txt", true);
        }
        if(file_exists($sig_path."/private_key.pem")) {
          $result["private_key"] = file_get_contents($sig_path."/private_key.pem", true);
        }
      }
      return $result;
    } catch (Exception $error) {
      throw new Exception($error->getMessage());
    }
  }

  /**
   * This function deletes the signature private key. 
   * 
   * @return Nothing.
   */
  public function deleteSigPrivateKey() {
    try {
      $this->_removeFolder($this->signature_path);
      return;
    } catch (Exception $error) {
      throw new Exception($error->getMessage());
    }
  }

  /**
   * This function creates a new encryption key pair for a user
   * 
   * @param user_id The user id of the patient
   * 
   * @return Nothing.
   */
  public function createEncKeyPair($user_id) {
    try {
      if (!$this->validator_instance->isValidUserID($user_id)) {
        throw new Exception('Invalid patient id');
      }
      $user_path = $this->enc_path."/".$user_id;
      if(!is_dir($user_path)) {
        mkdir($user_path, 0755, true);
      }
      $private_key_file = $user_path."/private_key.pem";
      $public_key_file = $user_path."/public_key.pem";
      exec("openssl genrsa -out {$private_key_file} 2048");
      exec("openssl rsa -in {$private_key_file} -pubout -out {$public_key_file}");
      return;
    } catch (Exception $e) {
      throw new Exception('Error Saving Encryption Key Pair');
    }
  }

  /**
   * It takes a user id, fetches the private key from the database, compresses it, encodes it, and then
   * generates a QR code
   * 
   * @param user_id The user id of the patient
   * 
   * @return the path of the generated QR code.
   */
  public function generateEncPrivateKeyQr($user_id) {
    if (!$this->validator_instance->isValidUserID($user_id)) {
      throw new Exception('Invalid patient id');
    }
    try {
      # Get Private Key
      $enc_user_path = $this->enc_path."/".$user_id;
      $pk_path = $enc_user_path."/private_key.pem";
      if(!is_dir($enc_user_path)){
        throw new Exception("No key pairs found for this user.");
      } 
      if(!file_exists($pk_path)){
        throw new Exception("No private key found for this user.");
      }
      $data = file_get_contents($pk_path, true);
      $compressed_pk_data = gzdeflate($data);
      $base64URLPK = $this->qrcode_instance->base64UrlEncode($compressed_pk_data);
      
      # Generate QR Code
      $qr_user_path = $this->qr_path."/keys"."/".$user_id;
      if(!is_dir($qr_user_path)) {
        mkdir($qr_user_path, 0755, true);
      }
      $result = $this->qrcode_instance->generatePrivateKeyQRCode($compressed_pk_data, $qr_user_path);
      return $result;
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  /**
   * It fetches the private and public keys for a user from the database and returns them as an array
   * 
   * @param user_id The user id of the user you want to get the keys for.
   * 
   * @return An array of the private and public keys.
   */
  public function getEncKeyPair($user_id) {
    if (!$this->validator_instance->isValidUserID($user_id)) {
      throw new Exception('Invalid patient id');
    }
    try {
      $keys = array();
      $user_path = $this->enc_path."/".$user_id;
      if(is_dir($user_path)){
        if ($dh = opendir($user_path)){
          while (($file = readdir($dh)) !== false){
            if($file === "private_key.pem") {
              $content = file_get_contents( $user_path."/".$file, true);
              $keys["private_key"] = $content;
            }
            if($file === "public_key.pem") {
              $content = file_get_contents( $user_path."/".$file, true);
              $keys["public_key"] = $content;
            }
          }
          closedir($dh);
        }
      }
      return $keys;
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  /**
   * This function deletes the encryption key pair for a user
   * 
   * @param user_id The user's ID in the EMR
   * 
   * @return Nothing.
   */
  public function deleteEncKeyPair($user_id) {
    try {
      if (!$this->validator_instance->isValidUserID($user_id)) {
        throw new Exception('Invalid patient id');
      }
      $folderName = $this->enc_path."/".$user_id;
      $this->_removeFolder($folderName);
      return;
    } catch (Exception $e) {
      $response = $e->getResponse();
      $jsonBody = (string) $response->getBody();
      $parseError = json_decode($jsonBody);
      throw new Exception($parseError->message);
    }
  }
}

# For testing purposes in PHP, you can change the path to your current environment.
$storage=new stdClass;
$storage->path="/Users/louiejohnseno/Desktop/qr_lib";

$manager = new HealthDataManager($storage);
$manager->generateEncPrivateKeyQr("emr-1");

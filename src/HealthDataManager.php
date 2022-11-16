<?php

define("HOSPITALS", array(
  "SAITAMA" => 1,
  "AIWA" => 2,
  "KEIAI" => 3,
  "SETO" => 4,
));

require dirname(__DIR__) . '/vendor/autoload.php';

require "HealthDataToken.php";
require "HealthDataQrCode.php";
require "Validator.php";
require "config/index.php";

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;


class HealthDataManager {

  private $storage_path;
  private $token_instance;
  private $qrcode_instance;
  private $validator_instance;
  private $client;
  private $endpoint; 
  private $institution;

  public function __construct( $selected_institution) {
    global $fhir_qr_config;
    if(((1 <= $selected_institution) && ($selected_institution <= 4))) {
      $this->token_instance = new HealthDataToken();
      $this->qrcode_instance = new HealthDataQrCode();
      $this->validator_instance = new Validator();
      $this->client = new Client();
      $this->endpoint = $fhir_qr_config["API_URL"];
      $this->institution = $selected_institution;
    } else {
      throw new Exception('Invalid hospital id');
    }
   
  }

  private function _generateJWSKeys() {
      # Generate a new JWS private (and public) key pair.
      $config = array(
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA
      );
      $keys = openssl_pkey_new($config);

      # Store Patient Private Key.
      openssl_pkey_export($keys, $private_key);
    
      # Store Patient Public Key.
      $public_key_detail = openssl_pkey_get_details($keys);
      $public_key = $public_key_detail["key"];

      return array(
        'private_key' => $private_key,
        'public_key' => $public_key
      );
  }

  private function _generateJWEKeys() {
     # Generate a new JWE private (and public) key pair.
    $config = array(
      "private_key_bits" => 2048,
      "private_key_type" => OPENSSL_KEYTYPE_RSA
    );
    $keys = openssl_pkey_new($config);

     # Store Patient Private Key.
    openssl_pkey_export($keys, $private_key);

    # Store Patient Public Key.
    $public_key_detail = openssl_pkey_get_details($keys);
    $public_key = $public_key_detail["key"];

    return array(
      'private_key' => $private_key,
      'public_key' => $public_key
    );
  }

  private function _setStorageDirectory($user_id) {
    $dir_path = dirname(__FILE__).'/patient_qr'."/".$this->institution."/".$user_id;
    $folder_not_exists = !file_exists($dir_path);
    if ($folder_not_exists) {
      mkdir($dir_path, 0777, true);
    }
    $this->storage_path = $dir_path;
  }

  private function _fetchJWEKeys($user_id, $request_type) {
    try {
      $request = $this->client->request('POST', $this->endpoint . "/qr-library/get-key-pair", [
        'form_params' => [
            'emr_patient_id' => $user_id,
            'jose_type' => 'JWE',
            'institution_id' => $this->institution
        ]
      ]);
      $response = $request->getBody();
      $result = json_decode($response);
      return $result->data->pem_list;
    } catch (ServerException $e) {
      $response = $e->getResponse();
      $jsonBody = (string) $response->getBody();
      $parseError = json_decode($jsonBody);
      $not_exists = str_contains(strtolower($parseError->message), 'row not found');
      if($not_exists && $request_type !=='generatePrivateKey') {
        return [];
      } else {
        if($not_exists) {
          throw new Exception('Keys not found');
        } else {
          # Throw other internal server error message
          throw new Exception($parseError->message);
        }
      }
    }
  }

  public function createEncKeyPair($user_id) {
    try {
      if(!$this->validator_instance->isValidUserID($user_id)) {
        throw new Exception('Invalid patient id');
      }
  
      // Generate Encryption Key Pairs
      $keys = $this->_generateJWEKeys();
  
      $request = $this->client->request('POST', $this->endpoint . "/qr-library/key-pair", [
        'form_params' => [
            'emr_patient_id' => $user_id,
            'pem_list' => $keys,
            'jose_type' => 'JWE',
            'institution_id' => $this->institution
        ]
      ]);
      return; 
    } catch (ServerException $e) {
      throw new Exception('Error Saving Encryption Key Pair');
    } 
  } 

  public function generateEncPrivateKeyQr($user_id) {
    if(!$this->validator_instance->isValidUserID($user_id)) {
      throw new Exception('Invalid patient id');
    }
    try {
      $data = $this->_fetchJWEKeys($user_id, 'generatePrivateKey');
      $private_key = $data[0]->private_key;
      $data = json_encode(array(
        "key" => $private_key,
        "emp_patient_id" => $user_id
      ));

      $this->_setStorageDirectory($user_id);
      $compressed_pk_data = gzdeflate($data);
      $base64URLPK = $this->qrcode_instance->base64UrlEncode($compressed_pk_data);
      $result = $this->qrcode_instance->generatePrivateKeyQRCode($base64URLPK, $this->storage_path);
      return $result;
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
      
    } 
  }

  public function getEncKeyPair($user_id) {
    if(!$this->validator_instance->isValidUserID($user_id)) {
      throw new Exception('Invalid patient id');
    }
    try {
      $data = $this->_fetchJWEKeys($user_id, 'getEncKeyPair');
      if(count($data) > 0) {
        $private_key = $data[0]->private_key;
        $public_key = $data[1]->public_key;
        return array(
          "private_key" => $private_key,
          "public_key" => $public_key
        );
      }
      return [];
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    } 
  }

  public function deleteEncKeyPair($user_id) {
    try {
      $request = $this->client->request('POST', $this->endpoint . "/qr-library/del-key-pair", [
        'form_params' => [
            'emr_patient_id' => $user_id,
            'jose_type' => 'JWE',
            'institution_id' => $this->institution
        ]
      ]);
      return; 
    } catch (Exception $e) {
      $response = $e->getResponse();
      $jsonBody = (string) $response->getBody();
      $parseError = json_decode($jsonBody);
      throw new Exception($parseError->message);
    } 
  }
}
// $manager = new HealthDataManager(HOSPITALS["SAITAMA"]);
// $manager->createEncKeyPair("EMR-101");
<?php

define("HOSPITALS", array(
  "SAITAMA" => 1,
  "AIWA" => 2,
  "KEIAI" => 3,
  "SETO" => 4,
));

require dirname(__DIR__) . '/vendor/autoload.php';

require "HealthDataToken.php";
require "Config.php";

class HealthDataManager {

  private $storage_path;
  private $token_instance;
  private $qrcode_instance;
  private $validator_instance;
  private $endpoint;
  private $institution;

  /**
   * The constructor function initializes the class variables and creates instances of the other classes
   * 
   * @param selected_institution The hospital id of the hospital you want to use.
   */
  public function __construct($selected_institution) {
    if (((1 <= $selected_institution) && ($selected_institution <= 4))) {
      $config  = new Config();
      $this->token_instance = new HealthDataToken();
      $this->qrcode_instance = new HealthDataQrCode();
      $this->validator_instance = new Validator();
      $this->endpoint = $config->getConfig();
      $this->institution = $selected_institution;
      echo $this->endpoint;
    } else {
      throw new Exception('Invalid hospital id');
    }
  }

  /**
   * The function that calls the post requests.
   * 
   * @param path The path to request.
   * @param postParams Array of post request.
   */
  private function _libraryPostRequest($path, $postParams) {
    $curlHandle = curl_init();
    curl_setopt($curlHandle, CURLOPT_URL, $this->endpoint."/qr-library".$path);
    curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $postParams);
    curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
    # Add on windows to treat request as POST
    if(stripos(PHP_OS, 'win') === 0) {
      curl_setopt($curlHandle,CURLOPT_POST, true);
    }
    

    $curlResponse = curl_exec($curlHandle);

    if($curlResponse=== false){
        throw new Exception('Curl error: ' . curl_error($curlHandle));
        echo 'Curl error: ' . curl_error($curlHandle);
    } else {
        echo "Success";
    }
    curl_close($curlHandle);
    return $curlResponse;
  }

  /**
   * The function that calls the get requests.
   * 
   * @param path The path to request.
   */

  private function _libraryGetRequest($path) {
    $curlHandle = curl_init();
    curl_setopt($curlHandle, CURLOPT_URL, $this->endpoint."/qr-library".$path);
    curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curlHandle, CURLOPT_HTTPGET, true);  

    $curlResponse = curl_exec($curlHandle);

    if($curlResponse=== false){
        throw new Exception('Curl error: ' . curl_error($curlHandle));
        echo 'Curl error: ' . curl_error($curlHandle);
    } else {
        echo "Success";
    }
    curl_close($curlHandle);
    return $curlResponse;
  }

  /**
   * It creates a directory for the user if it doesn't exist
   * 
   * @param path The path to create.
   */
  private function _setStorageDirectory($path) {
    $dir_path = $path;
    $folder_not_exists = !file_exists($dir_path);
    if ($folder_not_exists) {
      mkdir($dir_path, 0777, true);
    }
    $this->storage_path = $dir_path;
  }

  /**
   * It fetches the public and private keys from the server
   * 
   * @param user_id The user's ID
   * @param request_type This is the type of request you are making. It can be either
   * 'generatePrivateKey' or 'getPublicKey'.
   * 
   * @return an array of keys.
   */
  private function _fetchJWEKeys($user_id, $request_type) {
    try {
      $postParameter = array(
        'emr_patient_id' => $user_id,
        'jose_type' => 'JWE',
        'institution_id' => $this->institution
      );
      $request = $this->_libraryPostRequest("/get-key-pair", $postParameter);
      $result = json_decode($request);
      $not_exists = str_contains(strtolower($result->message), 'row not found');
      if($result->statusCode == 200) {
        return $result->data->pem_list;
      } else {
        if($not_exists && $request_type !== 'generatePrivateKey') {
          return [];
        } else {
          if ($not_exists) {
            throw new Exception('Keys not found');
          } else {
            # Throw other internal server error message
            throw new Exception($parseError->message);
          }
        }
      }
    } catch (Exeption $e) {
      throw new Exception('Something went wrong when fetching keys.');
    }
  }

  /**
   * Generate a dummy JWS private (and public) key pair for testing purpose only to replicate prod behavior.
   * 
   * @return An array of the private and public keys.
   */
  public function simulateJWSKeys() {
    $request = $this->_libraryGetRequest("/gen-keys");
    $keys = json_decode($request)->data;
    $res= array(
      "private_key" => $keys->privateKey,
      "public_key" => $keys->publicKey
    );

    $this->_setStorageDirectory(dirname(__FILE__) . '/test_jws' . "/" . $this->institution);
    file_put_contents($this->storage_path . '/test-private-key.pem', $res["private_key"]);
    file_put_contents($this->storage_path . '/test-public-key.pem', $res["public_key"]);

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
      $validKid = $this->validator_instance->isValidKID($kid);
      $validPem = $this->validator_instance->isValidPEM($private_pem);
      if (!$validKid) {
        throw new Exception('Invalid kid argument');
      }
      if (!$validPem) {
        throw new Exception('Invalid private pem argument');
      }
      $postParameter = array(
        'kid' => $kid,
        'private_key' =>  $private_pem
      );
      $result = $this->_libraryPostRequest("/sig-private-key", $postParameter);
      $resp =  json_decode($result);
      if($resp->statusCode != 200) {
        throw new Exception($resp->message);
      }
      return;
    } catch (Exception $error) {
      throw new Exception($error->getMessage());
    }
  }

  /**
   * Get signature private key that was set in setSigPrivateKey.
   * 
   * @param kid Key ID of the key pair for signature
   * 
   * @return An array of the kid and private key.
   */
  public function getSigPrivateKey($kid) {
    try {
      $validKid = $this->validator_instance->isValidKID($kid);
      if (!$validKid) {
        throw new Exception('Invalid kid argument');
      }
      $postParameter = array(
        'kid' => $kid
      );
      $request = $this->_libraryPostRequest("/get-private-key", $postParameter);
      $resp = json_decode($request);
      if($resp->statusCode == 200) {
        $pair_list = $resp->data;

        return count($pair_list) > 0 ? array(
          "kid" => $pair_list[0],
          "private_key" => $pair_list[1]
        ) : [];
      }
      return [];
    } catch (Exception $error) {
      throw new Exception($error->getMessage());
    }
  }

  /**
   * This function deletes the signature private key.
   * 
   * @param kid  Key ID of the key pair for signature
   * 
   * @return Nothing.
   */
  public function deleteSigPrivateKey($kid) {
    try {
      $validKid = $this->validator_instance->isValidKID($kid);
      if (!$validKid) {
        throw new Exception('Invalid kid argument');
      }
      $postParameter = array(
        'kid' => $kid
      );
      $request = $this->_libraryPostRequest("/del-private-key", $postParameter);
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
      $postParameter = array(
        'emr_patient_id' => $user_id,
        'jose_type' => 'JWE',
        'institution_id' => $this->institution
      );
      $this->_libraryPostRequest("/key-pair", $postParameter);
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
      $data = $this->_fetchJWEKeys($user_id, 'generatePrivateKey');
      $private_key = $data[0]->private_key;
      $data = json_encode(array(
        "key" => $private_key,
        "emp_patient_id" => $user_id
      ));

      $this->_setStorageDirectory(dirname(__FILE__) . '/patient_qr' . "/" . $this->institution . "/" . $user_id);
      $compressed_pk_data = gzdeflate($data);
      $base64URLPK = $this->qrcode_instance->base64UrlEncode($compressed_pk_data);
      $result = $this->qrcode_instance->generatePrivateKeyQRCode($base64URLPK, $this->storage_path);
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
      $data = $this->_fetchJWEKeys($user_id, 'getEncKeyPair');
      if (count($data) > 0) {
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

  /**
   * This function deletes the encryption key pair for a user
   * 
   * @param user_id The user's ID in the EMR
   * 
   * @return Nothing.
   */
  public function deleteEncKeyPair($user_id) {
    try {
      $postParameter = array(
        'emr_patient_id' => $user_id,
        'jose_type' => 'JWE',
        'institution_id' => $this->institution
      );
      $this->_libraryPostRequest("/del-key-pair", $postParameter);
      return;
    } catch (Exception $e) {
      $response = $e->getResponse();
      $jsonBody = (string) $response->getBody();
      $parseError = json_decode($jsonBody);
      throw new Exception($parseError->message);
    }
  }
}

$manager = new HealthDataManager(HOSPITALS["SAITAMA"]);
$manager->generateEncPrivateKeyQr("LS-106");
// $res = $manager->simulateJWSKeys();
// $res = $manager->deleteSigPrivateKey("kid-12");
// print_r($res);
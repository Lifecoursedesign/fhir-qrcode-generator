<?php

use Gamegos\JWS\Algorithm\RSA_SSA_PKCSv15;

use Sop\CryptoEncoding\PEM;
use Sop\JWX\JWK\RSA\RSAPublicKeyJWK;
use Sop\JWX\JWE\KeyAlgorithm\RSAESOAEPAlgorithm;
use Sop\JWX\JWE\EncryptionAlgorithm\A128CBCHS256Algorithm;

use Sop\JWX\JWS\Algorithm\HS256Algorithm;
use Sop\JWX\JWT\Claim\AudienceClaim;
use Sop\JWX\JWT\Claim\Claim;
use Sop\JWX\JWT\Claim\ExpirationTimeClaim;
use Sop\JWX\JWT\Claim\IssuedAtClaim;
use Sop\JWX\JWT\Claim\IssuerClaim;
use Sop\JWX\JWT\Claim\JWTIDClaim;
use Sop\JWX\JWT\Claim\NotBeforeClaim;
use Sop\JWX\JWT\Claim\SubjectClaim;
use Sop\JWX\JWT\Claims;
use Sop\JWX\JWT\Header\Header;
use Sop\JWX\JWT\Parameter\JWTParameter;
use Sop\JWX\JWT\JWT;
use Sop\JWX\Util\UUIDv4;

class HealthDataToken {

  private $qrcode_instance;

  /**
   * The constructor function initializes the class variables and creates instances of the other classes
   */
  public function __construct() {
    $this->qrcode_instance = new HealthDataQrCode();
  }

  /**
   * It creates a JWT token with the data provided and the user_id provided
   * 
   * @param data The data to be encrypted
   * @param user_id The user's ID in the database.
   * 
   * @return A JWT token
   */
  public function createPEMToken($data, $user_id) {
    $claims = new Claims(
      new IssuerClaim('Saitama QR Code Generator'),
      new SubjectClaim('Private Key'),
      new AudienceClaim('Patient:' . $user_id),
      IssuedAtClaim::now(),
      NotBeforeClaim::now(),
      new JWTIDClaim(UUIDv4::createRandom()->canonical()),
      new Claim('data', $data)
    );
    $jwt = JWT::signedFromClaims($claims, new HS256Algorithm('secret'), new Header(new JWTParameter('zip', 'DEF')));
    return $jwt;
  }

  /**
   * It creates a JWS token with the data provided and its private key for signature
   * 
   * @param private_key The private key to sign the token.
   * @param content The token payload or custom claims.
   * 
   * @return A JWS token
   */
  function createJWSToken($private_key, $content) {
    
    # Compressed Payload
    $compressed = gzdeflate($content);
    $payload_part = $this->qrcode_instance->base64UrlEncode($compressed);

    # JWS Header
    $header = array(
      'alg' => 'RS256',
      'zip' => 'DEF',
      'kid' => 'dummy'
    );
    $header_part = $this->qrcode_instance->base64UrlEncode(json_encode($header));

    # Create Signature
    $rsa = new RSA_SSA_PKCSv15(OPENSSL_ALGO_SHA256);
    $header_payload_part = $header_part . '.' . $payload_part;
    $sig = $rsa->sign($private_key, $header_payload_part);
    $sig_part = $this->qrcode_instance->base64UrlEncode($sig);

    // Generate JWS Token
    $token = $header_payload_part . '.' . $sig_part;
    return $token;
  }

  /**
   * It creates a JWE token with the data provided and its public key for encryption.
   * 
   * @param public_key The public key to encrypt the token.
   * @param content The token payload or custom claims.
   * 
   * @return A JWE token
   */
  function createJWEToken($public_key, $content) {
    $claims = new Claims(
      IssuedAtClaim::now(),
      NotBeforeClaim::now(),
      new JWTIDClaim(UUIDv4::createRandom()->canonical()),
      new Claim('data', $content)
    );

    # Load RSA public key
    // $jwk = RSAPublicKeyJWK::fromPEM($public_key);
    $jwk = RSAPublicKeyJWK::fromPEM(
      PEM::fromString($public_key));
    $key_algo = RSAESOAEPAlgorithm::fromPublicKey($jwk);
    $enc_algo = new A128CBCHS256Algorithm();

    # Create an encrypted JWT token
    $jwt = JWT::encryptedFromClaims($claims, $key_algo, $enc_algo);
    return $jwt->token();
  }
}

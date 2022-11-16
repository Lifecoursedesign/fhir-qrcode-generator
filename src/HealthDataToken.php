<?php

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

  public function createPEMToken($data, $user_id) {
    $claims = new Claims(
      new IssuerClaim('Saitama QR Code Generator'),
      new SubjectClaim('Private Key'),
      new AudienceClaim('Patient:'.$user_id),
      IssuedAtClaim::now(),
      NotBeforeClaim::now(),
      new JWTIDClaim(UUIDv4::createRandom()->canonical()),
      new Claim('data', $data));
      $jwt = JWT::signedFromClaims($claims, new HS256Algorithm('secret'), new Header(new JWTParameter('zip', 'DEF')));
      return $jwt;
  }
}
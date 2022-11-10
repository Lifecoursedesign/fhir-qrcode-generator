<?php
/**
 * Compose JWT claims and produce a token signed with HMAC using SHA-256.
 *
 * php jws-create.php
 */

declare(strict_types = 1);

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
use Sop\JWX\JWT\JWT;
use Sop\JWX\Util\UUIDv4;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

require dirname(__DIR__) . '/vendor/autoload.php';

class IdToken {
  public $signedJWT;

  function __construct(Claims $claims) {
    $this->signedJWT = JWT::signedFromClaims($claims, new HS256Algorithm('secret'));
  }

  function getSignedJWT() {
    return $this->signedJWT;
  }
}

$claims = new Claims(
  new IssuerClaim('John Doe'),
  new SubjectClaim('Jane Doe'),
  new AudienceClaim('acme-client'),
  IssuedAtClaim::now(),
  NotBeforeClaim::now(),
  ExpirationTimeClaim::fromString('now + 30 minutes'),
  new JWTIDClaim(UUIDv4::createRandom()->canonical()),
  new Claim('custom claim', ['any', 'values']));
$token_instance = new IdToken($claims);
$signedToken = $token_instance->getSignedJWT();


$test = 'eyJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJKb2huIERvZSIsInN1YiI6IkphbmUgRG9lIiwiYXVkIjpbImFjbWUtY2xpZW50Il0sImlhdCI6MTY2ODA0MDQwMywibmJmIjoxNjY4MDQwNDAzLCJleHAiOjE2NjgwNDIyMDMsImp0aSI6ImJhNGY3MjM0LTY5ODAtNGRhMC05MDJjLWU1MDVlODNjOGI1NiIsImN1c3RvbSBjbGFpbSI6WyJhbnkiLCJ2YWx1ZXMiXX0.sZlo8AaYquWjprcKiljgcWLtb-TAnYq8gqLFNkk1gK4';
$writer = new PngWriter();
$qrCode = QrCode::create($test)
    ->setEncoding(new Encoding('UTF-8'))
    ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
    ->setSize(250)
    ->setForegroundColor(new Color(0, 0, 0))
    ->setBackgroundColor(new Color(255, 255, 255));

$result = $writer->write($qrCode);
header('Content-Type: '.$result->getMimeType());
$result->saveToFile(__DIR__.'/qrcode.png');
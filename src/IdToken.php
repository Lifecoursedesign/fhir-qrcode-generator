<?php

require './JOSE/JWT.php';

class IdToken {
  var $jwt;

  function __construct($claims = array()) {
      $this->jwt = new JOSE_JWT($claims);
  }

  function toString() {
    return $this->jwt->toString();
  }
}

$id_token = new IdToken(array(
  'iss' => 'https://gree.net',
  'aud' => 'greeapp_12345',
  'sub' => 'greeuser_12345',
  'iat' => time(),
  'exp' => time() + 1 * 60 * 60
));

echo $id_token->toString();
<?php

class Validator {

  /**
   * > This function returns true if the user_id is a string that is less than or equal to 255 characters
   * and greater than 0 characters
   * 
   * @param user_id The user's ID.
   * 
   * @return A boolean value.
   */
  public function isValidUserID($user_id) {
    return is_string($user_id) && strlen($user_id) <= 255 and strlen($user_id) > 0;
  }

  public function isValidKID($kid) {
    return is_string($kid) && strlen($kid) > 0;
  }

  public function isValidPEM($pem_file){
    $regex = '/^(-----BEGIN PUBLIC KEY-----(\n|\r|\r\n)([0-9a-zA-Z\+\/=]{64}(\n|\r|\r\n))*([0-9a-zA-Z\+\/=]{1,63}(\n|\r|\r\n))?-----END PUBLIC KEY-----)|(-----BEGIN PRIVATE KEY-----(\n|\r|\r\n)([0-9a-zA-Z\+\/=]{64}(\n|\r|\r\n))*([0-9a-zA-Z\+\/=]{1,63}(\n|\r|\r\n))?-----END PRIVATE KEY-----)$/';
    return is_string($pem_file) && preg_match($regex, $pem_file);
  }
}

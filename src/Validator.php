<?php

class Validator
{

  /**
   * > This function returns true if the user_id is a string that is less than or equal to 255 characters
   * and greater than 0 characters
   * 
   * @param user_id The user's ID.
   * 
   * @return A boolean value.
   */
  public function isValidUserID($user_id)
  {
    return is_string($user_id) && strlen($user_id) <= 255 and strlen($user_id) > 0;
  }

  /**
   * > This function returns true if the kid is a string that is greater than 0 characters.
   * 
   * @param kid Key ID of the key pair for signature.
   * 
   * @return A boolean value.
   */
  public function isValidKID($kid)
  {
    return is_string($kid) && strlen($kid) > 0;
  }

  /**
   * > This function returns true if the pem_file is a string and in a valid pem file format.
   * 
   * @param pem_file Private Key for Signature（PEM format）
   * 
   * @return A boolean value.
   */
  public function isValidPrivatePEM($pem_file)
  {
    $regex = '/^(-+BEGIN[a-zA-Z ]*-+(\n|\r|\r\n)([0-9a-zA-Z\+\/=]{64}(\n|\r|\r\n))*([0-9a-zA-Z\+\/=]{1,63}(\n|\r|\r\n))?-+END[a-zA-Z ]*-+)$/';
    return is_string($pem_file) && preg_match($regex, $pem_file);
  }
}

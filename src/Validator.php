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
}

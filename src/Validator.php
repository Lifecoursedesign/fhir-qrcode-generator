<?php

class Validator {

  public function isValidUserID($user_id) {
    return is_string($user_id) && strlen($user_id) <=255 and strlen($user_id) > 0;
  }
}

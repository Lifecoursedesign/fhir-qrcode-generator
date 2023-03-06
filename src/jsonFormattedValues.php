<?php

namespace Saitama\QR;

// class JsonFormattedValues
// {

// 	public $institution_id;
// 	public $department_id;
// 	public $doctor_name;
// 	public $medical_card_id;
// 	public $obs_consult_date;
// 	public $obs_next_otp_date;
// 	public $obs_weight;
// 	public $exam_egfr;
// 	public $exam_cre;
// 	public $obs_bp_contraction;
// 	public $obs_bp_dilation;
// 	public $patient_name_kana;
// 	public $patient_name_furi;
// 	public $patient_occupation;
// 	public $patient_marital_status;

// 	/**
// 	 ** The constructor function initializes the class variables and creates instances of the other classes
// 	 */
// 	public function __construct()
// 	{
// 		$this->institution_id = null;
// 		$this->patient_name_kana = null;
// 		$this->patient_name_furi = null;
// 		$this->department_id = null;
// 		$this->patient_marital_status = null;
// 		$this->patient_occupation = null;
// 		$this->doctor_name = null;
// 		$this->medical_card_id = null;
// 		$this->obs_consult_date = null;
// 		$this->obs_next_otp_date = null;
// 		$this->obs_weight = null;
// 		$this->exam_egfr = null;
// 		$this->exam_cre = null;
// 		$this->obs_bp_contraction = null;
// 		$this->obs_bp_dilation = null;
// 	}
// }
class JsonFormattedValues implements \ArrayAccess {

    /**
     * Data
     *
     * @var array
     * @access private
     */
    private $data;
    
    public function toArray(){
        return json_encode($this->data);
        // return json_decode(
        //     json_encode($this->data),
        //     true
        // );
    }
    /**
     * Get a data by key
     *
     * @param string The key data to retrieve
     * @access public
     */
    public function &__get ($key) {
        return $this->data[$key];
    }

    /**
     * Assigns a value to the specified data
     * 
     * @param string The data key to assign the value to
     * @param mixed  The value to set
     * @access public 
     */
    public function __set($key,$value) {
        $this->data[$key] = $value;
    }

    /**
     * Whether or not an data exists by key
     *
     * @param string An data key to check for
     * @access public
     * @return boolean
     * @abstracting ArrayAccess
     */
    public function __isset ($key) {
        return isset($this->data[$key]);
    }

    /**
     * Unsets an data by key
     *
     * @param string The key to unset
     * @access public
     */
    public function __unset($key) {
        unset($this->data[$key]);
    }

    /**
     * Assigns a value to the specified offset
     *
     * @param string The offset to assign the value to
     * @param mixed  The value to set
     * @access public
     * @abstracting ArrayAccess
     */
    public function offsetSet($offset,$value) {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
     * Whether or not an offset exists
     *
     * @param string An offset to check for
     * @access public
     * @return boolean
     * @abstracting ArrayAccess
     */
    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }

    /**
     * Unsets an offset
     *
     * @param string The offset to unset
     * @access public
     * @abstracting ArrayAccess
     */
    public function offsetUnset($offset) {
        if ($this->offsetExists($offset)) {
            unset($this->data[$offset]);
        }
    }

    /**
     * Returns the value at specified offset
     *
     * @param string The offset to retrieve
     * @access public
     * @return mixed
     * @abstracting ArrayAccess
     */
    public function offsetGet($offset) {
        return $this->offsetExists($offset) ? $this->data[$offset] : null;
    }

}
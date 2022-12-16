<?php
namespace Saitama\QR;
class jsonFormattedValues
{

	public $institution_id;
	public $department_id;
	public $doctor_name;
	public $medical_card_id;
	public $obs_consult_date;
	public $obs_next_otp_date;
	public $obs_weight;
	public $exam_egfr;
	public $exam_cre;
	public $obs_bp_contraction;
	public $obs_bp_dilation;

    /**
     ** The constructor function initializes the class variables and creates instances of the other classes
    */
  public function __construct()
  {
    $this->institution_id = null;
    $this->department_id = null;
    $this->doctor_name = null;
    $this->medical_card_id = null;
	$this->obs_consult_date = null;
	$this->obs_next_otp_date = null;
	$this->obs_weight = null;
	$this->exam_egfr = null;
	$this->exam_cre = null;
	$this->obs_bp_contraction = null;
	$this->obs_bp_dilation = null;

  }
}

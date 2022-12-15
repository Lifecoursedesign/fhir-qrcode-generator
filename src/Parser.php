<?php
require_once __DIR__ . '/../vendor/autoload.php';
class formattedValues {
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
}

function getData($type,$resource,$obj){
	switch ($type){
		case "Patient":
			$obj->medical_card_id = " ".$resource->getId();
			break;
		case "Organization": 
			$key = $resource->getIdentifier()[0]->getValue()."_id" ;

			if ($key === "institution_id"){
				// $insts = array("埼玉医大総合医療センター"=>1, "愛和病院"=>2, "恵愛病院"=>3, "瀬戸病院"=>4);
				$institution_id = array("Saitama Medical University General Medical Center"=>1, "Aiwa Hospital"=>2, "Keiai Hospital"=>3, "Seto Hospital"=>4);
				$text = $resource->getIdentifier()[0]->getType()->getText()."";
				$id = null;
				foreach($institution_id as $arrKey => $val) {
					if (stripos($arrKey,$text) !== false){
						$id = $val;
						break;
					}
				}
				$obj->$key =  $id;
			}else {
				// $department = array("産科"=>1, "内科"=>2, "栄養指導"=>3, "糖尿病内科"=>4);
				$department_id = array("Obstetrics"=>1, "Internal Medicine"=>2, "Nutritional Guidance"=>3, "Diabetes Internal Medicine"=>4);
				$text = $resource->getIdentifier()[0]->getType()->getText()."";
				$id = null;
				foreach($department_id as $arrKey => $val) {
					if (stripos($arrKey,$text) !== false){
						$id = $val;
						break;
					}
				}
				$obj->$key =  $id;
			}
			break;
		case "Practitioner": 
			$key = "doctor_name";
			if ($resource->getName()){
				$givenNames = $resource->getName()[0]->getGiven();
				$givenName = "";
				foreach ($givenNames as $v) {
					$givenName = $givenName." ".$v->getValue();
				}
				$obj->doctor_name = " ".($resource->getName()[0]->getFamily()->getValue()).$givenName;
			}
			
			break;
		case "Encounter":
			$obj->obs_consult_date = " ".$resource->getPeriod()->getStart()->getValue();
			$obj->obs_next_otp_date = "".$resource->getPeriod()->getEnd()->getValue();
			break;
		case "Observation":
			$arrOfCoding = [];
			echo property_exists($resource, 'code');
			if ($resource->getCode() !== null){
				$arrOfCoding = $resource->getCode()->getCoding();
			}else {
				break;
				// $arrOfCoding = $resource->getComponent()
			}
			
			$obsList = array(
			'obs_week_of_prgncy' => "weeks of pregnancy", 
			'obs_body_temp' => "body temperature", // s
			'obs_pulse' => "pulse",
			'obs_bp_contraction' => "Systolic Blood Pressure",
			'obs_bp_dilation' => "Diastolic Blood Pressure",
			'obs_weight' => "body weight",
			'obs_urine_sugar' => "Sugar in Urine",
			'obs_urine_protein' => "Protein in Urine",
			'obs_edema'=> "edema",
			'obs_fetal_weight'=> "fetal weight",
			'obs_afi'=> "afi",
			'obs_mvp'=> "mvp",
			'obs_message'=> "message",
			'obs_notes'=> "notes",
			'exam_eval_date'=> "evaluation date",
			'exam_cre'=> "cre",
			'exam_egfr'=> "Glomerular filtration rate",
			'exam_uacr'=> "uacr",
			'exam_ast'=> "ast",
			'exam_alt'=> "alt",
			'exam_gtp'=> "gtp",
			'exam_hb'=> "hb",
			'exam_hba1c'=> "hba1c",
			'exam_ga'=> "ga",
			'exam_anti_gad'=> "anti gad",
			'exam_blood_c_peptide'=> "blood c peptide",
			'exam_blood_sugar'=> "blood sugar",
			'exam_other'=> "other",
			'exam_rsn_for_75gogtt'=> "reason for 75gogtt",
			'exam_eval_date_75gogtt'=> "eval date 75gogtt",
			'exam_sgr_0min_75gogtt'=> "sugar 0min 75gogtt",
			'exam_sgr_60min_75gogtt'=> "sugar 60min 75gogtt",
			'exam_sgr_120min_75gogtt'=> "sugar 120min 75gogtt",
			'exam_insln_0min_75gogtt'=> "insulin 0min 75gogtt",
			'exam_insln_60min_75gogtt'=> "insulin 60min 75gogtt",
			'exam_insln_120min_75gogtt'=> "insulin 120min 75gogtt",
			);
			$type = "";
			$typeVal = "";
			$doneFlag = false;
			$tempResource = $resource;
			foreach ($arrOfCoding as $code) {
				foreach ($obsList as $key => $key_value){
					if (stripos($code->getDisplay(),$key_value) !== false  || stripos($key_value,$code->getDisplay()) !== false ){
					echo "\n"."code->getDisplay: ".$code->getDisplay()." key_value: ".$key_value;
						$type = $key;
						if (stripos($key_value,"blood pressure") !== false ){
							$index = 0;
							foreach ($resource->getComponent() as $comps) {
								echo "\n".$comps->getCode()->getText();
								if(stripos($comps->getCode()->getText(),$key_value) !== false){
									$tempResource = $resource->getComponent()[$index];
									$obj->$type = $tempResource->getValueQuantity()->getValue()."";
									break;
								}
								$index++;
							};
						}else {
							$doneFlag = true;
							break;
						}
					}
				}
				if ($doneFlag == true ){
					break;
				}
			}
			echo "\n"."type : ".$type;
			if (strlen($type) && stripos($type,"obs_bp") === false){
				if (stripos($type,"urine") !== false){
					$obj->$type = "".$tempResource->getvalueCodeableConcept()->getText();
				}else {
					$obj->$type = "".$tempResource->getValueQuantity()->getValue();

				}
			}
			break;

	}
}
$parser = new \DCarbone\PHPFHIRGenerated\R4\PHPFHIRResponseParser();

if(file_exists('bouba_FHIR_sample.json') ==1)
{
	$filename = 'sample.fhir.json';
	echo "\n";
	$data = file_get_contents($filename); //data read from json file

	$users = json_decode($data);  //decode a data
	$entries = $users->entry;
	$obj = new formattedValues();

	for($x = 0; $x < sizeof($entries); $x++){
		if (property_exists($entries[$x], 'resource')) {
			$resource = $parser->parse(json_encode($entries[$x]->resource));
			$fhirType = $resource->_getFHIRTypeName();
			getData($fhirType,$resource,$obj);
		}else {
			$resource = $parser->parse(json_encode($entries[$x]));
			$fhirType = $resource->_getFHIRTypeName();
			getData($fhirType,$resource,$obj);
		}
	}
	echo "\n"."test".print_r($obj);
}


?>
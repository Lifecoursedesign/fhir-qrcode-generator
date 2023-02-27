<?php

namespace Saitama\QR;
use Exception;

require_once "jsonFormattedValues.php";

if (!strpos(__DIR__, 'vendor')) {
	include_once(__DIR__ . '/../vendor/autoload.php');
}

use DCarbone\PHPFHIRGenerated\R4\PHPFHIRResponseParser;

class FHIRParser
{
	private $parser;
	private $jsonFormattedValues;
	// private $institution_id;
	// private $department_id;
	private $loincObsList;
	private $loincFamilyHistoryList;
	private $loinc;

	/**
	 * The constructor function initializes the class variables and creates instances of the other classes
	 * 
	 * @param storage The folder path to store the files.
	 */
	public function __construct()
	{
		$this->jsonFormattedValues = new JsonFormattedValues();
		$this->parser = new PHPFHIRResponseParser();
		// $this->department_id = array("Obstetrics" => 1, "Internal Medicine" => 2, "Nutritional Guidance" => 3, "Diabetes Internal Medicine" => 4);
		// $insts = array("埼玉医大総合医療センター"=>1, "愛和病院"=>2, "恵愛病院"=>3, "瀬戸病院"=>4);
		// $department = array("産科"=>1, "内科"=>2, "栄養指導"=>3, "糖尿病内科"=>4);
		// $this->institution_id = array("Saitama Medical University General Medical Center" => 1, "Aiwa Hospital" => 2, "Keiai Hospital" => 3, "Seto Hospital" => 4);
		$this->loinc = new LOINC();
		$this->loincObsList = $this->loinc->loincObsList;
		$this->loincFamilyHistoryList = $this->loinc->loincFamilyHistoryList;
	}

	/**
	 * This gets the data per entry and place it into a json formatted object
	 * 
	 * @param $type resourceType
	 * @param $resource the entry
	 * 
	 * @return Nothing.
	 */
	private function getData($type, $resource)
	{
		switch ($type) {
			case "FamilyMemberHistory":
				// get names
				// foreach ($resource->getName() as $name) {
				// 	$valueCode = $name->getExtension()[0]->getValueCode()->getValue()->getValue();
				// 	$key = "partner_name_kana";
				// 	if ($valueCode == "SYL") {
				// 		$key = "partner_name_furi";
				// 	}

				// 	$this->jsonFormattedValues->$key = $name->getText()->getValue()->getValue();
				// }
				$this->jsonFormattedValues->partner_age = $resource->getAgeAge()->getValue()->getValue()->getValue();
				$arrOfExtensions = $resource->getExtension();
				foreach ($arrOfExtensions as $code) {
					$componentCode = $code->getValueCodeableConcept()->getCoding()[0]->getCode()->getValue()->getValue();
					// check if code is supported

					if (array_key_exists($componentCode, $this->loincFamilyHistoryList)) {
						$exploded = explode("~", $this->loincFamilyHistoryList[$componentCode]);
						$identifier = "get" . $exploded[0];
						$key = $exploded[1];
						// clone to dismantle protected prop
						$testArr = (array) $code->getValueCodeableConcept()->{$identifier}();
						$prefix = chr(0) . '*' . chr(0);
						if (array_key_exists($prefix . "value", $testArr)) {
							$this->jsonFormattedValues->$key = $testArr[$prefix . "value"]->getValue();
						} else {
							echo "Identifier Function does not exist for - " . $identifier . " - " . $key . "\n";
						}
					}
				}
				// $resource->getBornDate
				break;
			case "Patient":
				// get medical card _id
				// $this->jsonFormattedValues->medical_card_id = " " . $resource->getId();

				// get names
				// foreach ($resource->getName() as $name) {
				// 	$valueCode = $name->getExtension()[0]->getValueCode()->getValue()->getValue();
				// 	$key = "patient_name_kana";
				// 	if ($valueCode == "SYL") {
				// 		$key = "patient_name_furi";
				// 	}

				// 	$this->jsonFormattedValues->$key = $name->getText()->getValue()->getValue();
				// }

				// needs actual data
				// get Occupation 
				// if ($resource->getExtension()){
				// var_dump($resource->getExtension());
				// var_dump($resource->getExtension()[0]);
				// $this->jsonFormattedValues->patient_occupation = $resource->getExtension()[0]->getText()->getValue();

				// }

				// get marital status
				if ($resource->getMaritalStatus()) {
					$this->jsonFormattedValues->patient_marital_status = $resource->getMaritalStatus()->getText()->getValue()->getValue();
				}

				break;
			case "Organization":
				$extension = $resource->getExtension()[0];
				if (method_exists($extension, "getValueCodeableConcept")) {
					if ($extension->getValueCodeableConcept()->getCoding()[0]->getCode()->getValue()->getValue() === "58237-9") {
						$this->jsonFormattedValues->hospital_name = $resource->getName()->getValue()->getValue();
					}
				} else if (method_exists($extension, "getValueCode")) {
					if ($extension->getValueCode()->getValue() === "62330-6") {
						$this->jsonFormattedValues->birth_hospital_facility_name = $resource->getName()->getValue()->getValue();
					}
				}
			
				// }
				break;
			case "Practitioner":


				$identifier_code = $resource->getIdentifier()[0]->getType()->getCoding()[0]->getCode()->getValue()->getValue();
				switch ($identifier_code) {
					case "22028-5":
						$idKey = "attending_physician_id";
						break;
					case "18775-7":
						$idKey = "staff_practitioner_id";
						break;
				}
				$this->jsonFormattedValues->$idKey = $resource->getIdentifier()[0]->getValue()->getValue()->getValue();

				$name_code = $resource->getName()[0]->getExtension()[0]->getValueCodeableConcept()->getCoding()[0]->getCode()->getValue()->getValue();
				switch ($name_code) {
					case "52526-1":
						$nameKey = "attending_physician_name";
						break;
					case "PX161601010400":
						$nameKey = "midwife_name";
						break;
					case "18774-0":
						$nameKey = "staff_practitioner_name";
						break;
				}
				$this->jsonFormattedValues->$nameKey =  $resource->getName()[0]->getText()->getValue()->getValue();

				break;
			case "Encounter":
				// $this->jsonFormattedValues->obs_consult_date = " " . $resource->getPeriod()->getStart()->getValue();
				// $this->jsonFormattedValues->obs_next_otp_date = "" . $resource->getPeriod()->getEnd()->getValue();
				break;
			case "Observation":
				$arrOfCoding = $resource->getComponent();
				foreach ($arrOfCoding as $code) {
					$componentCode = $code->getCode()->getCoding()[0]->getCode()->getValue()->getValue();
					// check if code is supported
					if (array_key_exists($componentCode, $this->loincObsList)) {
						// echo $componentCode."\n";
						$exploded = explode("~", $this->loincObsList[$componentCode]);
						$identifier = "get" . $exploded[0];
						$key = $exploded[1];
						// echo "Code: " . $componentCode . " - " . $identifier . " - " . $key . "\n";

						// clone to dismantle protected prop
						$testArr = (array) $code->{$identifier}();
						$prefix = chr(0) . '*' . chr(0);

						if ($identifier === "getValueQuantity") {
							if (array_key_exists($prefix . "value", $testArr) && array_key_exists($prefix . "unit", $testArr)) {
								$this->jsonFormattedValues->$key = $testArr[$prefix . "value"]->getValue()->getValue() . $testArr[$prefix . "unit"]->getValue()->getValue();
							} else {
								echo "Identifier Function does not exist for - " . $identifier . " - " . $key . "\n";
							}
						} else if ($identifier === "getValueCodeableConcept") {
							if (array_key_exists($prefix . "text", $testArr)) {
								$this->jsonFormattedValues->$key = $testArr[$prefix . "text"]->getValue()->getValue();
							} else {
								echo "Identifier Function does not exist for - " . $identifier . " - " . $key . "\n";
							}
						} else if ($identifier === "getValueDateTime" || $identifier === "getValueString") {
							if (array_key_exists($prefix . "value", $testArr)) {
								$this->jsonFormattedValues->$key = $testArr[$prefix . "value"]->getValue();
							} else {
								echo "Identifier Function does not exist for - " . $identifier . " - " . $key . "\n";
							}
						}
					}
				}
				break;
		}
	}
	/**
	 * This split a json string into per entry and sends each entry by resourceType
	 * 
	 * @param $json json string
	 * 
	 * @return String of Json Formatted Values.
	 */
	public function handleJson($json)
	{
		$users = json_decode($json);
		$entries = $users->entry;
		for ($x = 0; $x < sizeof($entries); $x++) {
			if (property_exists($entries[$x], 'resource')) {
				$resource = $this->parser->parse(json_encode($entries[$x]->resource));
				$fhirType = $resource->_getFHIRTypeName();
				// if ($fhirType == "Observation"){
				$this->getData($fhirType, $resource);
				// }
			}
			// else {
			// 	$resource = $this->parser->parse(json_encode($entries[$x]));
			// 	$fhirType = $resource->_getFHIRTypeName();
			// 	$this->getData($fhirType, $resource);
			// }
		}
		return $this->jsonFormattedValues->toArray();
	}
}

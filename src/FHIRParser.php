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
	private $loincGroupTypes;
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
		$this->loincGroupTypes = $this->loinc->obsGroupTypes;
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
				// var_dump($resource->getName());
				$group = "patient_profile";
				foreach ($resource->getName() as $name) {
					$valueCode = $name->getExtension()[0]->getValueCode()->getValue()->getValue();
					$key = "partner_name_kana";
					if ($valueCode == "SYL") {
						$key = "partner_name_furi";
					}

					$this->jsonFormattedValues->$group->$key = $name->getText()->getValue()->getValue();
				}
				$this->jsonFormattedValues->$group->partner_age = $resource->getAgeAge()->getValue()->getValue()->getValue();
				$arrOfExtensions = $resource->getExtension();
				foreach ($arrOfExtensions as $code) {

					$componentCode = $code->getValueCodeableConcept() != null ?
						$code->getValueCodeableConcept()->getCoding()[0]->getCode()->getValue()->getValue()
						: $code->getExtension()[0]->getValueCoding()->getCode()->getValue()->getValue();
					// check if code is supported

					if (array_key_exists($componentCode, $this->loincFamilyHistoryList)) {
						$exploded = explode("~", $this->loincFamilyHistoryList[$componentCode]);
						$identifier = "get" . $exploded[0];
						$key = $exploded[1];
						$group = $exploded[2];

						if ($code->getValueCodeableConcept() == null) {
							$code = $code->getExtension()[1];
						}

						if (method_exists($code, $identifier) && $code->{$identifier}() != NULL) {
							if ($identifier === "getValueDateTime" || $identifier === "getValueString") {
								$this->jsonFormattedValues->$group->$key = $code->{$identifier}()->getValue();
							} else {
								$this->jsonFormattedValues->$group->$key = $code->{$identifier}()->getValue()->getValue();
							}
						}
					}
				}
				$this->jsonFormattedValues->$group->partner_birthdate = $resource->getBornDate()->getValue();
				break;
			case "Patient":
				$group = "patient_profile";

				// get medical card _id
				$this->jsonFormattedValues->$group->medical_card_id = " " . $resource->getId();

				// get names
				foreach ($resource->getName() as $name) {
					$valueCode = $name->getExtension()[0]->getValueCode()->getValue()->getValue();
					$key = "patient_name_kana";
					if ($valueCode == "SYL") {
						$key = "patient_name_furi";
					}

					$this->jsonFormattedValues->$group->$key = $name->getText()->getValue()->getValue();
				}

				// get Occupation 
				if ($resource->getExtension() != NULL && sizeof($resource->getExtension())) {
					$code = $resource->getExtension()[0]->getExtension()[0]->getValueCoding()->getCode()->getValue()->getValue();
					if ($code == "74164-5") {
						$this->jsonFormattedValues->$group->patient_occupation = $resource->getExtension()[0]->getExtension()[1]->getValueString()->getValue();
					}
				}

				// get marital status
				if ($resource->getMaritalStatus()) {
					$this->jsonFormattedValues->$group->patient_marital_status = $resource->getMaritalStatus()->getText()->getValue()->getValue();
				}

				break;
			case "Organization":
				$extension = $resource->getExtension()[0];
				$group = "health_checkup";
				if (method_exists($extension, "getValueCodeableConcept")) {
					if ($extension->getValueCodeableConcept()->getCoding()[0]->getCode()->getValue()->getValue() === "58237-9") {
						$this->jsonFormattedValues->$group->hospital_name = $resource->getName()->getValue()->getValue();
					}
				}else if (method_exists($extension, "getValueCode")) {
					if ($extension->getValueCode()->getValue() === "62330-6") {
						$this->jsonFormattedValues->birth_hospital_facility_name = $resource->getName()->getValue()->getValue();
					}
				}

				// }
				break;
			case "Practitioner":


				// $identifier_code = $resource->getIdentifier()[0]->getType()->getCoding()[0]->getCode()->getValue()->getValue();
				// switch ($identifier_code) {
				// 	case "22028-5":
				// 		$idKey = "attending_physician_id";
				// 		break;
				// 	case "18775-7":
				// 		$idKey = "staff_practitioner_id";
				// 		break;
				// }
				// $this->jsonFormattedValues->$idKey = $resource->getIdentifier()[0]->getValue()->getValue()->getValue();

				$group = "health_checkup";
				$name_code = $resource->getName()[0]->getExtension()[0]->getValueCodeableConcept()->getCoding()[0]->getCode()->getValue()->getValue();
				$resourceCode = NULL;
				$nameKey = NULL;
				// $resourceCode = $resource->getCode() != NULL ? $resource->getCode()->getCoding()[0]->getCode()->getValue()->getValue() : null;
				switch ($name_code) {
					case "52526-1":
						if ($resourceCode != NULL){
							$nameKey = "attending_physician_name";

							$concat = "_hc";
							if ($resourceCode == "15508-5"){
									$concat = "_dp";
							}
							$nameKey = $nameKey.$concat;
						}
						break;
					case "PX161601010400":
						$nameKey = "midwife_name";
						break;
					case "18774-0":
						$nameKey = "staff_practitioner_name";
						break;
					default:
						$nameKey = NULL;
						break;
				}
				if ($nameKey != NULL){
					$this->jsonFormattedValues->$group->$nameKey =  $resource->getName()[0]->getText()->getValue()->getValue();
				}

				break;
			case "Encounter":

				if ($resource->getExtension() != NULL & sizeof($resource->getExtension())) {
					$code = $resource->getExtension()[0]->getValueCodeableConcept()->getCoding()[0]->getCode()->getValue()->getValue();
					if ($code == "76427-4") {
						if (method_exists($resource, "getEffectiveDateTime") && $resource->getEffectiveDateTime() != NULL) {
							$group = "health_checkup";
							$this->jsonFormattedValues->$group->visit_date_hc = $resource->getEffectiveDateTime()->getValue()->getValue();
						}
					}
				}

				break;
			case "Observation":
				$arrOfCoding = $resource->getComponent();
				$resourceCode = $resource->getCode()->getCoding()[0]->getCode()->getValue()->getValue();
				if (sizeof($arrOfCoding)) {
					// var_dump($arrOfCoding);
					if (array_key_exists($resourceCode, $this->loincGroupTypes)) {
						$groupType = $this->loincGroupTypes[$resourceCode];
						foreach ($arrOfCoding as $code) {
							$componentCode = $code->getCode()->getCoding()[0]->getCode()->getValue()->getValue();
							// check if code is supported

							if (array_key_exists($componentCode, $groupType)) {
								$exploded = explode("~", $groupType[$componentCode]);
								$identifier = "get" . $exploded[0];
								$key = $exploded[1];
								$group = $exploded[2];
								// $txt = "Code: " . $componentCode . " - " . $identifier . " - " . $key;
								// file_put_contents($qr_user_path.$dir_slash."arrOfComponentCode.txt", $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
								// echo "Code: " . $componentCode . " - " . $identifier . " - " . $key . "\n";

								// clone to dismantle protected prop
								// $testArr = (array) $code->{$identifier}();
								// var_dump($code->{$identifier}());

								if (method_exists($code, $identifier) && $code->{$identifier}() != NULL) {
									if ($identifier === "getValueDateTime" || $identifier === "getValueString") {
										$this->jsonFormattedValues->$group->$key = $code->{$identifier}()->getValue();
									} else {
										$this->jsonFormattedValues->$group->$key = $code->{$identifier}()->getValue()->getValue();
									}

									// $prefix = chr(0) . '*' . chr(0);
									// file_put_contents($qr_user_path.$dir_slash."testArr_".$componentCode.".txt", json_encode($testArr));
									// if ($identifier === "getValueQuantity") {
									// 	if (array_key_exists($prefix . "value", $testArr) && array_key_exists($prefix . "unit", $testArr)) {
									// 		$this->jsonFormattedValues->$key = $testArr[$prefix . "value"]->getValue()->getValue() . $testArr[$prefix . "unit"]->getValue()->getValue();
									// 	} else {
									// 		echo "Identifier Function does not exist for - " . $identifier . " - " . $key . "\n";
									// 	}
									// } else if ($identifier === "getValueCodeableConcept") {
									// 	if (array_key_exists($prefix . "text", $testArr)) {
									// 		$this->jsonFormattedValues->$key = $testArr[$prefix . "text"]->getValue()->getValue();
									// 	} else {
									// 		echo "Identifier Function does not exist for - " . $identifier . " - " . $key . "\n";
									// 	}
									// } else if ($identifier === "getValueDateTime" || $identifier === "getValueString") {
									// 	if (array_key_exists($prefix . "value", $testArr)) {
									// 		$this->jsonFormattedValues->$key = $testArr[$prefix . "value"]->getValue();
									// 	} else {
									// 		echo "Identifier Function does not exist for - " . $identifier . " - " . $key . "\n";
									// 	}
									// }
								}
							}
						}
					}
				} else {
					if (array_key_exists($resourceCode, $this->loincObsList)) {
						$exploded = explode("~", $this->loincObsList[$resourceCode]);
						$identifier = "get" . $exploded[0];
						$key = $exploded[1];
						$group = $exploded[2];

						if (method_exists($resource, $identifier) && $resource->{$identifier}() != NULL) {
							if ($identifier === "getValueDateTime" || $identifier === "getValueString") {
								$this->jsonFormattedValues->$group->$key = $resource->{$identifier}()->getValue();
							} else {
								$this->jsonFormattedValues->$group->$key = $resource->{$identifier}()->getValue()->getValue();
							}

							// $prefix = chr(0) . '*' . chr(0);
							// file_put_contents($qr_user_path.$dir_slash."testArr_".$componentCode.".txt", json_encode($testArr));
							// if ($identifier === "getValueQuantity") {
							// 	if (array_key_exists($prefix . "value", $testArr) && array_key_exists($prefix . "unit", $testArr)) {
							// 		$this->jsonFormattedValues->$key = $testArr[$prefix . "value"]->getValue()->getValue() . $testArr[$prefix . "unit"]->getValue()->getValue();
							// 	} else {
							// 		echo "Identifier Function does not exist for - " . $identifier . " - " . $key . "\n";
							// 	}
							// } else if ($identifier === "getValueCodeableConcept") {
							// 	if (array_key_exists($prefix . "text", $testArr)) {
							// 		$this->jsonFormattedValues->$key = $testArr[$prefix . "text"]->getValue()->getValue();
							// 	} else {
							// 		echo "Identifier Function does not exist for - " . $identifier . " - " . $key . "\n";
							// 	}
							// } else if ($identifier === "getValueDateTime" || $identifier === "getValueString") {
							// 	if (array_key_exists($prefix . "value", $testArr)) {
							// 		$this->jsonFormattedValues->$key = $testArr[$prefix . "value"]->getValue();
							// 	} else {
							// 		echo "Identifier Function does not exist for - " . $identifier . " - " . $key . "\n";
							// 	}
							// }
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
		//initialize jsons
		$groups = ["patient_profile", "health_checkup", "blood_urine", "delivery_postpartum"];

		for ($x = 0; $x < sizeof($groups); $x++) {
			$this->jsonFormattedValues->{$groups[$x]} = new \stdClass();
		}
		
		for ($x = 0; $x < sizeof($entries); $x++) {
			if (property_exists($entries[$x], 'resource')) {
				$resource = $this->parser->parse(json_encode($entries[$x]->resource));
				$fhirType = $resource->_getFHIRTypeName();
				// echo $fhirType."\n";
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

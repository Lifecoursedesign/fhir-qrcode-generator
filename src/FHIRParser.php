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
		$this->loinc = new LOINC();
		$this->loincObsList = $this->loinc->loincObsList;
		$this->loincGroupTypes = $this->loinc->obsGroupTypes;
		$this->loincFamilyHistoryList = $this->loinc->loincFamilyHistoryList;
	}
	/**	
	 * This checks if functions exist, returns false if it doesnt.
	 * 
	 * @param $arr array of strings depicting functions
	 * @param $resource the entry
	 * 
	 * @return Boolean if set of functions exist.
	 */
	private function CheckFuncExists($arr, $resource)
	{
		$flag = true;
		$tempResource = $resource;
		for ($x = 0; $x < sizeof($arr) && $flag != false; $x++) {
			if (preg_match("([[0-9]+])", $arr[$x]) == true) {
				$chars = preg_split("([[0-9]+])", $arr[$x]);
				preg_match("([0-9]+)", $arr[$x], $matches);
				if (method_exists($tempResource, $chars[0]) == true && $tempResource->{$chars[0]}() !== null) {
					if (array_key_exists($matches[0], $tempResource->{$chars[0]}())) {
						$tempResource = $tempResource->{$chars[0]}()[$matches[0]];
					}
				} else {
					$flag = false;
					break;
				}
			} else if (method_exists($tempResource, $arr[$x]) == true && $tempResource->{$arr[$x]}() !== null) {
				$tempResource = $tempResource->{$arr[$x]}();
			} else {
				$flag = false;
				break;
			}
		}
		return $flag;
	}
	/**	
	 * This checks if functions exist, returns false if it doesnt.
	 * 
	 * @param $string to get cut
	 * @param $max length
	 * 
	 * @return String the cutted string 
	 */
	private function cutString($string, $length)
	{
		if (gettype($string) !== "string") {
			$string = strval($string);
		}

		if (gettype($string) === "string" && mb_strlen($string) > (int)$length) {
			return mb_substr($string, 0, $length, 'UTF-8');
		}
		return $string;
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

				$group = "patient_profile";

				if ($this->CheckFuncExists(["getName"], $resource)) {
					foreach ($resource->getName() as $name) {
						if ($this->CheckFuncExists(["getExtension[0],getValueCode,getValue,getValue"], $name)) {
							$valueCode = $name->getExtension()[0]->getValueCode()->getValue()->getValue();
							$key = "partner_name_kana";
							$maxLength = 60;
							if ($valueCode == "SYL") {
								$key = "partner_name_furi";
								$maxLength = 20;
							}

							$value = $this->cutString($name->getText()->getValue()->getValue(), $maxLength);
							$this->jsonFormattedValues->$group->$key = $value;
						}
					}
				}

				if ($this->CheckFuncExists(["getAgeAge", "getValue", "getValue", "getValue"], $resource)) {
					$maxLength = 2;
					$this->jsonFormattedValues->$group->partner_age = $this->cutString($resource->getAgeAge()->getValue()->getValue()->getValue(), $maxLength);
				}

				if ($this->CheckFuncExists(["getExtension"], $resource)) {
					$arrOfExtensions = $resource->getExtension();

					foreach ($arrOfExtensions as $code) {
						if (
							$this->CheckFuncExists(["getValueCodeableConcept", "getCoding[0]", "getCode", "getValue", "getValue"], $code) ||
							$this->CheckFuncExists(["getExtension[0]", "getValueCoding", "getCode", "getValue", "getValue"], $code)
						) {
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
										$value = $code->{$identifier}()->getValue()->getValue();
										$maxLength = strlen($value);
										if (array_key_exists(3, $exploded)) {
											$maxLength = $exploded[3];
										}
										$this->jsonFormattedValues->$group->$key = $this->cutString($value, $maxLength);
									} else {
										$value = $code->{$identifier}()->getValue()->getValue()->getValue();
										$maxLength = strlen($value);
										if (array_key_exists(3, $exploded)) {
											$maxLength = $exploded[3];
										}
										$this->jsonFormattedValues->$group->$key = $this->cutString($value, $maxLength);
									}
								}
							}
						}
					}
				}

				if ($this->CheckFuncExists(["getBornDate", "getValue", "getValue"], $resource)) {
					$value = $resource->getBornDate()->getValue()->getValue();
					$maxLength = 30;
					$this->jsonFormattedValues->$group->partner_birthdate = $this->cutString($value, $maxLength);
				}
				break;
			case "Patient":
				$group = "patient_profile";

				// get medical card _id
				if ($this->CheckFuncExists(["getId", "getValue"], $resource)) {
					$maxLength = 15;
					$value = $this->cutString($resource->getId()->getValue(), $maxLength);
					$this->jsonFormattedValues->$group->medical_card_id = $value;
				}

				// get names
				if ($this->CheckFuncExists(["getName"], $resource)) {
					foreach ($resource->getName() as $name) {
						if ($this->CheckFuncExists(["getExtension[0]", "getValueCode", "getValue", "getValue"], $name)) {
							$valueCode = $name->getExtension()[0]->getValueCode()->getValue()->getValue();
							$key = "patient_name_kana";
							$maxLength = 60;
							if ($valueCode == "SYL") {
								$key = "patient_name_furi";
								$maxLength = 30;
							}

							$value = $this->cutString($name->getText()->getValue()->getValue(), $maxLength);
							$this->jsonFormattedValues->$group->$key = $value;
						}
					}
				}


				// get Occupation 
				if ($this->CheckFuncExists(["getExtension[0]", "getExtension[0]", "getValueCoding", "getCode", "getValue", "getValue"], $resource)) {
					$code = $resource->getExtension()[0]->getExtension()[0]->getValueCoding()->getCode()->getValue()->getValue();
					if ($code == "74164-5") {
						$maxLength = 40;
						$value = $this->cutString($resource->getExtension()[0]->getExtension()[1]->getValueString()->getValue(), $maxLength);
						$this->jsonFormattedValues->$group->patient_occupation = $value;
					}
				}

				// get marital status
				if ($this->CheckFuncExists(["getMaritalStatus", "getText", "getValue", "getValue"], $resource)) {
					$maxLength = 65;
					$value = $this->cutString($resource->getMaritalStatus()->getText()->getValue()->getValue(), $maxLength);

					$this->jsonFormattedValues->$group->patient_marital_status = $value;
				}

				break;
			case "Organization":

				if ($this->CheckFuncExists(["getExtension[0]"], $resource)) {
					$extension = $resource->getExtension()[0];
					$group = "health_checkup";
					if ($this->CheckFuncExists(["getValueCodeableConcept", "getCoding[0]", "getCode", "getValue", "getValue"], $extension)) {
						if ($extension->getValueCodeableConcept()->getCoding()[0]->getCode()->getValue()->getValue() === "58237-9") {
							$maxLength = 100;
							$value = $this->cutString($resource->getName()->getValue()->getValue(), $maxLength);

							$this->jsonFormattedValues->$group->hospital_name = $value;
						}
					} else if ($this->CheckFuncExists(["getValueCode", "getValue"], $extension)) {
						if ($extension->getValueCode()->getValue() === "62330-6") {
							$maxLength = 100;
							$value = $this->cutString($resource->getName()->getValue()->getValue(), $maxLength);
							$this->jsonFormattedValues->birth_hospital_facility_name = $value;
						}
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

				if ($this->CheckFuncExists(["getName[0]", "getExtension[0]", "getValueCodeableConcept", "getCoding[0]", "getCode", "getValue", "getValue"], $resource)) {
					$name_code = $resource->getName()[0]->getExtension()[0]->getValueCodeableConcept()->getCoding()[0]->getCode()->getValue()->getValue();
					$nameKey = NULL;
					switch ($name_code) {
						case "52526-1":
							if ($this->CheckFuncExists(["getCode", "getCoding[0]", "getCode", "getValue", "getValue"], $resource)) {
								$nameKey = "attending_physician_name";
								$resourceCode =  $resource->getCode()->getCoding()[0]->getCode()->getValue()->getValue();
								$concat = "_hc";
								$group = "health_checkup";
								if ($resourceCode == "15508-5") {
									$concat = "_dp";
									$group = "delivery_postpartum";
								}

								$nameKey = $nameKey . $concat;
								$maxLength = 20;
							}
							break;
						case "PX161601010400":
							$nameKey = "midwife_name";
							$group = "delivery_postpartum";
							$maxLength = 20;
							break;
						case "18774-0":
							$nameKey = "staff_practitioner_name";
							$group = "delivery_postpartum";
							$maxLength = 20;
							break;
						default:
							$nameKey = NULL;
							$maxLength = 0;
							break;
					}
					if ($nameKey != NULL) {
						if ($this->CheckFuncExists(["getName[0]", "getText", "getValue", "getValue"], $resource)) {

							$value = $this->cutString($resource->getName()[0]->getText()->getValue()->getValue(), $maxLength);
							$this->jsonFormattedValues->$group->$nameKey =  $value;
						}
					}
				}


				break;
			case "Encounter":

				if ($this->CheckFuncExists(["getExtension[0]", "getValueCodeableConcept", "getCoding[0]", "getCode", "getValue", "getValue"], $resource)) {
					$code = $resource->getExtension()[0]->getValueCodeableConcept()->getCoding()[0]->getCode()->getValue()->getValue();
					if ($code == "76427-4") {
						if (method_exists($resource, "getEffectiveDateTime") && $resource->getEffectiveDateTime() != NULL) {
							$group = "health_checkup";
							$maxLength = 25;
							$value = $this->cutString($resource->getEffectiveDateTime()->getValue()->getValue(), $maxLength);

							$this->jsonFormattedValues->$group->visit_date_hc = $value;
						}
					}
				}

				break;
			case "Observation":
				if ($this->CheckFuncExists(["getComponent"], $resource)) {
					$arrOfCoding = $resource->getComponent();
					if ($this->CheckFuncExists(["getCode", "getCoding[0]", "getCode", "getValue", "getValue"], $resource)) {
						$resourceCode = $resource->getCode()->getCoding()[0]->getCode()->getValue()->getValue();
						if (sizeof($arrOfCoding)) {
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

										if (method_exists($code, $identifier) && $code->{$identifier}() != NULL) {
											if ($identifier === "getValueDateTime" || $identifier === "getValueString") {
												$value = $code->{$identifier}()->getValue()->getValue();
												$maxLength = strlen($value);
												if (array_key_exists(3, $exploded)) {
													$maxLength = $exploded[3];
												}
												$this->jsonFormattedValues->$group->$key = $this->cutString($value, $maxLength);
											} else {
												$value = $code->{$identifier}()->getValue()->getValue()->getValue();
												$maxLength = strlen($value);
												if (array_key_exists(3, $exploded)) {
													$maxLength = $exploded[3];
												}
												$this->jsonFormattedValues->$group->$key = $this->cutString($value, $maxLength);
											}
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
										$value = $resource->{$identifier}()->getValue()->getValue();
										$maxLength = strlen($value);
										if (array_key_exists(3, $exploded)) {
											$maxLength = $exploded[3];
										}
										$this->jsonFormattedValues->$group->$key = $this->cutString($value, $maxLength);
									} else {
										$value = $resource->{$identifier}()->getValue()->getValue()->getValue();
										$maxLength = strlen($value);
										if (array_key_exists(3, $exploded)) {
											$maxLength = $exploded[3];
										}
										$this->jsonFormattedValues->$group->$key = $this->cutString($value, $maxLength);
									}
								}
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
		//initialize jsons
		$groups = ["patient_profile", "health_checkup", "blood_urine", "delivery_postpartum"];

		for ($x = 0; $x < sizeof($groups); $x++) {
			$this->jsonFormattedValues->{$groups[$x]} = new \stdClass();
		}

		for ($x = 0; $x < sizeof($entries); $x++) {
			if (property_exists($entries[$x], 'resource')) {
				$resource = $this->parser->parse(json_encode($entries[$x]->resource));
				$fhirType = $resource->_getFHIRTypeName();
				$this->getData($fhirType, $resource);
			}
		}
		return $this->jsonFormattedValues->toArray();
	}
}

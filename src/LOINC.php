<?php

namespace Saitama\QR;

class LOINC
{

	public $loincObsList;
	public $loincFamilyHistoryList;
	public $obsGroupTypes;

	/**
	 ** The constructor function initializes the class variables and creates instances of the other classes
	 */
	public function __construct()
	{
		$this->loincObsList = array(
			"8302-2" => "ValueQuantity~body_height~patient_profile",
			"69460-4" => "ValueQuantity~body_weight_pre_current_pregnancy~patient_profile",
			"72166-2"=> "ValueString~tobacco_smoking_status~patient_profile",
			"11331-6"=> "ValueString~history_of_alcohol_use~patient_profile",
			"33717-0" => "ValueString~cervical_or_vaginal_cytology_study~blood_urine",

			// "11613-7" => "ValueQuantity~no_of_abortion", gone from final list
			// "8678-5"=> "ValueString~menstrual_status", gone from final list
			// "11778-8" => "ValueDateTime~delivery_date_estimated",
			// "11779-6" => "ValueDateTime~delivery_date_estimated_from_last_menstrual",
			// "11780-4" => "ValueDateTime~delivery_date_estimated_from_ovulation",
			// "53692-0" => "ValueDateTime~delivery_date_estimated_from_conception",
			// "57063-0" => "ValueDateTime~delivery_date_estimated_from_quickening",
			// "90368-2" => "ValueDateTime~delivery_date_estimated_from_physical_exam",
			// "57064-8" => "ValueDateTime~delivery_date_estimated_from_date_fundal_height_reaches_umb",
			// "53694-6" => "ValueDateTime~delivery_date_estimated_from_prior_gestational_age",
			// "48767-8" => "ValueString~annotation_comment",
			
			// "54106-0" => "ValueString~newborn_hearing_screen_method_left_ear",
			// "54109-4" => "ValueString~newborn_hearing_screen_method_right_ear",
			// "81079-6" => "ValueQuantity~vitamin_k_intake_24_hour_measured",
			// "42851-6" => "ValueString~infant_feeding_pattern_impairment",
			// "78375-3" => "ValueString~discharge_diagnosis_narrative",
			// "67703-9" => "ValueString~other_infant_factors_that_affect_newborn_screening",
			// "73806-2" => "ValueString~newborn_age_in_hours", lacking data in sheet
			// "80442-7" => "ValueString~breast_milk_intake_panel",
			// "10206-1" => "ValueString~physical_findings_of_skin_narrative",
			// "57724-7" => "ValueString~newborn_screening_short_narrative_summary",

		);
		$this->loincFamilyHistoryList = array(
			"85658-3" => "ValueString~partner_occupation~patient_profile",
			"89060-8" => "ValueString~partner_contact_no~patient_profile",
			"883-9" => "ValueString~partner_abo_group_blood~patient_profile",
			"10331-7" => "ValueString~partner_rh_blood~patient_profile",
			"72166-2" => "ValueString~partner_smoking_status~patient_profile",
			"11331-6" => "ValueString~partner_history_alcohol_use~patient_profile",
		);

		$this->obsGroupTypes = array(
			"G10001" => array(
				"97063-2"=> "ValueString~family_history_of_diabetes~patient_profile",
				"Z82.49"=> "ValueString~family_history_of_heart_circulatory_disease~patient_profile",
				"8670-2"=> "ValueString~history_of_family_diseases~patient_profile",
			),
			"49033-4" => array(
				"92660-0"=> "ValueString~time_range_until_next_menstrual_period~patient_profile",
				"3144-3"=> "ValueString~last_menstrual_period_duration~patient_profile",
				"33066-2"=> "ValueDateTime~last_menstrual_period~patient_profile",
			),
			"G10002" => array(
				"11996-6" => "ValueQuantity~no_of_pregnancies~patient_profile",
				"11977-6" => "ValueQuantity~no_of_parity~patient_profile",
				"11637-6" => "ValueQuantity~no_of_premature_birth~patient_profile",
				"68497-7" => "ValueQuantity~no_of_previous_cesarean_deliveries~patient_profile",
				"11614-5" => "ValueQuantity~no_of_spontaneous_abortion~patient_profile",
				"73772-6" => "ValueQuantity~no_of_fetal_deaths_delivered~patient_profile",
			),
			"G10003" => array(
				"93857-1" => "ValueDateTime~date_time_obstetric_delivery_pp~patient_profile",
				"76516-4"=> "ValueString~gestational_age_at_birth~patient_profile",
				"11882-8" => "ValueString~fetal_sex_us_pp~patient_profile",
				"8339-4" => "ValueString~birth_weight_measured_pp~patient_profile",
				"72149-8" => "ValueString~delivery_method~patient_profile",
				"10162-6" => "ValueTime~history_of_pregnancies_narrative~patient_profile",
				"32416-0" => "ValueString~newborn_complication~patient_profile",
				"72150-6" => "ValueString~delivery_location~patient_profile",
			),
			"G10005" => array(
				"82364-1" => "ValueString~reproductive_endocrinology_infertility_plan_care_note~patient_profile",
				"55281-0" => "ValueString~no_of_fetuses_pp~patient_profile",
				"85712-8" => "ValueString~expected_delivery_location~patient_profile",
				"57079-6" => "ValueString~birth_plan_narrative~patient_profile",
				"11359-7"=> "ValueString~history_of_allergies_immunologic_disorders~patient_profile",
				"52418-1"=> "ValueString~current_medication_name~patient_profile",
				"10164-2" => "ValueString~history_of_present_illness~patient_profile",
				"65869-0" => "ValueString~pregnancy_complication~patient_profile",
				"11349-8" => "ValueString~past_illness~patient_profile",
				"56838-6" => "ValueString~history_of_infectious_disease~patient_profile",
			),
			// "G10006" => array(
				
			// ),
			"100230-2" => array(
				"18185-9" => "ValueInteger~gestational_age~health_checkup",
				"11881-0" => "ValueQuantity~uterus_fundal_height_tape_measure~health_checkup",
				"8280-0"  => "ValueQuantity~waist_circumference_umbilicus_tape_measure~health_checkup",
				"8480-6"  => "ValueQuantity~systolic_blood_pressure~health_checkup",
				"8462-4"  => "ValueQuantity~diastolic_blood_pressure~health_checkup",
				"55407-1" => "ValueString~pitting_edema_severity_ankle~health_checkup", 
				"20454-5" => "ValueString~protein_prescence_in_urine_hc~health_checkup", 
				"25428-4" => "ValueString~glucose_prescence_in_urine_hc~health_checkup", 
				"5797-6" => "ValueString~ketones_mass_volume_in_urine_hc~health_checkup",
				"3141-9"  => "ValueQuantity~body_weight_measured_hc~health_checkup", 
				"66330-2"  => "ValueQuantity~weight_gain_and_lose~health_checkup", 
				"11961-0" => "ValueString~cervix_length_us~health_checkup",
				"32423-6" => "ValueString~physical_findings_cervix~health_checkup",
			),
			"35096-7" => array(
				"55281-0" => "ValueQuantity~no_of_fetuses_hc~health_checkup",
				"11875-2" => "ValueString~fetal_position_us~health_checkup",
				// "11948-7" => "ValueString~fetal_heart_rate_us~health_checkup",
				"11633-5" => "ValueString~fetal_heart_rate_reactivity_us~health_checkup", //alternate title of above
				"11957-8" => "ValueString~fetal_crown_rump_length_us~health_checkup",
				"11820-8" => "ValueQuantity~fetal_head_diameter_biparietal_us~health_checkup",
				"11777-0" => "ValueQuantity~fetal_abdomen_area_cross_section_us~health_checkup",
				"11979-2" => "ValueQuantity~abdominal_circumference~health_checkup",
				"11963-6" => "ValueQuantity~fetal_femur_diaphysis_length~health_checkup",
				"11727-5" => "ValueQuantity~fetal_body_weight_estimated~health_checkup",
				// "11768-9" => "ValueQuantity~fetal_body_weight_percentile_estimated_gestational_age~health_checkup",
				"12167-3" => "ValueString~fetal_amniotic_fluid_space~health_checkup",
				"11832-3" => "ValueQuantity~fetal_amniotic_fluid_space_maximum_diameter_largest_pocket~health_checkup",
				"11627-7" => "ValueQuantity~fetal_amniotic_fluid_index_sum_derived_us~health_checkup",
				"11882-8" => "ValueString~fetal_sex_us_hc~health_checkup",
				// "12130-1" => "ValueString~fetal_narrative_interpretation_study_observation~health_checkup",
				"11995-8" => "ValueString~narrative_placenta_position_us~health_checkup",
				"12116-0" => "ValueString~fetal_umbilical_cord_study_observation_us~health_checkup",
			),
			"24357-6" => array(
				"20454-5" => "ValueString~protein_prescence_in_urine_but~blood_urine", 
				"25428-4" => "ValueString~glucose_prescence_in_urine_but~blood_urine", 
				"5797-6" => "ValueString~ketones_mass_volume_in_urine_but~blood_urine",
				"14959-1" => "ValueQuantity~microalbumin_creatinine_ratio_urine~blood_urine",
			),
			"G10007" => array(
				"58452-4" => "ValueString~hepatitis_b_virus_surface_ag_volume_in_serum~blood_urine",
				"16128-1" => "ValueString~hepatitis_c_virus_surface_ag_volume_in_serum_active~blood_urine",
				"21333-0" => "ValueString~hiv_1_rna_volume_serum~blood_urine",
				"20507-0" => "ValueString~reagin_ab_presence_serum~blood_urine",
				"22587-0" => "ValueString~treponema_pallidum_ab_presence_serum~blood_urine",
				"40732-0" => "ValueString~hiv_1_lgg_ab_presence_serum_immunoblot~blood_urine",
				"22497-2" => "ValueString~rubella_virus_ab_titer_serum~blood_urine",
				"72607-5" => "ValueString~streptococcus_agalactiae_presence_vag_rectum~blood_urine",
				"45084-1" => "ValueString~chlamydia_trachomatis_dna_presence_vaginal_fluid~blood_urine",
			),
			"24364-2" => array(
				"718-7" => "ValueString~hemoglobin_mass_volume_in_blood~blood_urine",
				"26515-7" => "ValueString~platelets_volume_in_blood~blood_urine",
				"883-9" => "ValueString~abo_group_type_in_blood~blood_urine",
				"10331-7" => "ValueString~rh_type_in_blood~blood_urine",
				"890-4" => "ValueString~blood_group_antibody_presence_serum_plasma~blood_urine",
				"2885-2" => "ValueString~protein_mass_volume_serum_plasma~blood_urine",
				"1751-7" => "ValueString~albumin_mass_volume_serum_plasma~blood_urine",
				"2160-0" => "ValueString~creatinine_mass_volume_serum_plasma~blood_urine",
				"3094-0" => "ValueString~urea_nitrogen_mass_volume_serum_plasma~blood_urine",
				"69405-9" => "ValueString~glomerular_filtration_rate_in_serum_plasma_blood~blood_urine",
				"1920-8" => "ValueString~aspartate_aminotransferase_activity_volume_in_serum_plasma~blood_urine",
				"1742-6" => "ValueString~urate_mass_volume_in_serum_plasma~blood_urine",
				"2093-3" => "ValueQuantity~cholesterol_mass_volume_in_serum_plasma~blood_urine",
				"2089-1" => "ValueQuantity~cholesterol_in_ldl_mass_volume_in_serum_plasma~blood_urine",
				"2085-9" => "ValueQuantity~cholesterol_in_hdl_mass_volume_in_serum_plasma~blood_urine",
				"2571-8" => "ValueQuantity~triglyceride_mass_volume_in_serum_plasma~blood_urine",
				"4548-4" => "ValueString~hemoglobin_a1c_total_in_blood~blood_urine",
				"13873-5" => "ValueString~albumin_glycated_total_in_blood~blood_urine",
				"13926-1" => "ValueQuantity~glutamate_decarboxylase_units_volume_in_serum_plasma~blood_urine",
				"1986-9" => "ValueString~c_peptide_mass_volume_in_serum_plasma~blood_urine",
				"41652-9" => "ValueString~glucose_mass_volume_in_venous_blood~blood_urine",
				"14979-9" => "ValueString~aptt_in_platetelet_poor_plasma_by_coagulation_assay~blood_urine",
				"3016-3" => "ValueString~thyrotropin_units_volume_in_serum_plasma~blood_urine",
				"3051-0" => "ValueString~triiodothyronine_mass_volume_in_serum_plasma~blood_urine",
				"3024-7" => "ValueString~thyroxine_mass_volume_in_serum_plasma~blood_urine",
				"18332-7"=> "ValueString~thyroperoxidase_lgg_ab_units_volume_in_serum_plasma~blood_urine",
				"56635-6"=> "ValueString~thyroglobulin_lgg_ab_units_volume_in_serum_plasma~blood_urine",
				"5385-0"=> "ValueString~thyrotropin_receptor_ab_units_volume_in_serum_plasma~blood_urine",
				"30166-3"=> "ValueString~thyroid_stimulating_immunoglobulins_actual_normal_in_serum~blood_urine",
			),
			"93794-6" => array(
				"1547-9" => "ValueString~glucose_mass_volume_in_serum_plasma_baseline~blood_urine",
				"1527-1" => "ValueString~glucose_mass_volume_in_serum_plasma_30_minutes_post_75g_glucose_po~blood_urine",
				"1507-3" => "ValueString~glucose_mass_volume_in_serum_plasma_1_hour_post_75g_glucose_po~blood_urine",
				"1518-0" => "ValueString~glucose_mass_volume_in_serum_plasma_2_hours_post_75g_glucose_po~blood_urine",
				"1570-1" => "ValueString~insulin_mass_volume_in_serum_plasma_baseline~blood_urine",
				"9307-0" => "ValueString~insulin_mass_volume_in_serum_plasma_30_minutes_post_75g_glucose_po~blood_urine",
				"1561-0"=> "ValueString~insulin_mass_volume_in_serum_plasma_1_hour_post_75g_glucose_po~blood_urine",
				"1564-4"=> "ValueString~insulin_mass_volume_in_serum_plasma_2_hours_post_75g_glucose_po~blood_urine",
				"20441-2"=> "ValueString~glucose_mass_volume_in_serum_plasma_post_50g_glucose~blood_urine",
				"1504-0" => "ValueString~glucose_mass_volume_in_serum_plasma_1_hour_post_50g_glucose_po~blood_urine",
				"41024-1"=> "ValueString~glucose_mass_volume_in_serum_plasma_2_hours_post_50g_glucose_po_active~blood_urine",
			),
			"15508-5" => array(
				"73813-8" => "ValueString~characteristics_of_labor_and_delivery~delivery_postpartum",
				"69461-2" => "ValueQuantity~mothers_body_weight_at_delivery~delivery_postpartum",
				"55103-6" => "ValueQuantity~estimated_blood_loss~delivery_postpartum",
				"1322-7" => "ValueQuantity~transfusion_volume~delivery_postpartum",
				"73760-1" => "ValueString~if_cesarean_trial_of_labor_attempted~delivery_postpartum",
				"80565-5" => "ValueString~medication_administration_record~delivery_postpartum",
				"73781-7" => "ValueString~maternal_morbidity~delivery_postpartum",
				"89223-2" => "ValueString~gynecology_procedure_note~delivery_postpartum",
			),
			"54089-8" => array(
				"55281-0" => "ValueQuantity~no_of_fetuses_dp~delivery_postpartum",
				"73771-8" => "ValueString~birth_order~delivery_postpartum",
				"97103-6" => "ValueString~zygosity_type_in_fetuses_by_molecular_genetics~delivery_postpartum",
				"93857-1" => "ValueDateTime~date_time_obstetric_delivery_dp~delivery_postpartum",
				"57071-3" => "ValueString~obstetric_delivery_method~delivery_postpartum",
				// "PX241301060100" => "ValueString~mode_delivery_induce_labor_reason~delivery_postpartum",
				// "72155-5" => "ValueString~position_in_womb_fetus_rhea~delivery_postpartum", commented out cause no data type in sheet
				"32396-4" => "ValueQuantity~labor_duration~delivery_postpartum",
				"28646-8" => "ValueQuantity~ph_of_arterical_cord_blood~delivery_postpartum",
				"47995-6" => "ValueQuantity~glucose_moles_volume_in_cord_blood~delivery_postpartum",
				"28638-5" => "ValueQuantity~base_excess_in_arterial_cord_calculation~delivery_postpartum",
				"28644-3" => "ValueQuantity~carbon_dioxide_partial_pressure_in_arterial_cord_blood~delivery_postpartum",
				"28648-4" => "ValueQuantity~oxygen_partial_pressure_in_arterial_cord_blood~delivery_postpartum",
				"76689-9" => "ValueString~sex_assigned_at_birth~delivery_postpartum",
				"89269-5" => "ValueQuantity~body_height_measured_at_birth~delivery_postpartum",
				"8339-4" => "ValueQuantity~birth_weight_measured_dp~delivery_postpartum",
				"8290-9" => "ValueQuantity~head_occipital_frontal_circumference_at_birth_by_tape_measure~delivery_postpartum",
				"8279-2" => "ValueQuantity~chest_circumference_at_nipple_line~delivery_postpartum",
				"9272-6" => "ValueQuantity~1_minute_apgar_score~delivery_postpartum",
				"9274-2" => "ValueQuantity~5_minute_apgar_score~delivery_postpartum",
				"92272-4" => "ValueString~stayed_in_intensive_care~delivery_postpartum",
				"73812-0" => "ValueString~abnormal_conditions_of_newborn~delivery_postpartum",
				"57075-4" => "ValueString~newborn_delivery_information~delivery_postpartum",
			),
			"G10008" => array(
				"76427-4" => "ValueDateTime~visit_date_dp~delivery_postpartum",
				// "32486-3" => "ValueString~physical_findings_uterus~delivery_postpartum", commented out cause no data type
				"LP7269-6" => "ValueString~genital_lochia~delivery_postpartum",
				// "32422-8" => "ValueString~physical_findings_breast~delivery_postpartum", commented out cause no data type
				"55284-4" => "ValueQuantity~blood_pressure_systolic_diastolic~delivery_postpartum",
				"3141-9"  => "ValueQuantity~body_weight_measured_dp~delivery_postpartum", 
				"2887-8" => "ValueString~protein_presence_in_urine~delivery_postpartum",
				"2349-9" => "ValueString~glucose_presence_in_urine~delivery_postpartum",
				"57076-2" => "ValueString~postpartum_hospitalization_treatment_narrative~delivery_postpartum",
				"99046-5" => "ValueQuantity~total_score_epds~delivery_postpartum",
			),
			"G10009" => array(
				"69969-4" => "ValueString~newborn_screening_report_overall_laboratory_comment~delivery_postpartum",
			),
		);
	}
}
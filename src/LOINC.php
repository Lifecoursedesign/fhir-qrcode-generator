<?php

namespace Saitama\QR;

class LOINC
{

	public $loincObsList;
	public $loincFamilyHistoryList;

	/**
	 ** The constructor function initializes the class variables and creates instances of the other classes
	 */
	public function __construct()
	{
		$this->loincObsList = array(
			"8302-2" => "ValueQuantity~body_height",
			"69460-4" => "ValueQuantity~body_weight_pre_current_pregnancy",
			"11996-6" => "ValueQuantity~no_of_pregnancies",
			"11977-6" => "ValueQuantity~no_of_parity",
			"11637-6" => "ValueQuantity~no_of_premature_birth",
			"68497-7" => "ValueQuantity~no_of_previous_cesarean_deliveries",
			"11614-5" => "ValueQuantity~no_of_spontaneous_abortion",
			"11613-7" => "ValueQuantity~no_of_abortion",
			"73772-6" => "ValueQuantity~no_of_fetal_deaths_delivered",
			"72166-2"=> "ValueString~tobacco_smoking_status",
			"11331-6"=> "ValueString~history_of_alcohol_use",
			"11359-7"=> "ValueString~history_of_allergies_immunologic_disorders",
			"52418-1"=> "ValueString~current_medication_name",
			"97063-2"=> "ValueString~family_history_of_diabetes",
			"Z82.49"=> "ValueString~family_history_of_heart_circulatory_disease",
			"8670-2"=> "ValueString~history_of_family_diseases",
			"8678-5"=> "ValueString~menstrual_status",
			"92660-0"=> "ValueString~time_range_until_next_menstrual_period",
			"76516-4"=> "ValueString~gestational_age_at_birth",
			"8339-4" => "ValueString~birth_weight_measured",
			"72149-8" => "ValueString~delivery_method",
			"73781-7" => "ValueString~maternal_morbidity",
			"32416-0" => "ValueString~newborn_complication",
			"72150-6" => "ValueString~delivery_location",
			"82364-1" => "ValueString~reproductive_endocrinology_infertility_plan_care_note",
			"55281-0" => "ValueString~no_of_fetuses",
			"85712-8" => "ValueString~expected_delivery_location",
			"57079-6" => "ValueString~birth_plan_narrative",
			"10164-2" => "ValueString~history_of_present_illness",
			"11349-8" => "ValueString~past_illness",
			"56838-6" => "ValueString~history_of_infectious_disease",
			"33066-2" => "ValueDateTime~last_menstrual_period",
			"93857-1" => "ValueDateTime~date_time_obstetric_delivery",
			"11778-8" => "ValueDateTime~delivery_date_estimated",
			"11779-6" => "ValueDateTime~delivery_date_estimated_from_last_menstrual",
			"11780-4" => "ValueDateTime~delivery_date_estimated_from_ovulation",
			"53692-0" => "ValueDateTime~delivery_date_estimated_from_conception",
			"57063-0" => "ValueDateTime~delivery_date_estimated_from_quickening",
			"90368-2" => "ValueDateTime~delivery_date_estimated_from_physical_exam",
			"57064-8" => "ValueDateTime~delivery_date_estimated_from_date_fundal_height_reaches_umb",
			"53694-6" => "ValueDateTime~delivery_date_estimated_from_prior_gestational_age",
			"18185-9" => "ValueString~gestational_age",
			"11881-0" => "ValueQuantity~uterus_fundal_height_tape_measure",
			"8280-0"  => "ValueQuantity~waist_circumference_umbilicus_tape_measure",
			"8480-6"  => "ValueQuantity~systolic_blood_pressure",
			"8462-4"  => "ValueQuantity~diastolic_blood_pressure",
			"55407-1" => "ValueString~pitting_edema_severity_ankle", 
			"20454-5" => "ValueString~protein_prescence_in_urine", 
			"25428-4" => "ValueString~glucose_prescence_in_urine", 
			"5797-6" => "ValueString~ketones_mass_volume_in_urine",
			"3141-9"  => "ValueQuantity~body_weight_measured", 
			"66330-2"  => "ValueQuantity~weight_gain_and_lose", 
			"11961-0" => "ValueString~cervix_length_us",
			"32423-6" => "ValueString~physical_findings_cervix",
			"48767-8" => "ValueString~annotation_comment",
			"11875-2" => "ValueString~fetal_position_us",
			"11948-7" => "ValueString~fetal_heart_rate_us",
			"11633-5" => "ValueString~fetal_heart_rate_reactivity_us", //alternate title of above
            "11957-8" => "ValueString~fetal_crown_rump_length_us",
			"11820-8" => "ValueQuantity~fetal_head_diameter_biparietal_us",
			"11777-0" => "ValueQuantity~fetal_abdomen_area_cross_section_us",
			"11979-2" => "ValueQuantity~abdominalCircumference",
			"11963-6" => "ValueQuantity~fetal_femur_diaphysis_length",
			"11727-5" => "ValueQuantity~fetal_body_weight_estimated",
			"11768-9" => "ValueQuantity~fetal_body_weight_percentile_estimated_gestational_age",
			"12167-3" => "ValueString~fetal_amniotic_fluid_space",
			"11832-3" => "ValueQuantity~fetal_amniotic_fluid_space_maximum_diameter_largest_pocket",
			"11627-7" => "ValueQuantity~fetal_amniotic_fluid_index_sum_derived_us",
			"12130-1" => "ValueString~fetal_narrative_interpretation_study_observation",
			"11995-8" => "ValueString~narrative_placenta_position_us",
			"12116-0" => "ValueString~fetal_umbilical_cord_study_observation_us",
			"14959-1" => "ValueString~microalbumin_creatinine_ratio_urine",
			"58452-4" => "ValueString~hepatitis_b_virus_surface_ag_volume_in_serum",
			"16128-1" => "ValueString~hepatitis_c_virus_surface_ag_volume_in_serum_active",
			"21333-0" => "ValueString~hiv_1_rna_volume_serum",
			"20507-0" => "ValueString~reagin_ab_presence_serum",
			"22587-0" => "ValueString~treponema_pallidum_ab_presence_serum",
			"40732-0" => "ValueString~hiv_1_lgg_ab_presence_serum_immunoblot",
			"22497-2" => "ValueString~rubella_virus_ab_titer_serum",
			"72607-5" => "ValueString~streptococcus_agalactiae_presence_vag_rectum",
			"45084-1" => "ValueString~chlamydia_trachomatis_dna_presence_vaginal_fluid",
			"718-7" => "ValueString~hemoglobin_mass_volume_in_blood",
			"26515-7" => "ValueString~platelets_volume_in_blood",
			"883-9" => "ValueString~abo_group_type_in_blood",
			"10331-7" => "ValueString~rh_type_in_blood",
			"890-4" => "ValueString~blood_group_antibody_presence_serum_plasma",
			"2885-2" => "ValueString~protein_mass_volume_serum_plasma",
			"1751-7" => "ValueString~albumin_mass_volume_serum_plasma",
			"2160-0" => "ValueString~creatinine_mass_volume_serum_plasma",
			"3094-0" => "ValueString~urea_nitrogen_mass_volume_serum_plasma",
			"69405-9" => "ValueString~glomerular_filtration_rate_in_serum_plasma_blood",
			"1920-8" => "ValueString~aspartate_aminotransferase_activity_volume_in_serum_plasma",
			"1742-6" => "ValueString~urate_mass_volume_in_serum_plasma",
			"2093-3" => "ValueString~cholesterol_mass_volume_in_serum_plasma",
			"2089-1" => "ValueString~cholesterol_in_ldl_mass_volume_in_serum_plasma",
			"2085-9" => "ValueString~cholesterol_in_hdl_mass_volume_in_serum_plasma",
			"2571-8" => "ValueString~triglyceride_mass_volume_in_serum_plasma",
			"4548-4" => "ValueString~hemoglobin_a1c_total_in_blood",
			"13873-5" => "ValueString~albumin_glycated_total_in_blood",
			"13926-1" => "ValueString~glutamate_decarboxylase_units_volume_in_serum_plasma",
			"1986-9" => "ValueString~c_peptide_mass_volume_in_serum_plasma",
			"41652-9" => "ValueString~glucose_mass_volume_in_venous_blood",
			"14979-9" => "ValueString~aptt_in_platetelet_poor_plasma_by_coagulation_assay",
			"3016-3" => "ValueString~thyrotropin_units_volume_in_serum_plasma",
			"3051-0" => "ValueString~triiodothyronine_mass_volume_in_serum_plasma",
			"3024-7" => "ValueString~thyroxine_mass_volume_in_serum_plasma",
			"18332-7"=> "ValueString~thyroperoxidase_lgg_ab_units_volume_in_serum_plasma",
			"56635-6"=> "ValueString~thyroglobulin_lgg_ab_units_volume_in_serum_plasma",
			"5385-0"=> "ValueString~thyrotropin_receptor_ab_units_volume_in_serum_plasma",
			"30166-3"=> "ValueString~thyroid_stimulating_immunoglobulins_actual_normal_in_serum",
			"1547-9" => "ValueString~glucose_mass_volume_in_serum_plasma_baseline",
			"1527-1" => "ValueString~glucose_mass_volume_in_serum_plasma_30_minutes_post_75g_glucose_po",
			"1507-3" => "ValueString~glucose_mass_volume_in_serum_plasma_1_hour_post_75g_glucose_po",
			"1518-0" => "ValueString~glucose_mass_volume_in_serum_plasma_2_hours_post_75g_glucose_po",
			"1570-1" => "ValueString~insulin_mass_volume_in_serum_plasma_baseline",
			"9307-0" => "ValueString~insulin_mass_volume_in_serum_plasma_30_minutes_post_75g_glucose_po",
			"1561-0"=> "ValueString~insulin_mass_volume_in_serum_plasma_1_hour_post_75g_glucose_po",
			"1564-4"=> "ValueString~insulin_mass_volume_in_serum_plasma_2_hours_post_75g_glucose_po",
			"20441-2"=> "ValueString~glucose_mass_volume_in_serum_plasma_post_50g_glucose",
			"1504-0" => "ValueString~glucose_mass_volume_in_serum_plasma_1_hour_post_50g_glucose_po",
			"41024-1"=> "ValueString~glucose_mass_volume_in_serum_plasma_2_hours_post_50g_glucose_po_active",
			"33717-0" => "ValueString~cervical_or_vaginal_cytology_study",

			"73813-8" => "ValueString~characteristics_of_labor_and_delivery",
			"69461-2" => "ValueQuantity~mothers_body_weight_at_delivery",
			"55103-6" => "ValueQuantity~estimated_blood_loss",
			"1322-7" => "ValueQuantity~transfusion_volume",
			"73760-1" => "ValueString~if_cesarean_trial_of_labor_attempted",
			"80565-5" => "ValueString~medication_administration_record",
			"73781-7" => "ValueString~maternal_morbidity",
			"89223-2" => "ValueString~gynecology_procedure_note",
			// "55281-0" => "ValueQuantity~no_of_fetuses",
			"73771-8" => "ValueString~birth_order",
			"97103-6" => "ValueString~zygosity_type_in_fetuses_by_molecular_genetics",
			"57071-3" => "ValueString~obstetric_delivery_method",
			"PX241301060100" => "ValueString~mode_delivery_induce_labor_reason",
			"72155-5" => "ValueString~position_in_womb_fetus_rhea",
			// "32396-4" => "ValueString~cervical_or_vaginal_cytology_study", to be asked
			"28646-8" => "ValueQuantity~ph_of_arterical_cord_blood",
			"47995-6" => "ValueQuantity~glucose_moles_volume_in_cord_blood",
			"28638-5" => "ValueQuantity~base_excess_in_arterial_cord_calculation",
			"28644-3" => "ValueQuantity~carbon_dioxide_partial_pressure_in_arterial_cord_blood",
			"28648-4" => "ValueQuantity~oxygen_partial_pressure_in_arterial_cord_blood",
			"76689-9" => "ValueString~sex_assigned_at_birth",
			"89269-5" => "ValueQuantity~body_height_measured_at_birth",
			"8290-9" => "ValueQuantity~head_occipital_frontal_circumference_at_birth_by_tape_measure",
			"8279-2" => "ValueQuantity~chest_circumference_at_nipple_line",
			"9272-6" => "ValueQuantity~1_minute_apgar_score",
			"9274-2" => "ValueQuantity~5_minute_apgar_score",
			"92272-4" => "ValueString~stayed_in_intensive_care",
			"73812-0" => "ValueString~abnormal_conditions_of_newborn",
			"57075-4" => "ValueString~newborn_delivery_information",
			"32486-3" => "ValueString~physical_findings_uterus",
			"LP7269-6" => "ValueString~genital_lochia",
			"32422-8" => "ValueString~physical_findings_breast",
			// "55284-4" => "ValueQuantity~systolic", need to be confirmed
			"2887-8" => "ValueString~protein_presence_in_urine",
			"2349-9" => "ValueString~glucose_presence_in_urine",
			"57076-2" => "ValueString~postpartum_hospitalization_treatment_narrative",
			"99046-5" => "ValueQuantity~total_score_epds",
			// "54089-8" => "ValueString~cervical_or_vaginal_cytology_study", no data in excel or json
			"69969-4" => "ValueString~newborn_screening_report_overall_laboratory_comment",
			"54106-0" => "ValueString~newborn_hearing_screen_method_left_ear",
			"54109-4" => "ValueString~newborn_hearing_screen_method_right_ear",
			"81079-6" => "ValueQuantity~vitamin_k_intake_24_hour_measured",
			"42851-6" => "ValueString~infant_feeding_pattern_impairment",
			"78375-3" => "ValueString~discharge_diagnosis_narrative",
			"67703-9" => "ValueString~other_infant_factors_that_affect_newborn_screening",
			// "73806-2" => "ValueString~newborn_age_in_hours", lacking data in sheet
			"80442-7" => "ValueString~breast_milk_intake_panel",
			"10206-1" => "ValueString~physical_findings_of_skin_narrative",
			"57724-7" => "ValueString~newborn_screening_short_narrative_summary",
			
			// "76427-4" => "EffectiveDateTime~visit_date" - needs to be confirmed

		);
		$this->loincFamilyHistoryList = array(
			"85658-3" => "Text~partner_occupation",
			"89060-8" => "Text~partner_contact_no",
			"883-9" => "Text~partner_abo_group_blood",
			"10331-7" => "Text~partner_rh_blood",
			"72166-2" => "Text~partner_smoking_status",
			"11331-6" => "Text~partner_history_alcohol_use",
		);
	}
}
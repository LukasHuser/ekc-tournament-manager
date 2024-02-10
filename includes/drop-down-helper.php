<?php

/**
 * Helper class for html select elements (drop down lists)
 */
class Ekc_Drop_Down_Helper {

	const SELECTION_NONE	= "selection_none";

	const TEAM_SIZE_1 	= "1vs1";
	const TEAM_SIZE_2	= "2vs2";
	const TEAM_SIZE_2plus	= "2+vs2+";
	const TEAM_SIZE_3 	= "3vs3";
	const TEAM_SIZE_3plus	= "3+vs3+";
	const TEAM_SIZE_6	= "6vs6";

	const TEAM_SIZE		= array( 
		Ekc_Drop_Down_Helper::TEAM_SIZE_1, 
		Ekc_Drop_Down_Helper::TEAM_SIZE_2,
		Ekc_Drop_Down_Helper::TEAM_SIZE_2plus, 
		Ekc_Drop_Down_Helper::TEAM_SIZE_3, 
		Ekc_Drop_Down_Helper::TEAM_SIZE_3plus, 
		Ekc_Drop_Down_Helper::TEAM_SIZE_6,
	);

	const TOURNAMENT_SYSTEM_KO			= "elimination"; // elimination bracket only
	const TOURNAMENT_SYSTEM_GROUP_KO	= "group+elimination"; // group stage + elimination bracket
	const TOURNAMENT_SYSTEM_SWISS_KO	= "swiss+elimination"; // swiss system + elimination bracket

	const TOURNAMENT_SYSTEM	= array(
		Ekc_Drop_Down_Helper::TOURNAMENT_SYSTEM_KO,
		Ekc_Drop_Down_Helper::TOURNAMENT_SYSTEM_GROUP_KO,
		Ekc_Drop_Down_Helper::TOURNAMENT_SYSTEM_SWISS_KO,
	);

	const ELIMINATION_BRACKET_1_2 = "semifinals";
	const ELIMINATION_BRACKET_1_4 = "1/4-finals";
	const ELIMINATION_BRACKET_1_8 = "1/8-finals";
	const ELIMINATION_BRACKET_1_16 = "1/16-finals";

	const ELIMINATION_BRACKET = array(
		Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_2,
		Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_4,
		Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_8,
		Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_16
	);

	const TOURNAMENT_STAGE_KO		= "elimination";
	const TOURNAMENT_STAGE_SWISS	= "swiss";
	const TOURNAMENT_STAGE_GROUP	= "group";

	const FILTER_ALL	= 'all';
	const FILTER_YES	= 'yes';
	const FILTER_NO		= 'no';

	const FILTER_ALL_YES_NO = array(
		Ekc_Drop_Down_Helper::FILTER_ALL,
		Ekc_Drop_Down_Helper::FILTER_YES,
		Ekc_Drop_Down_Helper::FILTER_NO
	);

	public static function empty_if_none( $selected_key ) {
		if ( $selected_key === Ekc_Drop_Down_Helper::SELECTION_NONE) {
			return '';
		}
		return $selected_key;
	}

	public static function none_if_empty( $selected_key ) {
		if ( ! $selected_key ) {
			return Ekc_Drop_Down_Helper::SELECTION_NONE;
		}
		return $selected_key;
	}

	public static function team_size_drop_down($id, $selected_key) {
		Ekc_Drop_Down_Helper::drop_down($id, $selected_key, Ekc_Drop_Down_Helper::TEAM_SIZE, null, "ekc-selectmenu");
	}

	public static function tournament_system_drop_down($id, $selected_key) {
		Ekc_Drop_Down_Helper::drop_down($id, $selected_key, Ekc_Drop_Down_Helper::TOURNAMENT_SYSTEM, null, "ekc-selectmenu");
	}

	public static function elimination_bracket_drop_down($id, $selected_key) {
		Ekc_Drop_Down_Helper::drop_down($id, $selected_key, Ekc_Drop_Down_Helper::ELIMINATION_BRACKET, null, "ekc-selectmenu");
	}

	public static function filter_yes_no_drop_down( $id, $selected_key, $all_text) {
		$values = array($all_text, 'Yes', 'No');
		Ekc_Drop_Down_Helper::drop_down( $id, $selected_key, Ekc_Drop_Down_Helper::FILTER_ALL_YES_NO, $values, '', false );
	}

	private static function drop_down($id, $selected_key, $all_keys, $all_values, $css_class, $include_empty = true ) {
		if ( ! $all_values ) {
			$all_values = $all_keys;
		}
		$is_empty_selected = ! $selected_key || strval( $selected_key ) === Ekc_Drop_Down_Helper::SELECTION_NONE;
?>
<select name="<?php _e($id) ?>" id="<?php _e($id) ?>" class="<?php _e($css_class) ?>" >
<?php if ( $include_empty ) { ?>
	<option <?php $is_empty_selected ? _e("selected") : _e("") ?> value="<?php esc_html_e( Ekc_Drop_Down_Helper::SELECTION_NONE ) ?>"></option>
<?php }	
	for( $i = 0; $i < count($all_keys); $i++) { 
		if ( $all_keys[$i] !== Ekc_Drop_Down_Helper::SELECTION_NONE ) { ?>
	<option <?php strval( $selected_key ) === strval( $all_keys[$i] ) ? _e("selected") : _e("") ?> value="<?php esc_html_e($all_keys[$i]) ?>"><?php esc_html_e($all_values[$i]) ?></option>
<?php	}	
	} ?>
</select>

<?php
	}

// ========================================================== //
// Teams

public static function teams_drop_down_data( $tournament_id ) {
	$db = new Ekc_Database_Access();
	// get all active teams, ignoring the waiting list or maximum number of teams for the tournament
	// teams on the waiting list must have been set to inactive before starting the tournament
	$teams = $db->get_active_teams( $tournament_id, 0, 'asc', true ); 
	$result = array();

	$helper = new Ekc_Swiss_System_Helper();
	$tournament = $db->get_tournament_by_id( $tournament_id );
	if ( $helper->is_pitch_limit_mode( $tournament ) ) {
		$teams = array_merge( $teams, $helper->get_byes_for_pitch_limit_mode( $tournament ) );
	}
	else if ( count($teams) % 2 == 1 ) {
		$bye = new Ekc_Team();
		$bye->set_team_id( Ekc_Team::TEAM_ID_BYE );
		$bye->set_name( 'BYE' );
		$teams[] = $bye;
	}

	?>
	<script>
		var ekc = ekc || {};
		ekc.teamsDropDownData = { <?php
			foreach ( $teams as $team ) {
				_e('"' . esc_html( $team->get_team_id() ) . '" : "' . esc_html( $team->get_name() ) . '", ');
				$result[$team->get_team_id()] = $team->get_name();
			}?>
		};
	</script>
	<?php
	return $result;
}

public static function teams_drop_down($id, $selected_key, $selected_value ) {
	$keys = array( $selected_key );
	$values = array( $selected_value );
	Ekc_Drop_Down_Helper::drop_down( $id, $selected_key, $keys, $values, "ekc-teams-combobox" );
}


// ========================================================= //
// Country

	const COUNTRY_AT = 'at';
	const COUNTRY_BE = 'be';
	const COUNTRY_HR = 'hr';
	const COUNTRY_CZ = 'cz';
	const COUNTRY_FR = 'fr';
	const COUNTRY_DE = 'de';
	const COUNTRY_IE = 'ie';
	const COUNTRY_IT = 'it';
	const COUNTRY_LU = 'lu';
	const COUNTRY_NL = 'nl';
	const COUNTRY_NO = 'no';
	const COUNTRY_PL = 'pl';
	const COUNTRY_ES = 'es';
	const COUNTRY_SK = 'sk';
	const COUNTRY_SE = 'se';
	const COUNTRY_CH = 'ch';
	const COUNTRY_GB = 'gb';
	
	const COUNTRY_BG = 'bg';
	const COUNTRY_DK = 'dk';
	const COUNTRY_EE = 'ee';
	const COUNTRY_FI = 'fi';
	const COUNTRY_GR = 'gr';
	const COUNTRY_HU = 'hu';
	const COUNTRY_LI = 'li';
	const COUNTRY_LT = 'lt';
	const COUNTRY_LV = 'lv';
	const COUNTRY_PT = 'pt';
	const COUNTRY_RO = 'ro';
	const COUNTRY_SI = 'si';
	const COUNTRY_TR = 'tr';
	const COUNTRY_US = 'us';

	const COUNTRY_UNDEFINED = 'eu'; // use 'eu' as iso code, this corresponds to the european flag in the drop down

	const COUNTRY_COMMON = array(
		Ekc_Drop_Down_Helper::COUNTRY_AT => 'Austria',
		Ekc_Drop_Down_Helper::COUNTRY_BE => 'Belgium',
		Ekc_Drop_Down_Helper::COUNTRY_HR => 'Croatia',
		Ekc_Drop_Down_Helper::COUNTRY_CZ => 'Czech Republic',
		Ekc_Drop_Down_Helper::COUNTRY_FR => 'France',
		Ekc_Drop_Down_Helper::COUNTRY_DE => 'Germany',
		Ekc_Drop_Down_Helper::COUNTRY_IE => 'Ireland',
		Ekc_Drop_Down_Helper::COUNTRY_IT => 'Italy',
		Ekc_Drop_Down_Helper::COUNTRY_LU => 'Luxembourg',
		Ekc_Drop_Down_Helper::COUNTRY_NL => 'Netherlands',
		Ekc_Drop_Down_Helper::COUNTRY_NO => 'Norway',
		Ekc_Drop_Down_Helper::COUNTRY_PL => 'Poland',
		Ekc_Drop_Down_Helper::COUNTRY_ES => 'Spain',
		Ekc_Drop_Down_Helper::COUNTRY_SK => 'Slovakia',
		Ekc_Drop_Down_Helper::COUNTRY_SE => 'Sweden',
		Ekc_Drop_Down_Helper::COUNTRY_CH => 'Switzerland',
		Ekc_Drop_Down_Helper::COUNTRY_GB => 'United Kingdom',
	);
	
	const COUNTRY_OTHER = array(
		Ekc_Drop_Down_Helper::COUNTRY_BG => 'Bulgaria',
		Ekc_Drop_Down_Helper::COUNTRY_DK => 'Denmark',
		Ekc_Drop_Down_Helper::COUNTRY_EE => 'Estonia',
		Ekc_Drop_Down_Helper::COUNTRY_FI => 'Finland',
		Ekc_Drop_Down_Helper::COUNTRY_GR => 'Greece',
		Ekc_Drop_Down_Helper::COUNTRY_HU => 'Hungary',
		Ekc_Drop_Down_Helper::COUNTRY_LV => 'Latvia',
		Ekc_Drop_Down_Helper::COUNTRY_LI => 'Liechtenstein',
		Ekc_Drop_Down_Helper::COUNTRY_LT => 'Lithuania',
		Ekc_Drop_Down_Helper::COUNTRY_PT => 'Portugal',
		Ekc_Drop_Down_Helper::COUNTRY_RO => 'Romania',
		Ekc_Drop_Down_Helper::COUNTRY_SI => 'Slovenia',
		Ekc_Drop_Down_Helper::COUNTRY_TR => 'Turkey',
		Ekc_Drop_Down_Helper::COUNTRY_US => 'USA',
	);

	const COUNTRY_SPECIAL = array(
		Ekc_Drop_Down_Helper::COUNTRY_UNDEFINED => 'Other',
	);


	public static function country_small_drop_down($id, $selected_value = "") {
		$is_empty_selected = ! $selected_value || strval( $selected_value ) === Ekc_Drop_Down_Helper::SELECTION_NONE;
?>
<select name="<?php _e($id) ?>" id="<?php _e($id) ?>" class="ekc-country-selectmenu f16" >
	<option <?php $is_empty_selected ? _e("selected") : _e("") ?> ></option>
<?php foreach(Ekc_Drop_Down_Helper::COUNTRY_COMMON as $key => $value) { ?>
	<option class="flag-icon <?php _e('flag-icon-' . $key) ?>" <?php $selected_value === $key ? _e("selected") : _e("") ?> value="<?php _e($key) ?>"><?php _e($value) ?></option>
<?php } ?>
	<option disabled></option>
<?php foreach(Ekc_Drop_Down_Helper::COUNTRY_OTHER as $key => $value) { ?>
	<option class="flag-icon <?php _e('flag-icon-' . $key) ?>" <?php $selected_value === $key ? _e("selected") : _e("") ?> value="<?php _e($key) ?>"><?php _e($value) ?></option>
<?php } ?>
	<option disabled></option>
	<option class="flag-icon <?php _e('flag-icon-' . Ekc_Drop_Down_Helper::COUNTRY_UNDEFINED) ?>" value="<?php _e(Ekc_Drop_Down_Helper::COUNTRY_UNDEFINED) ?>"><?php _e(Ekc_Drop_Down_Helper::COUNTRY_SPECIAL[Ekc_Drop_Down_Helper::COUNTRY_UNDEFINED]) ?></option>
</select>

<?php
	}

	public static function filter_country_small_drop_down( $id, $selected_key ) {
		?>
		<select name="<?php _e($id) ?>" id="<?php _e($id) ?>" >
		<option <?php $selected_key === Ekc_Drop_Down_Helper::FILTER_ALL ? _e("selected") : _e("") ?> value="<?php _e(Ekc_Drop_Down_Helper::FILTER_ALL) ?>">All countries</option>
		<?php 
		foreach(Ekc_Drop_Down_Helper::COUNTRY_COMMON as $key => $value) { ?>
			<option <?php $selected_key === $key ? _e("selected") : _e("") ?> value="<?php _e($key) ?>"><?php _e($value) ?></option>
		<?php
		} ?>
		</select>
		<?php		
	}

	public static function country_drop_down() {
?>
<select>
	<option value="AF">Afghanistan</option>
	<option value="AX">Åland Islands</option>
	<option value="AL">Albania</option>
	<option value="DZ">Algeria</option>
	<option value="AS">American Samoa</option>
	<option value="AD">Andorra</option>
	<option value="AO">Angola</option>
	<option value="AI">Anguilla</option>
	<option value="AQ">Antarctica</option>
	<option value="AG">Antigua and Barbuda</option>
	<option value="AR">Argentina</option>
	<option value="AM">Armenia</option>
	<option value="AW">Aruba</option>
	<option value="AU">Australia</option>
	<option value="AT">Austria</option>
	<option value="AZ">Azerbaijan</option>
	<option value="BS">Bahamas</option>
	<option value="BH">Bahrain</option>
	<option value="BD">Bangladesh</option>
	<option value="BB">Barbados</option>
	<option value="BY">Belarus</option>
	<option value="BE">Belgium</option>
	<option value="BZ">Belize</option>
	<option value="BJ">Benin</option>
	<option value="BM">Bermuda</option>
	<option value="BT">Bhutan</option>
	<option value="BO">Bolivia, Plurinational State of</option>
	<option value="BQ">Bonaire, Sint Eustatius and Saba</option>
	<option value="BA">Bosnia and Herzegovina</option>
	<option value="BW">Botswana</option>
	<option value="BV">Bouvet Island</option>
	<option value="BR">Brazil</option>
	<option value="IO">British Indian Ocean Territory</option>
	<option value="BN">Brunei Darussalam</option>
	<option value="BG">Bulgaria</option>
	<option value="BF">Burkina Faso</option>
	<option value="BI">Burundi</option>
	<option value="KH">Cambodia</option>
	<option value="CM">Cameroon</option>
	<option value="CA">Canada</option>
	<option value="CV">Cape Verde</option>
	<option value="KY">Cayman Islands</option>
	<option value="CF">Central African Republic</option>
	<option value="TD">Chad</option>
	<option value="CL">Chile</option>
	<option value="CN">China</option>
	<option value="CX">Christmas Island</option>
	<option value="CC">Cocos (Keeling) Islands</option>
	<option value="CO">Colombia</option>
	<option value="KM">Comoros</option>
	<option value="CG">Congo</option>
	<option value="CD">Congo, the Democratic Republic of the</option>
	<option value="CK">Cook Islands</option>
	<option value="CR">Costa Rica</option>
	<option value="CI">Côte d'Ivoire</option>
	<option value="HR">Croatia</option>
	<option value="CU">Cuba</option>
	<option value="CW">Curaçao</option>
	<option value="CY">Cyprus</option>
	<option value="CZ">Czech Republic</option>
	<option value="DK">Denmark</option>
	<option value="DJ">Djibouti</option>
	<option value="DM">Dominica</option>
	<option value="DO">Dominican Republic</option>
	<option value="EC">Ecuador</option>
	<option value="EG">Egypt</option>
	<option value="SV">El Salvador</option>
	<option value="GQ">Equatorial Guinea</option>
	<option value="ER">Eritrea</option>
	<option value="EE">Estonia</option>
	<option value="ET">Ethiopia</option>
	<option value="FK">Falkland Islands (Malvinas)</option>
	<option value="FO">Faroe Islands</option>
	<option value="FJ">Fiji</option>
	<option value="FI">Finland</option>
	<option value="FR">France</option>
	<option value="GF">French Guiana</option>
	<option value="PF">French Polynesia</option>
	<option value="TF">French Southern Territories</option>
	<option value="GA">Gabon</option>
	<option value="GM">Gambia</option>
	<option value="GE">Georgia</option>
	<option value="DE">Germany</option>
	<option value="GH">Ghana</option>
	<option value="GI">Gibraltar</option>
	<option value="GR">Greece</option>
	<option value="GL">Greenland</option>
	<option value="GD">Grenada</option>
	<option value="GP">Guadeloupe</option>
	<option value="GU">Guam</option>
	<option value="GT">Guatemala</option>
	<option value="GG">Guernsey</option>
	<option value="GN">Guinea</option>
	<option value="GW">Guinea-Bissau</option>
	<option value="GY">Guyana</option>
	<option value="HT">Haiti</option>
	<option value="HM">Heard Island and McDonald Islands</option>
	<option value="VA">Holy See (Vatican City State)</option>
	<option value="HN">Honduras</option>
	<option value="HK">Hong Kong</option>
	<option value="HU">Hungary</option>
	<option value="IS">Iceland</option>
	<option value="IN">India</option>
	<option value="ID">Indonesia</option>
	<option value="IR">Iran, Islamic Republic of</option>
	<option value="IQ">Iraq</option>
	<option value="IE">Ireland</option>
	<option value="IM">Isle of Man</option>
	<option value="IL">Israel</option>
	<option value="IT">Italy</option>
	<option value="JM">Jamaica</option>
	<option value="JP">Japan</option>
	<option value="JE">Jersey</option>
	<option value="JO">Jordan</option>
	<option value="KZ">Kazakhstan</option>
	<option value="KE">Kenya</option>
	<option value="KI">Kiribati</option>
	<option value="KP">Korea, Democratic People's Republic of</option>
	<option value="KR">Korea, Republic of</option>
	<option value="KW">Kuwait</option>
	<option value="KG">Kyrgyzstan</option>
	<option value="LA">Lao People's Democratic Republic</option>
	<option value="LV">Latvia</option>
	<option value="LB">Lebanon</option>
	<option value="LS">Lesotho</option>
	<option value="LR">Liberia</option>
	<option value="LY">Libya</option>
	<option value="LI">Liechtenstein</option>
	<option value="LT">Lithuania</option>
	<option value="LU">Luxembourg</option>
	<option value="MO">Macao</option>
	<option value="MK">Macedonia, the former Yugoslav Republic of</option>
	<option value="MG">Madagascar</option>
	<option value="MW">Malawi</option>
	<option value="MY">Malaysia</option>
	<option value="MV">Maldives</option>
	<option value="ML">Mali</option>
	<option value="MT">Malta</option>
	<option value="MH">Marshall Islands</option>
	<option value="MQ">Martinique</option>
	<option value="MR">Mauritania</option>
	<option value="MU">Mauritius</option>
	<option value="YT">Mayotte</option>
	<option value="MX">Mexico</option>
	<option value="FM">Micronesia, Federated States of</option>
	<option value="MD">Moldova, Republic of</option>
	<option value="MC">Monaco</option>
	<option value="MN">Mongolia</option>
	<option value="ME">Montenegro</option>
	<option value="MS">Montserrat</option>
	<option value="MA">Morocco</option>
	<option value="MZ">Mozambique</option>
	<option value="MM">Myanmar</option>
	<option value="NA">Namibia</option>
	<option value="NR">Nauru</option>
	<option value="NP">Nepal</option>
	<option value="NL">Netherlands</option>
	<option value="NC">New Caledonia</option>
	<option value="NZ">New Zealand</option>
	<option value="NI">Nicaragua</option>
	<option value="NE">Niger</option>
	<option value="NG">Nigeria</option>
	<option value="NU">Niue</option>
	<option value="NF">Norfolk Island</option>
	<option value="MP">Northern Mariana Islands</option>
	<option value="NO">Norway</option>
	<option value="OM">Oman</option>
	<option value="PK">Pakistan</option>
	<option value="PW">Palau</option>
	<option value="PS">Palestinian Territory, Occupied</option>
	<option value="PA">Panama</option>
	<option value="PG">Papua New Guinea</option>
	<option value="PY">Paraguay</option>
	<option value="PE">Peru</option>
	<option value="PH">Philippines</option>
	<option value="PN">Pitcairn</option>
	<option value="PL">Poland</option>
	<option value="PT">Portugal</option>
	<option value="PR">Puerto Rico</option>
	<option value="QA">Qatar</option>
	<option value="RE">Réunion</option>
	<option value="RO">Romania</option>
	<option value="RU">Russian Federation</option>
	<option value="RW">Rwanda</option>
	<option value="BL">Saint Barthélemy</option>
	<option value="SH">Saint Helena, Ascension and Tristan da Cunha</option>
	<option value="KN">Saint Kitts and Nevis</option>
	<option value="LC">Saint Lucia</option>
	<option value="MF">Saint Martin (French part)</option>
	<option value="PM">Saint Pierre and Miquelon</option>
	<option value="VC">Saint Vincent and the Grenadines</option>
	<option value="WS">Samoa</option>
	<option value="SM">San Marino</option>
	<option value="ST">Sao Tome and Principe</option>
	<option value="SA">Saudi Arabia</option>
	<option value="SN">Senegal</option>
	<option value="RS">Serbia</option>
	<option value="SC">Seychelles</option>
	<option value="SL">Sierra Leone</option>
	<option value="SG">Singapore</option>
	<option value="SX">Sint Maarten (Dutch part)</option>
	<option value="SK">Slovakia</option>
	<option value="SI">Slovenia</option>
	<option value="SB">Solomon Islands</option>
	<option value="SO">Somalia</option>
	<option value="ZA">South Africa</option>
	<option value="GS">South Georgia and the South Sandwich Islands</option>
	<option value="SS">South Sudan</option>
	<option value="ES">Spain</option>
	<option value="LK">Sri Lanka</option>
	<option value="SD">Sudan</option>
	<option value="SR">Suriname</option>
	<option value="SJ">Svalbard and Jan Mayen</option>
	<option value="SZ">Swaziland</option>
	<option value="SE">Sweden</option>
	<option value="CH">Switzerland</option>
	<option value="SY">Syrian Arab Republic</option>
	<option value="TW">Taiwan, Province of China</option>
	<option value="TJ">Tajikistan</option>
	<option value="TZ">Tanzania, United Republic of</option>
	<option value="TH">Thailand</option>
	<option value="TL">Timor-Leste</option>
	<option value="TG">Togo</option>
	<option value="TK">Tokelau</option>
	<option value="TO">Tonga</option>
	<option value="TT">Trinidad and Tobago</option>
	<option value="TN">Tunisia</option>
	<option value="TR">Turkey</option>
	<option value="TM">Turkmenistan</option>
	<option value="TC">Turks and Caicos Islands</option>
	<option value="TV">Tuvalu</option>
	<option value="UG">Uganda</option>
	<option value="UA">Ukraine</option>
	<option value="AE">United Arab Emirates</option>
	<option value="GB">United Kingdom</option>
	<option value="US">United States</option>
	<option value="UM">United States Minor Outlying Islands</option>
	<option value="UY">Uruguay</option>
	<option value="UZ">Uzbekistan</option>
	<option value="VU">Vanuatu</option>
	<option value="VE">Venezuela, Bolivarian Republic of</option>
	<option value="VN">Viet Nam</option>
	<option value="VG">Virgin Islands, British</option>
	<option value="VI">Virgin Islands, U.S.</option>
	<option value="WF">Wallis and Futuna</option>
	<option value="EH">Western Sahara</option>
	<option value="YE">Yemen</option>
	<option value="ZM">Zambia</option>
	<option value="ZW">Zimbabwe</option>
</select>

<?php
	}
}


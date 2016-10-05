<?php

class PersonalCalendar{

 const HEBREW_DATE = 'hebrew_date';
 const SUNSET_TIME = 'sunset_time';
 const CANDLE_TIME = 'candle_time';
 const JEWISH_HOLIDAYS = 'jewish_holidays';
 const ROSH_CHODESH = 'rosh_chodesh'; 
 
 const HEBREW_MONTH_TISHREI = "1";
	const HEBREW_MONTH_HESHVAN = "2";
	const HEBREW_MONTH_KISLEV = "3";
	const HEBREW_MONTH_TEVET = "4";
	const HEBREW_MONTH_SHEVAT = "5";
	const HEBREW_MONTH_ADAR = "6";
	const HEBREW_MONTH_ADAR_2 = "7";
	const HEBREW_MONTH_NISAN = "8";
	const HEBREW_MONTH_IYYAR = "9";
	const HEBREW_MONTH_SIVAN = "10";
	const HEBREW_MONTH_TAMUZ = "11";
	const HEBREW_MONTH_AV    = "12";
	const HEBREW_MONTH_ELUL  = "13";
	


function util_get_hebrew_month_name( &$julian_date, &$hebrew_date){
		/* Use month spellings from HebCal.com for all months.
		
            */
		
		list($hebrewMonth, $hebrewDay, $hebrewYear) = split('/',$hebrew_date);
		if($hebrewMonth == $this::HEBREW_MONTH_TISHREI){
			return ts("Tishrei");	
		}else if($hebrewMonth == $this::HEBREW_MONTH_HESHVAN){
			return ts("Cheshvan");
		}else if($hebrewMonth == $this::HEBREW_MONTH_KISLEV){
			return ts("Kislev");
		}else if($hebrewMonth == $this::HEBREW_MONTH_TEVET){
			return ts("Tevet");
		}else if($hebrewMonth == $this::HEBREW_MONTH_SHEVAT){
			return ts("Sh'vat");
		}else if( $hebrewMonth == $this::HEBREW_MONTH_ADAR ){
			/* Its Adar or AdarI */
			
			$hebrew_leap_year = $this->is_hebrew_year_leap_year($hebrewYear);
			if( $hebrew_leap_year){
				return ts("Adar I");
			}else{
				return ts("Adar");
			}
		}else if($hebrewMonth == $this::HEBREW_MONTH_ADAR_2){
			return ts("Adar II");
		}else if($hebrewMonth == $this::HEBREW_MONTH_NISAN){
			return ts("Nisan");
		}else if($hebrewMonth == $this::HEBREW_MONTH_IYYAR){
			return ts("Iyyar");
		}else if($hebrewMonth == $this::HEBREW_MONTH_SIVAN){
			return ts("Sivan");
		}else if($hebrewMonth == $this::HEBREW_MONTH_TAMUZ){
			return ts("Tamuz");
		}else if($hebrewMonth == $this::HEBREW_MONTH_AV){
			return ts("Av");
		}else if($hebrewMonth == $this::HEBREW_MONTH_ELUL){
			return ts("Elul");
		}else{
			 
			 //Old logic: just use the PHP function to get the month name. */
			//return jdmonthname($julian_date,4);
			
			// something went wrong,
			return "Unknown Month";
		}
	}


/***  This function is typically called by the FountainTribe version of the Drupal7 calendar module.  */
function getDrupal7CalendarMonthCell($raw_date, $granularity){
  
  $cell = array('data' => "");

  $iyear = '';
  $imonth = '';
  $iday = ''; 
  
  $personal_occasion_raw = ""; 
  
  // Split out year, month, and date from format used by Drupal monthly calendar. 
  
   list($iyear, $imonth, $iday) = split('[-]', $raw_date);
 
  
  
  $tmpHTMLcell = "";
   
  
  if(self::showCalendarOption(self::HEBREW_DATE) ){
  	$heb_date_str = "<span class='fountaintribe_hebrew_date'>".self::get_heb_date($iyear, $imonth, $iday)."</span>";
  }else{
  	$heb_date_str = "<span class='fountaintribe_hebrew_date'></span>";
  }
  
  
  if(self::showCalendarOption(self::CANDLE_TIME) ){
  	 $candle_time_str = "<span class='fountaintribe_candle_time'>".self::get_candle_time($iyear, $imonth, $iday)."</span>";
  }else{
  	$candle_time_str = "<span class='fountaintribe_candle_time'></span>";
  }
  
  if(self::showCalendarOption(self::SUNSET_TIME) ){
  	 $sunset_time_str = "<span class='fountaintribe_candle_time'>".self::get_sunset_time($iyear, $imonth, $iday)."</span>";
  }else{
  	$sunset_time_str = "<span class='fountaintribe_candle_time'></span>";
  }
  
   if(self::showCalendarOption(self::JEWISH_HOLIDAYS) ){
  	// TODO: include span tag for Jewish holidays in this function, do not rely on other function to do it. 
  	$jewish_holiday_str = self::get_holiday_name_for_cal($iyear, $imonth, $iday) ; 
  
  }else{
  	$jewish_holiday_str = "";
  }
  
  	if(self::showCalendarOption(self::ROSH_CHODESH) ){
  		$rosh_hodesh_html_str = self::get_rosh_hodesh_html_str($iyear, $imonth, $iday);
  	}else{
  		$rosh_hodesh_html_str = "";
  	}
  	
  	
  	$personal_occasion_raw = self::getPersonalOccasionsForCalendar($iyear, $imonth, $iday);
  	if(isset($personal_occasion_raw ) && strlen($personal_occasion_raw) > 0 ){
  	   $personal_occasion_full = "<span class='fountaintribe_occasion'>".$personal_occasion_raw."</span>";
  	
  	}else{
  		$personal_occasion_full = "<span class='fountaintribe_occasion'></span>";
  	}
  
  

   $tmpHTMLcell = $heb_date_str.$cell['data'].$personal_occasion_full.
            $candle_time_str.$sunset_time_str.$jewish_holiday_str.$rosh_hodesh_html_str  ;

   

   return $tmpHTMLcell;
}


/************************************************************************************************/
/***  This function is typically called by the FountainTribe version of the Drupal6 calendar module.  */
function getDrupalCalendarMonthCell($row, $cell){
  
  
  $raw_date = $cell['id']; 
  
  $iyear = '';
  $imonth = '';
  $iday = ''; 
  
  // Split out year, month, and date from format used by Drupal monthly calendar. 
  if (ereg("^civicrm_events-weekno", $raw_date )){
     $tmpHTMLcell = '';
     return $tmpHTMLcell;
 }else if(ereg("^calendar-", $date_parm)){
 
      	list($a, $iyear, $imonth, $iday) = split('[-]', $raw_date);
 }else{
   	list($a , $iyear, $imonth, $iday) = split('[-]', $raw_date);
   }	
  
  
  $tmpHTMLcell = "";
   
  
  if(self::showCalendarOption(self::HEBREW_DATE) ){
  	$heb_date_str = "<span class='fountaintribe_hebrew_date'>".self::get_heb_date($iyear, $imonth, $iday)."</span>";
  }
  
  if(self::showCalendarOption(self::CANDLE_TIME) ){
  	 $candle_time_str = "<span class='fountaintribe_candle_time'>".self::get_candle_time($iyear, $imonth, $iday)."</span>";
  	
  }
  if(self::showCalendarOption(self::SUNSET_TIME) ){
  	 $sunset_time_str = "<span class='fountaintribe_candle_time'>".self::get_sunset_time($iyear, $imonth, $iday)."</span>";
  
  }
  
   if(self::showCalendarOption(self::JEWISH_HOLIDAYS) ){
  	// TODO: include span tag for Jewish holidays in this function, do not rely on other function to do it. 
  	$jewish_holiday_str = self::get_holiday_name_for_cal($iyear, $imonth, $iday) ; 
  
  }
  
  	if(self::showCalendarOption(self::ROSH_CHODESH) ){
  		$rosh_hodesh_html_str = self::get_rosh_hodesh_html_str($iyear, $imonth, $iday);
  	}
  
  $tmpHTMLcell = $heb_date_str.$cell['data'].
           "<span class='fountaintribe_occasion'>".self::getPersonalOccasionsForCalendar($iyear, $imonth, $iday)."</span>".
            $candle_time_str.$sunset_time_str.$jewish_holiday_str.$rosh_hodesh_html_str  ;


   return $tmpHTMLcell;
}

/*************************************************************************************************/
function get_rosh_hodesh_html_str($iyear, $imonth, $iday){
	$rosh_hodesh_html_str = ""; 
	
 	
 	$rosh_hodesh_tmp = $this->get_rosh_hodesh_name($iyear, $imonth, $iday);
 	
 	if(strlen($rosh_hodesh_tmp) > 0 ){
  		$rosh_hodesh_html_str = "<br><span class='fountaintribe_rosh_chodesh'>".$this->get_rosh_hodesh_name($iyear, $imonth, $iday)."</span>"; 
  	}
  	
  	
  	return $rosh_hodesh_html_str; 
  }
  	
/*************************************************************************************************/
/****   This function is typically called by the FountainTribe version of the Drupal calendar module. */
function getDrupalCalendarWeekCell($day){

  $tmpHTMLcell = "";
  $iyear = '';
  $imonth = '';
  $iday = ''; 
  
  $raw_date = $day['date']; 
  list( $iyear, $imonth, $iday) = split('[-]', $raw_date);

  if(self::showCalendarOption(self::HEBREW_DATE) ){
  	 $heb_date_str = "<span class='fountaintribe_hebrew_date'>".self::get_heb_date($iyear, $imonth, $iday)."</span>";
  }	

   if(self::showCalendarOption(self::SUNSET_TIME) ){
  	 $sunset_time_str = "<span class='fountaintribe_candle_time'>".self::get_sunset_time($iyear, $imonth, $iday)."</span>";
  
  }
  
   if(self::showCalendarOption(self::JEWISH_HOLIDAYS) ){
  	// TODO: include span tag for Jewish holidays in this function, do not rely on other function to do it. 
  	 $jewish_holiday_str ="<br>".self::get_holiday_name_for_cal($iyear, $imonth, $iday) ; 
  
  }


	if(self::showCalendarOption(self::ROSH_CHODESH) ){
  		$rosh_hodesh_html_str = self::get_rosh_hodesh_html_str($iyear, $imonth, $iday);
  	}
  	

	//define("foo", "bar"); 
	//if (empty(foo)) echo "empty";
 $tmpHTMLcell = $heb_date_str.$day['datebox'].
                "<br>".$jewish_holiday_str.
                "<br><span class='fountaintribe_occasion'>".self::getPersonalOccasionsForCalendar($iyear, $imonth, $iday)."</span>".
                $sunset_time_str.$rosh_hodesh_html_str   ;
                

 return $tmpHTMLcell;
 
 
}

/*************************************************************************************************/
/****  This function is typically called by the FountainTribe version of the Drupal calendar module.  */
function getDrupalCalendarDayCell($date_parm){

	$tmpHTMLcell = "";
	 $iyear = '';
  	$imonth = '';
  	$iday = ''; 

	list( $iyear, $imonth, $iday) = split('[-]', $date_parm);
	if(self::showCalendarOption(self::HEBREW_DATE) ){
  		$heb_date_str = "<span class='fountaintribe_hebrew_date'>".self::get_heb_date($iyear, $imonth, $iday)."<br></span>";
  }	

   if(self::showCalendarOption(self::SUNSET_TIME) ){
  	$sunset_time_str = "<span class='fountaintribe_candle_time'>".self::get_sunset_time($iyear, $imonth, $iday)."<br></span>";
  
  }
  
   if(self::showCalendarOption(self::JEWISH_HOLIDAYS) ){
  	// TODO: include span tag for Jewish holidays in this function, do not rely on other function to do it. 
  	$jewish_holiday_str = self::get_holiday_name_for_cal($iyear, $imonth, $iday) ; 
  
  }
  
	if(self::showCalendarOption(self::ROSH_CHODESH) ){
  		$rosh_hodesh_html_str = self::get_rosh_hodesh_html_str($iyear, $imonth, $iday);
  	}
  	
	$tmpHTMLcell = $heb_date_str.
	               $jewish_holiday_str.
	               "<span class='fountaintribe_occasion'>".self::getPersonalOccasionsForCalendar($iyear, $imonth, $iday)."</span>".
	               $sunset_time_str.$rosh_hodesh_html_str;  
	return $tmpHTMLcell; 

}
/*************************************************************************************************/
function showCalendarOption($cal_option){

 	$show_option = false;
 	
 	
 	// TODO: add these preferences to this module's admin area 
 	
 	 $display_sunset = "1";
	    $display_candle = "";
	   $display_heb_dates = "1";
	   $display_jewish_holidays_major = "1"; 
	   $display_rosh_chodesh = "1";
 	
 	/*
 	// Need to make sure to bootstrap CiviCRM since this may be called from a Drupal module. 
 	global $civicrm_root;
	
	if ( file_exists( '../../administrator/components/com_civicrm/' ) ) {
		// This is a Joomla site. 
    		$civicrm_root = '../../administrator/components/com_civicrm/civicrm/';
	} else {
    		$civicrm_root = $_SERVER["DOCUMENT_ROOT"].'/sites/all/modules/civicrm/';
	}




       $tmp_config = $civicrm_root.'civicrm.config.php';
        require_once $tmp_config;

    require_once 'CRM/Core/Config.php';
    $config =& CRM_Core_Config::singleton( );
	

    require_once 'CRM/Core/Error.php';
    
 	
 	require_once('utils/util_custom_fields.php');

$custom_field_group_label = "Calendar Preferences";
$customFieldLabels = array();

$custom_field_zenith_label = "Zenith Used to Calculate Sunset";
$customFieldLabels[] = $custom_field_zenith_label;

$custom_field_display_sunset_label = "Display Sunset Times" ;
$customFieldLabels[] = $custom_field_display_sunset_label;

$custom_field_display_candle_label = "Display Candle Lighting Times" ;
$customFieldLabels[] = $custom_field_display_candle_label;

$custom_field_display_hebrew_dates_label = "Display Hebrew Dates" ;
$customFieldLabels[] = $custom_field_display_hebrew_dates_label;

$custom_field_display_jewish_holidays_major_label = "Display Jewish Holidays - Major" ;
$customFieldLabels[] = $custom_field_display_jewish_holidays_major_label;

$custom_field_display_rosh_chodesh_label = "Display Rosh Chodesh" ;
$customFieldLabels[] = $custom_field_display_rosh_chodesh_label;

$outCustomColumnNames = array();


$error_msg = getCustomTableFieldNames($custom_field_group_label, $customFieldLabels, $sql_table_name, $outCustomColumnNames ) ;



if(strlen( $error_msg) > 0){
	// print "<br>Configuration error: ".$error_msg;
	return false;


}
$sql_zenith_field  =  $outCustomColumnNames[$custom_field_zenith_label];
$sql_display_sunset_field  =  $outCustomColumnNames[$custom_field_display_sunset_label];
$sql_display_candle_field  =  $outCustomColumnNames[$custom_field_display_candle_label];
$sql_display_hebrew_dates_field  =  $outCustomColumnNames[$custom_field_display_hebrew_dates_label];
$sql_display_jewish_holidays_major_field  =  $outCustomColumnNames[$custom_field_display_jewish_holidays_major_label];
$sql_display_rosh_chodesh_field = $outCustomColumnNames[$custom_field_display_rosh_chodesh_label];
//

$sql = "Select geo_code_1, geo_code_2, ".$sql_zenith_field." as zenith, ".$sql_display_sunset_field."  as display_sunset,
	".$sql_display_candle_field."  as display_candle,
	".$sql_display_hebrew_dates_field." as display_heb_dates, 
	".$sql_display_jewish_holidays_major_field." as display_jewish_holidays_major,
	".$sql_display_rosh_chodesh_field." as display_rosh_chodesh
	from civicrm_contact AS contact_a
	left join civicrm_address on contact_a.id = civicrm_address.contact_id
	left join civicrm_state_province on civicrm_address.state_province_id = civicrm_state_province.id
	left join ".$sql_table_name." as cal_prefs on contact_a.id = cal_prefs.entity_id
	WHERE
	contact_a.contact_sub_type =  'Primary_Organization' AND
	civicrm_address.is_primary = 1
	order by contact_a.id "; 
$zenith = '';	
$dao =& CRM_Core_DAO::executeQuery( $sql,   CRM_Core_DAO::$_nullArray ) ;

	if ( $dao->fetch( ) ) {
	   $latitude = $dao->geo_code_1;
	   $longitude = $dao->geo_code_2; 
	   $zenith = $dao->zenith;
	   $display_sunset = $dao->display_sunset;
	    $display_candle = $dao->display_candle;
	   $display_heb_dates = $dao->display_heb_dates;
	   $display_jewish_holidays_major = $dao->display_jewish_holidays_major; 
	   $display_rosh_chodesh = $dao->display_rosh_chodesh;
	  
	}else{
	   $no_data = true; 
	
	}
	
	
	$dao->free( );
	
	if($no_data){ //print  "<br>Configuration Error: Unknown Calendar Preferences"; 
	 }  
	
	
	*/
	if($cal_option == self::SUNSET_TIME ) {
		if( $display_sunset == '0' ){
			// Organization does not want sunset times on public calendar. 
			return false;	
		}else{
			return true; 
		}
	}
	
	if($cal_option == self::CANDLE_TIME ) {
		if( $display_candle == '0' ){
			// Organization does not want this option on public calendar. 
			return false;	
		}else if($display_candle == '1' ){
			return true; 
		
		}else{
		// Since sunset time is displayed by default, then candlelighting times should be off by default. 
			return false; 
		}
	}
	
	if($cal_option == self::HEBREW_DATE ) {
		if( $display_heb_dates == '0' ){
			// Organization does not want this option  on public calendar. 
			return false;	
		}else{
			return true; 
		}
	}


	if($cal_option == self::JEWISH_HOLIDAYS ) {
		if( $display_jewish_holidays_major == '0' ){
			// Organization does not want this option  on public calendar. 
			return false;	
		}else{
			return true; 
		}
	}

	if($cal_option == self::ROSH_CHODESH ) {
		if( $display_rosh_chodesh == '0' ){
			// Organization does not want this option  on public calendar. 
			return false;	
		}else{
			return true; 
		}
	}
	
	return $show_option;
}





function is_hebrew_year_leap_year( $hebrewYear){
		
		
  // https://en.wikipedia.org/wiki/Hebrew_calendar
  //  To determine whether year n of the calendar is a leap year, 
  // find the remainder on dividing [(7 × n) + 1] by 19. 
  // If the remainder  is 6 or less it is a leap year; if it is 7 or more it is not.
		
		$tmp = (7 * $hebrewYear) + 1;
		$remainder = $tmp % 19; 
		
		if( $remainder <= 6 ){
			return true;
		}else{
			return false;
		}
		
	}
		
/*************************************************************************************************/
/****   */
function getPersonalOccasionsForCalendar($iyear, $imonth, $iday){


	global $user;
   	$username = $user->name; 
   	
   	$tmp_occasions_html = ""; 
	if(strlen($username) > 0 ){
	
		// TODO: Check if CiviCRM is installed
		/*
		civicrm_initialize();
		require_once('CRM/Core/BAO/UFMatch.php');
		$cid = CRM_Core_BAO_UFMatch::getContactId($user->uid);
		
		if(strlen($cid) == 0){
			print "<br><br>Issue: User is logged in, but could not determine CRM contact id.";
			return ""; 
		}
		
		
		$tmp_items_to_show = array();
		$tmp_items_to_show[] = self::get_all_birthdays_for_contact($cid, $iyear, $imonth, $iday);
		
		$tmp_items_to_show[] = self::get_all_anniversaries_for_contact($cid, $iyear, $imonth, $iday);
		
		$tmp_items_to_show[] = self::get_all_yahrzeits_for_contact($cid, $iyear, $imonth, $iday);
		
		
		foreach( $tmp_items_to_show as $cur_item){
			if(strlen($tmp_occasions_html) > 0  && strlen($cur_item) > 0){
		     		$tmp_occasions_html = $tmp_occasions_html."<br>".$cur_item ; 
			}else{
		     		$tmp_occasions_html = $tmp_occasions_html.$cur_item;
			}
			
		}
		  
		
		*/
		return  $tmp_occasions_html;
	
	}else{
	  // anonymous user, ie not logged in.
		return "";
	}
}


/*************************************************************************************************/
/****   */
function get_all_birthdays_for_contact($cid, $iyear, $imonth, $iday){

$tmp_greetings = "";
// TODO: Check if CiviCRM is installed and active. If so, get birthdays.

/*
   require_once('RelationshipTools.php');
   $tmpRelTools = new RelationshipTools();
   
   $tmp_cid_array = array();
   $tmp_cid_array[] = $cid; 
   $cid_list =  $tmpRelTools->get_contact_ids_for_sql( $tmp_cid_array) ; 

   $sql_str = "select id, display_name from civicrm_contact 
              where id IN ($cid_list)  
              AND is_deceased <> 1
              AND is_deleted = 0
              AND month(birth_date) = $imonth
	      AND day(birth_date) = $iday ";  
	      
   $dao =& CRM_Core_DAO::executeQuery( $sql_str,   CRM_Core_DAO::$_nullArray ) ;
    
     $tmp_display_name = "";
    while ( $dao->fetch() ) {
      $tmp_display_name = $dao->display_name; 
      $tmp_id = $dao->id;
      if($tmp_id == $cid){
          $tmp_greetings = "Happy Birthday to you!"; 
      }else{
          $tmp_greetings = "Birthday of ".$tmp_display_name ;  
      }
      
    }
    $dao->free( ); 
     */
      
      if(strlen( $tmp_greetings) > 0){    
	      
		return $tmp_greetings; 
	}else{
		return ""; 
	}
	
	

}


function get_all_anniversaries_for_contact($cid, $iyear, $imonth, $iday){

$tmp_greetings = "";

// TODO: Check if CiviCRM is installed and active. If so, get anniversaries.

/*
  $sql_str = "SELECT start_date from civicrm_relationship r 
  	      LEfT JOIN civicrm_contact a ON r.contact_id_a = a.id
  	      LEFT JOIN civicrm_contact b ON r.contact_id_b = b.id , civicrm_relationship_type rt
              where (r.contact_id_a = $cid || r.contact_id_b = $cid)
              and r.relationship_type_id = rt.id
              and (rt.name_a_b like '%Spouse%'  || rt.name_a_b like '%spouse%')
              AND a.is_deceased <> 1 AND a.is_deleted = 0
              AND b.is_deceased <> 1 AND b.is_deleted = 0
              and r.is_active = 1
              and month(r.start_date) = $imonth 
              and day(r.start_date) = $iday " ;
       
     //  print "<br>sql: ".$sql_str;       
   $dao =& CRM_Core_DAO::executeQuery( $sql_str,   CRM_Core_DAO::$_nullArray ) ;
   
      $tmp_display_name = "";
    while ( $dao->fetch() ) {
      //$tmp_display_name = $dao->display_name; 
      
      
      $tmp_greetings = "Happy Anniversary!" ;  
      
      
    }
    $dao->free( ); 
     */
      
      if(strlen( $tmp_greetings) > 0){    
	      
		return $tmp_greetings; 
	}else{
		return ""; 
	}
	
	
	        

}

function get_all_yahrzeits_for_contact($cid, $iyear, $imonth, $iday){


$tmp_greetings = "";
  // TODO: Check if CiviCRM is installed and active. If so, get yahrzeits.
  
  /*
	$heb_path = './civicrm_custom_code/CRM/Hebrew/HebrewDates.php';   
	require_once($heb_path );
 	$tmpHebCal = new HebrewCalendar();
 	
   $yahrzeit_table_name = $tmpHebCal->get_sql_table_name(); 	

   $sql_str = "SELECT deceased_display_name as display_name, yahrzeit_type FROM ".$yahrzeit_table_name."
where mourner_contact_id = $cid
and month(yahrzeit_date)  = $imonth
and day(yahrzeit_date) = $iday
and year(yahrzeit_date) = $iyear
and yahrzeit_type = 'Hebrew' ";


$dao =& CRM_Core_DAO::executeQuery( $sql_str,   CRM_Core_DAO::$_nullArray ) ;
    
     $tmp_display_name = "";
    while ( $dao->fetch() ) {
      $tmp_display_name = $dao->display_name; 
      
      
      $tmp_greetings = "Yahrzeit of ".$tmp_display_name ;  
      
      
    }
    $dao->free( ); 
    
    */
      
      if(strlen( $tmp_greetings) > 0){    
	      
		return $tmp_greetings; 
	}else{
		return ""; 
	}
	
	


}

/*************************************************************************************************/
/****   */
function get_heb_date( $iyear, $imonth, $iday ){

 
   	 $hebrew_format = 'dd MM yy'; 
   	 $ibeforesunset = '1';
    	$hebrew_date_formated = $this->util_convert2hebrew_date($iyear, $imonth, $iday, $ibeforesunset, $hebrew_format);
   	
  return $hebrew_date_formated;
}




function retrieve_sunset_or_candlelighting_times($iyear, $imonth, $iday, $sunset_or_candle){
	return "";

}


function get_jewish_holiday_name($iyear, $imonth, $iday){
	return "";
}


/*************************************************************************************************/
/****   */
function get_sunset_time( $iyear, $imonth, $iday ){

 $sunset_time_formated = ""; 

	

    	$sunset_time = $this->retrieve_sunset_or_candlelighting_times($iyear, $imonth, $iday, 'sunset'); 
   	
   	
   if( strlen($sunset_time) > 0){
   	$sunset_time_formated = ' Sunset:'.$sunset_time ;  
   }else{
   	//$candle_time_formated = '<span style="display: hidden">candles:ss:33pm</span>'; 
   }	
 return $sunset_time_formated;
 

}
/*************************************************************************************************/
/****   */
function get_candle_time( $iyear, $imonth, $iday ){

	$candle_time_formated = ""; 
    	$candle_time = $this->retrieve_sunset_or_candlelighting_times($iyear, $imonth, $iday, 'candle'); 
   	
   	
   if( strlen($candle_time) > 0){
   	$candle_time_formated = ' Candles:'.$candle_time ;  
   }else{
   	
   	//$candle_time_formated = '<span style="display: hidden">candles:ss:33pm</span>'; 
   }	
 return $candle_time_formated;
 

}

/*************************************************************************************************/
/****   */
function get_holiday_name_for_cal($iyear, $imonth, $iday ){
   
	
 
    	$holiday_name = $this->get_jewish_holiday_name($iyear, $imonth, $iday); 
   	
   
   if( strlen($holiday_name) > 0){
   	$holiday_name = "<span class='fountaintribe_holiday_name'>".$holiday_name."</span>"; 
   }
   
 return $holiday_name;
 



}



/******************************************************************
*   This function takes in a English date, and returns the name of 
*   the Jewish rosh hodesh. If there is no rosh hodesh, then  an empty 
*   string is returned. 
******************************************************************/
function get_rosh_hodesh_name($iyear, $imonth, $iday){

	$tmp_name = "";
	$month_name = "";
	$date_before_sunset = 1;
	$hebrew_date_format = 'mm/dd/yy' ;
	$heb_date =  self::util_convert2hebrew_date($iyear, $imonth, $iday, $date_before_sunset, $hebrew_date_format);
	
	$heb_date_array = explode ( "/" , $heb_date ) ;
	$heb_month = $heb_date_array[0];
	$heb_day = $heb_date_array[1];
	$heb_year = $heb_date_array[2];
	
	if($heb_month <> "1"){
		if($heb_day == "1"){
			$julian_date = gregoriantojd($imonth,$iday,$iyear);
			$month_name = self::util_get_hebrew_month_name( $julian_date, $heb_date);
	    		$tmp_name = "Rosh Hodesh ".$month_name ;
	
	
		}else if( $heb_day == "30"){
			// TODO: Need to advance Jullian date to the next day. 
			// TODO: Need to advance Hebrew date to the next day. 
			$tmp_name = "Rosh Hodesh ".$month_name ;
	
		}else{
			$tmp_name = "";
	
		}
	}
	
	return $tmp_name;

}


function util_convert2hebrew_date(&$iyear, &$imonth, &$iday, &$ibeforesunset, &$hebrewformat){
   
   $defaultmsg = "Cannot determine Hebrew date";
   if($iyear == ''  ){
     return $defaultmsg." because year is blank";
   }
   
   if($imonth == ''  ){
     return $defaultmsg." because month is blank";
   }

   if($iday == ''  ){
     return $defaultmsg." because day is blank";
   }


  if($ibeforesunset == ''  ){
     return $defaultmsg." because before sunset flag is blank";
   }




   # date_default_timezone_set('Europe/London');
$idate_tmp = new DateTime("$iyear-$imonth-$iday");

$idate_str = $idate_tmp->format('F j, Y');
// Date provided: $idate_str  

$sunset_info_formated = '';
if($ibeforesunset == "0"){
  	$tmpdate_unix = mktime(0, 0, 0,  $idate_tmp->format('m')  ,  $idate_tmp->format('d')+1,  $idate_tmp->format('Y'));
  	$tmpdate_array = getdate($tmpdate_unix );	
 

	$gregorianMonth = $tmpdate_array['mon'];
	$gregorianDay = $tmpdate_array['mday'];
	$gregorianYear = $tmpdate_array['year'];
  	// After sunset, so added 1 day to Gregorian date. 
	
	$sunset_info_formated = '';

}else if($ibeforesunset == "1"){
	$gregorianMonth = $idate_tmp->format('n');
	$gregorianDay = $idate_tmp->format('j');
	$gregorianYear = $idate_tmp->format('Y');

	$sunset_info_formated = ' until sunset';
        // Before sunset, so no change to Gregorian date. 

}else{
	return "Cannot determine Hebrew date because ibeforesunset is not 1 or 0.";

}

// Date to convert to Hebrew date( mm-dd-yyyy) :  $gregorianMonth - $gregorianDay - $gregorianYear 

$jdDate = gregoriantojd($gregorianMonth,$gregorianDay,$gregorianYear);


if($hebrewformat == 'mm/dd/yy'){
	$hebrewDate = jdtojewish($jdDate);
	list($hebrewMonth, $hebrewDay, $hebrewYear) = split('/',$hebrewDate);
	$hebrew_date_formated = "$hebrewMonth/$hebrewDay/$hebrewYear"; 
}else if($hebrewformat == 'dd MM yy sunset'){
	$hebrewDate = jdtojewish($jdDate);
	list($hebrewMonth, $hebrewDay, $hebrewYear) = split('/',$hebrewDate);
	$hebrewMonthName = self::util_get_hebrew_month_name($jdDate, $hebrewDate);
	$hebrew_date_formated = "$hebrewDay  $hebrewMonthName  $hebrewYear $sunset_info_formated";
}else if($hebrewformat == 'dd MM yy' || $hebrewformat == 'dd_MM_yy'){
	$hebrewDate = jdtojewish($jdDate);
	list($hebrewMonth, $hebrewDay, $hebrewYear) = split('/',$hebrewDate);
	$hebrewMonthName = self::util_get_hebrew_month_name($jdDate, $hebrewDate);
	$hebrew_date_formated = "$hebrewDay $hebrewMonthName $hebrewYear";
}else if($hebrewformat == 'dd MM' || $hebrewformat == 'dd_MM'  ){
	$hebrewDate = jdtojewish($jdDate);
	list($hebrewMonth, $hebrewDay, $hebrewYear) = split('/',$hebrewDate);
	$hebrewMonthName = self::util_get_hebrew_month_name($jdDate, $hebrewDate);
	$hebrew_date_formated = "$hebrewDay $hebrewMonthName";

}else if($hebrewformat == 'yy'){
	$hebrewDate = jdtojewish($jdDate);
	list($hebrewMonth, $hebrewDay, $hebrewYear) = split('/',$hebrewDate);
	$hebrew_date_formated  = "$hebrewYear";
}else if($hebrewformat == 'hebrew'){
	$hebrew_date_formated =  mb_convert_encoding( jdtojewish( $jdDate, true ), "UTF-8", "ISO-8859-8"); 
}else{
        $hebrew_date_formated = "Unrecognized Hebrew date format: $hebrewformat"; 
}



// Hebrew Date formatted: $hebrew_date_formated 
return $hebrew_date_formated;

   }


}



?>
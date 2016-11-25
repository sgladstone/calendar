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
	
	static private $allRemoteHebCalData = array();  // data from the http://hebcal.com API.
	
	private static function getHavdalahtimeByDate( &$date_parm ){
		$year_parm = substr($date_parm, 0, 4 );
		
		$tmp_hebcal_data_all_years = PersonalCalendar::getAllRemoteHebCalData($year_parm);
		
		
		
		if( isset( $tmp_hebcal_data_all_years[$year_parm] ) ){
			$tmp_hebcal_data = $tmp_hebcal_data_all_years[$year_parm];
			//CRM_Core_Error::debug($year_parm." Heb data: ", $tmp_hebcal_data );
			foreach( $tmp_hebcal_data as $cur ){
		
				$hebcal_date_tmp = substr($cur->date, 0, 10);
		
				if( $cur->category == "havdalah" &&  $hebcal_date_tmp == $date_parm ){
		
					$time_raw_str = substr($cur->date, 11, 5 ) ;
					$time_arr = explode(":", $time_raw_str);
						
					$hour_24_style = $time_arr[0];
					$min_str = $time_arr[1];
						
					$hour_pretty = $hour_24_style - 12;
					$time_pretty = $hour_pretty.":".$min_str."pm";
		
					return $time_pretty;
		
				}else{
					// keep looking.
				}
			}
		}
	}
	
	private static function getOmerByDate( &$date_parm ){
	
		// {"hebrew":"?? ?????","category":"omer","date":"2016-05-03","title":"10th day of the Omer"}
		// TODO: Get day of the Omer to display on front-facing calendar.
		
	}
	
	private static function getCandletimeByDate( &$date_parm ){
		/* Example from HebCal API result:
		 * "category": "candles",
        "title": "Candle lighting: 5:04 pm",
        "date": "2015-05-22T17:04:00-03:00"
		 */
		$year_parm = substr($date_parm, 0, 4 );
		
		$tmp_hebcal_data_all_years = PersonalCalendar::getAllRemoteHebCalData($year_parm);
		
		
		
		if( isset( $tmp_hebcal_data_all_years[$year_parm] ) ){
			$tmp_hebcal_data = $tmp_hebcal_data_all_years[$year_parm];
			//CRM_Core_Error::debug($year_parm." Heb data: ", $tmp_hebcal_data );
			foreach( $tmp_hebcal_data as $cur ){
				
				$hebcal_date_tmp = substr($cur->date, 0, 10);
				
				if( $cur->category == "candles" &&  $hebcal_date_tmp == $date_parm ){
		
					$time_raw_str = substr($cur->date, 11, 5 ) ;
					$time_arr = explode(":", $time_raw_str);
					
					$hour_24_style = $time_arr[0];
					$min_str = $time_arr[1];
					
					$hour_pretty = $hour_24_style - 12;
					$time_pretty = $hour_pretty.":".$min_str."pm";
		
					return $time_pretty;
		
				}else{
					// keep looking.
				}
			}
		}
		
		
		
		
	}
	
	private static function getHolidayByDate( &$date_parm){
	
		// date returned from HebCal API will include time information when candlelighting parm = on. 
		
	
		$year_parm = substr($date_parm, 0, 4 );
		$tmp_hebcal_data_all_years = PersonalCalendar::getAllRemoteHebCalData($year_parm);
	
		if( isset( $tmp_hebcal_data_all_years[$year_parm] ) ){
			$tmp_hebcal_data = $tmp_hebcal_data_all_years[$year_parm];
			
			foreach( $tmp_hebcal_data as $cur ){
				$hebcal_date_tmp = substr($cur->date, 0, 10);
				
				if( $cur->category == "holiday" &&  $hebcal_date_tmp == $date_parm ){
	
					$holiday_title = $cur->title;
					$holiday_date = $cur->date;
	
					// if Chanukah, remove candle times from title. example of raw title: "Chanukah: 6 Candles: 6:35pm"
					if( strpos($holiday_title, 'Candles:') !== false || strpos($holiday_title, 'Candle:') !== false){
						$title_arr = explode(":", $holiday_title);
						
						$holiday_title = $title_arr[0].":".$title_arr[1];
						
					}
					
					return $holiday_title;
	
				}else{
					// keep looking.
				}
	
	
			}
		}
	
	
	}
	
	
	
	
	
	
	private static function getRemoteHebCalDataByYear(&$year_parm ){
	
		// Documentation for HebCal.com REST API: https://www.hebcal.com/home/195/jewish-calendar-rest-api
		$geo_api_query_string = "&geo=none"; 
		
		// Check default country of Drupal.
		$tmp_country = variable_get( 'site_default_country' );
		if( $tmp_country == "IL" ){
			$in_israel_setting = true;
			$tmp_in_israel = "on";
		}else{
			$in_israel_setting =  false;  
			$tmp_in_israel = "off";
			// HebCal.com API:	'off' = diaspora
		}	
	
		if(strlen($year_parm) == 4  ){
				
			// Mutually exclusive language parameter for HebCal API:
	
		//	 lg=s – Sephardic transliterations (default if unspecified)
		//	 lg=sh – Sephardic translit. + Hebrew
		//	 lg=a – Ashkenazis transliterations
		//	 lg=ah – Ashkenazis translit. + Hebrew
		//	 lg=h – Hebrew only
			
			$language_api_parm = "s"; // Uses the modern Hebrew transliteration, ie 'parashat'.   'a' and 'ah' uses the older, Yiddish-style ie 'parashas' which is rarely used.
			 
		 	 // get time zone things from admin params
			$timezone =   variable_get( 'date_default_timezone'  ); // "America/New_York";
			
			$latitude =  variable_get( 'calendar_candlelighting_latitude'  );  // "28.095656";
			$longitude = variable_get( 'calendar_candlelighting_longitude' );  // "-82.730689"; 
			
			$geotype = variable_get('calendar_candlelighting_geotype');
			
			
			// latitude=[-90 to 90]
			// longitude=[-180 to 180]
			if( $geotype <> "usezip" ){
				if( strlen($latitude ) == 0 || strlen($longitude ) == 0){
					$candles_parm = "off";
					$geo_api_query_string = "&geo=none";
				}else{
					// check if numbers are within range.
					if( $latitude >= -90 && $latitude <= 90  && $longitude >= -180 && $longitude <= 180 && strlen($timezone) > 0 ){
						$candles_parm = "on";
						$geo_api_query_string = "&geo=pos&latitude=".$latitude."&longitude=".$longitude."&tzid=".$timezone;
					}else{
						$candles_parm = "off";
						$geo_api_query_string = "&geo=none"; 
					}
				}
			}else{
				// use ZIP code 
				$zip_code_raw = variable_get('calendar_candlelighting_us_zipcode');
				
				$zip_code_5 = substr( $zip_code_raw, 0 , 5 );
				if( strlen( $zip_code_5) == 5 && is_numeric($zip_code_5)){
					$candles_parm = "on";
					$geo_api_query_string = "&geo=zip&zip=".$zip_code_5;
					
				}else{
					$candles_parm = "off";
					$geo_api_query_string = "&geo=none";
				}
				
			}
			
		
			
			// 
			
	
			// Get Major holidays?
			if( variable_get('calendar_add_jewish_holidays_major') <> "0"){
				$maj_holidays = "on";
				
			}else{
				$maj_holidays = "off";
			}
			
			// Get Minor holidays?
			if( variable_get('calendar_add_jewish_holidays_minor') <> "0"){
				$min_holidays = "on";
			
			}else{
				$min_holidays = "off";
			}
			
			// get minor fast days?
			if( variable_get('calendar_add_jewish_holidays_minorfasts') <> "0"){
				$minorfasts = "on";
					
			}else{
				$minorfasts = "off";
			}
			
			// get special shabbatot?
			if( variable_get('calendar_add_jewish_holidays_specialshabbatot') <> "0"){
				$special_shabbatot = "on";
					
			}else{
				$special_shabbatot = "off";
			}
			
			if( variable_get('calendar_add_jewish_holidays_roshhodesh') <> "0"){
				$rosh_hodesh = "on";
			}else{
				$rosh_hodesh = "off";
			}
			
			$modern_holidays = "on";  // Modern holidays (Yom HaShoah, Yom HaAtzma’ut, …)
			
			$shabbat_parasha = "on";
			
			$havdalah_minutes_after_sundown = "50"; 
			
			$days_of_omar = "on";
			/*
			 HebCal API parms:
			 
			      m=50 – Havdalah 50 minutes after sundown. Set to m=0 (zero) to disable Havdalah times
			      b=18 – Candle-lighting time minutes before sunset
			 D=on – Hebrew date for dates with some event
			 d=on – Hebrew date for entire date range
			 o=on – Days of the Omer
			
			 */
			$service_url = "http://www.hebcal.com/hebcal/?v=1&cfg=json".
					"&maj=".$maj_holidays.
					"&min=".$min_holidays.
					"&lg=".$language_api_parm.
					"&i=".$tmp_in_israel.
					"&mod=".$modern_holidays.
					"&nx=".$rosh_hodesh.
					"&year=".$year_parm.
					"&month=x".
					"&d=off".
					"&D=off".
					"&o=".$days_of_omar.
					"&ss=".$special_shabbatot.
					"&mf=".$minorfasts.
					"&c=".$candles_parm.
					"&m=".$havdalah_minutes_after_sundown.
					"&s=".$shabbat_parasha.
					$geo_api_query_string;
			
					/* 
					$service_url = "http://www.hebcal.com/hebcal/?v=1&cfg=json&maj=on&min=on&lg=s&i=off&mod=on&nx=on&year=2016&month=x&d=on&D=on&ss=on&mf=on&c=on&m=50&s=on&geo=pos&latitude=28.095656&longitude=-82.730689&tzid=America/New_York;
		 */
					$curl = curl_init($service_url);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					$curl_response = curl_exec($curl);
					if ($curl_response === false) {
						$info = curl_getinfo($curl);
						curl_close($curl);
						CRM_Core_Error::debug("Error getting remote hebcal.com data: ", 'error occured during curl exec. Additional info: ' . var_export($info));
					}
					curl_close($curl);
					$decoded = json_decode($curl_response);
					if (isset($decoded->response->status) && $decoded->response->status == 'ERROR') {
						CRM_Core_Error::debug("Error getting remote hebcal.com data: " . $decoded->response->errormessage);
					}
					//CRM_Core_Error::debug(" $tmp_year Good news, got hebcal.com data: " , 'response ok!');
					//CRM_Core_Error::debug("$tmp_year Data: ", $decoded);
					PersonalCalendar::$allRemoteHebCalData[$year_parm] = $decoded->items;
	
	
					//var_export($decoded->response);
		}else{
			CRM_Core_Error::debug("Error: missing required parms for either 'year_parm' or 'in_israel_crm_domain_setting' " , "Did not attempt to get anything from hebcal.com API");
		}
	
	
	
	}
	
	
	
	
	
	private static function getAllRemoteHebCalData(&$tmp_year){
	
		$tmp_rtn = null;
		if(count( PersonalCalendar::$allRemoteHebCalData) == 0){
				
			$years_array = array();
			$years_array[] = $tmp_year;
			
			/*
			// Use remote hebcal.com API to get needed data for last 4 years, current year, and next 4 years.
			$years_offsets_arr = array(  -2, -1, 0, 1, 2, 3, 4) ;
			foreach($years_offsets_arr as $year_offset){
	
				$tmp_year = date("Y") + $year_offset;
				$years_array[] = $tmp_year;
			}
				*/
				
			foreach($years_array as $tmp_year){
	
				PersonalCalendar::getRemoteHebCalDataByYear( $tmp_year );
			}
				
		}
	
		$tmp_rtn = PersonalCalendar::$allRemoteHebCalData;
		return $tmp_rtn;
	
	}
	
	

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
  	 $candle_time_str = "<span class='fountaintribe_candle_time'>".self::get_candle_time_formatted($iyear, $imonth, $iday)."</span>";
  }else{
  	$candle_time_str = "<span class='fountaintribe_candle_time'></span>";
  }
  
  
  /*
  if(self::showCalendarOption(self::SUNSET_TIME) ){
  	 $sunset_time_str = "<span class='fountaintribe_candle_time'>".self::get_sunset_time($iyear, $imonth, $iday)."</span>";
  }else{
  	$sunset_time_str = "<span class='fountaintribe_candle_time'></span>";
  }
  */
  
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
  


   $tmpHTMLcell = $heb_date_str.$cell['data'].$personal_occasion_full.$candle_time_str.$jewish_holiday_str.$rosh_hodesh_html_str  ;
   //$candle_time_str.$sunset_time_str.$jewish_holiday_str.$rosh_hodesh_html_str  ;

   

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
  	 $candle_time_str = "<span class='fountaintribe_candle_time'>".self::get_candle_time_formatted($iyear, $imonth, $iday)."</span>";
  	
  }
  
  /*
  if(self::showCalendarOption(self::SUNSET_TIME) ){
  	 $sunset_time_str = "<span class='fountaintribe_candle_time'>".self::get_sunset_time($iyear, $imonth, $iday)."</span>";
  
  }*/
  
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
/*************************************************************************************************/
function showCalendarOption($cal_option){

 	$show_option = false;
	
	if($cal_option == self::CANDLE_TIME ) {
		$tmp_var = variable_get('calendar_add_candlelighting');
		if( $tmp_var == '0' ){
			// Organization does not want this option on public calendar. 
			return false;	
		}else{
			return true; 
		
		}
	}
	
	if($cal_option == self::HEBREW_DATE ) {
		$tmp_var = variable_get('calendar_add_hebrew_date');
		if( $tmp_var == '0' ){
			// Organization does not want this option  on public calendar. 
			return false;	
		}else{
			return true; 
		}
	}


	if($cal_option == self::JEWISH_HOLIDAYS ) {
		$tmp_var1 = variable_get('calendar_add_jewish_holidays_major');
		$tmp_var2 = variable_get('calendar_add_jewish_holidays_minor');
		$tmp_var3 =	variable_get('calendar_add_jewish_holidays_minorfasts');
		$tmp_var4 =	variable_get('calendar_add_jewish_holidays_specialshabbatot');
		//  'site_default_country'
		if( $tmp_var1 == '0' && $tmp_var2 == '0' && $tmp_var3 == '0' && $tmp_var4 == '0'){
			// Organization does not want this option  on public calendar. 
			return false;	
		}else{
			return true; 
		}
	}

	if($cal_option == self::ROSH_CHODESH ) {
		$tmp_var = variable_get( 'calendar_add_jewish_holidays_roshhodesh');
		
		if( $tmp_var == '0' ){
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
  // find the remainder on dividing [(7 Ã— n) + 1] by 19. 
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



/*
function XXXretrieve_sunset_or_candlelighting_times($iyear, $imonth, $iday, $sunset_or_candle){
	return "";

}
*/


function get_jewish_holiday_name($iyear, $imonth, $iday){
	
	$date_tmp = $iyear."-".$imonth."-".$iday;
	
	$tmp_holiday_name =   $this->getHolidayByDate( $date_tmp);
	return $tmp_holiday_name;
}


/*************************************************************************************************/

/*************************************************************************************************/
/****   */
function get_candle_time_formatted( $iyear, $imonth, $iday ){

	$candle_time_formated = ""; 
	$date_tmp = $iyear."-".$imonth."-".$iday;
    	//$candle_time = $this->retrieve_sunset_or_candlelighting_times($iyear, $imonth, $iday, 'candle'); 
	$candle_time =  PersonalCalendar::getCandletimeByDate( $date_tmp ) ;
   	
   if( strlen($candle_time) > 0){
   	  $candle_time_formated = ' Candles:'.$candle_time ;  
   }else{
   	
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
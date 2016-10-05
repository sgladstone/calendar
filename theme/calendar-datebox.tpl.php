<?php
/**
 * @file 
 * Template to display the date box in a calendar.
 *
 * - $view: The view.
 * - $granularity: The type of calendar this box is in -- year, month, day, or week.
 * - $mini: Whether or not this is a mini calendar.
 * - $class: The class for this box -- mini-on, mini-off, or day.
 * - $day:  The day of the month.
 * - $date: The current date, in the form YYYY-MM-DD.
 * - $link: A formatted link to the calendar day view for this day.
 * - $url:  The url to the calendar day view for this day.
 * - $selected: Whether or not this day has any items.
 * - $items: An array of items for this day.
 */
?>
<?php 

  //print "<br>mini? ".$mini;
  
   if( $mini <> "1" ){
	   $cal_path =  './sites/all/modules/calendar/PersonalCalendar.php';	
	   require_once($cal_path );
	   $tmpPersonalCal = new PersonalCalendar();
	
	   $fountaintribe_extra = $tmpPersonalCal->getDrupal7CalendarMonthCell($date, $granularity); 
	   
	   $num_span_str = "<span class='fountaintribe_cal_num'>"; 
	   $num_span_end = "</span>"; 
   }else{
      $pogstone_extra = ""; 
   
   	$num_span_str = ""; 
   	$num_span_end = "";
   }
   

?>

<div class="<?php print $granularity ?> <?php print $class; ?>"> <?php print !empty($selected) ? $num_span_str.$link.$num_span_end.$fountaintribe_extra :   $num_span_str.$day.$num_span_end.$fountaintribe_extra ; ?> </div>
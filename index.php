<?php

$xml_url	= "https://edt.univ-tlse3.fr/FSI/FSImentionM/Info/g30747.xml";
//$xml_url = "data.xml";

$ch = curl_init($xml_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$xml_data = curl_exec($ch);
curl_close($ch);
$GROUPE = 0;

$xml_data = new SimpleXMLElement($xml_data);

$nameGroupe[0] = "A11";
$nameGroupe[1] = "A12";
$nameGroupe[2] = "A21";



for ($indexGroupe=0; $indexGroupe < count($nameGroupe); $indexGroupe++) {
	$tempCalendar	="";
	$tempCalendar  	= "BEGIN:VCALENDAR\n";
	$tempCalendar 	.= "X-WR-CALNAME:SEE3 ENSEIRB\n";
	$tempCalendar 	.= "X-WR-TIMEZONE:Europe/Paris\n";


	foreach($xml_data->event as $event) {
		$tempEvent = "";
		$week_start_date = null;
		foreach($xml_data->span as $week) {
			$week_tab = json_decode(json_encode($week),true);
			if(strpos($event->rawweeks, "Y")+1 == $week_tab["@attributes"]["rawix"]) {
				$week_start_date = $week_tab["@attributes"]["date"];
				break;
			}
		}
		if($week_start_date == null) continue;

		$year		= preg_replace("#^([0-9]{2})/([0-9]{2})/([0-9]{4})$#", "$3", $week_start_date);
		$month		= preg_replace("#^([0-9]{2})/([0-9]{2})/([0-9]{4})$#", "$2", $week_start_date);
		$day		= preg_replace("#^([0-9]{2})/([0-9]{2})/([0-9]{4})$#", "$1", $week_start_date);
		$time 		= mktime(0, 0, 0, $month, $day, $year) + 24*3600*$event->day;;
		$date 		= date("Ymd",$time);
		$hstart		= str_replace(":", "", $event->starttime);
		$hend		= str_replace(":", "", $event->endtime);

		$name			= $event->resources->module->item;
		$description	= $event->resources->staff;
		$location 		= $event->resources->room->item;
		$category		= $event->category;
		$name			= substr_replace($name, $category,  0, 8);
		$listItem 		= $event->resources->group->item;

		for ($i=0; $i < count($listItem) ; $i++) {
			echo $i." ".$listItem[$i]."<br />";
			if(strcmp($listItem[$i], "M1 INF-DL s1 - TD".substr($nameGroupe[$indexGroupe], 0,2))	== 0 ||
				strcmp($listItem[$i], "M1 INF-DL s1 - TP".$nameGroupe[$indexGroupe])				== 0 ||
				strcmp($listItem[$i], "M1 INF-DL s2 - TD".substr($nameGroupe[$indexGroupe], 0,2))	== 0 ||
				strcmp($listItem[$i], "M1 INF-DL s2 - TP".$nameGroupe[$indexGroupe])				== 0 ||
				strcmp($listItem[$i], "M1 INF-DL s1 - CMA")											== 0 ||
				strcmp($listItem[$i], "M1 INF-DL s2 - CMA")											== 0)
				$GROUPE = 1;
		}

		if($GROUPE){
			$tempEvent .= "BEGIN:VEVENT\n";
			$tempEvent .= "LOCATION:"			.$location.			"\n";
			$tempEvent .= "SUMMARY:"			.$name.				"\n";
			$tempEvent .= "CATEGORIES:"			.$category.			"\n";
			$tempEvent .= "DESCRIPTION:"		.$description.		"\n";
			$tempEvent .= "DTSTART:"			.$date."T".$hstart.	"00\n";
			$tempEvent .= "DTEND:"				.$date."T".$hend.	"00\n";
			$tempEvent .= "END:VEVENT\n";

			$tempCalendar .= $tempEvent;
			$GROUPE = 0;
		}
	}

	$tempCalendar .= "END:VCALENDAR\n";
	//echo $tempCalendar;
	$tempCalendar = utf8_decode($tempCalendar);
	$tempCalendar = utf8_encode($tempCalendar);
	$calenderFile = fopen($nameGroupe[$indexGroupe].'.ics', 'w+');

	fseek($calenderFile, 0);
	fputs($calenderFile, $tempCalendar);
	fclose($calenderFile);

}
function object2array($object)
{
	return json_decode(json_encode($object), TRUE);
}

?>

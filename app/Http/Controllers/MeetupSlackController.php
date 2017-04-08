<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MeetupSlackController extends Controller
{
    
	public function test()
	{
		echo "hello";


$slack_token = config('services.slack.token');



$apipath = config('services.meetup.apipath');

$nextEvent = config('services.meetup.next');


$currentEvent = config('services.meetup.current');


$accesstoken = config('services.meetup.access');
	

$response = \Httpful\Request::get($uri)->send();

$current = "current";
$next = "next";

// whichMeetup holds the text value to be passed into the json output for slack 
// here setting the default text to "Next Meetup"
$whichMeetup = "Next Meetup"; 

// api call url set in intialize.php
// default state of the api call is the next meetup
$uri = $apipath.$nextEvent.$accesstoken;


// if user type "/cfp current" this sets the api call url to fetch the current meetup which is defined in the api as "recent_past" here https://www.meetup.com/meetup_api/docs/:urlname/events/#list
if(strcasecmp($text, $current) == 0)
	{	
	$uri = $apipath.$currentEvent.$accesstoken;
	$whichMeetup = "Current Meetup";
	}


// using httpful.phar to get and parse JSON object from API 
// http://phphttpclient.com
$response = \Httpful\Request::get($uri)->send();


// grab the title of the event from the response
$title = $response->body[0]->name;



// grab the time of the event from the response 
$time = $response->body[0]->time; 
$timeOffset = $response->body[0]->utc_offset; // time offset in the api is a negative number


// convert the date and time to user readable format 
$epoch = ($time+$timeOffset)/1000;
// echo date('r', $epoch); // output as RFC 2822 date - returns local time
$date = gmdate('M d', $epoch); 
$time = gmdate('g a', $epoch);


// grab the title of the place where the event is being held
$placeTitle = $response->body[0]->venue->name;

//grab the street address where the event is being held
$placeStreet = $response->body[0]->venue->address_1;

//grab the city where the event is being held 
$placeCity = $response->body[0]->venue->city; 




// creating slack json attachments array
  $arr = array("title" => $title,
   "text" => $placeTitle
   ."\n".
   $placeStreet
   ."\n".
   $placeCity
   ."\n".
   "When: "
   .$date
   ."\n". 
   "Time: ".$time);

// set json header for Slack 
header('Content-Type: application/json');

// convert theMessage to json so Slack can read it
$jsonMessage = json_encode(array("text" => $whichMeetup, "attachments" => array($arr))); 

echo "JSON";

echo $jsonMessage;

}

} // close class 

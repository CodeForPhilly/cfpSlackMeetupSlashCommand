<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


class MeetupController extends Controller {


/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
	
	}


	public function index()
	{
		return view('meetup');
	}


		public function hello()
	{
		echo "hello";
	}



		public function posts(Request $request)
	{

		$command = $request['command'];
		$token = $request['token'];
		$text = $request['text'];

		echo $command; 
		echo $text; 

	}	

	
	   /**
     * 
     *
     * @param  Request  $request
     * @return Response
     */
	public function slack(Request $request)
	{
		
	
 

		$command = $request->input('command');
		$token = $request->input('token');
		$text = $request->input('text');


		// slack token to authorize
		$slack_token = config('services.slack.token');

		//meetup api path
		$apipath = config('services.meetup.apipath');

		// api call for the next event
		$nextEvent = config('services.meetup.next');

		// api call for current event
		$todayEvent = config('services.meetup.today');

		// api call for the last event (have to change variable name to reflect last)
		$currentEvent = config('services.meetup.current');

		// access token for the api
		$accesstoken = config('services.meetup.apikey');

#
# Check for Token from Slack
#

if($token != $slack_token){ 
	$msg = "This slash command is broken, check with CFP leadership.";
	die($msg);
	echo $msg;
}

else {

// a user can type "/cfp last" to get the last meetup and "/cfp next" to get the next upcoming meetup so here I am setting the text for string comparison 
// $current = "current";
$next = "next";
$last = "last";
$today = "today";

// whichMeetup holds the text value to be passed into the json output for slack 
// here setting the default text to "Next Meetup"
$whichMeetup = "Next Meetup"; 

// api call url set in intialize.php
// default state of the api call is the next meetup
$uri = $apipath.$todayEvent.$accesstoken;



// if user type "/cfp last" this sets the api call url to fetch the current meetup which is defined in the api as "recent_past" here https://www.meetup.com/meetup_api/docs/:urlname/events/#list
if(strcasecmp($text, $last) == 0)
  { 
  $uri = $apipath.$currentEvent.$accesstoken;
  $whichMeetup = "Last Meetup";
  }

if(strcasecmp($text, $today) == 0)
  { 
  $uri = $apipath.$todayEvent.$accesstoken;
  $whichMeetup = "Today's Meetup";
  }

if(strcasecmp($text, $next) == 0)
  { 
  $uri = $apipath.$nextEvent.$accesstoken;
  $whichMeetup = "Next Meetup";
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
// header('Content-Type: application/json');

// convert theMessage to json so Slack can read it
// $jsonMessage = json_encode(array("text" => $whichMeetup, "attachments" => array($arr))); 





return response()->json([
    'text' => $whichMeetup,
    'attachments' => array($arr)
]);



} // close else

} // close class
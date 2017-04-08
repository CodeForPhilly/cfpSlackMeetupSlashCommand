<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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



$command = $_POST['command'];
$token = $_POST['token'];
$text = $_POST['text'];


$slack_token = $slack->token; 
$apipath = $meetup->apipath; 
$next = $meetup->next; 
$current = $meetup->current;
$accesstoken = $meetup->access;

#
# Check for Token from Slack
#

if($token != $slack_token){ 
	$msg = "This slash command is broken, check with CFP leadership.";
	die($msg);
	echo $msg;
}

else {

// a user can type "/cfp current" to get the current meetup and "/cfp next" to get the next upcoming meetup so here I am setting the text for string comparison 
$current = "current";
$next = "next";

// whichMeetup holds the text value to be passed into the json output for slack 
// here setting the default text to "Next Meetup"
$whichMeetup = "Next Meetup"; 

// api call url set in intialize.php
// default state of the api call is the next meetup
$uri = $apipath.$next.$accesstoken;


// if user type "/cfp current" this sets the api call url to fetch the current meetup which is defined in the api as "recent_past" here https://www.meetup.com/meetup_api/docs/:urlname/events/#list
if(strcasecmp($text, $current) == 0)
	{	
	$uri = $apipath.$current.$accesstoken;
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



} // close else 


$reply = "No reply";

$reply = $jsonMessage;

// send reply to slack 
echo $reply; 




}

} // close class
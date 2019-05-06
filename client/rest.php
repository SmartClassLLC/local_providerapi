<?php

require_once("MoodleRest.php");

$moodleRest = new MoodleRest('http://localhost/webservice/rest/server.php','3f132580636ac2108a0f0cb0f6754c80');
$moodleRest->setDebug();

$func = 'local_providerapi_checkinstitution';
$params = array('institutionkey' => 1);


$return = $moodleRest->request($func,$params);

$moodleRest->printRequest();
$moodleRest->getUrl();

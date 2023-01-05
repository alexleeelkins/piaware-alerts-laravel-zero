<?php

return [
    // Your API key from https://flightaware.com/aeroapi/portal/ NOTE: This is NOT free
    'AeroAPIToken'                 => env('AERO_API_TOKEN'),
    // The location of your dump1090-fa aircraft.json file (web or local file path)
    'AircraftJsonLocation'         => env('AIRCRAFT_JSON_LOCATION'),
    // Your latitude
    'MyLatitude'                   => env('MY_LATITUDE'),
    // Your longitude
    'MyLongitude'                  => env('MY_LONGITUDE'),
    // Aircraft further than this distance will be ignored
    'SearchRadiusMiles'            => env('SEARCH_RADIUS_MILES'),
    // Aircraft outside of this polygon will be ignored NOTE: CSV Lat Lon, example: "30.062619301073404 -83.83220800303775,30.243114416143417 -81.47014745616275,27.919863769446824 -81.68987401866275,30.062619301073404 -83.83220800303775"
    'SearchRadiusPolygon'          => env('SEARCH_RADIUS_POLYGON'),
    // polygon to use SEARCH_RADIUS_POLYGON, distance to use SearchRadiusMiles
    'SearchRadiusMechanism'        => env('SEARCH_RADIUS_MECHANISM'),
    // Do not Tweet the same flight unless it has been at least this many minutes
    'SpecificAircraftTweetMinutes' => env('SPECIFIC_AIRCRAFT_TWEET_MINUTES'),
    // How frequently to refresh the aircraft.json file and restart the process
    'PollEverySeconds'             => env('POLL_EVERY_SECONDS'),
    'TwitterConsumerKey'           => env('TWITTER_CONSUMER_KEY'),
    'TwitterConsumerSecret'        => env('TWITTER_CONSUMER_SECRET'),
    'TwitterAccessToken'           => env('TWITTER_ACCESS_TOKEN'),
    'TwitterAccessTokenSecret'     => env('TWITTER_ACCESS_TOKEN_SECRET'),
];

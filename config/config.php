<?php

return [
    'AeroAPIToken'                 => env('AERO_API_TOKEN'),
    'AircraftJsonLocation'         => env('AIRCRAFT_JSON_LOCATION'),
    'MyLatitude'                   => env('MY_LATITUDE'),
    'MyLongitude'                  => env('MY_LONGITUDE'),
    'SearchRadiusMiles'            => env('SEARCH_RADIUS_MILES'),
    // CSV Lat Lon, example: "30.062619301073404 -83.83220800303775,30.243114416143417 -81.47014745616275,27.919863769446824 -81.68987401866275,30.062619301073404 -83.83220800303775"
    'SearchRadiusPolygon'          => env('SEARCH_RADIUS_POLYGON'),
    'SearchRadiusMechanism'        => env('SEARCH_RADIUS_MECHANISM'),
    // wait at least this many minutes before tweeting the same flight
    'SpecificAircraftTweetMinutes' => env('SPECIFIC_AIRCRAFT_TWEET_MINUTES'),
    'PollEverySeconds'             => env('POLL_EVERY_SECONDS'),
    'TwitterConsumerKey'           => env('TWITTER_CONSUMER_KEY'),
    'TwitterConsumerSecret'        => env('TWITTER_CONSUMER_SECRET'),
    'TwitterAccessToken'           => env('TWITTER_ACCESS_TOKEN'),
    'TwitterAccessTokenSecret'     => env('TWITTER_ACCESS_TOKEN_SECRET'),
];

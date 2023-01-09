<?php

namespace App\Commands;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Aircraft;
use App\AircraftOperator;
use App\AircraftType;
use App\Service\AeroAPI;
use App\Service\HexDB;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Arr;
use LaravelZero\Framework\Commands\Command;
use Location\Coordinate;
use Location\Polygon;

class TweetNearbyFlights extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'tweet-nearby-flights';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Gets nearby flights, populates data about each, and Tweets the data';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $noGpsCoordinatesTotal = 0;
        $notInPolygonTotal     = 0;
        $tooFarTotal           = 0;
        $sawRecentlyTotal      = 0;
        $noAeroApiFlightTotal  = 0;
        $hexDBFailedTotal      = 0;
        $tweetedTotal          = 0;

        while (true) {
            $aircraftJson = json_decode(file_get_contents(config('config.AircraftJsonLocation')), true) ?? [];

            $noGpsCoordinates = 0;
            $notInPolygon     = 0;
            $tooFar           = 0;
            $sawRecently      = 0;
            $noAeroApiFlight  = 0;
            $hexDBFailed      = 0;
            $tweeted          = 0;

            foreach (Arr::get($aircraftJson, 'aircraft') ?? [] as $aircraft) {
                if (Arr::get($aircraft, 'lat') === null) {
                    $noGpsCoordinatesTotal++;
                    $noGpsCoordinates++;
                    continue;
                }

                $distance = $this->distance(Arr::get($aircraft, 'lat'), Arr::get($aircraft, 'lon'), config('config.MyLatitude'), config('config.MyLongitude'));

                if (config('config.SearchRadiusMechanism') === 'polygon') {
                    $polygon = explode(',', config('config.SearchRadiusPolygon'));

                    $geofence = new Polygon();

                    foreach ($polygon as $latitudeAndLongitudeString) {
                        $latitudeAndLongitudeStringSplit = explode(' ', $latitudeAndLongitudeString);
                        $geofence->addPoint(new Coordinate($latitudeAndLongitudeStringSplit[0], $latitudeAndLongitudeStringSplit[1]));
                    }

                    $aircraftCoordinate = new Coordinate(Arr::get($aircraft, 'lat'), Arr::get($aircraft, 'lon'));

                    if (!$geofence->contains($aircraftCoordinate)) {
                        $notInPolygonTotal++;
                        $notInPolygon++;
                        continue;
                    }
                } elseif (config('config.SearchRadiusMechanism') === 'distance') {
                    if ($distance > config('config.SearchRadiusMiles')) {
                        $tooFarTotal++;
                        $tooFar++;
                        continue;
                    }
                } else {
                    $this->error('config.SearchRadiusMechanism is not configured properly');
                }

                try {
                    $registrationCode = (new HexDB())->getRegistrationCode(Arr::get($aircraft, 'hex'));
                } catch (\Exception $e) {
                    $hexDBFailed++;
                    $hexDBFailedTotal++;
                    continue;
                }

                Arr::set($aircraft, 'registration', (new HexDB())->getRegistrationCode(Arr::get($aircraft, 'hex')));
                Arr::set($aircraft, 'latitude', Arr::get($aircraft, 'lat'));
                Arr::set($aircraft, 'longitude', Arr::get($aircraft, 'lon'));
                Arr::set($aircraft, 'knots', Arr::get($aircraft, 'gs'));
                Arr::set($aircraft, 'altitude', Arr::get($aircraft, 'alt_baro'));

                $sameAircraftInLastXMinutes = Aircraft::where(
                    fn($query) => $query->where('registration', Arr::get($aircraft, 'registration'))
                                        ->orWhere('hex', Arr::get($aircraft, 'hex'))
                )
                                                      ->where('created_at', '>=', now()->subMinutes(config('config.SpecificAircraftTweetMinutes')))
                                                      ->count();

                if ($sameAircraftInLastXMinutes > 0) {
                    $sawRecentlyTotal++;
                    $sawRecently++;
                    continue;
                }

                $dbAircraft = new Aircraft();
                $dbAircraft->fill($aircraft);
                $dbAircraft->flight = Arr::get($aircraft, 'flight') !== null ? trim(Arr::get($aircraft, 'flight')) : Arr::get($aircraft, 'registration');
                $dbAircraft->save();

                $aeroApi = new AeroAPI(config('config.AeroAPIToken'));

                $aeroApiFlight = $aeroApi->getFlight($dbAircraft->flight);

                if ($aeroApiFlight === null) {
                    $noAeroApiFlightTotal++;
                    $noAeroApiFlight++;
                    continue;
                }

                $dbAircraft->flight = Arr::get($aeroApiFlight, 'ident');
                $dbAircraft->save();

                $aircraftType = null;
                if (strlen(Arr::get($aeroApiFlight, 'aircraft_type') ?? '') > 0) {
                    $aeroApiAircraftType = $aeroApi->getAircraftType(Arr::get($aeroApiFlight, 'aircraft_type'));

                    if ($aeroApiAircraftType !== null) {
                        $aircraftType = AircraftType::firstOrCreate([
                                                                        'type' => Arr::get($aeroApiAircraftType, 'type'),
                                                                    ],
                                                                    [
                                                                        'manufacturer' => Arr::get($aeroApiAircraftType, 'manufacturer'),
                                                                        'description'  => Arr::get($aeroApiAircraftType, 'description'),
                                                                        'engine_count' => Arr::get($aeroApiAircraftType, 'engine_count'),
                                                                        'engine_type'  => Arr::get($aeroApiAircraftType, 'engine_type'),
                                                                    ]);
                    }
                }

                $aircraftOperator = null;
                if (strlen(Arr::get($aeroApiFlight, 'operator') ?? '') > 0) {
                    $aeroApiOperator = $aeroApi->getOperator(Arr::get($aeroApiFlight, 'operator'));

                    if ($aeroApiOperator !== null) {
                        $aircraftOperator = AircraftOperator::firstOrCreate([
                                                                                'icao' => Arr::get($aeroApiOperator, 'icao'),
                                                                            ], [
                                                                                'iata'      => Arr::get($aeroApiOperator, 'iata'),
                                                                                'callsign'  => Arr::get($aeroApiOperator, 'callsign'),
                                                                                'name'      => Arr::get($aeroApiOperator, 'name'),
                                                                                'country'   => Arr::get($aeroApiOperator, 'country'),
                                                                                'location'  => Arr::get($aeroApiOperator, 'location'),
                                                                                'phone'     => Arr::get($aeroApiOperator, 'phone'),
                                                                                'shortname' => Arr::get($aeroApiOperator, 'shortname'),
                                                                                'url'       => Arr::get($aeroApiOperator, 'url'),
                                                                                'wiki_url'  => Arr::get($aeroApiOperator, 'wiki_url'),
                                                                            ]);
                    }
                }

                $message = sprintf('Flight #%s', $dbAircraft->flight) . PHP_EOL;

                if ($aircraftOperator !== null) {
                    $operatorName = $aircraftOperator->name;

                    if (strlen($aircraftOperator->shortname ?? '') > 0) {
                        $operatorName = $aircraftOperator->shortname;
                    }

                    $message .= $operatorName . PHP_EOL;
                }

                $message .= sprintf('%s miles away', number_format($distance, 2)) . PHP_EOL;

                if (Arr::get($aeroApiFlight, 'position_only') ?? false) {
                    $message .= sprintf('Origin: %s', Arr::get($aeroApiFlight, 'origin.code')) . PHP_EOL;
                } else {
                    $message .= sprintf('%s to %s', Arr::get($aeroApiFlight, 'origin.code'), Arr::get($aeroApiFlight, 'destination.code')) . PHP_EOL;
                }

                if ($aircraftType !== null) {
                    $message .= sprintf('%s %s (%s)', $aircraftType->manufacturer, $aircraftType->type, Arr::get($aeroApiFlight, 'registration')) . PHP_EOL;
                }

                $message .= sprintf('%smph', number_format($dbAircraft->knots * 1.15077945, 2)) . PHP_EOL;

                $message .= sprintf('%sft', number_format($dbAircraft->altitude)) . PHP_EOL;

                $urlBase = $dbAircraft->flight === $dbAircraft->hex ? 'https://flightaware.com/live/modes/%s/redirect' : 'https://flightaware.com/live/flight/%s';
                $url     = sprintf($urlBase, $dbAircraft->flight);

                $message .= $url;

                $connection = new TwitterOAuth(config('config.TwitterConsumerKey'), config('config.TwitterConsumerSecret'), config('config.TwitterAccessToken'), config('config.TwitterAccessTokenSecret'));
                $connection->post('statuses/update', ['status' => $message]);

                $this->info(sprintf('%s %s %s tweet: %d', $dbAircraft->hex, $dbAircraft->flight, $dbAircraft->registration, $connection->getLastHttpCode()));

                $tweetedTotal++;
                $tweeted++;
            }

            system('clear');
            $this->table(
                headers: ['', 'No Coordinates', 'Outside Polygon', 'Outside Range', 'Checked Too Recently', 'No AeroAPI Details', 'HexDB Failure', 'Tweeted',],
                rows   : [
                             ['This Run', $noGpsCoordinates, $notInPolygon, $tooFar, $sawRecently, $noAeroApiFlight, $hexDBFailed, $tweeted],
                             ['Total', $noGpsCoordinatesTotal, $notInPolygonTotal, $tooFarTotal, $sawRecentlyTotal, $noAeroApiFlightTotal, $hexDBFailedTotal, $tweetedTotal],
                         ]
            );

            sleep(config('config.PollEverySeconds'));
        }
    }

    protected function distance($lat1, $lon1, $lat2, $lon2, $unit = 'M'): float|int
    {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
        } else {
            $theta = $lon1 - $lon2;
            $dist  = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist  = acos($dist);
            $dist  = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit  = strtoupper($unit);

            if ($unit == 'K') {
                return ($miles * 1.609344);
            } else {
                if ($unit == 'N') {
                    return ($miles * 0.8684);
                } else {
                    return $miles;
                }
            }
        }
    }

    /**
     * Define the command's schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}

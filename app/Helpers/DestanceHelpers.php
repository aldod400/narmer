<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class DestanceHelpers
{
    public static function getGoogleMapsDistanceAndDuration($originLat, $originLng, $destinationLat, $destinationLng)
    {
        $googleMapsUrl = "https://maps.googleapis.com/maps/api/directions/json?origin=$originLat,$originLng&destination=$destinationLat,$destinationLng&key=" . env('GOOGLE_MAP_KEY');

        $response = Http::get($googleMapsUrl);
        $data = $response->json();

        if (isset($data['routes'][0]['legs'][0]['distance']['value']) && isset($data['routes'][0]['legs'][0]['duration']['value'])) {
            $distanceInMeters = $data['routes'][0]['legs'][0]['distance']['value'];
            $durationInSeconds = $data['routes'][0]['legs'][0]['duration']['value'];

            $durationInMinutes = $durationInSeconds / 60;

            return [
                'distance' => $distanceInMeters,
                'duration' => $durationInMinutes,
            ];
        }

        return null;
    }
}

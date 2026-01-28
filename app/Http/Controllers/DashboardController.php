<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $zip = $request->input('zip') ?: '89502';
        $gauge = $request->input('gauge');



        //Convert ZIP → lat/lon
        $location = Http::get("https://api.zippopotam.us/us/{$zip}")->json();
        $state = $location['places'][0]['state abbreviation'] ?? null;
        $city = $location['places'][0]['place name'] ?? null;


        $lat = isset($location['places'][0]['latitude'])
            ? (float) $location['places'][0]['latitude']
            : null;

        $lon = isset($location['places'][0]['longitude'])
            ? (float) $location['places'][0]['longitude']
            : null;

        if (!$lat || !$lon) {
            return inertia('Dashboard', [
                'weather' => null,
                'zip' => $zip,
                'gageHeight' => null,
                'discharge' => null,
                'riverName' => "Invalid ZIP",
            ]);
        }

        // Weather stuff
        $weather = Http::get("https://api.open-meteo.com/v1/forecast", [
            'latitude' => $lat,
            'longitude' => $lon,
            'current' => ['temperature_2m', 'precipitation'],
        ])->json()['current'] ?? null;

        //River stuff
        if ($gauge) {
            $siteNumber = $gauge;
        } else {
            // No gauge entered, default to Truckee River at Reno
            $siteNumber = '10348000';
        }


        $usgs = Http::get("https://waterservices.usgs.gov/nwis/iv/", [
            'format' => 'json',
            'sites' => $siteNumber,
            'parameterCd' => '00065,00060', // height and discharge
        ])->json();

        $timeSeries = $usgs['value']['timeSeries'] ?? [];

        // get river name
        $siteName   = $timeSeries[0]['sourceInfo']['siteName'] ?? "Unknown gauge";
        $gageHeight = $timeSeries[0]['values'][0]['value'][0]['value'] ?? null;
        $discharge  = $timeSeries[1]['values'][0]['value'][0]['value'] ?? null;

        //Return everything to Vue
        return inertia('Dashboard', [
            'weather' => $weather,
            'zip' => $zip,
            'gauge' => $siteNumber,
            'gageHeight' => $gageHeight,
            'discharge' => $discharge,
            'riverName' => $siteName,
            'city' => $city,
            'state' => $state,

        ]);
    }
}

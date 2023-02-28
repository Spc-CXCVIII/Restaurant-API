<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RestaurantController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Bangkok');
        header('Content-Type: application/json');
    }

    public function SearchByKeyword(Request $request)
    {
        $credentials_data = $request->validate([
            'keyword' => 'nullable|required|string',
            'radius' =>  'nullable|required|int'
        ]);

        //! Get lat, lon from Keyword
        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'address' => $credentials_data['keyword'],
            'key' =>     env('GOOGLE_MAP_API_KEY'),
        ]);
        
        $results = $response->json();
        $lat = $results['results'][0]['geometry']['location']['lat'];
        $lng = $results['results'][0]['geometry']['location']['lng'];

        //! Find the restaurant
        $response = Http::get('https://maps.googleapis.com/maps/api/place/nearbysearch/json', [
            'location' => $lat.', '.$lng,
            'radius' =>   1500,
            'type' =>    'restaurant',
            'rankby' =>  'distance',
            'key' =>     env('GOOGLE_MAP_API_KEY'),
        ]);
        
        return $response->json();
    }
}

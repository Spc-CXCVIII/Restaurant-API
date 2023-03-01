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
      try
      {
        $credentials_data = $request->validate([
          'keyword' => 'nullable|required|string'
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

        $restaurant_list = [];
        $response = Http::get('https://maps.googleapis.com/maps/api/place/nearbysearch/json', [
            'location' => $lat . ', ' . $lng,
            'radius' => 100,
            'type' => 'restaurant',
            'key' => env('GOOGLE_MAP_API_KEY'),
        ]);
        
        $restaurant_list = array_merge($restaurant_list, $response['results']);
        while (isset($response['next_page_token'])) {
            sleep(1);
            $response = Http::get('https://maps.googleapis.com/maps/api/place/nearbysearch/json', [
                'pagetoken' => $response['next_page_token'],
                'key' => env('GOOGLE_MAP_API_KEY'),
            ]);
            $restaurant_list = array_merge($restaurant_list, $response['results']);
        }
        
        return $restaurant_list;
      }

      catch (\Exception $e)
      {
        return response()->json([
          'status' => 'error',
          'message' => $e->getMessage()
        ], 500);
      }
    }
}

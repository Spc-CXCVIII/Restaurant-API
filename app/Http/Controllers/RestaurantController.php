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
    try {
      $credentials_data = $request->validate([
        'keyword' => 'nullable|required|string'
      ]);

      //! Get lat, lon from Keyword
      $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
        'address' => $credentials_data['keyword'],
        'key' =>     env('GOOGLE_MAP_API_KEY'),
      ]);

      //! Check if keyword is valid
      if (count($response->json()['results']) === 0) {
        return response(
          array(
            'error_description' => 'Keyword not found',
            'status' => 'error'
          ),
          200
        );
      }

      //! Get lat, lon from Keyword
      $results = $response->json();
      $lat = $results['results'][0]['geometry']['location']['lat'];
      $lng = $results['results'][0]['geometry']['location']['lng'];

      //! Find the restaurant
      $restaurant_list = [];
      //! First query
      $response = Http::get('https://maps.googleapis.com/maps/api/place/nearbysearch/json', [
        'location' => $lat . ', ' . $lng,
        'radius' => 1500,
        'type' => 'restaurant',
        'key' => env('GOOGLE_MAP_API_KEY'),
      ]);

      //! Add $response['results'] to $restaurant_list
      $restaurant_list = array_merge($restaurant_list, $response['results']);
      //! Query next page if data $response has next_page_token
      while (isset($response['next_page_token'])) {
        sleep(1);
        $response = Http::get('https://maps.googleapis.com/maps/api/place/nearbysearch/json', [
          'pagetoken' => $response['next_page_token'],
          'key' => env('GOOGLE_MAP_API_KEY'),
        ]);
        $restaurant_list = array_merge($restaurant_list, $response['results']);
      }

      if (count($restaurant_list) === 0) {
        return response(
          array(
            'error_description' => 'Keyword not found',
            'status' => 'error'
          ),
          200
        );
      }

      //! Return the result
      return response(
        array(
          'results' => $restaurant_list,
          'status' => 'ok'
        ),
        200
      );
    } catch (\Exception $e) {
      return response(
        array(
          'error_description' => $e->getMessage(),
          'status' => 'error'
        ),
        200
      );
    }
  }
}

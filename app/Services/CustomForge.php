<?php
/**
 * Created by PhpStorm.
 * User: te7a
 * Date: 18/07/17
 * Time: 10:02 ص
 */

namespace App\Services;

use Themsaid\Forge\MakesHttpRequests;
use GuzzleHttp\Client as HttpClient;

class CustomForge {

    use MakesHttpRequests;

    public function __construct(HttpClient $guzzle = null)
    {
        $this->guzzle = $guzzle ?: new HttpClient([
            'base_uri' => 'https://forge.laravel.com/api/v1/',
            'http_errors' => false,
            'headers' => [
                'Authorization' => 'Bearer '.config('forge.token'),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ]);

    }

    public function createServer($data)
    {
        return $this->post('servers', $data);
    }


}
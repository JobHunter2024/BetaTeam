<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class SparqlService
{
    public $queryEndpoint;
    public $updateEndpoint;
    protected $user;
    protected $password;

    public function __construct()
    {
    }

    /**
     * Executes a SPARQL query and returns the response.
     *
     * @param string $query The SPARQL query.
     * @param string $format The response format (e.g., json, xml).
     * @return mixed
     * @throws Exception if the query fails.
     */
    public static function query($query, $format = 'json')
    {
        $response = Http::withBasicAuth(
            config('services.jobhunter_query.username'),
            config('services.jobhunter_query.password')
        )
            ->withHeaders([
                'Accept' => 'application/sparql-results+json', // Ensure JSON response
                'Content-Type' => 'application/x-www-form-urlencoded'
            ])
            ->asForm()->post(config('services.jobhunter_query.url'), [
                    'query' => $query,
                ]);

        if ($response->ok()) {
            return $response->json(); // Return JSON-decoded response
        }

        throw new Exception("SPARQL query failed: " . $response->body());
    }

    /**
     * Executes a SPARQL update query and returns the response.
     *
     * @param string $sparqlQuery The SPARQL update query.
     * @return string The response body.
     * @throws Exception if the update fails.
     */
    public static function executeUpdate($sparqlQuery)
    {
        // Execute the INSERT query with authentication
        $response = Http::withBasicAuth(
            config('services.jobhunter_update.username'),
            config('services.jobhunter_update.password')
        )
            ->asForm()->post(config('services.jobhunter_update.url'), [
                    'update' => $sparqlQuery,
                ]);

        if (!$response->successful()) {
            throw new Exception("SPARQL update failed: " . $response->body());
        }

        //dd($response, $sparqlQuery);
        return $response->body();
    }
}

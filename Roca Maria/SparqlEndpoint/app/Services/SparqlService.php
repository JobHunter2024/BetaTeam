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
        $this->queryEndpoint = env('FUSEKI_QUERY_ENDPOINT');
        $this->updateEndpoint = env('FUSEKI_UPDATE_ENDPOINT');
        $this->user = env('SPARQL_USER');
        $this->password = env('SPARQL_PASSWORD');
    }

    /**
     * Executes a SPARQL query and returns the response.
     *
     * @param string $query The SPARQL query.
     * @param string $format The response format (e.g., json, xml).
     * @return mixed
     * @throws Exception if the query fails.
     */
    public function query($query, $format = 'json')
    {
        $response = Http::withBasicAuth($this->user, $this->password)
            ->asForm()
            ->post($this->queryEndpoint, [
                'query' => $query,
                'format' => $format,
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
    public function executeUpdate($sparqlQuery)
    {
        $response = Http::withBasicAuth($this->user, $this->password)
            ->asForm()
            ->post($this->updateEndpoint, [
                'update' => $sparqlQuery,
            ]);

        if (!$response->successful()) {
            throw new Exception("SPARQL update failed: " . $response->body());
        }

        return $response->body();
    }
}

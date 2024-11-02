<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SparqlService
{
    protected $endpoint;
    protected $user;
    protected $password;

    public function __construct()
    {
        $this->endpoint = env('SPARQL_ENDPOINT');
        $this->user = env('SPARQL_USER');
        $this->password = env('SPARQL_PASSWORD');
    }

    /**
     * Executes a SPARQL query and returns the response.
     *
     * @param string $query The SPARQL query.
     * @param string $format The response format (e.g., json, xml).
     * @return mixed
     */
    public function query($query, $format = 'json')
    {
        $response = Http::withBasicAuth($this->user, $this->password)
            ->asForm() // Send query as form data
            ->post($this->endpoint, [
                'query' => $query,
                'format' => $format,
            ]);

        if ($response->ok()) {
            return $response->json(); // Returns JSON-decoded response
        }

        // Handle errors
        return [
            'error' => 'Failed to retrieve data',
            'status' => $response->status(),
        ];
    }
}

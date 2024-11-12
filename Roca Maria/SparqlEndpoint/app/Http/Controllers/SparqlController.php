<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SparqlService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class SparqlController extends Controller
{
    protected $sparqlService;

    public function __construct(SparqlService $sparqlService)
    {
        $this->sparqlService = $sparqlService;
    }

    /**
     * Handle SPARQL query requests.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function query(Request $request)
    {
        $query = $request->input('query');
        $format = $request->input('format', 'json'); // Default to JSON format

        // Log the query request
        Log::info("Executing SPARQL query", ['query' => $query, 'format' => $format]);

        // Execute the query using SparqlService
        $response = $this->sparqlService->query($query, $format);

        // Log the response or error
        if (isset($response['error'])) {
            Log::error("SPARQL query failed", ['response' => $response]);
        } else {
            Log::info("SPARQL query succeeded", ['response' => $response]);
        }

        return response()->json($response);
    }
}
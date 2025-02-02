<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SparqlService;
use Illuminate\Support\Facades\Log;
use App\Services\Map\CoordinatesService;
use App\Services\Map\GeocodingService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class MapController extends Controller
{
    protected $sparqlService;
    protected $geocodingService;

    public function __construct(SparqlService $sparqlService, GeocodingService $geocodingService)
    {
        $this->sparqlService = $sparqlService;
        $this->geocodingService = $geocodingService;

    }

    public function getMapData()
    {
        $sparqlQuery = '
        PREFIX jh: <http://www.semanticweb.org/ana/ontologies/2024/10/JobHunterOntology#> 

        SELECT ?job ?jobTitle ?companyName ?location ?employmentType ?datePosted 
        WHERE {  
            ?job a jh:Job ;        
                jh:jobTitle ?jobTitle ;
                jh:postedByCompany ?company .  
            ?company jh:companyName ?companyName .  
            OPTIONAL { ?job jh:jobLocation ?location . } 
            OPTIONAL { ?job jh:employmentType ?employmentType . }  
            OPTIONAL { ?job jh:datePosted ?datePosted . }  
            OPTIONAL { ?job jh:dateRemoved ?dateRemoved . }  # Data când jobul a fost eliminat

            # Filtrăm joburile localizate în România
            FILTER(CONTAINS(LCASE(?location), "romania"))

            # Excludem joburile care au fost eliminate
            FILTER(!BOUND(?dateRemoved))
        }
        ORDER BY DESC(?datePosted)';

        $response = $this->sparqlService->query($sparqlQuery, 'json');

        if (empty($response)) {
            return response()->json(['error' => 'No jobs data found'], 404);
        }

        $jobs = $response['results']['bindings'] ?? [];

        foreach ($jobs as &$job) {
            $locationString = $job['location']['value'] ?? null;
            $jobIRI = $job['job']['value'] ?? null; // Adăugăm IRI-ul jobului

            if ($locationString) {
                $coordinates = $this->geocodingService->getCoordinates($locationString);
                $job['coordinates'] = $coordinates;
            } else {
                $job['coordinates'] = null;
            }

            $job['jobIRI'] = $jobIRI; // Adăugăm IRI-ul în răspuns
        }

        return response()->json($jobs);
    }

    // public function getMapData()
    // {
    //     $sparqlQuery =

    //         'PREFIX jh: <http://www.semanticweb.org/ana/ontologies/2024/10/JobHunterOntology#> 

    //     SELECT ?jobTitle ?companyName ?location ?employmentType ?datePosted 
    //     WHERE {  
    //         ?job a jh:Job ;        
    //             jh:jobTitle ?jobTitle ;
    //             jh:postedByCompany ?company .  
    //         ?company jh:companyName ?companyName .  
    //         OPTIONAL { ?job jh:jobLocation ?location . } 
    //         OPTIONAL { ?job jh:employmentType ?employmentType . }  
    //         OPTIONAL { ?job jh:datePosted ?datePosted . }

    //         # Filter jobs located in Romania
    //         FILTER(CONTAINS(LCASE(?location), "romania"))
    //     }
    //     ORDER BY DESC(?datePosted)';

    //     $response = $this->sparqlService->query($sparqlQuery, 'json');

    //     if (empty($response)) {
    //         return response()->json(['error' => 'No jobs data found'], 404);
    //     }

    //     $jobs = $response['results']['bindings'] ?? [];

    //     foreach ($jobs as &$job) {
    //         $locationString = $job['location']['value'] ?? null;

    //         if ($locationString) {
    //             $coordinates = $this->geocodingService->getCoordinates($locationString);
    //             $job['coordinates'] = $coordinates;
    //         } else {
    //             $job['coordinates'] = null;
    //         }
    //     }

    //     return response()->json($jobs);
    // }

    public function getJobsMapData()
    {
        // Define SPARQL query to fetch job locations
        $sparqlQuery =
            'PREFIX jh: <http://www.semanticweb.org/ana/ontologies/2024/10/JobHunterOntology#> 

            SELECT ?jobTitle ?companyName ?location ?employmentType ?datePosted 
            WHERE {  
                ?job a jh:Job ;        
                    jh:jobTitle ?jobTitle ;
                    jh:postedByCompany ?company .  
                ?company jh:companyName ?companyName .  
                OPTIONAL { ?job jh:jobLocation ?location . } 
                OPTIONAL { ?job jh:employmentType ?employmentType . }  
                OPTIONAL { ?job jh:datePosted ?datePosted . }

                # Filter jobs located in Romania
                FILTER(CONTAINS(LCASE(?location), "romania"))
            }
            ORDER BY DESC(?datePosted)';

        // Fetch job data from SPARQL service
        $response = $this->sparqlService->query($sparqlQuery, 'json');

        if (empty($response)) {
            return response()->json(['error' => 'No location data found'], 404);
        }

        $eventData = [];
        $eventsData = [];

        foreach ($response as $event) {
            //  dd($event);
            //$locationString = $event['location']['value'];
            //   dd($locationString);

            $locationString = $event['city']['value'];
            // // Convert location string to coordinates using OpenStreetMap Nominatim API
            // $geoResponse = Http::get("https://nominatim.openstreetmap.org/search", [
            //     'q' => $locationString,
            //     'format' => 'json',
            //     'limit' => 1,
            // ]);
            //$locationString = "Krasnodar Krai, Russian Federation";

            // Convert location string to coordinates using OpenStreetMap Nominatim API
            $geoResponse = Http::withHeaders([
                'User-Agent' => 'YourAppName/1.0 (your@email.com)' // Replace with your app name and contact info
            ])->get("https://nominatim.openstreetmap.org/search", [
                        'q' => $locationString,
                        'format' => 'json',
                        'limit' => 1,
                    ]);

            // Check if the request was successful
            if ($geoResponse->successful()) {
                $data = $geoResponse->json();

                // Add coordinates to the response
                $eventData['location'] = [
                    'latitude' => $data[0]['lat'],
                    'longitude' => $data[0]['lon'],
                ];
                // add event title
                $eventData['eventTitle'] = $event['eventTitle']['value'];

                // Add event date
                $eventData['eventDate'] = $event['eventDate']['value'];

                // Check if the response contains data
                if (!empty($data)) {
                    //   dd($data); // Dump the first result
                } else {
                    //   dd('No results found for the given location.');
                }
            } else {
                //   dd('Failed to retrieve data from the Nominatim API.');
            }
            // Add event data to the events array
            $eventsData[] = $eventData;
        }

        return response()->json($eventsData);
    }
}
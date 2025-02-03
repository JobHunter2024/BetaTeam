<?php

namespace App\Http\Controllers;

use DateTime;
use Illuminate\Http\Request;
use App\Services\SparqlService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Services\Map\GeocodingService;
use App\Services\Map\CoordinatesService;

class MapController extends Controller
{
    protected $sparqlService;
    protected $geocodingService;

    public function __construct(SparqlService $sparqlService, GeocodingService $geocodingService)
    {
        $this->sparqlService = $sparqlService;
        $this->geocodingService = $geocodingService;

    }

    public function getMapData(Request $request)
    {
        $filters = [
            'employmentType' => $request->input('employmentType'),
            'experienceLevel' => $request->input('experienceLevel'),
            'jobLocationType' => $request->input('jobLocationType'),
            'dateRange' => $request->input('dateRange', 'all'),
        ];

        $sparqlQuery = $this->buildSparqlQuery($filters);

        $response = $this->sparqlService->query($sparqlQuery, 'json');

        if (empty($response)) {
            return response()->json(['error' => 'No jobs data found'], 404);
        }

        $jobs = $response['results']['bindings'] ?? [];

        foreach ($jobs as &$job) {
            $locationString = $job['location']['value'] ?? null;
            $jobIRI = $job['job']['value'] ?? null;

            if ($locationString) {
                $coordinates = $this->geocodingService->getCoordinates($locationString);
                $job['coordinates'] = $coordinates;
            } else {
                $job['coordinates'] = null;
            }

            $job['jobIRI'] = $jobIRI;
        }

        return response()->json($jobs);
    }

    private function buildSparqlQuery(array $filters): string
    {
        $query = '
            PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
            PREFIX jh: <http://www.semanticweb.org/ana/ontologies/2024/10/JobHunterOntology#> 
            SELECT ?job ?jobTitle ?companyName ?location ?employmentType 
                ?datePosted ?experienceLevel ?jobLocationType
            WHERE {  
                ?job a jh:Job ;        
                    jh:jobTitle ?jobTitle ;
                    jh:postedByCompany ?company .  
                ?company jh:companyName ?companyName .  
                OPTIONAL { ?job jh:jobLocation ?location . }
              OPTIONAL { ?job jh:dateRemoved ?dateRemoved . }';

        // Employment Type filter
        if ($filters['employmentType']) {
            $query .= "\n    ?job jh:employmentType \"{$filters['employmentType']}\" .";
        } else {
            $query .= "\n    OPTIONAL { ?job jh:employmentType ?employmentType . }";
        }

        // Experience Level filter
        if ($filters['experienceLevel']) {
            $query .= "\n    ?job jh:experienceLevel \"{$filters['experienceLevel']}\" .";
        } else {
            $query .= "\n    OPTIONAL { ?job jh:experienceLevel ?experienceLevel . }";
        }

        // Location Type filter
        if ($filters['jobLocationType']) {
            $query .= "\n    ?job jh:jobLocationType \"{$filters['jobLocationType']}\" .";
        } else {
            $query .= "\n    OPTIONAL { ?job jh:jobLocationType ?jobLocationType . }";
        }

        // Date Range filter
        if ($filters['dateRange'] && $filters['dateRange'] !== 'all') {
            $startDate = $this->calculateStartDate($filters['dateRange']);
            $query .= "\n    ?job jh:datePosted ?datePosted .";
            $query .= "\n    FILTER (?datePosted >= \"$startDate\"^^xsd:date)";
        } else {
            $query .= "\n    OPTIONAL { ?job jh:datePosted ?datePosted . }";
        }
        $query .= '
                FILTER(CONTAINS(LCASE(?location), "romania"))
                FILTER(!BOUND(?dateRemoved))
            }
            ORDER BY DESC(?datePosted)';

        return $query;
    }

    private function calculateStartDate(string $dateRange): string
    {
        $now = new DateTime();
        switch ($dateRange) {
            case 'lastWeek':
                $now->modify('-1 week');
                break;
            case 'lastMonth':
                $now->modify('-1 month');
                break;
            case 'last3Months':
                $now->modify('-3 months');
                break;
            case 'lastYear':
                $now->modify('-1 year');
                break;
            default:
                return '';
        }
        return $now->format('Y-m-d');
    }
    public function getEventsMapData(Request $request)
    {
        $sparqlQuery = '
       PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX jh: <http://www.semanticweb.org/ana/ontologies/2024/10/JobHunterOntology#>
PREFIX owl: <http://www.w3.org/2002/07/owl#>

SELECT DISTINCT ?event ?eventTitle ?cityLabel  ?country ?eventDate ?eventType
WHERE {
    # Selectăm toate evenimentele
    ?event a jh:Event ;
           rdfs:label ?eventTitle .
    
  
    
    # Proprietăți optionale
    OPTIONAL { ?event jh:eventDate ?eventDate }
    OPTIONAL { ?event jh:eventType ?eventType }
    
   
}
ORDER BY DESC(?eventDate)';

        $response = $this->sparqlService->query($sparqlQuery, 'json');

        if (empty($response)) {
            return response()->json(['error' => 'No events data found'], 404);
        }

        $events = $response['results']['bindings'] ?? [];
        $processedEvents = [];

        foreach ($events as $event) {
            $cityString = $event['city']['value'] ?? null;
            $eventIRI = $event['event']['value'] ?? null;

            if ($cityString) {
                $coordinates = $this->geocodingService->getCoordinates($cityString . ', Romania');

                $processedEvent = [
                    'eventIRI' => $eventIRI,
                    'title' => $event['eventTitle']['value'],
                    'date' => $event['eventDate']['value'],
                    'city' => $cityString,
                    'coordinates' => $coordinates,
                    'isOnline' => isset($event['isOnline']) ? ($event['isOnline']['value'] === 'true') : false,
                    'eventType' => $event['eventType']['value'] ?? 'Not specified',
                    'topic' => $event['topic']['value'] ?? null,
                    'relatedJobs' => []
                ];

                // Add related job if exists
                if (isset($event['relatedJob'])) {
                    $processedEvent['relatedJobs'][] = [
                        'jobIRI' => $event['relatedJob']['value'],
                        'title' => $event['jobTitle']['value'],
                        'company' => $event['companyName']['value']
                    ];
                }

                $processedEvents[] = $processedEvent;
            }
        }

        return response()->json($processedEvents);
    }
    // public function getEventMapData()
    // {
    //     $sparqlQuery = '
    //         PREFIX jh: <http://www.semanticweb.org/ana/ontologies/2024/10/JobHunterOntology#> 
    //         SELECT ?event ?eventTitle ?eventDate ?cityName ?eventType ?isOnline ?eventURL
    //         WHERE {
    //             ?event a jh:Event ;
    //                 jh:eventTitle ?eventTitle ;
    //                 jh:takesPlaceIn ?city .
    //             ?city jh:cityName ?cityName .
    //             OPTIONAL { ?event jh:eventDate ?eventDate . }
    //             OPTIONAL { ?event jh:eventType ?eventType . }
    //             OPTIONAL { ?event jh:isOnline ?isOnline . }
    //             OPTIONAL { ?event jh:eventURL ?eventURL . }
    //         }
    //         ORDER BY DESC(?eventDate)';

    //     $response = $this->sparqlService->query($sparqlQuery, 'json');

    //     if (empty($response)) {
    //         return response()->json(['error' => 'No events data found'], 404);
    //     }

    //     $events = $response['results']['bindings'] ?? [];

    //     foreach ($events as &$event) {
    //         $locationString = $event['cityName']['value'] ?? null;
    //         $eventIRI = $event['event']['value'] ?? null;

    //         if ($locationString) {
    //             $coordinates = $this->geocodingService->getCoordinates($locationString);
    //             $event['coordinates'] = $coordinates;
    //         } else {
    //             $event['coordinates'] = null;
    //         }

    //         $event['eventIRI'] = $eventIRI;
    //     }

    //     return response()->json($events);
    // }

    public function getEventsData(Request $request)
    {
        $filters = [
            'eventType' => $request->input('eventType'),
            'dateRange' => $request->input('dateRange', 'all'),
            'isFromRomania' => $request->input('isFromRomania', true),
        ];

        $sparqlQuery = $this->buildEventSparqlQuery($filters);
        $response = $this->sparqlService->query($sparqlQuery, 'json');

        if (empty($response)) {
            return response()->json(['error' => 'No events data found'], 404);
        }

        $events = $response['results']['bindings'] ?? [];

        foreach ($events as &$event) {
            $locationString = $event['location']['value'] ?? null;
            $eventIRI = $event['event']['value'] ?? null;

            if ($locationString) {
                $coordinates = $this->geocodingService->getCoordinates($locationString);
                $event['coordinates'] = $coordinates;
            } else {
                $event['coordinates'] = null;
            }

            $event['eventIRI'] = $eventIRI;

            // is from romania or România
            $event['isFromRomania'] = (strpos($locationString, 'Romania') || strpos($locationString, 'România')) !== false;
        }

        return response()->json($events);
    }

    private function buildEventSparqlQuery(array $filters): string
    {
        $query = '
        PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
        PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
        PREFIX jh: <http://www.semanticweb.org/ana/ontologies/2024/10/JobHunterOntology#> 
        SELECT ?event ?eventTitle ?eventType ?location ?eventDate ?eventURL
        WHERE {
            ?event a jh:Event ;
                jh:eventTitle ?eventTitle ;
                jh:eventType ?eventType ;
                jh:takesPlaceIn ?city .
                ?city rdfs:label ?location .
                OPTIONAL { ?event jh:eventDate ?eventDate . }
                OPTIONAL { ?event jh:eventURL ?eventURL . }';


        // Event Type filter
        if ($filters['eventType']) {
            $query .= "    ?event jh:eventType \"{$filters['eventType']}\" .";
        }

        // Date Range filter
        if ($filters['dateRange'] && $filters['dateRange'] !== 'all') {
            $startDate = $this->calculateStartDate($filters['dateRange']);
            $query .= "    ?event jh:eventDate ?eventDate .";
            $query .= "    FILTER (?eventDate >= \"$startDate\"^^xsd:date)";
        }

        // Is from Romania filter
        // if ($filters['isFromRomania']) {
        //     $query .= "FILTER(CONTAINS(LCASE(?location), 'romania'))";
        // }
        $query .= '            }
            ORDER BY DESC(?eventDate)';
        ;
        return $query;
    }
}
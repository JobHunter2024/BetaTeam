<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Services\SparqlService;
use App\Services\TripleService;
use Illuminate\Support\Facades\Log;
use App\Services\Ontology\OntologyGenerator;
use App\Events\DataValidationEvent;
use App\Events\TripleGenerationEvent;
use App\Events\TripleInsertionEvent;


class TriplesController extends Controller
{
    protected $tripleService;
    protected $sparqlService;

    public function __construct(TripleSErvice $tripleService, SparqlService $sparqlService)
    {
        $this->tripleService = $tripleService;
        $this->sparqlService = $sparqlService;
    }

    /**
     * Display a listing of the triples.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $triples = $this->tripleService->all();
        // return response()->json($triples);
        return response()->json();
    }

    /**
     * Stores a list of triples.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Post(
     *     path="/api/triples/store",
     *     summary="Store job-related triples in the knowledge base",
     *     tags={"Triples"},
     *     description="Stores RDF triples for jobs with batch processing and error logging",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Job data for triple generation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 required={"jobTitle", "company", "location"},
     *                 @OA\Property(property="date", type="string", example="27 May 2024"),
     *                 @OA\Property(property="jobTitle", type="string", example="Senior Developer"),
     *                 @OA\Property(property="company", type="string", example="Tech Corp"),
     *                 @OA\Property(property="location", type="string", example="New York, USA"),
     *                 @OA\Property(property="jobDescription", type="string", example="What you need to know: - .NET Core, ASP.NET MVC, Web API - C# - HTML and CSS"),
     *            ),
     *             example={
     *                 {"date": "12 December 2024", "jobTitle": "Backend Engineer", "company": "StartupX", "location": "Remote", "jobDescription": "Experience with Node.js, Express, MongoDB"},
     *                 {"date": "15 November 2024", "jobTitle": "Data Scientist", "company": "AI Labs", "location": "London, UK", "jobDescription": "Python, TensorFlow, Keras"}
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Triples stored successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="inserted_triples", type="integer", example=42),
     *             @OA\Property(property="errors", type="array", 
     *                 @OA\Items(type="string", example="Error processing job 3: Invalid data format")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="No data provided")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="jobTitle", type="array",
     *                     @OA\Items(type="string", example="The jobTitle field is required")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function storeJobTriples(Request $request)
    {
        $error = "";
        try {
            $request_json = $request->json()->all();

            Log::info('Received POST data', ['data' => $request_json]); // Log incoming data

            //dd($request_json);
            if (empty($request_json)) {
                Log::warning('No data provided in POST request');
                return response()->json(['error' => 'No data provided'], 400);
            }

            // insert entities and propersties
            $baseUri = config('ontology.base_uri');
            $generator = new OntologyGenerator($baseUri);
            $entitiesTriples = $generator->generate();
            //  dd($entitiesTriples);

            foreach ($entitiesTriples as $triple) {
                // Insert triples into Fuseki
                $response = $this->tripleService->insertTriples($triple);
                //   dd($response);
                //------  event(new TripleInsertionEvent($response));

                if ($response['status'] !== 200) {
                    Log::error('Triple insertion failed', ['triple' => $triple, 'response' => $response]);
                } else {
                    Log::info('Triple inserted successfully', ['triple' => $triple]);
                }
            }

            // Împarte datele în batch-uri de câte 5
            $batches = array_chunk($request_json, 5);

            foreach ($batches as $batchIndex => $batch) {
                Log::info("Processing batch #" . ($batchIndex + 1));
                //  dd($batch);
                foreach ($batch as $key => $value) {

                    //---- event(new DataValidationEvent($value));

                    // Log ontology creation start
                    Log::info("Processing job data for key: {$key}");

                    // Execute python script for ontology creation
                    $data = $this->tripleService->executeScript($value);

                    if ($data['output'] != null) {

                        // Prepare triples
                        $triples = $this->tripleService->prepareIndividualTriples($data['output']);

                        //---- event(new TripleGenerationEvent($triples['output']));

                        if (!empty($triples['output'])) {

                            foreach ($triples['output'] as $triple) {
                                // Insert triples into Fuseki
                                $response = $this->tripleService->insertTriples($triple);
                            }
                        } else {
                            $error .= "Error for job {$key}: " . $triples["error"] . "\n";
                            Log::error('Triple preparation failed', ['key' => $key, 'error' => $triples['error']]);
                        }

                    } else {
                        $error .= "Error for job {$key}: " . $data["error"] . "\n";
                        Log::error('Ontology creation failed', ['key' => $key, 'error' => $data['error']]);
                    }
                }
                $response["errors"] = $error;
            }
            return response()->json(
                $response
            );

        } catch (Exception $e) {
            Log::error('Unexpected error occurred', ['exception' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created triple in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Post(
     *     path="/api/triples/storeEventTriple",
     *     summary="Store event-related RDF triples in the knowledge base",
     *     tags={"Triples"},
     *     description="Stores event triples with batch processing and detailed status reporting",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Event data for triple generation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 required={"eventTitle", "location", "startDate"},
     *                 @OA\Property(property="eventTitle", type="string", example="Jazz Festival"),
     *                 @OA\Property(property="location", type="string", example="Sibiu, Romania"),
     *                 @OA\Property(property="startDate", type="string", format="date-time", example="2024-07-15T19:00:00Z"),
     *                 @OA\Property(property="endDate", type="string", format="date-time", example="2024-07-18T22:00:00Z"),
     *                 @OA\Property(property="eventType", type="string", example="music festival"),
     *                 @OA\Property(property="description", type="string", example="Annual international jazz event"),
     *                  @OA\Property(property="isOnline", type="boolean", example=true ),
     *                 @OA\Property(property="city", type="string", example="Sibiu"),
     *                 @OA\Property(property="country", type="string", example="Romania"),
     *                  @OA\Property(property="topicCategory", type="string", example="Framework"),
     *                  @OA\Property(property="topicCategoryDetails", type="string", example="Angular"),
     * ),
     *             example={
     *                 {
     *                     "eventTitle": "Tech Conference",
     *                     "location": "Cluj-Napoca",
     *                     "startDate": "2024-09-01T09:00:00Z"
     *                 }
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=207,
     *         description="Multi-status response with individual operation results",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Operation completed."),
     *             @OA\Property(property="results", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="triple", type="object",
     *                         @OA\Property(property="subject", type="string"),
     *                         @OA\Property(property="predicate", type="string"),
     *                         @OA\Property(property="object", type="string")
     *                     ),
     *                     @OA\Property(property="message", type="string", example="Triple inserted successfully."),
     *                     @OA\Property(property="status", type="integer", example=201),
     *                     @OA\Property(property="details", type="object", nullable=true)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input data",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No data provided!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error during triple preparation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to prepare triples."),
     *             @OA\Property(property="error", type="string", example="Specific error message")
     *         )
     *     )
     * )
     */
    public function storeEventTriple(Request $request)
    {
        $request_json = $request->json()->all();

        // Validate the input data
        if (empty($request_json)) {
            return response()->json([
                "message" => "No data provided!"
            ], 400); // Bad Request
        }

        try {

            // insert entities and propersties
            $baseUri = config('ontology.base_uri');
            $generator = new OntologyGenerator($baseUri);
            $entitiesTriples = $generator->generate();

            foreach ($entitiesTriples as $triple) {
                // Insert triples into Fuseki
                $response = $this->tripleService->insertTriples($triple);

                if ($response['status'] !== 200) {
                    Log::error('Triple insertion failed', ['triple' => $triple, 'response' => $response]);
                } else {
                    Log::info('Triple inserted successfully', ['triple' => $triple]);
                }
            }

            // Prepare triples
            $triples = $this->tripleService->prepareEventTriples($request_json);
        } catch (Exception $e) {
            return response()->json([
                "message" => "Failed to prepare triples.",
                "error" => $e->getMessage()
            ], 500); // Internal Server Error
        }

        $results = [];
        foreach ($triples['output'] as $triple) {
            try {
                // Insert triples into Fuseki
                $response = $this->tripleService->insertTriples($triple);

                if ($response['status'] === 201) {
                    $results[] = [
                        "triple" => $triple,
                        "message" => "Triple inserted successfully.",
                        "status" => 201
                    ];
                } else {
                    $results[] = [
                        "triple" => $triple,
                        "message" => $response['message'],
                        "status" => $response['status'],
                        "details" => $response['response'] ?? null
                    ];
                }
            } catch (Exception $e) {
                $results[] = [
                    "triple" => $triple,
                    "message" => "Failed to insert triple.",
                    "error" => $e->getMessage(),
                    "status" => 500
                ];
            }
        }

        // Return a summary of all results
        return response()->json([
            "message" => "Operation completed.",
            "results" => $results
        ], 207); // Multi-Status
    }

    /**
     * Display the specified triple.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // $triple = $this->tripleRepository->find($id);
        // return response()->json($triple);
        return response()->json();
    }

    /**
     * Update the specified triple in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'subject' => 'required|string',
            'predicate' => 'required|string',
            'object' => 'required|string',
        ]);

        // $triple = $this->tripleRepository->update($id, $validatedData);
        // return response()->json($triple);
        return response()->json();
    }

    /**
     * Remove the specified triple from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // $this->tripleRepository->delete($id);
        return response()->json(null, 204);
    }
    public function getSparqlData()
    {
        $query = "
            SELECT ?subject ?predicate ?object
            WHERE {
                ?subject ?predicate ?object.
            }
            LIMIT 10
        ";

        $results = $this->sparqlService->query($query);

        return response()->json($results);
    }

    public function executeScript(Request $request)
    {
        // Get input as JSON from the request
        $input = $request->json()->all();

        // Path to the Python script
        //$scriptPath = 'C:/xampp/htdocs/BetaTeam/CiobanuAna/Processing/services/processors/script.py';
        $scriptPath = base_path(env('PYTHON_SCRIPT_PATH'));

        // Encode the input as JSON
        $jsonArgument = json_encode($input, JSON_UNESCAPED_SLASHES);

        // Properly escape the JSON argument for the command
        $escapedJsonArgument = addcslashes($jsonArgument, '"'); // Escape double quotes for the shell

        // Construct the command
        $command = "python $scriptPath \"$escapedJsonArgument\"";
        // dd($jsonArgument, $escapedJsonArgument, $command);
        try {
            // Execute the command
            $output = shell_exec($command);
            //dd($output);
            if ($output === null) {
                throw new Exception('Error executing Python script.');
            }
            // Extract the JSON portion from the output
            preg_match('/\{.*\}/s', $output, $matches);

            if (empty($matches)) {
                throw new Exception('No valid JSON found in Python script output.');
            }

            $jsonOutput = $matches[0]; // The JSON part of the output
            $decodedOutput = json_decode($jsonOutput, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON output from Python script: ' . json_last_error_msg());
            }

            // Return the decoded output as a JSON response
            return response()->json(['output' => $decodedOutput], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


}
<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Triple;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Services\SparqlService;
use App\Services\TripleService;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Artisan;
use App\Repositories\TripleRepositoryInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

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

    public function test()
    {
        return "Hello, World!";
    }

    public function storeJobTriples(Request $request)
    {
        $error = "";
        try {
            $request_json = $request->json()->all();

            if (isset($request_json))
                foreach ($request_json as $key => $value) {
                    // Execute python script for ontology creation
                    $data = $this->tripleService->executeScript($value);
                    //dd($data['output']);
                    Log::info("Script output: " . json_encode($data['output']));
                    if ($data['output'] != null) {

                        // Prepare triples
                        $triples = $this->tripleService->prepareIndividualTriples($data['output']);
                        Log::info("Triples prepared" . json_encode($triples["output"]));

                        // dd($triples['output']);
                        if (!empty($triples['output'])) {

                            // insert entities and propersties
                            $entitiesTriples = $this->tripleService->createOntologyEntities();
                            foreach ($entitiesTriples as $triple) {
                                // Insert triples into Fuseki
                                $response = $this->tripleService->insertTriples($triple);
                            }

                            foreach ($triples['output'] as $triple) {
                                // Insert triples into Fuseki
                                $response = $this->tripleService->insertTriples($triple);
                            }
                        } else {
                            $error .= "Eroare pentru job: " . $key . " a aparut eroarea: " . $triples["error"] . "\n";
                        }

                        // return response()->json(
                        //     [
                        //         "message" => "Failed to insert",
                        //         "status" => $triples["status"],
                        //         "error" => $triples["error"]
                        //     ]
                        // );
                    } else {
                        $error .= "Eroare pentru job: " . $key . " a aparut eroarea: " . $data["error"] . "\n";
                    }
                    // return response()->json(
                    //     [
                    //         "message" => "Failed to execute script",
                    //         "status" => $data["status"],
                    //         "error" => $data["error"]
                    //     ]
                    // );
                }
            $response["errors"] = $error;
            return response()->json(
                $response
            );
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
        $scriptPath = 'C:/xampp/htdocs/BetaTeam/CiobanuAna/Processing/services/processors/script.py';

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
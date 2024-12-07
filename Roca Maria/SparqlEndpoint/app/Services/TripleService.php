<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class TripleService
{
    private $fusekiEndpoint = 'http://localhost:3030/jobHunterDataset/update';
    private $fusekiQueryEndpoint = 'http://localhost:3030/jobHunterDataset/query';

    public function validateJobData($data)
    {
        $validator = Validator::make($data, [
            'title' => 'required|string',
            'companyName' => 'required|string',
            'datePosted' => 'required|date_format:d/m/Y',
            'language_skills' => 'array',
            'hard_skills' => 'array',
            'soft_skills' => 'array',
            'degree_level' => 'array',
            'education_field' => 'array',
            'employment_type' => 'array',
            'experience_years' => 'array',
            'job_location' => 'array',
            'job_location_type' => 'array',
        ]);

        if ($validator->fails()) {
            throw new Exception('Validation failed: ' . $validator->errors()->first());
        }
    }

    public function prepareTriples($data)
    {
        $triples = "
            _:job rdf:type <http://example.org/JobPosting> ;
                :hasTitle \"" . addslashes(trim($data['title'])) . "\" ;
                :hasCompany \"" . addslashes(trim($data['companyName'])) . "\" ;
                :datePosted \"" . addslashes(trim($data['datePosted'])) . "\" ;
                :employmentType \"" . addslashes(trim($data['employment_type'][0])) . "\" ;
                :experienceRequired \"" . addslashes(trim($data['experience_years'][0])) . "\" ;
                :locationType \"" . addslashes(trim($data['job_location_type'][0])) . "\" ;
        ";

        // Add hard skills
        foreach ($data['hard_skills'] as $skill) {
            $triples .= ":requiresHardSkill \"" . addslashes(trim($skill)) . "\" ;\n";
        }

        // Add soft skills
        foreach ($data['soft_skills'] as $skill) {
            $triples .= ":requiresSoftSkill \"" . addslashes(trim($skill)) . "\" ;\n";
        }

        // Add degree levels
        foreach ($data['degree_level'] as $degree) {
            $triples .= ":requiresDegreeLevel \"" . addslashes(trim($degree)) . "\" ;\n";
        }

        // Add education fields
        foreach ($data['education_field'] as $field) {
            $triples .= ":requiresEducationField \"" . addslashes(trim($field)) . "\" ;\n";
        }

        // Add job location
        foreach ($data['job_location'] as $location) {
            $triples .= ":hasLocation \"" . addslashes(trim($location)) . "\" ;\n";
        }

        // End the triples block
        $triples .= ".\n";

        return $triples;
    }

    public function insertTriples($triples)
    {
        // Check if the triples already exist
        if ($this->tripleExists($triples)) {
            return response()->json([
                'message' => 'The triples already exist in the dataset.',
            ], 200);
        }

        // Construct the SPARQL INSERT query
        $sparqlUpdate = "
            PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
            PREFIX : <http://example.org#>
            
            INSERT DATA {
                $triples
            }
        ";

        // Execute the INSERT query
        $response = Http::asForm()->post($this->fusekiEndpoint, [
            'update' => $sparqlUpdate,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to insert triples into Fuseki: ' . $response->body());
        }

        // Check if the response body indicates success
        if (str_contains(strtolower($response->body()), 'update succeeded')) {
            return response()->json([
                'message' => 'Triples inserted successfully.',
            ], 201);
        }

        // Handle unexpected responses
        return response()->json([
            'message' => 'Triples inserted, but response was unexpected.',
            'response' => $response->body(),
        ], 202);
    }

    /**
     * Check if a triple exists in Fuseki
     */
    private function tripleExists($triples)
    {
        // Prepare the ASK query based on the provided triples
        $askQuery = "
            PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
            PREFIX : <http://example.org#>
            
            ASK {
                $triples
            }
        ";

        // Send the ASK query with the correct headers to request a JSON response
        $response = Http::withHeaders([
            'Accept' => 'application/json', // Request JSON response
        ])->asForm()->post($this->fusekiQueryEndpoint, [
                    'query' => $askQuery,
                ]);

        if ($response->failed()) {
            throw new Exception('Failed to query Fuseki: ' . $response->body());
        }

        // Decode the JSON response from Fuseki
        $askResult = json_decode($response->body(), true);

        // Log or debug the result
        if (!isset($askResult['boolean'])) {
            throw new Exception('Invalid response from Fuseki: ' . $response->body());
        }

        return $askResult['boolean'];
    }

    /**
     * Summary of executeScript
     * @param mixed $input
     * @throws \Exception
     * @return mixed
     */
    public function executeScript($input)
    {
        // Path to the Python script
        $scriptPath = 'C:/xampp/htdocs/BetaTeam/CiobanuAna/Processing/services/processors/script.py';

        // Encode the input as JSON
        $jsonArgument = json_encode($input, JSON_UNESCAPED_SLASHES);

        // Properly escape the JSON argument for the command
        $escapedJsonArgument = addcslashes($jsonArgument, '"'); // Escape double quotes for the shell

        // Construct the command
        $command = "python $scriptPath \"$escapedJsonArgument\"";

        //  try {
        // Execute the command
        $output = shell_exec($command);
        //dd($output);
        if ($output === null) {
            throw new Exception('Error executing Python script.');
        }
        // // Extract the JSON portion from the output
        // preg_match('/\{.*\}/s', $output, $matches);

        // if (empty($matches)) {
        //     throw new Exception('No valid JSON found in Python script output.');
        // }

        // Match all JSON objects in the string
        preg_match_all('/\{.*?\}/s', $output, $matches);

        if (empty($matches[0]) || count($matches[0]) < 2) {
            throw new Exception('Second JSON object not found.');
        }


        // Extract the second JSON object
        $jsonOutput = $matches[0][1]; // The JSON part of the output
        $jsonOutput = preg_replace('/[\r\n]+/', '', $jsonOutput);
        $jsonOutput = trim($jsonOutput, " ");

        $decodedOutput = json_decode($jsonOutput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON output from Python script: ' . json_last_error_msg());
        }

        return $decodedOutput;
    }



}
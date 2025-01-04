<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class TripleService
{
    public function validateJobData($data)
    {
        $validator = Validator::make($data, [
            'jobTitle' => 'required|string',
            'company' => 'required|string',
            'date' => 'required|date_format:d/m/Y',
            'location' => 'string',
            'jobDescription' => 'string',
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
        ";

        // Add experience years
        if (isset($data["experience_years"])) {
            if (!empty($data["experience_years"])) {
                foreach ($data['experience_years'] as $key => $value) {
                    $triples .= ":experienceYears \"" . addslashes(trim($value)) . "\" ;\n";
                }
            }
        }

        // Add experience level
        if (isset($data["experience_level"])) {
            $triples .= ":experienceLevel \"" . addslashes(trim($data["experience_level"])) . "\" ;\n";
        }


        // Add employment type
        if (isset($data["employment_type"])) {
            if (!empty($data["employment_type"])) {
                foreach ($data['employment_type'] as $key => $value) {
                    $triples .= ":employmentType \"" . addslashes(trim($value)) . "\" ;\n";
                }
            }
        }

        // Add soft skills
        if (isset($data["language_skills"])) {
            if (!empty($data["language_skills"])) {
                foreach ($data['language_skills'] as $skill) {
                    $triples .= ":requiresLanguageSkill \"" . addslashes(trim($skill)) . "\" ;\n";
                }
            }
        }

        // Add soft skills
        if (isset($data["soft_skills"])) {
            if (!empty($data["soft_skills"])) {
                foreach ($data['soft_skills'] as $skill) {
                    $triples .= ":requiresSoftSkill \"" . addslashes(trim($skill)) . "\" ;\n";
                }
            }
        }

        // Add degree levels
        if (isset($data["degree_level"])) {
            if (!empty($data["degree_level"])) {
                foreach ($data['degree_level'] as $degree) {
                    $triples .= ":requiresDegreeLevel \"" . addslashes(trim($degree)) . "\" ;\n";
                }
            }
        }

        // Add education fields
        if (isset($data["education_field"])) {
            if (!empty($data["education_field"])) {
                foreach ($data['education_field'] as $field) {
                    $triples .= ":requiresEducationField \"" . addslashes(trim($field)) . "\" ;\n";
                }
            }
        }

        // Add job location
        if (isset($data['job_location']))
            $triples .= ":hasLocation \"" . addslashes(trim($data['job_location'])) . "\" ;\n";


        // Add job location type
        if (isset($data['job_location_type'])) {
            if (!empty($data["job_location_type"])) {
                foreach ($data['job_location_type'] as $field) {
                    $triples .= ":hasLocationType \"" . addslashes(trim($field)) . "\" ;\n";
                }
            }
        }

        // Add programming languages
        if (isset($data["programming_languages"])) {
            if (!empty($data["programming_languages"])) {
                foreach ($data['programming_languages'] as $language) {
                    $triples .= ":requiresProgrammingLanguage \"" . addslashes(trim($language["skill_name"])) . "\" ;\n";

                    // Add influenced_by languages
                    if (!empty($language['influenced_by'])) {
                        foreach ($language['influenced_by'] as $influenced) {
                            $triples .= ":influencedBy \"" . addslashes(trim($influenced)) . "\" ;\n";
                        }
                    }

                    // Add programmed_in languages
                    if (!empty($language['programmed_in'])) {
                        foreach ($language['programmed_in'] as $programmed) {
                            $triples .= ":programmedIn \"" . addslashes(trim($programmed)) . "\" ;\n";
                        }
                    }
                }
            }
        }

        // Add unclasified skills
        if (isset($data["unclassified_skills"])) {
            if (!empty($data["unclassified_skills"])) {
                foreach ($data['unclassified_skills'] as $skill) {
                    $triples .= ":unclasifiedSkill \"" . addslashes(trim($skill)) . "\" ;\n";
                }
            }
        }

        // Add libraries
        if (isset($data["libraries"])) {
            if (!empty($data["libraries"])) {
                foreach ($data['libraries'] as $library) {
                    $triples .= ":requiresLibrary \"" . addslashes(trim($library['skill_name'])) . "\" ;\n";

                    // Add influenced_by libraries
                    if (!empty($library['influenced_by'])) {
                        foreach ($library['influenced_by'] as $influenced) {
                            $triples .= ":libraryInfluencedBy \"" . addslashes(trim($influenced)) . "\" ;\n";
                        }
                    }

                    // Add programmed_in languages
                    if (!empty($library['programmed_in'])) {
                        foreach ($library['programmed_in'] as $programmed) {
                            $triples .= ":libraryProgrammedIn \"" . addslashes(trim($programmed)) . "\" ;\n";
                        }
                    }
                }
            }
        }

        // Add frameworks
        if (isset($data["frameworks"])) {
            if (!empty($data["frameworks"])) {
                //Log::info("Frameworks" . json_encode($data["frameworks"]));

                foreach ($data['frameworks'] as $framework) {
                    $triples .= ":requiresFramework \"" . addslashes(trim($framework['skill_name'])) . "\" ;\n";

                    if (!empty($framework['influenced_by'])) {
                        foreach ($framework['influenced_by'] as $influenced) {
                            $triples .= ":frameworkInfluencedBy \"" . addslashes(trim($influenced)) . "\" ;\n";
                        }
                    }

                    if (!empty($framework['programmed_in'])) {
                        foreach ($framework['programmed_in'] as $programmed) {
                            $triples .= ":frameworkProgrammedIn \"" . addslashes(trim($programmed)) . "\" ;\n";
                        }
                    }
                }
            }
        }

        // End the triples block
        $triples .= ".\n";

        Log::info('Generated Triples:', ['triples' => $triples]);
        $triples = trim($triples);

        return $triples;
    }

    public function insertTriples($triples)
    {
        // Check if the triples already exist
        if ($this->tripleExists($triples)) {
            return response()->json([
                'message' => 'The triples already exist in the dataset.',
            ], 200); // This is okay if already exists
        }

        // Construct the SPARQL INSERT query
        $sparqlUpdate = "
            PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
            PREFIX : <http://example.org#>
            
            INSERT DATA {
                $triples
            }
        ";

        // Execute the INSERT query with authentication
        $response = Http::withBasicAuth(
            config('services.jobhunter_update.username'),
            config('services.jobhunter_update.password')
        )
            ->asForm()->post(config('services.jobhunter_update.url'), [
                    'update' => $sparqlUpdate,
                ]);


        if ($response->failed()) {
            throw new Exception('Failed to insert triples into Fuseki: ' . $response->body());
        }

        // Check if the response body indicates success
        if (str_contains(strtolower($response->body()), 'update succeeded')) {
            return response()->json([
                'message' => 'Triples inserted successfully.',
            ], 201);  // Ensure a 201 is returned here on successful insert
        }

        // Handle unexpected responses
        return response()->json([
            'message' => 'Triples inserted, but response was unexpected.',
            'response' => $response->body(),
        ], 202);  // If the response was unexpected, you might want to return 202
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

        // Execute the INSERT query with authentication
        $response = Http::withBasicAuth(
            config('services.jobhunter_query.username'),
            config('services.jobhunter_query.password')
        )
            ->asForm()->post(config('services.jobhunter_query.url'), [
                    'query' => $askQuery,
                ]);

        if ($response->failed()) {
            throw new Exception('Failed to query Fuseki: ' . $response->body());
        }

        // Decode the JSON response from Fuseki
        $askResult = json_decode($response->body(), true);

        // Log::info('Fuseki ASK Query Response: ' . $response->body());
        if (!isset($askResult['boolean'])) {
            //    throw new Exception('Invalid response from Fuseki: ' . json_encode($askResult));
        }

        return $askResult['boolean'] ?? false;
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

        // Convert the associative array to a JSON string
        $json = json_encode($input);

        // Write the JSON string to a temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'json_');
        file_put_contents($tempFile, $json);

        // Build command to execute the Python script with the temporary file as an argument
        $command = "python $scriptPath " . escapeshellarg($tempFile);

        // Set execution timeout
        set_time_limit(500);

        // Execute the command
        $output = shell_exec($command);

        $json = json_decode($output);

        unlink($tempFile);

        if ($output === null) {
            throw new Exception('Error executing Python script.');
        }

        $result = $this->extractJson($output);
        $decodedOutput = json_decode($result, true);

        return $decodedOutput;
    }

    /**
     * Extract the JSON object from mixed output.
     */
    public function extractJson(string $output): string
    {
        // Use a regular expression to extract JSON
        preg_match('/\{.*\}/s', $output, $matches);

        if (!empty($matches)) {
            return $matches[0]; // Return the JSON part
        }

        throw new Exception('No JSON object found in the output');
    }

}
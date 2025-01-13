<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class TripleService
{
    public function validateJobData($data)
    {
        $validator = Validator::make($data, [
            'title' => 'required|string',
            'companyName' => 'required|string',
            'datePosted' => 'required|date_format:d/m/Y',
            'location' => 'string',
            'jobDescription' => 'string',
            'experience_years' => 'array',
            'experience_level' => 'string',
            'employment_type' => 'array',
            'language_skills' => 'array',
            'soft_skills' => 'array',
            'degree_level' => 'array',
            'education_field' => 'array',
            'job_location' => 'string',
            'job_location_type' => 'array',
            'programming_languages' => 'array',
            'unclassified_skills' => 'array',
            'libraries' => 'array',
            'frameworks' => 'array',
        ]);

        if ($validator->fails()) {
            throw new Exception('Validation failed: ' . $validator->errors()->first());
        }
    }

    function prepareIndividualTriples(array $data)
    {
        try {
            $baseUri = "http://www.semanticweb.org/ana/ontologies/2024/10/JobHunterOntology#";
            $triples = [];

            // Core Job Information
            if (isset($data['title'])) {
                $cleanTitle = str_replace(' ', '', $data['title']);
                $triples[] = "<{$baseUri}{$cleanTitle}> rdf:type <{$baseUri}Job> .";
                $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}jobTitle> \"" . addslashes($data['title']) . "\"^^xsd:string .";
                $triples[] = "<{$baseUri}{$cleanTitle}> rdfs:label \"" . addslashes($data['title']) . "\"^^xsd:string .";

            }

            if (isset($data['companyName'])) {
                $cleanCompanyName = str_replace(' ', '', $data['companyName']);
                $triples[] = "<{$baseUri}{$cleanCompanyName}> rdf:type <{$baseUri}Company> .";
                $triples[] = "<{$baseUri}{$cleanCompanyName}> <{$baseUri}companyName> \"" . addslashes($data['companyName']) . "\"^^xsd:string .";
                $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}postedByCompany> <{$baseUri}{$cleanCompanyName}> .";
                $triples[] = "<{$baseUri}{$cleanCompanyName}> rdfs:label \"" . addslashes($data['companyName']) . "\"^^xsd:string .";

            }

            // if (isset($data['datePosted'])) {
            //     $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}datePosted> \"" . addslashes($data['datePosted']) . "\"^^xsd:dateTime .";
            // }
            // dd($data['datePosted']);
            if (isset($data['datePosted'])) {
                try {
                    // Parse the date using Carbon
                    $formattedDate = Carbon::createFromFormat('F d, Y', $data['datePosted'])->format('d-m-Y');

                    // Use the formatted date in the triple
                    $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}datePosted> \"" . $formattedDate . "\"^^xsd:dateTime .";
                    //     $triples[] = "<{$baseUri}{$cleanTitle}> rdfs:label \"" . addslashes($data['datePosted']) . "\"^^xsd:dateTime .";

                } catch (Exception $e) {
                    // Handle invalid date format if necessary
                    throw new Exception("Invalid date format for datePosted: " . $data['datePosted']);
                }
            }

            if (isset($data['job_location'])) {
                $cleanJobLocation = str_replace(' ', '', $data['job_location']);

                $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}jobLocation> \"" . addslashes($data['job_location']) . "\"^^xsd:string .";
                $triples[] = "<{$baseUri}{$cleanJobLocation}> rdfs:label \"" . addslashes($data['job_location']) . "\"^^xsd:string .";
            }

            if (isset($data['experienceInYears'])) {
                $cleanExperienceInYears = str_replace(' ', '', $data['experienceInYears']);
                $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}experienceInYears> \"" . intval($data['experienceInYears']) . "\"^^xsd:int .";
                $triples[] = "<{$baseUri}{$cleanExperienceInYears}> rdfs:label \"" . addslashes($data['experienceInYears']) . "\"^^xsd:string .";

            }

            // Soft Skills
            if (!empty($data['soft_skills'])) {
                foreach ($data['soft_skills'] as $skill) {
                    $cleanSkill = str_replace(' ', '', $skill);
                    $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}requiresSkill> <{$baseUri}{$cleanSkill}> .";
                    $triples[] = "<{$baseUri}{$cleanSkill}> rdf:type <{$baseUri}SoftSkill> .";
                    $triples[] = "<{$baseUri}{$cleanSkill}> rdfs:label \"" . addslashes($skill) . "\"^^xsd:string .";
                }
            }

            dd($data["programming_languages"]);
            // Programming Languages
            if (!empty($data['programming_languages'])) {
                foreach ($data['programming_languages'] as $language) {
                    $langName = str_replace(' ', '', $language['skill_name'] ?? 'Unknown');
                    $triples[] = "<{$baseUri}{$langName}> rdf:type <{$baseUri}ProgrammingLanguage> .";
                    $triples[] = "<{$baseUri}{$langName}> rdf:type <{$baseUri}TechnicalSkill> .";
                    $triples[] = "<{$baseUri}{$langName}> rdf:type <{$baseUri}Skill> .";
                    $triples[] = "<{$baseUri}{$langName}> rdfs:label \"" . addslashes($language['skill_name']) . "\"^^xsd:string .";
                    if (!empty($language['official_website'])) {
                        $triples[] = "<{$baseUri}{$langName}> <{$baseUri}officialWebsite> \"" . addslashes($language['official_website']) . "\"^^xsd:anyURI .";
                    }
                    if (!empty($library['influenced_by'])) {
                        foreach ($language['influenced_by'] as $influenced) {
                            $cleanInfluenced = str_replace(' ', '', $influenced);
                            $triples[] = "<{$baseUri}{$langName}> <{$baseUri}influencedBy> <{$baseUri}{$cleanInfluenced}> .";
                        }
                    }
                    if (!empty($library['programmed_in'])) {
                        foreach ($language['programmed_in'] as $programmed) {
                            $cleanProgrammed = str_replace(' ', '', $programmed);
                            $triples[] = "<{$baseUri}{$langName}> <{$baseUri}programmedIn> <{$baseUri}{$cleanProgrammed}> .";
                        }
                    }
                }
            }

            // Education Fields
            if (!empty($data['education_field'])) {
                foreach ($data['education_field'] as $field) {
                    $cleanField = str_replace(' ', '', $field);
                    $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}requiresEducation> <{$baseUri}{$cleanField}> .";
                    $triples[] = "<{$baseUri}{$cleanField}> rdf:type <{$baseUri}Education> .";
                    $triples[] = "<{$baseUri}{$cleanField}> rdfs:label \"" . addslashes($field) . "\"^^xsd:string .";
                }
            }

            // Unclassified Skills
            if (!empty($data['unclassified_skills'])) {
                foreach ($data['unclassified_skills'] as $skill) {
                    $cleanSkill = str_replace(' ', '', $skill);
                    $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}hasSkill> <{$baseUri}{$cleanSkill}> .";
                    $triples[] = "<{$baseUri}{$cleanSkill}> rdf:type <{$baseUri}Skill> .";
                    $triples[] = "<{$baseUri}{$cleanSkill}> rdfs:label \"" . addslashes($skill) . "\"^^xsd:string .";
                }
            }

            // Libraries
            if (!empty($data['libraries'])) {
                foreach ($data['libraries'] as $library) {
                    $libName = str_replace(' ', '', $library['skill_name'] ?? 'UnknownLibrary');
                    $triples[] = "<{$baseUri}{$libName}> rdf:type <{$baseUri}Library> .";
                    $triples[] = "<{$baseUri}{$libName}> rdf:type <{$baseUri}TechnicalSkill> ."; // Optional
                    $triples[] = "<{$baseUri}{$libName}> rdf:type <{$baseUri}Skill> .";
                    $triples[] = "<{$baseUri}{$libName}> rdfs:label \"" . addslashes($library['skill_name']) . "\"^^xsd:string .";
                    if (!empty($library['influenced_by'])) {
                        foreach ($library['influenced_by'] as $influenced) {
                            $cleanInfluenced = str_replace(' ', '', $influenced);
                            $triples[] = "<{$baseUri}{$libName}> <{$baseUri}influencedBy> <{$baseUri}{$cleanInfluenced}> .";
                        }
                    }
                    if (!empty($library['programmed_in'])) {
                        foreach ($library['programmed_in'] as $programmed) {
                            $cleanProgrammed = str_replace(' ', '', $programmed);
                            $triples[] = "<{$baseUri}{$libName}> <{$baseUri}programmedIn> <{$baseUri}{$cleanProgrammed}> .";
                        }
                    }
                }
            }

            // Frameworks
            if (!empty($data['frameworks'])) {
                foreach ($data['frameworks'] as $framework) {
                    $fwName = str_replace(' ', '', $framework['skill_name'] ?? 'UnknownFramework');
                    $triples[] = "<{$baseUri}{$fwName}> rdf:type <{$baseUri}Framework> .";
                    $triples[] = "<{$baseUri}{$fwName}> rdf:type <{$baseUri}TechnicalSkill> ."; // Optional
                    $triples[] = "<{$baseUri}{$fwName}> rdf:type <{$baseUri}Skill> .";
                    $triples[] = "<{$baseUri}{$fwName}> rdfs:label \"" . addslashes($framework['skill_name']) . "\"^^xsd:string .";
                    if (!empty($framework['influenced_by'])) {
                        foreach ($framework['influenced_by'] as $influenced) {
                            $cleanInfluenced = str_replace(' ', '', $influenced);
                            $triples[] = "<{$baseUri}{$fwName}> <{$baseUri}influencedBy> <{$baseUri}{$cleanInfluenced}> .";
                        }
                    }
                    if (!empty($framework['programmed_in'])) {
                        foreach ($framework['programmed_in'] as $programmed) {
                            $cleanProgrammed = str_replace(' ', '', $programmed);
                            $triples[] = "<{$baseUri}{$fwName}> <{$baseUri}programmedIn> <{$baseUri}{$cleanProgrammed}> .";
                        }
                    }
                }
            }

        } catch (Exception $e) {
            return [
                'output' => [],
                'error' => $e->getMessage(),
                'status' => 500
            ];
        }
        //dd($triples);
        return [
            'output' => $triples,
            'error' => null,
            'status' => 200,
        ];
    }

    public function insertTriples($triple)
    {
        // Check if the triples already exist
        if ($this->tripleExists($triple)) {
            return [
                'message' => 'The triples already exist in the dataset.',
                'status' => 200 // This is okay if already exists
            ];
        }

        // Construct the SPARQL INSERT query
        $sparqlUpdate = "

            PREFIX :                  <http://www.semanticweb.org/ana/ontologies/2024/10/JobHunterOntology/>
            PREFIX JobHunterOntology: <http://www.semanticweb.org/ana/ontologies/2024/10/JobHunterOntology#>
            PREFIX owl:               <http://www.w3.org/2002/07/owl#>
            PREFIX rdf:               <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
            PREFIX rdfs:              <http://www.w3.org/2000/01/rdf-schema#>
            PREFIX xml:               <http://www.w3.org/XML/1998/namespace>
            PREFIX xsd:               <http://www.w3.org/2001/XMLSchema#>

            INSERT DATA {
                $triple
            }
        ";

        // // Execute the INSERT query with authentication
        // $response = Http::withBasicAuth(
        //     config('services.jobhunter_update.username'),
        //     config('services.jobhunter_update.password')
        // )
        //     ->asForm()->post(config('services.jobhunter_update.url'), [
        //             'update' => $sparqlUpdate,
        //         ]);


        // if ($response->failed()) {
        //     throw new Exception('Failed to insert triples into Fuseki: ' . $response->body());
        // }

        // // Check if the response body indicates success
        // if (str_contains(strtolower($response->body()), 'update succeeded')) {
        //     return [
        //         'message' => 'Triples inserted successfully.',
        //         //'body' => $response->body(),
        //         'status' => 201,  // Return a 201 status code for successful insert
        //     ];
        // }

        $response = SparqlService::executeUpdate($sparqlUpdate);
        //dd($response);
        // Handle unexpected responses
        return [
            'message' => 'Triples inserted, but response was unexpected.',
            'response' => $response,
            'status' => 202  // If the response was unexpected, you might want to return 202
        ];
    }

    /**
     * Check if a triple exists in Fuseki
     */
    private function tripleExists($triples)
    {
        // Prepare the ASK query based on the provided triples
        $askQuery = "
            PREFIX :                  <http://www.semanticweb.org/ana/ontologies/2024/10/JobHunterOntology/>
            PREFIX JobHunterOntology: <http://www.semanticweb.org/ana/ontologies/2024/10/JobHunterOntology#>
            PREFIX owl:               <http://www.w3.org/2002/07/owl#>
            PREFIX rdf:               <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
            PREFIX rdfs:              <http://www.w3.org/2000/01/rdf-schema#>
            PREFIX xml:               <http://www.w3.org/XML/1998/namespace>
            PREFIX xsd:               <http://www.w3.org/2001/XMLSchema#>
            
            ASK {
                $triples
            }
        ";

        // Log the ASK query for debugging
        Log::info('SPARQL ASK Query:', ['query' => $askQuery]);

        // Execute the ASK query with authentication
        // $response = Http::withBasicAuth(
        //     config('services.jobhunter_query.username'),
        //     config('services.jobhunter_query.password')
        // )
        //     ->withHeaders([
        //         'Accept' => 'application/sparql-results+json', // Ensure JSON response
        //         'Content-Type' => 'application/x-www-form-urlencoded'
        //     ])
        //     ->asForm()->post(config('services.jobhunter_query.url'), [
        //             'query' => $askQuery,
        //         ]);

        // Log the raw response for debugging
        // Log::info('Raw ASK query response:', ['response' => $response->body()]);
        // Parse the response
        // $result = $response->json();

        // if ($response->failed()) {
        //     throw new Exception('Failed to query Fuseki: ' . $response->body());
        // }

        //Log::info("ask query response: " . json_encode($response));

        // Decode the JSON response from Fuseki
        // $askResult = json_decode($response->body(), true);

        // if (isset($result['boolean'])) {
        //     Log::info('ASK query result:', ['exists' => $result['boolean']]);
        // } else {
        //     Log::error('ASK query did not return a valid boolean result', ['response' => $result]);
        // }

        //return $askResult['boolean'] ?? true;
        $result = SparqlService::query($askQuery);
        //  dd($result, $askQuery);
        //  dd($result);
        return $result['boolean'];
    }

    /**
     * Summary of executeScript
     * @param mixed $input
     * @throws \Exception
     * @return mixed
     */
    public function executeScript($input)
    {
        $error = "";
        try {
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

            try {
                // Execute the command
                $output = shell_exec($command);
                // dd($output);
            } catch (Exception $e) {
                return [
                    'output' => null,
                    'error' => "Script output:" . $output . ". Error: " . $e->getMessage(),
                    'status' => 500
                ];
            }
            Log::info("Script: " . json_encode($output));
            $json = json_decode($output);

            unlink($tempFile);

            if ($output === null) {
                return [
                    'output' => null,
                    'error' => "Error executing python script",
                    'status' => 500
                ];
            }

            $result = $this->extractJson($output);
            if ($result["success"]) {
                $decodedOutput = json_decode($result["output"], true);
            } else {
                return [
                    'output' => null,
                    'error' => $result["output"],
                    'status' => 500
                ];
            }
        } catch (Exception $e) {
            return [
                'output' => null,
                'error' => $e->getMessage(),
                'status' => 500
            ];
        }
        return [
            'output' => $decodedOutput,
            'error' => null,
            'status' => 200,
        ];
    }

    /**
     * Extract the JSON object from mixed output.
     */
    public function extractJson(string $output): array
    {
        // Use a regular expression to extract JSON
        preg_match('/\{.*\}/s', $output, $matches);

        if (!empty($matches)) {
            return [
                "success" => 1,
                "output" => $matches[0] // Return the JSON part
            ];
        }
        return [
            "success" => 0,
            "output" => ' No JSON object found in the output. Script output:/' . $output
        ];

    }

}
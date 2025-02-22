<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Services\Ontology\OntologyGenerator;

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
            if (isset($data['datePosted'])) {
                $formattedDate = self::convertDateFormatString($data['datePosted']);
                $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}datePosted> \"{$formattedDate}\"^^xsd:date .";
            }
            // dd($data);
            // dateRemoved
            if (isset($data['date_removed'])) {
                if ($data['date_removed'] != "") {
                    $formattedDate = self::convertDateFormatString($data['date_removed']);
                    $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}dateRemoved> \"{$formattedDate}\"^^xsd:date .";
                }
            }
            // Log::info("datePosted: " . $data['datePosted']);
            // if (isset($data['datePosted'])) {
            //     try {
            //         // Format date using strtotime
            //         $formattedDate = date('Y-m-d', strtotime($data['datePosted']));
            //         Log::info("datePosted strtotime: " . $formattedDate);
            //         // Use the formatted date in the triple
            //         $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}datePosted> \"" . $formattedDate . "\"^^xsd:date .";
            //         //     $triples[] = "<{$baseUri}{$cleanTitle}> rdfs:label \"" . addslashes($data['datePosted']) . "\"^^xsd:date .";

            //     } catch (Exception $e) {
            //         // Handle invalid date format if necessary
            //         throw new Exception("Invalid date format for datePosted: " . $data['datePosted']);
            //     }
            // }

            if (isset($data['job_location'])) {
                $cleanJobLocation = str_replace(' ', '', $data['job_location']);
                $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}jobLocation> \"" . addslashes($data['job_location']) . "\"^^xsd:string .";
                $triples[] = "<{$baseUri}{$cleanJobLocation}> rdfs:label \"" . addslashes($data['job_location']) . "\"^^xsd:string .";
            }


            // jobLocatedIn
            if (isset($data['job_city'])) {
                $cleanJobCity = str_replace(' ', '', $data['job_city']);
                $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}jobLocatedIn> <{$baseUri}{$cleanJobCity}> .";
                $triples[] = "<{$baseUri}{$cleanJobCity}> rdfs:label \"" . addslashes($data['job_city']) . "\"^^xsd:string .";
            }

            if (isset($data['experienceInYears'])) {
                $cleanExperienceInYears = str_replace(' ', '', $data['experienceInYears']);
                $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}experienceInYears> \"" . intval($data['experienceInYears']) . "\"^^xsd:int .";
                $triples[] = "<{$baseUri}{$cleanExperienceInYears}> rdfs:label \"" . addslashes($data['experienceInYears']) . "\"^^xsd:string .";

            }

            // experienceLevel
            if (isset($data['experienceLevel'])) {
                if ($data['experienceLevel'] != "") {
                    $cleanExperienceLevel = str_replace(' ', '', $data['experienceLevel']);
                    $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}experienceLevel> <{$baseUri}{$cleanExperienceLevel}> .";
                    $triples[] = "<{$baseUri}{$cleanExperienceLevel}> rdfs:label \"" . addslashes($data['experienceLevel']) . "\"^^xsd:string .";
                }
            }
            // Employment Type
            // Soft Skills
            if (!empty($data['soft_skills'])) {
                foreach ($data['soft_skills'] as $skill) {
                    $cleanSkill = str_replace(' ', '', $skill);
                    $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}requiresSkill> <{$baseUri}{$cleanSkill}> .";
                    $triples[] = "<{$baseUri}{$cleanSkill}> rdf:type <{$baseUri}SoftSkill> .";
                    $triples[] = "<{$baseUri}{$cleanSkill}> rdf:type <{$baseUri}Skill> .";
                    $triples[] = "<{$baseUri}{$cleanSkill}> rdfs:label \"" . addslashes($skill) . "\"^^xsd:string .";
                }
            }

            // Programming Languages
            if (!empty($data['programming_languages'])) {
                foreach ($data['programming_languages'] as $language) {
                    $langName = str_replace(' ', '', $language['skill_name'] ?? 'Unknown');
                    $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}requiresSkill> <{$baseUri}{$langName}> .";
                    $triples[] = "<{$baseUri}{$langName}> rdf:type <{$baseUri}ProgrammingLanguage> .";
                    $triples[] = "<{$baseUri}{$langName}> rdf:type <{$baseUri}TechnicalSkill> .";
                    $triples[] = "<{$baseUri}{$langName}> rdf:type <{$baseUri}Skill> .";
                    $triples[] = "<{$baseUri}{$langName}> rdfs:label \"" . addslashes($language['skill_name']) . "\"^^xsd:string .";
                    if (!empty($language['official_website'])) {
                        $triples[] = "<{$baseUri}{$langName}> <{$baseUri}officialWebsite> \"" . addslashes($language['official_website']) . "\"^^xsd:anyURI .";
                    }
                    if (!empty($language['wikidata_uri'])) {
                        $triples[] = "<{$baseUri}{$langName}> <{$baseUri}wikidataURI> \"" . addslashes($language['wikidata_uri']) . "\"^^xsd:anyURI .";
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
                    $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}requiresSkill> <{$baseUri}{$cleanSkill}> .";
                    //  $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}hasSkill> <{$baseUri}{$cleanSkill}> .";
                    $triples[] = "<{$baseUri}{$cleanSkill}> rdf:type <{$baseUri}Skill> .";
                    $triples[] = "<{$baseUri}{$cleanSkill}> rdfs:label \"" . addslashes($skill) . "\"^^xsd:string .";
                }
            }

            // Libraries
            if (!empty($data['libraries'])) {
                foreach ($data['libraries'] as $library) {
                    $libName = str_replace(' ', '', $library['skill_name'] ?? 'UnknownLibrary');
                    $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}requiresSkill> <{$baseUri}{$libName}> .";
                    $triples[] = "<{$baseUri}{$libName}> rdf:type <{$baseUri}Library> .";
                    $triples[] = "<{$baseUri}{$libName}> rdf:type <{$baseUri}TechnicalSkill> ."; // Optional
                    $triples[] = "<{$baseUri}{$libName}> rdf:type <{$baseUri}Skill> .";
                    $triples[] = "<{$baseUri}{$libName}> rdfs:label \"" . addslashes($library['skill_name']) . "\"^^xsd:string .";
                    if (!empty($library['wikidata_uri'])) {
                        //    $triples[] = "<{$baseUri}{$langName}> <{$baseUri}wikidataURI> \"" . addslashes($library['wikidata_uri']) . "\"^^xsd:anyURI .";
                        $triples[] = "<{$baseUri}{$libName}> <{$baseUri}wikidataURI> \"" . addslashes($library['wikidata_uri']) . "\"^^xsd:anyURI .";
                    }
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
                    $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}requiresSkill> <{$baseUri}{$fwName}> .";
                    $triples[] = "<{$baseUri}{$fwName}> rdf:type <{$baseUri}Framework> .";
                    $triples[] = "<{$baseUri}{$fwName}> rdf:type <{$baseUri}TechnicalSkill> ."; // Optional
                    $triples[] = "<{$baseUri}{$fwName}> rdf:type <{$baseUri}Skill> .";
                    $triples[] = "<{$baseUri}{$fwName}> rdfs:label \"" . addslashes($framework['skill_name']) . "\"^^xsd:string .";
                    if (!empty($framework['wikidata_uri'])) {
                        $triples[] = "<{$baseUri}{$fwName}> <{$baseUri}wikidataURI> \"" . addslashes($framework['wikidata_uri']) . "\"^^xsd:anyURI .";
                    }
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

            // is_available
            // if (!empty($data['is_available'])) {
            //     $cleanIsAvailable = str_replace(' ', '', $data['is_available']);
            //     $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}isAvailable> \"" . addslashes($data['is_available']) . "\"^^xsd:boolean .";
            //     $triples[] = "<{$baseUri}{$cleanIsAvailable}> rdfs:label \"" . addslashes($data['is_available']) . "\"^^xsd:boolean .";
            // }

            // isReal
            if (!empty($data['isReal'])) {
                $cleanIsReal = str_replace(' ', '', $data['isReal']);
                $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}isReal> \"" . addslashes($data['isReal']) . "\"^^xsd:boolean .";
                $triples[] = "<{$baseUri}{$cleanIsReal}> rdfs:label \"" . addslashes($data['isReal']) . "\"^^xsd:boolean .";
            }
            //dd($triples);

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

        try {
            $response = SparqlService::executeUpdate($sparqlUpdate);

            // If response indicates success
            if ($response === true || $response === "success") {
                return [
                    'message' => 'Triples inserted successfully.',
                    'status' => 201 // Created
                ];
            }

            // Handle unexpected responses
            return [
                'message' => 'Triples inserted, but response was unexpected.',
                'response' => $response,
                'status' => 202 // Accepted but unexpected
            ];
        } catch (Exception $e) {
            // Catch and return the error
            return [
                'message' => 'Failed to insert triples.',
                'error' => $e->getMessage(),
                'status' => 500 // Internal server error
            ];
        }
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

        //return $askResult['boolean'] ?? true;
        $result = SparqlService::query($askQuery);

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
            $scriptPath = env('PYTHON_SCRIPT_PATH');
            // $scriptPath = base_path(env('PYTHON_SCRIPT_PATH'));
            //  dd($scriptPath);
            // Convert the associative array to a JSON string
            $json = json_encode($input);

            // Write the JSON string to a temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'json_');
            file_put_contents($tempFile, $json);

            // Build command to execute the Python script with the temporary file as an argument
            $command = "python $scriptPath " . escapeshellarg($tempFile);
            //dd($command);
            // Set execution timeout
            set_time_limit(500);

            try {
                // Execute the command
                $output = shell_exec($command);
                //dd($output);
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

    public function prepareEventTriples($data)
    {
        //  dd($data);
        try {
            $baseUri = "http://www.semanticweb.org/ana/ontologies/2024/10/JobHunterOntology#";
            $triples = [];
            // "eventTitle": "Chaos Engineering"
            // Core Job Information
            if (isset($data['eventTitle'])) {
                if ($data['eventTitle'] != "None" || $data['eventTitle'] != "none") {
                    $cleanTitle = str_replace(' ', '', $data['eventTitle']);
                    $triples[] = "<{$baseUri}{$cleanTitle}> rdf:type <{$baseUri}Event> .";
                    $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}eventTitle> \"" . addslashes($data['eventTitle']) . "\"^^xsd:string .";
                    $triples[] = "<{$baseUri}{$cleanTitle}> rdfs:label \"" . addslashes($data['eventTitle']) . "\"^^xsd:string .";
                }
            }
            //dd($triples);
            // "eventDate": "12-2-2025",
            if (isset($data['eventDate'])) {
                if ($data['eventDate'] != "None" || $data['eventDate'] != "none") {

                    try {
                        // // Parse the date using Carbon
                        $formattedDate = self::convertDateFormat($data['eventDate']);
                        // Use the formatted date in the triple
                        $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}eventDate> \"" . $formattedDate . "\"^^xsd:dateTime .";
                        // $triples[] = "<{$baseUri}{$cleanTitle}> rdfs:label \"" . addslashes($data['eventDate']) . "\"^^xsd:dateTime .";
                    } catch (Exception $e) {
                        // Handle invalid date format if necessary
                        throw new Exception("Invalid date format for eventDate: " . $data['eventDate']);
                    }
                }
            }
            // "eventType": "conference",
            if (isset($data['eventType'])) {
                if ($data['eventType'] != "None" || $data['eventType'] != "none") {

                    $cleanEventType = str_replace(' ', '', $data['eventType']);
                    $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}eventType> \"" . addslashes($data['eventType']) . "\"^^xsd:string .";
                    $triples[] = "<{$baseUri}{$cleanEventType}> rdfs:label \"" . addslashes($data['eventType']) . "\"^^xsd:string .";
                }
            }
            // // "isOnline": "True",
            // if (isset($data['isOnline'])) {
            //     //$cleanIsOnline = boolean($data['isOnline']);
            //     $cleanIsOnline = $data['isOnline'];
            //     $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}isOnline> \"" . addslashes($data['isOnline']) . "\"^^xsd:boolean .";
            //     $triples[] = "<{$baseUri}{$cleanIsOnline}> rdfs:label \"" . addslashes($data['isOnline']) . "\"^^xsd:boolean .";
            // }
            if (isset($data['isOnline'])) {
                if ($data['isOnline'] != "None" || $data['isOnline'] != "none") {

                    // Normalize boolean value (ensure lowercase and valid RDF boolean format)
                    $cleanIsOnline = filter_var($data['isOnline'], FILTER_VALIDATE_BOOLEAN) ? "true" : "false";

                    // Construct the triple correctly
                    $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}isOnline>  \"" . trim($cleanIsOnline) . "\"^^xsd:boolean .";
                }
            }

            // "topic": "DevOps"
            if (isset($data['topic'])) {
                if ($data['topic'] != "None" || $data['topic'] != "none") {
                    $cleanTopic = str_replace(' ', '', $data['topic']);
                    // type topic
                    $triples[] = "<{$baseUri}{$cleanTopic}> rdf:type <{$baseUri}Topic> .";
                    $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}topic> \"" . addslashes($data['topic']) . "\"^^xsd:string .";
                    $triples[] = "<{$baseUri}{$cleanTopic}> rdfs:label \"" . addslashes($data['topic']) . "\"^^xsd:string .";
                    // hasTopic
                    $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}hasTopic> <{$baseUri}{$cleanTopic}> .";
                }
            }

            if (isset($data['topicCategory'])) {
                //  dd($data['topicCategory']);
                if ($data['topicCategory'] != 'Topic') {
                    if (!empty($data['topicCategoryDetails'])) {
                        if ($data['topicCategory'] == 'Programming Language') {
                            // Programming Languages
                            $language = $data['topicCategoryDetails'];

                            $langName = str_replace(' ', '', $language['skill_name'] ?? 'Unknown');
                            // $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}requiresSkill> <{$baseUri}{$langName}> .";
                            $triples[] = "<{$baseUri}{$langName}> rdf:type <{$baseUri}ProgrammingLanguage> .";
                            $triples[] = "<{$baseUri}{$langName}> rdf:type <{$baseUri}TechnicalSkill> .";
                            $triples[] = "<{$baseUri}{$langName}> rdf:type <{$baseUri}Skill> .";
                            $triples[] = "<{$baseUri}{$langName}> rdfs:label \"" . addslashes($language['skill_name']) . "\"^^xsd:string .";
                            if (!empty($language['official_website'])) {
                                $triples[] = "<{$baseUri}{$langName}> <{$baseUri}officialWebsite> \"" . addslashes($language['official_website']) . "\"^^xsd:anyURI .";
                            }
                            if (!empty($language['wikidata_uri'])) {
                                $triples[] = "<{$baseUri}{$langName}> <{$baseUri}wikidataURI> \"" . addslashes($language['wikidata_uri']) . "\"^^xsd:anyURI .";
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

                        } else if ($data['topicCategory'] == "Library") {
                            // Library
                            $library = $data['topicCategoryDetails'];
                            $libName = str_replace(' ', '', $library['skill_name'] ?? 'UnknownLibrary');
                            //     $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}requiresSkill> <{$baseUri}{$libName}> .";
                            $triples[] = "<{$baseUri}{$libName}> rdf:type <{$baseUri}Library> .";
                            $triples[] = "<{$baseUri}{$libName}> rdf:type <{$baseUri}TechnicalSkill> ."; // Optional
                            $triples[] = "<{$baseUri}{$libName}> rdf:type <{$baseUri}Skill> .";
                            $triples[] = "<{$baseUri}{$libName}> rdfs:label \"" . addslashes($library['skill_name']) . "\"^^xsd:string .";
                            if (!empty($library['wikidata_uri'])) {
                                $triples[] = "<{$baseUri}{$libName}> <{$baseUri}wikidataURI> \"" . addslashes($library['wikidata_uri']) . "\"^^xsd:anyURI .";
                            }
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

                            // dd($triples);
                        } else if ($data['topicCategory'] == 'Framework') {
                            // Fremework
                            $framework = $data['topicCategoryDetails'];
                            $fwName = str_replace(' ', '', $framework['skill_name'] ?? 'UnknownFramework');
                            //    $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}requiresSkill> <{$baseUri}{$fwName}> .";
                            $triples[] = "<{$baseUri}{$fwName}> rdf:type <{$baseUri}Framework> .";
                            $triples[] = "<{$baseUri}{$fwName}> rdf:type <{$baseUri}TechnicalSkill> ."; // Optional
                            $triples[] = "<{$baseUri}{$fwName}> rdf:type <{$baseUri}Skill> .";
                            $triples[] = "<{$baseUri}{$fwName}> rdfs:label \"" . addslashes($framework['skill_name']) . "\"^^xsd:string .";
                            if (!empty($framework['wikidata_uri'])) {
                                $triples[] = "<{$baseUri}{$fwName}> <{$baseUri}wikidataURI> \"" . addslashes($framework['wikidata_uri']) . "\"^^xsd:anyURI .";
                            }
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
                }
            }
            if (isset($data['isOnline'])) {
                //dd($data);
                // daca eventul este onsite
                if ($data['isOnline'] == "false" || $data['isOnline'] == "False" || $data['isOnline'] == false) {

                    // "city" : "Iasi"
                    if (isset($data['city'])) {
                        if ($data['city'] != "None" || $data['city'] != "none") {
                            $cleanCity = str_replace(' ', '', $data['city']);
                            // type city
                            $triples[] = "<{$baseUri}{$cleanCity}> rdf:type <{$baseUri}City> .";
                            $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}city> \"" . addslashes($data['city']) . "\"^^xsd:string .";
                            $triples[] = "<{$baseUri}{$cleanCity}> rdfs:label \"" . addslashes($data['city']) . "\"^^xsd:string .";
                            $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}takesPlaceIn> <{$baseUri}{$cleanCity}> .";
                            //isLocatedIn
                            if (isset($data['country'])) {
                                $cleanCountry = str_replace(' ', '', $data['country']);
                                $triples[] = "<{$baseUri}{$cleanCity}> <{$baseUri}isLocatedIn> <{$baseUri}{$cleanCountry}> .";
                            }
                        }
                    }
                    // "country": "Romania"
                    if (isset($data['country'])) {
                        if ($data['country'] != "None" || $data['country'] != "none") {
                            $cleanCountry = str_replace(' ', '', $data['country']);
                            //type country
                            $triples[] = "<{$baseUri}{$cleanCountry}> rdf:type <{$baseUri}Country> .";
                            $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}country> \"" . addslashes($data['country']) . "\"^^xsd:string .";
                            $triples[] = "<{$baseUri}{$cleanCountry}> rdfs:label \"" . addslashes($data['country']) . "\"^^xsd:string .";
                        }
                    }
                }
            }

            if (isset($data['eventURL'])) {
                $triples[] = "<{$baseUri}{$cleanTitle}> <{$baseUri}eventURL> \"" . addslashes($data['eventURL']) . "\"^^xsd:anyURI .";
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

    /**
     * Convert date format from DD-MM-YYYY to YYYY-MM-DD
     */
    private function convertDateFormat($date)
    {
        try {
            return Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
        } catch (Exception $e) {
            Log::error("Invalid date format: " . $date);
            return $date; // Return original if conversion fails
        }
    }
    public static function convertDateFormatString($dateString)
    {
        try {
            // Convertim în format ISO 8601 (YYYY-MM-DD)
            return Carbon::parse($dateString)->format('Y-m-d');
        } catch (Exception $e) {
            throw new Exception("Invalid date format: " . $dateString);
        }
    }
}
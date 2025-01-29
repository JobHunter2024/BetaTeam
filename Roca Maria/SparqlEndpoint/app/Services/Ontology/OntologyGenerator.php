<?php

namespace App\Services\Ontology;

class OntologyGenerator
{
    private string $baseUri;
    private array $triples = [];

    public function __construct(string $baseUri)
    {
        $this->baseUri = rtrim($baseUri, '#') . '#'; // Ensure base URI ends with "#"
    }

    public function generate(): array
    {
        $this->addOntology();
        $this->addClasses();
        $this->addSubclasses();
        $this->addObjectProperties();
        $this->addInverseProperties();
        $this->addDataProperties();
        $this->addNamedIndividuals();
        $this->addGlobalPropertyLabels();

        return $this->triples;
    }

    private function addOntology(): void
    {
        $this->triples[] = "<{$this->baseUri}> rdf:type owl:Ontology .";
    }

    private function addClasses(): void
    {
        $classes = [
            'Company' => 'Company',
            'Education' => 'Education',
            'Framework' => 'Framework',
            'Job' => 'Job',
            'LanguageSkill' => 'Language Skill',
            'Library' => 'Library',
            'ProgrammingLanguage' => 'Programming Language',
            'Skill' => 'Skill',
            'SoftSkill' => 'Soft Skill',
            'TechnicalSkill' => 'Technical Skill',
            'Event' => 'Event',
            'City' => 'City',
            'Country' => 'Country',
            'Topic' => 'Topic',
        ];

        foreach ($classes as $class => $label) {
            $this->triples[] = "<{$this->baseUri}{$class}> rdf:type owl:Class .";
            $this->triples[] = "<{$this->baseUri}{$class}> rdfs:label \"" . addslashes($label) . "\"^^xsd:string .";
        }
    }

    private function addSubclasses(): void
    {
        $subclasses = [
            'LanguageSkill' => 'Skill',
            'SoftSkill' => 'Skill',
            'TechnicalSkill' => 'Skill',
            'Framework' => 'TechnicalSkill',
            'Library' => 'TechnicalSkill',
            'ProgrammingLanguage' => 'TechnicalSkill',
        ];

        foreach ($subclasses as $subclass => $superclass) {
            $this->triples[] = "<{$this->baseUri}{$subclass}> rdfs:subClassOf <{$this->baseUri}{$superclass}> .";
        }
    }

    private function addObjectProperties(): void
    {
        $objectProperties = [
            'hasFramework' => [
                'domain' => 'ProgrammingLanguage',
                'range' => 'Framework',
                'label' => 'Has Framework'
            ],
            'hasLibrary' => [
                'domain' => 'ProgrammingLanguage',
                'range' => 'Library',
                'label' => 'Has Library'
            ],
            'influencedBy' => [
                'domain' => 'TechnicalSkill',
                'range' => 'TechnicalSkill',
                'label' => 'Influenced By'
            ],
            'isFrameworkOf' => [
                'domain' => 'Framework',
                'range' => 'ProgrammingLanguage',
                'label' => 'Is Framework Of'
            ],
            'isLibraryOf' => [
                'domain' => 'Library',
                'range' => 'ProgrammingLanguage',
                'label' => 'Is Library Of'
            ],
            'postedByCompany' => [
                'domain' => 'Job',
                'range' => 'Company',
                'label' => 'Posted By Company'
            ],
            'postedJob' => [
                'domain' => 'Company',
                'range' => 'Job',
                'label' => 'Posted Job'
            ],
            'programmedIn' => [
                'domain' => ['Framework', 'Library'],
                'range' => 'ProgrammingLanguage',
                'label' => 'Programmed In'
            ],
            'requiresEducation' => [
                'domain' => 'Job',
                'range' => 'Education',
                'label' => 'Requires Education'
            ],
            'requiresSkill' => [
                'domain' => 'Job',
                'range' => 'Skill',
                'label' => 'Requires Skill'
            ],
            'hasTopic' => [
                'domain' => 'Event',
                'range' => ['Topic', 'TechnicalSkill'],
                'label' => 'Has Topic'
            ],
            'isLocatedIn' => [
                'domain' => 'City', //['City', 'Country'],
                'range' => 'Country',
                'label' => 'is Located In'
            ],
            'takesPlace' => [
                'domain' => 'Event',
                'range' => 'City',
                'label' => 'Takes Place'
            ]
        ];

        foreach ($objectProperties as $property => $details) {
            $this->triples[] = "<{$this->baseUri}{$property}> rdf:type owl:ObjectProperty .";
            $this->addPropertyDomains($property, $details['domain']);
            $this->addPropertyRanges($property, $details['range']);
            //$this->triples[] = "<{$this->baseUri}{$property}> rdfs:range <{$this->baseUri}{$details['range']}> .";
            $this->triples[] = "<{$this->baseUri}{$property}> rdfs:label \"" . addslashes($details['label']) . "\"^^xsd:string .";
        }
    }

    private function addPropertyDomains(string $property, $domains): void
    {
        if (!is_array($domains)) {
            $domains = [$domains];
        }

        foreach ($domains as $domain) {
            $this->triples[] = "<{$this->baseUri}{$property}> rdfs:domain <{$this->baseUri}{$domain}> .";
        }
    }

    private function addPropertyRanges(string $property, $ranges): void
    {
        if (!is_array($ranges)) {
            $ranges = [$ranges];
        }

        foreach ($ranges as $range) {
            $this->triples[] = "<{$this->baseUri}{$property}> rdfs:range <{$this->baseUri}{$range}> .";
        }
    }

    private function addInverseProperties(): void
    {
        $inverseProperties = [
            'hasFramework' => 'isFrameworkOf',
            'hasLibrary' => 'isLibraryOf'
        ];

        foreach ($inverseProperties as $prop => $inverse) {
            $this->triples[] = "<{$this->baseUri}{$prop}> owl:inverseOf <{$this->baseUri}{$inverse}> .";
        }
    }

    private function addDataProperties(): void
    {
        $dataProperties = [
            'companyName' => [
                'domain' => 'Company',
                'label' => 'Company Name'
            ],
            'datePosted' => [
                'domain' => 'Job',
                'label' => 'Date Posted'
            ],
            'educationDegreeLevel' => [
                'domain' => 'Education',
                'label' => 'Education Degree Level'
            ],
            'educationField' => [
                'domain' => 'Education',
                'label' => 'Education Field'
            ],
            'employmentType' => [
                'domain' => 'Job',
                'label' => 'Employment Type'
            ],
            'experienceInYears' => [
                'domain' => 'Job',
                'label' => 'Experience in Years'
            ],
            'experinceLevel' => [
                'domain' => 'Job',
                'label' => 'Experience Level'
            ],
            'jobLocation' => [
                'domain' => 'Job',
                'label' => 'Job Location'
            ],
            'jobLocationType' => [
                'domain' => 'Job',
                'label' => 'Job Location Type'
            ],
            'jobTitle' => [
                'domain' => 'Job',
                'label' => 'Job Title'
            ],
            'officialWebsite' => [
                'domain' => 'TechnicalSkill',
                'label' => 'Official Website'
            ],
            'eventDate' => [
                'domain' => 'Event',
                'label' => 'Event Date'
            ],
            'eventTitle' => [
                'domain' => 'Event',
                'label' => 'Event Title'
            ],
            'eventType' => [
                'domain' => 'Event',
                'label' => 'Event Type'
            ],
            'isOnline' => [
                'domain' => 'Event',
                'label' => 'Is Online'
            ],
            'isAvailable' => [
                'domain' => 'Job',
                'label' => 'Is Available'
            ],
            'isReal' => [
                'domain' => 'Job',
                'label' => 'Is Real'
            ],
            'wikidataURI' => [
                'domain' => 'TechnicalSkill',
                'label' => 'Wikidata URI'
            ],
        ];

        foreach ($dataProperties as $property => $details) {
            $this->triples[] = "<{$this->baseUri}{$property}> rdf:type owl:DatatypeProperty .";
            $this->triples[] = "<{$this->baseUri}{$property}> rdfs:domain <{$this->baseUri}{$details['domain']}> .";
            $this->triples[] = "<{$this->baseUri}{$property}> rdfs:label \"" . addslashes($details['label']) . "\"^^xsd:string .";

            $range = match ($property) {
                'datePosted' => 'xsd:dateTime',
                'experienceInYears' => 'xsd:int',
                'eventDate' => 'xsd:dateTime',
                'isOnline' => 'xsd:boolean',
                'isAvailable' => 'xsd:boolean',
                'isReal' => 'xsd:boolean',
                'wikidataURI' => 'xsd:anyURI',
                default => 'xsd:string'
            };
            $this->triples[] = "<{$this->baseUri}{$property}> rdfs:range {$range} .";
        }
    }

    private function addGlobalPropertyLabels(): void
    {
        // Label for rdfs:label
        $this->triples[] = "<http://www.w3.org/2000/01/rdf-schema#label> rdfs:label \"Label\"^^xsd:string .";

        // Label for rdf:type
        $this->triples[] = "<http://www.w3.org/1999/02/22-rdf-syntax-ns#type> rdfs:label \"Instance Of\"^^xsd:string .";
    }


    private function addNamedIndividuals(): void
    {
        $individuals = [
            'Numpy' => 'Library',
            'Software_Developer' => 'Job'
        ];

        foreach ($individuals as $individual => $type) {
            $this->triples[] = "<{$this->baseUri}{$individual}> rdf:type <{$this->baseUri}{$type}> .";
        }
    }
}

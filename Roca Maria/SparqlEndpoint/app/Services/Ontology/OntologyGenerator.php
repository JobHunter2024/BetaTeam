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
            ]
        ];

        foreach ($objectProperties as $property => $details) {
            $this->triples[] = "<{$this->baseUri}{$property}> rdf:type owl:ObjectProperty .";
            $this->addPropertyDomains($property, $details['domain']);
            $this->triples[] = "<{$this->baseUri}{$property}> rdfs:range <{$this->baseUri}{$details['range']}> .";
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
            ]
        ];

        foreach ($dataProperties as $property => $details) {
            $this->triples[] = "<{$this->baseUri}{$property}> rdf:type owl:DatatypeProperty .";
            $this->triples[] = "<{$this->baseUri}{$property}> rdfs:domain <{$this->baseUri}{$details['domain']}> .";
            $this->triples[] = "<{$this->baseUri}{$property}> rdfs:label \"" . addslashes($details['label']) . "\"^^xsd:string .";

            $range = match ($property) {
                'datePosted' => 'xsd:dateTime',
                'experienceInYears' => 'xsd:int',
                default => 'xsd:string'
            };
            $this->triples[] = "<{$this->baseUri}{$property}> rdfs:range {$range} .";
        }
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

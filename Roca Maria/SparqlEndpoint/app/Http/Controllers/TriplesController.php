<?php

namespace App\Http\Controllers;

use App\Repositories\TripleRepositoryInterface;
use App\Services\SparqlService;
use Illuminate\Http\Request;

class TriplesController extends Controller
{
    protected $tripleRepository;
    protected $sparqlService;

    public function __construct(SparqlService $sparqlService)
    {
        $this->sparqlService = $sparqlService;
    }
    /**
     * Display a listing of the triples.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $triples = $this->tripleRepository->all();
        return response()->json($triples);
    }

    /**
     * Store a newly created triple in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'subject' => 'required|string',
            'predicate' => 'required|string',
            'object' => 'required|string',
        ]);

        $triple = $this->tripleRepository->create($validatedData);
        return response()->json($triple, 201);
    }

    /**
     * Display the specified triple.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $triple = $this->tripleRepository->find($id);
        return response()->json($triple);
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

        $triple = $this->tripleRepository->update($id, $validatedData);
        return response()->json($triple);
    }

    /**
     * Remove the specified triple from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->tripleRepository->delete($id);
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
}
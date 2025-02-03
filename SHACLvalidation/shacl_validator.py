from pyshacl import validate
import rdflib
import os
from dotenv import load_dotenv

load_dotenv()

DATA_TTL_PATH = os.getenv('DATA_TTL_PATH')
SHAPES_TTL_PATH = os.getenv('SHAPES_TTL_PATH')

g = rdflib.Graph()

# Load rdf data from file
g.parse(f"{DATA_TTL_PATH}data.ttl", format="ttl")

# Load SHACL shapes from file
shapes_graph = rdflib.Graph()
shapes_graph.parse(f'{SHAPES_TTL_PATH}shapes.ttl', format='ttl')

# Validate the RDF data against SHACL shapes
conforms, results_graph, results_text = validate(
    data_graph=g,
    shacl_graph=shapes_graph,
    ont_graph=None,
    inference=False 
)

# Print validation results
if conforms:
    print("The RDF data conforms to the SHACL shapes.")
else:
    print("The RDF data does not conform to the SHACL shapes.")
    print("Validation Results:")
    print(results_text)

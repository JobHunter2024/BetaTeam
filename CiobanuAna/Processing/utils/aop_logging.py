import aspectlib
import time
from CiobanuAna.Processing.utils.logging_config import get_papertrail_logger

# Create a logger instance
logger = get_papertrail_logger(__name__)

@aspectlib.Aspect
def log_aspect(*args, **kwargs):
    """Aspect for logging function calls and results."""
    logger.info(f"Calling {__name__} with args: {args}, kwargs: {kwargs}")
    result = yield aspectlib.Proceed
    logger.info(f"{__name__} returned {result}")
    yield aspectlib.Return(result)

@aspectlib.Aspect
def exception_handling_aspect(*args, **kwargs):
    """Aspect for handling and logging exceptions."""
    try:
        result = yield aspectlib.Proceed
        yield aspectlib.Return(result)
    except Exception as e:
        logger.error(f"Exception in {__name__}: {e}")
        raise  # Re-raise the exception after logging

@aspectlib.Aspect
def execution_time_aspect(*args, **kwargs):
    """Aspect for measuring execution time of a function."""
    start_time = time.time()  # Capture start time
    result = yield aspectlib.Proceed  # Proceed with function execution
    end_time = time.time()  # Capture end time
    execution_time = end_time - start_time
    logger.info(f"Execution time: {execution_time:.4f} seconds")
    yield aspectlib.Return(result)  


# Aspect for validating arguments
@aspectlib.Aspect
def argument_validation_aspect(*args, **kwargs):
    """Aspect for validating that the second and third arguments are lists of strings."""

    if len(args) != 3:
        raise ValueError(f"Expected 3 arguments, but got {len(args)}. Arguments should be: self, language_skills, and soft_skills.")
    
    language_skills, soft_skills = args[1], args[2]
    
    # Validate that both `language_skills` and `soft_skills` are lists
    if not isinstance(language_skills, list):
        raise TypeError(f"Second argument (language_skills) must be a list, but got {type(language_skills)}")
    if not isinstance(soft_skills, list):
        raise TypeError(f"Third argument (soft_skills) must be a list, but got {type(soft_skills)}")
    
    if not all(isinstance(item, str) for item in language_skills):
        raise TypeError("All elements in the second argument (language_skills) must be strings.")
    if not all(isinstance(item, str) for item in soft_skills):
        raise TypeError("All elements in the third argument (soft_skills) must be strings.")
    
    print(f"Arguments validated: {args}")
    
    # Proceed with function execution
    result = yield aspectlib.Proceed
    yield aspectlib.Return(result)

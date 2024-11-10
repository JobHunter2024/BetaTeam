import aspectlib
from .logging_config import get_papertrail_logger

# Create a logger instance
logger = get_papertrail_logger(__name__)

@aspectlib.Aspect
def log_aspect(*args, **kwargs):
    """Aspect for logging function calls and results."""
    logger.info(f"Calling {__name__} with args: {args}, kwargs: {kwargs}")
    result = yield aspectlib.Proceed
    logger.info(f"{__name__} returned {result}")
    yield aspectlib.Return(result.strip())

@aspectlib.Aspect
def exception_handling_aspect(*args, **kwargs):
    """Aspect for handling and logging exceptions."""
    try:
        result = yield aspectlib.Proceed
        yield aspectlib.Return(result.strip())
    except Exception as e:
        logger.error(f"Exception in {__name__}: {e}")
        raise  # Re-raise the exception after logging

import logging
import logging.handlers
import os

# Load environment variables for Papertrail
PAPERTRAIL_HOST = os.getenv('PAPERTRAIL_HOST')
PAPERTRAIL_PORT = int(os.getenv('PAPERTRAIL_PORT'))

def get_papertrail_logger(name: str) -> logging.Logger:
    """Configure and return a logger that sends logs to Papertrail."""
    logger = logging.getLogger(name)
    logger.setLevel(logging.INFO)

    # Set up SysLogHandler for Papertrail
    syslog_handler = logging.handlers.SysLogHandler(address=(PAPERTRAIL_HOST, PAPERTRAIL_PORT))
    syslog_handler.setLevel(logging.INFO)

    # Define log format
    formatter = logging.Formatter('%(asctime)s - %(name)s - %(levelname)s - %(message)s')
    syslog_handler.setFormatter(formatter)

    # Attach handler to logger
    if not logger.handlers:
        logger.addHandler(syslog_handler)

    return logger

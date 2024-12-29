from CiobanuAna.Processing.utils.logging_config import get_papertrail_logger

logger = get_papertrail_logger(__name__)

class FSMMonitor:
    """
     SkillExtractor must be called before TechnicalSkillExtractor or SoftSkillExtractor.
    """

    STATES = {"UNINITIALIZED", "INITIALIZED", "ERROR"}
    _instance = None

    def __new__(cls, *args, **kwargs):
        if cls._instance is None:
            cls._instance = super(FSMMonitor, cls).__new__(cls, *args, **kwargs)
            cls._instance.state = "UNINITIALIZED"
        return cls._instance

    def call_all_skill_extractor(self):
        if self.state == "UNINITIALIZED":
            logger.info("AllSkillExtractor called: State -> INITIALIZED")
            self.state = "INITIALIZED"
        else:
            logger.info("AllSkillExtractor called again. State remains INITIALIZED")

    def call_technical_skill_extractor(self):
        if self.state == "INITIALIZED":
            logger.info("TechnicalSkillExtractor called: State remains INITIALIZED")
        else:
            self._violation("TechnicalSkillExtractor called before AllSkillExtractor")

    def call_soft_skill_extractor(self):
        if self.state == "INITIALIZED":
            logger.info("SoftSkillExtractor called: State remains INITIALIZED")
        else:
            self._violation("SoftSkillExtractor called before AllSkillExtractor")        

    def _violation(self, message):
        logger.info(f"Violation detected: {message}. State -> ERROR")
        self.state = "ERROR"
        raise RuntimeError(message)

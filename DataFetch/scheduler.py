from apscheduler.schedulers.blocking import BlockingScheduler
import asyncio
from rss_feed_parser import rss_fetch_and_store_events

def scheduled_rss_fetching():
    loop = asyncio.new_event_loop()
    asyncio.set_event_loop(loop)
    loop.run_until_complete(rss_fetch_and_store_events())

scheduler = BlockingScheduler()

scheduler.add_job(scheduled_rss_fetching, 'cron', hour=0, minute=0)

print("Scheduler started.")

scheduler.start()

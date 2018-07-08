#!/usr/bin/python
#encoding:utf-8

# name of the metric
name = "time_720"

# libraries required for metric measurement
import time
from datetime import datetime, timedelta
import sqlite3              #Import SQLite library

# hour_value is the number of minute in full hours that have passed in the day
hour_value = time.localtime().tm_hour * 60

# minute_value is the number of minutes that have passed in a day.
minute_value = hour_value + time.localtime().tm_min

c = time.strftime('%Y-%m-%d %H:%M:00', time.gmtime())
c2 = time.strptime(c,"%Y-%m-%d %H:%M:%S")

# Base time in seconds
base_time = time.mktime(c2)

#reduce the time by minute increments till we find the correct base time
while int(minute_value/720) != (minute_value/720):
	minute_value = minute_value - 1
	base_time = base_time - 60

base_time = time.strftime("%Y-%m-%d %H:%M:%S",time.localtime(base_time))

#Query using results of the first query_vcvarsall
db = sqlite3.connect('monitoring')
db.row_factory = lambda cursor, row: row[0]
c = db.cursor()
## Select all metrics with the appropriate schedules
c.execute("\
select dtg from stored \
WHERE  dtg <= ? \
AND name IN (\
select name from status \
WHERE schedule=720 \
AND monitored=1 \
AND measured=1 \
) \
ORDER BY dtg DESC \
LIMIT 1\
", (base_time,))
## Store the data in the variable 'result'
results = c.fetchall()
## Close the database
db.close()

# convert base_time and results[0] to seconds and subtract for value

c2 = time.strptime(base_time,"%Y-%m-%d %H:%M:%S")
base_time = time.mktime(c2)

c2 = time.strptime(results[0],"%Y-%m-%d %H:%M:%S")
high_time = time.mktime(c2)

value = base_time - high_time


############## Check Update and Store ##################
# Import the local python module
import checkupdatestore

checkupdatestore.row(name,value)


#!/usr/bin/python
#encoding:utf-8

#Import SQLite library
import sqlite3

# Opens a database file called measurements
conn = sqlite3.connect('monitoring', isolation_level=None)
db = conn.cursor()

# Delete any records that are older than 1 year
db.execute('DELETE FROM stored WHERE dtg<DATETIME("now","localtime", "-14 days")')
# VACUUM the database to remove any unnecessary data
db.execute('VACUUM')

# Commit the changes to the database and close the connection
conn.commit()
conn.close

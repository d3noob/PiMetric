# Define the function and gather the variable
def action(name):
	
	# default libraries required to use database
	import sqlite3

	## Opens the metrics database
	db = sqlite3.connect('monitoring')
	db.row_factory = sqlite3.Row
	c = db.cursor()
	## Select all metrics with the appropriate schedules
	c.execute("\
	select * from status \
	WHERE name=? \
	", (name,))
	## Store the data in the variable 'result'
	results = c.fetchall()
	## Close the database
	db.close()

	originalvalue = results[0]['value']
	originallevel_actual = results[0]['level_actual']

	print(originalvalue,originallevel_actual)
	#return results

def record(name,value,dtg,level_actual):
	
	print(name)
	print(value)
	print(dtg)
	print(level_actual)
	return name,value,dtg,level_actual


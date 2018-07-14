# Define the function and gather the variable
def row(name,value):
	
	# default libraries required to use database
	import sqlite3,time

	# Convert value to float
	value = float(value)

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

	O_value = results[0]['value']
	O_dtg = results[0]['dtg']
	Mparent = results[0]['parent']
	Mpriority = results[0]['priority']
	Mmeasured = results[0]['measured']
	Mmonitored = results[0]['monitored']
	Malert_lower = results[0]['alert_lower']
	Mcaution_lower = results[0]['caution_lower']
	Mnormal_lower = results[0]['normal_lower']
	Mnormal_upper = results[0]['normal_upper']
	Mcaution_upper = results[0]['caution_upper']
	Malert_upper = results[0]['alert_upper']
	O_level_actual = results[0]['level_actual']
	O_level_ack = results[0]['level_ack']
	O_acknowledged = results[0]['acknowledged']

	# Logic for updating
	# do if statement from inside out If green, else, orange etc
	if value >= Mnormal_lower and value <= Mnormal_upper:
		level_actual = 'green'
		level_ack = 'green'
	elif value >= Mcaution_lower and value <= Mcaution_upper:
		level_actual = 'orange'
		level_ack = 'orange'
	elif value >= Malert_lower and value <= Malert_upper:
		level_actual = 'red'
		level_ack = 'red'
	else:
		level_actual = 'blue'
		level_ack = 'blue'
	
	# if the metric is already acknowledged, the level_ack needs to be green
	if O_acknowledged >= 1:
		level_ack = 'green'
	
	print('original = ',O_value,' New = ',value) 

	# get the time
	dtg = time.strftime('%Y-%m-%d %H:%M:%S', time.gmtime())

	print('dtg = ',dtg,' level_actual = ',level_actual,' level_ack = ',level_ack) 

	print(Malert_lower,Mcaution_lower,Mnormal_lower,Mnormal_upper,Mcaution_upper, Malert_upper) 


	# Update the values to the database
	## Opens the metrics database
	db = sqlite3.connect('monitoring')
	c = db.cursor()
	## Update the row value and time in the status table 
	c.execute("UPDATE status SET value = ?, dtg = ?,  level_actual = ?, level_ack = ? WHERE name = ?", (value, dtg, level_actual, level_ack, name))
	## Insert the new metric row into the stored table 
	c.execute("INSERT INTO stored (name, value, dtg) VALUES (?,?,?)", (name, value, dtg))
	## commit the changes to the table
	db.commit()
	## Close the database
	db.close()

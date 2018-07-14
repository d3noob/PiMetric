# Define the function and gather the variable
def row(name):
	
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

	Mvalue = results[0]['value']
	Mdtg = results[0]['dtg']
	Mlabel = results[0]['label']
	Mscript = results[0]['script']
	Mschedule = results[0]['schedule']
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
	Mlevel_actual = results[0]['level_actual']
	Mlevel_ack = results[0]['level_ack']
	Mhelp_url = results[0]['help_url']
	Mdescription = results[0]['description']

	#return results
	return Mvalue,Mdtg,Mlabel,Mscript,Mschedule,Mparent,Mpriority,Mmeasured,Mmonitored,Malert_lower,Mcaution_lower,Mnormal_lower,Mnormal_upper,Mcaution_upper,Malert_upper,Mlevel_actual,Mlevel_ack,Mhelp_url,Mdescription




#!/usr/bin/python
#encoding:utf-8
########################################################################
# Work through all the metrics checking the levels and adjusting
# them appropriately through the hierarchy
########################################################################

# Get all the unique instantiations of parents measured and monitored metrics from the database
# this provides an array of all the parents of measured metrics
import sqlite3

# Get the values from the database
## Open the metrics database
db = sqlite3.connect('monitoring')
## Set row_factory property to sqlite3.Row to allow use of dictonary 
## names in variables (aka associative arrays)
db.row_factory = sqlite3.Row
c = db.cursor()
## Select the unique parent names of metrics that are measured and monitored
c.execute("\
SELECT DISTINCT parent FROM status \
WHERE monitored >= 1 \
AND measured >= 1\
")
## Store the data in the variable 'resultsI'
resultsI = c.fetchall()
## Close the database
db.close()

# Loop throuth all the parents (i = the index)
## Find the number of metrics
num_parents = len(resultsI)

## Loop through the metrics
for i in range(0,num_parents): #Iterate beteween 0 and the end of results
	parent = resultsI[i]['parent']

	print()
	print (parent,' ',i)
	
	# Loop up through the hierachy of metrics for each parent 
	while parent != 'null': 
	
		# Get all parent metrics [p] 
		db = sqlite3.connect('monitoring')
		db.row_factory = sqlite3.Row
		c = db.cursor()
		c.execute("SELECT * FROM status WHERE name = ?", (parent,))
		resultsP = c.fetchall()
		db.close()
		
		level_actual_P = resultsP[0]['level_actual']
		level_ack_P = resultsP[0]['level_ack']
	
		# Get all metrics with currnet parent 
		db = sqlite3.connect('monitoring')
		db.row_factory = sqlite3.Row
		c = db.cursor()
		c.execute("SELECT * FROM status WHERE parent = ?", (parent,))
		resultsM = c.fetchall()
		db.close()
		
		# Loop through all the metrics [m] of parent [p]
		## Find the number of metrics
		num_metrics = len(resultsM)

		# Set the default values to 'green'
		highest_level_ack = 'green'
		highest_level_actual = 'green'
		
		# Set the default for being acknowledged
		ack_state = 0
		
		# loop through the metrics
		for m in range(0,num_metrics):
			print(resultsM[m]['name'],'\'s parent is ',parent)
			level_actual_M = resultsM[m]['level_actual']
			level_ack_M = resultsM[m]['level_ack']
						
			# work out what the highest acknowledged colour is across all the metrics that belong to a parent
			if level_ack_M == 'blue':
				highest_level_ack = 'blue'
			elif (level_ack_M == 'red' and (highest_level_ack != 'blue' and highest_level_ack != 'red')):
				highest_level_ack = 'red'
			elif (level_ack_M == 'orange' and (highest_level_ack != 'blue' and highest_level_ack != 'red' and highest_level_ack != 'orange')):
				highest_level_ack = 'orange'

			# work out what the highest actual colour is across all the metrics that belong to a parent
			if level_actual_M == 'blue':
				highest_level_actual = 'blue'
			elif (level_actual_M == 'red' and (highest_level_actual != 'blue' and highest_level_actual != 'red')):
				highest_level_actual = 'red'
			elif (level_actual_M == 'orange' and (highest_level_actual != 'blue' and highest_level_actual != 'red' and highest_level_actual != 'orange ')):
				highest_level_actual = 'orange'
				
			# Work out if any of the metrics under the parent are acknowledged
			# and if they are set the ack_state to 1
			if resultsM[m]['acknowledged'] >= 1:
				ack_state = 1

		# The highest_level_ack at this point is the highest acknowledged alert level for that parent
		# The highest_level_actual at this point is the highest actual alert level for that parent
		print('Acknowledged: Original level = ',level_ack_P,'. New level = ',highest_level_ack)
		print('Actual: Original level = ',level_actual_P,'. New level = ',highest_level_actual)
		level_ack_P = highest_level_ack
		level_actual_P = highest_level_actual

		# update the database to reflect the new alert levels and acknowledged state
		## Opens the metrics database
		dbu = sqlite3.connect('monitoring')
		cu = dbu.cursor()
		## Update the levels in the status table for the parent 
		cu.execute("UPDATE status SET level_ack = ?, level_actual = ?, acknowledged = ? WHERE name = ?", (level_ack_P, level_actual_P, ack_state, parent))
		## commit the changes to the table
		dbu.commit()
		## Close the database
		dbu.close()
			
		parent = resultsP[0]['parent']
		
		print(parent)

print("Finished")   

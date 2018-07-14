#!/usr/bin/python
#encoding:utf-8

# name of the metric
name = "monitoring_db_size"

# libraries required for metric measurement
import os

# Get the value
## File Size in k bytes

value = os.path.getsize('monitoring')/1024

############## Check Update and Store ##################
# Import the local python module
import checkupdatestore

checkupdatestore.row(name,value)





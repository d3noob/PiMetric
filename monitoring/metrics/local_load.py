#!/usr/bin/python
#encoding:utf-8

# local_load
## Returns the the system load over the past minute

# libraries required for metric measurement
import subprocess

# name of the metric
name = "local_load"

# Get the value
try:
    s = subprocess.check_output(["cat","/proc/loadavg"]).decode('utf-8')
    value = float(s.split()[0])
except:
    value = 100


############## Check Update and Store ##################
# Import the local python module
import checkupdatestore

checkupdatestore.row(name,value)



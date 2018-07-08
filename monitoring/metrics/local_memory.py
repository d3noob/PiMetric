#!/usr/bin/python
#encoding:utf-8

# local_memory
## Returns the used ram as a percentage of the total available

# libraries required for metric measurement
import subprocess

# name of the metric
name = "local_memory"

# Get the value
try:
    s = subprocess.check_output(["free","-m"]).decode('utf-8')
    lines = s.split('\n')
    used_mem = float(lines[1].split()[2])
    total_mem = float(lines[1].split()[1])
    value = (int((used_mem/total_mem)*100))
except:
    value = 100

############## Check Update and Store ##################
# Import the local python module
import checkupdatestore

checkupdatestore.row(name,value)



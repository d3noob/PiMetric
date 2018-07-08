#!/usr/bin/python
#encoding:utf-8

## Returns the percentage used disk space on the /dev/root partition

# libraries required for metric measurement
import subprocess

# name of the metric
name = "local_disk"

# Get the value
try:
    s = subprocess.check_output(["df"]).decode('utf-8')
    lines = s.split("\n")
    value = int(lines[1].split("%")[0].split()[4])
except:
    value = 100

############## Check Update and Store ##################
# Import the local python module
import checkupdatestore

checkupdatestore.row(name,value)



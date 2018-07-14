#!/usr/bin/python
#encoding:utf-8

# libraries required for metric measurement
from subprocess import check_output

# name of the metric
name = "google_ping"

# Get the value
value = check_output("ping -c 1 8.8.8.8 | awk -F'=| ' 'NR==2 {print $10}'", shell=True).decode('utf-8')


############## Check Update and Store ##################
# Import the local python module
import checkupdatestore

checkupdatestore.row(name,value)


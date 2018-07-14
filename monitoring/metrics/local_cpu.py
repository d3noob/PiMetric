#!/usr/bin/python
#encoding:utf-8

## Metric for reading the value of the local CPU in percentage
## https://stackoverflow.com/questions/276052/how-to-get-current-cpu-and-ram-usage-in-python

# libraries required for metric measurement
import os
import time

# name of the metric
name = "local_cpu"

# Get the value
value=str(round(float(os.popen('''grep 'cpu ' /proc/stat | awk '{usage=($2+$4)*100/($2+$4+$5)} END {print usage }' ''').readline()),2))

# get the time
dtg = time.strftime('%Y-%m-%d %H:%M:%S', time.gmtime())
print (dtg)

#print results
print("local_cpu = " + value)

  

############## Check Update and Store ##################
# Import the local python module
import checkupdatestore

checkupdatestore.row(name,value)



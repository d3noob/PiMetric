#!/usr/bin/python
#encoding:utf-8

######### Returns the temperature in degrees C of the CPU

# libraries required for metric measurement
import subprocess

# name of the metric
name = "local_cpu_temperature"

# Get the value
try:
    dir_path="/opt/vc/bin/vcgencmd"
    s = subprocess.check_output([dir_path,"measure_temp"]).decode('utf-8')
    value = float(s.split("=")[1][:-3])
except:
    value = 0

############## Check Update and Store ##################
# Import the local python module
import checkupdatestore

checkupdatestore.row(name,value)



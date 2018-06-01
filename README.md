# PiMetric

Monitoring system for infrastructure and services 

## Overview

PiMetric is a project created to carry out regular checks on hardware and software services. It has been built to run on a Raspberry Pi, but there is no reason why it couldn't scale up for larger installations.

It is designed around the concept of 'metrics' where each metric is a measurable entity that can be recorded and evaluated for correct operation within set parameters.

Examples of the type of metrics include;

- Data pulled from SNMP requests (hard drive space, memory, CPU load)
- Information scraped from a web page.
- Values parsed from a text file (logs)
- HTTP RESTful/API queries (Nagios, bespoke systems, external providers)
- Database content

## Structure

The project is roughly divided into three parts. The measurement core, the management interface and the operating layer.

### Measurement

The measurement core focuses on getting information and recording it appropriately.

It utilises separate processes to gather data in a progromatic way via Python based data gathering modules. These processes store measured values in a SQLite database that contains a table for stored information on all of themetrics and a table for the configuration of the metrics themselves.

The metrics can be arranged into a tree structure to allow users to categorise their monitoring to create greater context for evaluation.

The processes are individually scheduled and run via a cron job.

### Management

The management interface is a simple CRUD system to provide the ability create, edit, delete and view the metric information. While it is not intended to be a operational interface, it shares some features of one. 

The managemnent system includes the appropriate loging, validation and sanitisation required to maintain the integrity of the SQLite database and the structure of the metrics. 

It is built from HTML and PHP using a lightly modified Bootstrap front end and some d3.js graphing components

### Operating

The operating layer provides an end user with the ability to explore the monitoring emvironment and the values that it has collected.

It is designed to be used to display information in different ways depending on the role or end use of the data. For example a 'weather' role might just include information from a local weather station and externaly derived services, wheras a 'network' function might include data rates, access availability 

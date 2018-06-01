# PiMetric

Monitoring system for infrastructure and services 

## Overview

PiMetric is a project created to do regular checks on hardware and software services. It has been built to run on a Raspberry Pi, but there is no reason why it couldn't scale up for larger installations.

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




### Operating

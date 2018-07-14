
# PiMetric

A monitoring system for infrastructure and services.

## Overview

PiMetric is a system designed to allow the user to see what is going on in the world around us in a simple way. With it we can measure values, see when they exceed set limits and view their history from a web interface.

It was written to be as simple as possible to allow it to run on a Raspberry Pi but there is no reason why it couldn't scale up for larger installations.

It uses the concept of 'metrics', where each 'metric' has a measurable value which is recorded and evaluated for correct operation.

![A 'live' Operational view](https://github.com/d3noob/PiMetric/blob/master/img/2018-07-14%2013_56_34-Operating%20Page.png)

![Graph of measured values](https://github.com/d3noob/PiMetric/blob/master/img/2018-06-27%2006_40_42-Read%20Metrics.png)


Examples of the type of metrics include;

- Data pulled from SNMP requests (hard drive space, memory, CPU load)
- Information scraped from a web page.
- Values parsed from a text file (logs)
- HTTP RESTful/API queries (via Nagios, bespoke systems, external providers)
- Database content

## Installation

The [installation instructions](https://github.com/d3noob/PiMetric/wiki/Installation) are fairly manual at this stage, but it is hoped to automete these to make the process easier to new users of the Raspberry Pi.

## Structure.

PiMetric is intended to be a simple combination of well established technologies so that it is easy for users to understand and extend. A description of the structure that it uses can be found in the Wiki [here](https://github.com/d3noob/PiMetric/wiki/Framework).

# PiMetric

Monitoring system for infrastructure and services 

PiMetric is a project created to do regular checks on hardware and software services. It has been built to run on a Raspberry Pi, but there is no reason why it couldn't scale up for larger installations.

It is designed around the concept of 'metrics' where each metric is a measurable entity that can be recorded and evaluated for correct operation within set parameters.

Examples of the type of metrics include;

- Data pulled from SNMP requests (hard drive space, memory, CPU load)
- Information scraped from a web page.
- Values parsed from a text file (logs)
- HTTP RESTful/API queries (Nagios, bespoke systems, external providers)
- Database content

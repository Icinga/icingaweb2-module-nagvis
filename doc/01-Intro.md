# Intro -  Icinga Web 2 Nagvis Module

## Requirements

* Icinga Web 2 (&gt;= 2.0.0)
* NagVis

## Steps


* Install this module 
* Prepare NagVis
* Config this module nagvis

## Overview of the used <parameter>

  Key				| Type		| Description
  ------------------------------|---------------|-----------------
  icingweb2-module-nagvis	| Directory	| Full path to this module
  ICINGAWEB_CONFIGDIR		| Directory	| Path to icingaweb2 configuration dir
  ICINGAWEB_DIR			| Directory	| Path to Icingaweb2 program
  nagvis-dir			| Directory	| Path to NagVis program
  nagvis-conf-dir		| Directory	| Path to NagVis configuration dir
  
  

Example of key/value:

  Key				| Value
  ------------------------------|--------------------------------------
  ICINGAWEB_CONFIGDIR		| /etc/icingaweb2
  ICINGAWEB_DIR			| /usr/share/icingaweb2
  icingweb2-module-nagvis 	| /usr/share/icingaweb2/modules/nagvis
  nagvis-dir			| /usr/share/nagvis
  nagvis-conf-dir		| <nagvis-dir>/etc
  nagvis-conf-dir		| /etc/nagvis


## Where to find the sources?

* This module  https://github.com/icinga/icingaweb2-module-nagvis/
* NagVis https://github.com/NagVis/nagvis


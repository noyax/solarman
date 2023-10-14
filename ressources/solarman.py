#!/usr/bin/python3
# -*- coding: utf-8 -*-
# vim: tabstop=8 expandtab shiftwidth=4 softtabstop=4

# This file is part of Jeedom.
#
# Jeedom is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# Jeedom is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Jeedom. If not, see <http://www.gnu.org/licenses/>.

import logging
import sys
import os
from optparse import OptionParser
from os.path import join
import json
import argparse
import globals
import yaml

try:
    from jeedom.jeedom import *
except ImportError as ex:
    print("Error: importing module from jeedom folder")
    print(ex)
    sys.exit(1)

from pysolarmanv5 import PySolarmanV5
from parser import ParameterParser

QUERY_RETRY_ATTEMPTS = 2

def lire():
	with open(globals.path + globals.lookup_file) as f:
		parameter_definition = yaml.full_load(f)
	
	result = 1
	params = ParameterParser(parameter_definition)
	requests = parameter_definition['requests']
	logging.debug(f"Starting to query for [{len(requests)}] ranges...")

	try:

		for request in requests:
			start = request['start']
			end = request['end']
			mb_fc = request['mb_functioncode']
			logging.debug(f"Querying [{start} - {end}]...")
			_SendData = {}
			attempts_left = QUERY_RETRY_ATTEMPTS
			while attempts_left > 0:
				attempts_left -= 1
				try:
					logging.info(f"Connecting to solarman data logger {globals.inverter_host}:{globals.inverter_port}")
					modbus = PySolarmanV5(globals.inverter_host, globals.inverter_sn, port=globals.inverter_port, mb_slave_id=globals.inverter_mb_slaveid, logger=logging, auto_reconnect=True, socket_timeout=15)
					length = end - start + 1
					if mb_fc==3:
						response=''
						response  = modbus.read_holding_registers(register_addr=start, quantity=length)
					if mb_fc==4:
						response  = modbus.read_input_registers(register_addr=start, quantity=length)
					params.parse(response, start, length)        
					result = 1
				except Exception as e:
					result = 0
					logging.warning(f"Querying [{start} - {end}] failed with exception [{type(e).__name__}: {e}]")
					logging.info(f"Disconnecting from solarman data logger {globals.inverter_host}:{globals.inverter_port}")
					modbus.disconnect()
				if result == 0:
					logging.warning(f"Querying [{start} - {end}] failed, [{attempts_left}] retry attempts left")
				else:
					logging.debug(f"Querying [{start} - {end}] succeeded")
					break
			if result == 0:
				logging.warning(f"Querying registers [{start} - {end}] failed, aborting.")
				sys.exit()
		if result == 1:
			logging.debug(f"All queries succeeded, exposing updated values.")
			current_val = params.get_result()
			logging.debug('Resultat')
			logging.debug(current_val)
			try:
				_SendData = current_val
				logging.debug(_SendData)
				globals.JEEDOM_COM.add_changes('device::' + globals.ideqpmnt, _SendData)
			except Exception:
				error_com = "Connection error"
				logging.error(error_com)
				sys.exit()
			sys.exit()
		else:
			current_val = {}
			logging.info(f"Disconnecting from solarman data logger {globals.inverter_host}:{globals.inverter_port}")
			modbus.disconnect()
			sys.exit()
	except Exception as e:
		logging.warning(f"Querying inverter {globals.inverter_sn} at {globals.inverter_host}:{globals.inverter_port} failed on connection start with exception [{type(e).__name__}: {e}]")
		current_val = {}
		logging.info(f"Disconnecting from solarman data logger {globals.inverter_host}:{globals.inverter_port}")
		modbus.disconnect()
		sys.exit()

    

# ------------------------------------------------------------------------------
# MAIN
# ------------------------------------------------------------------------------

parser = argparse.ArgumentParser(description='Solarman python for Jeedom plugin')
parser.add_argument("--apikey", help="Value to write", type=str)
parser.add_argument("--loglevel", help="Log Level for the daemon", type=str)
parser.add_argument("--callback", help="Value to write", type=str)
parser.add_argument("--cyclesommeil", help="Wait time between 2 readline", type=str)
parser.add_argument("--cycle", help="Cycle to send event", type=str)
parser.add_argument("--nameonduleur", help="Nom de l onduleur", type=str)

parser.add_argument("--configonduleur", help="fichier de config onduleur", type=str)
parser.add_argument("--idonduleur", help="Id de l equipement", type=str)
parser.add_argument("--ipclewifi", help="Adresse IP de la cle wifi", type=str)
parser.add_argument("--portclewifi", help="Port de la cle wifi", type=str)
parser.add_argument("--serialclewifi", help="Numero de serie de la cle wifi", type=str)
parser.add_argument("--mbslaveid", help="Id modbus de l onduleur", type=str)
args = parser.parse_args()

globals.apikey = args.apikey
globals.log_level = args.loglevel
globals.callback = args.callback
globals.cycle_sommeil = args.cyclesommeil
globals.cycle = args.cycle
globals.inverter_name = args.nameonduleur
globals.lookup_file = args.configonduleur
globals.ideqpmnt = args.idonduleur
globals.inverter_host = args.ipclewifi
globals.inverter_port = int(args.portclewifi)
globals.inverter_sn = int(args.serialclewifi)
globals.inverter_mb_slaveid = int(args.mbslaveid)
globals.path = './html/plugins/solarman/data/inverters/'

globals.cycle = float(globals.cycle)
globals.cycle_sommeil = float(globals.cycle_sommeil)


jeedom_utils.set_log_level(globals.log_level)

globals.JEEDOM_COM = jeedom_com(apikey=globals.apikey, url=globals.callback, cycle=globals.cycle)
logging.info('Solarman ------ debut recup donnees de l onduleur : ' + str(globals.ideqpmnt))

logging.info('SOLARMAN------ Apikey : ' + str(globals.apikey))
logging.info('SOLARMAN------ Log level : ' + str(globals.log_level))
logging.info('SOLARMAN------ Callback : ' + str(globals.callback))
logging.info('SOLARMAN------ Cycle Sommeil : ' + str(globals.cycle_sommeil))
logging.info('SOLARMAN------ Cycle : ' + str(globals.cycle))

logging.info('SOLARMAN------ Onduleur : ' + str(globals.inverter_name))
logging.info('SOLARMAN------ Fichier de config : ' + str(globals.lookup_file))
logging.info('SOLARMAN------ Id de l equipement : ' + str(globals.ideqpmnt))
logging.info('SOLARMAN------ Adresse IP de la cle wifi : ' + str(globals.inverter_host))
logging.info('SOLARMAN------ Port de la cle wifi : ' + str(globals.inverter_port))
logging.info('SOLARMAN------ Numero de serie de la cle wifi File : ' + str(globals.inverter_sn))
logging.info('SOLARMAN------ Id modbus de l onduleur : ' + str(globals.inverter_mb_slaveid))

if not globals.JEEDOM_COM.test():
    logging.error('MODEM------ Network communication issues. Please fix your Jeedom network configuration.')
    sys.exit()

lire()
sys.exit()

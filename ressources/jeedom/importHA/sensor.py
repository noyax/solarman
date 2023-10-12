#!/usr/bin/python3
# -*- coding: utf-8 -*-
# vim: tabstop=8 expandtab shiftwidth=4 softtabstop=4

################################################################################
#   Solarman local interface.
#
#   This component can retrieve data from the solarman dongle using version 5
#   of the protocol.
#
###############################################################################

import re
import argparse
import json
import sys
import traceback
import globals
import yaml

try:
    from jeedom.jeedom import *
except ImportError as ex:
    print("Error: importing module from jeedom folder")
    print(ex)
    sys.exit(1)

from pysolarmanv5 import PySolarmanV5
from .solarman import Inverter
from .scanner import InverterScanner
from .services import *
from datetime import date, datetime

class error(Exception):
    def __init__(self, value):
        self.value = value
    def __str__(self):
        return repr(self.value)


# ----------------------------------------------------------------------------
# Solarman core
# ----------------------------------------------------------------------------

#_LOGGER = logging.getLogger(__name__)
_inverter_scanner = InverterScanner()


def lire():
    inverter = Inverter(globals.path, globals.inverter_sn, globals.inverter_host, globals.inverter_port, globals.inverter_mb_slaveid, globals.lookup_file)
    #  Prepare the sensor entities.
    hass_sensors = []
    for sensor in inverter.get_sensors():
        try:
            if "isstr" in sensor:
                hass_sensors.append(SolarmanSensorText(globals.inverter_name, inverter, sensor, globals.inverter_sn))
            else:
                hass_sensors.append(SolarmanSensor(globals.inverter_name, inverter, sensor, globals.inverter_sn))
        except BaseException as ex:
            logging.error(f'Config error {ex} {sensor}')
            raise
    hass_sensors.append(SolarmanStatus(globals.inverter_name, inverter, "status_lastUpdate", globals.inverter_sn))
    hass_sensors.append(SolarmanStatus(globals.inverter_name, inverter, "status_connection", globals.inverter_sn))

    logging.debug(hass_sensors)

    async_add_entities(hass_sensors)
    
    
    
    
    

# Set-up from configuration.yaml
async def async_setup_platform(hass: HomeAssistant, config, async_add_entities : AddEntitiesCallback, discovery_info=None):
    logging.debug(f'sensor.py:async_setup_platform: {config}') 
    _do_setup_platform(hass, config, async_add_entities)
       
# Set-up from the entries in config-flow
async def async_setup_entry(hass: HomeAssistant, entry: ConfigEntry, async_add_entities: AddEntitiesCallback):
    logging.debug(f'sensor.py:async_setup_entry: {entry.options}') 
    _do_setup_platform(hass, entry.options, async_add_entities)


#############################################################################################################
# This is the Device seen by Home Assistant.
#  It provides device_info to Home Assistant which allows grouping all the Entities under a single Device.
#############################################################################################################

class SolarmanSensor():
    """Solarman Device class."""

    def __init__(self, id: str = None, device_name: str = None, model: str = None, manufacturer: str = None):
        self.id = id
        self.device_name = device_name
        self.model = model
        self.manufacturer = manufacturer

    @property
    def device_info(self):
        return {
            "identifiers": {(DOMAIN, self.id)},
            "name": self.device_name,
            "model": self.model,
            "manufacturer": self.manufacturer,
        }

    @property
    def extra_state_attributes(self):
        """Return the extra state attributes."""
        return {
            "id": self.id,
            "integration": DOMAIN,
        }


#############################################################################################################
# This is the entity seen by Home Assistant.
#  It derives from the Entity class in HA and is suited for status values.
#############################################################################################################

class SolarmanStatus(SolarmanSensor, Entity):
    def __init__(self, inverter_name, inverter, field_name, sn):
        super().__init__(sn, inverter_name, inverter.lookup_file)
        self._inverter_name = inverter_name
        self.inverter = inverter
        self._field_name = field_name
        self.p_state = None
        self.p_icon = 'mdi:magnify'
        self._sn = sn
        return

    @property
    def icon(self):
        #  Return the icon of the sensor. """
        return self.p_icon

    @property
    def name(self):
        #  Return the name of the sensor.
        return "{} {}".format(self._inverter_name, self._field_name)

    @property
    def unique_id(self):
        # Return a unique_id based on the serial number
        return "{}_{}_{}".format(self._inverter_name, self._sn, self._field_name)

    @property
    def state(self):
        #  Return the state of the sensor.
        return self.p_state

    def update(self):
        self.p_state = getattr(self.inverter, self._field_name, None)


#############################################################################################################
#  Entity displaying a text field read from the inverter
#   Overrides the Status entity, supply the configured icon, and updates the inverter parameters
#############################################################################################################

class SolarmanSensorText(SolarmanStatus):
    def __init__(self, inverter_name, inverter, sensor, sn):
        SolarmanStatus.__init__(self,inverter_name, inverter, sensor['name'], sn)
        if 'icon' in sensor:
            self.p_icon = sensor['icon']
        else:
            self.p_icon = ''
        return


    def update(self):
    #  Update this sensor using the data.
    #  Get the latest data and use it to update our sensor state.
    #  Retrieve the sensor data from actual interface
        self.inverter.update()

        val = self.inverter.get_current_val()
        if val is not None:
            if self._field_name in val:
                self.p_state = val[self._field_name]
            else:
                uom = getattr(self, 'uom', None)
                if uom and (re.match("\S+", uom)):
                    self.p_state = None
                logging.debug(f'No value recorded for {self._field_name}')


#############################################################################################################
#  Entity displaying a numeric field read from the inverter
#   Overrides the Text sensor and supply the device class, last_reset and unit of measurement
#############################################################################################################

class SolarmanSensor(SolarmanSensorText):
    def __init__(self, inverter_name, inverter, sensor, sn):
        SolarmanSensorText.__init__(self, inverter_name, inverter, sensor, sn)
        self._device_class = sensor['class']
        if 'state_class' in sensor:
            self._state_class = sensor['state_class']
        else:
            self._state_class = None
        self.uom = sensor['uom']
        return

    @property
    def device_class(self):
        return self._device_class


    @property
    def extra_state_attributes(self):
        if self._state_class:
            return  {
                'state_class': self._state_class
            }
        else:
            return None

    @property
    def unit_of_measurement(self):
        return self.uom
    

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
globals.inverter_port = args.portclewifi
globals.inverter_sn = args.serialclewifi
globals.inverter_mb_slaveid = args.mbslaveid
globals.path = '../data/inverters/'

globals.cycle = float(globals.cycle)
globals.cyclesommeil = float(globals.cyclesommeil)


jeedom_utils.set_log_level(globals.loglevel)

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
    shutdown()

lire()
sys.exit()

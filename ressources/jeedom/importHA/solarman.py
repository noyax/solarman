#!/usr/bin/python3
# -*- coding: utf-8 -*-
# vim: tabstop=8 expandtab shiftwidth=4 softtabstop=4

import socket
import yaml
import struct
import globals
from datetime import datetime
from ...parser import ParameterParser
from pysolarmanv5 import PySolarmanV5



QUERY_RETRY_ATTEMPTS = 2

class Inverter:
    def __init__(self, path, serial, host, port, mb_slaveid, lookup_file):
        self._modbus = None
        self._serial = serial
        self.path = path
        self._host = host
        self._port = port
        self._mb_slaveid = mb_slaveid
        self._current_val = None
        self.status_connection = "Disconnected"
        self.status_lastUpdate = "N/A"
        self.lookup_file = lookup_file

        with open(self.path + self.lookup_file) as f:
            self.parameter_definition = yaml.full_load(f)


    def connect_to_server(self):
        if self._modbus:
            return self._modbus
        logging.info(f"Connecting to solarman data logger {self._host}:{self._port}")
        self._modbus = PySolarmanV5(self._host, self._serial, port=self._port, mb_slave_id=self._mb_slaveid, logger=logging, auto_reconnect=True, socket_timeout=15)

    def disconnect_from_server(self):
        if self._modbus:
            try:
                logging.info(f"Disconnecting from solarman data logger {self._host}:{self._port}")
                self._modbus.disconnect()
            finally:
                self._modbus = None

    def send_request(self, params, start, end, mb_fc):
        length = end - start + 1
        match mb_fc:
            case 3:
                response  = self._modbus.read_holding_registers(register_addr=start, quantity=length)
            case 4:
                response  = self._modbus.read_input_registers(register_addr=start, quantity=length)
        params.parse(response, start, length)        


    def get_statistics(self):
        result = 1
        params = ParameterParser(self.parameter_definition)
        requests = self.parameter_definition['requests']
        logging.debug(f"Starting to query for [{len(requests)}] ranges...")

        try:

            for request in requests:
                start = request['start']
                end = request['end']
                mb_fc = request['mb_functioncode']
                logging.debug(f"Querying [{start} - {end}]...")

                attempts_left = QUERY_RETRY_ATTEMPTS
                while attempts_left > 0:
                    attempts_left -= 1
                    try:
                        self.connect_to_server()
                        self.send_request(params, start, end, mb_fc)
                        result = 1
                    except Exception as e:
                        result = 0
                        logging.warning(f"Querying [{start} - {end}] failed with exception [{type(e).__name__}: {e}]")
                        self.disconnect_from_server()
                    if result == 0:
                        logging.warning(f"Querying [{start} - {end}] failed, [{attempts_left}] retry attempts left")
                    else:
                        logging.debug(f"Querying [{start} - {end}] succeeded")
                        break
                if result == 0:
                    logging.warning(f"Querying registers [{start} - {end}] failed, aborting.")
                    break

            if result == 1:
                logging.debug(f"All queries succeeded, exposing updated values.")
                self.status_lastUpdate = datetime.now().strftime("%m/%d/%Y, %H:%M:%S")
                self.status_connection = "Connected"
                self._current_val = params.get_result()
            else:
                self.status_connection = "Disconnected"
                # Clear cached previous results to not report stale and incorrect data
                self._current_val = {}
                self.disconnect_from_server()
        except Exception as e:
            logging.warning(f"Querying inverter {self._serial} at {self._host}:{self._port} failed on connection start with exception [{type(e).__name__}: {e}]")
            self.status_connection = "Disconnected"
            # Clear cached previous results to not report stale and incorrect data
            self._current_val = {}
            self.disconnect_from_server()

    def get_current_val(self):
        return self._current_val

    def get_sensors(self):
        params = ParameterParser(self.parameter_definition)
        return params.get_sensors ()

# Service calls
    def service_write_holding_register(self, register, value):
        logging.debug(f'Service Call: write_holding_register : [{register}], value : [{value}]')
        try:
            self.connect_to_server()
            self._modbus.write_holding_register(register, value)
        except Exception as e:
            logging.warning(f"Service Call: write_holding_register : [{register}], value : [{value}] failed with exception [{type(e).__name__}: {e}]")
            self.disconnect_from_server()
        return

    def service_write_multiple_holding_registers(self, register, values):
        logging.debug(f'Service Call: write_multiple_holding_registers: [{register}], values : [{values}]')
        try:
            self.connect_to_server()
            self._modbus.write_multiple_holding_registers(register, values)
        except Exception as e:
            logging.warning(f"Service Call: write_multiple_holding_registers: [{register}], values : [{values}] failed with exception [{type(e).__name__}: {e}]")
            self.disconnect_from_server()
        return
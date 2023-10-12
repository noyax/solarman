#!/usr/bin/python3
# -*- coding: utf-8 -*-
# vim: tabstop=8 expandtab shiftwidth=4 softtabstop=4

from solarman import Inverter


SERVICE_WRITE_REGISTER = 'write_holding_register'
SERVICE_WRITE_MULTIPLE_REGISTERS = 'write_multiple_holding_registers'
PARAM_REGISTER = 'register'
PARAM_VALUE = 'value'
PARAM_VALUES = 'values'



# Apart from this, it also need to be defined in the file 
# services.yaml for the Home Assistant UI in "Developer Tools"


def register_services (inverter: Inverter ):

    async def write_holding_register(call) -> None:
        inverter.service_write_holding_register(
            register=call.data.get(PARAM_REGISTER), 
            value=call.data.get(PARAM_VALUE))
        return

    async def write_multiple_holding_registers(call) -> None:
        inverter.service_write_multiple_holding_registers(
            register=call.data.get(PARAM_REGISTER),
            values=call.data.get(PARAM_VALUES))
        return

    return
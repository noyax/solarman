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

# import logging
import sys
import argparse

try:
    from jeedom.jeedom import *
except ImportError as ex:
    print("Error: importing module from jeedom folder")
    print(ex)
    sys.exit(1)

#from pysolarmanv5 import PySolarmanV5
from pySolarman import PySolarmanV5

QUERY_RETRY_ATTEMPTS = 2

def ecrire(inverter_host, inverter_port, inverter_sn, inverter_mb_slaveid, mb_fc, registre, liste, unique):
    result = 0
    erreur = 0

    try:
        attempts_left = QUERY_RETRY_ATTEMPTS
        while attempts_left > 0:
            attempts_left -= 1
            try:
                logging.info(f"Connexion au data logger {inverter_host}:{inverter_port}")
                logging.info(f"Données à envoyer : " + str(liste))
                modbus = PySolarmanV5(inverter_host, inverter_sn, port=inverter_port, mb_slave_id=inverter_mb_slaveid, logger=logging, auto_reconnect=True, socket_timeout=15)
                response = 0
                if mb_fc==16:
                    response = modbus.write_multiple_holding_registers(register_addr=registre, values=liste)
                if mb_fc==6:
                    response = modbus.write_holding_register(register_addr=registre, value=unique)
                result = 1
                erreur = 1
            except Exception as e:
                erreur = 0
                logging.warning(f"Ecriture du registre [{hex(registre)}] NOK avec l'exception [{type(e).__name__}: {e}]")
            if 'modbus' in locals():
                try:
                    logging.info(f"Deconnexion du logger {inverter_host}:{inverter_port}")
                    modbus.disconnect()
                finally:
                    modbus = None
            if erreur == 0:
                logging.warning(f"Ecriture du registre [{hex(registre)}] NOK, il reste [{attempts_left} essai]")
            else:
                logging.info(f"Ecriture du registre [{hex(registre)}] succes")
                break
        if result == 0:
            logging.warning(f"Ecriture du registre [{hex(registre)}] NOK, abandon.")
        if result == 1:
            logging.info(f"Ecriture OK.")
        else:
            logging.info(f"Ecriture NOK.")
        sys.exit()
    except Exception as e:
        logging.warning(f"Ecriture du registre [{hex(registre)}] non aboutie avec l'exception [{type(e).__name__}: {e}]")
        sys.exit()

    

# ------------------------------------------------------------------------------
# MAIN
# ------------------------------------------------------------------------------

parser = argparse.ArgumentParser(description='Commande via Solarman python for Jeedom plugin')
parser.add_argument("--modbus", help="code pour ecrire", type=int)
parser.add_argument("--registre", help="1er registre a ecrire", type=int)
parser.add_argument("--ipclewifi", help="Adresse IP de la cle wifi", type=str)
parser.add_argument("--portclewifi", help="Port de la cle wifi", type=int, default=8899)
parser.add_argument("--serialclewifi", help="Numero de serie de la cle wifi", type=int)
parser.add_argument("--mbslaveid", help="Id modbus de l onduleur", type=int, default=1)
parser.add_argument("--liste", help="listes des valeurs a ecrire", nargs='+', type=int, default=0)
parser.add_argument("--unique", help="valeur unique a ecrire", type=int, default=0)

args = parser.parse_args()
if type(args.liste) != list:
    args.liste = [args.list]

jeedom_utils.set_log_level('info')

ecrire(args.ipclewifi, args.portclewifi, args.serialclewifi, args.mbslaveid, args.modbus, args.registre, args.liste, args.unique)
sys.exit()

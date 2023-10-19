#!/usr/bin/python3
# -*- coding: utf-8 -*-
# vim: tabstop=8 expandtab shiftwidth=4 softtabstop=4

import socket
import logging
import sys
import os
import globals
import argparse
try:
    from jeedom.jeedom import *
except ImportError as ex:
    print("Error: importing module from jeedom folder")
    print(ex)
    sys.exit(1)


def main():
    sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM, socket.IPPROTO_UDP)
    sock.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
    sock.setsockopt(socket.SOL_SOCKET, socket.SO_BROADCAST, 1)
    sock.settimeout(1.0)

    request = "WIFIKIT-214028-READ"
    address = ("<broadcast>", 48899)

    sock.sendto(request.encode(), address)
    while True:
        try:
            data = sock.recv(1024)
        except socket.timeout:
            break
        keys = dict.fromkeys(['ipaddress', 'mac', 'serial'])
        values = data.decode().split(",")
        result = dict(zip(keys, values))
        logging.info("Resultat de la recherche : " + str(result))




parser = argparse.ArgumentParser(description='Solarman python for Jeedom plugin')
parser.add_argument("--loglevel", help="Log Level for the daemon", type=str)
args = parser.parse_args()

globals.log_level = args.loglevel

jeedom_utils.set_log_level(globals.log_level)

logging.info('Solarman ------ scan réseau : ')
main()
sys.exit()

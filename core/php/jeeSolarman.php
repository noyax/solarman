<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
set_time_limit(15);

if (!jeedom::apiAccess(init('apikey'), 'solarman')) {
    echo __('Clef API non valide, vous n\'êtes pas autorisé à effectuer cette action (solarman)', __FILE__);
    http_response_code(403);
    die();
}

if (init('test') != '') {
	echo 'OK';
	die();
}

$result = json_decode(file_get_contents("php://input"), true);
if (!is_array($result)) {
	die();
}


$var_to_log = '';

if (isset($result['device'])) {
    foreach ($result['device'] as $key => $data) {
        log::add('solarman','debug',"Message du programme solarman. Id de l'équipement : " . $key);
        $eqlogic = eqLogic::byId(intval($key), 'solarman');
        //if (is_object($eqlogic)) {
            $flattenResults = array_flatten($data);
            foreach ($flattenResults as $key2 => $value) {
                log::add('solarman','debug','Registre décodé en hexa : ' . $key2 . ' en décimal : ' . intval($key2,0) . ' valeur = ' . strval($value));
                $cmd = $eqlogic->getCmd('info',intval($key2,0));
                if (is_object($cmd)){
                    $cmd->event($value);
                }
            }
        //}
    }
}


function array_flatten($array) {
    global $var_to_log;
    $return = array();
    foreach ($array as $key => $value) {
        $var_to_log = $var_to_log . $key . '=' . $value . '|';
        if (is_array($value))
            $return = array_merge($return, array_flatten($value));
        else
            $return[$key] = $value;
    }
    return $return;
}
 

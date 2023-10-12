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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
  include_file('desktop', '404', 'php');
  die();
}

$port = config::byKey('port', 'solarman');
$core_version = '1.1.1';

if (!file_exists(dirname(__FILE__) . '/info.json')) {
  log::add('solarman','warning','Pas de fichier info.json');
}
$data = json_decode(file_get_contents(dirname(__FILE__) . '/info.json'), true);
if (!is_array($data)) {
  log::add('solarman','warning','Impossible de décoder le fichier info.json');
}
try {
  $core_version = $data['pluginVersion'];
} catch (\Exception $e) {
  log::add('solarman','warning','Impossible de récupérer la version.');
}

?>
<form class="form-horizontal">
  <fieldset>
    <div class="configGenerale">
      <legend><i class="icon fas fa-warning"></i> {{Options du Plugin}}</legend>
      <div class="form-group">
          <label class="col-lg-4 control-label">{{Cycle d'interrogation des onduleurs (en minute)}}</label>
          <div class="col-lg-2">
              <input class="configKey form-control" data-l1key="cron" placeholder="{{non utilisé, saisir en cron de l'équipement}}" disabled="disabled"/>
          </div>
      </div>
      <div class="form-group">
          <label class="col-sm-4 control-label">{{Cycle (s)}}</label>
          <div class="col-sm-2">
              <input class="configKey form-control" data-l1key="cycle" placeholder="{{0.3}}"/>
          </div>
      </div>
    </div>
  </fieldset>
  <br /><br /><br /><br />
  <fieldset>
    <legend><i class="icon loisir-pacman1"></i> {{Version}}</legend>
    <div class="form-group">
        <label class="col-lg-4 control-label">Core Solarman <sup><i class="fas fa-question-circle tooltips" title="{{C'est la version du plugin}}" style="font-size : 1em;color:grey;"></i></sup></label>
        <span style="top:6px;" class="col-lg-4"><?php echo $core_version; ?></span>
    </div>
  </fieldset>

</form>

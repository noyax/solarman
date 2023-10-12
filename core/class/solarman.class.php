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

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class solarman extends eqLogic {
  const PATH_INVERTERS          = 'data/inverters/';

  /*     * *************************Attributs****************************** */

  /*
  * Permet de définir les possibilités de personnalisation du widget (en cas d'utilisation de la fonction 'toHtml' par exemple)
  * Tableau multidimensionnel - exemple: array('custom' => true, 'custom::layout' => false)
  public static $_widgetPossibility = array();
  */

  /*
  * Permet de crypter/décrypter automatiquement des champs de configuration du plugin
  * Exemple : "param1" & "param2" seront cryptés mais pas "param3"
  public static $_encryptConfigKey = array('param1', 'param2');
  */

  /*     * ***********************Methode static*************************** */

  public static function cron() {
    $debug = false;
    $idOnduleur = 'Aucun';
    system::kill('solarman.py');
    foreach (eqLogic::byType('solarman') as $eqLogic) {
      $autorefresh = $eqLogic->getConfiguration('autorefresh');
      if ($autorefresh=='* * * *'){
        $idOnduleur = $eqLogic->getId();
        $nameOnduleur = $eqLogic->getName();
        log::add('solarman', 'debug', " récupération des données de l'onduleur : " . '  ' . $nameOnduleur);
        solarman::interroSolarman($eqLogic);
      }
    }
    
  }
  
  public static function cron5() {    
  }
  
  public static function cron10() {    
  }
  
  public static function cron15() {    
  }
  
  public static function cron30() {    
  }
  
  public static function cronHourly() {    
  }
  
  public static function cronDaily() {    
  }
  
  /*     * *********************Méthodes d'instance************************* */

  // Fonction exécutée automatiquement avant la création de l'équipement
  public function preInsert() {
  }

  // Fonction exécutée automatiquement après la création de l'équipement
  public function postInsert() {
    $eqLogic = self::byId($this->getId());
    $configInverter = $eqLogic->getconfiguration('configInverter');
    $fichYaml = '/var/www/html/plugins/solarman/data/inverters/' . $configInverter;
    log::add('solarman', 'debug', ' fichier conf onduleur : ' . '  ' . $configInverter);
    $infoYaml = yaml_parse_file($fichYaml);
    $parametres = $infoYaml['parameters'];
    foreach ($parametres as $item => $items){
      foreach ($items as $value2 => $group){
        $commandes = $group;
        foreach ($commandes as $value3 => $comm){
          $isstr = 'numeric';
          foreach ($comm as $value4 => $details){
            switch($value4){
              case 'name':
                $name = $details;
              break;
              case 'registers':
                $registers = $details;
              break;
              case 'uom':
                $unite = $details;
              break;
              case 'isstr':
                $isstr = 'string';
              break;
              case 'scale':
                $scale = $details;
              break;
              case 'rule':
                $rule = $details;
              break;
              default:
                //toutes les autres valeurs
            }
          }
          log::add('solarman', 'debug', ' récupération des infos de la commande: ' . $name);
          $cmd = (new solarmanCmd());
          $cmd->setEqLogic_id($this->id);
          $cmd->setname($name);
          $cmd->setLogicalId($registers[0]);
          $cmd->setType('info');
          $cmd->setUnite($unite);
          $cmd->setSubType($isstr);
          $cmd->setConfiguration('scale', $scale);
          $cmd->setConfiguration('rule', $rule);
          $cmd->save();
          $cmd->refresh();
        }
      }
    }
  }

  // Fonction exécutée automatiquement avant la mise à jour de l'équipement
  public function preUpdate() {
  }

  // Fonction exécutée automatiquement après la mise à jour de l'équipement
  public function postUpdate() {
  }

  // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement
  public function preSave() {
  }

  // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement
  public function postSave() {
  }

  // Fonction exécutée automatiquement avant la suppression de l'équipement
  public function preRemove() {
  }

  // Fonction exécutée automatiquement après la suppression de l'équipement
  public function postRemove() {
  }

  /*
  * Permet de crypter/décrypter automatiquement des champs de configuration des équipements
  * Exemple avec le champ "Mot de passe" (password)
  public function decrypt() {
    $this->setConfiguration('password', utils::decrypt($this->getConfiguration('password')));
  }
  public function encrypt() {
    $this->setConfiguration('password', utils::encrypt($this->getConfiguration('password')));
  }
  */

  /*
  * Permet de modifier l'affichage du widget (également utilisable par les commandes)
  public function toHtml($_version = 'dashboard') {}
  */

  /*
  * Permet de déclencher une action avant modification d'une variable de configuration du plugin
  * Exemple avec la variable "param3"
  public static function preConfig_param3( $value ) {
    // do some checks or modify on $value
    return $value;
  }
  */

  /*
  * Permet de déclencher une action après modification d'une variable de configuration du plugin
  * Exemple avec la variable "param3"
  public static function postConfig_param3($value) {
    // no return value
  }
  */

  /*
   * Permet d'indiquer des éléments supplémentaires à remonter dans les informations de configuration
   * lors de la création semi-automatique d'un post sur le forum community
   public static function getConfigForCommunity() {
      return "les infos essentiel de mon plugin";
   }
   */

  /*     * **********************Getteur Setteur*************************** */

  public static function interroSolarman($eqLogic)
  {
    $solarmanPath         	  = realpath(dirname(__FILE__) . '/../../ressources');
    log::add('solarman', 'info', '---------------------------------------------------------------');
    log::add('solarman', 'info', ' Démarrage Interrogation Onduleur ' . $eqLogic->getName());
    $idOnduleur = $eqLogic->getId();

    if ($idOnduleur!='Aucun' && $idOnduleur!=''){
      $cmd          = 'nice -n 19 /usr/bin/python3 ' . $solarmanPath . '/solarman.py';
      $cmd         .= ' --apikey ' . jeedom::getApiKey('solarman');
      $cmd         .= ' --loglevel '. log::convertLogLevel(log::getLogLevel(__CLASS__));
      $cmd         .= ' --callback ' . network::getNetworkAccess('internal', 'proto:127.0.0.1:port:comp') . '/plugins/solarman/core/php/jeeSolarman.php';
      $cmd         .= ' --cyclesommeil ' . config::byKey('cycle_sommeil', 'solarman', '0.5');
      $cmd         .= ' --cycle ' . config::byKey('cycle', 'solarman','0.3');
      $cmd         .= ' --nameonduleur ' . $eqLogic->getName();
      $cmd         .= ' --configonduleur ' . $eqLogic->getConfiguration('configInverter');
      $cmd         .= ' --idonduleur ' . $eqLogic->getId();
      $cmd         .= ' --ipclewifi ' . $eqLogic->getConfiguration('ipCleWifi');
      $cmd         .= ' --portclewifi ' . $eqLogic->getConfiguration('portCleWifi');
      $cmd         .= ' --serialclewifi ' . $eqLogic->getConfiguration('serialCleWifi');
      $cmd         .= ' --mbslaveid ' . $eqLogic->getConfiguration('mbSlaveId');

      log::add('solarman', 'debug', ' Exécution du service : ' . $cmd);
      $result = exec('nohup ' . $cmd . ' >> ' . log::getPathToLog('solarman_python_' . $eqLogic->getName()) . ' 2>&1 &');
      if (strpos(strtolower($result), 'error') !== false || strpos(strtolower($result), 'traceback') !== false) {
          log::add('solarman', 'error', '[SOLARMAN]-----' . $result);
          return false;
      }
      log::add('solarman', 'info', '[SOLARMAN] OK');
      log::add('solarman', 'info', '---------------------------------------------------------------');
    }
    else {
      log::add('solarman', 'info', '[SOLARMAN] HS, aucun fichier de paramètres Onduleur sélectionné');
      log::add('solarman', 'info', '---------------------------------------------------------------');
    }
  }

  	/**
	 * Return a list of all inverters name and file.
	 */
	public static function inverterList(){
		$return = array();
		// Get inverters
//		foreach (ls(__DIR__ . '/../../' . self::PATH_INVERTERS, '*.yaml', false, array('files', 'quiet')) as $file) {
 //     $return[] = array($file);
//		}
    $dir = '/../../data/inverters/';
    $return = array_diff(scandir($dir), array('.', '..'));
  	return $return;
	}

  public static function razConfigInverter($fichInverter='Aucun', $id = 0){
    log::add('solarman', 'debug', ' fichier conf onduleur : ' . '  ' . $fichInverter . '    Id Onduleur à modifier : ' . $id);
    $eqLogic = solarman::byId($id);

    $fichYaml = '../../data/inverters/' . $fichInverter;
    $fichexist = file_exists($fichYaml);
    if ($fichexist){
      log::add('solarman', 'debug', ' fichier conf onduleur : ' . '  ' . $fichYaml);
      $infoYaml = yaml_parse_file($fichYaml);
      $parametres = $infoYaml['parameters'];
      foreach ($parametres as $item => $items){
        foreach ($items as $value2 => $group){
          $commandes = $group;
          foreach ($commandes as $value3 => $comm){
            $isstr = 'numeric';
            foreach ($comm as $value4 => $details){
              switch($value4){
                case 'name':
                  $name = $details;
                break;
                case 'registers':
                  $registers = $details;
                break;
                case 'uom':
                  $unite = $details;
                break;
                case 'isstr':
                  $isstr = 'string';
                break;
                case 'scale':
                  $scale = $details;
                break;
                case 'rule':
                  $rule = $details;
                break;
                default:
                  //toutes les autres valeurs
              }
            }
            log::add('solarman', 'debug', ' récupération des infos item: ' . $name . ' logicalId: ' . $registers[0]);
            $cmd = $cmd->getLogicalId($registers[0]);
            //$cmd = $eqLogic->getCmd('info', $registers[0]);
            log::add('solarman', 'debug', ' commande selectionnée : ' . '  ' . $cmd->getLogicalId());
            if (is_object($cmd)) {
              log::add('solarman', 'debug', ' commande existante modifiée : ' . '  ' . $cmd);
              $cmd->setname($name);
              $cmd->setUnite($unite);
              $cmd->setSubType($isstr);
              $cmd->setConfiguration('scale', $scale);
              $cmd->setConfiguration('rule', $rule);
              $cmd->save();
              $cmd->refresh();
            }
            else {
              $cmd = (new solarmanCmd());
              $cmd->setEqLogic_id($this->id);
              $cmd->setname($name);
              $cmd->setLogicalId($registers[0]);
              $cmd->setType('info');
              $cmd->setUnite($unite);
              $cmd->setSubType($isstr);
              $cmd->setConfiguration('scale', $scale);
              $cmd->setConfiguration('rule', $rule);
              $cmd->save();
              $cmd->refresh();
            }  
          }
        }
      }
    }
    else{
      log::add('solarman', 'debug', " Le fichier conf onduleur n'existe pas");
    }
  }
}

class solarmanCmd extends cmd {
  /*     * *************************Attributs****************************** */

  /*
  public static $_widgetPossibility = array();
  */

  /*     * ***********************Methode static*************************** */


  /*     * *********************Methode d'instance************************* */

  /*
  * Permet d'empêcher la suppression des commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
  public function dontRemoveCmd() {
    return true;
  }
  */

  // Exécution d'une commande
  public function execute($_options = array()) {
  }

  /*     * **********************Getteur Setteur*************************** */
}


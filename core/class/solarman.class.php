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
    foreach (eqLogic::byType('solarman') as $eqLogic) {
      if ($eqLogic->getisEnable()==1){
        $autorefresh = $eqLogic->getConfiguration('autorefresh');
        if ($autorefresh=='* * * *' or $autorefresh=='* * * * *' or $autorefresh==1){
          $idOnduleur = $eqLogic->getId();
          $nameOnduleur = $eqLogic->getName();
          log::add('solarman', 'debug', " récupération des données de l'onduleur : " . '  ' . $nameOnduleur);
          solarman::interroSolarman($eqLogic);
        }
      }
    }
  }
  
  public static function cron5() {    
    $debug = false;
    $idOnduleur = 'Aucun';
    foreach (eqLogic::byType('solarman') as $eqLogic) {
      if ($eqLogic->getisEnable()==1){
        $autorefresh = $eqLogic->getConfiguration('autorefresh');
        if ($autorefresh=='*/5 * * *' or $autorefresh=='*/5 * * * *' or $autorefresh==5){
          $idOnduleur = $eqLogic->getId();
          $nameOnduleur = $eqLogic->getName();
          log::add('solarman', 'debug', " récupération des données de l'onduleur : " . '  ' . $nameOnduleur);
          solarman::interroSolarman($eqLogic);
        }
      }
    }
  }
  
  public static function cron10() {    
    $debug = false;
    $idOnduleur = 'Aucun';
    foreach (eqLogic::byType('solarman') as $eqLogic) {
      if ($eqLogic->getisEnable()==1){
        $autorefresh = $eqLogic->getConfiguration('autorefresh');
        if ($autorefresh=='*/10 * * *' or $autorefresh=='*/10 * * * *' or $autorefresh==10){
          $idOnduleur = $eqLogic->getId();
          $nameOnduleur = $eqLogic->getName();
          log::add('solarman', 'debug', " récupération des données de l'onduleur : " . '  ' . $nameOnduleur);
          solarman::interroSolarman($eqLogic);
        }
      }
    }
  }
  
  public static function cron15() {    
    $debug = false;
    $idOnduleur = 'Aucun';
    foreach (eqLogic::byType('solarman') as $eqLogic) {
      if ($eqLogic->getisEnable()==1){
        $autorefresh = $eqLogic->getConfiguration('autorefresh');
        if ($autorefresh=='*/15 * * *' or $autorefresh=='*/15 * * * *' or $autorefresh==15){
          $idOnduleur = $eqLogic->getId();
          $nameOnduleur = $eqLogic->getName();
          log::add('solarman', 'debug', " récupération des données de l'onduleur : " . '  ' . $nameOnduleur);
          solarman::interroSolarman($eqLogic);
        }
      }
    }
  }
  
  public static function cron30() {    
    $debug = false;
    $idOnduleur = 'Aucun';
    foreach (eqLogic::byType('solarman') as $eqLogic) {
      if ($eqLogic->getisEnable()==1){
        $autorefresh = $eqLogic->getConfiguration('autorefresh');
        if ($autorefresh=='*/30 * * *' or $autorefresh=='*/30 * * * *' or $autorefresh==30){
          $idOnduleur = $eqLogic->getId();
          $nameOnduleur = $eqLogic->getName();
          log::add('solarman', 'debug', " récupération des données de l'onduleur : " . '  ' . $nameOnduleur);
          solarman::interroSolarman($eqLogic);
        }
      }
    }
  }
  
  public static function cronHourly() {    
  }
  
  public static function cronDaily() {   
    exec("pkill -f 'solarman.py'");
  }
  
  /*     * *********************Méthodes d'instance************************* */

  // Fonction exécutée automatiquement avant la création de l'équipement
  public function preInsert() {
  }

  // Fonction exécutée automatiquement après la création de l'équipement
  public function postInsert() {
    try{
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
            $widget = '';
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
                case 'widget':
                  $widget = $details;
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
            if($widget!=''){
            	$cmd->setConfiguration('widget', $widget);
            }
            $cmd->save();
            $cmd->refresh();
          }
        }
        $displayParam = displayParams();
        $cmd = (new solarmanCmd());
        $cmd->setEqLogic_id($this->id);
        $cmd->setname('01-Template');
        $cmd->setLogicalId('Template');
        $cmd->setType('info');
        $cmd->setSubType('string');
        $cmd->setTemplate('dashboard', 'solarman::distribution_onduleur');
        $cmd->setDisplay('parameters', $displayParam);
        $cmd->save();
        $cmd->refresh();
      }
           
      
    } catch (Exception $e) {
      log::add('solarman', 'error', ' Attention, erreur lors du postInsert : ' . $e->getMessage());
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

  public static function majCommandes($id){
    $eqLogic = eqLogic::byId($id);
    $interro = solarman::interroSolarman($eqLogic);
    return("ok");
  }

  public static function getInvertersLists(){
    $return = array();
    $dir = '../../data/inverters/';
    $return = array_diff(scandir($dir), array('.', '..'));
    return($return);
  }

  public static function interroSolarman($eqLogic)
  {
    $solarmanPath         	  = realpath(dirname(__FILE__) . '/../../ressources');
    log::add('solarman', 'info', '---------------------------------------------------------------');
    log::add('solarman', 'info', ' Démarrage Interrogation Onduleur ' . strval($eqLogic->getName()));
    $idOnduleur = $eqLogic->getId();
    $nameOnduleur = str_replace(' ', '_', strval($eqLogic->getName()));

    if ($idOnduleur!='Aucun' && $idOnduleur!=''){
      $cmd          = 'nice -n 19 /usr/bin/python3 ' . $solarmanPath . '/solarman.py';
      $cmd         .= ' --apikey ' . jeedom::getApiKey('solarman');
      $cmd         .= ' --loglevel '. log::convertLogLevel(log::getLogLevel(__CLASS__));
      $cmd         .= ' --callback ' . network::getNetworkAccess('internal', 'proto:127.0.0.1:port:comp') . '/plugins/solarman/core/php/jeeSolarman.php';
      $cmd         .= ' --cyclesommeil ' . config::byKey('cycle_sommeil', 'solarman', '0.5');
      $cmd         .= ' --cycle ' . config::byKey('cycle', 'solarman','0.3');
      $cmd         .= ' --nameonduleur ' . $nameOnduleur;
      $cmd         .= ' --configonduleur ' . $eqLogic->getConfiguration('configInverter');
      $cmd         .= ' --idonduleur ' . $eqLogic->getId();
      $cmd         .= ' --ipclewifi ' . $eqLogic->getConfiguration('ipCleWifi');
      $cmd         .= ' --portclewifi ' . $eqLogic->getConfiguration('portCleWifi', '8899');
      $cmd         .= ' --serialclewifi ' . $eqLogic->getConfiguration('serialCleWifi');
      $cmd         .= ' --mbslaveid ' . $eqLogic->getConfiguration('mbSlaveId', '1');

      log::add('solarman', 'debug', ' Exécution du service : ' . $cmd);
      $result = exec('nohup ' . $cmd . ' >> ' . log::getPathToLog('solarman_python_' . $nameOnduleur) . ' 2>&1 &');
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

  public static function searchSolarman()
  {
    log::add('solarman_recherche_reseau', 'info', '---------------------------------------------------------------');
    log::add('solarman_recherche_reseau', 'info', '------------------Démarrage recherche réseau-------------------');
    $solarmanPath         	  = realpath(dirname(__FILE__) . '/../../ressources');

    $cmd          = 'nice -n 19 /usr/bin/python3 ' . $solarmanPath . '/scanner.py';
    $cmd         .= ' --loglevel '. log::convertLogLevel(log::getLogLevel(__CLASS__));

    log::add('solarman_recherche_reseau', 'debug', ' lancement programme : ' . $cmd);
    $result = exec('nohup ' . $cmd . ' >> ' . log::getPathToLog('solarman_recherche_reseau 2>&1 &'));
    if (strpos(strtolower($result), 'error') !== false || strpos(strtolower($result), 'traceback') !== false) {
        log::add('solarman_recherche_reseau', 'error', 'Erreur pendant la recherche : ' . $result);
        return false;
    }
    log::add('solarman_recherche_reseau', 'info', '-----------------------Recherche terminée----------------------');
    log::add('solarman_recherche_reseau', 'info', '---------------------------------------------------------------');
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

  public static function raz_ConfigInverter($fichInverter, $id){
    log::add('solarman', 'debug', ' fichier conf onduleur : ' . '  ' . $fichInverter . '    Id Onduleur à modifier : ' . $id);
    $eqLogic = eqLogic::byId($id);
    $fichYaml = '../../data/inverters/' . $fichInverter;
    $fichexist = file_exists($fichYaml);
    if (($fichexist) && is_object($eqLogic)){
      //log::add('solarman', 'debug', ' fichier conf onduleur : ' . '  ' . $fichYaml);
      $infoYaml = yaml_parse_file($fichYaml);
      $parametres = $infoYaml['parameters'];
      foreach ($parametres as $item => $items){
        foreach ($items as $value2 => $group){
          $commandes = $group;
          foreach ($commandes as $value3 => $comm){
            $isstr = 'numeric';
            $widget = '';
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
                case 'widget':
                  $widget = $details;
                break;
                default:
                  //toutes les autres valeurs
              }
            }
            //$cmd = $cmd->getLogicalId($registers[0]);
            log::add('solarman', 'debug', ' récupération des infos item : ' . $name . ' logicalId: ' . $registers[0]);
            if ($registers[0] != ''){
              $cmd = $eqLogic->getCmd('info', intval($registers[0]));
              //log::add('solarman', 'debug', ' blablabla : ' . var_dump($cmd->getName()));
              //log::add('solarman', 'debug', ' blablabla : ' . var_dump($cmd));
              if (is_object($cmd)) {
                try{
                  $cmd->setname($name);
                  $cmd->setUnite($unite);
                  $cmd->setSubType($isstr);
                  $cmd->setConfiguration('scale', $scale);
                  $cmd->setConfiguration('rule', $rule);
                  if ($widget != '') {
                    $cmd->setConfiguration('widget', $widget);
                  }
                  $cmd->save();
                  $cmd->refresh();
                  log::add('solarman', 'debug', " commande existante remise à l'état initial si besoin : " . '  ' . $name . ' registre : ' . $registers[0]);
                } catch (Exception $e) {
                  log::add('solarman', 'error', ' Attention, erreur sur la commande '. $name . ' : ' . $e->getMessage());
                }
              }
              else {
                try{
                  $cmd = (new solarmanCmd());
                  $cmd->setEqLogic_id($id);
                  $cmd->setname($name);
                  $cmd->setLogicalId($registers[0]);
                  $cmd->setType('info');
                  $cmd->setUnite($unite);
                  $cmd->setSubType($isstr);
                  $cmd->setConfiguration('scale', $scale);
                  $cmd->setConfiguration('rule', $rule);
                  if ($widget != '') {
                    $cmd->setConfiguration('widget', $widget);
                  }
                  $cmd->save();
                  $cmd->refresh();
                  log::add('solarman', 'debug', ' commande absente (re) créée : ' . '  ' . $name . ' registre : ' . $registers[0]);
                } catch (Exception $e) {
                  log::add('solarman', 'error', ' Attention, erreur sur la commande '. $name . ' : ' . $e->getMessage());
                }
              }
            } 
          }
        }
      }
      $cmd = $eqLogic->getCmd('info', 'Template');
      if (!is_object($cmd)) {
        $displayParam = displayParams();
        $cmd = (new solarmanCmd());
        $cmd->setEqLogic_id($id);
        $cmd->setname('01-Template');
        $cmd->setLogicalId('Template');
        $cmd->setType('info');
        $cmd->setSubType('string');
        $cmd->setTemplate('dashboard', 'solarman::distribution_onduleur');
        $cmd->setDisplay('parameters',$displayParam);
        $cmd->save();
        $cmd->refresh();
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


 /*     * **********************Fonctions locales*************************** */



function displayParams(){
/*
 Background : Couleur arrière plan du widget [ Exemple : #fffff, white, linear-gradient... | Défaut : transparent ]
inverterColor : Couleur des éléments de catégorie "onduleur" [ Exemple : #fffff, white]
noGridColor : Couleur du logo "noGridColor" [ Exemple : #fffff, white]
------------ Solar ------------
pv1Name : personnalisation du nom du Pv1. (Ex: Ouest, Nord, PV1 ...)
pv2Name : personnalisation du nom du Pv2. (Ex: Ouest, Nord, PV2 ...)
pv3Name : personnalisation du nom du Pv3. (Ex: Ouest, Nord, PV3 ...)
pv4Name : personnalisation du nom du Pv4. (Ex: Ouest, Nord, PV4 ...)
dailySolarText : personnalisation du texte. (défaut : DAILY SOLAR)
pvMaxPower : Puissance max des PV. (permet la gestion de vitesse de l'animation)
solarColor : Couleur des éléments de catégorie "solaire" [ Exemple : #fffff, white]
------------ Load ------------
load1Name : personnalisation du nom du Load1. (Ex: C.E, Clim, ...)
load1Icon : Afficher Icone. Choix : oven, pump, aircon, boiler, charging
load2Name : personnalisation du nom du Load2. (Ex: C.E, Clim, ....)
load2Icon : Afficher Icone. Choix : oven, pump, aircon, boiler, charging
load3Name : personnalisation du nom du Load3. (Ex: C.E, Clim, ....)
load3Icon : Afficher Icone. Choix : oven, pump, aircon, boiler, charging
load4Name : personnalisation du nom du Load4. (Ex: C.E, Clim, ....)
load4Icon : Afficher Icone. Choix : oven, pump, aircon, boiler, charging
dailyLoadText : personnalisation du texte. (défaut : DAILY LOAD)
loadMaxPower : Puissance max des équipements "Load". (permet la gestion de vitesse de l'animation)
loadColor : Couleur des éléments de catégorie "load" [ Exemple : #fffff, white]
loadAnimate: Pour désactiver l'animation des Load passer ce paramètre a 0
------------ Grid ------------
dailyGridSellText : personnalisation du texte. (défaut : DAILY GRID SELL)
dailyGridBuyText : personnalisation du texte. (défaut : DAILY GRID BUYL)
gridMaxPower : Puissance max de consommation. (permet la gestion de vitesse de l'animation)
gridColor : Couleur des éléments de catégorie "réseau" [ Exemple : #fffff, white]
------------ Battery ------------
dailyBatteryChargeText : personnalisation du texte. (défaut : DAILY CHARGE)
dailyBatteryDischargeText : personnalisation du texte. (défaut : DAILY DISCHARGE)
batteryMaxPower : Puissance max de la batterie. (permet la gestion de vitesse de l'animation)
batterySocShutdown : SOC mini. (defaut: 0)
mpptName : personnalisation du nom du Chargeur PV.
batteryColor : Couleur des éléments de catégorie "batterie" [ Exemple : #fffff, white]
------------ Aux ------------
auxColor : Couleur des éléments de catégorie "aux" [ Exemple : #fffff, white]
auxMaxPower : Puissance max des "Aux". (permet la gestion de vitesse de l'animation)
*/

  $return = array();
  $return = array(
    'Background'=>'transparent',
    'inverterColor'=>'', // : Couleur des éléments de catégorie "onduleur" [ Exemple : #fffff, white]
    'noGridColor'=> '', // : Couleur du logo "noGridColor" [ Exemple : #fffff, white]
    //------------ Solar ------------//
    'pv1Name'=>'PV1',
    'pv2Name'=>'PV2', // : personnalisation du nom du Pv2. (Ex: Ouest, Nord, PV2 ...)
    'pv3Name'=>'PV3', // : personnalisation du nom du Pv3. (Ex: Ouest, Nord, PV3 ...)
    'pv4Name'=>'PV4', // : personnalisation du nom du Pv4. (Ex: Ouest, Nord, PV4 ...)
    'dailySolarText'=>'Production du jour', // : personnalisation du texte. (défaut : DAILY SOLAR)
    'pvMaxPower'=>6000, //Puissance max des PV. (permet la gestion de vitesse de l animation)
    'solarColor'=>'', // Couleur des éléments de catégorie "solaire" [ Exemple : #fffff, white]
    //------------ Load ------------
    'load1Name'=>'Charge 1', // : personnalisation du nom du Load1. (Ex: C.E, Clim, ...)
    'load1Icon'=>'', //: Afficher Icone. Choix : oven, pump, aircon, boiler, charging
    'load2Name'=>'Charge 2', //: personnalisation du nom du Load2. (Ex: C.E, Clim, ....)
    'load2Icon'=>'', //: Afficher Icone. Choix : oven, pump, aircon, boiler, charging
    'load3Name'=>'Charge 3', //: personnalisation du nom du Load3. (Ex: C.E, Clim, ....)
    'load3Icon'=>'', //: Afficher Icone. Choix : oven, pump, aircon, boiler, charging
    'load4Name'=>'Charge 4',// : personnalisation du nom du Load4. (Ex: C.E, Clim, ....)
    'load4Icon'=>'',// : Afficher Icone. Choix : oven, pump, aircon, boiler, charging
    'dailyLoadText'=>'Conso du jour',// : personnalisation du texte. (défaut : DAILY LOAD)
    'loadMaxPower'=>6000,// : Puissance max des équipements "Load". (permet la gestion de vitesse de l'animation)
    'loadColor'=>'',// : Couleur des éléments de catégorie "load" [ Exemple : #fffff, white]
    'loadAnimate'=>1,//: Pour désactiver l'animation des Load passer ce paramètre a 0
    //------------ Grid ------------
    'dailyGridSellText'=>'Surplus du jour',// : personnalisation du texte. (défaut : DAILY GRID SELL)
    'dailyGridBuyText'=>'Conso réseau du jour',// : personnalisation du texte. (défaut : DAILY GRID BUYL)
    'gridMaxPower'=>6000,// : Puissance max de consommation. (permet la gestion de vitesse de l'animation)
    'gridColor'=>'',// : Couleur des éléments de catégorie "réseau" [ Exemple : #fffff, white]
    //------------ Battery ------------
    'dailyBatteryChargeText'=>'Charge du jour',// : personnalisation du texte. (défaut : DAILY CHARGE)
    'dailyBatteryDischargeText'=>'Décharge du jour',// : personnalisation du texte. (défaut : DAILY DISCHARGE)
    'batteryMaxPower'=>2640,// : Puissance max de la batterie. (permet la gestion de vitesse de l'animation)
    'batterySocShutdown'=>0,// : SOC mini. (defaut: 0)
    'mpptName'=>'Nom du chargeur supplémentaire',// : personnalisation du nom du Chargeur PV.
    'batteryColor'=>'',// : Couleur des éléments de catégorie "batterie" [ Exemple : #fffff, white]
    // ------------ Aux ------------
    'auxColor'=>'',// : Couleur des éléments de catégorie "aux" [ Exemple : #fffff, white]
    'auxMaxPower'=>0,// : Puissance max des "Aux". (permet la gestion de vitesse de l'animation)    );
  );
  return($return);
}

/*     * **********************Fonctions locales*************************** */

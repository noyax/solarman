<br><br><br>

Présentation
===
Le plugin Solarman permet de récupérer les informations de votre onduleur monitoré par le site https://home.solarmanpv.com/ , comme par exemple les Sofar Solar HYD de 3 à 6K ES. 

Fichiers de configuration auto pour: Afore_BNTxxxKTL-2mppt, deye_2mppt, deye_4mppt, deye_hybrid, deye_sg04lp3, deye_string, hyd-zss-hp-3k-6k, kstar_hybrid, sofar_g3hyd, sofar_hyd3k-6k-es, sofar_lsw3, sofar_wifikit, sofar_XXTL-G3.yaml, solis_1p8k-5g, solis_3p-4g, solis_hybrid, solis_s6-gr1p, zcs_azzurro-ktl-v3.
<br>

Liste (non exhaustives) des onduleurs pris en compte à l'heure actuelle et le fichier de configuration associé:  
<br>

| Fichier de configuration   | Onduleurs supportés                      | Observations                                                     |
|----------------------------|------------------------------------------|------------------------------------------------------------------|
| Afore_BNTxxxKTL-2mppt.yaml | ?                                        |                                                                  |
| deye_2mppt.yaml            | DEYE Microinverter with 2 MPPT Trackers  | e.g. SUN600G3-EU-230 / SUN800G3-EU-230 / SUN1000G3-EU-230        |
| deye_4mppt.yaml            | DEYE Microinverter with 4 MPPT Trackers  | e.g. SUN1300G3-EU-230 / SUN1600G3-EU-230 / SUN2000G3-EU-230      |
| deye_hybrid.yaml           | DEYE/Sunsynk/SolArk Hybrid inverters     | used when no lookup specified                                    |
| deye_sg04lp3.yaml          | DEYE/Sunsynk/SolArk Hybrid 8/12K-SG04LP3 | e.g. 12K-SG04LP3-EU                                              |
| deye_string.yaml           | DEYE/Sunsynk/SolArk String inverters     | e.g. SUN-4/5/6/7/8/10/12K-G03 Plus                               |
| hyd-zss-hp-3k-6k.yaml      | ?                                        |                                                                  |
| kstar_hybrid.yaml          | ?                                        |                                                                  |
| sofar_g3hyd.yaml           | SOFAR Hybrid Three-Phase inverter        | HYD 6000 or rebranded (three-phase), ex. ZCS Azzurro 3PH HYD-ZSS |
| sofar_hyd3k-6k.yaml        | SOFAR Hybrid Single-Phase inverter       | HYD 6000 or rebranded (single-phase), ex. ZCS Azzurro HYD-ZSS    |
| sofar_lsw3.yaml            | SOFAR Inverters                          |                                                                  |
| sofar_wifikit.yaml         | ?                                        |                                                                  |
| sofar_XXTL-G3.yaml         | SOFAR xxxx TL G3                         | Testé sur Sofar Solar 3000 TL G3                                 |
| solid_1p8k-5g.yaml         | SOLIS 1P8K-5G                            |                                                                  |
| solid_3p-4g.yaml           | SOLIS 3P-4G                              |                                                                  |
| solis_hybrid.yaml          | SOLIS Hybrid inverter                    |                                                                  |
| solid_s6-grip.yaml         | SOLIS S6-GRIP                            |                                                                  |
| zcs_azzurro-ktl-v3.yaml    | ZCS Azzurro KTL-V3 inverters             | ZCS Azzurro 3.3/4.4/5.5/6.6 KTL-V3 (rebranded Sofar KTLX-G3)     |


<br><br><br><br><br>


Pré-requis:
===
Pour pouvoir récupérer les infos de votre onduleur il faut un onduleur compatible (liste ci dessus) équipé d'une clé wifi. Cela ressemble à ça:

![Clé Wifi](cle_wifi.png)

<br>
<br>

Installation du plugin
===
Besoin d'explications? Ok, alors une fois le plugin installé faites une mise à jour des dépendances

<br>
<br>

Configuration générale du plugin
===
![Config générale](recherche.png)

<br>
<br>
Un bouton permet de chercher sur le réseau les onduleurs qui s'y trouvent:
<br>

Pour rechercher: cliquer sur 1, si le bouton de log 3 n'apparait pas alors cliquer sur 4 et enfin cliquer sur le bouton 3.

Voici le genre de log que vous verrez apparaitre:

![Log recherche réseau](log_recherche.png)

Vous n'aurez plus qu'à copier l'adresse IP et le serial dans la configuration de votre onduleur.

Rien de particulier en plus. Un champ non accessible pour une éventuelle évolution mais pas certain qu'elle voit le jour.

<br>
<br>
<br>
<br>
<br>

Création d'un nouvel équipement
===
![Nouvel équipement](ajout_ondul.png)

Cliquer sur le + "Ajouter"

## Choix
<br>

![Choix équipement](ajout_ondul1.png)

Donnez un nom à votre nouvel équipement puis choisissez le fichier modèle qui servira à le paramétrer

<br>

## Paramétrage de l'équipement:
<br>

![paramétrage équipement](param_equipmnt.png)
<br>
Les premiers champs sont classiques.

Ensuite le fichier de configuration que vous avez sélectionné ne sera plus modifiable. Si vous vous êtes trompé alors supprimer cet équipement et recréez en un autre.

Il faut que vous saisissiez l'adresse ip de votre clé wifi, le port qu'elle utilise pour communiquer et son numéro de série. Le port est en général 8899 mais il faudra que vous alliez chercher le numéro de série dans les configuration de votre clé.

la page de configuration se visualise avec votre navigateur internet en saisissant l'adresse de votre clé: http://adresse_ip_de_votre_clé_wifi l'utilisateur et le mot de passe par défaut si vous ne les avez pas changés sont admin et admin

![paramétrage clé wifi](param_cle.png)

Ensuite choississez la fréquence d'interrogation que vous souhaitez, seuls 1, 5, 10, 15 et 30 minutes sont valides.


Si votre onduleur n'est pas dans la liste
===

Si vous pensez que votre onduleur peut faire partie de ceux pouvant être monitorés par Solarman et que vous connaissez les registres modbus à interroger alors en utilisant le ![fichier modèle](modele_onduleur.yaml) vous pouvez en recréér un en respectant bien les principes suivants:

## pour la partie "requests":

exemple:

requests:
  - start: 0x0200
    end:  0x0255
    mb_functioncode: 0x03
  - start: 0x10B0
    end: 0x10BC
    mb_functioncode: 0x04

Il faut donc que vous connaissiez les codes fonction modbus utilisés pour interroger les registres de votre onduleur. Pour chaque "mb_functioncode" vous devez indiquer l'intervalle entre le premier registre à interroger, "start", et le dernier, "end".

## pour la partie "parameters":

exemple:

parameters:
 - group: blablabla  
   items: blablablabla  
    - name: "Le nom du registre"  
      uom: "Wh"  
      scale: 0.01  
      rule: 1  
      registers: [0x0200]  
  
    - name: "Total Production"  
      uom: "kWh"  
      scale: 1  
      rule: 3  
      registers: [0x0255,0x0254]  
  
    - name: "Grid Current"  
      uom: "A"  
      scale: 0.01  
      rule: 2  
      registers: [0x10B0]  
  
    - name: "Inverter status"  
      uom: ""  
      scale: 1  
      rule: 1  
      registers: [0x10BC]  
      isstr: true  
      lookup:  
      -  key: 0  
         value: "Stand-by"  
      -  key: 1  
         value: "Self-Checking"  
      -  key: 2  
         value: "Normal"  
      -  key: 3  
         value: "Discharging Check State"  
      -  key: 4  
         value: "Discharging State"  
      -  key: 5  
         value: "EPS State"  
      -  key: 6
         value: "Fault State"  
      -  key: 7
         value: "Permanent State"  
  
### explications

instructions clés:  

   group: ne sert pas dans le plugin mais à conserver, le texte mis ensuite ne sert à rien. Il est nécessaire malgé tout de le conserver!  
   items: idem group. Sous cette instruction vous allez regrouper les différents registres que vous allez demander au plugin d'interroger.  
      name: nom qui sera affiché pour votre commande dans jeedom  
      uom: ici on indique entre guillemets l'unité de mesure de ce qui est stocké dans le registre, va être utilisé dans jeedom  
      scale: facteur multiplicateur à utiliser. Un scale de 0.1 transforme par exemple la valeur 100 en 10  
      rule: règle que le plugin devra appliquer pour interpréter les valeurs stockées dans le registre:  
         1: entier non signé => valeur entre 0 et 65 535 (FF FF en héxadécimal)  
         2: entier signé => valeur entre -32 768 et +32 768  
         3: entier non signé stocké sur plusieurs octets (voir la partie registers)  
         4: entier signé stocké sur plusieurs registres  
         5: valeur en ascii  
         6: utilisation des bits  
         7: version (?)  
         8: date et heure  
         9: heure  
      registers: le ou les registres stockant(s) les valeurs. Toujours à indiquer en héxadécimal et entre crochets (tableau). S'il y a plusieurs registres, les séparer par une virgule et à classer dans le sens du nombre le plus élevé vers le plus petit  
      isstr: optionnel si false. Indique si true que la valeur est en caractère et sera à traduire en utilisant la clé lookup ci dessous  
      lookup: liste de la signification du registre en fonction de sa valeur.  
         key: valeur du registre à traduire en texte  
         value: texte qui sera affiché à la place de la valeur du registre  
  
Attention à bien respecter la structure de ce fichier, les tirets sur certaines lignes et pas sur les autres, l'identation, les guillements ou non, ...  


Remerciements
===

Merci à @jmccrohan pour avoir développé l'excellente librairie pySolarmanV5 [https://pysolarmanv5](https://pysolarmanv5.readthedocs.io/en/stable/#)  

Merci à [@StephaneJoubert](https://github.com/StephanJoubert/home_assistant_solarman) qui a développé un module pour Home Assistant sur lequel j'ai récupéré queleques infos et fichiers python très bien écrits  

Merci enfin à [@Lydie13](https://community.jeedom.com/u/lydie13) qui a traduit les fichiers de configuration dans notre langue  

Bug
===

En cas de bug sur le plugin il est possible de demander de l'aide :

[https://community.jeedom.com/tag/plugin-solarman](https://community.jeedom.com/tag/plugin-solarman)

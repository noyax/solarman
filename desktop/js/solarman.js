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

/* Permet la réorganisation des commandes dans l'équipement */
$("#table_cmd").sortable({
  axis: "y",
  cursor: "move",
  items: ".cmd",
  placeholder: "ui-state-highlight",
  tolerance: "intersect",
  forcePlaceholderSize: true
})

/* Fonction permettant l'affichage des commandes dans l'équipement */
function addCmdToTable(_cmd) {
  if (!isset(_cmd)) {
    var _cmd = { configuration: {} }
  }
  if (!isset(_cmd.configuration)) {
    _cmd.configuration = {}
  }
  var logicaltohex = '0x' + (parseInt(_cmd.logicalId).toString(16).padStart(4, '0'))
  var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">'
  tr += '<td class="hidden-xs">'
  tr += '<span class="cmdAttr" data-l1key="id"></span>'
  tr += '</td>'
  tr += '<td>'
  tr += '<div class="input-group">'
  tr += '<input class="cmdAttr form-control input-sm roundedLeft" data-l1key="name" placeholder="{{Nom de la commande}}">'
  tr += '<span class="input-group-btn"><a class="cmdAction btn btn-sm btn-default" data-l1key="chooseIcon" title="{{Choisir une icône}}"><i class="fas fa-icons"></i></a></span>'
  tr += '<span class="cmdAttr input-group-addon roundedRight" data-l1key="display" data-l2key="icon" style="font-size:19px;padding:0 5px 0 0!important;"></span>'
  tr += '</div>'
  tr += '<select class="cmdAttr form-control input-sm" data-l1key="value" style="display:none;margin-top:5px;" title="{{Commande info liée}}">'
  tr += '<option value="">{{Aucune}}</option>'
  tr += '</select>'
  tr += '</td>'
  tr += '<td>'
  tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>'
  tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>'
  tr += '</td>'
  tr += '<td class="hidden-xs">'
  tr += '<span class="cmdAttr" data-l1key="logicalId"></span>'
  tr += '</td>'
  tr += '<td class="hidden-xs">'
  tr += logicaltohex
  tr += '</td>'
  tr += '<td class="hidden-xs">'
  tr += '<span class="cmdAttr" data-l1key="configuration" data-l2key="widget"></span>'
  tr += '</td>'
  tr += '<td>'
  tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/>{{Afficher}}</label> '
  tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isHistorized" checked/>{{Historiser}}</label> '
  tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label> '
  tr += '<div style="margin-top:7px;">'
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="unite" placeholder="Unité" title="{{Unité}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
  tr += '</div>'
  tr += '</td>'
  tr += '<td>';
  tr += '<span class="cmdAttr" data-l1key="htmlstate"></span>';
  tr += '</td>';
  tr += '<td>'
  if (is_numeric(_cmd.id)) {
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> '
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>'
  }
  tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove" title="{{Supprimer la commande}}"></i></td>'
  tr += '</tr>'
  $('#table_cmd tbody').append(tr)
  var tr = $('#table_cmd tbody tr').last()
  jeedom.eqLogic.buildSelectCmd({
    id: $('.eqLogicAttr[data-l1key=id]').value(),
    filter: { type: 'info' },
    error: function (error) {
      $('#div_alert').showAlert({ message: error.message, level: 'danger' })
    },
    success: function (result) {
      tr.find('.cmdAttr[data-l1key=value]').append(result)
      tr.setValues(_cmd, '.cmdAttr')
      jeedom.cmd.changeType(tr, init(_cmd.subType))
    }
  })
}

$('.eqLogicAction[data-action=addSolarmanEq]').off('click').on('click', function () {
	var dialog_message = '<label class="control-label">{{Nom du nouvel équipement :}}</label> ';
	dialog_message += '<input class="bootbox-input bootbox-input-text form-control" autocomplete="nope" type="text" id="addSolarmanEqName"><br/><br/>';
	dialog_message += '<label class="control-label">{{Utiliser une config pré établie :}}</label> ';
	dialog_message += '<select class="bootbox-input bootbox-input-select form-control" id="addSolarmanInverterSelector">';
  dialog_message += '</select><br/>';
	bootbox.confirm({
		title: "{{Ajouter un nouvel onduleur}}",
		message: dialog_message,
		callback: function (result){ if (result) {
			var eqName = $('#addSolarmanEqName').value();
			if (eqName === undefined || eqName == null || eqName === '' || eqName == false) {
				$.fn.showAlert({message: "{{Le nom de l'équipement ne peut pas être vide !}}", level: 'warning'});
				return false;
			}
			var eqInverter = $('#addSolarmanInverterSelector').val();
      if (eqInverter == '' || eqInverter == 'Aucun') {
				$.fn.showAlert({message: "{{Il faut choisir un modèle d'onduleur dans la liste !}}", level: 'warning'});
				return false;
			}

			jeedom.eqLogic.save({
				type: 'solarman',
				eqLogics: [ $.extend({name: eqName}, {logicalId: eqName}, {configuration: {configInverter: eqInverter}}) ],
//				eqLogics: [ $.extend({logicalId: eqName}), ],
				error: function (error) {
					$.fn.showAlert({message: error.message, level: 'danger'});
				},
				success: function (savedEq) {
          $.fn.showAlert({message: "{{Nouvel onduleur créé, rafraichissez la page si vous ne le voyez pas apparaitre}}", level: 'warning'});
        }
			});
		}}
	});


  $.ajax({// fonction permettant de faire de l'ajax
//    type: "POST", // methode de transmission des données au fichier php
    url: "plugins/solarman/core/ajax/solarman.ajax.php", // url du fichier php
    data: {
        action: "getInverterList",
    },
    dataType: 'json',
    error: function(error) {},
    success: function(dataresult) { // si l'appel a bien fonctionné
      opts = '<option value="">{{Aucun}}</option>';
      for (var value1 in dataresult.result) {
//        console.log(dataresult[value1]);
        opts += '<option value="' + dataresult.result[value1] + '">' + dataresult.result[value1] + '</option>';
      }
      $('#addSolarmanInverterSelector').html(opts);
    }
  });
  

});


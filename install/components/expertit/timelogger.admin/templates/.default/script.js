
function Timelog(option){
	this.xhr = new XMLHttpRequest();
}

Timelog.prototype.init = function(){
	var self = this;
	
	self.initEvents();
	
	return this;
};

Timelog.prototype.Color = function(self, color_elem_id, tbody_elem_id,default_id,tr_timelog_new_id){
	
	document.getElementById(color_elem_id).addEventListener('click', function(event){
		
		var target = event.target;
		var id = target.dataset.id;
				
		if(target.className == 'link change btn btn-success'){
			
			self.hideErrorsTrs(tbody_elem_id);
			
			
			
			var trs = self.validChangeTime(id,tbody_elem_id,default_id,tr_timelog_new_id);
			if(trs.length > 0){
				self.showErrorsTrs(trs);
				return false;
			}else target.parentNode.parentNode.className = "";//Если ошибок нет удаляем желтую полосу с tr
			self.changeTime(id);
			
		}else if(target.className == 'link delete btn btn-danger'){
			if(id == 'new'){
				document.getElementById(tr_timelog_new_id).setAttribute('hidden', 'hidden');
			}else{
				self.deleteTimeById(id, tbody_elem_id);
			}
		}else if(target.className == 'link save btn btn-success'){
			
			self.hideErrorsTrs(tbody_elem_id);
			
			
			var trs = self.validChangeTime('new',tbody_elem_id,default_id,tr_timelog_new_id);
			if(trs.length > 0){
				self.showErrorsTrs(trs);
				return false;
			}
			self.saveTimeRange(color_elem_id,default_id,tr_timelog_new_id);
		}
		
			 
		return false;
	}, false);
}

Timelog.prototype.TimeRangeBtn = function(self, range_btn_elem_id, tbody_elem_id, tr_timelog_new_id){
		document.getElementById(range_btn_elem_id).addEventListener('click', function(event){
		event.preventDefault();
		
		self.addNewTrTimeRange(tbody_elem_id,tr_timelog_new_id);
		
		return false;
	}, false);
}

Timelog.prototype.TBody = function(self, tbody_elem_id, default_id, tr_timelog_new_id){
	document.getElementById(tbody_elem_id).addEventListener('change', function(event){
		event.preventDefault();
		
		var target = event.target;
		
		if(target.className == 'hourse' && target.tagName == 'INPUT'){

			var value = target.value;
			var id = target.id.split('-');
			self.hideErrorsTrs(tbody_elem_id);
			var trs = self.validChangeTime(id[1], tbody_elem_id, default_id, tr_timelog_new_id);
			if(trs.length > 0){
				self.showErrorsTrs(trs);
			}
			if(value > 24){
				target.value = 24;
			}else if(value < 0){
				target.value = 0;
			}
			
			if(target.parentNode.parentNode.getAttribute("class") != 'info')
				target.parentNode.parentNode.className = "warning";

			
		}else if(target.className == 'minutes' && target.tagName == 'INPUT'){
			
			var value = target.value;
			var id = target.id.split('-');
			self.hideErrorsTrs(tbody_elem_id);
			var trs = self.validChangeTime(id[1], tbody_elem_id, default_id, tr_timelog_new_id);
			if(trs.length > 0){
				self.showErrorsTrs(trs);
			}else{
				
			}
			if(value > 59){
				target.value = 59;
			}else if(value < 0){
				target.value = 0;
			}
			
			if(target.parentNode.parentNode.getAttribute("class") != 'info')
				target.parentNode.parentNode.className = "warning";
		}
		
		return false;
	});
}

Timelog.prototype.OtherInitEvents = function(self){
	
	document.getElementById('chekbox_timelog_opt').addEventListener('change', function(event)
	{
		event.preventDefault();
		
		var target = event.target;
		if(target.tagName == "INPUT" && target.getAttribute('data-id') == '1')
		{
			self.checkboxChange(target.dataset.id, Number(target.checked), target);
		}
		return false;
	});
	
	self.AddProgresColor();
	
	
	
}
Timelog.prototype.AddProgresColor = function()
{
	var inputs_color = document.getElementsByTagName('input');
	
	for(var i=0; i<inputs_color.length; i++)
	{
		if(inputs_color[i].getAttribute('type') == 'color')	
		{
			
			var EventProgresColor = function() {
				if(this.parentNode.parentNode.className != 'info')
				this.parentNode.parentNode.className = "warning";
				return false;
			};
			
			inputs_color[i].removeEventListener('change', EventProgresColor, false);
			
			inputs_color[i].addEventListener('change', EventProgresColor, false );
		}
	
	}
}

Timelog.prototype.AddTable = function(self,color_elem_id, tbody_elem_id, tr_timelog_new_id, range_btn_elem_id, default_id){
	self.Color(self, color_elem_id, tbody_elem_id, default_id, tr_timelog_new_id);
	self.TimeRangeBtn(self, range_btn_elem_id, tbody_elem_id,tr_timelog_new_id);
	self.TBody(self, tbody_elem_id, default_id, tr_timelog_new_id);
}


Timelog.prototype.initEvents = function(){
	var self = this;
	
	self.AddTable(self,'color','timelog-tbody','tr-timelog-new','add-time-range-button','default');
	self.AddTable(self,'color-leaks','timelog-leaks-tbody','tr-timelog-leaks-new','add-time-leaks-range-button','default-leaks');
	
	self.OtherInitEvents(self);
	
	var countOnPage;
	document.getElementById('count-on-page').addEventListener('input', function(event){
		console.log(event.target);
		clearTimeout(countOnPage);
		countOnPage = setTimeout(function(){ 
			self.countOnPageChange(parseInt(event.target.getAttribute('data-id')), parseInt(event.target.value), event.target);
		}, 1000);
	});
	return this;
};

Timelog.prototype.hideErrorsTrs = function(tbody_elem_id){

	var tbody = document.getElementById(tbody_elem_id);
	for(var i = 0; i < tbody.rows.length; i++){
		if(tbody.rows[i].getAttribute('class')  != 'info' && tbody.rows[i].getAttribute('class')  != 'warning')
		tbody.rows[i].removeAttribute('class');
	}

};

Timelog.prototype.showErrorsTrs = function(trs){
	var current_tr;

	for(var i = 0; i < trs.length; i++){
		current_tr = document.getElementById(trs[i]);
		if(current_tr.getAttribute('class')  != 'info')
			current_tr.setAttribute('class', 'error');
	}
		
};

Timelog.prototype.validChangeTime = function(id, tbody_elem_id, default_id, tr_timelog_new_id){
	var self = this;

	var fromMinutes = document.getElementById('from_minutes-' + id).value;
	var fromSeconds = document.getElementById('from_seconds-' + id).value;
	var toMinutes = document.getElementById('to_minutes-' + id).value;
	var toSeconds = document.getElementById('to_seconds-' + id).value;
	
	var fromData2 = {
		'hourse': parseInt(fromMinutes),
		'minute': parseInt(fromSeconds)
	}
	
	var toData2 = {
		'hourse': parseInt(toMinutes),
		'minute': parseInt(toSeconds)
	}
	
	var tbody = document.getElementById(tbody_elem_id);
	
	var trs = new Array();
	
	for(var i = 0; i < tbody.rows.length; i++){
	  
	  if(tbody.rows[i].id == tr_timelog_new_id || tbody.rows[i].id == default_id){
		continue;
	  }else if(id != 'new' && tbody.rows[i].id == ('tr-timelog-' + id)){
		  continue;
	  }
		
	  var flag = false;
	  for(var j = 0; j < tbody.rows[i].attributes.length; j++){
		 if(tbody.rows[i].attributes[j]['localName'] == 'hidden'){
		   flag = true;
		   break;
		 }    
	  }
	  
	  if(flag){
		continue;
	  }
	  
	  var fromHourse = tbody.rows[i].cells[1].children[0].value;
	  var fromMinutes = tbody.rows[i].cells[1].children[1].value;
	  
	  var toHourse = tbody.rows[i].cells[2].children[0].value;
	  var toMinutes = tbody.rows[i].cells[2].children[1].value;
	  
	  var fromData1 = {
		'hourse': parseInt(fromHourse),
		'minute': parseInt(fromMinutes)
	  }
	
	  var toData1 = {
		'hourse': parseInt(toHourse),
		'minute': parseInt(toMinutes)
	  }
	  
		if(!((self.compareDate(fromData2, fromData1) && self.compareDate(toData2, fromData1)) || (self.compareDate(toData1, fromData2) && self.compareDate(toData1, toData2)) && self.compareDate(fromData2, toData2))){
			trs.push(tbody.rows[i].id);
			//return false;
		}
	}

	return trs;
};

Timelog.prototype.compareDate = function(date1, date2){
	if((date1.hourse < date2.hourse) || (date1.hourse == date2.hourse && date1.minute < date2.minute)){
		return true;
	}
	return false;
};

Timelog.prototype.saveTimeRange = function(color_elem_id,default_id,tr_timelog_new_id){
	var self = this;
	
	var fromMinutes = document.getElementById('from_minutes-new').value;
	var fromSeconds = document.getElementById('from_seconds-new').value;
	var toMinutes = document.getElementById('to_minutes-new').value;
	var toSeconds = document.getElementById('to_seconds-new').value;
	var colorHex = document.getElementById('color_hex-new').value;
	var t_type_id = document.getElementById(color_elem_id).dataset.typetable;
	

	
	self.sendXhr({
		'url' : '/bitrix/components/expertit/timelogger.admin/ajax.php',
		'method' : 'POST',
		'contentType' : 'application/x-www-form-urlencoded',
		'data' : {
			'fromMinutes' : fromMinutes,
			'fromSeconds' : fromSeconds,
			'toMinutes' : toMinutes,
			'toSeconds' : toSeconds,
			'colorHex' : colorHex,
			't_type_id' : t_type_id,
			'class' : 'Color',
			'action' : 'save'
		},
		'success' : function(response){
			var id = JSON.parse(response);
			document.getElementById(tr_timelog_new_id).setAttribute('id', 'tr-timelog-' + id);
			document.getElementById('from_minutes-new').setAttribute('id', 'from_minutes-' + id);
			document.getElementById('from_seconds-new').setAttribute('id', 'from_seconds-' + id);
			document.getElementById('to_minutes-new').setAttribute('id', 'to_minutes-' + id);
			document.getElementById('to_seconds-new').setAttribute('id', 'to_seconds-' + id);
			document.getElementById('color_hex-new').setAttribute('id', 'color_hex-' + id);
			
			document.getElementById('time-range-td-new').innerHTML = '<span data-id="' + id + '" class="link change btn btn-success">Сохранить</span> <span data-id="' + id + '" class="link delete btn btn-danger">Удалить</span>';
			document.getElementById('time-range-td-new').removeAttribute('id');
			
			document.getElementById('new-row').innerHTML = '<span>' + id + '</span>';
			document.getElementById('new-row').removeAttribute('id');
			
			if(document.getElementById(default_id) != null){
				document.getElementById(default_id).setAttribute('hidden', 'hidden');
			}
		},
		'fail' : function(xhrSatus, statusText){
			
		}
	});
	
	return this;
}

Timelog.prototype.deleteTimeById = function(timeId,tbody_elem_id){
	var self = this;
	
	self.sendXhr({
		'url' : '/bitrix/components/expertit/timelogger.admin/ajax.php',
		'method' : 'POST',
		'contentType' : 'application/x-www-form-urlencoded',
		'data' : {
			'id' : timeId,
			'class' : 'Color',
			'action' : 'delete'
		},
		'success' : function(response){
			document.getElementById('tr-timelog-' + timeId).setAttribute('hidden', 'hidden');
			self.hideErrorsTrs(tbody_elem_id);
		},
		'fail' : function(xhrSatus, statusText){
			
		}
	});
	
	return this;
};

Timelog.prototype.changeTime = function(timeId){
	var self = this;
	
	var fromMinutes = document.getElementById('from_minutes-' + timeId).value;
	var fromSeconds = document.getElementById('from_seconds-' + timeId).value;
	var toMinutes = document.getElementById('to_minutes-' + timeId).value;
	var toSeconds = document.getElementById('to_seconds-' + timeId).value;
	var colorHex = document.getElementById('color_hex-' + timeId).value;
	
	self.sendXhr({
		'url' : '/bitrix/components/expertit/timelogger.admin/ajax.php',
		'method' : 'POST',
		'contentType' : 'application/x-www-form-urlencoded',
		'data' : {
			'id' : timeId,
			'fromMinutes' : fromMinutes,
			'fromSeconds' : fromSeconds,
			'toMinutes' : toMinutes,
			'toSeconds' : toSeconds,
			'colorHex' : colorHex,
			'class' : 'Color',
			'action' : 'change'
		},
		'success' : function(response){
			//response = JSON.parse(response);
			//self.renderTableColor(response);
		},
		'fail' : function(xhrSatus, statusText){
			
		}
	});
	
	return this;
};

Timelog.prototype.sendXhr = function(data){
	var self = this;
	self.xhr.open(data['method'], data['url'], true);
	
	var body = '';
	for(var key in data['data']){
		body += key + '=' + data['data'][key] + '&';
	}
	
	if(body != ''){
		body = body.substring(0, body.length - 1);
	}
	
	self.xhr.setRequestHeader('Content-Type', data['contentType']);
	
	self.xhr.send(body);
	
	self.xhr.onreadystatechange = function() {
		if (self.xhr.readyState != 4) return;
		if (self.xhr.status == 200) {
		  data['success'](self.xhr.responseText);
		}else{
		  data['fail'](self.xhr.status, self.xhr.statusText);
		}
	}
	return this;
};

Timelog.prototype.renderTableColor = function(dataTime){
	var self = this;
	var html = '<table id="timelog-table" class="table">';
	html += '<thead>';
	html += '</tr>';
	html += '<th><span>#ID</span></th>';
	html += '<th><span>От</span></th>';
	html += '<th><span>До</span></th>';
	html += '<th><span>Цвет</span></th>';
	html += '<th><span>Действие</span></th>';
	html += '</tr>';
	html += '</thead>';
	html += '<tbody id="timelog-tbody">';
	if(dataTime.length > 0){
		for(var i = 0; i < dataTime.length; i++){
			html += '<tr id="tr-timelog-' + dataTime[i]['id'] + '">';
			html += '<td><span>' + dataTime[i]['id'] + '</span></td>';
			html += '<td>';
			html += '<input type="number" class="hourse" id="from_minutes-' + dataTime[i]['id'] + '" name="from_minutes" value="' + dataTime[i]['from_minutes'] + '" />:';
			html += '<input type="number" class="minutes" id="from_seconds-' + dataTime[i]['id'] + '" name="from_seconds" value="' + dataTime[i]['from_seconds'] + '" />';
			html += '</td>';
			html += '<td>';
			html += '<input type="number" class="hourse" id="to_minutes-' + dataTime[i]['id'] + '" name="to_minutes" value="' + dataTime[i]['to_minutes'] + '" />:';
			html += '<input type="number" class="minutes" id="to_seconds-' + dataTime[i]['id'] + '" name="to_seconds" value="' + dataTime[i]['to_seconds'] + '" />';
			html += '</td>';
			html += '<td><input type="color" id="color_hex-' + dataTime[i]['id'] + '" name="color_hex" value="' + dataTime[i]['color_hex'] + '" /></td>';
			html += '<td><span data-id="' + dataTime[i]['id'] + '" class="link change btn btn-success">Сохранить</span> <span data-id="' + dataTime[i]['id'] + '" class="link delete btn btn-danger">Удалить</span></td>';
			html += '</tr>';
		}
	}else{
		html += '<tr id="default">';
		html += '<td colspan="5"><span>Данные по цветовой схеме не найдены...</span></td>';
		html += '</tr>';
	}
	html += '</tbody>';
	html += '</table>';
	html += '<button id="export-csv" class="btn btn-success">Добавить новый временной диапазон</button>';
	document.getElementById('color').innerHTML = html;
	
	return this;
};

Timelog.prototype.addNewTrTimeRange = function(tbody_elem_id,tr_timelog_new_id){
	var self = this;
	
	if(document.getElementById(tr_timelog_new_id) == null){
		var tr = document.createElement('TR');
		tr.id = tr_timelog_new_id;
		tr.className = "info";
		
		var td = document.createElement('TD');
		td.id = 'new-row';
		td.innerHTML = 'Новая запись';
		tr.appendChild(td);
		
		td = document.createElement('TD');
		td.innerHTML = '<input type="number" class="hourse" id="from_minutes-new" name="from_minutes" value="0" />:';
		td.innerHTML += '<input type="number" class="minutes" id="from_seconds-new" name="from_seconds" value="0" />';
		tr.appendChild(td);
		
		td = document.createElement('TD');
		td.innerHTML = '<input type="number" class="hourse" id="to_minutes-new" name="to_minutes" value="0" />:';
		td.innerHTML += '<input type="number" class="minutes" id="to_seconds-new" name="to_seconds" value="0" />';
		tr.appendChild(td);
		
		td = document.createElement('TD');
		td.innerHTML = '<input type="color" id="color_hex-new" name="color_hex" value="#FFFFFF" />';
		tr.appendChild(td);
		
		td = document.createElement('TD');
		td.id = 'time-range-td-new';
		td.innerHTML = '<span data-id="new" class="link save btn btn-success">Сохранить</span> <span data-id="new" class="link delete btn btn-danger">Удалить</span>';
		tr.appendChild(td);
		
		
		
		
		
		document.getElementById(tbody_elem_id).appendChild(tr);
		self.AddProgresColor();
	}else{
		document.getElementById(tr_timelog_new_id).removeAttribute('hidden');
	}
	
	return this;
};

//CHECKBOX
Timelog.prototype.countOnPageChange = function(id, is_show_turn, target_state){
	var self = this;

	if(target_state.disabled) 
		return this;
	
	target_state.disabled = true;
	
	self.sendXhr({
		'url' : '/bitrix/components/expertit/timelogger.admin/ajax.php',
		'method' : 'POST',
		'contentType' : 'application/x-www-form-urlencoded',
		'data' : {
			'id' : id,
			'is_show_turn' : is_show_turn,
			'class' : 'Checkbox',
			'action' : 'change'
		},
		'success' : function(response){
				setTimeout(function(){
						target_state.disabled = false;
					}, 500);
				
		},
		'fail' : function(xhrSatus, statusText){
			
		}
	});

	return this;
};

Timelog.prototype.checkboxChange = function(id_checkbox, is_show_turn, target_checkbox){
	var self = this;
	if(target_checkbox.disabled) return this;
	
	target_checkbox.disabled = true;

	
	self.sendXhr({
		'url' : '/bitrix/components/expertit/timelogger.admin/ajax.php',
		'method' : 'POST',
		'contentType' : 'application/x-www-form-urlencoded',
		'data' : {
			'id' : id_checkbox,
			'is_show_turn' : is_show_turn,
			'class' : 'Checkbox',
			'action' : 'change'
		},
		'success' : function(response){
				setTimeout(function(){
					target_checkbox.disabled = false;
					}, 500);
				
		},
		'fail' : function(xhrSatus, statusText){
			
		}
	});

	return this;
};

//---------


window.onload = function(){
	var timelog = new Timelog();
	timelog.init();
};







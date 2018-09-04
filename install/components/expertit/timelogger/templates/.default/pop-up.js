function TimelogPopUp(USER_RIGHTS, COLOR_TYPES, USER_ID)
{
	this.xhr = new XMLHttpRequest();
	this.location_orign = document.location.origin;	

	this.user_rights = USER_RIGHTS;
	this.color_types = COLOR_TYPES;
	this.curr_user_id = USER_ID;
	
	this.pauseClick = false;
	this.pauseTime = 5000;
	this.pauseTimeOut;
	this.IsShow = false;
	this.tempData_ajax = [];
	this.intervalListener;
	//$('html').append('<div id="pop_up_shadow_background"></div>');

	var rightNow = new Date();
	this.DateNow = rightNow.toISOString().slice(0,10).replace(/-/g,"-");
};


TimelogPopUp.prototype.Opt = function(key_value)
{
	var options = {
		'name':'pop_up_timelog'//ID pop-up
	};
	return options[key_value];
};

//Инциализация (Время перерыва больше часа или пустое)
TimelogPopUp.prototype.WrongtimeInit = function()
{
	var self = this;
	self.timelog_date_wrong_begin = document.getElementById('timelog_date_wrong_leaks').dataset.timebegin;
	self.timelog_date_wrong_end = document.getElementById('timelog_date_wrong_leaks').dataset.timeend;
	self.Wrongtime(self.timelog_date_wrong_begin, self.timelog_date_wrong_end);
};


//Пауза popUp_click
TimelogPopUp.prototype.SetPause = function()
{
	var self = this;
	
	self.pauseTimeOut = setTimeout(function(){
				self.pauseClick_timelog = false;
	}, self.pauseTime);
};


//Главная инициализация
TimelogPopUp.prototype.Init = function()
{
	var self = this;
	
	if(this.user_rights >= 'R')
		self.PopUpInit();//Вешаем обработчик для отображения PopUp
	
	self.WrongtimeInit();//время перерыва больше часа или пустое

};

//Инициализация popUp окна
TimelogPopUp.prototype.PopUpInit = function()
{
	var self = this;
	$('.table td:not(:nth-child(2)):not(:nth-child(1))').css('cursor','pointer');//Устанавливаем реакцию курсора
	$('.table td:not(:nth-child(2)):not(:nth-child(1))').click(function()
		{
			if(self.IsShow) return;
			
			self.IsShow = true;
			
			if(self.pauseClick_timelog) return;//все еще пауза, выходим
			else self.pauseClick_timelog = true;//запускаем popup и говорим что нужна пауза
			
			self.SetPause();//Пауза popUp_click
					
			if($(this).text() == "0:0")
				return;
			
			var pop_up = self.PopUpGet();//элемент PopUp			
			$(pop_up).remove();
			
			self.HideCountr();
			
			//Добавляем pop-up	
			var tempStrCountr = '<div><div id="pop_up_timelog_countr"></div>';//Контур вокруг времени
			var tempStr = '<span id="'+self.Opt('name')+'">';//Основной блок
			tempStr += '<div id="pop_up_relax_time">Перерыв: <span id="time-leaks"></span><span class="pop_up_close_btn pop_up_close">X</span></div>';
			tempStr += '<div id="pop_up_scroll_content">';
			
			//MAIN CONTENT POP_UP WILL BE APPEND

			tempStr += '</div>';
						
			tempStr += '<div id="pop_up_space">';
			
			tempStr += '<span id="pop_up_line_detail">';
			tempStr += '<b>Сотрудник:</b> <span id="pop_up_detail_employer_name"></span>';
			tempStr += '<span id="pop_up_detail_date_content"> <b>Дата:</b> <span id="pop_up_detail_date"></span> </span>';
			tempStr += '</span>';
			
			tempStr += '<button type="button" class="btn btn-default pop_up_close">Закрыть</button>';
			
			tempStr += '</div>';
			tempStr += '</span>';
			
			$('#popUpContent').append(tempStr);
			$(this).append(tempStrCountr);
			self.RotateCountr();
			
			self.Leaks(this);//Загружаем информацию в pop_up
			self.Listeners();

			self.blockScrollElementsDefault('pop_up_scroll_content');
			
		}
	);
};

TimelogPopUp.prototype.blockScrollElementsDefault = function(elementId)
{
	var scrollable = undefined;
		
	scrollable = document.getElementById(elementId);
	
	scrollable.addEventListener('wheel', function(event) {
		
		var deltaY = event.deltaY;
		var contentHeight = this.scrollHeight;
		var visibleHeight = this.offsetHeight;
		var scrollTop = this.scrollTop;
		
		if (scrollTop === 0 && deltaY < 0)
			event.preventDefault();
		else if (((visibleHeight + scrollTop) >= contentHeight) && deltaY > 0)
			event.preventDefault();
	});
}

//Собираем всю информацию и отправляем в функцию для отправка через ajax или подгрузка из сохраненых данных
TimelogPopUp.prototype.Leaks = function(this_obj)// Получаем перерыв
{
	var self = this;
	var time = $('#header-table-timelog th:eq('+$(this_obj).index()+')').data('time');//получаем день
	var user_id = this_obj.parentNode.children[0].dataset.id;//беру id пользователя
	document.getElementById('pop_up_detail_employer_name').innerHTML = this_obj.parentNode.children[0].innerHTML;//Загружаем имя сотрудника
	document.getElementById('pop_up_detail_date').innerHTML = time;

	self.UpdatePopUpTimeLeaks(user_id, time);//Загружаем информацию с базы
};

//Пока работает ajax вращается Countr => включим вращение
TimelogPopUp.prototype.RotateCountr = function()
{
	var self = this;
	var angle = 0;
	self.StopRotateCountr();
	self.intervalListener = setInterval(function() {
	  angle += 1;
	  jQuery("#pop_up_timelog_countr").css('transform', 'rotate('+angle+'deg)');
	}, 10);
};

//Пока работает ajax вращается Countr => остановим
TimelogPopUp.prototype.StopRotateCountr = function()
{
	var self = this;
	if(self.intervalListener!=undefined)
		window.clearInterval(self.intervalListener);
};

//Спрятать обводку времени
TimelogPopUp.prototype.HideCountr = function()
{
	var countr = document.getElementById('pop_up_timelog_countr');
	$(countr).remove();
};

//Для короткого обращение по ID
TimelogPopUp.prototype.PopUpGet = function()
{
	var self = this;
	return document.getElementById(self.Opt('name'));
};

//Время перерыва больше часа или пустое
TimelogPopUp.prototype.Wrongtime = function(date_begin, date_end)
{
	var self = this;
	
	self.sendXhr({
		'url' : '/bitrix/components/expertit/timelogger/ajax.php',
		'method' : 'POST',
		'contentType' : 'application/x-www-form-urlencoded',
		'data' : {
			'DATE_BEGIN' : date_begin,
			'DATE_END' : date_end,
			'class' : 'Popuptimelog',
			'action' : 'get_timeleaks_all'
		},
		'success' : function(response){
			response = JSON.parse(response);
			
			if(response!="")
			{
				var tableNodes = document.getElementById('timelog_table').getElementsByTagName("tr");
			
				for (var i = 0; i < tableNodes.length; i++) 
				{
					
					
					if(i == 0)
					{
						continue;
					}
					
					var tableNodes_td = tableNodes[i].getElementsByTagName("td");
					
					var USER_ID = 0;
					for (var j = 0; j < tableNodes_td.length; j++) 
					{
						if(j==0 || j == 1)
						{
							if(j==0)
								USER_ID = tableNodes_td[j].dataset.id;
							
							continue;
						}
						
						var DayTimeLeaks = $('#header-table-timelog th:eq('+$(tableNodes_td[j]).index()+')').data('time');

						if(self.DateNow == DayTimeLeaks)
						{
							break;
						}
						var check = true;
						
					
						
						if(tableNodes_td[j].innerHTML.trim() != "0:0" && tableNodes_td[j].innerHTML.trim() != "0:00")
						{
							
							for(var key in response)
							{
							
								
								if(response[key]['USER_ID'] == USER_ID && response[key]['DATE_START'] == DayTimeLeaks)
								{
									
									for(var keyColor in self.color_types)
									{
										
										if(Number(self.color_types[keyColor]['TYPE']) == 2 && Number(response[key]['TIME_LEAKS']) >= Number(self.color_types[keyColor]['BEGIN'])-60 && Number(response[key]['TIME_LEAKS']) <= Number(self.color_types[keyColor]['END'])+60)
										{
											tableNodes_td[j].innerHTML+='<span alt="'
											+response[key]['TIME_LEAKS']+ ">=" + self.color_types[keyColor]['BEGIN'] + " ; "+response[key]['TIME_LEAKS']+ "<=" + self.color_types[keyColor]['END'] +
											'" style="border-color:'+self.color_types[keyColor]['COLOR']+';" class="timewrong_timelog"></span>';
											check = false;
										}
										
										if(!check){ break;}
									}
								}
									if(!check){break;}
							}
						}else
						{
							tableNodes_td[j].style.cursor = "default";//Устанавливаем обычный курсор там где нет времени
						}
						
					}
				}
			}
		},
		'fail' : function(xhrSatus, statusText){
			
		}
	});

	return this;
};


//События вешаются с появлением окна и удаляются с исчезновением
TimelogPopUp.prototype.Listeners = function()
{
	var self = this;
	
	//CLOSE BUTTNONS
	var classname = document.getElementsByClassName("pop_up_close");
	
	var CloseButtnons = function() {
		self.Close();
	};
	
		
	for (var i = 0; i < classname.length; i++) {
		classname[i].removeEventListener('click', CloseButtnons, false);//Удаляем обработчик
		classname[i].addEventListener('click', CloseButtnons, false);
	}
	//CLOSE BUTTNONS	

};


//Время перерыва и информация по таскам - подгрузка контента (функция используется для двух целей: для загрузки с бд и из уже сохраненых данных)
TimelogPopUp.prototype.UpdatePopUpAjax = function(response){
			var self = this;
			var TASK_NAME_LENGTH = 200;
			
			var r = new RegExp("\x22+","g");//убираем ""
			$('#time-leaks').append(response['LEAKS'].replace(r,""));
			
			var tasksResult = "";
			var arr_groups = [];
			
			for(var key in response['TASKS'])
			{
				if(arr_groups.indexOf(response['TASKS'][key]['GROUP_ID']) == -1)
					arr_groups[
				
								response['TASKS'][key]['GROUP_ID']

					] =	(response['TASKS'][key]['GROUP_ID'] == 0) ? 'Без группы':response['TASKS'][key]['GROUP_NAME'];
					

			}
			
			for(var key_group in arr_groups)//Таски по группам
			{
				tasksResult+='<ul class="pop_up_list">';
				
				
				tasksResult+='<li>'+arr_groups[key_group]+'</li>';
				for(var key in response['TASKS'])
				{
					if(response['TASKS'][key]['GROUP_ID'] == key_group)
						{
							var hour = parseInt(response['TASKS'][key]['SECONDS'] / 60 / 60);
							var min = parseInt(response['TASKS'][key]['SECONDS'] / 60 % 60);
							
							tasksResult+='<li>'+"<span>"+
							((hour<=9)?"0"+hour:hour)+':'+((min<=9)?"0"+min:min)+'</span>'
							+'<a target="blank" href="'+self.location_orign+'/company/personal/user/'+self.curr_user_id+'/tasks/task/view/'+response['TASKS'][key]['TASK_ID']+'/">'+
							response['TASKS'][key]['TASK_NAME']
							+'</a></li>';
						}
				}
				tasksResult+="</ul>";
			}
		
			$('#pop_up_scroll_content').html(tasksResult);
			
			//ПОКАЗЫВАЕМ ОКНО
			$("#"+self.Opt('name')).fadeIn(100);
			self.StopRotateCountr();
			$('#pop_up_shadow_background').show();
			self.pauseClick_timelog = false;
			
};

//Время перерыв и информация по таскам
TimelogPopUp.prototype.UpdatePopUpTimeLeaks = function(user_id,date){
	
	var self = this;
	var result = null;
	
	if(self.tempData_ajax[user_id])
	{
		if(self.tempData_ajax[user_id][date])
		{	
			self.UpdatePopUpAjax(self.tempData_ajax[user_id][date]);
			return this; 
		}
	} 
	
	self.sendXhr({
		'url' : '/bitrix/components/expertit/timelogger/ajax.php',
		'method' : 'POST',
		'contentType' : 'application/x-www-form-urlencoded',
		'data' : {
			'USER_ID' : user_id,
			'TIMESTAMP_X' : date,
			'class' : 'Popuptimelog',
			'action' : 'pop_up_data'
		},
		'success' : function(response){
			response = JSON.parse(response);
			if(self.tempData_ajax[user_id] == undefined){
				self.tempData_ajax[user_id] = [];
			}
			
			self.tempData_ajax[user_id][date] = response;
			self.UpdatePopUpAjax(response);
			
		},
		'fail' : function(xhrSatus, statusText){
			
		}
	});

	return this;
};

TimelogPopUp.prototype.sendXhr = function(data){
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


//Если вне области popUp происходит клик - закрываем popup
TimelogPopUp.prototype.InitDocHandlers = function()
{
	var self = this;
	
	$(document).mouseup(function (e) {
		var container = $(self.PopUpGet());
		if (container.has(e.target).length === 0){
			self.Close();
		}
	});
	//По событию push Esc закрываем PopUp
	$(document).keyup(function(e) {
		if (e.keyCode === 27)
		{
			self.Close();
		};   // esc
	});
};

TimelogPopUp.prototype.Close = function(){
	var self = this;
	var container = $(self.PopUpGet());
	container.fadeOut(100);
	self.StopRotateCountr();
	self.HideCountr();
	self.IsShow = false;
	
	$('#pop_up_shadow_background').fadeOut(100);

};


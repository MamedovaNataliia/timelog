
function Timelog(option){
	//this.year = option.year;
	//this.month = option.month;

	this.dateFrom = option.dateFrom;
	this.dateTo = option.dateTo;
	this.groupId = option.groupId;
	this.userId = option.userId;
	this.mode = option.mode;
	this.pathForExportCsv = option.pathForExportCsv;
	this.pathForClearCsv = option.pathForClearCsv;
	this.pathForGetUser = option.pathForGetUser;
	
	this.rectangleSide = 50;
	this.optionsElem = document.getElementById('options-timelog');
	this.scrolableElement = document.getElementById('scrolable-element');
	this.footerTimelog = document.getElementById('timelog-footer');
	this.mainTable = document.getElementById('timelog_table');
	this.timelogElement = document.getElementById('timelog');
}

Timelog.prototype.fillToWindowScreen = function() {
	
	if(this.mainTable.clientHeight < window.innerHeight){
		this.mainTable.style.height = this.scrolableElement.clientHeight+'px';
	}else{
		var marginTop = 20;
		this.scrolableElement.style.height = window.innerHeight-this.footerTimelog.clientHeight-marginTop+'px';
	}
}

Timelog.prototype.deleteBitrixFooter = function(){
	//bx-layout-inner-left
	//bx-layout-inner-center template-bitrix24
	var leftFooter = document.querySelectorAll('.template-bitrix24 tbody td.bx-layout-inner-left')[1];
	var centerFooter = document.querySelectorAll('.template-bitrix24 tbody td.bx-layout-inner-center')[1];
	leftFooter.style.display = 'none';
	centerFooter.style.display = 'none';
}

Timelog.prototype.initOptionsBtn = function(){
	var self = this;

	var optionBtn = document.getElementById('options-btn');
	var container = document.getElementById('options-container');
	
	optionBtn.addEventListener('click', function(){
		container.classList.toggle('active');
	});

	//timelog-form-date
	var timelogFormDate = document.getElementById('timelog-form-date');
	container.appendChild(timelogFormDate);
	timelogFormDate.style.display = 'block';
}

Timelog.prototype.tableScroll = function(){
	
	var scrollableElement = document.querySelector('#scrolable-element');
	var maxWidth = document.getElementById('workarea-content').clientWidth;
	scrollableElement.style.width = (maxWidth - (maxWidth % this.rectangleSide)-this.rectangleSide) + 'px';
	
	scrollableElement.style.overflowX = 'scroll';
	scrollableElement.style.overflowY = 'scroll';
	scrollableElement.style.position = 'relative';

	var width = (parseInt(scrollableElement.style.width) + scrollableElement.offsetWidth - scrollableElement.clientWidth);

	scrollableElement.style.width = width + 'px';

	this.timelogElement.style.width = parseInt(this.optionsElem.getBoundingClientRect().width) + 'px';

}

Timelog.prototype.onScrollTableFixedTop = function() {
	var scrollableElement = document.getElementById('scrolable-element');
	var tableColumnsCopy = document.getElementById('timelog_table');
	var thead = tableColumnsCopy.querySelector('thead').cloneNode(true);

	var tableColumns = tableColumnsCopy.cloneNode(false);
	var colgroup = tableColumnsCopy.querySelector('colgroup').cloneNode(true);
	tableColumns.appendChild(colgroup);

	scrollableElement.appendChild(tableColumns);
	tableColumns.appendChild(thead);

	tableColumns.style.position = 'absolute';
	tableColumns.style.top = '0px';
	tableColumns.style.zIndex = '399';
	tableColumns.style.background = 'white';
	tableColumns.setAttribute('id', 'table-scroll-top-absolute');
}

Timelog.prototype.onScrollTable = function(){
	var scrollableElement = document.getElementById('scrolable-element');
	var tableColumnsCopy = document.getElementById('timelog_table');

	var tableColumns = tableColumnsCopy.cloneNode(false);
	var thead = tableColumnsCopy.querySelector('thead').cloneNode();
	var thead_tr = tableColumnsCopy.querySelector('thead tr').cloneNode();
	
	var colgroup = document.createElement('colgroup');
	tableColumns.appendChild(colgroup);
	for(var i=1; i < 3; i++){
		var newCol = document.createElement('col');
		newCol.classList.add('col'+i);
		colgroup.appendChild(newCol);
	}

	tableColumns.appendChild(thead);
	thead.appendChild(thead_tr);

	var widthColumns = 0;
	var ths = tableColumnsCopy.querySelectorAll('thead tr th');

	for(var i=0; i < 2; i++) {
		thead_tr.appendChild(ths[i].cloneNode(true));
		widthColumns += ths[i].clientWidth;
	}

	tableColumns.style.width = widthColumns+'px';

	var tbody = tableColumnsCopy.querySelector('tbody').cloneNode(false);
	tableColumns.appendChild(tbody);
	
	var trs = tableColumnsCopy.querySelectorAll('tbody > tr');

	for(var i=0; i < trs.length; i++){
		var tbody_tr = trs[i].cloneNode(false);
		tbody.appendChild(tbody_tr);
	
		var tds = trs[i].querySelectorAll('td');

		for(var j=0; j < 2; j++){
			if(tds[j]){
				tbody_tr.appendChild(tds[j].cloneNode(true));
			}
		};
	};

	scrollableElement.appendChild(tableColumns);

	tableColumns.style.position = 'absolute';
	tableColumns.style.background = 'white';
	tableColumns.style.zIndex = '400';
	/*tableColumns.style.left = tableColumns.offsetLeft+'px';*/
	tableColumns.style.top = 0 + 'px';
	tableColumns.setAttribute('id', 'table-scroll-absolute');

}

Timelog.prototype.setCountColumns = function(){
	var columnsHead = 2;
	document.getElementById('count-columns').setAttribute('span', document.querySelectorAll('#timelog_table thead th').length - columnsHead);
}

Timelog.prototype.init = function(){
	var self = this;
	
	//-----------------------------design fidner
	function initial(){
		self.onScrollTable();
		self.tableScroll();
		self.setCountColumns();
		self.onScrollTableFixedTop();
		self.blockScrollElements('scrolable-element');
		self.initOptionsBtn();
		self.deleteBitrixFooter();
		self.fillToWindowScreen();
		
		
		
		fixedPlagin = new FixedPlagin(document.getElementById('timelog'), undefined);
		
		window.onscroll = function() {
			fixedPlagin.fixedScreen();
		};
		fixedPlagin.fixedScreen();

		self.fixBitrixVersions();
		
		
		//-----------------------------design fidner

		$("#from-datepicker").datepicker({dateFormat: 'yy-mm-dd'});
		$("#to-datepicker").datepicker({dateFormat: 'yy-mm-dd'});
		
		if(self.mode == 1){
			
			$('#range-select').show();
			$('#range-datepicker').hide();
			
			var dat = self.dateFrom.split('-');
			var year = dat[0];
			var month = dat[1];
			
			$(".years-select option[value='" + year + "']").attr("selected", "selected");
			$(".months-select option[value='" + parseInt(month) + "']").attr("selected", "selected");
		}else{
			$('#range-select').hide();
			$('#range-datepicker').show();
			$('#control').attr('checked', 'checked');
		}
		
		
		$('#from-datepicker').val(self.dateFrom);
		$('#to-datepicker').val(self.dateTo);
		
		$(".fio-select option[value='" + self.userId + "']").attr("selected", "selected");
		$(".group-select option[value='" + self.groupId + "']").attr("selected", "selected");
		
		self.initEvents();
		
		self.generatePagination();
	}
	
	window.addEventListener('resize', function(){
		setTimeout(function(){
			self.tableScroll();
			self.setCountColumns();
			self.fillToWindowScreen();
		}, 200);
		
	});
	var screnEvents = Array('webkitfullscreenchange', 'mozfullscreenchange', 'fullscreenchange', 'MSFullscreenChange');
	
	for(var i = 0; i < screnEvents.length; i++){
		document.addEventListener(screnEvents[i], function( event ) {
			setTimeout(function(){
				self.tableScroll();
				self.setCountColumns();
				self.fillToWindowScreen();
			}, 200);
		});
	}

	initial();
	
	return self;
};

Timelog.prototype.initEvents = function(){
	var self = this;
	
	$('#selected-filter-top').on('click', function(event){
		
		var groupId = $('select[name="groups"]').val();
		var userId = $('select[name="fio"]').val();
		var from = '';
		var to = '';

        var section = $('input[name="section"]').val(); // SECTION .................
		
		if(self.mode == 1){
			var year = $('select[name="year"]').val();
			var month = $('select[name="months"]').val();
			
			if(month < 10){
				month = '0' + month;
			}
			
			from = year + '-' + month + '-01';
		}else if(self.mode == 2){
			from = $('#from-datepicker').val();
			to = $('#to-datepicker').val();
		}
		
		window.location = self.generateUrlForFilter(window.location.href, from, to, groupId, userId, section);
		
		//window.location = self.generateUrlForFilter(window.location.href, year, month, groupId, userId);
	});
			
	$('#export-csv').on('click', function(event){	
		var url = self.generateUrlForFilter(self.pathForExportCsv, self.dateFrom, self.dateTo, self.groupId, self.userId);
		window.open(url, '_self');
	});
	
	$('#clearcsv').on('click', function(event){		
		$.ajax({
		  url: self.pathForClearCsv,
		  success: function(response){				
			  alert('Все файлы были удалены (' + response + ').');
		  },
		});
	});
	
	$('#groups').on('change', function(event){
		
		var groupId = $(this).val();
		
		$.ajax({
		  url: self.pathForGetUser,
		  data:{
			'group_id': groupId
		  },
		  success: function(response){		
			response = JSON.parse(response);
			self.renderUsersSelect(response);
		  },
		});
	});
	
	$('#control').on('change', function(event){
		event.preventDefault();
		
		var checked = $('#control').prop("checked");
		if(checked){
			$('#range-select').hide();
			$('#range-datepicker').show();
			
			self.mode = 2;
		}else{
			$('#range-select').show();
			$('#range-datepicker').hide();
			self.mode = 1;
		}		
	});
	
	return this;
};

Timelog.prototype.showRangeDateTimepicker = function(){
	
};

Timelog.prototype.hideRangeDateTimepicker = function(){
	
};

Timelog.prototype.renderUsersSelect = function(users){
	var self = this;
	
	var html = '<option value="all">Все</option>';
	for(var i = 0; i < users.length; i++){
		html += '<option value="' + users[i]['ID'] + '">' + users[i]['NAME'] + ' ' + users[i]['LAST_NAME'] + '</option>';
	}
	
	document.getElementById('users').innerHTML = html;
	
	return this;
};
/*
Timelog.prototype.generateUrlForFilter = function(url, year, month, groupId, userId, day){
	
	day = day || '';
	
	var gets = 'year=' + year + '&month=' + month;
	
	if(groupId != null && groupId != 'all' && groupId != '0'){
		gets += '&group=' + groupId;
	}
	
	if(userId != null && userId != 'all' && userId != '0'){
		gets += '&user=' + userId;
	}
	
	var getsPos = url.indexOf('?');
	if(getsPos != -1){
		url = url.substring(0, getsPos);
	}
	
	url = url +'?' + gets;
	return url;
};
*/

Timelog.prototype.generateUrlForFilter = function(url, from, to, groupId, userId, section){
	var gets = 'from=' + from; // + '&to=' + to;
	
	if(to != null && to != undefined && to != ''){
		gets += '&to=' + to;
	}
	
	if(groupId != null && groupId != 'all' && groupId != '0'){
		gets += '&group=' + groupId;
	}
	
	if(userId != null && userId != 'all' && userId != '0'){
		gets += '&user=' + userId;
	}

    if(section && section != '0') gets += '&section=' + section;

	var getsPos = url.indexOf('?');
	if(getsPos != -1){
		url = url.substring(0, getsPos);
	}
	
	url = url +'?' + gets;
	return url;
};

Timelog.prototype.generatePagination = function(){
	var self = this;
	/*
	var year = $('select[name="year"]').val();
	var month = $('select[name="months"]').val();
	var groupId = $('select[name="groups"]').val();
	var userId = $('select[name="fio"]').val();
	
	var url = self.generateUrlForFilter(window.location.href, year, month, groupId, userId);
	*/
	/*var groupId = $('select[name="groups"]').val();
	var userId = $('select[name="fio"]').val();
	var from = '';
	var to = '';
		
	if(self.mode == 1){
		var year = $('select[name="year"]').val();
		var month = $('select[name="months"]').val();
		
		if(month < 10){
			month = '0' + month;
		}
			
		from = year + '-' + month + '-01';
	}else if(self.mode == 2){
		from = $('#from-datepicker').val();
		to = $('#to-datepicker').val();
	}*/
		
	var url = self.generateUrlForFilter(window.location.href, self.dateFrom, self.dateTo, self.groupId, self.userId);
		
	$('.href-pagination').each(function() {
		var page = $(this).data('page');
		$(this).attr('href', url + '&page=' + page);
	});
	
	return this;
};

Timelog.prototype.blockScrollElements = function(elementId)
{
	var scrollable = undefined;
	var self = this;
		
	scrollable = document.getElementById(elementId);


	scrollable.addEventListener('wheel', function(event) {
		
		var deltaY = event.deltaY;
		var contentHeight = this.scrollHeight;
		var visibleHeight = this.offsetHeight;
		var scrollTop = this.scrollTop;
		
		if (deltaY < 0){

			if(isChild(event.target, 'table-scroll-absolute', 'id', scrollable)){
				scrollable.scrollTop -= self.rectangleSide;
			}else{
				scrollable.scrollLeft -= self.rectangleSide;
			}
			
			event.preventDefault();
		}else{

			if(isChild(event.target, 'table-scroll-absolute', 'id', scrollable)){
				scrollable.scrollTop += self.rectangleSide;
			}else{
				scrollable.scrollLeft += self.rectangleSide;
			}
			
			event.preventDefault();
		}
			
	});
	
	var scrollLeft = true;
	var tableScrollAbsolute = document.getElementById('table-scroll-absolute');
	var tableScrollTopAbsolute = document.getElementById('table-scroll-top-absolute');
	scrollable.addEventListener('scroll', function(event){

		scrollable.scrollLeft -= scrollable.scrollLeft % self.rectangleSide;
		tableScrollAbsolute.style.left = scrollable.scrollLeft+'px';
		self.optionsElem.style.left = scrollable.scrollLeft+'px';
		tableScrollTopAbsolute.style.top = scrollable.scrollTop+'px';

	});
}

function isChild(node, value, attr, endElement){
	
	if(!node || node === endElement){
		return false;
	}
	
	if(attr) {
		if(node.getAttribute(attr) == value) {
			return true;
		}else {
			return isChild(node.parentNode, value, attr, endElement);
		}
	}else {
		if(node.classList.contains(value)) {
			return true;
		}else {
			return isChild(node.parentNode, value, attr, endElement);
		}
	}
	
}

//-------------------------------------FIXED SCRIPT----------------------------------------

function FixedPlagin(fixedElem, btnFullScreen) {

    this.fixedElem = fixedElem;
    this.offsetTop = document.querySelector('.bx-layout-table tbody tr').getBoundingClientRect().height + 
						 document.querySelector('.bx-layout-inner-inner-top-row').getBoundingClientRect().height;
	this.copyrightBitrix = document.getElementById('copyright');
    this.btnFullScreen = btnFullScreen;

    this.isFixed = false;
	this.isFixedFullScreen = false;
}

FixedPlagin.prototype.fixedScreen = function() {

		var Top = window.pageYOffset;
		
		var fixedWidth = this.fixedElem.clientWidth;

		if( window.pageYOffset > this.offsetTop){
			this.fixedElem.style.top = '0px';
			this.copyrightBitrix.style.display = 'none';
		}else{
			this.fixedElem.style.top = this.offsetTop - window.pageYOffset + 'px';
			this.copyrightBitrix.style.display = 'block';
		}

		this.fixedElem.style.position = 'fixed';
		this.fixedElem.style.width = fixedWidth +'px';
}

FixedPlagin.prototype.fullScreen = function() {
	
    var self = this;

    btnFullScreen.addEventListener('click', function() {

		var Top = window.pageYOffset;
		var fixed = self.fixedElem.getBoundingClientRect().top;
		var fixedWidth = self.fixedElem.clientWidth;
		var fixedStop = offsetTop;
			
		if(!self.isFixed || self.isFixedFullScreen) {
			self.isFixedFullScreen = false;
			if(!self.isFixed) {
				self.isFixed = true;
			}
			document.body.style.overflowY = 'hidden';
			self.fixedElem.style.position = 'fixed';
			self.fixedElem.style.top = '0px';
			self.fixedElem.style.left = '0px';
			self.fixedElem.style.width = '100%';
			self.fixedElem.style.height = '100%';
			self.fixedElem.style.zIndex = '1000';
			self.btnFullScreen.classList.add('active');
			return;
		}
			
		if(self.isFixed) {			
		
			document.body.style.overflowY = 'scroll';
			self.fixedElem.style.left = '';
			self.isFixed = false;
			self.fixedElem.style.position = 'relative';
			self.fixedElem.style.width = '100%';
			self.fixedElem.style.zIndex = '0';
			btnFullScreen.classList.remove('active');

			if(window.pageYOffset > self.offsetTop) {
				self.fixedScreen();
			}
			
			return;
		}
	});
}
//------------------------------------------------------------

Timelog.prototype.fixBitrixVersions = function(){
	document.getElementById('workarea').style.height = document.getElementById('timelog').clientHeight+'px';
}
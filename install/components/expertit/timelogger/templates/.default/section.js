function SetMenu () {
    this.sections = Object.create(null);
}
SetMenu.prototype.init = function (sections) {
    //debugger;
    this.sections = sections;
    this.level = 1;
    this.selectInput = document.getElementById('sectionInput');
    select = this.getSelect(this.level, this.sections);
    var parent = document.getElementById("selectMarker");
    if (!parent) return;
    parent.after(select);
    this.setChange(this.level, select.sections, select.value, select);
}
SetMenu.prototype.getSelect = function (level, sections) {
    var select = document.createElement('select');
    select.className = "group-select form-control";
    select.innerHTML = this.getOptionList(level, sections);
    select.level = level;
    select.sections = sections;
    select.dataset.level = level;
    select.onchange = this.changeSelect.bind(this);
    return select;
}
SetMenu.prototype.getOptionList = function (level, sections) {
    var html = (level == 1) ? '' : '<option value="0"> все </option>';
    for (key in sections) html += '<option value="'+key+'">'+sections[key].NAME+'</option>';
    return html;
}
SetMenu.prototype.changeSelect = function (event) {
    this.setChange(event.target.level, event.target.sections, event.target.value, event.target);
}
SetMenu.prototype.setChange = function (level, sections, sectionId, parent) {
    this.deleteSelect(level);
    this.selectInput.value = this.setSelectInput();
    if (!sectionId || !sections[sectionId].CHILD) return;
    if (!Object.keys(sections[sectionId].CHILD).length) return;
    this.level = ++level;
    var select = this.getSelect(this.level, sections[sectionId].CHILD);
    parent.after(select);
}
SetMenu.prototype.deleteSelect = function (level) {
    var selects = document.querySelectorAll('[data-level]');
    if (!selects || !selects.length) return;
    for (var i = 0; i < selects.length; i++){
        if (selects[i].dataset && selects[i].dataset.level && selects[i].dataset.level > level) selects[i].remove();
    }
}
SetMenu.prototype.setSelectInput = function () {
    var selects = document.querySelectorAll('[data-level]');
    if (!selects || !selects.length) return 0;
    var value = 0;
    var level = 0;
    for (var i = 0; i < selects.length; i++) {
        if (selects[i].level > level && selects[i].value != 0) {
            value = selects[i].value;
            level = selects[i].level;
        }
    }
    return value;
}
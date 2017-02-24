var CMTable = {
	width: 470,
	cellspacing: 10,
	lineheight: 15,
	minwidth: 20,
	maxcols: 0,
	divwidth: 0,
	columndiv: null,
	tablediv: null,
	columns: [],
	rows: [],
	dividers: [],
	dividerorigin: 0,
	columnid: 0,
	layout: 'none',
	drawn: false,
	init: function(width,cellspacing,data,rowalignments,colalignments,coltypes,colwidths,columnid,layout){
		if(width) this.width = width;
		if(cellspacing) this.cellspacing = cellspacing;
		this.maxcols = Math.floor(this.width / (this.minwidth+this.cellspacing));
		this.divwidth = this.width + this.maxcols + 41; //maxcols+1 for the divide lines; 20 for the row buttons; 20 for the scrollbar
		this.columndiv = document.getElementById('columns');
		this.tablediv = document.getElementById('table');
		this.columndiv.style.width = this.divwidth+'px';
		this.tablediv.style.width = this.divwidth+'px';
		this.columnid = columnid;
		this.layout = layout;
		
		if(data){
			for(var colid in data[1]){
				this.columns.push(new CMColumn(this,colalignments[colid],coltypes[colid],colwidths[colid]));
				if(this.columns.length >= this.maxcols) break;
			}
			for(var rowid in data){
				this.rows.push(new CMRow(this,rowalignments[rowid],data[rowid]));
			}
		}
	},
	insertColumn: function(index){ 
		this.setTextData();
		this.columns.splice(index,0,new CMColumn(this,'left','text', this.minwidth));
		for(var i=0;i<this.rows.length;i++){
			this.rows[i].cells.splice(index,0,'');
		}
		this.draw();
	},
	deleteColumn: function(index){
		if(confirm('Are you sure you want to delete this column? All data in all cells will be lost forever.')){
			this.setTextData();
			this.columns.splice(index,1);
			for(var i=0;i<this.rows.length;i++){
				this.rows[i].cells.splice(index,1);
			}
			this.draw();
		}
	},
	insertRow: function(index){ 
		this.setTextData();
		this.rows.splice(index,0,new CMRow(this,'middle'));
		this.draw();
	},
	deleteRow: function(index){
		if(confirm('Are you sure you want to delete this row? All data in all cells will be lost forever.')){
			this.setTextData();
			this.rows.splice(index,1);
			this.draw();
		}
	},
	
	draw: function(){
		this.calculateColumnWidths();
		
		// build the sizing bar
		var bar = document.getElementById('widths');
		bar.style.width = (this.width+this.columns.length-1)+'px';
		bar.innerHTML = '';
		this.dividers = [];
		for(var i=1;i<this.columns.length;i++) this.dividers.push(new CMDivider(this,i));
		
		// build the column table, which is outside the scrolling div
		this.columndiv.innerHTML = '';
		var table = document.createElement('table');
		table.cellSpacing = '0px';
		table.cellPadding = '0px';
		this.columndiv.appendChild(table);
		
		var toprow = table.insertRow(0);
		var firstcell = toprow.insertCell(0);
		firstcell.style.width = '20px';
		// create a cell for each column
		for(var colid=0;colid<this.columns.length;colid++){
			var colcell = toprow.insertCell(colid+1);
			this.columns[colid].actioncell = colcell;
			colcell.column = this.columns[colid];
			colcell.className = 'colcell';
			colcell.style.width = (this.columns[colid].width+this.cellspacing+1) + 'px';
			
			var container = document.createElement('div');
			container.className = 'colcontainer';
			colcell.appendChild(container);
			
			var button = document.createElement('div');
			button.id = 'colbutton_'+colid;
			button.className = 'colbutton';
			button.onclick = clickColumnButton;
			container.appendChild(button);
			
			var options = document.createElement('div');
			options.id = 'coloptions_'+colid;
			options.className = 'coloptions';
			options.style.display = 'none';
			
			if(this.columns.length < this.maxcols) options.innerHTML += '<a href="javascript:CMTable.insertColumn('+(colid+1)+');">Add column right</a><a href="javascript:CMTable.insertColumn('+(colid)+');">Add column left</a>';
			if(this.columns.length > 1) options.innerHTML += '<a href="javascript:CMTable.deleteColumn('+colid+');">delete column</a>';
			options.innerHTML += '<div style="height: 20px;width: 70px;margin: 5px 0px;"><span class="tablestyle colalign_left'+(this.columns[colid].alignment=='left'?' selected':'')+'" id="colalign_left_'+colid+'" onclick="CMTable.columns['+colid+'].setColumnAlign(\'left\');" title="Left aligned">&nbsp;</span><span class="tablestyle colalign_center'+(this.columns[colid].alignment=='center'?' selected':'')+'" id="colalign_center_'+colid+'" onclick="CMTable.columns['+colid+'].setColumnAlign(\'center\');" title="Center aligned">&nbsp;</span><span class="tablestyle colalign_right'+(this.columns[colid].alignment=='right'?' selected':'')+'" id="colalign_right_'+colid+'" onclick="CMTable.columns['+colid+'].setColumnAlign(\'right\');" title="Right aligned">&nbsp;</span></div>';
			options.innerHTML += '<div style="height: 20px;width: 70px;margin: 5px 0px;"><span class="tablestyle coltype_text'+(this.columns[colid].type=='text'?' selected':'')+'" id="coltype_text_'+colid+'" onclick="CMTable.columns['+colid+'].setColumnType(\'text\');" title="Text">&nbsp;</span><span class="tablestyle coltype_html'+(this.columns[colid].type=='html'?' selected':'')+'" id="coltype_html_'+colid+'" onclick="CMTable.columns['+colid+'].setColumnType(\'html\');" title="HTML">&nbsp;</span><span class="tablestyle coltype_icon'+(this.columns[colid].type=='icon'?' selected':'')+'" id="coltype_icon_'+colid+'" onclick="CMTable.columns['+colid+'].setColumnType(\'icon\');" title="Icons">&nbsp;</span></div>';
			container.appendChild(options);
		}
		
		// create a table to hold the actual data cells
		this.tablediv.innerHTML = '';
		this.table = document.createElement('table');
		this.table.className = 'datatable';
		this.table.cellSpacing = '0px';
		this.table.cellPadding = '0px';
		this.tablediv.appendChild(this.table);
		
		// create each row
		for(var rowid=0;rowid<this.rows.length;rowid++){
			var objrow = this.rows[rowid];
			var datarow = this.table.insertRow(-1);
			
			// options cell
			var optioncell = datarow.insertCell(0);
			this.rows[rowid].actioncell = optioncell;
			optioncell.row = this.rows[rowid];
			optioncell.className = 'rowcell';
			
			var container = document.createElement('div');
			container.className = 'colcontainer';
			optioncell.appendChild(container);
			
			var button = document.createElement('div');
			button.id = 'rowbutton_'+rowid;
			button.className = 'rowbutton';
			button.onclick = clickRowButton;
			container.appendChild(button);
			
			var options = document.createElement('div');
			options.id = 'rowoptions_'+rowid;
			options.className = 'rowoptions';
			
			options.innerHTML = '<a href="javascript:CMTable.insertRow('+rowid+');">Add row above</a><a href="javascript:CMTable.insertRow('+(rowid+1)+');">Add row below</a><a href="javascript:CMTable.deleteRow('+rowid+');">delete row</a>';
			options.innerHTML += '<div style="height: 20px;width: 70px;margin: 5px 0px;"><span class="tablestyle rowalign_top'+(this.rows[rowid].alignment=='top'?' selected':'')+'" id="rowalign_top_'+rowid+'" onclick="CMTable.rows['+rowid+'].setRowAlign(\'top\');" title="Top aligned">&nbsp;</span><span class="tablestyle rowalign_middle'+(this.rows[rowid].alignment=='middle'?' selected':'')+'" id="rowalign_middle_'+rowid+'" onclick="CMTable.rows['+rowid+'].setRowAlign(\'middle\');" title="Middle aligned">&nbsp;</span><span class="tablestyle rowalign_bottom'+(this.rows[rowid].alignment=='bottom'?' selected':'')+'" id="rowalign_bottom_'+rowid+'" onclick="CMTable.rows['+rowid+'].setRowAlign(\'bottom\');" title="Bottom aligned">&nbsp;</span></div>';
			container.appendChild(options);
			
			// add each cell
			for(var cellid=0;cellid<objrow.cells.length;cellid++){
				var datacell = datarow.insertCell(-1);
				var objcol = this.columns[cellid];
				if(((this.layout=='header'||this.layout=='both')&&rowid==0)||((this.layout=='lead'||this.layout=='both')&&cellid==0)){
					datacell.className = 'datacell cell_lead';
					datacell.onclick = function(){this.firstChild.focus();};
					datacell.style.verticalAlign = objrow.alignment;
					datacell.innerHTML = '<textarea id="datacell_'+rowid+'_'+cellid+'" class="leadtextbox" style="width: '+(objcol.width)+'px;height: '+this.calculateTextboxHeight(objcol.width-this.cellspacing,objrow.cells[cellid])+'px;text-align:'+objcol.alignment+';" onkeyup="resizeTextArea(this,'+cellid+');">' + objrow.cells[cellid] + '</textarea>';
				}else{				
					switch(objcol.type){
						case 'icon':
							datacell.className = 'datacell cell_icon';
							datacell.style.verticalAlign = objrow.alignment;
							eval('datacell.onclick = function(){ clickIconCell('+rowid+','+cellid+'); }');
							var html = '<div style="width: '+(objcol.width)+'px;text-align:'+objcol.alignment+';margin: '+(this.cellspacing/2)+'px" class="cell_icon_div">';
							if(objrow.cells[cellid].length){
								html += '<img src="icons/'+objrow.cells[cellid]+'" align="bottom" />';
								datacell.className = 'datacell cell_icon_in';
							}
							html += '</div>';
							datacell.innerHTML = html;
							break;
						case 'html':
							datacell.className = 'datacell cell_html';
							datacell.style.verticalAlign = objrow.alignment;
							eval('datacell.onclick = function(){ clickHTMLCell('+rowid+','+cellid+'); }');
							datacell.style.width = (objcol.width+this.cellspacing) + 'px';
							if(objrow.cells[cellid].length){
								datacell.className = 'datacell cell_html_in';
								// build the iframe to hold the HTML
								var iframe = document.createElement('iframe');
								iframe.name = 'htmlcell_'+rowid+'_'+cellid;
								iframe.id = iframe.name;
								iframe.style.width = (objcol.width)+'px';
								iframe.style.textAlign = objcol.alignment;
								iframe.style.margin = (this.cellspacing/2)+'px';
								iframe.className = 'cell_html_iframe';
								iframe.style.border = 0;
								iframe.frameBorder = 0;
								iframe.align = 'top';
								iframe.scrolling = "no";
								eval('iframe.onclick = function(){ clickHTMLCell('+rowid+','+cellid+'); }');
								datacell.appendChild(iframe);
								// build a form to post the content to the frame. This allows for very long content, 
								// and still allows the content to be added on the server side.
								var form = document.createElement('form');
								form.method = 'post';
								form.action = 'CMTable.html.php';
								form.target = iframe.name;
								var input_content = document.createElement('input');
								input_content.name = 'content';
								input_content.value = objrow.cells[cellid];
								input_content.type = 'hidden';
								form.appendChild(input_content);
								var input_rowid = document.createElement('input');
								input_rowid.name = 'rowid';
								input_rowid.value = rowid;
								input_rowid.type = 'hidden';
								form.appendChild(input_rowid);
								var input_colid = document.createElement('input');
								input_colid.name = 'colid';
								input_colid.value = cellid;
								input_colid.type = 'hidden';
								form.appendChild(input_colid);
								var input_columnid = document.createElement('input');
								input_columnid.name = 'columnid';
								input_columnid.value = this.columnid;
								input_columnid.type = 'hidden';
								form.appendChild(input_columnid);
								var input_align = document.createElement('input');
								input_align.name = 'align';
								input_align.value = CMTable.columns[cellid].alignment;
								input_align.type = 'hidden';
								form.appendChild(input_align);
								
								form.style.position = 'absolute';
								form.style.left = '-1000px';
								document.body.appendChild(form);
								form.submit();
								document.body.removeChild(form);
							}
							break;
						default:
							datacell.className = 'datacell cell_text';
							datacell.onclick = function(){this.firstChild.focus();};
							datacell.style.verticalAlign = objrow.alignment;
							datacell.innerHTML = '<textarea id="datacell_'+rowid+'_'+cellid+'" class="celltextbox" style="width: '+(objcol.width)+'px;height: '+this.calculateTextboxHeight(objcol.width-this.cellspacing,objrow.cells[cellid])+'px;text-align:'+objcol.alignment+';" onkeyup="resizeTextArea(this,'+cellid+');">' + objrow.cells[cellid] + '</textarea>';
							break;
					}
				}
			}
		}
		// after we've drawn everything, get the origin of the width divider sliders.
		this.dividerorigin = getAbsLeft(bar);
		this.drawn = true;
	},
	
	calculateColumnWidths: function(){
		var width = 0;
		for(var colid in this.columns) width += this.columns[colid].width;
		var totalwidth = this.width - (this.columns.length * this.cellspacing);
		if(totalwidth > width){
			var remaining = totalwidth - width;
			var share = Math.floor(remaining/this.columns.length);
			var left = remaining - (this.columns.length*share);
			for(var colid in this.columns) this.columns[colid].width += share + (colid<left?1:0);
		}else if(totalwidth < width){
			var excess = width - totalwidth;
			var share = Math.floor(excess/this.columns.length);
			var left = excess - (this.columns.length*share);
			for(var colid in this.columns){
				if(share==0){
					if(left>0 && this.columns[colid].width > this.minwidth){
						this.columns[colid].width -=1;
						left -= 1;
					}
				}else{
					if(this.columns[colid].width - share > this.minwidth){
						this.columns[colid].width -= share;
					}else{
						var part = this.columns[colid].width - this.minwidth;
						this.columns[colid].width -= part;
						left += share-part;
					}
				}
			}
			if(left>0) this.calculateColumnWidths();
		}
	},
	
	calculateTextboxHeight: function(columnwidth,content){
		var lines = content.split("\n");
		var height = 0;
		for(var i in lines){
			height += Math.max(Math.ceil((lines[i].length*7) / columnwidth),1) * this.lineheight;
		}
		return height;
	},
	
	setTextData: function(){
		for(var i=0;i<this.rows.length;i++){
			for(var j=0;j<this.rows[i].cells.length;j++){
				if(this.columns[j].type=='text' || ((this.layout=='header'||this.layout=='both')&&i==0)||((this.layout=='lead'||this.layout=='both')&&j==0)){
					this.rows[i].cells[j] = document.getElementById('datacell_'+i+'_'+j).value;
				}
			}
		}
	},
	
	selectHTML: function (){
		var pop = parent.PopupManager.popups.CMTable_html;
		var doc = pop.GetOwnerDocument();
		var rowid = doc.getElementById('rowid').value;
		var colid = doc.getElementById('colid').value;
		var win = doc.defaultView?doc.defaultView:doc.parentWindow;
		var content = win.tinyMCE.getInstanceById('content').getContent();
		CMTable.rows[rowid].cells[colid] = content;
		parent.PopupManager.hidePopup('CMTable_html');
		parent.PopupManager.hideLoading();
		CMTable.draw();
	},
	
	buildDataInputs : function(){
		this.setTextData();
		var inp_data = document.getElementById('data');
		var inp_rowstyles = document.getElementById('rowstyles');
		var inp_columnstyles = document.getElementById('columnstyles');
		var inp_columntypes = document.getElementById('columntypes');
		var inp_columnwidths = document.getElementById('columnwidths');
		
		var data = [];
		var rowstyles = [];
		var columnstyles = [];
		var columntypes = [];
		var columnwidths = [];
		for(var rowid=0;rowid<this.rows.length;rowid++){
			data[rowid] = [];
			for(var cellid=0;cellid<this.rows[rowid].cells.length;cellid++){
				data[rowid][cellid] = this.rows[rowid].cells[cellid];
			}
			rowstyles[rowid] = this.rows[rowid].alignment;
		}
		for(var colid=0;colid<this.columns.length;colid++){
			columnstyles[colid] = this.columns[colid].alignment;
			columntypes[colid] = this.columns[colid].type;
			columnwidths[colid] = this.columns[colid].width;
		}
		inp_data.value = JSON.stringify(data);
		inp_rowstyles.value = JSON.stringify(rowstyles);
		inp_columnstyles.value = JSON.stringify(columnstyles);
		inp_columntypes.value = JSON.stringify(columntypes);
		inp_columnwidths.value = JSON.stringify(columnwidths);
	}
}
parent.CMTable = CMTable;

function CMRow(table,alignment,data){
	this.table = table;
	this.alignment = alignment?alignment:'middle';
	this.cells = [];
	if(data){
		for(key in data){
			this.cells.push(data[key]);
			if(this.cells.length >= this.table.maxcols) break;
		}
	}
	while(this.cells.length<this.table.columns.length) this.cells.push('');
	this.actioncell = null;
	this.setRowAlign = function(value){
		if(value!=this.alignment){
			this.table.setTextData();
			var oldbutton = document.getElementById('rowalign_'+this.alignment+'_'+this.getRowId());
			var newbutton = document.getElementById('rowalign_'+value+'_'+this.getRowId());
			oldbutton.className = oldbutton.className.replace(" selected","");
			newbutton.className += ' selected';
			this.alignment = value;
			this.table.draw();
		}
	}
	this.getRowId = function(){
		for(var i=0;i<this.table.rows.length;i++) if(this.table.rows[i]==this) return i;
		return -1;
	}
}

function CMColumn(table,alignment,type,width){
	this.table = table;
	this.alignment = alignment?alignment:'left';
	this.type = type?type:'text';
	this.width = width | 0;
	this.actioncell = null;
	this.setColumnAlign = function(value){
		if(value!=this.alignment){
			this.table.setTextData();
			var oldbutton = document.getElementById('colalign_'+this.alignment+'_'+this.getColumnId());
			var newbutton = document.getElementById('colalign_'+value+'_'+this.getColumnId());
			oldbutton.className = oldbutton.className.replace(" selected","");
			newbutton.className += ' selected';
			this.alignment = value;
			this.table.draw();
		}
	}
	this.setColumnType = function(value){
		if(value!=this.type){
			this.table.setTextData();
			var oldbutton = document.getElementById('coltype_'+this.type+'_'+this.getColumnId());
			var newbutton = document.getElementById('coltype_'+value+'_'+this.getColumnId());
			oldbutton.className = oldbutton.className.replace(" selected","");
			newbutton.className += ' selected';
			this.type = value;
			this.table.draw();
		}
	}
	this.getColumnId = function(){
		for(var i=0;i<this.table.columns.length;i++) if(this.table.columns[i]==this) return i;
		return -1;
	}
}

function CMDivider(table,index){
	this.table = table;
	this.index = index;
	this.previouscolumn = table.columns[index-1];
	this.nextcolumn = table.columns[index];
	this.left = this.min = -1;
	for(var i=0;i<index;i++){
		this.left += table.columns[i].width + table.cellspacing + 1;
		if(i<index-1) this.min += table.columns[i].width + table.cellspacing + 1;
	}
	this.min += table.minwidth + table.cellspacing;
	this.max = this.left + this.nextcolumn.width - table.minwidth - table.cellspacing;
	this.movepos = this.left;
	this.div = document.createElement('img');
	this.div.src = 'widthmark.png';
	this.div.style.width = '17px';
	this.div.style.height = '14px';
	this.div.style.position = 'absolute';
	this.div.style.top = '0px';
	this.div.style.left = (this.left-8)+'px';
	this.div.style.cursor = 'move';
	this.div.divider = this;
	this.div.ondragstart = function(){return false;};
	this.div.onmousedown = function(e){
		CMTable.setTextData();
		e = e || this.document.parentWindow.event;
		var target = e.target != null ? e.target : e.srcElement;
		table.currentDragDivider = target.divider;
		PopupManager.showInvisible();
		attachEventHandler(document,'mousemove',target.divider.mousemove);
		attachEventHandler(document,'mouseup',target.divider.mouseup);
	};
	this.mousemove = function(e){
		var pos = new MousePosition(e);
		var newleft = pos.posx - CMTable.dividerorigin;
		if(newleft >= CMTable.currentDragDivider.min && newleft <= CMTable.currentDragDivider.max){
			CMTable.currentDragDivider.movepos = newleft;
			CMTable.currentDragDivider.div.style.left = (newleft-8)+'px';
		}
	};
	this.mouseup = function(){
		PopupManager.hideInvisible();
		CMTable.currentDragDivider.previouscolumn.width += CMTable.currentDragDivider.movepos-CMTable.currentDragDivider.left;
		CMTable.currentDragDivider.nextcolumn.width -= CMTable.currentDragDivider.movepos-CMTable.currentDragDivider.left;
		releaseEventHandler(document,'mousemove',CMTable.currentDragDivider.mousemove);
		releaseEventHandler(document,'mouseup',CMTable.currentDragDivider.mouseup);
		CMTable.draw();
	};
	document.getElementById('widths').appendChild(this.div);
}

function setLayout(value){
	var field = document.getElementById('headerlead');
	var oldvalue = field.value;
	if(value!=oldvalue){
		var oldbutton = document.getElementById('headerlead_'+oldvalue);
		var newbutton = document.getElementById('headerlead_'+value);
		oldbutton.className = 'headerlead';
		newbutton.className = 'headerlead selected';
		field.value = value;
		CMTable.layout = value;
	}
	if(CMTable.drawn) CMTable.draw();
}
function clickColumnButton(){
	var id = this.id.substring(10,this.id.length);
	var coloptions = document.getElementById('coloptions_'+id);
	if(coloptions.style.display=='block'){
		coloptions.style.display = 'none';
	}else{
		for(var i=0;i<CMTable.rows.length;i++){
			var opt = document.getElementById('rowoptions_'+i);
			if(opt) opt.style.display = 'none';
		}
		for(var i=0;i<CMTable.columns.length;i++){
			if(i==id){
				var left = 0;
				for(j=0;j<=id;j++) left += CMTable.columns[j].width + CMTable.cellspacing;
				if(left<132) coloptions.style.left = '0px';
				coloptions.style.display = 'block';
			}else{
				var opt = document.getElementById('coloptions_'+i);
				if(opt) opt.style.display = 'none';
			}
		}
	}
}
function clickRowButton(){
	var id = this.id.substring(10,this.id.length);
	var rowoptions = document.getElementById('rowoptions_'+id);
	if(rowoptions.style.display=='block'){
		rowoptions.style.display = 'none';
	}else{
		for(var i=0;i<CMTable.columns.length;i++){
			var opt = document.getElementById('coloptions_'+i);
			if(opt) opt.style.display = 'none';
		}
		for(var i=0;i<CMTable.rows.length;i++){
			if(i==id) rowoptions.style.display = 'block';
			else{
				var opt = document.getElementById('rowoptions_'+i);
				if(opt) opt.style.display = 'none';
			}
		}
	}
}
function clickIconCell(rowid,colid){
	CMTable.setTextData();
	var div = document.getElementById('icon_selector');
	var pop = PopupManager.createOrFetchPopup('icons','Icons',0,0,'div',div,'disabled');
	PopupManager.showDisabled();
	pop.Show({icon_selector_rowid:rowid,icon_selector_colid:colid});
}
function selectIcon(icon){
	var rowid = document.getElementById('icon_selector_rowid').value;
	var colid = document.getElementById('icon_selector_colid').value;
	CMTable.rows[rowid].cells[colid] = icon;
	PopupManager.hidePopup('icons');
	PopupManager.hideDisabled();
	CMTable.draw();
}
function clickHTMLCell(rowid,colid){
	CMTable.setTextData();
	var pop = parent.PopupManager.createOrFetchPopup('CMTable_html','HTML Cell Editor',0,0,'iframe','../content/CMTable/CMTable.tiny.php','loading');
	parent.PopupManager.showLoading();
	pop.ShowRefresh({rowid:rowid,colid:colid,width:CMTable.columns[colid].width});
}
function HTMLFrameLoaded(rowid,colid){
	var clone;
	var iframe = document.getElementById('htmlcell_'+rowid+'_'+colid);
	var doc = iframe.contentDocument?iframe.contentDocument:iframe.contentWindow.document;
	var site = doc.getElementById('site');
	var height = isie?site.scrollHeight:site.offsetHeight;
	// If the parent window has not shown yet, the height will be reported as 0. Wait a bit, then try again.
	if(height==0) setTimeout('HTMLFrameLoaded('+rowid+','+colid+')',100); 
	else iframe.style.height = height+'px';
}
function resizeTextArea(box, colid){
	box.style.height = CMTable.calculateTextboxHeight(CMTable.columns[colid].width-CMTable.cellspacing,box.value) + 'px';
}
/**
* Draw graphs for WikiPathways statistics using
* the google visualization API
*/

// Load the Visualization API
google.load('visualization', '1', {'packages':['corechart', 'table']});
google.load('jquery', '1.5.1');

// Set a callback to run when the Google Visualization API is loaded.
drawVisualizations = function() {
	var graphBuilder = new GraphBuilder();
	graphBuilder.drawVisualizations();
}
google.setOnLoadCallback(drawVisualizations);

function CheckDone(total, callback) {
	this.cb = callback;
	this.goal = total;
	this.done = 0;
}
CheckDone.prototype.isDone = function() {
	this.done++;
	if(this.done >= this.goal){
		this.cb();
	}
}

function GraphBuilder() { }

GraphBuilder.dataPath = '';
if(typeof(wgScriptPath) != 'undefined') { //Loaded from MW page
	GraphBuilder.dataPath = wgServer + wgScriptPath + '/wpi/statistics/';
	GraphBuilder.errorImg = wgServer + wgScriptPath + '/skins/common/images/cancel.gif';
}

GraphBuilder.prototype.drawVisualizations = function() {
	var that = this;
	
	var req = this.addSummary();
	req.then(function() { req = that.drawPathwayCounts(); });
	req.then(function() { req = that.drawUserCounts(); });
	req.then(function() { req = that.drawCollectionCounts(); });
	req.then(function() { req = that.drawEditCounts(); });
	req.then(function() { req = that.drawUserFrequencies(); });
	req.then(function() { req = that.drawLitFrequencies(); });
	req.then(function() { req = that.drawXrefFrequencies(); });
	req.then(function() { req = that.drawIntFrequencies(); });
	req.then(function() { req = that.drawViewFrequencies(); });
	req.then(function() { req = that.drawEditFrequencies(); });
	req.then(function() { req = that.drawWebserviceCounts(); });
	req.then(function() { req = that.drawUniquePerSpecies(); });
}

/**
 * Some browsers load interactive charts very slow. Determine whether not to draw
 * or fallback on static charts in these cases.
 */
GraphBuilder.prototype.slowBrowser = function() {
	if($.browser.msie && $.browser.version.slice(0,1) <= 8) {
		return true;
	}
	return false;
}

GraphBuilder.prototype.browserNotice = function(container) {
	$(container).html("Unable to draw graph in this browser. Please update your browser to the newest version.");
	$(container).prepend(
		$('<img>').attr('src', GraphBuilder.errorImg)
	);
	$(container).width('auto');
	$(container).height('auto');
}

GraphBuilder.prototype.addSummary = function() {
	var container = document.getElementById('summary');
	if(!container) {
		var dfd = $.Deferred();
		dfd.resolve();
		return dfd.promise();
	}
	
	return $.get(GraphBuilder.dataPath + 'summary.txt', function(data) {
		$(container).html(data);
	});
}

GraphBuilder.prototype.drawUserCounts = function() {
	var that = this;

	var dfd = $.Deferred();
	var check = new CheckDone(2, dfd.resolve);
	
	//Draw 2 graphs: cumulative users and active users per month
	var regContainer = document.getElementById('user_registered_graph');
	var actContainer = document.getElementById('user_active_graph');
	if(!regContainer && !actContainer) {
		dfd.resolve();
		return dfd.promise();
	}
	$.get(GraphBuilder.dataPath + 'userCounts.txt', function(data) {
		try {

			var regVis = new google.visualization.AreaChart(regContainer);
			var actVis = new google.visualization.ColumnChart(actContainer);
		
			var table = that.parseText(data);
			var formatter = new google.visualization.DateFormat(
				{pattern: 'MMMM yyyy'}
			);
			formatter.format(table, 0);
	
			var regView = new google.visualization.DataView(table);
			regView.setColumns([0, 2]);
			var actView = new google.visualization.DataView(table);
			actView.setColumns([0, 3]);
	
			google.visualization.events.addListener(regVis, 'ready', function() {
				check.isDone();
			});
			google.visualization.events.addListener(actVis, 'ready', function() { 
				check.isDone();
			});
			regVis.draw(regView, { isStacked: true });
			actVis.draw(actView, { legend: 'none' });
		} catch(e) {
			that.error(e);
			dfd.reject(e);
		}
	});
	
	return dfd.promise();
}

GraphBuilder.prototype.drawEditCounts = function() {
	var that = this;

	var dfd = $.Deferred();
	var check = new CheckDone(2, dfd.resolve);
	
	//Draw 2 graphs: cumulative edits and edits per month
	var totContainer = document.getElementById('edit_graph');
	var intContainer = document.getElementById('edit_interval_graph');
	if(!totContainer && !intContainer) {
		dfd.resolve();
		return dfd.promise();
	}
	$.get(GraphBuilder.dataPath + 'editCounts.txt', function(data) {
		try {

			var regVis = new google.visualization.AreaChart(totContainer);
			var actVis = new google.visualization.ColumnChart(intContainer);
			var table = that.parseText(data);
			//Modify column names (remove 'in month')
			table.setColumnLabel(2, table.getColumnLabel(1));
			table.setColumnLabel(4, table.getColumnLabel(3));
			table.setColumnLabel(6, table.getColumnLabel(5));
			var formatter = new google.visualization.DateFormat(
				{pattern: 'MMMM yyyy'}
			);
			formatter.format(table, 0);
	
			var totView = new google.visualization.DataView(table);
			totView.setColumns([0, 1, 3, 5]);
			var intView = new google.visualization.DataView(table);
			intView.setColumns([0, 2, 4, 6]);
	
			google.visualization.events.addListener(regVis, 'ready', function() {
				check.isDone();
			});
			google.visualization.events.addListener(actVis, 'ready', function() { 
				check.isDone();
			});
		
			regVis.draw(totView, { isStacked: true });
			actVis.draw(intView, { isStacked: true });
		} catch(e) {
			that.error(e);
			dfd.reject(e);
		}
	});
	
	return dfd.promise();
}

GraphBuilder.prototype.drawWebserviceCounts = function() {
	var that = this;

	var dfd = $.Deferred();
	
	var container = document.getElementById('webservice_graph');
	if(!container) {
		dfd.resolve();
		return dfd.promise();
	}
	$.get(GraphBuilder.dataPath + 'webservice.txt', function(data) {
		try {

			var vis = new google.visualization.ColumnChart(container);
			var table = that.parseText(data);
			var formatter = new google.visualization.DateFormat(
				{pattern: 'MMMM yyyy'}
			);
			formatter.format(table, 0);
			google.visualization.events.addListener(vis, 'ready', dfd.resolve);
			vis.draw(table, { isStacked: true });
		} catch(e) {
			that.error(e);
			dfd.reject(e);
		}
	});
	
	return dfd.promise();
}

GraphBuilder.prototype.drawEditFrequencies = function() {
	var that = this;

	var dfd = $.Deferred();

	var cont = document.getElementById('edits_frequencies_graph');
	if(!cont) {
		dfd.resolve();
		return dfd.promise();
	}
	$.get(GraphBuilder.dataPath + 'usageFrequencies.txt.edits', function(data) {
		try {

			if(that.slowBrowser()) {
				that.browserNotice(cont);
				dfd.reject();
				return;
			}
			var vis = new google.visualization.LineChart(cont);
			var table = that.parseText(data);
			google.visualization.events.addListener(vis, 'ready', dfd.resolve);
			vis.draw(table, {
				hAxis: {
					title:'Pathway rank (by number of edits)', showTextEvery:100
				}, 
				vAxis: {title:'Number of pathways'},	
				legend: 'none',
				categoryLabels: 50
			});
		} catch(e) {
			that.error(e);
			dfd.reject(e);
		}
	});
	
	return dfd.promise();
}

GraphBuilder.prototype.drawViewFrequencies = function() {
	var that = this;

	var dfd = $.Deferred();

	var cont = document.getElementById('views_frequencies_graph');
	if(!cont) {
		dfd.resolve();
		return dfd.promise();
	}
	$.get(GraphBuilder.dataPath + 'usageFrequencies.txt.views', function(data) {
		try {

			if(that.slowBrowser()) {
				that.browserNotice(cont);
				dfd.reject();
				return;
			}
			var vis = new google.visualization.LineChart(cont);
			var table = that.parseText(data);
			google.visualization.events.addListener(vis, 'ready', dfd.resolve);
			vis.draw(table, {
				hAxis: {
					title:'Pathway rank (by number of views)', showTextEvery:100
				}, 
				vAxis: {title:'Number of pathways'},	
				legend: 'none'
			});
		} catch(e) {
			that.error(e);
			dfd.reject(e);
		}
	});
	
	return dfd.promise();
}

GraphBuilder.prototype.drawXrefFrequencies = function() {
	var that = this;
	var dfd = $.Deferred();
	var cont = document.getElementById('xref_frequencies_graph');
	if(!cont) {
		dfd.resolve();
		return dfd.promise();
	}
	$.get(GraphBuilder.dataPath + 'contentFrequencies.txt.xrefs', function(data) {
		try {
			if(that.slowBrowser()) {
				that.browserNotice(cont);
				dfd.reject();
				return;
			}
			var vis = new google.visualization.LineChart(cont);
			var table = that.parseText(data);
			google.visualization.events.addListener(vis, 'ready', dfd.resolve);
			vis.draw(table, {
				hAxis: {title:'Pathway rank (by nr. xrefs)', showTextEvery:100}, 
				vAxis: {title:'Number of xrefs'},	
				legend: 'none'
			});
		} catch(e) {
			that.error(e);
			dfd.reject(e);
		}
	});
	return dfd.promise();
}

GraphBuilder.prototype.drawLitFrequencies = function() {
	var that = this;
	var dfd = $.Deferred();
	var cont = document.getElementById('lit_frequencies_graph');
	if(!cont) {
		dfd.resolve();
		return dfd.promise();
	}
	$.get(GraphBuilder.dataPath + 'contentFrequencies.txt.lit', function(data) {
		try {
			if(that.slowBrowser()) {
				that.browserNotice(cont);
				dfd.reject();
				return;
			}
			var vis = new google.visualization.LineChart(cont);
			var table = that.parseText(data);
			google.visualization.events.addListener(vis, 'ready', dfd.resolve);
			vis.draw(table, {
				hAxis: {title:'Pathway rank (by nr. literature references)', showTextEvery:100}, 
				vAxis: {title:'Number of literature references'},	
				legend: 'none'
			});
		} catch(e) {
			that.error(e);
			dfd.reject(e);
		}
	});
	return dfd.promise();
}

GraphBuilder.prototype.drawIntFrequencies = function() {
	var that = this;
	var dfd = $.Deferred();
	var cont = document.getElementById('int_frequencies_graph');
	if(!cont) {
		dfd.resolve();
		return dfd.promise();
	}
	$.get(GraphBuilder.dataPath + 'contentFrequencies.txt.int', function(data) {
		try {
			if(that.slowBrowser()) {
				that.browserNotice(cont);
				dfd.reject();
				return;
			}
			var vis = new google.visualization.LineChart(cont);
			var table = that.parseText(data);
			google.visualization.events.addListener(vis, 'ready', dfd.resolve);
			vis.draw(table, {
				hAxis: {title:'Pathway rank (by nr. connected lines)', showTextEvery:100}, 
				vAxis: {title:'Number of connected lines'},	
				legend: 'none'
			});
		} catch(e) {
			that.error(e);
			dfd.reject(e);
		}
	});
	return dfd.promise();
}

GraphBuilder.prototype.drawUserFrequencies = function() {
	var that = this;
	var dfd = $.Deferred();
	
	var check = new CheckDone(2, dfd.resolve);
	var freqContainer = document.getElementById('user_frequencies_graph');
	var actContainer = document.getElementById('most_active_graph');
	if(!freqContainer && !actContainer) {
		dfd.resolve();
		return dfd.promise();
	}
	$.get(GraphBuilder.dataPath + 'userFrequencies.txt', function(data) {
		try {
			//Draw 2 graphs: all users, most active users
			var table = that.parseText(data);
			
			if(freqContainer) {
				var freqVis = new google.visualization.LineChart(freqContainer);
				var freqView = new google.visualization.DataView(table);
				freqView.setColumns([1, 2]);
				google.visualization.events.addListener(freqVis, 'ready', function() {
					check.isDone();
				});

				freqVis.draw(freqView, { 
					hAxis: {title:'User rank (by number of edits)', showTextEvery:20}, 
					vAxis: {title:'Number of edits'},
					legend: 'none'
				});
			} else {
				check.isDone();
			}
			if(actContainer) {
				var actVis = new google.visualization.ColumnChart(actContainer);
				var actView = new google.visualization.DataView(table);
				actView.setColumns([0, 2]);
				actView.setRows(0, 14);
				google.visualization.events.addListener(actVis, 'ready', function() {
					check.isDone();
				});
	
				actVis.draw(actView, { 
					hAxis: {showTextEvery: 1, slantedText: true},
					vAxis: {title: 'Number of edits'},
					legend: 'none', isVertical: true
				});
			} else {
				check.isDone();
			}
		} catch(e) {
			that.error(e);
			dfd.reject(e);
		}
	});
	return dfd.promise();
}

GraphBuilder.prototype.drawPathwayCounts = function() {
	var that = this;
	var dfd = $.Deferred();
	var container = document.getElementById('pathway_counts_graph');
	if(!container) {
		dfd.resolve();
		return dfd.promise();
	}
	$.get(GraphBuilder.dataPath + 'pathwayCounts.txt', function(data) {
		try {
			var vis = null;
			var opt = null;

			vis = new google.visualization.LineChart(container);
			opt = { isStacked: true };
			var table = that.parseText(data);

			var formatter = new google.visualization.DateFormat(
				{pattern: 'MMMM yyyy'}
			);
			formatter.format(table, 0);

			//Add species selector
			var species = new Array();
			for(var i = 1; i < table.getNumberOfColumns(); i++) {
				species[i] = table.getColumnLabel(i);
			}
			
			var selected = 1;
			$.each(species, function(index, value) {  
			 	var s = $("<option></option>")
					.attr("value", index)
					.text(value);

				if(value == 'All species') {
					selected = index;
					s.attr('selected', true);
				}
				
				$('#species_combo').append(s);
			});
			
			todraw = new google.visualization.DataView(table);
			todraw.setColumns([0,selected]);
			opt.legend = 'none';
			
			$('#species_combo').change(function() {
				var s = $('#species_combo option:selected').text();
				var i = parseInt($('#species_combo option:selected').val());
				var view = new google.visualization.DataView(table);
				view.setColumns([0,i]);

				opt.legend = 'none';
				vis.draw(view, opt);
			});
	
			google.visualization.events.addListener(vis, 'ready', dfd.resolve);
			vis.draw(todraw, opt);
		} catch(e) {
			that.error(e);
			dfd.reject(e);
		}
	});
	return dfd.promise();
}

GraphBuilder.prototype.drawCollectionCounts = function() {
	var that = this;
	var dfd = $.Deferred();
	var container = document.getElementById('collection_counts_graph');
	if(!container) {
		dfd.resolve();
		return dfd.promise();
	}
	$.get(GraphBuilder.dataPath + 'collectionCounts.txt', function(data) {
		try {
			var vis = new google.visualization.LineChart(container);
			var table = that.parseText(data);
			var formatter = new google.visualization.DateFormat(
				{pattern: 'MMMM yyyy'}
			);
			formatter.format(table, 0);
	
			google.visualization.events.addListener(vis, 'ready', dfd.resolve);
			vis.draw(table);
		} catch(e) {
			that.error(e);
			dfd.reject(e);
		}
	});
	return dfd.promise();
}

GraphBuilder.prototype.drawUniquePerSpecies = function() {
	var that = this;
	var dfd = $.Deferred();
	var check = new CheckDone(2, dfd.resolve);
	
	var chart = null;
	var histTable = null;
	var graphCont = document.getElementById('unique_per_species_graph');
	if(graphCont) {
		$.get(GraphBuilder.dataPath + 'uniquePerSpecies.txt', function(data) {
			try {
				chart = new google.visualization.ColumnChart(graphCont);
				histTable = that.parseText(data);
	
				google.visualization.events.addListener(
					chart, 'ready', function() { check.done(); });
				chart.draw(histTable, { legend: 'none',
					vAxis: {title: 'Number of pathway titles'},
					hAxis: {title: 'Present in number of species' }
				});
			} catch(e) {
				that.error(e);
				check.isDone();
			}
		});
	} else {
		check.isDone();
	}
	
	var tblCont = document.getElementById('unique_per_species_table');
	if(tblCont) {
		$.get(GraphBuilder.dataPath + 'uniquePerSpecies.txt.titles', function(data) {
			try {
				var props = { height: '250' };
				var vis = new google.visualization.Table(tblCont);
				var table = that.parseText(data);
				vis.draw(table, props);
				check.isDone();
			} catch(e) {
				that.error(e);
				check.isDone();
			}
		});
	} else {
		check.isDone();
	}
	
	return dfd.promise();
}

GraphBuilder.prototype.parseText = function(text) {
	var that = this;
	var data = new google.visualization.DataTable();

	var lines = text.split("\n");
	if(lines.length < 3) "Missing headers in data file";
	var types = lines.shift().split("\t");
	var labels = lines.shift().split("\t");
	var ncol = types.length;
	for(var i = 0; i < ncol; i++) {
		data.addColumn(types[i], labels[i]);
	}
	for(var i = 0; i < lines.length; i++) {
		var l = lines[i];
		var cols = l.split("\t");
		var objs = new Array();
		if(cols.length != ncol) continue; //Skip lines with wrong col nr
		for(var j = 0; j < cols.length; j++) {
			objs[j] = that.parseValue(cols[j], types[j]);
		}
		data.addRow(objs);
	}
	return data;
}

GraphBuilder.prototype.parseValue = function(v, t) {
	if(t == 'date') return new Date(v);
	if(t == 'number') return parseFloat(v);
	return v;
}

GraphBuilder.prototype.error = function(msg) {
	if(console && console.log) console.log(msg);
}

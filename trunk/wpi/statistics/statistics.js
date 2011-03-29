/**
* Draw graphs for WikiPathways statistics using
* the google visualization API
*/

// Load the Visualization API
google.load('visualization', '1', {'packages':['corechart']});
google.load('jquery', '1.5.0');

// Set a callback to run when the Google Visualization API is loaded.
drawVisualizations = function() {
	var graphBuilder = new GraphBuilder();
	graphBuilder.drawVisualizations();
}
google.setOnLoadCallback(drawVisualizations);

function GraphBuilder() { }

GraphBuilder.dataPath = '';
if(typeof(wgScriptPath) != 'undefined') { //Loaded from MW page
	GraphBuilder.dataPath = wgServer + wgScriptPath + '/wpi/statistics/';
}

GraphBuilder.prototype.drawVisualizations = function() {
	try { this.drawPathwayCounts(); } catch(err) { this.error(err); }
	try { this.drawUserCounts(); } catch(err) { this.error(err); }
	try { this.drawCollectionCounts(); } catch(err) { this.error(err); }
	try { this.drawEditCounts(); } catch(err) { this.error(err); }
	try { this.drawUserFrequencies(); } catch(err) { this.error(err); }
	try { this.drawContentFrequencies(); } catch(err) { this.error(err); }
	try { this.drawViewFrequencies(); } catch(err) { this.error(err); }
	try { this.drawEditFrequencies(); } catch(err) { this.error(err); }
	try { this.addSummary(); } catch(err) { this.error(err); }
	try { this.drawWebserviceCounts(); } catch(err) { this.error(err); }
}

GraphBuilder.prototype.addSummary = function() {
	var container = document.getElementById('summary');
	jQuery.get(GraphBuilder.dataPath + 'summary.txt', function(data) {
		$(container).html(data);
	});
}

GraphBuilder.prototype.drawUserCounts = function() {
	var that = this;
	//Draw 2 graphs: cumulative users and active users per month
	var regContainer = document.getElementById('user_registered_graph');
	var actContainer = document.getElementById('user_active_graph');

	var regVis = new google.visualization.AreaChart(regContainer);
	var actVis = new google.visualization.ColumnChart(actContainer);

	jQuery.get(GraphBuilder.dataPath + 'userCounts.txt', function(data) {
		var table = that.parseText(data);
		var formatter = new google.visualization.DateFormat(
			{pattern: 'MMMM yyyy'}
		);
		formatter.format(table, 0);
	
		var regView = new google.visualization.DataView(table);
		regView.setColumns([0, 2, 1]);
		var actView = new google.visualization.DataView(table);
		actView.setColumns([0, 3]);
	
		regVis.draw(regView, { isStacked: true });
	
		actVis.draw(actView, { legend: 'none' });
	});
}

GraphBuilder.prototype.drawEditCounts = function() {
	var that = this;
	//Draw 2 graphs: cumulative edits and edits per month
	var totContainer = document.getElementById('edit_graph');
	var intContainer = document.getElementById('edit_interval_graph');

	var regVis = new google.visualization.AreaChart(totContainer);
	var actVis = new google.visualization.ColumnChart(intContainer);

	jQuery.get(GraphBuilder.dataPath + 'editCounts.txt', function(data) {
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
	
		regVis.draw(totView, { isStacked: true });
	
		actVis.draw(intView, { isStacked: true });
	});
}

GraphBuilder.prototype.drawWebserviceCounts = function() {
	var that = this;
	var container = document.getElementById('webservice_graph');

	var vis = new google.visualization.ColumnChart(container);

	jQuery.get(GraphBuilder.dataPath + 'webservice.txt', function(data) {
		var table = that.parseText(data);
		var formatter = new google.visualization.DateFormat(
			{pattern: 'MMMM yyyy'}
		);
		formatter.format(table, 0);
	
		vis.draw(table, { isStacked: true });
	});
}

GraphBuilder.prototype.drawEditFrequencies = function() {
	var that = this;
	var cont = document.getElementById('edits_frequencies_graph');

	var vis = new google.visualization.AreaChart(cont);

	jQuery.get(GraphBuilder.dataPath + 'usageFrequencies.txt.edits', function(data) {
		var table = that.parseText(data);
		
		vis.draw(table, {
			hAxis: {
				title:'Pathway rank (by number of edits)', showTextEvery:100
			}, 
			vAxis: {title:'Number of pathways'},	
			legend: 'none'
		});
	});
}

GraphBuilder.prototype.drawViewFrequencies = function() {
	var that = this;
	var cont = document.getElementById('views_frequencies_graph');

	var vis = new google.visualization.AreaChart(cont);

	jQuery.get(GraphBuilder.dataPath + 'usageFrequencies.txt.views', function(data) {
		var table = that.parseText(data);
		
		vis.draw(table, {
			hAxis: {
				title:'Pathway rank (by number of views)', showTextEvery:100
			}, 
			vAxis: {title:'Number of pathways'},	
			legend: 'none'
		});
	});
}

GraphBuilder.prototype.drawContentFrequencies = function() {
	var that = this;
	var xref_cont = document.getElementById('xref_frequencies_graph');
	var lit_cont = document.getElementById('lit_frequencies_graph');
	var int_cont = document.getElementById('int_frequencies_graph');

	var xref_vis = new google.visualization.AreaChart(xref_cont);
	var lit_vis = new google.visualization.AreaChart(lit_cont);
	var int_vis = new google.visualization.AreaChart(int_cont);

	jQuery.get(GraphBuilder.dataPath + 'contentFrequencies.txt.xrefs', function(data) {
		var table = that.parseText(data);
		
		xref_vis.draw(table, {
			hAxis: {title:'Pathway rank (by nr. xrefs)', showTextEvery:100}, 
			vAxis: {title:'Number of xrefs'},	
			legend: 'none'
		});
	});
	
	jQuery.get(GraphBuilder.dataPath + 'contentFrequencies.txt.lit', function(data) {
		var table = that.parseText(data);
			
		lit_vis.draw(table, {
			hAxis: {title:'Pathway rank (by nr. literature references)', showTextEvery:100}, 
			vAxis: {title:'Number of literature references'},	
			legend: 'none'
		});
	});
	
	jQuery.get(GraphBuilder.dataPath + 'contentFrequencies.txt.int', function(data) {
		var table = that.parseText(data);
			
		int_vis.draw(table, {
			hAxis: {title:'Pathway rank (by nr. connected lines)', showTextEvery:100}, 
			vAxis: {title:'Number of connected lines'},	
			legend: 'none'
		});
	});
}

GraphBuilder.prototype.drawUserFrequencies = function() {
	var that = this;
	//Draw 2 graphs: all users, most active users
	var freqContainer = document.getElementById('user_frequencies_graph');
	var actContainer = document.getElementById('most_active_graph');

	var freqVis = new google.visualization.AreaChart(freqContainer);
	var actVis = new google.visualization.ColumnChart(actContainer);

	jQuery.get(GraphBuilder.dataPath + 'userFrequencies.txt', function(data) {
		var table = that.parseText(data);
			
		var freqView = new google.visualization.DataView(table);
		freqView.setColumns([1, 2]);
		var actView = new google.visualization.DataView(table);
		actView.setColumns([0, 2]);
		actView.setRows(0, 14);
		
		/* Use this to set correct axis range when using log scale
		var maxX = freqView.getColumnRange(0).max;
		maxX = Math.pow(10, Math.ceil(Math.log(maxX) / Math.log(10))) + 1;
		
		var maxY = freqView.getColumnRange(1).max;
		maxY = Math.pow(10, Math.ceil(Math.log(maxY) / Math.log(10))) + 1;
		*/
		
		freqVis.draw(freqView, { 
			hAxis: {title:'User rank (by number of edits)', showTextEvery:20}, 
			vAxis: {title:'Number of edits'},
			legend: 'none'
		});
	
		actVis.draw(actView, { 
			hAxis: {showTextEvery: 1, slantedText: true},
			vAxis: {title: 'Number of edits'},
			legend: 'none'
		});
	});
}

GraphBuilder.prototype.drawPathwayCounts = function() {
	var that = this;
	var container = document.getElementById('pathway_counts_graph');
	var vis = new google.visualization.AreaChart(container);
	jQuery.get(GraphBuilder.dataPath + 'pathwayCounts.txt', function(data) {
		var table = that.parseText(data);

		var formatter = new google.visualization.DateFormat(
			{pattern: 'MMMM yyyy'}
		);
		formatter.format(table, 0);

		var vizopt = { isStacked: true };
	
		//Add species selector
		var species = new Array();
		for(var i = 1; i < table.getNumberOfColumns(); i++) {
			species[i] = table.getColumnLabel(i);
		}
		$.each(species, function(index, value) {   
			$('#species_combo').prepend(
				$("<option></option>")
				.attr("value", index)
				.text(value)
			);
		});
		$('#species_combo').prepend(
			$('<option></option>')
			.attr("value", -1)
			.text("All organisms")
			.attr("selected", true)
		);
		$('#species_combo').change(function() {
				var s = $('#species_combo option:selected').text();
				var i = parseInt($('#species_combo option:selected').val());
				if(i < 0) {
					vizopt.legend = 'right';
					vis.draw(table, vizopt);
				} else {
					var view = new google.visualization.DataView(table);
					view.setColumns([0,i]);
		
					vizopt.legend = 'none';
					vis.draw(view, vizopt);
				}
		});
	
		vis.draw(table, vizopt);
	});
}

GraphBuilder.prototype.drawCollectionCounts = function() {
	var that = this;
	var container = document.getElementById('collection_counts_graph');

	var vis = new google.visualization.LineChart(container);

	jQuery.get(GraphBuilder.dataPath + 'collectionCounts.txt', function(data) {
		var table = that.parseText(data);
		var formatter = new google.visualization.DateFormat(
			{pattern: 'MMMM yyyy'}
		);
		formatter.format(table, 0);
	
		vis.draw(table);
	});
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

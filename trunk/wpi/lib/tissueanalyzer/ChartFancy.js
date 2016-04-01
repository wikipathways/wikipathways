//google.load('visualization', '1', {packages: ['piechart']});   
//if ((typeof google === 'undefined') || (typeof google.visualization === 'undefined')) {
//     google.charts.load("current", {packages:['corechart']});
//}
google.charts.load("current", {packages:['corechart']});

function drawChartFancy(fileName,pathwayName,average,measured,tissue) {
			var dfd = $.Deferred();
      var jsonData = $.ajax({
          url: fileName,
          dataType: "json",
          async: false
          }).responseText;


      // Create our data table out of JSON data loaded from server.
      var data = new google.visualization.DataTable(jsonData);
			// Add a new style column for the color
			data.addColumn({ type: 'string', role: 'style' });
			for (i = 0; i < data.getNumberOfRows(); i++) {
				if (   data.getValue(i, 0).localeCompare(tissue)==0 ){
					console.log(tissue);
					// Change the color to orange for the selected tissue
					data.setValue(i, 2, "orange"); 
				}
			}
			var horizontalLine = parseFloat(average) ;
			var view = new google.visualization.DataView(data);
			// Create a column with the average value to the draw the horizontal line
			view.setColumns([0,1,2, {
					type: 'number', label:'Average tissues expression: '+horizontalLine,
					calc: function () {
						  return horizontalLine;
					}
			}]);
	 		var options = {
        title: "Pathway: "+pathwayName+"\n (median expression per tissue in "+measured.trim()+" measured genes )",
        width: 1400,
        height: 600,
        //bar: {groupWidth: "61.8%"},
				vAxis: {maxValue: 10},
				seriesType: "bars",
				//Change the type to line to the draw the horizontal line
				series: {1: {
				  type: "line", 
				  color: 'red',
					enableInteractivity: false ,
				  areaOpacity: 0
				  }
				},
				//hAxis: {textStyle: {showTextEvery: 0, fontSize: 12}},
				hAxis: {
								slantedText:true,
								slantedTextAngle:90 },
        legend: {position: 'right'},
      };
      // Instantiate and draw our chart, passing in some options.
			new google.visualization.ColumnChart(document.getElementById('data')).draw(view, options);
      //var chart = new google.visualization.ColumnChart(document.getElementById('data')).draw(view, options);
			//google.visualization.events.addListener(chart, 'ready', dfd.resolve);
      //chart.draw(data, options);
	
}

$(document).ready(function() {
	  $("a#inline").each(function() {
	    var f = this.getAttribute("file");
			var v = this.textContent;
	    var p = this.getAttribute("pathway");
			var m = this.getAttribute("measured");
			var t = this.getAttribute("tissue");
	    $(this).fancybox({	
	      'hideOnContentClick': true,
	      onComplete: function() { drawChartFancy(f,p,v,m,t); }  
	     });
	  });
});

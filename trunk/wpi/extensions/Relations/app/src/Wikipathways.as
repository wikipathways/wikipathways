import com.adobe.serialization.json.JSON;

import mx.controls.Alert;

function getSpecies():void
{
	//Create the URLLOader instance
	var myLoader:URLLoader = new URLLoader()
		
	myLoader.load(new URLRequest(appRoot + "?action=species"))
		
	myLoader.addEventListener(Event.COMPLETE, function(event:Event):void{
		var loader:URLLoader = URLLoader(event.target);
		speciesList = JSON.decode(loader.data) as Array;
//		speciesSelect.selectedItem = "homo sapiens";
	});	
}	

function getDataSourceURL():String
{
	 var url:String = appRoot + "?action=relations&species=" + species + "&type=" + relationType +  "&minscore=" + score;

	 return url;
}

function getPathwayDetails():void
{
	toggleUIControls(false);
	
	//Create the URLLOader instance
	var myLoader:URLLoader = new URLLoader()
	
	myLoader.load(new URLRequest(appRoot + "?action=info&pwId=" + pathwaySelect.selectedItem.pwId));
	
	myLoader.addEventListener(Event.COMPLETE, function(event:Event):void{
		var loader:URLLoader = URLLoader(event.target);
//		speciesList = JSON.decode(loader.data) as Array;
		
		viewPathway(loader.data);				 	
	});
}

	

	

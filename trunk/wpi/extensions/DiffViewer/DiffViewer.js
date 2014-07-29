$(function(){
	var $container = $('#pathvisiojs-container')
		, pathwayUriOld = $container.data('pathwayOld')
		, pathwayUriNew = $container.data('pathwayNew')
		;

	$container.pathvisiojs({
	  fitToContainer: true
	, manualRender: true
	, sourceData: [
	    {
	      uri: pathwayUriOld,
	      fileType:'gpml'
	    }
	  ]
	})

	// Get first element from array of instances
	pathInstance = $('#pathvisiojs-container').pathvisiojs('get').pop()
	window.pathInstance = pathInstance

	// Load plugins
	// pathvisiojsNotifications(pathInstance, {displayErrors: true, displayWarnings: true})
	pathvisiojsDiffviewer(pathInstance, {
	  sourceData: [
	    {
	      uri: pathwayUriNew,
	      fileType:'gpml'
	    }
	  ]
	})
	pathInstance.on('rendered', function(){
	  var hi = pathvisiojsHighlighter(pathInstance, {displayInputField: false})
	})

	// Call renderer
	pathInstance.render()
})

import mx.controls.Alert;
import mx.core.UIComponent;
import mx.graphics.codec.JPEGEncoder;

function saveJPG (object: UIComponent): void 
{

	var bd : BitmapData = new BitmapData(object.width, object.height );
	
	var m : Matrix = new Matrix();
	bd.draw( object, m );
	
	//Converting BitmapData into a JPEG-encoded ByteArray		
	var jpgObj: JPEGEncoder = new JPEGEncoder(100);
	var imageBytes: ByteArray = jpgObj.encode (bd);
	imageBytes.position = 0;
	
	var boundary: String = '---------------------------7d76d1b56035e';
	var header1: String  = '--'+boundary + '\r\n'
		+'Content-Disposition: form-data; name="Filename"\r\n\r\n'
		+'picture.jpg\r\n'
		+'--'+boundary + '\r\n'
		+'Content-Disposition: form-data; name="Filedata"; filename="picture.jpg"\r\n'
		+'Content-Type: application/octet-stream\r\n\r\n'
	//In a normal POST header, you'd find the image data here
	var header2: String =	'--'+boundary + '\r\n'
		+'Content-Disposition: form-data; name="Upload"\r\n\r\n'
		+'Submit Query\r\n'
		+'--'+boundary + '--';
	//Encoding the two string parts of the header
	var headerBytes1: ByteArray = new ByteArray();
	headerBytes1.writeMultiByte(header1, "ascii");
	
	var headerBytes2: ByteArray = new ByteArray();
	headerBytes2.writeMultiByte(header2, "ascii");
	
	//Creating one final ByteArray
	var sendBytes: ByteArray = new ByteArray();
	sendBytes.writeBytes(headerBytes1, 0, headerBytes1.length);
	sendBytes.writeBytes(imageBytes, 0, imageBytes.length);
	sendBytes.writeBytes(headerBytes2, 0, headerBytes2.length);
	
	var request: URLRequest = new URLRequest(appRoot + "/?action=save");
	request.data = sendBytes;
	request.method = URLRequestMethod.POST;
	request.contentType = "multipart/form-data; boundary=" + boundary;
	
	var loader:URLLoader = new URLLoader();
	loader.addEventListener(Event.COMPLETE, uploadCompleted);
	
	try {
		loader.load(request);
	} catch (error: Error) {
		trace("Unable to load requested document.");
	}
}

function uploadCompleted(e: Event):void 
{
	var result:String = e.target.data;
	
	if(result == "error")
		mx.controls.Alert.show("Server Error, Please try later!");
	else
	{
		var urlRequest:URLRequest = new URLRequest(result);
		navigateToURL(urlRequest, "_blank");
	}
	
	toggleUIControls(true);	
}

function exportHandler(e: Event):void
{
	var selectedIndex:int = e.target.selectedIndex;	
	
	toggleUIControls(false);
	
	switch(selectedIndex)
	{		
		case 0:
			saveJPG(vizContainer);
			break;
		
		case 1:
			var url:String = getDataSourceURL();
			var urlRequest:URLRequest = new URLRequest(url);
			navigateToURL(urlRequest, "_blank");
			
			toggleUIControls(true);
			break;
	}			

}	
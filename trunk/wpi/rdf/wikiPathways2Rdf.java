import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.net.MalformedURLException;
import java.net.URL;
import java.rmi.RemoteException;
import java.text.ParseException;

import javax.xml.parsers.ParserConfigurationException;
import javax.xml.rpc.ServiceException;
import javax.xml.xpath.XPathExpressionException;

import org.pathvisio.model.ConverterException;
import org.pathvisio.wikipathways.WikiPathwaysClient;
import org.pathvisio.wikipathways.webservice.WSPathwayInfo;
import org.w3c.dom.DOMException;
import org.xml.sax.SAXException;

import com.hp.hpl.jena.rdf.model.Model;
import com.hp.hpl.jena.rdf.model.ModelFactory;


public class wikiPathways2Rdf {

	/**
	 * @param args
	 */
	public static void main(String[] args) {
		// TODO Auto-generated method stub
		
		try {
			Pathway2Rdf p2r = new Pathway2Rdf();
			Model model = ModelFactory.createDefaultModel();
			FileOutputStream fout = new FileOutputStream("wikipathwaysRdf.rdf");
			WikiPathwaysClient client = new WikiPathwaysClient(new URL("http://www.wikipathways.org/wpi/webservice/webservice.php"));
			WSPathwayInfo[] pathwayList = client.listPathways();
			
		    for(WSPathwayInfo pathwaylist : pathwayList) {
				 String wpIdentifier = pathwaylist.getId();
				 System.out.println(wpIdentifier);
				 model.add(p2r.addPathway2Rdf(wpIdentifier, model));
			} 
			model.write(fout, "N-TRIPLE");
		} catch (MalformedURLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (ServiceException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (RemoteException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (FileNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (DOMException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (XPathExpressionException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (ConverterException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (ParserConfigurationException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (SAXException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (ParseException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		
		
	}

}

import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.OutputStreamWriter;
import java.io.StringReader;
import java.io.Writer;
import java.net.MalformedURLException;
import java.net.URL;
import java.rmi.RemoteException;
import java.util.*;
import java.text.*;

import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.ParserConfigurationException;
import javax.xml.rpc.ServiceException;
import javax.xml.xpath.XPath;
import javax.xml.xpath.XPathConstants;
import javax.xml.xpath.XPathExpressionException;
import javax.xml.xpath.XPathFactory;

import org.pathvisio.model.ConverterException;
import org.pathvisio.model.ObjectType;
import org.pathvisio.model.Pathway;
import org.pathvisio.model.PathwayElement;
import org.pathvisio.wikipathways.WikiPathwaysClient;
import org.pathvisio.wikipathways.webservice.WSPathway;
import org.pathvisio.wikipathways.webservice.WSPathwayHistory;
import org.w3c.dom.DOMException;
import org.w3c.dom.Document;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;
import org.xml.sax.InputSource;
import org.xml.sax.SAXException;

import com.hp.hpl.jena.rdf.model.Model;
import com.hp.hpl.jena.rdf.model.ModelFactory;
import com.hp.hpl.jena.rdf.model.Property;
import com.hp.hpl.jena.rdf.model.Resource;
import com.hp.hpl.jena.sparql.vocabulary.FOAF;
import com.hp.hpl.jena.vocabulary.DC;
import com.hp.hpl.jena.vocabulary.DCTerms;
import com.hp.hpl.jena.vocabulary.RDF;
import com.hp.hpl.jena.vocabulary.RDFS;
import com.hp.hpl.jena.vocabulary.XSD;

public class Pathway2Rdf {

	/**
	 * @param args
	 * @throws XPathExpressionException
	 * @throws DOMException
	 * @throws ServiceException
	 * @throws ConverterException
	 * @throws ParserConfigurationException
	 * @throws IOException
	 * @throws SAXException
	 * @throws ParseException
	 */
	public static Model addPathway2Rdf(String wpIdentifier, Model model)
			throws DOMException, XPathExpressionException, ServiceException,
			ConverterException, ParserConfigurationException, SAXException,
			IOException, ParseException {
		// Declare the Prefixes
		String nsWikipathways = "http://www.wikipathways.org/#/";// DOT#1
		String nsGenmapp = "http://www.genmapp.org/#/";// DOT#2
		String nsDbpedia = "http://dbpedia.org/resource/#/";// DOT#3
		String nsIdentifiers = "http://identifiers.org/#/";

		// Get data
		WikiPathwaysClient client = new WikiPathwaysClient(new URL(
				"http://www.wikipathways.org/wpi/webservice/webservice.php"));

		// Model wikipathways level into RDF
		Resource wikipathwaysResource = model.createResource(nsWikipathways);
		Resource wikiPathwaysPaperResource = model
				.createResource("http://www.ncbi.nlm.nih.gov/pubmed/18651794");
		wikipathwaysResource.addProperty(DCTerms.bibliographicCitation,
				wikiPathwaysPaperResource);
		String DefinitionURI = nsWikipathways + "Definition/";
		Resource wikipathwaysGroupDefinitionResource = model
				.createResource(DefinitionURI + "Group/");

		// Get the revision history and the authors
		DateFormat formatter;
		Date date;
		formatter = new SimpleDateFormat("yyyymmdd");
		date = (Date) formatter.parse("20000101");
		WSPathwayHistory pathwayHistory = client.getPathwayHistory(
				wpIdentifier, date);
		String pathwayURI = nsWikipathways + "Pathway/" + wpIdentifier + "/";
		System.out.println(wpIdentifier);
		Resource abstractPathwayResource = model.createResource(pathwayURI);
		for (int i = 0; i < pathwayHistory.getHistory().length; i++) {
			String revision = pathwayHistory.getHistory(i).getRevision();
			System.out.println(revision);
			String pathwayResourceURI = pathwayURI + revision + "/";
			abstractPathwayResource.addProperty(DCTerms.hasVersion,
					pathwayResourceURI);
			Resource pathwayResource = model.createResource(pathwayResourceURI);
			Resource wpUser = model.createResource(nsWikipathways + "user/"
					+ pathwayHistory.getHistory(i).getUser());
			pathwayResource.addProperty(DC.contributor, wpUser);
			wpUser.addProperty(RDF.type, FOAF.accountName);

			// A Pathway in Wikipathways is identified by its WP identifier and
			// its revision number;
			Resource pathwayIdentifierResource = model
					.createResource(nsIdentifiers + "/WikiPathways/"
							+ wpIdentifier + "/" + revision + "/");
			pathwayIdentifierResource.addProperty(RDFS.label, wpIdentifier);
			pathwayResource.addProperty(DC.identifier,
					pathwayIdentifierResource);
			pathwayResource.addProperty(RDFS.label, wpIdentifier);

			// PARSE GPML
			// Get the GPML from the webservice
			Pathway pathway = null;
			WSPathway wsPathway = client.getPathway(wpIdentifier,
					Integer.parseInt(revision));
			if(!wsPathway.getGpml().startsWith("{{deleted|")) {
				 pathway = WikiPathwaysClient.toPathway(wsPathway);	 
			String gpml = wsPathway.getGpml();


			
			DocumentBuilderFactory docBuilderFactory = DocumentBuilderFactory
					.newInstance();
			DocumentBuilder docBuilder = docBuilderFactory.newDocumentBuilder();
			StringReader reader = new StringReader(gpml);
			InputSource inputSource = new InputSource(reader);
			Document doc = docBuilder.parse(inputSource);
			reader.close();
			doc.getDocumentElement().normalize();

			// Get the Pathway Nodes
			XPath xPath = XPathFactory.newInstance().newXPath();

			Node pathwayLicense = ((Node) xPath.evaluate("/Pathway/@License",
					doc, XPathConstants.NODE));
			Node pathwayName = ((Node) xPath.evaluate("/Pathway/@Name", doc,
					XPathConstants.NODE));
			Node pathwayOrganism = ((Node) xPath.evaluate("/Pathway/@Organism",
					doc, XPathConstants.NODE));

			// Map the organism to DbPedia
			System.out.println(pathwayOrganism);
			if (pathwayOrganism != null) {
				Resource dbPediaSpeciesResource = model
						.createResource("http://dbpedia.org/page"
								+ pathwayOrganism.getNodeValue().replace(" ",
										"_"));
				wikipathwaysResource.addProperty(DC.coverage,
						dbPediaSpeciesResource);
			}

			// Add pathway level details to the RDF model
			if (pathwayName != null)
				pathwayResource.addProperty(RDFS.label,
						pathwayName.getNodeValue());
			if (pathwayIdentifierResource != null)
				pathwayResource.addProperty(DC.identifier,
						pathwayIdentifierResource);
			if (pathwayLicense != null)
				pathwayResource.addProperty(DCTerms.license,
						pathwayLicense.getNodeValue());
			pathwayResource.addProperty(RDF.type, Biopax_level3.Pathway);
			pathwayResource.addProperty(DCTerms.isPartOf, wikipathwaysResource);

			// Get the Group References by calling the getGroupIds from the
			// wikipathways webservices
			for (String groupRef : pathway.getGroupIds()) {
				Resource groupResource = model
						.createResource(pathwayResourceURI + "Group/"
								+ groupRef);
				groupResource.addProperty(RDF.type,
						wikipathwaysGroupDefinitionResource);
			}

			// Add pathwayElements to the RDF model
			for (PathwayElement pwElm : pathway.getDataObjects()) {
				// Only take elements with type DATANODE (genes, proteins,
				// metabolites)

				if (pwElm.getObjectType() == ObjectType.DATANODE) {
					Resource pathwayEntity = model
							.createResource(pathwayResourceURI + "Datanode/"
									+ pwElm.getDataNodeType().toString() + "/"
									+ pwElm.getGraphId());
					pathwayEntity
							.addProperty(DCTerms.isPartOf, pathwayResource);
					pathwayEntity.addProperty(RDFS.label, pwElm.getTextLabel());
					pathwayEntity.addProperty(RDF.type, Biopax_level3.Entity);

					if (pwElm.getXref().getDataSource() != null) {
						String xRefDataSource = pwElm.getXref().getDataSource()
								.toString().replace(" ", "_");
						String xRefId = pwElm.getXref().getId();
						Resource pwElmIdentifierResource = model
								.createResource(nsIdentifiers + "WikiPathways/"
										+ xRefDataSource + "/" + xRefId);
						pwElmIdentifierResource.addProperty(RDFS.label, xRefId);
						Resource pwElmSourceResource = model
								.createResource(nsIdentifiers + "WikiPathways/"
										+ xRefDataSource);
						pwElmSourceResource.addProperty(RDFS.label,
								xRefDataSource);
						pathwayEntity.addProperty(DC.identifier,
								pwElmIdentifierResource);
						pathwayEntity.addProperty(DC.source,
								pwElmSourceResource);
					}

					if (pwElm.getGroupRef() != null) { // Element is part of a
														// group
						pathwayEntity.addProperty(
								DCTerms.isPartOf,
								model.getResource(pathwayResourceURI + "Group/"
										+ pwElm.getGroupRef()));
					}
					// pathwayEntity.addProperty(DC.source,
					// pwElm.getDataSource().toString());
					if (pwElm.getDataNodeType() == "Metabolite") {
						pathwayEntity.addProperty(RDF.type,
								Biopax_level3.ChemicalStructure);
					}
					if (pwElm.getDataNodeType() == "Gene") {
						pathwayEntity.addProperty(RDF.type, Biopax_level3.Gene);
					}
					if (pwElm.getDataNodeType() == "Protein") {
						pathwayEntity.addProperty(RDF.type,
								Biopax_level3.Protein);
					}
					if (pwElm.getDataNodeType() == "Complex") {
						pathwayEntity.addProperty(RDF.type,
								Biopax_level3.Complex);
					}
					if (pwElm.getDataNodeType() == "RNA") {
						pathwayEntity.addProperty(RDF.type, Biopax_level3.Rna);
					}

					// In GPML an Interacting pathway is modeled as a DataNode.
					// Model an interaction is with RDFS.domain and RDFS.range
					if (pwElm.getDataNodeType().equals("Pathway")) {
						Resource interactingPathwayResource = model
								.createResource(pathwayResourceURI
										+ "Interaction/" + pwElm.getGraphId());
						interactingPathwayResource.addProperty(RDFS.domain,
								Biopax_level3.Interaction);
						interactingPathwayResource.addProperty(RDFS.range,
								pathwayResource);
						interactingPathwayResource.addProperty(
								RDFS.range,
								model.createResource(nsWikipathways
										+ "Pathway/" + pwElm.getXref()));
					}
				}
				if (pwElm.getObjectType().equals(ObjectType.LINE)) {
					Resource pathwayLine = model
							.createResource(pathwayResourceURI + "Interaction/"
									+ pwElm.getGraphId());
					pathwayLine.addProperty(RDFS.domain,
							Biopax_level3.Interaction);
					if (((pathway.getGroupIds().contains(
							pwElm.getStartGraphRef()) || (pathway.getGraphIds()
							.contains(pwElm.getStartGraphRef()))) && ((pathway
							.getGroupIds().contains(pwElm.getEndGraphRef())) || (pathway
							.getGraphIds().contains(pwElm.getEndGraphRef()))))) {
						String startGroupOrDatanode;
						String endGroupOrDatanode;
						if (pathway.getGroupIds().contains(
								pwElm.getStartGraphRef())) {
							startGroupOrDatanode = "/Group/";
						} else {
							startGroupOrDatanode = "/Datanode/";
						}
						if (pathway.getGroupIds().contains(
								pwElm.getEndGraphRef())) {
							endGroupOrDatanode = "/Group/";
						} else {
							endGroupOrDatanode = "/Datanode/";
						}
						pathwayLine.addProperty(
								RDFS.range,
								model.getResource(pathwayResourceURI
										+ startGroupOrDatanode
										+ pwElm.getStartGraphRef()));
						pathwayLine.addProperty(
								RDFS.range,
								model.getResource(pathwayResourceURI
										+ endGroupOrDatanode
										+ pwElm.getEndGraphRef()));
					}
				}
				if (pwElm.getObjectType() == ObjectType.STATE) {
					Resource pathwayEntity = model
							.createResource(pathwayResourceURI + "/State/"
									+ pwElm.getGraphId());
					pathwayEntity
							.addProperty(DCTerms.isPartOf, pathwayResource);
					pathwayEntity.addProperty(RDFS.label, pwElm.getTextLabel());
				}
			}

			// Get the Biopax References
			NodeList bpRef = doc.getElementsByTagName("BiopaxRef");
			HashMap<String, String> bpRefmap = new HashMap<String, String>();
			if (bpRef != null && bpRef.getLength() > 0) {

				for (int j = 0; j < bpRef.getLength(); j++) {
					if (bpRef.item(j).getParentNode().getNodeName()
							.equals("DataNode")) {
						bpRefmap.put(
								bpRef.item(j).getTextContent(),
								pathwayResourceURI
										+ "/Datanode/"
										+ bpRef.item(j).getParentNode()
												.getAttributes()
												.getNamedItem("GraphId")
												.getNodeValue());
					}
					if (bpRef.item(j).getParentNode().getNodeName()
							.equals("Pathway")) {
						bpRefmap.put(bpRef.item(j).getTextContent(),
								pathwayResourceURI);
					}
					if (bpRef.item(j).getParentNode().getNodeName()
							.equals("Line")) {
						// TODO make sure every entity has a graphId
						if (bpRef.item(j).getParentNode().getAttributes()
								.getNamedItem("GraphId") != null)
							bpRefmap.put(
									bpRef.item(j).getTextContent(),
									pathwayResourceURI
											+ "/Line/"
											+ bpRef.item(j).getParentNode()
													.getAttributes()
													.getNamedItem("GraphId")
													.getNodeValue());
					}
					if (bpRef.item(j).getParentNode().getNodeName()
							.equals("State")) {
						bpRefmap.put(
								bpRef.item(j).getTextContent(),
								pathwayResourceURI
										+ "/State/"
										+ bpRef.item(j).getParentNode()
												.getAttributes()
												.getNamedItem("GraphId")
												.getNodeValue());
					}
					if (bpRef.item(j).getParentNode().getNodeName()
							.equals("Group")) {
						bpRefmap.put(
								bpRef.item(j).getTextContent(),
								pathwayResourceURI
										+ "/Group/"
										+ bpRef.item(j).getParentNode()
												.getAttributes()
												.getNamedItem("GroupId")
												.getNodeValue());
					}
				}
			}
			NodeList nl = doc.getElementsByTagName("bp:PublicationXref");

			if (nl != null && nl.getLength() > 0) {
				for (int k = 0; k < nl.getLength(); k++) {
					NodeList refId = nl.item(k).getChildNodes();
					if (refId.getLength() > 3) {
						if (refId.item(3).getTextContent().equals("PubMed")
								&& (refId.item(1).getTextContent() != null)) {
							Resource pubmedEntity = model
									.createResource("http://www.ncbi.nlm.nih.gov/pubmed/"
											+ refId.item(1).getTextContent());
							pubmedEntity.addProperty(RDF.type,
									Biopax_level3.Evidence);
							pubmedEntity.addProperty(DCTerms.identifier, refId
									.item(1).getTextContent());
							if (bpRefmap.get(nl.item(k).getAttributes().item(0)
									.getNodeValue()) != null) {
								Resource tempItem = model
										.createResource(bpRefmap
												.get(nl.item(k).getAttributes()
														.item(0).getNodeValue())
												.toString());
								tempItem.addProperty(
										DCTerms.bibliographicCitation,
										pubmedEntity);
							}

						}
					} else {
						System.out.println("PROBLEM with: " + wpIdentifier);
					}
				}

			}
			else {
				 //report warning or something
				}
				
		}
		
		}
		return model;

	}

	public static void main(String[] args) {
		// The identifer of the pathway under scrutiny
		String wpIdentifier;
		String wpRevision;
		if (args.length == 0) {
			wpIdentifier = "WP1763";
		} else {
			wpIdentifier = args[0];
		}
		// Initiate the model
		Model model = ModelFactory.createDefaultModel();
		try {
			model = addPathway2Rdf(wpIdentifier, model);
			FileOutputStream fout;
			fout = new FileOutputStream("/tmp/" + wpIdentifier + ".rdf");
			model.write(fout, "N-TRIPLE");
		} catch (DOMException e1) {
			// TODO Auto-generated catch block
			e1.printStackTrace();
		} catch (XPathExpressionException e1) {
			// TODO Auto-generated catch block
			e1.printStackTrace();
		} catch (ServiceException e1) {
			// TODO Auto-generated catch block
			e1.printStackTrace();
		} catch (ConverterException e1) {
			// TODO Auto-generated catch block
			e1.printStackTrace();
		} catch (ParserConfigurationException e1) {
			// TODO Auto-generated catch block
			e1.printStackTrace();
		} catch (SAXException e1) {
			// TODO Auto-generated catch block
			e1.printStackTrace();
		} catch (IOException e1) {
			// TODO Auto-generated catch block
			e1.printStackTrace();
		} catch (ParseException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}

	}

}

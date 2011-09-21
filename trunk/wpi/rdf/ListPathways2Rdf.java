import java.io.*;
import java.net.URL;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Hashtable;
import java.util.List;
import java.util.UUID;

import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;

import org.bridgedb.Xref;
import org.bridgedb.bio.BioDataSource;
import org.pathvisio.data.GdbManager;
import org.pathvisio.model.ConverterException;
import org.pathvisio.model.DataNodeType;
import org.pathvisio.model.ObjectType;
import org.pathvisio.model.Pathway;
import org.pathvisio.model.PathwayElement;
import org.pathvisio.wikipathways.webservice.WSPathway;
import org.pathvisio.wikipathways.webservice.WSPathwayInfo;
import org.pathvisio.wikipathways.webservice.WSSearchResult;
import org.pathvisio.wikipathways.WikiPathwaysClient;
import org.w3c.dom.Document;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;


import com.hp.hpl.jena.query.Query;
import com.hp.hpl.jena.query.QueryExecution;
import com.hp.hpl.jena.query.QueryExecutionFactory;
import com.hp.hpl.jena.query.QueryFactory;
import com.hp.hpl.jena.query.QuerySolution;
import com.hp.hpl.jena.query.ResultSet;
import com.hp.hpl.jena.rdf.model.Model;
import com.hp.hpl.jena.rdf.model.ModelFactory;
import com.hp.hpl.jena.rdf.model.Property;
import com.hp.hpl.jena.rdf.model.RDFNode;
import com.hp.hpl.jena.rdf.model.Resource;
import com.hp.hpl.jena.rdf.model.Statement;
import com.hp.hpl.jena.vocabulary.DC;
import com.hp.hpl.jena.vocabulary.DCTerms;
import com.hp.hpl.jena.vocabulary.RDF;
import com.hp.hpl.jena.vocabulary.RDFS;

public class ListPathways2Rdf {

	/**
	 * @param args
	 */
	public static void main(String[] args) {
		try {
			// Create a client to the WikiPathways web service
			List<String> blockList=new ArrayList<String>();
			WikiPathwaysClient client = new WikiPathwaysClient(
					new URL(
							"http://www.wikipathways.org/wpi/webservice/webservice.php"));
			String wpIdentifier;

			/* if (args.length == 0) {
				wpIdentifier = "WP716";
			} else {
				wpIdentifier = args[0];
			}
			*/
			String nsWikipathways = "http://www.wikipathways.org/#";
			String nsGenmapp = "http://www.genmapp.org/#";
			String nsDwc = "http://rs.tdwg.org/dwc/terms/";
			

			
			WSPathwayInfo[] pathwayList = client.listPathways();
			Model model = ModelFactory.createDefaultModel();
			 for(WSPathwayInfo pathwaylist : pathwayList) {
				 try{
				wpIdentifier = pathwaylist.getId();
			//	 wpIdentifier = "WP1380";
				 
				if (!blockList.contains(wpIdentifier)){
				System.out.println(wpIdentifier);
				WSPathwayInfo pathwayInfo = client.getPathwayInfo(wpIdentifier);
				
				String pathwayURI = "http://www.wikipathways.org/pathway/"
						+ wpIdentifier;
				String pathwayName = pathwayInfo.getName();
				String pathwaySpecies = pathwayInfo.getSpecies();
				WSPathway wsPathway = client.getPathway(wpIdentifier);
				Pathway pathway = WikiPathwaysClient.toPathway(wsPathway);
				Writer output = null;
				File tempFile = new File("rdfoutput/"+UUID.randomUUID()+".gpml");
				System.out.println(tempFile.getName());
				output = new OutputStreamWriter(new FileOutputStream(tempFile), "UTF-8");
				output.write(wsPathway.getGpml());
				output.close();
				DocumentBuilderFactory docBuilderFactory = DocumentBuilderFactory.newInstance();
				DocumentBuilder docBuilder = docBuilderFactory.newDocumentBuilder();
				Document doc = docBuilder.parse (tempFile);
				doc.getDocumentElement ().normalize ();
                NodeList pathwayLicense = doc.getElementsByTagName("Pathway");

				NodeList bpRef = doc.getElementsByTagName("BiopaxRef");
				HashMap<String, String> bpRefmap = new HashMap<String, String>();
				if(bpRef != null && bpRef.getLength() > 0) {
					
					for(int i = 0 ; i < bpRef.getLength();i++) {
						System.out.println(bpRef.item(i).getParentNode().getNodeName());
						if (bpRef.item(i).getParentNode().getNodeName().equals("DataNode")){
							bpRefmap.put(bpRef.item(i).getTextContent(),nsWikipathways +"/"+wpIdentifier+ "/Datanode/"+ bpRef.item(i).getParentNode().getAttributes().getNamedItem("GraphId").getNodeValue());
						}
						if (bpRef.item(i).getParentNode().getNodeName().equals("Pathway")){
							bpRefmap.put(bpRef.item(i).getTextContent(),nsWikipathways + "/"+ wpIdentifier+"/");
						}
						if (bpRef.item(i).getParentNode().getNodeName().equals("Line")){
							bpRefmap.put(bpRef.item(i).getTextContent(),nsWikipathways +"/"+wpIdentifier+ "/Line/"+ bpRef.item(i).getParentNode().getAttributes().getNamedItem("GraphId").getNodeValue());
						}
						if (bpRef.item(i).getParentNode().getNodeName().equals("State")){
							bpRefmap.put(bpRef.item(i).getTextContent(),nsWikipathways +"/"+wpIdentifier+ "/State/"+ bpRef.item(i).getParentNode().getAttributes().getNamedItem("GraphId").getNodeValue());
						}
						if (bpRef.item(i).getParentNode().getNodeName().equals("Group")){
							bpRefmap.put(bpRef.item(i).getTextContent(),nsWikipathways +"/"+wpIdentifier+ "/Group/"+ bpRef.item(i).getParentNode().getAttributes().getNamedItem("GroupId").getNodeValue());
						}
					}
    			}

			// namespaces



			// predicates
			Property hasSpecies = model.createProperty(nsWikipathways,
					"species");
			Property hasName = model.createProperty(nsWikipathways, "hasName");
			Property hasGpmlObjectType = model.createProperty(nsGenmapp,
					"hasObject");
			Property hasXref = model.createProperty(nsWikipathways, "xref");
			Property rdfHasDataNodeType = model.createProperty(nsWikipathways,
					"DataNodeType");
			Property hasRoleIn = model.createProperty(nsWikipathways,
					"HasRoleIn");
			Property isGpmlDataNode = model.createProperty(nsGenmapp,
					"DataNode");
			Property scientificName = model.createProperty(nsDwc,
					"scientificName");


			
			for (String groupRef : pathway.getGroupIds()){
				Resource groupResource = model.createResource(nsWikipathways+"/"+wpIdentifier+"/Group/"+groupRef);
				groupResource.addProperty(RDF.type, model.getResource(nsGenmapp+"/Group/"));
				groupResource.addProperty(DC.identifier, groupRef);
			}
			
			

			Resource pathwayResource = model.createResource(pathwayURI);
			Resource wikipathwaysResource = model.createResource("http://www.wikipathways.org");
			Resource wikiPathwaysPaperResource = model.createResource("http://www.ncbi.nlm.nih.gov/pubmed/18651794");
			
			wikipathwaysResource.addProperty(DCTerms.bibliographicCitation, wikiPathwaysPaperResource);

			// Get all genes, proteins and metabolites for a pathway

			pathwayResource.addProperty(hasSpecies, pathwaySpecies);
			pathwayResource.addProperty(RDF.type, Biopax_level3.Pathway);
			pathwayResource.addProperty(RDFS.label, pathwayName);
			pathwayResource.addProperty(DCTerms.license, pathwayLicense.item(0).getAttributes().getNamedItem("License").getNodeValue());
			// pathwayResource.addProperty(Biopax_level3.BIOPAX3,
			// interactionName);
			pathwayResource.addProperty(DC.identifier, wpIdentifier);
			pathwayResource.addProperty(DC.publisher,
					"http://www.wikipathways.org");
			pathwayResource.addProperty(scientificName, pathwaySpecies);
			pathwayResource.addProperty(DCTerms.isPartOf, "http://www.wikipathways.org");
			//pathwayResrouce.addProperty(DCTerms.bibliographicCitation, )

			model.setNsPrefix("nsWikipathways", nsWikipathways);
			model.setNsPrefix("species", nsWikipathways + "/species/");
			model.setNsPrefix("genmapp", nsGenmapp);
			model.setNsPrefix("bibo", bibo.NS);
			model.setNsPrefix("biopax3", Biopax_level3.BIOPAX3);
	
			model.setNsPrefix("dwc", nsDwc);
			model.setNsPrefix("dcterms", DCTerms.getURI());
			for (PathwayElement pwElm : pathway.getDataObjects()) {
				// Only take elements with type DATANODE (genes, proteins,
				// metabolites)

				if (pwElm.getObjectType() == ObjectType.DATANODE) {

					Resource pathwayEntity = model
							.createResource(nsWikipathways+"/"+wpIdentifier + "/Datanode/"
									+ pwElm.getGraphId());
					pathwayEntity
							.addProperty(DCTerms.isPartOf, pathwayResource);
					pathwayEntity.addProperty(RDFS.label, pwElm.getTextLabel());
					pathwayEntity.addProperty(RDF.type, Biopax_level3.Entity);
					pathwayEntity.addProperty(rdfHasDataNodeType,
							pwElm.getDataNodeType());
					pathwayEntity.addProperty(DC.identifier, pwElm.getGeneID());
					if (pwElm.getGroupRef() != null){ //Element is part of a group
						pathwayEntity.addProperty(DCTerms.isPartOf, model.getResource(nsWikipathways+"/"+wpIdentifier+"/Group/"+pwElm.getGroupRef()));
					}
					//pathwayEntity.addProperty(DC.source, pwElm.getDataSource().toString());
					if (pwElm.getDataNodeType() == "Metabolite"){
						pathwayEntity.addProperty(RDF.type, Biopax_level3.ChemicalStructure);
					}
					if (pwElm.getDataNodeType().equals("Pathway")){
						System.out.println(pwElm.getDataNodeType());
						
						Resource interactingPathwayResource = model.createResource(nsWikipathways+"/"+wpIdentifier+"/PathwayInteraction/"+pwElm.getGraphId());
						interactingPathwayResource.addProperty(RDFS.domain, Biopax_level3.Interaction);
						interactingPathwayResource.addProperty(RDFS.range, pathwayResource);
						interactingPathwayResource.addProperty(RDFS.range, model.createResource("http://www.wikipathways.org/index.php/Pathway:"+pwElm.getXref()));
					}
				}
				if (pwElm.getObjectType().equals(ObjectType.LINE)){
					Resource pathwayLine = model.createResource(nsWikipathways +"/"+wpIdentifier+ "/Line/"+ pwElm.getGraphId());
					pathwayLine.addProperty(RDFS.domain, Biopax_level3.Interaction);
					if (((pathway.getGroupIds().contains(pwElm.getStartGraphRef())||(pathway.getGraphIds().contains(pwElm.getStartGraphRef()))) &&
							((pathway.getGroupIds().contains(pwElm.getEndGraphRef()))||(pathway.getGraphIds().contains(pwElm.getEndGraphRef()))))){
						String startGroupOrDatanode;
						String endGroupOrDatanode;
						if (pathway.getGroupIds().contains(pwElm.getStartGraphRef())){
				    		startGroupOrDatanode = "/Group/";
				    	}
				    	else {
				    		startGroupOrDatanode = "/Datanode/";
				    	}
				    	if (pathway.getGroupIds().contains(pwElm.getEndGraphRef())){
					    	endGroupOrDatanode = "/Group/";
					    }
					   	else {
					   		endGroupOrDatanode = "/Datanode/";
					   	}
						pathwayLine.addProperty(RDFS.range, model.getResource(nsWikipathways+"/"+wpIdentifier+startGroupOrDatanode+pwElm.getStartGraphRef()));
				    		pathwayLine.addProperty(RDFS.range, model.getResource(nsWikipathways+"/"+wpIdentifier+endGroupOrDatanode+pwElm.getEndGraphRef()));
				    }
				}
				
				
				if (pwElm.getObjectType() == ObjectType.LABEL) {
					//System.out.println("Label found");
					Resource pathwayEntity = model
							.createResource(nsWikipathways +"/"+wpIdentifier+ "/Label/"
									+ pwElm.getGraphId());
					pathwayEntity
							.addProperty(DCTerms.isPartOf, pathwayResource);
					pathwayEntity.addProperty(RDFS.label, pwElm.getTextLabel());
					pathwayEntity.addProperty(RDF.type, RDFS.comment);
					//pathwayEntity.addProperty(DC.source, pwElm.getDataSource().toString());
				}
				if (pwElm.getObjectType() == ObjectType.STATE) {
					//System.out.println("Label found");
					Resource pathwayEntity = model
							.createResource(nsWikipathways +"/"+wpIdentifier+ "/State/"
									+ pwElm.getGraphId());
					pathwayEntity
							.addProperty(DCTerms.isPartOf, pathwayResource);
					pathwayEntity.addProperty(RDFS.label, pwElm.getTextLabel());
					
					//pathwayEntity.addProperty(DC.source, pwElm.getDataSource().toString());
				}
				
			}
		//System.out.println(wsPathway.getGpml());

		

         
			
            NodeList nl = doc.getElementsByTagName("bp:PublicationXref");
            
            if(nl != null && nl.getLength() > 0) {
    			for(int i = 0 ; i < nl.getLength();i++) {
    				//System.out.println("xRef: "+nl.item(i).getAttributes().item(0).getNodeValue());
    				NodeList refId=nl.item(i).getChildNodes();
    				if (refId.getLength()>3){
    				if (refId.item(3).getTextContent().equals("PubMed") && (refId.item(1).getTextContent() != null) ){
    					Resource pubmedEntity = model.createResource("http://www.ncbi.nlm.nih.gov/pubmed/"+refId.item(1).getTextContent());
    					pubmedEntity.addProperty(DCTerms.identifier, refId.item(1).getTextContent());
    					System.out.println(bpRefmap.get(nl.item(i).getAttributes().item(0).getNodeValue()).toString());
    					System.out.println("Lees: "+bpRefmap.get(nl.item(i).getAttributes().item(0).getNodeValue()).toString());
    					Resource tempItem =model.createResource(bpRefmap.get(nl.item(i).getAttributes().item(0).getNodeValue()).toString());
    					
    					tempItem.addProperty(DCTerms.bibliographicCitation, pubmedEntity);
    					
    				}
    				//System.out.println(refId.item(3).getNodeName() + ": "+refId.item(3).getTextContent());
    				//System.out.println(refId.item(1).getNodeName()+": "+refId.item(1).getTextContent());
    			}else
    			{
    			   System.out.println("PROBLEM with: "+wpIdentifier);	
    			}
    			}
    			
            }
            FileOutputStream fout = new FileOutputStream("rdfoutput/"+wpIdentifier+".rdf");
			model.write(fout, "N-TRIPLE");
			} 
				 } catch (Exception e) {
					 System.out.println("ERROR IN: "+  pathwaylist.getId());
				e.printStackTrace();
			}
				 
			}
			FileOutputStream foutEnd = new FileOutputStream("rdfoutput/wikipathways.rdf");
			model.write(foutEnd, "N-TRIPLE");
			//model.write(fout);
		} catch (Exception e) {
			e.printStackTrace();
		}

	}

}

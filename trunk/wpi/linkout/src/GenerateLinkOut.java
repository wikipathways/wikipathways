import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileWriter;
import java.io.IOException;
import java.net.URL;
import java.util.Collection;
import java.util.HashMap;
import java.util.HashSet;
import java.util.List;
import java.util.Map;
import java.util.Set;
import java.util.logging.Level;
import java.util.logging.Logger;

import org.bridgedb.DataSource;
import org.bridgedb.IDMapper;
import org.bridgedb.IDMapperException;
import org.bridgedb.Xref;
import org.bridgedb.bio.BioDataSource;
import org.bridgedb.bio.Organism;
import org.bridgedb.rdb.GdbProvider;
import org.jdom.DocType;
import org.jdom.Document;
import org.jdom.Element;
import org.jdom.output.Format;
import org.jdom.output.XMLOutputter;
import org.kohsuke.args4j.CmdLineException;
import org.kohsuke.args4j.CmdLineParser;
import org.kohsuke.args4j.Option;
import org.pathvisio.model.ConverterException;
import org.pathvisio.model.Pathway;
import org.pathvisio.wikipathways.WikiPathwaysCache;
import org.pathvisio.wikipathways.WikiPathwaysClient;
import org.pathvisio.wikipathways.webservice.WSPathwayInfo;


public class GenerateLinkOut {
	private static final Logger log = Logger.getLogger(GenerateLinkOut.class.getName());
	
	private String providerId;
	private int linkId;
	private String urlBase;
	private GdbProvider idmp;
	
	WikiPathwaysCache cache;
	
	public GenerateLinkOut(WikiPathwaysCache cache, GdbProvider idmp) {
		this.cache = cache;
		this.idmp = idmp;
	}
	
	public void setProviderId(String providerId) {
		this.providerId = providerId;
	}
	
	public void setUrlBase(String urlBase) {
		this.urlBase = urlBase;
	}
	
	public Document createLinkOuts(Collection<File> pathwayFiles, DataSource tgtDs, String database) throws IDMapperException, FileNotFoundException, IOException, ConverterException {
		linkId = 0; //Reset link id for this linkset
		
		Document doc = new Document();
		
		DocType doctype = new DocType("LinkSet", 
				 "-//NLM//DTD LinkOut 1.0//EN", "http://www.ncbi.nlm.nih.gov/projects/linkout/doc/LinkOut.dtd");
		doc.setDocType(doctype);
		
		Element root = new Element("LinkSet");
		doc.setRootElement(root);
		
		//For each pathway
		int i = 0;
		for(File f : pathwayFiles) {
			if(i % 10 == 0) log.info("Processing pathway " + ++i + " out of " + pathwayFiles.size());
			Pathway p = new Pathway();
			p.readFromXml(f, false);
			
			//Gather the mapped xrefs
			Set<Xref> xrefs = new HashSet<Xref>();
			List<IDMapper> idms = idmp.getGdbs(Organism.fromLatinName(p.getMappInfo().getOrganism()));
			for(IDMapper idm : idms) {
				for(Set<Xref> xx : idm.mapID(new HashSet<Xref>(p.getDataNodeXrefs()), tgtDs).values()) {
					xrefs.addAll(xx);
				}
			}
			if(xrefs.size() == 0) continue;
			
			Element link = new Element("Link");
			root.addContent(link);
			
			Element lid = new Element("LinkId");
			lid.setText("" + linkId++);
			link.addContent(lid);
			
			Element pid = new Element("ProviderId");
			pid.setText(providerId);
			link.addContent(pid);
			
			Element objSel = new Element("ObjectSelector");
			link.addContent(objSel);
			Element objDb = new Element("Database");
			objDb.setText(database);
			objSel.addContent(objDb);
			
			Element objList = new Element("ObjectList");
			objSel.addContent(objList);
			
			for(Xref x : xrefs) {
				Element objId = new Element("ObjId");
				objId.setText(x.getId());
				objList.addContent(objId);
			}
			
			WSPathwayInfo info = cache.getPathwayInfo(f);
			
			Element objUrl = new Element("ObjectUrl");
			link.addContent(objUrl);

			Element base = new Element("Base");
			objUrl.addContent(base);
			base.setText(urlBase);
			Element rule = new Element("Rule");
			objUrl.addContent(rule);
			rule.setText("/index.php/Pathway:" + info.getId());
			Element urlName = new Element("UrlName");
			objUrl.addContent(urlName);
			urlName.setText(info.getName());
		}
		return doc;
	}
	
	public static void main(String[] args) {
		try {
			Args pargs = new Args();
			CmdLineParser parser = new CmdLineParser(pargs);
			try {
				parser.parseArgument(args);
			} catch(CmdLineException c) {
				parser.printUsage(System.err);
				System.exit(-1);
			}

			BioDataSource.init();
			//Class.forName("org.bridgedb.rdb.IDMapperRdb");
			
			log.info("Connecting to idmappers");
			GdbProvider idmp = GdbProvider.fromConfigFile(pargs.idmConfigFile);
			
			log.info("Updating pathways from " + pargs.baseUrl);
			WikiPathwaysClient client = new WikiPathwaysClient(new URL(
					pargs.baseUrl + "/wpi/webservice/webservice.php"
			));
			WikiPathwaysCache cache = new WikiPathwaysCache(client, pargs.cacheFile);
			cache.update();
			
			GenerateLinkOut linkout = new GenerateLinkOut(cache, idmp);
			linkout.setProviderId(pargs.provId);
			linkout.setUrlBase(pargs.baseUrl);
			Map<DataSource, String> dsm = new HashMap<DataSource, String>();
			dsm.put(BioDataSource.ENTREZ_GENE, "Gene");
			dsm.put(BioDataSource.PUBCHEM, "PCCompound");
			
			for(DataSource ds : dsm.keySet()) {
				Document doc = linkout.createLinkOuts(cache.getFiles(), ds, dsm.get(ds));
				XMLOutputter out = new XMLOutputter(Format.getPrettyFormat());
	            FileWriter writer = new FileWriter(new File(pargs.outFile, dsm.get(ds) + ".xml"));
	            out.output(doc, writer);
	            writer.flush();
	            writer.close();
			}
		} catch(Exception e) {
			log.log(Level.SEVERE, "Fatal error", e);
		}
	}
	
	private static class Args {
		@Option(name = "-out", required = true, usage = "The directory to write the LinkOut files to.")
		File outFile;
		
		@Option(name = "-baseUrl", usage = "The base url of wikipathways (e.g. 'http://www.wikipathways.org'.")
		String baseUrl = "http://www.wikipathways.org";
		
		@Option(name = "-cache", required = true, usage = "The directory to store the pathways cache.")
		File cacheFile;
		
		@Option(name = "-idmConfig", required = true, usage = "The bridgedb configuration file.")
		File idmConfigFile;
		
		@Option(name = "-provId", usage = "The provider id to use in the LinkOut file.")
		String provId = "1234";
	}
}

package org.wikipathways;

import java.io.File;
import java.io.PrintWriter;
import java.net.URL;
import java.util.ArrayList;
import java.util.Collection;
import java.util.HashMap;
import java.util.HashSet;
import java.util.List;
import java.util.Map;
import java.util.Set;
import java.util.logging.Level;
import java.util.logging.Logger;

import org.bridgedb.IDMapper;
import org.bridgedb.IDMapperStack;
import org.bridgedb.Xref;
import org.bridgedb.bio.Organism;
import org.bridgedb.rdb.GdbProvider;
import org.kohsuke.args4j.CmdLineException;
import org.kohsuke.args4j.CmdLineParser;
import org.kohsuke.args4j.Option;
import org.pathvisio.model.Pathway;
import org.pathvisio.wikipathways.WikiPathwaysCache;
import org.pathvisio.wikipathways.WikiPathwaysClient;
import org.pathvisio.wikipathways.webservice.WSPathwayInfo;

/**
 * Calculates relation scores for pathway pairs on WikiPathways, based
 * on the number of shared xrefs.
 * 
 * Optionally also creates or updates the xref index.
 */
public class CalculateRelationScores {
	private final static Logger log = Logger.getLogger(CalculateRelationScores.class.getName());
	
	/**
	 * Calculate the relation score.
	 * @param pathway1	The identifier of the first pathway
	 * @param xrefs1	The original (unmapped) xrefs on the first pathway
	 * @param mapped1	The mapped xrefs of the first pathway (used to lookup shared xrefs by taking into account identifier mapping)
	 * @param pathway2	The identifier of the second pathway
	 * @param xrefs2	The original (unmapped) xrefs on the first pathway
	 * @param mapped2	The mapped xrefs of the second pathway (used to lookup shared xrefs by taking into account identifier mapping)
	 * @return
	 */
	public static RelationScore getScore(String pathway1, Set<Xref> xrefs1, Set<Xref> mapped1, String pathway2, Set<Xref> xrefs2, Set<Xref> mapped2) {
		log.fine("Finding relation scores for " + pathway1 + " - " + pathway2);
		
		RelationScore s = new RelationScore();
		s.xrefs1 = xrefs1.size();
		s.xrefs2 = xrefs2.size();
		s.pathway1 = pathway1;
		s.pathway2 = pathway2;
		
		//Use the smallest pathway as reference and largest as mapped
		Set<Xref> mapped = null;
		Set<Xref> unmapped = null;
		if(xrefs1.size() > xrefs2.size()) {
			mapped = mapped1;
			unmapped = xrefs2;
		} else {
			mapped = mapped2;
			unmapped = xrefs1;
		}
		
		for(Xref x : unmapped) {
			if(mapped.contains(x)) s.sharedXrefs++;
		}
		
		Set<Xref> totalXrefs = new HashSet<Xref>(xrefs1);
		totalXrefs.addAll(xrefs2);
		
		s.totalXrefs = totalXrefs.size();
		
		return s;
	}
	
	public static void main(String[] args) {
		try {
			Args pargs = new Args();
			CmdLineParser parser = new CmdLineParser(pargs);
			try {
				parser.parseArgument(args);
			} catch(CmdLineException c) {
				log.log(Level.SEVERE, c.getMessage(), c);
				c.printStackTrace();
				parser.printUsage(System.err);
				System.exit(-1);
			}
			
			//Connect to the xref index
			XrefIndex index = new XrefIndex(pargs.indexLocation);
			index.connect();
			
			log.info("Creating wikipathways client and cache");
			WikiPathwaysClient wpclient = new WikiPathwaysClient(new URL(pargs.wpUrl));
			WikiPathwaysCache wpcache = new WikiPathwaysCache(wpclient, pargs.wpCache);
			
			//Connect to the idmappers
			log.info("Connecting to idmappers");
			GdbProvider gdbprov = GdbProvider.fromConfigFile(pargs.bridge);
			Map<String, IDMapper> idmappersBySpecies = new HashMap<String, IDMapper>();
			for(Organism o : Organism.values()) {
				IDMapperStack idm = new IDMapperStack();
				idmappersBySpecies.put(o.latinName(), idm);
				for(IDMapper i : gdbprov.getGdbs(o)) idm.addIDMapper(i);
			}

			int commitInterval = 25; //Commit once every 25 pathways
			if(pargs.createIndex) {
				index.getConnection().setAutoCommit(false);
				
				log.info("Creating xref index");
				wpcache.update();
				//Clear the xref index
				index.clear();
				//Update all pathways
				List<File> files = wpcache.getFiles();
				int i = 1;
				for(File f : files) {
					log.info("Processing pathway " + i + " out of " + files.size());
					Pathway p = new Pathway();
					p.readFromXml(f, false);
					String species = p.getMappInfo().getOrganism();
					index.update(
							wpcache.getPathwayInfo(f).getId(), 
							p, idmappersBySpecies.get(species));
					
					if(i % commitInterval == 0) {
						log.info("Committing to database");
						index.getConnection().commit();
					}
					i++;
				}
				
				index.getConnection().commit();
				index.getConnection().setAutoCommit(true);
				
				index.createIndices();
				//Reset connection after creation
				index.disconnect();
				index.connect();
			} else if(pargs.updateIndex) {
				index.getConnection().setAutoCommit(false);
				
				log.info("Updating xref index");
				//Update the changed pathways
				int i = 1;
				Collection<File> toUpdate = wpcache.update();
				for(File f : toUpdate) {
					log.info("Processing pathway " + i + " out of " + toUpdate.size());
					
					Pathway p = new Pathway();
					p.readFromXml(f, true);
					String species = p.getMappInfo().getOrganism();
					index.update(
							wpcache.getPathwayInfo(f).getId(), 
							p, idmappersBySpecies.get(species));
					if(i % commitInterval == 0) {
						log.info("Committing to database");
						index.getConnection().commit();
					}
					i++;
					
					index.getConnection().commit();
					index.getConnection().setAutoCommit(true);
				}
			}
			
			//List the pathways and calculate scores
			List<RelationScore> scores = new ArrayList<RelationScore>();
			
			//Collect the pathway info
			List<WSPathwayInfo> pathways = new ArrayList<WSPathwayInfo>();
			for(File f : wpcache.getFiles()) pathways.add(wpcache.getPathwayInfo(f));
			
			//Calculate the scores per species
			Map<String, List<WSPathwayInfo>> pathwaysBySpecies = new HashMap<String, List<WSPathwayInfo>>();
			for(WSPathwayInfo p : pathways) {
				List<WSPathwayInfo> pws = pathwaysBySpecies.get(p.getSpecies());
				if(pws == null) pathwaysBySpecies.put(p.getSpecies(), pws = new ArrayList<WSPathwayInfo>());
				pws.add(p);
			}
			
			for(String species : pathwaysBySpecies.keySet()) {
				log.info("Calculating scores for " + species);
				List<WSPathwayInfo> pws = pathwaysBySpecies.get(species);
				for(int i = 0; i < pws.size(); i++) {
					String pathway1 = pws.get(i).getId();
					log.fine("Processing pathway " + pathway1 + " (" + i + " out of " + pws.size() + ")");
					Set<Xref> xrefs1 = index.getOriginalXrefs(pathway1);
					Set<Xref> all1 = index.getAllXrefs(pathway1);
					for(int j = i + 1; j < pws.size(); j++) {
						String pathway2 = pws.get(j).getId();
						Set<Xref> xrefs2 = index.getOriginalXrefs(pathway2);
						Set<Xref> all2 = new HashSet<Xref>();
						if(xrefs2.size() > xrefs2.size()) index.getAllXrefs(pathway2);
						scores.add(getScore(pathway1, xrefs1, all1, pathway2, xrefs2, all2));
					}
				}
			}
			
			index.disconnect();
			
			//Write the scores to a text file
			log.info("Writing results to " + pargs.out);
			PrintWriter out = new PrintWriter(pargs.out);
			
			//Write header
			out.println(
				"Pathway 1\tPathway 2\tNr shared xrefs\t" +
				"Nr xrefs pathway 1\tNr xrefs pathway 2\tNr unique xrefs both pathways"	
			);
			for(RelationScore s : scores) {
				out.println(
					s.pathway1 + "\t" + s.pathway2 + "\t" + s.sharedXrefs + "\t" +
					s.xrefs1 + "\t" + s.xrefs2 + "\t" + s.totalXrefs
				);
			}
			out.close();
		} catch(Throwable t) {
			log.log(Level.SEVERE, t.getMessage(), t);
			t.printStackTrace();
			System.exit(-2);
		}
	}
	
	private static class Args {
		@Option(name = "-wikipathwaysServiceUrl", usage = "The url to the wikipathways web service.")
		String wpUrl = "http://www.wikipathways.org/wpi/webservice/webservice.php";
		
		@Option(name = "-index", required = true, usage = "The JDBC connection string to the xref index database.")
		String indexLocation;
		
		@Option(name = "-bridge", required = true, usage = "The bridgedb configuration file.")
		File bridge;
		
		@Option(name = "-out", required = true, usage = "The file to write the scores to.")
		File out;
		
		@Option(name = "-wikipathwaysCache", required = true, usage = "The path to the wikipathways cache.")
		File wpCache;
		
		@Option(name = "-updateIndex", usage = "If this parameter is specified, the wikipathways cache and xref index will be updated before the scores are calculated.")
		boolean updateIndex;
		
		@Option(name = "-createIndex", usage = "If this parameter is specified, the xref index will be created from scratch.")
		boolean createIndex;
	}
	
	private static class RelationScore {
		String pathway1;
		String pathway2;
		
		/**
		 * The total number of unique xrefs (unmapped, so original annotation as on pathways)
		 */
		int totalXrefs;
		/**
		 * The number of xrefs on the smallest pathway
		 */
		int xrefs1;
		/**
		 * The number of xrefs on the largest pathway
		 */
		int xrefs2;
		/**
		 * The number of xrefs shared between the pathway (taking into account identifier mapping)
		 */
		int sharedXrefs;
	}
}


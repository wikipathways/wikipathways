package org.wikipathways;

import java.sql.Connection;
import java.sql.DatabaseMetaData;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.HashMap;
import java.util.HashSet;
import java.util.Map;
import java.util.Set;
import java.util.logging.Logger;

import org.bridgedb.DataSource;
import org.bridgedb.IDMapper;
import org.bridgedb.IDMapperException;
import org.bridgedb.Xref;
import org.bridgedb.bio.BioDataSource;
import org.pathvisio.model.Pathway;

/**
 * Maintains an index of xref -> pathway associations for calculating
 * relations between pathways based on xref overlap.
 */
public class XrefIndex {
	private final static Logger log = Logger.getLogger(XrefIndex.class.getName());
	static {
		BioDataSource.init();
	}

	Connection con;
	String location;

	/**
	 * @param location The JDBC connection string to the database that contains the index
	 */
	public XrefIndex(String location) {
		this.location = location;
	}

	/**
	 * Open a connection to the index.
	 * @param location
	 * @throws SQLException 
	 */
	public void connect() throws SQLException {
		log.info("Connecting to " + location);
		if(con != null) {
			log.fine("Trying to connect when connection already open, disconnecting first");
			disconnect();
		}

		con = DriverManager.getConnection (location);
		if(!tablesExist()) {
			log.info("Tables do not exist yet in database, creating...");
			initDatabase();
		}
	}

	/**
	 * Close the database connection to the index
	 * @throws SQLException
	 */
	public void disconnect() throws SQLException {
		statements.clear();
		con.commit();
		con.close();
		con = null;
	}

	protected Connection getConnection() {
		return con;
	}

	/**
	 * Add an association between the xref and pathway.
	 * @param xref	The xref.
	 * @param pathway A string identifying the pathway.
	 * @param mapped True if the association was inferred by mapping the xref to other datasources
	 * @throws SQLException 
	 */
	private void addAssociation(Xref xref, String pathway, boolean mapped) throws SQLException {
		//log.fine("Adding association: " + xref + " -> " + pathway + " (mapped:" + mapped + ")");
		PreparedStatement pst = getPst(QUERY_ADD_ASSOCIATION);
		pst.setString(1, xref.getId());
		pst.setString(2, xref.getDataSource().getSystemCode());
		pst.setString(3, pathway);
		pst.setBoolean(4, mapped);
		pst.execute();
	}

	/**
	 * Clear the pathway -> xrefs associations in the index.
	 * @throws SQLException
	 */
	public void clear() throws SQLException {
		con.createStatement().execute("DELETE FROM " + TABLE_ASSOCIATIONS);
	}

	/**
	 * Update the index for the given pathway
	 * @throws SQLException
	 * @throws IDMapperException
	 */
	public void update(String pathwayId, Pathway pathway, IDMapper idm) throws SQLException, IDMapperException {
		log.info("Updating index for " + pathwayId);

		purgePathway(pathwayId); //First remove existing entries for this pathway

		//Add the xrefs
		Set<Xref> xrefs = new HashSet<Xref>();
		for(Xref x : pathway.getDataNodeXrefs()) {
			//Fix for problem in WP WP1406: trailing whitespace gives db constraint errors
			if(x.getId().endsWith(" ")) {
				x = new Xref(x.getId().trim(), x.getDataSource());
			}
			xrefs.add(x);
		}

		Set<Xref> mapped = new HashSet<Xref>();

		//Add the xrefs
		for(Xref x : xrefs) {
			if(!checkXref(x)) continue;
			addAssociation(x, pathwayId, false); //Add the unmapped association

			mapped.addAll(idm.mapID(x));
		}

		mapped.removeAll(xrefs); //Remove original xrefs from mapped
		
		//Add the mapped associations
		for(Xref mx : mapped) addAssociation(mx, pathwayId, true);
	}

	private boolean checkXref(Xref x) {
		if(x.getId() == null || "".equals(x.getId())) {
			log.fine("Skipping xref " + x + ": missing or empty identifier");
			return false;
		}
		if(x.getDataSource() == null || x.getDataSource().getSystemCode() == null) {
			log.fine("Skipping xref " + x + ": missing datasource");
			return false;
		}
		return true;
	}

	/**
	 * Get the xrefs of the pathway in their original annotation (unmapped).
	 * @throws SQLException
	 */
	public Set<Xref> getOriginalXrefs(String pathway) throws SQLException {
		PreparedStatement pst = getPst(QUERY_GET_XREFS);
		pst.setString(1, pathway);
		pst.setBoolean(2, false);

		Set<Xref> xrefs = new HashSet<Xref>();
		ResultSet r = pst.executeQuery();
		while(r.next()) {
			String id = r.getString(1);
			DataSource ds = DataSource.getBySystemCode(r.getString(2));
			xrefs.add(new Xref(id, ds));
		}
		r.close();
		return xrefs;
	}

	/**
	 * Get the xrefs of the pathway, mapped to all other available datasources.
	 * @throws SQLException
	 */
	public Set<Xref> getAllXrefs(String pathway) throws SQLException {
		PreparedStatement pst = getPst(QUERY_GET_ALL_XREFS);
		pst.setString(1, pathway);

		Set<Xref> xrefs = new HashSet<Xref>();
		ResultSet r = pst.executeQuery();
		while(r.next()) {
			String id = r.getString(1);
			DataSource ds = DataSource.getBySystemCode(r.getString(2));
			xrefs.add(new Xref(id, ds));
		}
		r.close();
		return xrefs;
	}

	/**
	 * Remove all entries for this pathway.
	 * @throws SQLException 
	 */
	public void purgePathway(String pathway) throws SQLException {
		PreparedStatement pst = getPst(QUERY_PURGE_PATHWAY);
		pst.setString(1, pathway);
		pst.execute();
	}

	/**
	 * Initialize the database.
	 * @param location
	 * @throws SQLException 
	 */
	private void initDatabase() throws SQLException {
		createTables();
	}

	private void createTables() throws SQLException {
		Statement sh = con.createStatement();
		sh.execute(
				"CREATE TABLE							" +
				TABLE_ASSOCIATIONS 	+
				" (   id VARCHAR(50) NOT NULL,			" +
				"     code VARCHAR(50) NOT NULL,		" +
				"     pathway VARCHAR(50) NOT NULL,		" +
				"     mapped SMALLINT NOT NULL,			" +
				"     PRIMARY KEY (id, code,			" +
				"		pathway)						" +
		" )										");
	}

	protected void createIndices() throws SQLException {
		log.info("Creating database indices");
		
		Statement sh = con.createStatement();
		sh.execute(
				"CREATE INDEX i_pwmapped" +
				" ON " + TABLE_ASSOCIATIONS + 
				" (pathway, mapped)"
		);
		sh.execute(
				"CREATE INDEX i_pw" +
				" ON " + TABLE_ASSOCIATIONS + 
				" (pathway)"
		);
	}
	
	private boolean tablesExist() throws SQLException {
		DatabaseMetaData dbm = con.getMetaData();
		ResultSet rs = dbm.getTables(null, null, TABLE_ASSOCIATIONS, null);
		return rs.next();
	}

	Map<String, PreparedStatement> statements = new HashMap<String, PreparedStatement>();

	private PreparedStatement getPst(String sql) throws SQLException {
		PreparedStatement pst = statements.get(con);
		if(pst == null) {
			statements.put(sql, pst = con.prepareStatement(sql));
		}
		return pst;
	}

	private String TABLE_ASSOCIATIONS = "ASSOCIATIONS";

	private String QUERY_PURGE_PATHWAY = 
		"DELETE FROM " + TABLE_ASSOCIATIONS + " WHERE pathway = ?";
	private String QUERY_ADD_ASSOCIATION = 
		"INSERT INTO " + TABLE_ASSOCIATIONS + 
		" (id, code, pathway, mapped) " +
		" VALUES (?, ?, ?, ?)";
	private String QUERY_GET_XREFS = 
		"SELECT id, code FROM " + TABLE_ASSOCIATIONS + " WHERE pathway = ? AND mapped = ?";
	private String QUERY_GET_ALL_XREFS = 
		"SELECT id, code FROM " + TABLE_ASSOCIATIONS + " WHERE pathway = ?";
}

TRUNCATE `thread`;
TRUNCATE `trackbacks`;
TRUNCATE `transcache`;
TRUNCATE `updatelog`;
TRUNCATE `user_message_state`;
TRUNCATE `user_newtalk`;
TRUNCATE `user_page_hits`;
TRUNCATE `user_page_views`;

DROP FUNCTION IF EXISTS delete_page;
DELIMITER //
 
CREATE FUNCTION delete_page(page_id_var INT)
	RETURNS INT
	LANGUAGE SQL
	NOT DETERMINISTIC
	MODIFIES SQL DATA
	SQL SECURITY INVOKER
	COMMENT 'permanently deletes pages from the database'
BEGIN
	DECLARE page_title_var VARCHAR(255);
	DECLARE page_namespace_var INT;
	SELECT page_title, page_namespace INTO page_title_var, page_namespace_var FROM page WHERE page_id = page_id_var;
	DELETE FROM redirect WHERE rd_from = page_id_var;
	DELETE FROM externallinks WHERE el_from = page_id_var;
	DELETE FROM langlinks WHERE ll_from = page_id_var;
	DELETE FROM searchindex WHERE si_page = page_id_var;
	DELETE FROM page_restrictions WHERE pr_page = page_id_var;
	DELETE FROM pagelinks WHERE pl_from = page_id_var;
	DELETE FROM categorylinks WHERE cl_from = page_id_var;
	DELETE FROM templatelinks WHERE tl_from = page_id_var;
	DELETE text.* FROM text LEFT JOIN revision ON (rev_text_id = old_id) WHERE rev_page = page_id_var;
	DELETE FROM revision WHERE rev_page = page_id_var;
	DELETE FROM imagelinks WHERE il_from = page_id_var;
	DELETE FROM recentchanges WHERE rc_namespace = page_namespace_var AND rc_title = page_title_var;
	DELETE text.* FROM text LEFT JOIN archive ON (ar_text_id = old_id) WHERE ar_namespace = page_namespace_var AND ar_title = page_title_var;
	DELETE FROM archive WHERE ar_namespace = page_namespace_var AND ar_title = page_title_var;
	DELETE FROM logging WHERE log_namespace = page_namespace_var AND log_title = page_title_var;
	DELETE FROM watchlist WHERE wl_namespace = page_namespace_var AND wl_title = page_title_var;
	DELETE FROM page WHERE page_id = page_id_var LIMIT 1;
	
	RETURN(1);
END//
 
DELIMITER ;

-- TEXT:
-- clear 2 (user), 3 (user talk), 102 (except WP4, which is sandbox), 103, 104, 105, 106 (except Sample* and Box-*), 107, 90, 91, 92, 93 (thread)
CREATE TEMPORARY TABLE toremove (
	page_id INT
);
INSERT INTO toremove (page_id)
	SELECT page_id FROM page WHERE page_namespace IN (2, 3, 103, 104, 105, 107, 90, 91, 92, 93);
INSERT INTO toremove (page_id)
	SELECT page_id FROM page WHERE page_namespace = 102 AND page_title != 'WP4';
INSERT INTO toremove (page_id)
	SELECT page_id FROM page WHERE page_namespace = 106 AND page_title NOT LIKE 'Sample%' AND page_title NOT LIKE 'Box-%';
	
SELECT delete_page(page_id) FROM toremove;

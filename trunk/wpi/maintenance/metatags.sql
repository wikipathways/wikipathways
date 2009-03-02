CREATE TABLE IF NOT EXISTS tag (
	-- Name of the tag
	tag_name	varchar(255),
	
	-- Contents of the tag
	tag_text	TEXT,
	
	-- Id ofthe page that is tagged
	page_id		int(8) UNSIGNED,
	
	-- The revision of the page that is tagged
	revision	int(8) UNSIGNED,
	
	-- Id of the user that added the tag
	user_add	int(5) UNSIGNED,
	
	-- Id of the user that last modified the tag
	user_mod	int(5),
	
	-- Timestamp of the tag creation
	time_add	char(14),
	
	-- Timestamp of the last tag modification
	time_mod	char(14),
	
	PRIMARY KEY (tag_name, page_id),
	INDEX tag_name (tag_name),
	INDEX tag_page (page_id)
);

CREATE TABLE IF NOT EXISTS tag_history (
	-- Name of the tag
	tag_name	varchar(255),
	
	-- Id of the page that is tagged
	page_id		int(8) UNSIGNED,
	
	-- Action that was taken
	action		varchar(255),
	
	-- The id of the user that performed the action
	action_user	int(8),
	
	-- Timestamp of action
	time		char(14),
	
	-- Old text value
	text		TEXT,
	
	INDEX taghist_name (tag_name),
	INDEX taghist_page (page_id)
);

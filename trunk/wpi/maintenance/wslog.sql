CREATE TABLE IF NOT EXISTS webservice_log (
	-- ip of the webservice user
	ip	char(16),
	
	-- called operation (if available)
	operation	varchar(255),
	
	-- date of the request
	request_timestamp	char(14)
);


# Create wikipathways database dumps that can be used to install a local
# wikipathways database without the user info (and either with or without the pathways)
# Usage: ./createPublicDump.sh mysql_user mysql_pass database_name

FILE=wikipathways-public.sql
FILE_CLEAN=wikipathways-clean.sql

#Export the full database schema
mysqldump --no-data -v -u $1 -p$2 $3 > $FILE

#Export the public table data (no user info)
mysqldump --no-create-info -v -u $1 -p$2 $3 \
--ignore-table=$3.webservice_log \
--ignore-table=$3.user \
--ignore-table=$3.user_groups \
--ignore-table=$3.user_page_hits \
--ignore-table=$3.user_page_views \
--ignore-table=$3.watchlist \
>> $FILE

#Append sql to add the basic users
cat basicusers.sql >> $FILE

#Also create a sql dump that generates a clean database
cp $FILE $FILE_CLEAN
#Append sql to remove the pathways
cat clearcontents.sql >> $FILE_CLEAN

gzip -f $FILE
gzip -f $FILE_CLEAN

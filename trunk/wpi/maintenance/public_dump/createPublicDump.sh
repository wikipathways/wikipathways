FILE=wikipathways-public.sql

#Export the full database schema
mysqldump --no-data -v -u $1 -p $2 wikipathways > $FILE

#Export the public table data (no user info)
mysqldump --no-create-info -v -u $1 -p $2 wikipathways \
--ignore-table=mywikipathways.webservice_log \
--ignore-table=wikipathways.user \
--ignore-table=wikipathways.user_groups \
--ignore-table=wikipathways.user_page_hits \
--ignore-table=wikipathways.user_page_views \
--ignore-table=wikipathways.watchlist \
>> $FILE

#Append sql to add the basic users
cat basicusers.sql >> $FILE

gzip $FILE

#Package the image directory (exclude cached pathway files)
EXCLUDE=`find /var/www/wikipathways/images -type f -name "WP*" -printf "--exclude=%p "`
tar cvfz wikipathways-public-images.tgz /var/www/wikipathways/images $EXCLUDE --exclude="thumb/*"

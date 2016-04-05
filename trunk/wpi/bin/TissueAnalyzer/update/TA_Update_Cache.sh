#!/bin/sh

cd /var/www/wikipathways/wpi/bin/TissueAnalyzer/update/

java -jar TA_Update_Cache.jar /var/www/wikipathways/wpi/bin/TissueAnalyzer/update/ 2> /var/www/wikipathways/wpi/bin/TissueAnalyzer/update/TA_log.txt

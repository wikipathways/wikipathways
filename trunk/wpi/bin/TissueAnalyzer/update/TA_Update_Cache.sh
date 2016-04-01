#!/bin/sh

cd /var/www/rcbranch.wikipathways.org/wpi/bin/TissueAnalyzer/update/

java -jar TA_Update_Cache.jar /var/www/rcbranch.wikipathways.org/wpi/bin/TissueAnalyzer/update/ 2> /var/www/rcbranch.wikipathways.org/wpi/bin/TissueAnalyzer/update/TA_log.txt

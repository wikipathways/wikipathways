
# Packages the image directory (exclude cached pathway files)
# Takes one argument, which is the location of the images directory

find $1 -type f ! \( -name "WP*" -o -name "Dm_*" -o -name "*.gpml" -o -wholename "*/thumb/*" -o -wholename "*/archive/*" -o -wholename "*/temp/*" -o -wholename "*/.svn/*" -o -wholename "*/deleted/*" \) -print | xargs tar cvfz wikipathways-public-images.tgz

ffmpeg -i $1 -acodec libfaac -ab 96k -vcodec libx264 -level 21 -refs 2 -b 345k -bt 345k -threads 0 -s $2 $1"."$2".ogv"
touch ThumbMaker.zip
rm ThumbMaker.zip
zip -r ThumbMaker.zip . -x *.git*
iron worker upload --stack php-5.6 ThumbMaker.zip php workers/ThumbMaker.php
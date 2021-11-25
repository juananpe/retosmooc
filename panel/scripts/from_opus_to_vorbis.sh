# execute from ../downloads

for i in */*/*.oga
do 
   if echo x"$i" | grep '*' > /dev/null; 
   then
	echo "Nothing to convert";
   else
	   destino=`dirname $i`/`basename $i .oga`.ogg; 
	   ffmpeg -i $i -acodec libvorbis  $destino;
	   copia=`dirname $i`/`basename $i .oga`; 
	   mv $i $copia;
   fi
done

for file in */*/file*; 
   do  
	   if [ ${file: -4:1} != "." ];  
	   then 
		   mv $file $file".oga" ; 
	   fi ; 
   done

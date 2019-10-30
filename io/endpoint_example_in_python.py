#! /usr/bin/python                                                              # By Kevin Franke May 2011
print "Content-type: text/plain"	                                              # must do to avoid 500 Internal Server Error
print															                                              # black line, end of headers

from xml.etree import ElementTree as ET
import os,sys,cgi,cgitb
from datetime import datetime
gwScript	= os.path.abspath(__file__)                                           # get operating full file name EG /var/www/cgi-bin/gateway.py
gwDir 		= os.path.dirname(gwScript)                                           # get just the dir EG /var/www/cgi-bin
workingDir= os.path.normpath(os.path.join(gwDir, "../"))                        # up a level to working dir EG /var/www

cgitb.enable(display=0, logdir=workingDir+"/logs")                              # activate exception handler && write error to file
errorLog	= open(workingDir+"/logs/errorLog.txt", 'a')                          # open in append mode
logFile		= open(workingDir+"/logs/gatewayLog.txt", 'w')                        # open in truncate then write mode

formPostData	= cgi.FieldStorage()                                              # define a container to contain cgi data
theXmlData		= formPostData.value                                              # this is the HTTP Posted XML
logFile.write(str(formPostData.value))                                          # log xml transaction

def main():
# This would be if parsing XML from a FILE
#	xml_file = os.path.abspath(__file__)
#	xml_file = os.path.dirname(xml_file)
#	xml_file = os.path.join(xml_file, "smartoneXML.xml")
#	xml = ET.parse(xml_file)
	xml = ET.ElementTree(ET.fromstring(theXmlData))																#Put XML into an ElementTree Object
	data = {}
	
	for child in xml.getiterator():                                               #For loop over the iterations of XML tags
		#print child.tag
		if child.tag == "trackermessage" :                                          #If the main "trackermessage" tag, pull out the type
			#print child.attrib.get('type')
			data['messagetype'] = child.attrib.get('type')
		if child.tag != xml.getroot().tag:                                          #just make sure we're not looping over the root
			if child.text != None :
				#print child.tag,child.text
				data[child.tag] = child.text                                            #if <tag>value</tag> if value exists then add value to data{}
			else:
				#print child.attrib.get('name'),child.attrib.get('data')
				data[child.attrib.get('name')] = child.attrib.get('data')               #if not, EG <tag atrib="someattrib"/> ad attribs to data{}

                                                                              	# XML Data now is in hash "data"
                                                                              	# You can pull the values out with data['whatYouwant']
        
	#print data                                                                   # Debug Print data to find the key needed
	global msgType
	msgType = str(data['messagetype'])
 	global esn                                                                    
	esn = str(data['esn'])                                                        #define vars to hold the esn,lat,lon referenced from data{}
	#global lat 
	#lat = str(data['latitude'])
	#global lon
	#lon  = str(data['longitude'])

	logFile.write('\nEnd Msg Recieved at:,'+(datetime.now().                      # Write end of XML timestamp to log
         #       strftime('%Y%m%d_%H%M%S'))+','+esn+','+lat+','+lon+'\n')
                strftime('%Y%m%d_%H%M%S'))+','+esn+'\n')
	logFile.close()                                                               # Close our open log file
	output_filename = (workingDir+"/pings/"+esn+"_"+msgType+"_%s.%i.xml" %         # Make up the filename for this xml transaction
                      (datetime.now().strftime('%Y%m%d_%H%M%S'), 
                        datetime.now().microsecond,))
	msgFile	= open(output_filename, 'w')                                          # Create and open the filename we just defined
	msgFile.write(str(formPostData.value))                                        # Write the contents of the XML transaction to this file
	msgFile.close()                                                               # Close it up

if __name__ == "__main__":                                                      # Some is launching this script directly then execute main()
	main() 

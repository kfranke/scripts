<?php
error_reporting(E_ALL);

/** Maybe you have your db connection details somewhere
 * require     ("../config/connection.php");   
 * connection.php sample
 * 
 * $hostname='my.ip.of.my.database';  //IP or URL of your database host
 * $username='myUserName';            //This is the user name you login with
 * $password='myAwesomePassword';     //this is your password you use to login with
 * $dbname='nameOfDatabase';          //this is your database name
 * $time = date("H:i dS F");          //get date & time
 * global $dbh;                       //set to global so you can call it later with PDO
 * 
 * try
 *   {
 *    $dbh = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
 *   }
 * catch (PDOException $e)
 *   {
 *    $logfile = "../../logs/connectionlog.html"; //some log file for the errors
 *    $open = fopen($logfile,"a+"); //open log file
 *    fwrite($open, "<b>Error:</b> ".$e->getMessage()."<br/>"); //write error text
 *    fwrite($open, "<b>Timestamp:</b> ".$time."<br/>"); //write the time it happened
 *    fclose($open); //close log file //close the error file
 *    die();
 *   }
 *   
*/

$date           = date("Ymd"); // eg 20101230
$fileNumLog     = "fileNumLog.txt";
$preFileNumber  = file_get_contents($fileNumLog); //File # stored in log. This could also be a db query
$newFileNumber  = $preFileNumber + 1;
$fileInt        = str_pad($newFileNumber,8,"0",STR_PAD_LEFT); // eg 00000029

///////////////////////Production vars for file and director names//////////////
//Uncomment these in production 																							//
//$xmlDir         = "../../inbound/pings";                                    //
//$xmlFile        = "ping_".$date.".".$fileInt.".xml";                        //
////////////////////////////////////////////////////////////////////////////////


///////////////////////Temp. file vars for testing locally//////////////////////
//comment these out in production																							//
$xmlDir				=	"";																														//
$xmlFile			=	"someMessage.xml";														              	//
////////////////////////////////////////////////////////////////////////////////

//$logFile        = "../../logs/dblog.html";
//$msglog         = "../../logs/msg_log.txt";
$validMsgRec    = 0;

/*--------------------------Recieve the Ping from _Post-----------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
  //print_r($_POST);
  if(isset($_POST['payload']))
  {
    //print '_POST[payload] is Set';
    $payloadString = trim($_POST['payload']);
  }
  else
  {
    //print 'Using php://input Stream';
    //$payloadString = implode("\n", @file('php://input'));
		$payloadString = @file('php://input');
  }
  $validMsgRec = 1;                                                           //Set valid flag
	file_put_contents($xmlDir.$xmlFile, stripslashes($payloadString));          //save the post XML to a file 
  file_put_contents($fileNumLog, $newFileNumber);                             //save the newfilenumber to log
}
else 
{
	$validMsgRec = 0;
	print 'No Valid XML Post Recieved; Quitting';
	//die();
}


/*--------------------------Check that file got saved-------------------------*/

if(file_exists($xmlDir.$xmlFile) == TRUE) {
		$validXmlRec = 1;
		print "Found data in: ".$xmlDir.$xmlFile."\n";
}
else {
		$validMsgRec = 0;		
		print "Could not find XML data in: ".$xmlDir.$xmlFile."\n";
		die();
}
/*----------------------------------------------------------------------------*/

/*--------------------------Open XML file and parse to Obj--------------------*/
// You might separate this to a new process
if($validXmlRec == 1)
  {
		$msgXml = file_get_contents($xmlDir.$xmlFile);
		if(isXML($msgXml) === true) {
		    $xmlObj = new SimpleXMLElement($msgXml);
    		print_r($xmlObj);
		}
  }
else {
		$validMsgRec = 0;		
		print "Error in opening msg XML file; Quitting\n";
		die();
}
/*-------------------------Pull out the fields we need------------------------*/
								/////////////////////////////////////
								//DATA IS NOW IN THE $xmlObj object//
								//extract what data you need       //
								/////////////////////////////////////

//Example
//Just pull out the unxitime, ESN, Lat & Lon (example assumes trackermessage xml)
$time				= $xmlObj->trackermessage['unixtime'];								
$time				=	date("m/d/y H:i:s", (int) $time);
$esn				= $xmlObj->trackermessage->asset->esn;
$lat				=	$xmlObj->trackermessage->position->coordinate->latitude;
$lon				= $xmlObj->trackermessage->position->coordinate->longitude;

//Array containing the field names of our table we're inserting to
$fieldNames = array (
                       'time',
                       'esn',
                       'lat',
                       'lon'
                    );
//Array containing our values from the XML payload
$values	    = array (
                       $time,
                       $esn,
                       $lat,
                       $lon
                     );
print "Recieved message at: $time from ESN: $esn at: $lat by $lon \n";
/*----------------------------------------------------------------------------*/
/*-------------------------Insert values into our MYSQL Database--------------*/      
/*
if($validMsgRec == 1) {
				
		$pingTableName	= "numerex_simplex_msgs";
    $sql = 'INSERT INTO '.$pingTableName.' ('.implode(',',$fieldNames).') 
            VALUES ('.qmarks_for($values).')';
    try
        {
        $dbh->beginTransaction();                                               //Start the DB transaction
        $stmnt = $dbh->prepare($sql);
        $stmnt->execute($valuesArr);                                            //Execute the SQL inserting into the statement the payload values
        $dbh->commit();                                                         //If all AOK then commit
        print ("Insert Successful\n");
        }  
    catch (PDOException $e)
        {
        $dbh->rollBack();
        $open = fopen($logfile,"a+");                                           //open log file
        fwrite($open, "<b>Error:</b> ".$e->getMessage()."<br/>");               //Write Error to error log
        fwrite($open, "<b>Timestamp:</b> ".$time."<br/>");
        fclose($open);                                                          //close log file
        print ($e->getMessage());                                               //Also print error to screen
        die();
        }
    $dbh = null;                                                                //close db connection      
  }
else{print 'Error in Database Insertion; Quitting';}
*/

/*-------------------------Log Basic Info About Msg Rec-----------------------*/    
/*
if($validMsgRec == 1)
  {
		$unixtime			= time();
    $current      = file_get_contents("$msglog");
    $newmsgtoadd  = $unixtime . "," . $esn . "\n";
    file_put_contents($msglog, $newmsgtoadd . $current); 
  }
*/
/*----------------------qmarks_for()------------------------------------------*/

/* @Jon M */
function qmark($item){
return "?";
}
/* @Jon M */
function qmarks_for($items){
return join(", ", array_map('qmark', $items));
}

/*-------------------------Validate XML as OK before unserializing------------*/
// Must be tested with ===, as in if(isXML($xml) === true){}
// Returns the error message on improper XML
function isXML($xml){
    libxml_use_internal_errors(true);

    $doc = new DOMDocument('1.0', 'utf-8');
    $doc->loadXML($xml);

    $errors = libxml_get_errors();

    if(empty($errors)){
        return true;
    }

    $error = $errors[0];
    if($error->level < 3){
        return true;
    }

    $explodedxml = explode("r", $xml);
    $badxml = $explodedxml[($error->line)-1];

    $message = $error->message . ' at line ' . $error->line . '. Bad XML: ' . htmlentities($badxml);
    return $message;
}
/*----------------------------------------------------------------------------*/
?>

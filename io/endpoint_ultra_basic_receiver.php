<?php
/*--------------------------Recieve the Ping from _Post-----------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if(isset($_POST['payload']))
      {
      $payloadString = trim($_POST['payload']);
      $validMsgRec = 1;
      }
    else
      {
      $payloadString = implode("\n", @file('php://input'));
      $validMsgRec = 1;
      }
    if($validMsgRec == 1)
    {
      $post_data_file = "/tmp/post_data-".time();
      $wrote = file_put_contents($post_data_file, stripslashes($payloadString));
      if(!$wrote) print "wrote post data to: $post_data_file\n";
    }
    
}
else
{
  $validMsgRec = 0;
  print "No Valid POST Recieved; Quitting\n";
}
?>

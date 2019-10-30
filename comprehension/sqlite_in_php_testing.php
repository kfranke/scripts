<?php

if ($db = sqlite_open('mysqlitedb', 0666, $sqliteerror)) { 
    
    //sqlite_query($db, 'CREATE TABLE foo (bar varchar(10))');
    //sqlite_query($db, "INSERT INTO foo VALUES ('fnord')");
    //sqlite_query($db, 'ALTER TABLE mysqlitedb.foo ADD COLUMN (firstname varchar(10), lastname varchar(10), age int(2), likes varchar(128), notes varchar(128))');
    sqlite_query($db, 'ALTER TABLE mysqlitedb.foo ADD COLUMN (firstname varchar(10) );
    //sqlite_query($db, "INSERT INTO foo VALUES ('bad', 'motherfucker', 69, '')");

    $result = sqlite_query($db, 'select * from foo');
    var_dump(sqlite_fetch_array($result)); 
} else {
    die($sqliteerror);
}

?>
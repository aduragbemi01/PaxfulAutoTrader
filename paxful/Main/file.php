<?php
$link = "https://swayhost.com/paxful/Main2/mail.php?subject=gfgg&info=HSH";
if(file_get_contents($link)){echo "works";} else {echo "not working";}
?>
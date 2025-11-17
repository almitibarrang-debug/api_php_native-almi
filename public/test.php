<?php
echo "Test file works!";
echo "<br>REQUEST_URI: " . $_SERVER['REQUEST_URI'];
echo "<br>SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'];
echo "<br>Base: " . dirname($_SERVER['SCRIPT_NAME']);
?>
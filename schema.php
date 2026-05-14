<?php
require_once 'includes/config.php';
$res = $conn->query("DESCRIBE products");
while($row = $res->fetch_assoc()){
    print_r($row);
}
?>

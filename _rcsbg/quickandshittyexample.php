<?php define( '__Permitted__', 1 ); require_once './wp-managers/CDriver.php'; ?>
<?php
define( 'API_KEY', '' );
$cdis		=	new CDriver();
echo $cdis->ConstructWebsite( API_KEY, '1000', 320 );
?>
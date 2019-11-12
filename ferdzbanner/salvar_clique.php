<?php 
define('WP_USE_THEMES', false);
require('../../../wp-load.php');


$idbanner = $_GET['ban'];
$idcliente = $_GET['cli'];
$nomecli = $_GET['nm'];
$redirecturl = urldecode($_GET['url']);
// 1st Method - Declaring $wpdb as global and using it to execute an SQL query statement that returns a PHP object

global $wpdb;
//$results = $wpdb->get_results( 'INSERT INTO wp_ferdzbanner_viewcount (id_banner, id_cliente) VALUES ("4321", "598475")', OBJECT );

$res = $wpdb->insert( 
'wp_ferdzbanner_viewcount', 
array( 
'id_banner' => $idbanner, 
'id_cliente' => $idcliente,
'nome_cliente' => $nomecli 
));
if($res) {
		header("Location: $redirecturl");
} else {
		header("Location: $redirecturl");
}


// 2nd Method - Utilizing the $GLOBALS superglobal. Does not require global keyword ( but may not be best practice )

//$results = $GLOBALS['wpdb']->get_results( 'SELECT * FROM wp_options WHERE option_id = 1', OBJECT );





?>
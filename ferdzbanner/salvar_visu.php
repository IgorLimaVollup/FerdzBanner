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
'wp_ferdzbanner_impressaocount', 
array( 
'id_banner' => $idbanner, 
'id_cliente' => $idcliente,
'nome_cliente' => $nomecli 
));
if($res) {
		
		$limite = $wpdb->get_results('SELECT meta_value FROM '.$wpdb->prefix.'postmeta WHERE meta_key = "limite" AND post_id = ' .$idbanner);
		$views = $wpdb->get_results('SELECT COUNT(*) as cnt FROM wp_ferdzbanner_impressaocount WHERE id_banner = '.$idbanner);

		if (($limite[0]->meta_value) > 0) {

			if (intval($views[0]->cnt) >= intval($limite[0]->meta_value)) {
				
				update_post_meta($idbanner, 'ativo', 'Inativo');

				// Disparo de E-mail
				require('../../../wp-load.php');
				$mensagem = "#O banner ". $idbanner ." acabou de atingir o limite de views ". intval($limite[0]->meta_value) . "  ";
				$headers[] = 'From: Relatório FerdzBanner <ferdzform@gmail.com>';
				$recebimento = "fernando.santos@vollup.com";
				wp_mail($recebimento, "O banner ". get_the_title() ." acabou de atingir o limite de views", $mensagem, $headers);
			}
		}
		
		return 1;
} else {
		return 0;
}



exit();
// 2nd Method - Utilizing the $GLOBALS superglobal. Does not require global keyword ( but may not be best practice )

//$results = $GLOBALS['wpdb']->get_results( 'SELECT * FROM wp_options WHERE option_id = 1', OBJECT );





?>
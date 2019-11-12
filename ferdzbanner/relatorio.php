<?php 
define('WP_USE_THEMES', false);
require('../../../wp-load.php');

global $wpdb;

$mensagem .= "

<h1>Relatório de ". date('d/m/Y',strtotime('-1 days')) ."</h1>
<table border='1'>
<tr>
	<th>
		ID do banner
	</th>

	<th>
		Nome do Banner
	</th>
	
	<th>
		Visualizações
	</th>
	
	<th>
		Cliques
	</th>
	
</tr>";

$querybanners = new WP_Query(array("post_type" => "banners", 'posts_per_page' => -1));
for($i=0;$i<count($querybanners->posts);$i++) {
	$pstId = $querybanners->posts[$i]->ID;
	$pstTitle = $querybanners->posts[$i]->post_title;
	$pstData = $querybanners->posts[$i]->post_date;
	$resultsViews = $wpdb->get_results( 'SELECT COUNT(*) AS cntviews FROM `wp_ferdzbanner_impressaocount` WHERE date(data_clique) = SUBDATE(CURDATE(),1) AND id_banner = '.$pstId.';', OBJECT );
	$resultsCliques = $wpdb->get_results( 'SELECT COUNT(*) AS cntcliques FROM `wp_ferdzbanner_viewcount` WHERE date(data_clique) = SUBDATE(CURDATE(),1) AND id_banner = '.$pstId.';', OBJECT );
	
$mensagem.=	"<tr>
		<td>
			".  $pstId."
		</td>
	
		<td> 
			".  $pstTitle."
		</td>
		
		<td>
			".  $resultsViews[0]->cntviews ."
		</td>
		
		<td>
			". $resultsCliques[0]->cntcliques."
		</td>
		
</tr>";

}

$mensagem.= "</table>";

//echo $mensagem;

?>


<?php 

	add_action( 'phpmailer_init', 'wpse8170_phpmailer_init' );
	function wpse8170_phpmailer_init( PHPMailer $phpmailer ) { 
		$phpmailer->Host = 'smtp.gmail.com';
		$phpmailer->Port = 587; // could be different
		$phpmailer->Username = 'no-reply@vollup.com'; // if required
		$phpmailer->Password = '@noreply123'; // if required
		$phpmailer->isHTML(true);                                  // Set email format to HTML
		$phpmailer->SMTPAuth = true; // if required
		$phpmailer->SMTPSecure = 'tls'; // enable if required, 'tls' is another possible value
		$phpmailer->IsSMTP();
	}

	$headers[] = 'From: Relatório FerdzBanner <igor.lima@vollup.com>';
if(get_option('recebimento_email')) {
	$recebimento = get_option('recebimento_email');
} else {
	$recebimento = "atendimento@nautica.com.br";
}
if(wp_mail( $recebimento, date('d/m/Y',strtotime('-1 days')), $mensagem, $headers )) {
	header("Location: ".get_admin_url() . "edit.php?post_type=banners&page=configuracoes_ferdzbanner_relatoriosemail&settings-updated=disparo");
} else {
	header("Location: ".get_admin_url() . "edit.php?post_type=banners&page=configuracoes_ferdzbanner_relatoriosemail&settings-updated=disparoerro");
}
?>
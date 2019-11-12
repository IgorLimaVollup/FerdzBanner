<?php 
define('WP_USE_THEMES', false);
require('../../../../wp-load.php');

global $wpdb;

if(isset($_POST['banner_id']) && $_POST['banner_id'] !== "") {
		
	$dtinicio = $_POST['dtinicio'];
	$dttermino = $_POST['dttermino'];
	
	
	$dti = $dtinicio;
	#$dti = str_replace('/', '-', $dti);
	$dtt = $dttermino;
	#$dtt = str_replace('/', '-', $dtt);
	$banner_id = $_POST['banner_id'];
	
	$querybanners = new WP_Query(array('post_type' => "banners",  "p" => $banner_id));
	//$querybanners = get_post($banner_id);
	
	$pstId = $querybanners->posts[0]->ID;
	$pstTitle = $querybanners->posts[0]->post_title;
	$pstData = $querybanners->posts[0]->post_date;
	$range = $_POST['range_maximo'];
	$resultsViews = $wpdb->get_results( 'SELECT COUNT(*) AS cntviews FROM `wp_ferdzbanner_impressaocount` WHERE data_clique >= "'.$dti.' 00:00:00" AND data_clique <= "'.$dtt.' 23:59:59" AND id_banner = '.$pstId.';', OBJECT );
	$resultsCliques = $wpdb->get_results( 'SELECT COUNT(*) AS cntcliques FROM `wp_ferdzbanner_viewcount` WHERE data_clique >= "'.$dti.' 00:00:00" AND data_clique <= "'.$dtt.' 23:59:59" AND id_banner = '.$pstId.';', OBJECT );
	$cntViews = $resultsViews[0]->cntviews;
	$cntCliques = $resultsCliques[0]->cntcliques;
	if($range > 0) {
	$queryTabelaCliques = 'SELECT * FROM `wp_ferdzbanner_viewcount` WHERE data_clique >= "'.$dti.' 00:00:00" AND data_clique <= "'.$dtt.' 23:59:59" AND id_banner = '.$banner_id.' LIMIT 0,' . $range;
	} else {
	$queryTabelaCliques = 'SELECT * FROM `wp_ferdzbanner_viewcount` WHERE data_clique >= "'.$dti.' 00:00:00" AND data_clique <= "'.$dtt.' 23:59:59" AND id_banner = '.$banner_id;		
	}	
	
	
	$tabelaCliques = $wpdb->get_results( $queryTabelaCliques );
	//$tabelaViews = $wpdb->get_results( 'SELECT * FROM `wp_ferdzbanner_impressaocount` WHERE data_clique >= "'.$dti.' 00:00:00" AND data_clique <= "'.$dtt.' 23:59:59" AND id_banner = '.$pstId.';' );
	//var_dump($tabelaCliques->posts);
	
	
	
	if(isset($_POST['enviaemail']) && $_POST['enviaemail'] == "1") {
		$mensagem.= "<h1>Relatório de ".$_POST['labelinicio']." à ".$_POST['labeltermino']." </h1>";		
		$mensagem.= "<h3 style='font-weight:normal;'><strong>Nome:</strong> ".$pstTitle ."<br/><strong>Total de Visualizações:</strong> ".$cntViews."<br/><strong>Total de Cliques: </strong>".$cntCliques."</h3><h3 style='margin-bottom:0;'>Relatório de cliques</h3>";		
		$mensagem.= "<table border='1'>";
	} else {
		$mensagem.= "<h1>Relatório de ".$_POST['labelinicio']." à ".$_POST['labeltermino']." </h1>";		
		$mensagem.= "<h3 style='font-weight:normal;'><strong>Nome:</strong> ".$pstTitle ."<br/><strong>Total de Visualizações:</strong> ".$cntViews."<br/><strong>Total de Cliques: </strong>".$cntCliques."</h3><h3 style='margin-bottom:0;'>Relatório de cliques</h3>";		
		$mensagem.= "<table  border='0'>";
	}
	$mensagem .= "
	<tr>
	
	<th>
	Dia do clique
	</th>
	
	<th>
	Horário do clique
	</th>
	
	</tr>";

		for($j=0;$j<count($tabelaCliques);$j++) {
		$data = $tabelaCliques[$j]->data_clique;
		$dataData = explode(" ", $data)[0];
		$dataHora = explode(" ", $data)[1];
			$mensagem.= "<tr>

			<td>
				".$dataData."
			</td>
			
			<td>
				".$dataHora."
			</td>
			
			</tr>";
			
			
		}
	
	
	
	$mensagem.= "</table>";
	
	
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
	
	
	
	if(isset($_POST['enviaemail']) && $_POST['enviaemail'] == "1") {
		if(wp_mail( $recebimento, "Relatório de cliques de ".$_POST['labelinicio']." à ".$_POST['labeltermino'].".", $mensagem, $headers )) {
			echo "Relatório enviado com sucesso.";
		} else {
			echo "Erro ao enviar relatório, por favor, tente novamente mais tarde.";
		}
		
	} else {
		echo $mensagem;	
	}
	
		
		
} else {
		


$dtinicio = $_POST['dtinicio'];
$dttermino = $_POST['dttermino'];
if(isset($_POST['enviaemail']) && $_POST['enviaemail'] == "1") {
$mensagem.= "<h1>Relatório de ".$_POST['labelinicio']." à ".$_POST['labeltermino']." </h1>";
$mensagem.= "<table border='1'>";
} else {
$mensagem.= "<table border='0'>";
}
$mensagem .= "
<tr>
	<th>
		ID do banner
	</th>

	<th>
		Nome do Banner
	</th>
	
	<th>
		Posição
	</th>
	
	<th>
		Visualizações
	</th>
	
	<th>
		Cliques
	</th>
	
</tr>";
$dti = $dtinicio;
#$dti = str_replace('/', '-', $dti);
$dtt = $dttermino;
#$dtt = str_replace('/', '-', $dtt);
$querybanners = new WP_Query(array("post_type" => "banners", 'posts_per_page' => -1));
for($i=0;$i<count($querybanners->posts);$i++) {
	$pstId = $querybanners->posts[$i]->ID;
	$pstTitle = $querybanners->posts[$i]->post_title;
	$pstData = $querybanners->posts[$i]->post_date;
	
	//var_dump(get_the_terms($pstId, "posicoes")[1]->name);
	$pstPosicoes = "";
	for($k=0;$k<count(get_the_terms($pstId, "posicoes"));$k++) {
		$pstPosicoes .= get_the_terms($pstId, "posicoes")[$k]->name . ", ";
	}
	$resultsViews = $wpdb->get_results( 'SELECT COUNT(*) AS cntviews FROM `wp_ferdzbanner_impressaocount` WHERE data_clique >= "'.$dti.' 00:00:00" AND data_clique <= "'.$dtt.' 23:59:59" AND id_banner = '.$pstId.';', OBJECT );
	$resultsCliques = $wpdb->get_results( 'SELECT COUNT(*) AS cntcliques FROM `wp_ferdzbanner_viewcount` WHERE data_clique >= "'.$dti.' 00:00:00" AND data_clique <= "'.$dtt.' 23:59:59" AND id_banner = '.$pstId.';', OBJECT );
	
	
$mensagem.= "<tr>
		<td>
			". $pstId."
		</td>
	
		<td>
			". $pstTitle ."
		</td>
		
		<td>
			".$pstPosicoes."
		</td>
		
		<td>
			". $resultsViews[0]->cntviews ."
		</td>
		
		<td>
			". $resultsCliques[0]->cntcliques ."
		</td>
		
</tr>";
	
}


$mensagem.= "</table>";
 
		
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



if(isset($_POST['enviaemail']) && $_POST['enviaemail'] == "1") {
	if(wp_mail( $recebimento, "Relatório de ".$_POST['labelinicio']." à ".$_POST['labeltermino'].".", $mensagem, $headers )) {
		echo "Relatório enviado com sucesso.";
	} else {
		echo "Erro ao enviar relatório, por favor, tente novamente mais tarde.";
	}
	
} else {
	echo $mensagem;	
}

}
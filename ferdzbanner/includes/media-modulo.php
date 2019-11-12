<?php
// Página de relatório de média de cliques



$meses = array(
'01' => 'Janeiro',
'1' => 'Janeiro',
'02' => 'Fevereiro',
'2' => 'Fevereiro',
'03' => 'Março',
'3' => 'Março',
'04' => 'Abril',
'4' => 'Abril',
'05' => 'Maio',
'5' => 'Maio',
'06' => 'Junho',
'6' => 'Junho',
'07' => 'Julho',
'7' => 'Julho',
'08' => 'Agosto',
'8' => 'Agosto',
'09' => 'Setembro',
'9' => 'Setembro',
'10' => 'Outubro',
'11' => 'Novembro',
'12' => 'Dezembro'
);


//Adiciona o menu para configurações gerais
function inserir_menu_ferdzbanner_media() {
	// Add to admin_menu function
	add_submenu_page('edit.php?post_type=banners', __('Médias'), __('Médias'), 'edit_pages', 'configuracoes_ferdzbanner_medias', 'my_menu_render_confs_medias');
	add_action( 'admin_init', 'registrar_configuracoes_ferdzbanner_medias' );
}


function registrar_configuracoes_ferdzbanner_medias() {
	//register our settings
	//register_setting( 'grupo-configuracoes-ferdzbanner-medias', 'recebimento_m' );
}

function my_menu_render_confs_medias() {
    global $title;
	global $meses;
?>
<link href="<?php echo  plugins_url(); ?>/ferdzbanner/jquery.tagsinput.css "/>
<script src="<?php echo  plugins_url(); ?>/ferdzbanner/jquery.tagsinput.js"></script>
<style>
	.form-opts-banners-emails th{
	width:350px;
	font-weight:400;
	text-align:left;
	}
	
	.media-cliques td {
		padding:10px;
	}

</style>



    <table class="form-table form-opts-banners-emails">
		
		<?php 
			$mes_clique_selecionado = "-".(date("n") - date("n", strtotime("-1 month")))." month";
			$mes_clique_nome = $mes_clique_selecionado;
			if(isset($_GET['meses_cliques']) && $_GET['meses_cliques'] != "") {
				$mes_clique_selecionado = "-".(date("n") - $_GET['meses_cliques'])." month";
			}

			if(isset($_GET['ano_cliques']) && $_GET['ano_cliques'] != "") {
				$mes_clique_nome = "-".(date("n") - $_GET['meses_cliques'])." month";
				$mes_clique_selecionado = "-".((date("n") - $_GET['meses_cliques']) + $_GET['ano_cliques'])." month";
			}
		?>
		
		<tr valign="top">
			<th colspan="2"><h1>Média de cliques de <?php echo $meses[date("m", strtotime($mes_clique_nome))]; ?> (01-<?php echo date("m-Y", strtotime($mes_clique_selecionado)) ?> até <?php echo date("t", strtotime($mes_clique_selecionado)) ."-". date("m-Y", strtotime($mes_clique_selecionado))?>)</h1></th>
		</tr>
		
	</table>



	<table style="width: 366px;margin-top: -40px;">
		<tr style="text-align:left;">
			<th style="text-align:left">
				<h4>Mês / Ano</h4>
			</th>
			<th style="text-align:left">
				<form action="edit.php" method="get" id="form_meses_visu" style="margin-bottom: 0px;">
					
					<input type="hidden" name="post_type" value="banners">
					<input type="hidden" name="page" value="configuracoes_ferdzbanner_medias">
					
					<select name="meses_cliques" id="meses_cliques">
						<?php
							$mesSelect = date("n", strtotime("-1 month"));
							if(isset($_GET['meses_cliques']) && $_GET['meses_cliques'] != "") {
								$mesSelect = $_GET['meses_cliques'];
							}
	
							$ano_clique_Select = date("Y");
							if(isset($_GET['ano_cliques']) && $_GET['ano_cliques'] != "") {
								$ano_clique_Select = $_GET['ano_cliques'];
							}
								
							for($mes = 1; $mes <= 12; $mes++) {
								$selected = "";
								$disabled = "";
								if($mesSelect == $mes) {
									$selected = "selected";
								}
								
								if($mes > date("n", strtotime("-1 month")) && $ano_clique_Select == date("Y")) {
									$disabled = "disabled";
								}
								
								echo '<option value="'.$mes.'" '.$selected.' '.$disabled.'>'.$meses[$mes].'</option>';
							}
						?>
					</select>
					
					<select name="ano_cliques" id="ano_cliques" >
						<?php
							
							$ano_clique_Atual = date("Y");
							$contagem_ano_visu = 0;
							
							for($ano = $ano_clique_Atual; $ano >= 2016; $ano--) {
								
								if($ano != $ano_clique_Atual) {
									$contagem_ano_visu += 12;
								}
								$selected = "";
								if($ano_clique_Select == $contagem_ano_visu) {
									$selected = "selected";
								}
								echo '<option value="'.$contagem_ano_visu.'" '.$selected.'>'.$ano.'</option>';
								
								
							}
						?>
					</select>
					
					<input class="button" type="submit" value="Filtrar">
					
				</form>
			</th>
		</tr>
	</table>


<table class="media-cliques">
	
	<tr style="text-align:left;">
		<th style="text-align:left;">
			Posição
		</th>
		
		<th style="text-align:left;">
			Média por dia
		</th>
	</tr>
		<?php 
	
			$posicoes = get_terms("posicoes");
			//for($i=0;$i<count($posicoes);$i++) {
				
		?>

		
		<?php 
			function getPostsByTaxonomy() {
				$custom_terms = get_terms('posicoes');
				$obje = array();
				foreach($custom_terms as $custom_term) {
					wp_reset_query();
					$args = array('post_type' => 'banners',
					'tax_query' => array(
					array(
					'taxonomy' => 'posicoes',
					'field' => 'slug',
					'terms' => $custom_term->slug,
					),
					),
					);
					
					global $wpdb;
					
					$loop = new WP_Query($args);
					if($loop->have_posts()) {
						echo "<tr>";
						echo '<td>'.$custom_term->name . "</td>";
						$postCounter = 0;
						$clickCounter = 0;
						$diferenca = 0;
						$queryTotalCliques = "";
						
						echo "<td>";						
						while($loop->have_posts()) : $loop->the_post();
						$id = get_the_ID();
						
						$diferenca = cal_days_in_month(CAL_GREGORIAN, date("m", strtotime($mes_clique_selecionado)), date("Y", strtotime($mes_clique_selecionado)));
						
						$mes_clique_count = 1;
						$ano_clique_select = date('Y');
						$mes_clique_select = date('n', strtotime("- 1 month"));
						if(isset($_GET['meses_cliques']) && $_GET['meses_cliques'] != "") {
							$mes_clique_count = date("n") - $_GET['meses_cliques'];
							$mes_clique_select = $_GET['meses_cliques'];
						}
						
						if($mes_clique_select <= 9) {
							$mes_clique_select = "0".$mes_clique_select;
						}
						
						if(isset($_GET['ano_cliques']) && $_GET['ano_cliques'] != "") {
							$mes_clique_count = ((date("n") - $_GET['meses_cliques']) + $_GET['ano_cliques']);
							$ano_clique_select = date('Y', strtotime("- ".($_GET['ano_cliques'] / 12)." year"));
						}
												
						$queryTotalCliques = "SELECT COUNT(*) as cnt FROM wp_ferdzbanner_viewcount WHERE id_banner = " . $id . " AND data_clique >= '".$ano_clique_select."-".$mes_clique_select."-01' AND data_clique <= '".$ano_clique_select."-".$mes_clique_select."-".$diferenca."';";
						
						$res = $wpdb->get_results( $queryTotalCliques );
						
						$clickCounter= $clickCounter + intval($res[0]->cnt);
						endwhile;
						if($diferenca == 0) {
							$diferenca = 1;
						}
						echo round($clickCounter / $diferenca) .' <span style="display:none">'.$queryTotalCliques.'</span> </td>';
						echo "</tr>";
					}
				}
			}
		?>
		
		
		<?php getPostsByTaxonomy(); ?>
 
			<?php //} ?>
</table>

<table class="form-table form-opts-banners-emails">
	<?php 
		$mes_visu_selecionado = "-".(date("n") - date("n", strtotime("-1 month")))." month";
		$mes_visu_nome = $mes_visu_selecionado;
		if(isset($_GET['meses_visualizacoes']) && $_GET['meses_visualizacoes'] != "") {
			$mes_visu_selecionado = "-".(date("n") - $_GET['meses_visualizacoes'])." month";
		}
	
		if(isset($_GET['ano_visualizacoes']) && $_GET['ano_visualizacoes'] != "") {
			$mes_visu_nome = "-".(date("n") - $_GET['meses_visualizacoes'])." month";
			$mes_visu_selecionado = "-".((date("n") - $_GET['meses_visualizacoes']) + $_GET['ano_visualizacoes'])." month";
		}
	?>

	
		<tr valign="top" data-conta="<?php echo $testeConta?>">
			<th colspan="2"><h1>Média de visualizações de <?php echo $meses[date("m", strtotime($mes_visu_nome))]; ?> (01-<?php echo date("m-Y", strtotime($mes_visu_selecionado)) ?> até <?php echo date("t", strtotime($mes_visu_selecionado)) ."-". date("m-Y", strtotime($mes_visu_selecionado))?>)</h1></th>
		</tr>
		
	</table>

	<table style="width: 366px;margin-top: -40px;">
		<tr style="text-align:left;">
			<th style="text-align:left">
				<h4>Mês / Ano</h4>
			</th>
			<th style="text-align:left">
				<form action="edit.php" method="get" id="form_meses_visu" style="margin-bottom: 0px;">
					
					<input type="hidden" name="post_type" value="banners">
					<input type="hidden" name="page" value="configuracoes_ferdzbanner_medias">
					
					<select name="meses_visualizacoes" id="meses_visualizacoes">
						<?php
							$mesSelect = date("n", strtotime("-1 month"));
							if(isset($_GET['meses_visualizacoes']) && $_GET['meses_visualizacoes'] != "") {
								$mesSelect = $_GET['meses_visualizacoes'];
							}
	
							$ano_visu_Select = date("Y");
							if(isset($_GET['ano_visualizacoes']) && $_GET['ano_visualizacoes'] != "") {
								$ano_visu_Select = $_GET['ano_visualizacoes'];
							}
								
							for($mes = 1; $mes <= 12; $mes++) {
								$selected = "";
								$disabled = "";
								if($mesSelect == $mes) {
									$selected = "selected";
								}
								
								if($mes > date("n", strtotime("-1 month")) && $ano_visu_Select == date("Y")) {
									$disabled = "disabled";
								}
								
								echo '<option value="'.$mes.'" '.$selected.' '.$disabled.'>'.$meses[$mes].'</option>';
							}
						?>
					</select>
					
					<select name="ano_visualizacoes" id="ano_visualizacoes" >
						<?php
							
							$ano_visu_Atual = date("Y");
							$contagem_ano_visu = 0;
							
							for($ano = $ano_visu_Atual; $ano >= 2016; $ano--) {
								
								if($ano != $ano_visu_Atual) {
									$contagem_ano_visu += 12;
								}
								$selected = "";
								if($ano_visu_Select == $contagem_ano_visu) {
									$selected = "selected";
								}
								echo '<option value="'.$contagem_ano_visu.'" '.$selected.'>'.$ano.'</option>';
								
								
							}
						?>
					</select>
					
					<input class="button" type="submit" value="Filtrar">
					
				</form>
			</th>
		</tr>
	</table>

	<table class="media-cliques">
	
			
		
	<tr style="text-align:left;">
		<th style="text-align:left;">
			Posição
		</th>
		
		<th style="text-align:left;">
			Média por dia
		</th>
	</tr>
		<?php 
	
			$posicoes = get_terms("posicoes");				
		?>

		
		<?php 
			function getPostsByTaxonomy2() {
				$custom_terms = get_terms('posicoes');
				$obje = array();
				foreach($custom_terms as $custom_term) {
					wp_reset_query();
					$args = array('post_type' => 'banners',
					'tax_query' => array(
					array(
					'taxonomy' => 'posicoes',
					'field' => 'slug',
					'terms' => $custom_term->slug,
					),
					),
					);
					
					global $wpdb;
					
					$loop = new WP_Query($args);
					if($loop->have_posts()) {
						echo "<tr>";
						echo '<td>'.$custom_term->name . "</td>";
						$postCounter2 = 0;
						$viewCounter = 0;
						$diferenca2 = 0;
						$idPost = "";
						$queryTotalViews = "";
						
						echo "<td>";						
						while($loop->have_posts()) : $loop->the_post();
						$id = get_the_ID();
						
						$diferenca2 = cal_days_in_month(CAL_GREGORIAN, date("m", strtotime($mes_visu_selecionado)), date("Y", strtotime($mes_visu_selecionado)));
						
						$mes_visu_count = 1;
						$ano_visu_select = date('Y');
						$mes_visu_select = date('n', strtotime("- 1 month"));
						if(isset($_GET['meses_visualizacoes']) && $_GET['meses_visualizacoes'] != "") {
							$mes_visu_count = date("n") - $_GET['meses_visualizacoes'];
							$mes_visu_select = $_GET['meses_visualizacoes'];
						}
						
						if($mes_visu_select <= 9) {
							$mes_visu_select = "0".$mes_visu_select;
						}
						
						if(isset($_GET['ano_visualizacoes']) && $_GET['ano_visualizacoes'] != "") {
							$mes_visu_count = ((date("n") - $_GET['meses_visualizacoes']) + $_GET['ano_visualizacoes']);
							$ano_visu_select = date('Y', strtotime("- ".($_GET['ano_visualizacoes'] / 12)." year"));
						}
												
						$queryTotalViews = "SELECT COUNT(*) as cnt FROM wp_ferdzbanner_impressaocount WHERE id_banner = " . $id . " AND data_clique >= '".$ano_visu_select."-".$mes_visu_select."-01' AND data_clique <= '".$ano_visu_select."-".$mes_visu_select."-".$diferenca2."';";
						
						$res = $wpdb->get_results( $queryTotalViews );
						
						$viewCounter = $viewCounter + intval($res[0]->cnt);
						$idPost = $id;
						
						endwhile;
						if($diferenca2 == 0) {
							$diferenca2 = 1;
						}
						echo round($viewCounter / $diferenca2) .' <span style="display:none">'.$queryTotalViews.'</span> </td>';
						echo "</tr>";
					}
				}
			}
		?>
		
		
		<?php getPostsByTaxonomy2(); ?>
 
			<?php //} ?>
</table>
	
<?php
}?>
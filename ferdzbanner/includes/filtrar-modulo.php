<?php
				

// Adiciona menu para relatórios e gráficos -----------------------------------
function inserir_menu_ferdzbanner_relatorios() {
	// Add to admin_menu function
	add_submenu_page('edit.php?post_type=banners', __('Relatórios'), __('Relatórios'), 'edit_pages', 'my_new_submenu_relatorios', 'my_menu_render_relatorios');
	
}

function my_menu_render_relatorios() {
    global $title;
?>
<link rel="stylesheet" href="<?php echo  plugins_url(); ?>/ferdzbanner/jquery-ui.min.css"/>
<script src="<?php echo  plugins_url(); ?>/ferdzbanner/jquery-ui.min.js"></script>
<script src="<?php echo  plugins_url(); ?>/ferdzbanner/datepicker-pt-BR.js"></script>
<script>
	
	function yyyymmdd(obj){
		
		var d1 = obj.split("/");
		
		return d1[2] + "-" + d1[1] + "-" + d1[0];
		
	}
	
	jQuery(document).ready(function() {
		
		jQuery( document ).ajaxStart(function() {
			jQuery(".enviando-msg").fadeIn();
		});
		
		jQuery( document ).ajaxComplete(function() {
			jQuery(".enviando-msg").fadeOut();
		});
		
		
		//jQuery.datepicker.setDefaults( jQuery.datepicker.formatDate( "dd/mm/yy") );
		jQuery( "input[name='dtinicio']" ).datepicker({
			dateFormat: "dd/mm/yy"
		});
		jQuery( "input[name='dttermino']" ).datepicker({
			dateFormat: "dd/mm/yy"
		});
		jQuery.datepicker.setDefaults( jQuery.datepicker.regional[ "pt-BR" ] );
		
		jQuery.datepicker.regional[ "pt_br" ];
		jQuery("input[name='dtinicio']").datepicker();
		jQuery("input[name='dttermino']").datepicker();
		
		jQuery("#form-filtros").submit(function() {
				
				var dti = yyyymmdd(jQuery("input[name='dtinicio']").val());
				var dtiDate = new Date(yyyymmdd(jQuery("input[name='dtinicio']").val()));
				var dtt = yyyymmdd(jQuery("input[name='dttermino']").val());
				var dttDate = new Date(yyyymmdd(jQuery("input[name='dttermino']").val()));
				if(dti > dtt) {
					alert("A data de início deve ser menor que a data de término.");
					return;
				} else {
						
				
			var form = "dtinicio="+dti+"&dttermino="+dtt;
			jQuery.ajax({
				type: 'POST',
				url: "<?php echo  plugins_url(); ?>/ferdzbanner/includes/filtrar.php",
				async: true,
				data: form,
				success: function (data) {
					jQuery("#resu").html(data);
				},
				error: function (xhr, ajaxOptions, thrownError) {
					
				},
				beforeSend: function () {
					
				},
				complete: function () {
					
				}
			});
			
			}
			
		});
		
		jQuery(".btn-enviar-relatorio").click(function() {
				
			var dti = yyyymmdd(jQuery("input[name='dtinicio']").val());
			var dtiDate = new Date(yyyymmdd(jQuery("input[name='dtinicio']").val()));
			var dtt = yyyymmdd(jQuery("input[name='dttermino']").val());
			var dttDate = new Date(yyyymmdd(jQuery("input[name='dttermino']").val()));
			if(dti > dtt) {
				alert("A data de início deve ser menor que a data de término.");
				return;
			} else {
				
				
				var form = "dtinicio="+dti+"&dttermino="+dtt+"&enviaemail=1&labelinicio=" + jQuery("input[name='dtinicio']").val() + "&labeltermino=" + jQuery("input[name='dttermino']").val();
				jQuery.ajax({
					type: 'POST',
					url: "<?php echo  plugins_url(); ?>/ferdzbanner/includes/filtrar.php",
					async: true,
					data: form,
					success: function (data) {
						jQuery(".enviando-msg").fadeOut();						
						alert(data);
					},
					error: function (xhr, ajaxOptions, thrownError) {
						
					},
					beforeSend: function () {
						jQuery(".enviando-msg").fadeIn();
					},
					complete: function () {
						
					}
				});
				
			}
				
		});
		
		jQuery(".btn-filtrar-individual").click(function() {
			filtrarIndividual();	
		});
		
		jQuery(".btn-enviar-relatorio-individual").click(function() {
			enviarIndividual();			
		})
		
	});
	
	function filtrarIndividual() {
			
		var dti = yyyymmdd(jQuery("input[name='dtinicio']").val());
		var dtiDate = new Date(yyyymmdd(jQuery("input[name='dtinicio']").val()));
		var dtt = yyyymmdd(jQuery("input[name='dttermino']").val());
		var dttDate = new Date(yyyymmdd(jQuery("input[name='dttermino']").val()));
		if(dti > dtt) {
			alert("A data de início deve ser menor que a data de término.");
			return;
		} else if(jQuery("select[name='banner_individual']").val() == ""){
				
			alert("Por favor, escolha algum banner para filtrar individualmente.");
			return;
		} else {
			
			var form = "dtinicio="+dti+"&dttermino="+dtt + "&banner_id=" + jQuery("select[name='banner_individual']").val() + "&range_maximo=" +  jQuery("input[name='range_maximo']").val() + "&labelinicio=" + jQuery("input[name='dtinicio']").val() + "&labeltermino=" + jQuery("input[name='dttermino']").val();
			jQuery.ajax({ 
				type: 'POST',
				url: "<?php echo  plugins_url(); ?>/ferdzbanner/includes/filtrar.php",
				async: true,
				data: form,
				success: function (data) {
					jQuery("#resu").html(data);
				},
				error: function (xhr, ajaxOptions, thrownError) {
					
				},
				beforeSend: function () {
					
				},
				complete: function () {
					
				}
			});
			
		}
			
	}
	
	function enviarIndividual() {
		var dti = yyyymmdd(jQuery("input[name='dtinicio']").val());
		var dtiDate = new Date(yyyymmdd(jQuery("input[name='dtinicio']").val()));
		var dtt = yyyymmdd(jQuery("input[name='dttermino']").val());
		var dttDate = new Date(yyyymmdd(jQuery("input[name='dttermino']").val()));
		if(dti > dtt) {
			alert("A data de início deve ser menor que a data de término.");
			return;
		} else {
			
			
			var form = "dtinicio="+dti+"&dttermino="+dtt+"&enviaemail=1&labelinicio=" + jQuery("input[name='dtinicio']").val() + "&labeltermino=" + jQuery("input[name='dttermino']").val() + "&banner_id=" +  jQuery("select[name='banner_individual']").val() + "&range_maximo=" +  jQuery("input[name='range_maximo']").val();
			jQuery.ajax({
				type: 'POST',
				url: "<?php echo  plugins_url(); ?>/ferdzbanner/includes/filtrar.php",
				async: true,
				data: form,
				success: function (data) {
					jQuery(".enviando-msg").fadeOut();						
					alert(data);
				},
				error: function (xhr, ajaxOptions, thrownError) {
					
				},
				beforeSend: function () {
					jQuery(".enviando-msg").fadeIn();
				},
				complete: function () {
					
				}
			});
			
		}
		
	}
	
</script>


<form method="post" id="form-filtros" action="javascript:void(0)">
	
	<table class="form-table">
		<tr valign="top">
			<th colspan="2"><h1><?php echo $title;?></h1></th>
		</tr>
		<tr>
						<th scope="row">Filtros:</th>
		</tr>
		
		<tr>
			<td>
				<div style="display:inline-block;width:170px;top:-17px;position:relative;">Data de Início:<br/><input name="dtinicio" type="text"/></div>
			</td>
			<td>
				<div style="display:inline-block;width:170px;top:-17px;position:relative;">Data de término:<br/><input name="dttermino" type="text"/></div> 
			</td>
			
		</tr>
		
		<tr>
			<td style="padding-bottom:0;">
				
				
				<div style="display:inline-block;top:-20px;position:relative;">Banner individual:<br/>
					
					<select style="width:150px;" name="banner_individual">
						<option value="">Selecione</option>
						<?php 
							
							$qry = new WP_Query(array("post_type" => "banners", 'posts_per_page' => -1, "meta_key" => "ativo", "meta_value" => "ativo"));
							
							for($i=0;$i<count($qry->posts);$i++) {
								echo "<option value='".$qry->posts[$i]->ID."'>".$qry->posts[$i]->post_title."</option>";
							}
							
						?>
					</select>
					
				</div> 
			</td>
			
			<td style="padding-bottom:0;">
				<div style="display:inline-block;top:-30px;position:relative;"><br/>
					Mostrar até <br/><input type="number" style="width:70px;" name="range_maximo"/>	 resultados
				</div>
			</td>
			
		</tr>
		
		<tr>
			<td colspan="2" style="padding-top:0;">
				<input class="button" type="submit" value="Filtrar"/>
				<input class="button btn-filtrar-individual" type="button" value="Filtrar Individualmente"/>				
				<input class="button btn-enviar-relatorio" type="button" value="Enviar por E-mail"/>
				<input class="button btn-enviar-relatorio-individual" type="button" value="Enviar relatório individual por E-mail"/>
				<br/>
				<span style="position:relative;"><a target="_blank" href="edit.php?post_type=banners&page=configuracoes_ferdzbanner_relatoriosemail#config">Clique aqui e inclua o e-mail para envio</a></span> 
				<span style="position:relative;display:none;" class="enviando-msg">Enviando...</span>
			</td>
		</tr>
		
        <tr valign="top">


			<tr valign="top">
				<td colspan="2">
					<div id="resu"></div>
				</td>
			</tr>
		</table>
		
	</form>
	
	
	
	<?php
	}

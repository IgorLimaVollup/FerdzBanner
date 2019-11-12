<?php
// Página de disparo de relatórios por e-mail


//Adiciona o menu para configurações gerais
function inserir_menu_ferdzbanner_relatoriosemail() {
	// Add to admin_menu function
	add_submenu_page('edit.php?post_type=banners', __('Disparo de Relatórios'), __('Disparo de Relatórios'), 'edit_pages', 'configuracoes_ferdzbanner_relatoriosemail', 'my_menu_render_confs_relatorioemail');
	add_action( 'admin_init', 'registrar_configuracoes_ferdzbanner_relatorioemail' );
}


function registrar_configuracoes_ferdzbanner_relatorioemail() {
	//register our settings
	register_setting( 'grupo-configuracoes-ferdzbanner-relatoriosemail', 'recebimento_email' );
}

function my_menu_render_confs_relatorioemail() {
    global $title;
?>
<link href="<?php echo  plugins_url(); ?>/ferdzbanner/jquery.tagsinput.css "/>
<script src="<?php echo  plugins_url(); ?>/ferdzbanner/jquery.tagsinput.js"></script>
<style>
	.form-opts-banners-emails th{
	width:350px;
	font-weight:400;
	text-align:left;
	}
	
	.tag {
    padding: 5px;
    border: 1px black solid;
    margin: 3px;
    display: inline-block;
	}
	
	.tagsinput {
			width:100% !important;
	}
	
	#tags_addTag {
		
	}
	
	#tags_addTag input {
	border: 1px solid #ddd;
    -webkit-box-shadow: inset 0 1px 2px rgba(0,0,0,.07);
    box-shadow: inset 0 1px 2px rgba(0,0,0,.07);
    background-color: #fff;
    color: #32373c;
    outline: 0;
    -webkit-transition: 50ms border-color ease-in-out;
    transition: 50ms border-color ease-in-out;
	}
	
	#tags_addTag input:focus {
	border-color: #5b9dd9;
    -webkit-box-shadow: 0 0 2px rgba(30,140,190,.8);
    box-shadow: 0 0 2px rgba(30,140,190,.8);
	}
</style>
<?php
	if(isset($_GET['settings-updated']) && $_GET['settings-updated'] == "true") {
	?>
	<div style="margin-left:2px;" class="notice notice-success is-dismissible">
		<p>Configurações salvas.</p>
	</div>
	<?php
	}
?>

<?php
	if(isset($_GET['settings-updated']) && $_GET['settings-updated'] == "disparo") {
	?>
	<div style="margin-left:2px;" class="notice notice-success is-dismissible">
		<p>Disparo feito com sucesso.</p>
	</div>
	<?php
	}
?>


<?php
	if(isset($_GET['settings-updated']) && $_GET['settings-updated'] == "disparoerro") {
	?>
	<div style="margin-left:2px;" class="notice notice-error is-dismissible">
		<p>Disparo feito com sucesso.</p>
	</div>
	<?php
	}
?>
<script>
	jQuery(document).ready(function() {
		jQuery('#tags').tagsInput();	
	});
</script>

    <table class="form-table form-opts-banners-emails">
		<tr valign="top">
			<th colspan="2"><h1>Disparo de relatórios por e-mail</h1></th>
		</tr>
        <tr valign="top">
			<th scope="row">Disparar relatório do dia anterior:</th>
			<td>
				<a href="<?php echo  plugins_url(); ?>/ferdzbanner/relatorio.php" class="button">Disparar</a>
			</td>
        </tr>
		<tr valign="top">
			<th scope="row">Disparar relatório semanal (Últimos 7 dias):</th>
			<td>
				<a href="<?php echo  plugins_url(); ?>/ferdzbanner/relatorio-4.php" class="button">Disparar</a>
			</td>
        </tr>
		
		<tr valign="top">
			<th scope="row">Disparar relatório quinzenal (Últimos 15 dias):</th>
			<td>
				<a href="<?php echo  plugins_url(); ?>/ferdzbanner/relatorio-2.php" class="button">Disparar</a>
			</td>
        </tr>


		
		<tr valign="top">
			<th scope="row">Disparar relatório mensal (Mês Passado):</th>
			<td>
				<a href="<?php echo  plugins_url(); ?>/ferdzbanner/relatorio-3.php" class="button">Disparar</a>
			</td>
        </tr>
		
		<tr valign="top">
			<th colspan="2"><h1 id="config">Configurações de disparo</h1></th>
		</tr>
	</table>	
<form method="post" id="form-opts-relatorios-banners" action="options.php">
    <?php settings_fields( 'grupo-configuracoes-ferdzbanner-relatoriosemail' ); ?>
    <?php do_settings_sections( 'grupo-configuracoes-ferdzbanner-relatoriosemail' ); ?>
	
		<table class="form-opts-banners-emails">
		
		<tr valign="top">
			<th scope="row">E-mail de recebimento:</th>
			<td style="width:500px;">
				<input id="tags" name="recebimento_email" value="<?php if(get_option('recebimento_email')) { echo  get_option('recebimento_email'); }?>" type="text"/>
			</td>
        </tr>
		
		
		
		
    </table>
    
    <?php submit_button(); ?>
	
</form>
<?php
}
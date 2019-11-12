<?php //Adiciona o menu para configurações gerais
function inserir_menu_ferdzbanner_confs() {
	// Add to admin_menu function
	add_submenu_page('edit.php?post_type=banners', __('Configurações'), __('Configurações'), 'edit_pages', 'configuracoes_ferdzbanner', 'my_menu_render_confs');
	add_action( 'admin_init', 'registrar_configuracoes_ferdzbanner' );
}


function registrar_configuracoes_ferdzbanner() {
	//register our settings
	register_setting( 'grupo-configuracoes-ferdzbanner', 'pre_visualizacao' );
	register_setting( 'grupo-configuracoes-ferdzbanner', 'ativo_desativo' );
	register_setting( 'grupo-configuracoes-ferdzbanner', 'cliques' );
	register_setting( 'grupo-configuracoes-ferdzbanner', 'visualizacoes' );
	register_setting( 'grupo-configuracoes-ferdzbanner', 'posicao' );
	register_setting( 'grupo-configuracoes-ferdzbanner', 'cliente' );
}

function my_menu_render_confs() {
    global $title;
?>
<style>
	#form-opts-banners th, .form-opts-banners th{
		width:350px;
		font-weight:400;
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
<form method="post" id="form-opts-banners" action="options.php">
<?php //var_dump($_GET); ?>
    <?php settings_fields( 'grupo-configuracoes-ferdzbanner' ); ?>
    <?php do_settings_sections( 'grupo-configuracoes-ferdzbanner' ); ?>

    <table class="form-table">
	<tr valign="top">
		<th colspan="2"><h1>Configurações de visualização</h1></th>
	</tr>
        <tr valign="top">
			<th scope="row">Mostrar pré-visualização do banner:</th>
			<td>
			<input type="radio" <?php if(esc_attr( get_option('pre_visualizacao') ) == "true") { echo "checked"; }?> name="pre_visualizacao" value="true"/> Sim <br/>
			<input type="radio" <?php if(esc_attr( get_option('pre_visualizacao') ) == "false" || esc_attr( get_option('pre_visualizacao') ) == "") { echo "checked"; }?> name="pre_visualizacao" value="false"/> Não <br/>
			</td>
        </tr>
		
        <tr valign="top">
			<th scope="row">Mostrar status do banner (Ativo/Desativo):</th>
			<td>
			<input type="radio" <?php if(esc_attr( get_option('ativo_desativo') ) == "true") { echo "checked"; }?> name="ativo_desativo" value="true"/> Sim <br/>
			<input type="radio" <?php if(esc_attr( get_option('ativo_desativo') ) == "false" || esc_attr( get_option('ativo_desativo') ) == "") { echo "checked"; }?> name="ativo_desativo" value="false"/> Não <br/>
			</td>
        </tr>

		
        <tr valign="top">
			<th scope="row">Mostrar quantidade de Cliques:</th>
			<td>
			<input type="radio" <?php if(esc_attr( get_option('visualizacoes') ) == "true" || esc_attr( get_option('visualizacoes') ) == "") { echo "checked"; }?> name="visualizacoes" value="true"/> Sim <br/>
			<input type="radio" <?php if(esc_attr( get_option('visualizacoes') ) == "false") { echo "checked"; }?> name="visualizacoes" value="false"/> Não <br/>
			</td>
        </tr>

        <tr valign="top">
			<th scope="row">Mostrar quantidade de Visualizações:</th>
			<td>
				<input type="radio" <?php if(esc_attr( get_option('cliques') ) == "true" || esc_attr( get_option('cliques') ) == "") { echo "checked"; }?> name="cliques" value="true"/> Sim <br/>
				<input type="radio" <?php if(esc_attr( get_option('cliques') ) == "false") { echo "checked"; }?> name="cliques" value="false"/> Não <br/>
			</td>
        </tr>
		
        <tr valign="top">
			<th scope="row">Mostrar posição do banner:</th>
			<td>
				<input type="radio" <?php if(esc_attr( get_option('posicao') ) == "true" || esc_attr( get_option('posicao') ) == "") { echo "checked"; }?> name="posicao" value="true"/> Sim <br/>
				<input type="radio" <?php if(esc_attr( get_option('posicao') ) == "false") { echo "checked"; }?> name="posicao" value="false"/> Não <br/>
			</td>
        </tr>
		
        <tr valign="top">
			<th scope="row">Mostrar cliente do banner:</th>
			<td>
				<input type="radio" <?php if(esc_attr( get_option('cliente') ) == "true" || esc_attr( get_option('cliente') ) == "") { echo "checked"; }?> name="cliente" value="true"/> Sim <br/>
				<input type="radio" <?php if(esc_attr( get_option('cliente') ) == "false") { echo "checked"; }?> name="cliente" value="false"/> Não <br/>
			</td>
        </tr>
		
    </table>
    
    <?php submit_button(); ?>
	
</form>
<?php
}
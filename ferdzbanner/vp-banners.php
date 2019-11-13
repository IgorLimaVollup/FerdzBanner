<?php
/*
Plugin Name: FerdzBanner
Plugin URI: http://vollup.com
Description: Plugin de Banners FerdzBanner
Version: 2.0
Author: Vollup 
Author URI: http://vollup.com
*/


// ---- Novo custom type

// Our custom post type function
function criar_banners() {
	
	register_post_type( 'banners',
	// CPT Options
	array(
	'labels' => array(
	'name' => __( 'Banners' ),
	'singular_name' => __( 'Banner' ),
	'add_new_item' => __( 'Adicionar novo Banner' ),
	'new_item' => __( 'Adicionar novo Banner' ),
	'add_new' => __( 'Adicionar novo Banner' ),
	'edit_item' => __( 'Editar Banner' )
	),
	'public' => false,  // it's not public, it shouldn't have it's own permalink, and so on
	'publicly_queriable' => true,  // you should be able to query it
	'show_ui' => true,  // you should be able to edit it in wp-admin
	'exclude_from_search' => true,  // you should exclude it from search results
	'show_in_nav_menus' => false,  // you shouldn't be able to add it to menus
	'has_archive' => false,  // it shouldn't have archive page
	'rewrite' => false,  // it shouldn't have rewrite rules
	'supports' => array('title'),
	)
	);
	
	//CREATE TABLE IF NOT EXISTS `test`.`t1` ( `col` VARCHAR(16) NOT NULL )
	global $wpdb;
	$wpdb->query(
	"
	CREATE TABLE IF NOT EXISTS `wp_ferdzbanner_viewcount` (
	`id_view` int(11) NOT NULL AUTO_INCREMENT,
	`id_banner` int(11) NOT NULL,
	`id_cliente` varchar(100) NOT NULL,
	`nome_cliente` varchar(100) NOT NULL,
	`data_clique` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id_view`)
	) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;
	"
	);
	
	$wpdb->query(
	"
	CREATE TABLE IF NOT EXISTS `wp_ferdzbanner_impressaocount` (
	`id_impressao` int(11) NOT NULL AUTO_INCREMENT,
	`id_banner` int(11) NOT NULL,
	`id_cliente` varchar(100) NOT NULL,
	`nome_cliente` varchar(100) NOT NULL,
	`data_clique` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id_impressao`)
	) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
	"
	);
	
}
add_action( 'init', 'criar_banners' );

register_activation_hook(__FILE__, 'cron_verifica_limite');
function cron_verifica_limite() {
	if (!wp_next_scheduled('evento_verifica_limite')) {
		wp_schedule_event(time(), 'daily', 'evento_verifica_limite');
	}
}
add_action('evento_verifica_limite', 'verifica_limite_hora');

function verifica_limite_hora() {
	global $wpdb;
	$params = array( 'post_type' => 'banners', 'meta_key' => 'ativo', 'meta_value' => 'Ativo');	

	$dadosPost = new WP_Query( $params );
	while ( $dadosPost->have_posts() ) : $dadosPost->the_post();
		$urlban = get_post_meta(get_the_id(), 'url-banner', true);
		$idbann = get_the_id();
	
		$limite = $wpdb->get_results('SELECT meta_value FROM '.$wpdb->prefix.'postmeta WHERE meta_key = "limite" AND post_id = ' .get_the_id());
		$views = $wpdb->get_results('SELECT COUNT(*) as cnt FROM wp_ferdzbanner_impressaocount WHERE id_banner = '.$idbann);

		if (intval($views[0]->cnt) >= intval($limite) && intval($limite) != 0) {
			update_post_meta($idbann, 'ativo', 'Inativo');

			// Disparo de E-mail
			require('../../../../wp-load.php');
			$mensagem = "O banner ". get_the_title() ." acabou de atingir o limite de views";
			$headers[] = 'From: Relatório FerdzBanner <fernando.santos@vollup.com>';
			$recebimento = "fernando.santos@vollup.com";
			wp_mail($recebimento, "O banner ". get_the_title() ." acabou de atingir o limite de views", $mensagem, $headers);
		}
	endwhile;
}

register_activation_hook(__FILE__, 'cron_verifica_exibicao');
function cron_verifica_exibicao() {
    if (!wp_next_scheduled('evento_verifica_exibicao')) {
		wp_schedule_event(time(), 'daily', 'evento_verifica_exibicao');
    }
}
add_action('evento_verifica_exibicao', 'verifica_exibicao_diariamente');
function verifica_exibicao_diariamente() {
	global $wpdb;
	$params = array( 'post_type' => 'banners', 'meta_key' => 'ativo', 'meta_value' => 'Ativo');			
	 
	$dadosPost = new WP_Query( $params );
	while ( $dadosPost->have_posts() ) : $dadosPost->the_post();
		$urlban = get_post_meta(get_the_id(), 'url-banner', true);
		$idbann = get_the_id();
	
		$exibe_inicio = $wpdb->get_results('SELECT meta_value FROM '.$wpdb->prefix.'postmeta WHERE meta_key = "exibicao_inicio" AND post_id = ' .get_the_id());
		foreach ( $exibe_inicio as $page ) {
			$inicio_exibicao = $page->meta_value;
		}
		$exibe_fim = $wpdb->get_results('SELECT meta_value FROM '.$wpdb->prefix.'postmeta WHERE meta_key = "exibicao_fim" AND post_id = ' .get_the_id());
		foreach ( $exibe_fim as $page ) {
			$fim_exibicao = $page->meta_value;
		}
		$dataAtual = date('Y-m-d');
		if($inicio_exibicao == "") {
			$inicio_exibicao = "01/01/1111";
		}
		if($fim_exibicao == "") {
			$fim_exibicao = "31/12/9999";
		}

		/*if($nofollow == "on" && $nofollow != "") {
			$follow = 'rel="nofollow"';
		}*/

		$inicio_exibicao = explode("/", $inicio_exibicao);
		$ini_exib = $inicio_exibicao[2]."-".$inicio_exibicao[1]."-".$inicio_exibicao[0];

		$fim_exibicao = explode("/", $fim_exibicao);
		$fim_exib = $fim_exibicao[2]."-".$fim_exibicao[1]."-".$fim_exibicao[0];

		$bannerCode .= "<div style='display:none' class='nome-cliente'>".get_the_title()."</div>";
		if(strtotime($dataAtual) >= strtotime($ini_exib) && strtotime($dataAtual) <= strtotime($fim_exib)) {/*echo "aparece";*/}
		else {
			update_post_meta($idbann, 'ativo', 'Inativo');
			// Disparo de E-mail
			require('../../../../wp-load.php');
			$mensagem = "O banner ". get_the_title() ." acabou de atingir a data limite";
			$headers[] = 'From: Relatório FerdzBanner <fernando.santos@vollup.com>';
			$recebimento = "fernando.santos@vollup.com";
			wp_mail($recebimento, "O banner ". get_the_title() ." acabou de atingir a data limite", $mensagem, $headers);
		}
	endwhile;
}

register_deactivation_hook(__FILE__, 'desabilita_cron_verifica_exibicao');

function desabilita_cron_verifica_exibicao() {
	wp_clear_scheduled_hook('evento_verifica_exibicao');
}


// Início da inserção do novo campo de upload de arquivo


function add_custom_meta_boxes() {
	
    // Define the custom attachment for posts
    add_meta_box(
	'wp_custom_attachment',
	'Upload do Banner',
	'wp_custom_attachment',
	'banners',
	'advanced'
    );
	
	
	add_meta_box(
	'wp_custom_attachment_expansivo',
	'Upload do Banner Expansivo (até 1000px de altura)',
	'wp_custom_attachment_expansivo',
	'banners',
	'advanced'
    );
	
	
	
} // end add_custom_meta_boxes
add_action( 'add_meta_boxes', 'prfx_custom_meta' );
add_action( 'add_meta_boxes', 'prfx_custom_meta_target' );
add_action( 'add_meta_boxes', 'prfx_custom_meta_limit_view' );
add_action( 'add_meta_boxes', 'prfx_custom_meta_exibicao_inicio' );
add_action( 'add_meta_boxes', 'prfx_custom_meta_exibicao_fim' );
add_action( 'add_meta_boxes', 'prfx_custom_meta_ativo' );
add_action( 'add_meta_boxes', 'prfx_custom_meta_tipo_banner' );
add_action( 'add_meta_boxes', 'prfx_custom_meta_zip_file' );
add_action( 'add_meta_boxes', 'prfx_custom_meta_html_code' );
add_action( 'add_meta_boxes', 'prfx_custom_meta_tipo_exib' );


add_action('add_meta_boxes', 'add_custom_meta_boxes');

// -----------------------


function prfx_custom_meta_limit_view() {
	global $post;
	wp_nonce_field( basename( __FILE__ ), 'prfx_nonce' );
    $prfx_stored_meta = get_post_meta( $post->ID );
	add_meta_box( 'prfx_meta_limit', __( 'Limite de Visualizações', 'prfx-limit' ), 'prfx_meta_callback_limit', 'banners' );
}

function prfx_meta_callback_limit($post) {
	wp_nonce_field( basename( __FILE__ ), 'prfx_nonce' );
    $prfx_stored_meta = get_post_meta( $post->ID );
	?>
		<p>
			<input type="text" name="limite" id="limit-banner" value="<?php if($prfx_stored_meta['limite'] != "") {echo $prfx_stored_meta['limite'][0];} ?>" placeholder="0" />
			<label style="vertical-align: middle;display: inline-block;margin-bottom: 5px;margin-left: 20px">
				<input class="ilimitado" <?php if ($prfx_stored_meta['limite'][0] == "") echo "checked"; ?> type="checkbox"> Ilimitado
			</label>
		</p>
		<script>
			jQuery(document).ready(function() {
				if (jQuery(".ilimitado").is(":checked")) {
					jQuery("#limit-banner")
						.attr('readonly', 'true')
						.unmask()
						.attr("value", "");
				}
			});
			jQuery(".ilimitado").click(function () {
				if (jQuery(this).is(':checked')) {
					jQuery("#limit-banner").attr('readonly', 'true');
					//jQuery("#limit-banner").unmask();
					jQuery("#limit-banner").attr("value", "");
					jQuery("#limit-banner").removeClass("error");
					jQuery(".button.button-primary.button-large").removeAttr("disabled");
				} else {
					jQuery("#limit-banner").removeAttr('readonly');
					//jQuery("#limit-banner").mask("99999?9");
					jQuery("#limit-banner").focus();
				}
			});
			jQuery("#limit-banner").blur(function() {
				var data = jQuery(this).val();
				var RegExPattern = /^\d+$/;

				if (!RegExPattern.test(data)){
					if (data != "") {
						jQuery(".button.button-primary.button-large").attr("disabled", "disabled");
						jQuery(this).addClass('error');
					}
				} else {
					jQuery(this).removeClass('error');
					jQuery(".button.button-primary.button-large").removeAttr("disabled");
				}
			});
		</script>
	<?php
}

function prfx_meta_save_limit($post_id) {
	global $post;
	global $wp_query;
	// Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
	$is_valid_nonce = ( isset( $_POST[ 'prfx_nonce' ] ) && wp_verify_nonce( $_POST[ 'prfx_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
	
	// Exits script depending on save status
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
	}
	
	// Checks for input and sanitizes/saves if needed
    if( isset( $_POST[ 'limite' ] ) ) {
		$ativ = "";
        update_post_meta( $post_id, 'limite', sanitize_text_field( $_POST[ 'limite' ] ) );
    }
}
add_action( 'save_post', 'prfx_meta_save_limit' );

// Código para o campo de URL
/**
 * Adds a meta box to the post editing screen
 */
function prfx_custom_meta() {
    add_meta_box( 'prfx_meta', __( 'Link do Banner', 'prfx-textdomain' ), 'prfx_meta_callback', 'banners' );
}

/**
 * Outputs the content of the meta box
 */
function prfx_meta_callback( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'prfx_nonce' );
    $prfx_stored_meta = get_post_meta( $post->ID );
?>



<p>
	<!--<label for="url-banner" class="prfx-row-title"><?php _e( '', 'prfx-textdomain' )?></label>-->
	<input type="text" style="width:100%;" name="url-banner" id="url-banner" value="<?php if ( isset ( $prfx_stored_meta['url-banner'] ) ) echo $prfx_stored_meta['url-banner'][0]; ?>" />
</p>
<?php
}

/**
 * Saves the custom meta input
 */
function prfx_meta_save( $post_id ) {
	global $post;
	global $wp_query;
    // Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'prfx_nonce' ] ) && wp_verify_nonce( $_POST[ 'prfx_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
	
    // Exits script depending on save status
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
    }
	
    // Checks for input and sanitizes/saves if needed
    if( isset( $_POST[ 'url-banner' ] ) ) {
        update_post_meta( $post_id, 'url-banner', sanitize_text_field( $_POST[ 'url-banner' ] ) );
    }
	
}
add_action( 'save_post', 'prfx_meta_save' );


// Código para o campo de URL




function prfx_custom_meta_tipo_exib() {
    add_meta_box( 'prfx_meta_tipo_exib', __( 'Tipo de Exibição', 'prfx-tipo-exib' ), 'prfx_meta_callback_tipo_exib', 'banners' );
}



function prfx_meta_callback_tipo_exib( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'prfx_nonce' );
    $prfx_stored_meta = get_post_meta( $post->ID );
?>
<script>
	
	jQuery(document).ready(function() {
		
		if(jQuery("select[name='tipo_exib']").val() == "Expansivo") {
			jQuery("#wp_custom_attachment_expansivo").fadeIn();
		} else if(jQuery("select[name='tipo_exib']").val() == "Normal") {
			jQuery("#wp_custom_attachment_expansivo").hide();
		}
		
		jQuery("select[name='tipo_exib']").change(function() {
			if(jQuery(this).val() == "Expansivo") {
				jQuery("#wp_custom_attachment_expansivo").fadeIn();
			} else if(jQuery(this).val() == "Normal") {
				jQuery("#wp_custom_attachment_expansivo").fadeOut();
			}
		});
		
		
		
		if(jQuery("select[name='tipo_banner']").val() == "imagem") {
			jQuery("#wp_custom_attachment").fadeIn();
			jQuery("#prfx_meta_tipo_exib").fadeIn();
			jQuery("#prfx_meta_zip_file").hide();
			jQuery("#prfx_meta_html_code").hide();
		} else if(jQuery("select[name='tipo_banner']").val() == "zip") {
			jQuery("#wp_custom_attachment_expansivo").hide();
			jQuery("#wp_custom_attachment").hide();
			jQuery("#wp_custom_attachment_expansivo").hide();
			jQuery("#prfx_meta_tipo_exib").hide();
			jQuery("#prfx_meta_html_code").hide();
			jQuery("#prfx_meta_zip_file").fadeIn();
		} else if(jQuery("select[name='tipo_banner']").val() == "html") {
			jQuery("#wp_custom_attachment_expansivo").hide();
			jQuery("#wp_custom_attachment").hide();
			jQuery("#wp_custom_attachment_expansivo").hide();
			jQuery("#prfx_meta_tipo_exib").hide();
			jQuery("#prfx_meta_zip_file").hide();
			jQuery("#prfx_meta_html_code").fadeIn();
		}
		
		jQuery("select[name='tipo_banner']").change(function() {
			if(jQuery(this).val() == "imagem") {
				jQuery("#wp_custom_attachment").fadeIn();
				jQuery("#prfx_meta_tipo_exib").fadeIn();
				jQuery("#prfx_meta_zip_file").hide();
				jQuery("#prfx_meta_html_code").hide();
			} else if(jQuery(this).val() == "zip") {
				jQuery("#wp_custom_attachment_expansivo").hide();
				jQuery("#wp_custom_attachment").hide();
				jQuery("#wp_custom_attachment_expansivo").hide();
				jQuery("#prfx_meta_tipo_exib").hide();
				jQuery("#prfx_meta_html_code").hide();
				jQuery("#prfx_meta_zip_file").fadeIn();
			} else if(jQuery(this).val() == "html") {
				jQuery("#wp_custom_attachment_expansivo").hide();
				jQuery("#wp_custom_attachment").hide();
				jQuery("#wp_custom_attachment_expansivo").hide();
				jQuery("#prfx_meta_tipo_exib").hide();
				jQuery("#prfx_meta_zip_file").hide();
				jQuery("#prfx_meta_html_code").fadeIn();
			}
		});
		
		jQuery("input[name='zip_file']").change(function() {
			validaExtensao('zip');
		});
		
	});
	
	function validaExtensao(extensao, codigo = "") {
		var _validFileExtensions = ["."+extensao];    
		var arquivo = jQuery("input[name='"+extensao+"_file']");
		var sFileName = arquivo.val();
		if (sFileName.length > 0) {
			var blnValid = false;
			for (var j = 0; j < _validFileExtensions.length; j++) {
				var sCurExtension = _validFileExtensions[j];
				if (sFileName.substr(sFileName.length - sCurExtension.length, sCurExtension.length).toLowerCase() == sCurExtension.toLowerCase()) {
					blnValid = true;
					var file_data = arquivo.prop('files')[0];   
					var form_data = new FormData();                  
					form_data.append('file', file_data);	
					form_data.append('post_id', jQuery("#post_ID").val());
					form_data.append('extensao', extensao);
					$.ajax({
						url: '/wp-content/plugins/ferdzbanner/zip_files.php', 
						dataType: 'text',
						cache: false,
						contentType: false,
						processData: false,
						data: form_data,                         
						type: 'post',
						success: function(php_script_response){
							console.log(php_script_response);
						},
						error: function (xhr, ajaxOptions, thrownError) {
							console.log(xhr, ajaxOptions, thrownError);
						},
					});
					break;
				}
			}
			if (!blnValid) {
				alert("Arquivo inválido! Extensão de arquivo permitida: ."+extensao);
				arquivo.val("");
				return false;
			}
		}
		return true;
	}
	
</script>
<p>
	<!--<label for="url-banner" class="prfx-row-title"><?php _e( '', 'prfx-ativo' )?></label>-->	
	<select name="tipo_exib" id="tipo_exib" style="width:100%;">
		<?php 
			if ( isset ( $prfx_stored_meta['ativo'] ) ) { 
				//echo $prfx_stored_meta['url-banner'][0]; 
			?>
			<option <?php if($prfx_stored_meta['tipo_exib'][0] == "Normal") {echo "selected";} ?> value="Normal">Normal</option>
			<option <?php if($prfx_stored_meta['tipo_exib'][0] == "Expansivo") {echo "selected";} ?> value="Expansivo">Expansivo</option>
			<?php }  else { ?>
			<option value="Normal">Normal</option>
			<option value="Expansivo">Expansivo</option>
		<?php } ?>
	</select>
	
	
</p>
<?php
}

function prfx_meta_save_tipo_exib( $post_id ) {
	global $post;
	global $wp_query;
    // Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'prfx_nonce' ] ) && wp_verify_nonce( $_POST[ 'prfx_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
	
    // Exits script depending on save status
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
    }
	
    // Checks for input and sanitizes/saves if needed
    if( isset( $_POST[ 'tipo_exib' ] ) ) {
		$ativ = "";
        update_post_meta( $post_id, 'tipo_exib', sanitize_text_field( $_POST[ 'tipo_exib' ] ) );
    }
	
}
add_action( 'save_post', 'prfx_meta_save_tipo_exib' );



function wp_custom_attachment_expansivo() {
	
	
?>

<!-- Teste de campo de galeria de imagens -->

<?php wp_enqueue_media(); ?>

<?php
    wp_nonce_field(plugin_basename(__FILE__), 'wp_custom_attachment_nonce');
	
	global $post;
	
	// Get WordPress' media upload URL
	$upload_link = esc_url( get_upload_iframe_src( 'image', $post->ID ) );
	
	// See if there's a media id already saved as post meta
	$your_img_id = get_post_meta( $post->ID, '_your_img_id', true );
	
	// Get the image src
	$your_img_src = wp_get_attachment_image_src( $your_img_id, 'full' );
	
	// For convenience, see if the array is valid
	$you_have_img = is_array( $your_img_src );
?>

<!-- Select para mudar o tipo do banner à ser inserido, o padrão é da galeria do wordpress -->

<!-- Your image container, which can be manipulated with js -->

<div class="custom-img-container-expansivo">
    <?php if ( get_post_meta(get_the_ID(), 'wp_custom_attachment_expansivo', true) !== "") { 
		$a = get_post_meta(get_the_ID(), 'wp_custom_attachment_expansivo', true);?>
		<?php if(is_array($a))  { ?>
			<img src="<?php echo $a['url']; ?>" alt="" style="max-width:100%;" />
			<?php } else { ?>
			<img src="<?php echo $a; ?>" alt="" style="max-width:100%;" />
		<?php } ?>
		
		<?php } else { ?>
		<div><?php echo get_post_meta(get_the_ID(), 'wp_custom_attachment_expansivo', true); ?></div>
	<?php } ?>
</div>

<!-- Your add & remove image links -->
<p class="hide-if-no-js">
	
	<!--
		<?php if(get_post_meta(get_the_ID(), 'wp_custom_attachment_type_expansivo', true) !== "Da galeria" || get_post_meta(get_the_ID(), 'wp_custom_attachment_type_expansivo', true) !== "") { ?> <?php } ?> <?php if ( $you_have_img  ) { echo 'hidden'; } ?>
	-->
    <a  class="upload-custom-img-expansivo " 
	href="<?php echo $upload_link ?>">
        <?php _e('Escolher imagem da galeria') ?>
    </a>
	
	
</p>

<!-- A hidden input to set and post the chosen image id -->
<?php if(is_array($a))  { ?>
	<input class="custom-img-id-expansivo" name="wp_custom_attachment_expansivo" type="hidden" style="visibility:hidden;" value="<?php echo $a['url']; ?>" />
	<?php } else { ?>
	<input class="custom-img-id-expansivo" name="wp_custom_attachment_expansivo" type="hidden" style="visibility:hidden;" value="<?php echo $a; ?>" />
	
<?php } ?>

<script>
	
	function mudaForma(val) {
		if(val == "Da galeria") {
			jQuery(".upload-custom-img-expansivo").fadeIn();
			jQuery(".imagem-por-url-expansivo").fadeOut();
			jQuery(".imagem-por-html-expansivo").fadeOut();
		} else if(val == "URL") {
			jQuery(".upload-custom-img-expansivo").fadeOut();
			jQuery(".imagem-por-url-expansivo").fadeIn();
			jQuery(".imagem-por-html-expansivo").fadeOut();
		} else if(val == "Código HTML") {
			jQuery(".upload-custom-img-expansivo").fadeOut();
			jQuery(".imagem-por-url-expansivo").fadeOut();
			jQuery(".imagem-por-html-expansivo").fadeIn();
		}
	}
	
	jQuery(document).ready(function() {
		//mudaForma(jQuery("select[name='wp_custom_attachment_type_expansivo']").val());
	});
	
	jQuery(function($){
		
		// Set all variables to be used in scope
		var frame,
		metaBox = $('#meta-box-id.postbox'), // Your meta box id here
		addImgLink = jQuery('.upload-custom-img-expansivo'),
		delImgLink = jQuery( '.delete-custom-img-expansivo'),
		imgContainer = jQuery( '.custom-img-container-expansivo'),
		imgIdInput = jQuery( '.custom-img-id-expansivo' );
		
		// ADD IMAGE LINK
		addImgLink.on( 'click', function( event ){
			
			event.preventDefault();
			
			// If the media frame already exists, reopen it.
			if ( frame ) {
				frame.open();
				return;
			}
			
			// Create a new media frame
			frame = wp.media({
				title: 'Escolha ou faça o upload de um banner',
				button: {
					text: 'Usar este banner'
				},
				multiple: false  // Set to true to allow multiple files to be selected
			});
			
			
			// When an image is selected in the media frame...
			frame.on( 'select', function() {
				
				// Get media attachment details from the frame state
				var attachment = frame.state().get('selection').first().toJSON();
				
				// Send the attachment URL to our custom image input field.
				imgContainer.html( '<img src="'+attachment.url+'" alt="" style="max-width:100%;"/>' );
				
				// Send the attachment id to our hidden input
				imgIdInput.val( attachment.url );
				
				// Hide the add image link
				//addImgLink.addClass( 'hidden' );
				
				// Unhide the remove image link
				//delImgLink.removeClass( 'hidden' );
			});
			
			// Finally, open the modal on click
			frame.open();
		});
		
		
		// DELETE IMAGE LINK
		delImgLink.on( 'click', function( event ){
			
			event.preventDefault();
			
			// Clear out the preview image
			imgContainer.html( '' );
			
			// Un-hide the add image link
			addImgLink.removeClass( 'hidden' );
			
			// Hide the delete image link
			delImgLink.addClass( 'hidden' );
			
			// Delete the image id from the hidden input
			imgIdInput.val( '' );
			
		});
		
	});
</script>
<!-- X Teste de campo de galeria de imagens -->

<div class="imagem-por-url-expansivo" <?php if(get_post_meta(get_the_ID(), 'wp_custom_attachment_type_expansivo', true) !== "URL") { ?>  style="display:none;" <?php } ?>>
	<input type="text" class="form-control" name="url-image-expansivo" <?php if(get_post_meta(get_the_ID(), 'wp_custom_attachment_type_expansivo', true) == "URL") { ?> value="<?php echo get_post_meta(get_the_ID(), 'wp_custom_attachment', true); ?>" <?php } ?> placeholder="URL da Imagem" style="width:100%;"/>
</div>



<div class="imagem-por-html-expansivo" <?php if(get_post_meta(get_the_ID(), 'wp_custom_attachment_type_expansivo', true) !== "Código HTML") { ?> style="display:none;" <?php } ?>>
	<textarea class="form-control" name="html-image-expansivo" style="width:100%;height:160px;"> <?php if(get_post_meta(get_the_ID(), 'wp_custom_attachment_type_expansivo', true) == "Código HTML") { ?>  <?php echo get_post_meta(get_the_ID(), 'wp_custom_attachment_expansivo', true); ?> <?php } ?></textarea>
</div>

<!--
	<p>Tipo do banner:</p>
	<select style="width:100%;" onchange="mudaForma(this.value)" name="wp_custom_attachment_type_expansivo" class="form-control">
	<option <?php if(get_post_meta(get_the_ID(), 'wp_custom_attachment_type_expansivo', true) == "Da galeria") { echo "selected"; } ?> value="Da galeria">Da galeria</option>
	<option <?php if(get_post_meta(get_the_ID(), 'wp_custom_attachment_type_expansivo', true) == "URL") { echo "selected"; } ?> value="URL">URL</option>
	<option <?php if(get_post_meta(get_the_ID(), 'wp_custom_attachment_type_expansivo', true) == "Código HTML") { echo "selected"; } ?> value="Código HTML">Código HTML</option>
	</select>
-->
<br/>


<?php
	
    $html = '<style>#normal-sortables{min-height:0 !important;}</style><p class="description">';
    $html .= '</p>';
	$html .= '<script>!function(e){var t,n=function(){var e=document.createElement("input");return e.setAttribute("onpaste",""),"function"==typeof e.onpaste?"paste":"input"}()+".mask",a=navigator.userAgent,r=/iphone/i.test(a),i=/android/i.test(a);e.mask={definitions:{9:"[0-9]",a:"[A-Za-z]","*":"[A-Za-z0-9]"},dataName:"rawMaskFn",placeholder:"_"},e.fn.extend({caret:function(e,t){var n;if(0!==this.length&&!this.is(":hidden"))return"number"==typeof e?(t="number"==typeof t?t:e,this.each(function(){this.setSelectionRange?this.setSelectionRange(e,t):this.createTextRange&&((n=this.createTextRange()).collapse(!0),n.moveEnd("character",t),n.moveStart("character",e),n.select())})):(this[0].setSelectionRange?(e=this[0].selectionStart,t=this[0].selectionEnd):document.selection&&document.selection.createRange&&(n=document.selection.createRange(),e=0-n.duplicate().moveStart("character",-1e5),t=e+n.text.length),{begin:e,end:t})},unmask:function(){return this.trigger("unmask")},mask:function(a,o){var c,l,s,u,f;return!a&&this.length>0?e(this[0]).data(e.mask.dataName)():(o=e.extend({placeholder:e.mask.placeholder,completed:null},o),c=e.mask.definitions,l=[],s=f=a.length,u=null,e.each(a.split(""),function(e,t){"?"==t?(f--,s=e):c[t]?(l.push(new RegExp(c[t])),null===u&&(u=l.length-1)):l.push(null)}),this.trigger("unmask").each(function(){function h(e){for(;++e<f&&!l[e];);return e}function d(e){for(;--e>=0&&!l[e];);return e}function m(e,t){var n,a;if(!(e<0)){for(n=e,a=h(t);n<f;n++)if(l[n]){if(!(a<f&&l[n].test(y[a])))break;y[n]=y[a],y[a]=o.placeholder,a=h(a)}v(),b.caret(Math.max(u,e))}}function p(e){var t,n,a,r;for(t=e,n=o.placeholder;t<f;t++)if(l[t]){if(a=h(t),r=y[t],y[t]=n,!(a<f&&l[a].test(r)))break;n=r}}function g(e,t){var n;for(n=e;n<t&&n<f;n++)l[n]&&(y[n]=o.placeholder)}function v(){b.val(y.join(""))}function k(e){var t,n,a=b.val(),r=-1;for(t=0,pos=0;t<f;t++)if(l[t]){for(y[t]=o.placeholder;pos++<a.length;)if(n=a.charAt(pos-1),l[t].test(n)){y[t]=n,r=t;break}if(pos>a.length)break}else y[t]===a.charAt(pos)&&t!==s&&(pos++,r=t);return e?v():r+1<s?(b.val(""),g(0,f)):(v(),b.val(b.val().substring(0,r+1))),s?t:u}var b=e(this),y=e.map(a.split(""),function(e,t){if("?"!=e)return c[e]?o.placeholder:e}),x=b.val();b.data(e.mask.dataName,function(){return e.map(y,function(e,t){return l[t]&&e!=o.placeholder?e:null}).join("")}),b.attr("readonly")||b.one("unmask",function(){b.unbind(".mask").removeData(e.mask.dataName)}).bind("focus.mask",function(){clearTimeout(t);var e;x=b.val(),e=k(),t=setTimeout(function(){v(),e==a.length?b.caret(0,e):b.caret(e)},10)}).bind("blur.mask",function(){k(),b.val()!=x&&b.change()}).bind("keydown.mask",function(e){var t,n,a,i=e.which;8===i||46===i||r&&127===i?(n=(t=b.caret()).begin,(a=t.end)-n==0&&(n=46!==i?d(n):a=h(n-1),a=46===i?h(a):a),g(n,a),m(n,a-1),e.preventDefault()):27==i&&(b.val(x),b.caret(0,k()),e.preventDefault())}).bind("keypress.mask",function(t){var n,a,r,c=t.which,s=b.caret();t.ctrlKey||t.altKey||t.metaKey||c<32||c&&(s.end-s.begin!=0&&(g(s.begin,s.end),m(s.begin,s.end-1)),(n=h(s.begin-1))<f&&(a=String.fromCharCode(c),l[n].test(a)&&(p(n),y[n]=a,v(),r=h(n),i?setTimeout(e.proxy(e.fn.caret,b,r),0):b.caret(r),o.completed&&r>=f&&o.completed.call(b))),t.preventDefault())}).bind(n,function(){setTimeout(function(){var e=k(!0);b.caret(e),o.completed&&e==b.val().length&&o.completed.call(b)},0)}),k()}))}})}(jQuery);</script>';
    echo $html;
	
} // end wp_custom_attachment


function salvar_conteudo_customiz_expansivo($id) {
	global $wp_query;
	global $post;
    /* --- security verification --- */
    if(isset($_POST['wp_custom_attachment_nonce'])) {
	if(!wp_verify_nonce($_POST['wp_custom_attachment_nonce'], plugin_basename(__FILE__))) {
		return $id;
    } // end if
	}
	
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $id;
    } // end if
	
	if(isset($_POST['post_type'])) {
		    if('page' == $_POST['post_type']) {
		if(!current_user_can('edit_page', $id)) {
			return $id;
		} // end if
    } else {
        if(!current_user_can('edit_page', $id)) {
            return $id;
        } // end if
    } // end if

	}
	
	
	if(isset($_POST['wp_custom_attachment_expansivo'])) {
			add_post_meta($id, 'wp_custom_attachment_expansivo', $_POST['wp_custom_attachment_expansivo']);
	//add_post_meta($id, 'wp_custom_attachment_type_expansivo', $_POST['wp_custom_attachment_type_expansivo']);
	update_post_meta($id, 'wp_custom_attachment_expansivo', $_POST['wp_custom_attachment_expansivo']); 
	//update_post_meta($id, 'wp_custom_attachment_type_expansivo', $_POST['wp_custom_attachment_type_expansivo']); 
	

	}
	
	
} // end salvar_conteudo_customiz
add_action('save_post', 'salvar_conteudo_customiz_expansivo');

function update_edit_form_expansivo() {
    echo ' enctype="multipart/form-data"';
} // end update_edit_form
add_action('post_edit_form_tag', 'update_edit_form_expansivo');

// Término do novo campo de upload





// Código para o campo de Target
/**
 * Adds a meta box to the post editing screen
 */
function prfx_custom_meta_target() {
    add_meta_box( 'prfx_meta_target', __( 'Target', 'prfx-target' ), 'prfx_meta_callback_target', 'banners' );
}

/**
 * Outputs the content of the meta box
 */
function prfx_meta_callback_target( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'prfx_nonce' );
    $prfx_stored_meta = get_post_meta( $post->ID );
?>

<p>
	<!--<label for="url-banner" class="prfx-row-title"><?php _e( '', 'prfx-target' )?></label>-->
	
	<select name="target" id="target" style="width:100%;" data-id="<?php echo get_the_ID() ?>">
		<?php 
			if ( isset ( $prfx_stored_meta['target'] ) ) { 
				//echo $prfx_stored_meta['url-banner'][0]; 
			?>
			<option <?php if($prfx_stored_meta['target'][0] == "_blank") {echo "selected";} ?> value="_blank">_blank</option>
			<option <?php if($prfx_stored_meta['target'][0] == "_self") {echo "selected";} ?> value="_self">_self</option>
			<?php }  else { ?>
			<option value="_blank">_blank</option>
			<option value="_self">_self</option>
		<?php } ?>
	</select>
	
	
</p>
<?php
}

/**
 * Saves the custom meta input
 */
function prfx_meta_save_target( $post_id ) {
	global $post;
	global $wp_query;
    // Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'prfx_nonce' ] ) && wp_verify_nonce( $_POST[ 'prfx_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
	
    // Exits script depending on save status
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
    }
	
    // Checks for input and sanitizes/saves if needed
    if( isset( $_POST[ 'target' ] ) ) {
        update_post_meta( $post_id, 'target', sanitize_text_field( $_POST[ 'target' ] ) );
    }
	
}
add_action( 'save_post', 'prfx_meta_save_target' );


// Código para o campo de Target









// Código para o campo de Exibicao_inicio
// Adds a meta box to the post editing screen
function prfx_custom_meta_exibicao_inicio() {
    add_meta_box( 'prfx_meta_exibicao_inicio', __( 'Início da exibição', 'prfx-exibicao_inicio' ), 'prfx_meta_callback_exibicao_inicio', 'banners' );
}

//Adiciona o código HTML do campo novo ao formulário do wordpress
function prfx_meta_callback_exibicao_inicio( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'prfx_nonce' );
    $prfx_stored_meta = get_post_meta( $post->ID );
?>
	<style>
		input.error {
			border: 1px solid red !important;
		}
	</style>
	<script>
		jQuery(document).ready(function () {
			jQuery(".check-data").each(function () {
				var exibicao = jQuery(this).data("exibicao");
				if(jQuery(this).is(':checked')) {
					jQuery(".data-exibicao-"+exibicao).attr('readonly', 'true');
					jQuery(".data-exibicao-"+exibicao).unmask();
					jQuery(".data-exibicao-"+exibicao).attr("value", "");
				}
			})
			jQuery(".data-exibicao").mask("99/99/9999");
			jQuery(".data-exibicao").blur(function () {
				var data = jQuery(this).val();
				var mes = data.split("/")
				if(mes[1]=="02") {
					var RegExPattern = /^([1-9]|0[1-9]|[1,2][0-9])\/([1-9]|0[1-9]|1[0,1,2])\/\d{4}$/;
				}
				else if(mes[1]=="04" || mes[1]=="06" || mes[1]=="09" || mes[1]=="11") {
					var RegExPattern = /^([1-9]|0[1-9]|[1,2][0-9]|3[0])\/([1-9]|0[1-9]|1[0,1,2])\/\d{4}$/;
				}
				else {
					var RegExPattern = /^([1-9]|0[1-9]|[1,2][0-9]|3[0,1])\/([1-9]|0[1-9]|1[0,1,2])\/\d{4}$/;
				}
				if (!RegExPattern.test(jQuery(this).val())){
					if(jQuery(this).val() != "") {
						jQuery(".button.button-primary.button-large").attr("disabled", "disabled");
						jQuery(this).addClass('error');
						console.log('Data inválida.');
					}
				}else{
					console.log('Data válida.');
					jQuery(this).removeClass('error');
					jQuery(".button.button-primary.button-large").removeAttr("disabled");
				}
			});
			jQuery(".check-data").click(function () {
				var exibicao = jQuery(this).data("exibicao");
				if(jQuery(this).is(':checked')) {
					jQuery(".data-exibicao-"+exibicao).attr('readonly', 'true');
					jQuery(".data-exibicao-"+exibicao).unmask();
					jQuery(".data-exibicao-"+exibicao).attr("value", "");
					jQuery(".data-exibicao-"+exibicao).removeClass("error");
					jQuery(".button.button-primary.button-large").removeAttr("disabled");
				}else {
					jQuery(".data-exibicao-"+exibicao).removeAttr('readonly');
					jQuery(".data-exibicao-"+exibicao).mask("99/99/9999");
					jQuery(".data-exibicao-"+exibicao).focus();
				}
			})
		})
	</script>
	<p>
		<input type="text" name="exibicao_inicio" value="<?php if($prfx_stored_meta['exibicao_inicio'] != "") {echo $prfx_stored_meta['exibicao_inicio'][0];} ?>" placeholder="00/00/0000" class="data-exibicao data-exibicao-inicio">
		<label style="vertical-align: middle;display: inline-block;margin-bottom: 5px;margin-left: 20px">
			<input type="checkbox" <?php if($prfx_stored_meta['exibicao_inicio'][0] == "") {echo "checked";} ?> class="check-data" data-exibicao="inicio"> Vazio
		</label>
	</p>
<?php
}

//Salva
function prfx_meta_save_exibicao_inicio( $post_id ) {
	global $post;
	global $wp_query;
    // Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'prfx_nonce' ] ) && wp_verify_nonce( $_POST[ 'prfx_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
	
    // Exits script depending on save status
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
    }
	
    // Checks for input and sanitizes/saves if needed
    if( isset( $_POST[ 'exibicao_inicio' ] ) ) {
		$ativ = "";
        update_post_meta( $post_id, 'exibicao_inicio', sanitize_text_field( $_POST[ 'exibicao_inicio' ] ) );
    }
	
}
add_action( 'save_post', 'prfx_meta_save_exibicao_inicio' );
// Fim do código para o campo exibicao_inicio


// Código para o campo de Exibicao_inicio
// Adds a meta box to the post editing screen
function prfx_custom_meta_exibicao_fim() {
    add_meta_box( 'prfx_meta_exibicao_fim', __( 'Fim da exibição', 'prfx-exibicao_fim' ), 'prfx_meta_callback_exibicao_fim', 'banners' );
}

//Adiciona o código HTML do campo novo ao formulário do wordpress
function prfx_meta_callback_exibicao_fim( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'prfx_nonce' );
    $prfx_stored_meta = get_post_meta( $post->ID );
?>
	<p>
		<input type="text" name="exibicao_fim" value="<?php if($prfx_stored_meta['exibicao_fim'] != "") {echo $prfx_stored_meta['exibicao_fim'][0];} ?>" placeholder="00/00/0000" class="data-exibicao data-exibicao-fim" value="<?php if($prfx_stored_meta['exibicao_fim'] != "") {echo $prfx_stored_meta['exibicao_fim'][0];} ?>">
		<label style="vertical-align: middle;display: inline-block;margin-bottom: 5px;margin-left: 20px">
			<input <?php if($prfx_stored_meta['exibicao_fim'][0] == "") {echo "checked";} ?> type="checkbox" class="check-data" data-exibicao="fim"> Vazio
		</label>
	</p>
<?php
}

//Salva
function prfx_meta_save_exibicao_fim( $post_id ) {
	global $post;
	global $wp_query;
    // Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'prfx_nonce' ] ) && wp_verify_nonce( $_POST[ 'prfx_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
	
    // Exits script depending on save status
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
    }
	
    // Checks for input and sanitizes/saves if needed
    if ( isset( $_POST[ 'exibicao_fim' ] ) ) {
		$ativ = "";
        update_post_meta( $post_id, 'exibicao_fim', sanitize_text_field( $_POST[ 'exibicao_fim' ] ) );
    }
	
}
add_action( 'save_post', 'prfx_meta_save_exibicao_fim' );
// Fim do código para o campo exibicao_fim












// Código para o campo de Ativo
/**
 * Adds a meta box to the post editing screen
 */
function prfx_custom_meta_ativo() {
    add_meta_box( 'prfx_meta_ativo', __( 'Ativo', 'prfx-ativo' ), 'prfx_meta_callback_ativo', 'banners' );
}

/**
 * Outputs the content of the meta box
 */
function prfx_meta_callback_ativo( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'prfx_nonce' );
    $prfx_stored_meta = get_post_meta( $post->ID );
?>

<p>
	<!--<label for="url-banner" class="prfx-row-title"><?php _e( '', 'prfx-ativo' )?></label>-->
	
	<select name="ativo" id="ativo" style="width:100%;">
		<?php 
			if ( isset ( $prfx_stored_meta['ativo'] ) ) { 
				//echo $prfx_stored_meta['url-banner'][0]; 
			?>
			<option <?php if($prfx_stored_meta['ativo'][0] == "Ativo") {echo "selected";} ?> value="Ativo">Ativo</option>
			<option <?php if($prfx_stored_meta['ativo'][0] == "Inativo") {echo "selected";} ?> value="Inativo">Inativo</option>
			<?php }  else { ?>
			<option value="Ativo">Ativo</option>
			<option value="Inativo">Inativo</option>
		<?php } ?>
	</select>
	
	
</p>
<?php
}

/**
 * Saves the custom meta input
 */
function prfx_meta_save_ativo( $post_id ) {
	global $post;
	global $wp_query;
    // Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'prfx_nonce' ] ) && wp_verify_nonce( $_POST[ 'prfx_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
	
    // Exits script depending on save status
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
    }
	
    // Checks for input and sanitizes/saves if needed
    if( isset( $_POST[ 'ativo' ] ) ) {
		$ativ = "";
        update_post_meta( $post_id, 'ativo', sanitize_text_field( $_POST[ 'ativo' ] ) );
    }
	
}
add_action( 'save_post', 'prfx_meta_save_ativo' );
// Código para o campo de Ativo









// Código para o campo de Tipo de Banner
/**
 * Adds a meta box to the post editing screen
 */
function prfx_custom_meta_tipo_banner() {
    add_meta_box( 'prfx_meta_tipo_banner', __( 'Tipo de Banner', 'prfx-tipo-banner' ), 'prfx_meta_callback_tipo_banner', 'banners' );
}

/**
 * Outputs the content of the meta box
 */
function prfx_meta_callback_tipo_banner( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'prfx_nonce' );
    $prfx_stored_meta = get_post_meta( $post->ID );
?>

<p>
	<!--<label for="url-banner" class="prfx-row-title"><?php _e( '', 'prfx-tipo-banner' )?></label>-->
	
	<select name="tipo_banner" id="tipo_banner" style="width:100%;">
		<?php 
			if ( isset ( $prfx_stored_meta['tipo_banner'] ) ) { 
				//echo $prfx_stored_meta['url-banner'][0]; 
			?>
			<option <?php if($prfx_stored_meta['tipo_banner'][0] == "imagem") {echo "selected";} ?> value="imagem">Imagem</option>
			<option <?php if($prfx_stored_meta['tipo_banner'][0] == "zip") {echo "selected";} ?> value="zip">Arquivo ZIP</option>
			<option <?php if($prfx_stored_meta['tipo_banner'][0] == "html") {echo "selected";} ?> value="html">Arquivo HTML</option>
			<?php }  else { ?>
			<option value="imagem">Imagem</option>
			<option value="zip">Arquivo ZIP</option>
			<option value="html">Arquivo HTML</option>
		<?php } ?>
	</select>
	
	
</p>
<?php
}

/* Saves the custom meta input */
function prfx_meta_save_tipo_banner( $post_id ) {
	global $post;
	global $wp_query;
    // Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'prfx_nonce' ] ) && wp_verify_nonce( $_POST[ 'prfx_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
	
    // Exits script depending on save status
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
    }
	
    // Checks for input and sanitizes/saves if needed
    if( isset( $_POST[ 'tipo_banner' ] ) ) {
		$ativ = "";
        update_post_meta( $post_id, 'tipo_banner', sanitize_text_field( $_POST[ 'tipo_banner' ] ) );
    }
	
}
add_action( 'save_post', 'prfx_meta_save_tipo_banner' );
// Código para o campo de Tipo de Banner




// Código para o campo de ZIP
/**
 * Adds a meta box to the post editing screen
 */
function prfx_custom_meta_zip_file() {
    add_meta_box( 'prfx_meta_zip_file', __( 'Arquivo ZIP', 'prfx-zip-file' ), 'prfx_meta_callback_zip_file', 'banners' );
}
/**
 * Outputs the content of the meta box
 */
function prfx_meta_callback_zip_file( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'prfx_nonce' );
    $prfx_stored_meta = get_post_meta( $post->ID );
?>

<p>
	<!--<label for="url-banner" class="prfx-row-title"><?php _e( '', 'prfx-zip-file' )?></label>-->
	<?php if ( isset ( $prfx_stored_meta['zip_file'] ) ) { ?>
		Arquivo selecionado - <?php echo $prfx_stored_meta['zip_file'][0];?><br>
	<?php } ?>
	<input type="file" name="zip_file" />
</p>
<?php
}

/* Saves the custom meta input */
function prfx_meta_save_zip_file( $post_id ) {
	global $post;
	global $wp_query;
    // Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'prfx_nonce' ] ) && wp_verify_nonce( $_POST[ 'prfx_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
	
    // Exits script depending on save status
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
    }
	
    // Checks for input and sanitizes/saves if needed
    if( isset( $_FILES['zip_file'] ) && $_FILES['zip_file']['name'] != "" ) {
		$ativ = "";
        update_post_meta( $post_id, 'zip_file', sanitize_text_field( $_FILES['zip_file']['name'] ) );
		update_post_meta( $post_id, 'url_zip_file', 'arquivos/'.$post_id.'/index.html');
    }
	
}
add_action( 'save_post', 'prfx_meta_save_zip_file' );







// Código para o campo de HTML
/**
 * Adds a meta box to the post editing screen
 */
function prfx_custom_meta_html_code() {
    add_meta_box( 'prfx_meta_html_code', __( 'Código HTML', 'prfx-zip-file' ), 'prfx_meta_callback_html_code', 'banners' );
}
/**
 * Outputs the content of the meta box
 */
function prfx_meta_callback_html_code( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'prfx_nonce' );
    $prfx_stored_meta = get_post_meta( $post->ID );
?>

<p>
	<!--<label for="url-banner" class="prfx-row-title"><?php _e( '', 'prfx-zip-file' )?></label>-->
	<?php 
		$codigo = "";
		if ( isset ( $prfx_stored_meta['html_code'] ) ) { 
			$codigo = $prfx_stored_meta['html_code'][0];
		} 
	?>
	<textarea name="html_code" id="html_code" rows="5" style="width: 100%;"><?php echo $codigo; ?></textarea>
</p>
<?php
}

/* Saves the custom meta input */
function prfx_meta_save_html_code( $post_id ) {
	global $post;
	global $wp_query;
    // Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'prfx_nonce' ] ) && wp_verify_nonce( $_POST[ 'prfx_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
	
    // Exits script depending on save status
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
    }
	
    // Checks for input and sanitizes/saves if needed
    if( isset( $_POST['html_code'] )) {
		$ativ = "";
		update_post_meta( $post_id, 'html_code', $_POST['html_code'] );
    }
	
}
add_action( 'save_post', 'prfx_meta_save_html_code' );












// Término do código da meta box de Link



function insereBannerFront($atts) {
	$parametros = shortcode_atts( array(
	'categoria' => 'none',
	'id_banner' => 'none',
	'cliente' => 'none'
    ),$atts);
	
	return retornaCodigoBanner($atts);
	//return 	var_dump($atts);	
}

add_shortcode( 'ferdzbanner', 'insereBannerFront' );


function wp_custom_attachment() {
	
	
?>

<!-- Teste de campo de galeria de imagens -->

<?php wp_enqueue_media(); ?>

<?php
    wp_nonce_field(plugin_basename(__FILE__), 'wp_custom_attachment_nonce');
	
	global $post;
	
	// Get WordPress' media upload URL
	$upload_link = esc_url( get_upload_iframe_src( 'image', $post->ID ) );
	
	// See if there's a media id already saved as post meta
	$your_img_id = get_post_meta( $post->ID, '_your_img_id', true );
	
	// Get the image src
	$your_img_src = wp_get_attachment_image_src( $your_img_id, 'full' );
	
	// For convenience, see if the array is valid
	$you_have_img = is_array( $your_img_src );
?>

<!-- Your image container, which can be manipulated with js -->
<div class="custom-img-container">
    <?php if ( get_post_meta(get_the_ID(), 'wp_custom_attachment', true) !== "" ) : 
	$a = get_post_meta(get_the_ID(), 'wp_custom_attachment', true);?>
	<?php if(is_array($a))  { ?>
		<img src="<?php echo $a['url']; ?>" alt="" style="max-width:100%;" />
		<?php } else { ?>
		<img src="<?php echo $a; ?>" alt="" style="max-width:100%;" />
	<?php } ?>
    <?php endif; ?>
</div>

<!-- Your add & remove image links -->
<p class="hide-if-no-js">
    <a class="upload-custom-img <?php if ( $you_have_img  ) { echo 'hidden'; } ?>" 
	href="<?php echo $upload_link ?>">
        <?php _e('Escolher imagem da galeria') ?>
    </a>
	<!--  <a class="delete-custom-img <?php if ( ! $you_have_img  ) { echo 'hidden'; } ?>" 
		href="#">
        <?php _e('Remover imagem') ?>
    </a>-->
</p>

<!-- A hidden input to set and post the chosen image id -->
<?php if(is_array($a))  { ?>
	<input class="custom-img-id" name="wp_custom_attachment" type="hidden" style="visibility:hidden;" value="<?php echo $a['url']; ?>" />
	<?php } else { ?>
	<input class="custom-img-id" name="wp_custom_attachment" type="hidden" style="visibility:hidden;" value="<?php echo $a; ?>" />
	
<?php } ?>

<script>
	jQuery(function($){
		
		// Set all variables to be used in scope
		var frame,
		metaBox = $('#meta-box-id.postbox'), // Your meta box id here
		addImgLink = jQuery('.upload-custom-img'),
		delImgLink = jQuery( '.delete-custom-img'),
		imgContainer = jQuery( '.custom-img-container'),
		imgIdInput = jQuery( '.custom-img-id' );
		
		// ADD IMAGE LINK
		addImgLink.on( 'click', function( event ){
			
			event.preventDefault();
			
			// If the media frame already exists, reopen it.
			if ( frame ) {
				frame.open();
				return;
			}
			
			// Create a new media frame
			frame = wp.media({
				title: 'Escolha ou faça o upload de um banner',
				button: {
					text: 'Usar este banner'
				},
				multiple: false  // Set to true to allow multiple files to be selected
			});
			
			
			// When an image is selected in the media frame...
			frame.on( 'select', function() {
				
				// Get media attachment details from the frame state
				var attachment = frame.state().get('selection').first().toJSON();
				
				// Send the attachment URL to our custom image input field.
				imgContainer.html( '<img src="'+attachment.url+'" alt="" style="max-width:100%;"/>' );
				
				// Send the attachment id to our hidden input
				imgIdInput.val( attachment.url );
				
				// Hide the add image link
				//addImgLink.addClass( 'hidden' );
				
				// Unhide the remove image link
				//delImgLink.removeClass( 'hidden' );
			});
			
			// Finally, open the modal on click
			frame.open();
		});
		
		
		// DELETE IMAGE LINK
		delImgLink.on( 'click', function( event ){
			
			event.preventDefault();
			
			// Clear out the preview image
			imgContainer.html( '' );
			
			// Un-hide the add image link
			addImgLink.removeClass( 'hidden' );
			
			// Hide the delete image link
			delImgLink.addClass( 'hidden' );
			
			// Delete the image id from the hidden input
			imgIdInput.val( '' );
			
		});
		
	});
</script>


<!-- X Teste de campo de galeria de imagens -->



<?php
	
    $html = '<style>#normal-sortables{min-height:0 !important;}</style><p class="description">';
	$html .= '';
    $html .= '</p>';
    // $html .= '<input type="file" id="wp_custom_attachment" name="wp_custom_attachment" size="25" />';
	// if(get_post_meta(get_the_ID(), 'wp_custom_attachment', true) !== "") {
	// $a = get_post_meta(get_the_ID(), 'wp_custom_attachment', true);
    // $html .= '<br/><br/><p>ID do banner: '.get_the_ID().'</p><p>Visualização:</p><img class="img-preview" style="max-width:600px;" src="'.$a['url'].'"/>';
	// }
	//get_post_meta(get_the_ID(), 'wp_custom_attachment', true)['url']
    echo $html;
	
} // end wp_custom_attachment


function salvar_conteudo_customiz($id) {
	global $wp_query;
	global $post;

    /* --- security verification --- */
	if(isset($_POST['wp_custom_attachment_nonce'])) {
    if(wp_verify_nonce($_POST['wp_custom_attachment_nonce'], plugin_basename(__FILE__))) {
		return $id;
    } // end if
	}
	
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $id;
    } // end if
	
	
	if(isset($_POST['post_type'])) {
    if('page' == $_POST['post_type']) {
		if(!current_user_can('edit_page', $id)) {
			return $id;
		} // end if
    } else {
        if(!current_user_can('edit_page', $id)) {
            return $id;
        } // end if
    } // end if
	}
	
    /* - end security verification - */
	
    // Make sure the file array isn't empty
    // if(!empty($_FILES['wp_custom_attachment']['name'])) {
	
	// // Setup the array of supported file types. In this case, it's just PDF.
	// $supported_types = array('application/pdf', 'image/jpg', 'image/jpeg', 'image/png', 'image/gif');
	
	// // Get the file type of the upload
	// $arr_file_type = wp_check_filetype(basename($_FILES['wp_custom_attachment']['name']));
	// $uploaded_type = $arr_file_type['type'];
	
	// // Check if the type is supported. If not, throw an error.
	// if(in_array($uploaded_type, $supported_types)) {
	
	// // Use the WordPress API to upload the file
	// $upload = wp_upload_bits($_FILES['wp_custom_attachment']['name'], null, file_get_contents($_FILES['wp_custom_attachment']['tmp_name']));
	
	// if(isset($upload['error']) && $upload['error'] != 0) {
	// wp_die('There was an error uploading your file. The error is: ' . $upload['error']);
	// } else {
	
	if(isset($_POST['wp_custom_attachment'])) {
	add_post_meta($id, 'wp_custom_attachment', $_POST['wp_custom_attachment']);
	update_post_meta($id, 'wp_custom_attachment', $_POST['wp_custom_attachment']); 
	}
	
	
	
	
	
	//} // end if/else
	
	// } else {
	// wp_die("Imagem no formato inválido, por favor, selecione um dos formatos: JPG, JPEG, PNG, GIF");
	// } // end if/else
	
	//  } // end if
	
} // end salvar_conteudo_customiz
add_action('save_post', 'salvar_conteudo_customiz');

function update_edit_form() {
    echo ' enctype="multipart/form-data"';
} // end update_edit_form
add_action('post_edit_form_tag', 'update_edit_form');

// Término do novo campo de upload

//------------------------------------------------------

function retornaCodigoBanner($attsArr) {
	global $post;
	$temp_post = $post;
	$parametros = array('categoria' => 'category_name', 'id_banner' => 'page_id');
	if($attsArr !== '') {
		foreach($attsArr as $key => $val)
		{
			foreach($parametros as $parKey => $parVal) {
				if ($key == $parKey) {
					$attsArr[$parametros[$key]] = $attsArr[$key];
					unset($attsArr[$key]);
				} 			
			}
		}
		$params = array_merge(array( 'post_type' => 'banners', 'orderby' => 'rand', 'posts_per_page' => 1, 'meta_key' => 'ativo', 'meta_value' => 'Ativo'), $attsArr);		
	} else {
		$params = array( 'post_type' => 'banners', 'orderby' => 'rand', 'posts_per_page' => 1, 'meta_key' => 'ativo', 'meta_value' => 'Ativo');		
	}	
	
	 
	$dadosPost = new WP_Query( $params );
	while ( $dadosPost->have_posts() ) : $dadosPost->the_post();
	$urlban = get_post_meta(get_the_id(), 'url-banner', true);
	$urlCru = get_post_meta(get_the_id(), 'url-banner', true);
	$idbann = get_the_id();
	
	
	$cntidcli = "";
	for($i=0;$i<count(get_the_terms($post->ID, "clientes"));$i++) {
		if($i == 0) {
			$cntidcli0 = get_the_terms($post->ID, "clientes");
			$cntidcli.= $cntidcli0[$i]->slug;
		} else {
			$cntidcli0 = get_the_terms($post->ID, "clientes");
			$cntidcli.= ",".$cntidcli0[$i]->slug;
		}
	}
	//echo $cntidcli;
	
	
	$idclie = $cntidcli;
	
	
	$cntnomecli = "";
	for($i=0;$i<count(get_the_terms($post->ID, "clientes"));$i++) {
		if($i == 0) {
			$cntnomecli0 = get_the_terms($post->ID, "clientes");
			$cntnomecli.= $cntnomecli0[$i]->name;
		} else {
			$cntnomecli0 = get_the_terms($post->ID, "clientes");
			$cntnomecli.= ",".$cntnomecli0[$i]->name;
		}
	}
	
	$cntposicao = "";
	for($i=0;$i<count(get_the_terms($post->ID, "posicoes"));$i++) {
		if($i == 0) {
			$cntposicao0 = get_the_terms($post->ID, "posicoes");
			$cntposicao.= $cntposicao0[$i]->name;
		} else {
			$cntposicao0 = get_the_terms($post->ID, "posicoes");
			$cntposicao.= ",".$cntposicao0[$i]->name;
		}
	}
	
	$totalposicoes = $cntposicao;
	
	//echo $cntnomecli;
	
	
	$nmcli =  $cntnomecli;
	
	//inserindo dados de impressão de banner
	
	// X inserindo dados de impressão de banner
	$target = "";
	if(get_post_meta(get_the_id(), 'target', true) == "") {
		$target="_blank";	
	} else {
		$target = get_post_meta(get_the_id(), 'target', true);
	}
	$urlban = plugin_dir_url( __FILE__ ) ."salvar_clique.php?"."ban=".$idbann."&cli=".$idclie."&nm=".$nmcli."&url=" . urlencode($urlban);
	$urlbanClique = plugin_dir_url( __FILE__ ) ."salvar_clique.php?"."ban=".$idbann."&cli=".$idclie."&nm=".$nmcli;
	
	
	$bannerCode0 = get_post_meta(get_the_id(), 'wp_custom_attachment', true);
	if(is_array($bannerCode0)) {
		$bannerCode0 = $bannerCode0['url'];
	}
	$bannerPosicao = $totalposicoes;
	$BannerCliente = $nmcli;
	$tamanho = get_post_meta(get_the_id(), 'tamanho', true);
	$tamanho = explode("x", $tamanho);
	if($tamanho[0] != "") {
		$largura = $tamanho[0]."px";
		$altura = $tamanho[1]."px";
	}
	else {
		$largura = "auto";
		$altura = "auto";
	}
	
	global $wpdb;	
	$rowcount = $wpdb->get_var('SELECT COUNT(*) FROM wp_ferdzbanner_impressaocount WHERE id_banner = ' .get_the_id());
	
	$exibe_inicio = $wpdb->get_results('SELECT meta_value FROM '.$wpdb->prefix.'postmeta WHERE meta_key = "exibicao_inicio" AND post_id = ' .get_the_id());
	foreach ( $exibe_inicio as $page ) {
		$inicio_exibicao = $page->meta_value;
	}
	$exibe_fim = $wpdb->get_results('SELECT meta_value FROM '.$wpdb->prefix.'postmeta WHERE meta_key = "exibicao_fim" AND post_id = ' .get_the_id());
	foreach ( $exibe_fim as $page ) {
		$fim_exibicao = $page->meta_value;
	}
	$dataAtual = date('Y-m-d');
	if ($inicio_exibicao == "") {
		$inicio_exibicao = "01/01/1111";
	}
	if ($fim_exibicao == "") {
		$fim_exibicao = "31/12/9999";
	}
	
	/*if ($nofollow == "on" && $nofollow != "") {
		$follow = 'rel="nofollow"';
	}*/
	
	$inicio_exibicao = explode("/", $inicio_exibicao);
	$ini_exib = $inicio_exibicao[2]."-".$inicio_exibicao[1]."-".$inicio_exibicao[0];
	
	$fim_exibicao = explode("/", $fim_exibicao);
	$fim_exib = $fim_exibicao[2]."-".$fim_exibicao[1]."-".$fim_exibicao[0];
	if(!isset($bannerCode)) {
		$bannerCode = "";
	}
	$bannerCode .= "<div style='display:none' class='nome-cliente'>".get_the_title()."</div>";
	if(strtotime($dataAtual) >= strtotime($ini_exib) && strtotime($dataAtual) <= strtotime($fim_exib)) {
		$urlbanVisu = plugin_dir_url( __FILE__ ) ."salvar_visu.php?"."ban=".$idbann."&cli=".$idclie."&nm=".$nmcli."&url=" . urlencode($urlban);
		
		if(get_post_meta(get_the_id(), 'tipo_banner', true) == "zip") {
			$linkIframe = get_post_meta(get_the_id(), 'url_zip_file', true);
			$bannerCode .= "
			
			<div class='vp-banner-ad banner-zip' style='display:inline-block;'>
				<a onClick=".'"'."ga("."'send', 'event', 'clique ".get_the_title()." - ".$attsArr['posicoes']." - ".$BannerCliente."', 'clique ".get_the_title()." - ".$attsArr['posicoes']." - ".$BannerCliente."')".'"'." target='". $target ."' href='".  $urlban  ."'></a>
				<iframe id='iframe_banner_".get_the_id()."' src='/wp-content/plugins/ferdzbanner/".$linkIframe."' frameborder='0' scrolling='no'></iframe>
			</div>
			<script>
					jQuery('.vp-banner-ad iframe#iframe_banner_".get_the_id()."').load(function(){
						jQuery(this).attr('width', jQuery('.vp-banner-ad iframe#iframe_banner_".get_the_id()."').contents().width());
						if(jQuery('.vp-banner-ad iframe#iframe_banner_".get_the_id()."').contents().width() == 728) {
							jQuery(this).attr('height', 90);
						}
						else {
							jQuery(this).attr('height', jQuery('.vp-banner-ad iframe#iframe_banner_".get_the_id()."').contents().height());
						}
					});
					jQuery('#iframe_banner_".get_the_id()."').load(function(){
						var iframe_zip_".get_the_id()." = jQuery('#iframe_banner_".get_the_id()."').contents();
						iframe_zip_".get_the_id().".find('body').click(function(){
							 alert('clicou zip');
						});
					});
			</script>
			";
			$bannerCode .= "
				<style>
					.td-a-rec {display:block !important}
					.vp-banner-ad.banner-zip, .vp-banner-ad.banner-html {position:relative;width: 100%;}
					.vp-banner-ad.banner-zip a {position:absolute;width:100%;height:100%;}
					.vp-banner-ad.banner-zip iframe {width: 100% !important;min-width: auto !important;}
				</style>
			";
		} 
		else if(get_post_meta(get_the_id(), 'tipo_banner', true) == "html") {
			$bannerCode .= "
			<div class='vp-banner-ad banner-html' style='display:inline-block;' id='iframe_html_".get_the_id()."'>".get_post_meta(get_the_id(), 'html_code', true)."</div>";
			
			
			$bannerCode .= "
			<script>
				jQuery(document).ready(function() {
					var iframe_html_".get_the_id()." = 0;
					jQuery(document).on('visibilitychange', function() {
						if(document.hidden && iframe_html_".get_the_id()." == 1) {
							jQuery.ajax({
								url: '$urlbanClique',
								dataType: 'jsonp',
								type: 'POST',
								success: function(data){},
								error: function(data){}
							});
						}
					});
					jQuery('#iframe_html_".get_the_id()."').mouseover(function() {
						iframe_html_".get_the_id()." = 1;
					});
					jQuery('.vp-banner-ad:not(.banner-html)').mouseover(function() {
						iframe_html_".get_the_id()." = 0;
					});
					jQuery('#iframe_html_".get_the_id()."').mouseleave(function() {
						setTimeout(function() {
							iframe_html_".get_the_id()." = 0;
						}, 100);
					});
				});
			</script>";
			
		} 
		else {
			if(get_post_meta(get_the_id(), 'tipo_exib', true) == "Expansivo") {

				$urlBanExpansivo = get_post_meta(get_the_id(), 'wp_custom_attachment_expansivo', true);
				$bannerCode .= "
				<div class='vp-banner-ad vp-ad-expansivo' style='display:inline-block;'>
					<a onClick=".'"'."ga("."'send', 'event', 'clique ".get_the_title()." - ".$attsArr['posicoes']." - ".$BannerCliente."', 'clique ".get_the_title()." - ".$attsArr['posicoes']." - ".$BannerCliente."')".'"'." target='". $target ."' href='".  $urlban  ."' ".$follow.">
						<img data-expansivo='".$urlBanExpansivo."' class='vp-banner-ad-img  vp-banner-ad-expansivo' src='".$bannerCode0."'/>
					</a>
				</div>";

			} else if(get_post_meta(get_the_id(), 'tipo_exib', true) == "Normal") {

				$bannerCode .= "<div class='vp-banner-ad' style='display:inline-block;'><a onClick=".'"'."ga("."'send', 'event', 'clique ".get_the_title()." - ".$attsArr['posicoes']." - ".$BannerCliente."', 'clique ".get_the_title()." - ".$attsArr['posicoes']." - ".$BannerCliente."')".'"'." target='". $target ."' href='".  $urlban  ."'><img data-alt='' class='vp-banner-ad-img' src='".$bannerCode0."'/></a></div>";

			} else {
				$bannerCode .= "<div class='vp-banner-ad' style='display:inline-block;'><a onClick=".'"'."ga("."'send', 'event', 'clique ".get_the_title()." - ".$attsArr['posicoes']." - ".$BannerCliente."', 'clique ".get_the_title()." - ".$attsArr['posicoes']." - ".$BannerCliente."')".'"'." target='". $target ."' href='".  $urlban  ."'><img data-alt='' class='vp-banner-ad-img' src='".$bannerCode0."'/></a></div>";
			}
		}
	}
	
	$jqueryCountView = "
	<style> 
		.vp-ad-expansivo {
			text-align:center;
			display:inline-block;
			z-index: 999;
			position:relative;
			overflow-y:visible;
			height:90px;
		}
		.vp-ad-expansivo a {
			left: 0px;
			right: 0px;
			margin:auto;
			max-height: 90px;
			overflow-y:hidden;
		}
		.vp-ad-expansivo a:hover {
			max-height: 1000px;
		}
		@media only screen and (max-width: 767px) {
			.vp-ad-expansivo a, .vp-ad-expansivo {
				height:auto !important;
			}
		}
		.vp-banner-ad {transition:ease 0.4s;}  
		.vp-banner-ad a{transition:ease 0.4s;display:inline-block;} 
	</style>
	<script>
	jQuery(document).ready(function() {
	
	jQuery.get('$urlbanVisu', function(data) {console.log(data)})
	
	});";
	
	if(get_post_meta(get_the_id(), 'tipo_exib', true) == "Expansivo") {
		$jqueryCountView.= "
		if(window.innerWidth > 767) {
			jQuery('.vp-banner-ad-expansivo').mouseover(function() {
				tamanhoInicial = jQuery(this)[0].offsetHeight;
				widthInicial = jQuery(this)[0].offsetWidth;
				jQuery(this).parent().parent().css('width', widthInicial);
				jQuery(this).parent().css('width', widthInicial);
				urlInicial = jQuery(this).attr('src');
				urlExpansivo = jQuery(this).attr('data-expansivo');
				jQuery(this).attr('data-expansivo', urlInicial);
				jQuery(this).attr('src', urlExpansivo);
				jQuery(this).attr('data-alt', tamanhoInicial);
				jQuery(this).parent().css('position', 'absolute');
			});

			jQuery('.vp-banner-ad-expansivo').mouseleave(function() {
				var obj = jQuery(this);
				urlInicial = obj.attr('src');
				urlExpansivo = obj.attr('data-expansivo');
				obj.attr('data-expansivo', urlInicial);
				obj.attr('src', urlExpansivo);
			});
		}
		";
	}
	
	$jqueryCountView.= "</script>";
	$bannerCode.= $jqueryCountView;
	
	$post = $temp_post;
	return $bannerCode;
	//return var_dump($attsArr);
	endwhile;
}

//--------------------------------------------
/*
function ferdzbanner_slide_shortcode() {
	$dados['script'] = "";
	$dados['html'] = "";
	global $wpdb;
	$idPosicao = $wpdb->get_var("SELECT tt.term_taxonomy_id FROM ".$wpdb->prefix."terms t INNER JOIN ".$wpdb->prefix."term_taxonomy tt ON t.term_id = tt.term_id WHERE t.name = 'Galerias' AND tt.taxonomy = 'posicoes'");

	$idAnuncio = $wpdb->get_var("SELECT p.* FROM wp_term_relationships tr INNER JOIN wp_posts p ON p.ID = tr.object_id INNER JOIN wp_postmeta pm ON pm.post_id = tr.object_id WHERE tr.term_taxonomy_id = $idPosicao AND (pm.meta_key = 'ativo' AND pm.meta_value = 'Ativo') ORDER BY rand() LIMIT 1;");
	
	if($idAnuncio != "") {
		$cntidcli = "";
		for($i=0;$i<count(get_the_terms($idAnuncio, "clientes"));$i++) {
			if($i == 0) {
				$cntidcli0 = get_the_terms($idAnuncio, "clientes");
				$cntidcli.= $cntidcli0[$i]->slug;
			} else {
				$cntidcli0 = get_the_terms($idAnuncio, "clientes");
				$cntidcli.= ",".$cntidcli0[$i]->slug;
			}
		}
		$idclie = $cntidcli;

		$cntnomecli = "";
		for($i=0;$i<count(get_the_terms($post->ID, "clientes"));$i++) {
			if($i == 0) {
				$cntnomecli0 = get_the_terms($post->ID, "clientes");
				$cntnomecli.= $cntnomecli0[$i]->name;
			} else {
				$cntnomecli0 = get_the_terms($post->ID, "clientes");
				$cntnomecli.= ",".$cntnomecli0[$i]->name;
			}
		}
		$nmcli =  $cntnomecli;

		$linkAnuncio = get_post_meta($idAnuncio, 'url-banner', true);
		$imagemAnuncio = get_post_meta($idAnuncio, 'wp_custom_attachment', true);
		$target = get_post_meta($idAnuncio, 'target', true);
		$nomeBanner = get_the_title($idAnuncio);

		$urlbanVisu = plugin_dir_url( __FILE__ ) ."salvar_visu.php?ban=".$idAnuncio."&cli=".$idclie."&nm=".$nmcli."&url=" . urlencode($linkAnuncio);

		$urlban = plugin_dir_url( __FILE__ ) ."salvar_clique.php?"."ban=".$idbann."&cli=".$idclie."&nm=".$nmcli."&url=" . urlencode($linkAnuncio);
		
		$dados['html'] = "<div class='anuncio' data-visu='".$urlbanVisu."'><a onClick='ga(\'send\', \'event\', \'clique '.$nomeBanner.' - Galeria - '.$nmcli.'\', \'clique '.$nomeBanner.' - Galeria - '.$nmcli.'\')' target='". $target ."' href='".  $urlban  ."' ".$follow."><img src='".$imagemAnuncio."'/></a></div>";
		
		return $dados;
	}
}

function ferdzbanner_slides() { ?>
	<script>
		jQuery(window).load(function (){
			if(jQuery(".post_td_gallery").length) {
				jQuery(".post_td_gallery").each(function() {
					<?php
						$ad_slide = ferdzbanner_slide_shortcode();
						$htmlSlide = $ad_slide['html'];	   	   
					?>
					var idSlide = Math.floor((Math.random() * 9999) + 1);
					jQuery(this).attr('data-slide', idSlide);
					jQuery(this).attr('data-clique', 0);
					jQuery(this).prepend("<div class='ferdzbanner_anuncio anuncio_"+idSlide+"'><span class='close_ad'>Fechar</span></div>");
					jQuery(this).find(".anuncio_"+idSlide).append("<?php echo $htmlSlide ?>");
				});
				jQuery(".post_td_gallery .td-gallery-slide-prev-next-but i, .post_td_gallery .td-slider .td-button").click(function() {
					var clique = parseInt(jQuery(this).closest(".post_td_gallery").attr("data-clique")) + 1;
					jQuery(this).closest(".post_td_gallery").attr("data-clique", clique);
					if(clique == 5) {
						var urlBannerVisu = jQuery(this).closest(".post_td_gallery").find(".anuncio").data('visu');
						console.log(urlBannerVisu);
						jQuery.get(urlBannerVisu, function(data) {});
					   	jQuery(this).closest(".post_td_gallery").find(".ferdzbanner_anuncio").addClass("ativo");
						jQuery(this).closest(".post_td_gallery").attr("data-clique", 0);
					}
				});
				jQuery('body').on('click', '.ferdzbanner_anuncio .close_ad', function(e) {
					e.preventDefault(0);
					jQuery(this).parent().removeClass("ativo");
				});
				jQuery(".td-slide-galery-figure img").click(function() {
					var anuncio = jQuery(this).closest(".post_td_gallery").find(".ferdzbanner_anuncio").html();
					var target = document.querySelector("body");
					var observer = new MutationObserver( handleMutationObserver );
					var config = { childList: true, attributes: true };

					function handleMutationObserver( mutations ) {
						mutations.forEach(function(mutation) {
							setTimeout(function() {
								if(jQuery(".mfp-wrap.mfp-gallery").length) {
									if(!jQuery("body > .ferdzbanner_anuncio").length) {
										jQuery("body").prepend("<div class='ferdzbanner_anuncio'>"+anuncio+"</div>");
										var cliqueFull = 0;
										jQuery(".mfp-container .mfp-arrow").click(function() {
											cliqueFull++;
											if(cliqueFull == 5) {
												var urlBannerVisu = jQuery("body > .ferdzbanner_anuncio").find(".anuncio").data('visu');
												jQuery.get(urlBannerVisu, function(data) {});
												jQuery("body > .ferdzbanner_anuncio").addClass("ativo");
											}
										});
									}
								}
								else {
									jQuery("body > .ferdzbanner_anuncio").remove();
								}
							}, 1000);
						});
					}
					observer.observe( target, config );
				});
			}
		});
	</script>
	<style>
		.post_td_gallery {position: relative;}
		.ferdzbanner_anuncio {width: 100%;height: 100%;position: absolute;z-index: 2;background-color: rgba(127,127,127,.9);top: -100%;visibility: hidden;opacity: 0;-webkit-transition: all 0.5s ease;-moz-transition: all 0.5s ease;-o-transition: all 0.5s ease;transition: all 0.5s ease;}
		.ferdzbanner_anuncio.ativo {top: 0;visibility: visible;opacity: 1;}
		.ferdzbanner_anuncio .close_ad {position: absolute;border: 2px solid white;padding: 5px 20px;right: 15px;top: 15px;text-transform: uppercase;font-size: 13px;font-weight: 700;cursor: pointer;color: white;}
		.post_td_gallery .ferdzbanner_anuncio .close_ad {top: 40px;}
		.ferdzbanner_anuncio .anuncio {position: absolute;left: 50%;top: 50%;transform: translate(-50%, -50%);}
		.ferdzbanner_anuncio .anuncio img {margin-bottom: 0px}
		.mfp-container .ferdzbanner_anuncio {z-index: 99999;}
		body > .ferdzbanner_anuncio {position: fixed;cursor: default;z-index: 99999}
	</style>
<?php }
add_action( 'wp_footer', 'ferdzbanner_slides', 5 );*/




//global $wp_query;
//global $post;
//post_type_archive_title( "banners", "Adicionar novo banner" );

// Custom taxonomies



add_action( 'init', 'create_cliente_taxonomy' );

function create_cliente_taxonomy() {
	$labels = array(
	'name'                           => 'Clientes',
	'singular_name'                  => 'Cliente',
	'search_items'                   => 'Pesquisar Cliente',
	'all_items'                      => 'Todos os Clientes',
	'edit_item'                      => 'Editar Cliente',
	'update_item'                    => 'Atualizar Cliente',
	'add_new_item'                   => 'Adicionar Novo Cliente',
	'new_item_name'                  => 'Novo Nome de Cliente',
	'menu_name'                      => 'Clientes',
	'view_item'                      => 'Ver Clientes',
	'popular_items'                  => 'Clientes mais Usados',
	'separate_items_with_commas'     => 'Separe os Clientes por vírgulas',
	'add_or_remove_items'            => 'Adicionar ou remover Clientes',
	'choose_from_most_used'          => 'Escolher dos Clientes mais usados',
	'not_found'                      => 'Não foi encontrado nenhum cliente'
	);
	
	register_taxonomy(
	'clientes',
	'banners',
	array(
	'label' => __( 'Cliente' ),
	'hierarchical' => true,
	'labels' => $labels
	)
	);
}


// Adicionar coluna para mostrar o(s) clientes do banner
if(esc_attr(get_option('cliente')) == "true" || esc_attr(get_option('cliente')) == "") {
	add_filter('manage_edit-banners_columns', 'my_columns');
	function my_columns($columns) {
		//	var_dump($columns);
		$columns['clientes'] = 'Cliente';
		return $columns;
	}
	
	add_action('manage_posts_custom_column',  'my_show_columns');
	function my_show_columns($name) {
		global $post;
		switch ($name) {
			case 'clientes':
			$cliente = get_the_terms($post->ID, "clientes");
			$contclientes = "";
			for($i=0;$i<count($cliente);$i++) {
				if($i == 0) {
					$contclientes.= $cliente[$i]->name;
				} else {
					$contclientes.= ", ".$cliente[$i]->name;
				}
			}
			echo $contclientes;
		}
	}
}


// Criando taxonomia de posições do banner

add_action( 'init', 'create_posicao_taxonomy' );

function create_posicao_taxonomy() {
	$labels = array(
	'name'                           => 'Posições',
	'singular_name'                  => 'Posição',
	'search_items'                   => 'Pesquisar Posoções',
	'all_items'                      => 'Todas as Posições',
	'edit_item'                      => 'Editar Posição',
	'update_item'                    => 'Atualizar Posição',
	'add_new_item'                   => 'Adicionar Nova Posição',
	'new_item_name'                  => 'Novo Nome de Posição',
	'menu_name'                      => 'Posições',
	'view_item'                      => 'Ver Posições',
	'popular_items'                  => 'Posições mais Usadas',
	'separate_items_with_commas'     => 'Separe as Posições por vírgulas',
	'add_or_remove_items'            => 'Adicionar ou remover Posições',
	'choose_from_most_used'          => 'Escolher das Posições mais usados',
	'not_found'                      => 'Não foi encontrado nenhuma posição'
	);
	
	register_taxonomy(
	'posicoes',
	'banners',
	array(
	'label' => __( 'Posição' ),
	'hierarchical' => true,
	'labels' => $labels,
	// 'capabilities' => array(
	// 'manage_terms' => 'a_capability_the_user_doesnt_have',
	// 'edit_terms'   => 'a_capability_the_user_doesnt_have',
	// 'delete_terms' => 'a_capability_the_user_doesnt_have',
	// 'assign_terms' => 'edit_posts'
	// ),
	'show_ui' => true
	)
	);
}

// Adicionar coluna para mostrar a posição do banner
if(esc_attr(get_option('posicao')) == "true" || esc_attr(get_option('cliente')) == "") {
	add_filter('manage_edit-banners_columns', 'my_columns_posicao');
	function my_columns_posicao($columns) {
		//	var_dump($columns);
		$columns['posicoes'] = 'Posição';
		return $columns;
	}
	
	add_action('manage_posts_custom_column',  'my_show_columns_posicao');
	function my_show_columns_posicao($name) {
		global $post;
		switch ($name) {
			case 'posicoes':
			$posicoes = get_the_terms($post->ID, "posicoes");
			$contposicoes = "";
			for($i=0;$i<count($posicoes);$i++) {
				if($i == 0) {
					$contposicoes.= $posicoes[$i]->name;
				} else {
					$contposicoes.= ", ".$posicoes[$i]->name;
				}
			}
			echo $contposicoes;
		}
	}
}

// Adicionar coluna para mostrar a quantidade de cliques
if(esc_attr(get_option('cliques')) == "true" || esc_attr(get_option('cliente')) == "") {
	add_filter('manage_edit-banners_columns', 'my_columns_cliques');
	function my_columns_cliques($columns) {
		//	var_dump($columns);
		$columns['cliques'] = 'Cliques';
		return $columns;
	}
	
	add_action('manage_posts_custom_column',  'my_show_columns_cliques');
	function my_show_columns_cliques($name) {
		global $post;
		switch ($name) {
			case 'cliques':
			global $wpdb;		
			$resCliques = $wpdb->get_results( 
			"
			SELECT * FROM wp_ferdzbanner_viewcount WHERE id_banner = ".$post->ID."
			"
			);
			$cliques = count($resCliques);
			echo $cliques;
		}
	}
}


// Adicionar coluna para mostrar a quantidade de impressões

if(esc_attr(get_option('visualizacoes')) == "true" || esc_attr(get_option('cliente')) == "") {
add_filter('manage_edit-banners_columns', 'my_columns_impressoes');
function my_columns_impressoes($columns) {
	//	var_dump($columns);
    $columns['impressoes'] = 'Visualizacoes';
    return $columns;
}

add_action('manage_posts_custom_column',  'my_show_columns_impressoes');
function my_show_columns_impressoes($name) {
    global $post;
    switch ($name) {
        case 'impressoes':
		global $wpdb;	
		$wpdb->flush();		

		$resCliques2 = $wpdb->get_results( 
		"
		SELECT COUNT(*) as cnt FROM wp_ferdzbanner_impressaocount WHERE id_banner = ".$post->ID."
		"
		);
		//$cliques2 = count($resCliques2);
		echo $resCliques2[0]->cnt;
    }
}
	
}



// Adicionar coluna para mostrar se está ativo ou inativo

if(esc_attr(get_option('ativo_desativo')) == "true") {
	add_filter('manage_edit-banners_columns', 'my_columns_ativo');
	function my_columns_ativo($columns) {
		//	var_dump($columns);
		$columns['ativo'] = 'Ativo';
		return $columns;
	}
	
	add_action('manage_posts_custom_column',  'my_show_columns_ativo');
	function my_show_columns_ativo($name) {
		global $post;
		switch ($name) {
			case 'ativo':
			//global $wpdb;		
			
			//echo get_post_meta($post->ID, 'ativo', true);
			if(get_post_meta($post->ID, 'ativo', true) == "Ativo") {
				echo "<img src='".plugin_dir_url( __FILE__ ) . "images/ativo.png' style='width:25px;'/>";
			} else {
				echo "<img src='".plugin_dir_url( __FILE__ ) . "images/inativo.png' style='width:25px;'/>";
			}
			
			
			/*echo '          <div class="switch">
				<input id="cmn-toggle-1" class="cmn-toggle cmn-toggle-round" type="checkbox">
				<label for="cmn-toggle-1"></label>
			</div>';*/
		}
	}
	
}


// Adicionar coluna para pré-visualização de banner
if(esc_attr(get_option('pre_visualizacao')) == "true") {
	
	
	add_filter('manage_edit-banners_columns', 'my_columns_thumb');
	function my_columns_thumb($columns) {
		//	var_dump($columns);
		$columns['thumb'] = 'Pré-visualização';
		return $columns;
	}
	
	add_action('manage_posts_custom_column',  'my_show_columns_thumb');
	function my_show_columns_thumb($name) {
		global $post;
		switch ($name) {
			case 'thumb':
			$methodHolder = get_post_meta(get_the_id(), 'wp_custom_attachment', true);
			if($methodHolder == ""){
				$imgurl = "";
			} else {
				if(is_array($methodHolder)) {
					$imgurl = $methodHolder['url'];
				} else {
					$imgurl = $methodHolder;				
				}
				echo "<img style='max-width:110px;max-height:150px;' src='".$imgurl."'/><br/><a href='".$imgurl."' target='_blank' style='margin-left:16px;'>Ver imagem</a>";
				
			}
		}
	}
	
}


// Adicionar um novo método de filtro ao admin dos banners-settings para filtrar por clientes
/**
 * Display a custom taxonomy dropdown in admin
 * @author Mike Hemberger
 * @link http://thestizmedia.com/custom-post-type-filter-admin-custom-taxonomy/
 */
add_action('restrict_manage_posts', 'tsm_filter_post_type_by_taxonomy_clientes');
function tsm_filter_post_type_by_taxonomy_clientes() {
	global $typenow;
	$post_type = 'banners'; // change to your post type
	$taxonomy  = 'clientes'; // change to your taxonomy
	if ($typenow == $post_type) {
		$selected      = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
		$info_taxonomy = get_taxonomy($taxonomy);
		wp_dropdown_categories(array(
		'show_option_all' => __("Mostrar todos {$info_taxonomy->label}"),
		'taxonomy'        => $taxonomy,
		'name'            => $taxonomy,
		'orderby'         => 'name',
		'selected'        => $selected,
		'show_count'      => true,
		'hide_empty'      => true,
		));
	};
}
/**
 * Filter posts by taxonomy in admin
 * @author  Mike Hemberger
 * @link http://thestizmedia.com/custom-post-type-filter-admin-custom-taxonomy/
 */
add_filter('parse_query', 'tsm_convert_id_to_term_in_query_clientes');
function tsm_convert_id_to_term_in_query_clientes($query) {
	global $pagenow;
	$post_type = 'banners'; // change to your post type
	$taxonomy  = 'clientes'; // change to your taxonomy
	$q_vars    = &$query->query_vars;
	if ( $pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0 ) {
		$term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
		$q_vars[$taxonomy] = $term->slug;
	}
}


// Adicionar um novo método de filtro ao admin dos banners-settings para filtrar por posições
/**
 * Display a custom taxonomy dropdown in admin
 * @author Mike Hemberger
 * @link http://thestizmedia.com/custom-post-type-filter-admin-custom-taxonomy/
 */
add_action('restrict_manage_posts', 'tsm_filter_post_type_by_taxonomy_posicoes');
function tsm_filter_post_type_by_taxonomy_posicoes() {
	global $typenow;
	$post_type = 'banners'; // change to your post type
	$taxonomy  = 'posicoes'; // change to your taxonomy
	if ($typenow == $post_type) {
		$selected      = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
		$info_taxonomy = get_taxonomy($taxonomy);
		wp_dropdown_categories(array(
		'show_option_all' => __("Mostrar todas as {$info_taxonomy->label}"),
		'taxonomy'        => $taxonomy,
		'name'            => $taxonomy,
		'orderby'         => 'name',
		'selected'        => $selected,
		'show_count'      => true,
		'hide_empty'      => true,
		));
	};
}
/**
 * Filter posts by taxonomy in admin
 * @author  Mike Hemberger
 * @link http://thestizmedia.com/custom-post-type-filter-admin-custom-taxonomy/
 */
add_filter('parse_query', 'tsm_convert_id_to_term_in_query_posicoes');
function tsm_convert_id_to_term_in_query_posicoes($query) {
	global $pagenow;
	$post_type = 'banners'; // change to your post type
	$taxonomy  = 'posicoes'; // change to your taxonomy
	$q_vars    = &$query->query_vars;
	if ( $pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0 ) {
		$term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
		$q_vars[$taxonomy] = $term->slug;
	}
}

include "includes/configuracoes-modulo.php";


// create custom plugin settings menu
add_action('admin_menu', 'inserir_menu_ferdzbanner_confs');





// js css especifico 

add_action( 'admin_head-edit.php', 'custom_css_js_so_14257172' );

function custom_css_js_so_14257172() 
{
    // Apply only in the correct CPT, otherwise it would print in Pages/Posts
    global $current_screen;
    if( 'banners' != $current_screen->post_type)
	return;
?>

<script>
	jQuery(document).ready(function() {
		jQuery.each(jQuery("select#posicoes option:first-child"),function() {
			var str =jQuery(this).html(); 
			if(str.indexOf("Show All") !== -1) {
				jQuery(this).parent().remove();
			}
		});
		
		jQuery.each(jQuery("select#clientes option:first-child"),function() {
			var str =jQuery(this).html(); 
			if(str.indexOf("Show All") !== -1) {
				jQuery(this).parent().remove();
			}
		});
	})
</script>
<?php
}

include "includes/relatorio-modulo.php";

// create custom plugin settings menu
add_action('admin_menu', 'inserir_menu_ferdzbanner_relatoriosemail');


include "includes/filtrar-modulo.php";

// create custom plugin settings menu
add_action('admin_menu', 'inserir_menu_ferdzbanner_relatorios');


include "includes/media-modulo.php";

// create custom plugin settings menu
add_action('admin_menu', 'inserir_menu_ferdzbanner_media');

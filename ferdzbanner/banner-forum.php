<?php 
define('WP_USE_THEMES', false);
require('../../../wp-load.php');
//echo do_shortcode("[ferdzbanner posicoes='Forum']");
$qry = new WP_Query(array("post_type" => "banners", "posicoes" => "Forum"));
//var_dump($qry);

while ( $qry->have_posts() ) : $qry->the_post();
	$file = get_post_meta(get_the_ID())['wp_custom_attachment'][0];
endwhile;
$file = "../../.." . explode(home_url(), $file)[1];
//$file = '../../../wp-content/uploads/2016/11/Final-C40-full-250.gif';
$type = 'image/gif';
//echo "3542343254325";

header('Content-Type:'.$type);
header('Content-Length: ' . filesize($file));
readfile($file);
?>
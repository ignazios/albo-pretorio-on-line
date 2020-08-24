<?php

//Check for rights
$path  = '';

if (!defined('WP_LOAD_PATH')) {
	$root = dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/';
	if (file_exists($root.'wp-load.php') ) {
		define('WP_LOAD_PATH', $root);
	} else {
		if (file_exists($path.'wp-load.php'))
			define('WP_LOAD_PATH', $path);
	}
}

//Load wp-load.php
if (defined('WP_LOAD_PATH'))
	require_once(WP_LOAD_PATH.'wp-load.php');
	
if ( !is_user_logged_in() || !current_user_can('edit_posts') )
	wp_die(__("You are not allowed to access this file."));

@header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php _e("Albo OnLine gruppo atti","albo-online");?></title>
	<base target="_self" />
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
	<script language="javascript" type="text/javascript">

		function init() {
			tinyMCEPopup.resizeToInnerSize();
		}

		function insertAlboShortCode() {
			var titolo   	 = document.getElementById('Titolo').value;
			var meta   		 = document.getElementById('listaAttiMeta').value;
			var valore   	 = document.getElementById('Value').value;
			var InvForm = document.forms.form;
			var tagtext = "[AlboGruppiAtti ";
			tagtext = tagtext + " titolo=\"" + titolo + "\"";
			tagtext = tagtext + " meta=\"" + meta + "\"";
			tagtext = tagtext + " valore=\"" + valore + "\"";
			tagtext = tagtext + "]";
			if(window.tinyMCE) {
				window.tinyMCE.activeEditor.execCommand('mceInsertContent', 0, tagtext);
			}
			tinyMCEPopup.close();
			return;
		}
	</script>
</head>
<body onload="tinyMCEPopup.executeOnLoad('init();');">
	<div class="mceActionPanel">
		<form name="form" action="#" method="get" accept-charset="utf-8">
				<label for="Titolo"><strong><?php _e("Intestazione Sezione","albo-online");?></strong></label><br /> 
				<input type="text" name="Titolo" id="Titolo" size="45">
				</p>				
				<p>
				<label for="listaAttiMeta"><strong><?php _e("Meta Dati codificati","albo-online");?></strong></label>
				<?php echo ap_get_elenco_attimeta("Select","listaAttiMeta","ListaAttiMeta","Si");?>
				</p>
				<p>
				<label for="Value"><strong><?php _e("Valore Meta","albo-online");?></strong></label><br /> 
				<input type="text" name="Value" id="Value">
				</p>				
		</form>
	</div>
		<div style="float: left">
			<input type="submit" id="insert" name="insert" value="<?php _e("Inserisci","albo-online");?>" onclick="insertAlboShortCode();" />
		</div>
		<div style="float: right">
			<input type="button" id="cancel" name="cancel" value="<?php _e("Annulla","albo-online");?>" onclick="tinyMCEPopup.close();" />
		</div>
</body>
</html>
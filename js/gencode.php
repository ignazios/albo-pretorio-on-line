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
	<title><?php _e("Albo OnLine","albo-online");?></title>
	<base target="_self" />
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
	<script language="javascript" type="text/javascript">

		function init() {
			tinyMCEPopup.resizeToInnerSize();
		}

		function insertAlboShortCode() {
			var stato   		 = document.getElementById('StatoAtti').value;
			var categorie 		 = document.getElementById('Categoria');
			var filtri  		 = document.getElementById('Filtri');
			var minfiltri  		 = document.getElementById('MinFiltri');
			var per_page  		 = document.getElementById('Per_page').value;
			var Categoriesel="";
			var InvForm = document.forms.form;
		    for (x=0;x<InvForm.Categoria.length;x++){
				if(InvForm.Categoria[x].selected){
					Categoriesel+= InvForm.Categoria[x].value + ",";
				}
			}
			Categoriesel=Categoriesel.substring(0, Categoriesel.length-1);
			var tagtext = "[Albo ";
			tagtext = tagtext + " stato=\"" + stato+ "\"";
			if (Categoriesel.length>0 )
				tagtext = tagtext + " cat=\"" + Categoriesel+"\"";
			if (filtri.checked)
				tagtext = tagtext + " filtri=\"si\"";
			else
				tagtext = tagtext + " filtri=\"no\"";
			if (minfiltri.checked)
				tagtext = tagtext + " minfiltri=\"no\"";
			else
			tagtext = tagtext + " minfiltri=\"si\"";
			tagtext = tagtext + " per_page=\""+per_page+"\"";
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
<?php 
$Ele_Cate=ap_get_dropdown_categorie("Categoria","Categoria","","","Nessuna", TRUE, FALSE,TRUE);
?>
	<div class="mceActionPanel">
		<form name="form" action="#" method="get" accept-charset="utf-8">
				<p>
				<label for="StatoAtti"><strong><?php _e("Stato Atti","albo-online");?></strong></label>
					<select id="StatoAtti" name="StatoAtti">
						<option value="1"><?php _e("Atti Correnti","albo-online");?></option>
						<option value="2"><?php _e("Atti Scaduti, Storico","albo-online");?></option>
					</select>
				</p>
				<p>
					<label for="Categoria"><strong><?php _e("Categoria","albo-online");?></strong></label>
					<?php echo $Ele_Cate; ?>
				</p>
				<p>
					<label for="Filtri"><strong><?php _e("Visualizza Filtri","albo-online");?></strong></label>
					<input type="checkbox" name="Filtri" id="Filtri" value="si"/>
				</p>
				<p>
					<label for="MinFiltri"><strong><?php _e("Finestra Filtri sempre visibile","albo-online");?></strong></label>
					<input type="checkbox" name="MinFiltri" id="MinFiltri" value="si"/>
				</p>
				<p>
					<label for="MinFiltri"><strong><?php _e("Numero atti da visualizzare","albo-online");?></strong></label>
					<input type="number" name="Per_page" id="Per_page" value="10" style="width:40px;" />
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
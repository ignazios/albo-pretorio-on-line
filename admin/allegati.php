<?php
/**
 * Gestione Allegati.
 * @link       http://www.eduva.org
 * @since      4.5.6
 *
 * @package    Albo On Line
 */
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }
?>
<div class="wrap">
	<div class="HeadPage" style="margin-bottom: 30px;">
		<h2 class="wp-heading-inline">Atti</h2>
		<a href="<?php echo site_url().'/wp-admin/admin.php?page=atti';?>" class="add-new-h2 tornaindietro"><?php echo __("Torna indietro","albo-online");?></a>
		<h3><?php echo __("Associa nuovo Allegato con file precedentemente caricato","albo-online");?></h3>	
	</div>
<div id="col-container">
	<form id="allegato" method="post" action="?page=atti" class="validate">
	<input type="hidden" name="operazione" value="associa_allegato" />
	<input type="hidden" name="action" value="memo-allegato-atto-associato" />
	<input type="hidden" name="secure" value="<?php echo wp_create_nonce('uploallegatoassociato')?>" />
	<input type="hidden" name="id" value="<?php echo (int)$_REQUEST['id']; ?>" />
<?php 
	if (isset($_REQUEST['ref']))
		echo '<input type="hidden" name="ref" value="'.htmlentities($_REQUEST['ref']).'" />';
?>	
	<table class="widefat">
	    <thead>
		<tr>
			<th colspan="3" style="text-align:center;font-size:2em;"><?php echo __("Dati Allegato","albo-online");?></th>
		</tr>
	    </thead>
	    <tbody id="dati-allegato">
		<tr>
			<th><?php _e("Descrizione Allegato","albo-online");?></th>
			<td><textarea  name="Descrizione" rows="2" cols="100" wrap="ON" maxlength="255" required></textarea></td>
		</tr>
		<tr>
			<th><?php _e("Natura File","albo-online");?></th>
			<td><select name="Natura" id="Natura" wrap="ON" >
				<option value="D">Documento firmato</option>
				<option value="A">Allegato</option>
			</select></td>
		</tr>
		<tr>
			<th><?php _e("Documento Integrale?","albo-online");?></th>
			<td><input type="checkbox" name="Integrale" value="1" id="Integrale" checked> </td>
		</tr>
		<tr>
			<th>File:</th>
			<td><?php echo ap_get_allegati_file_scollegati("Select");?></td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" name="submit" id="submit" class="button" value="<?php _e("Collega Allegato","albo-online");?>"  />
			<input type="submit" name="annulla" id="annulla" class="button" value="<?php _e("Annulla Operazione","albo-online");?>" />
			</td>
		</tr>
	    </tbody>
	</table>
	</form>
</div>
</div>
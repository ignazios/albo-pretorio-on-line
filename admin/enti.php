<?php
/**
 * WGestione Enti.
 * @link       http://www.eduva.org
 * @since      4.5.7
 *
 * @package    Albo On Line
 */

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

$messages[1] = __('Elemento aggiunto.','albo-online');
$messages[2] = __('Elemento cancellato.','albo-online');
$messages[3] = __('Elemento aggiornato.','albo-online');
$messages[4] = __('Elemento non aggiunto.','albo-online');
$messages[5] = __('Elemento non aggiornato.','albo-online');
$messages[6] = __('Elemento non cancellato.','albo-online');
$messages[7] = __('Impossibile cancellare Enti che sono collegati ad Atti','albo-online');
$messages[9] = __('Bisogna assegnare il nome al nuovo ente','albo-online');
$messages[80] = __("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione è stata annullata","albo-online");

?>
<div class="wrap nosubsub">
	<div class="HeadPage">
		<h2 class="wp-heading-inline"><span class="dashicons dashicons-awards" style="font-size: 1.1em;"></span> <?php _e("Enti","albo-online");?>
		<a href="?page=enti" class="add-new-h2"><?php _e("Aggiungi nuovo","albo-online");?></a></h2>
	</div>
<?php 
if ( (isset($_REQUEST['message']) && ( $msg = (int) $_REQUEST['message']))) {
	echo '<div id="message" class="updated"><p>'.$messages[$msg];
	if (isset($_REQUEST['errore'])) 
		echo '<br />'.htmlentities($_REQUEST['errore']);
	echo '</p></div>';
	$_SERVER['REQUEST_URI'] = remove_query_arg(array('message'), $_SERVER['REQUEST_URI']);
}
if (isset($_REQUEST['action']) And $_REQUEST['action']=="edit"){
	$risultato=ap_get_ente((int)$_REQUEST['id']);
	$edit=True;
}else{
	$edit=False;
}
?>
<div id="errori" title="<?php _e("Validazione Dati","albo-online");?>" style="display:none">
  <h3><?php _e("Lista Campi con Errori","albo-online");?>:</h3>
  	<p id="ElencoCampiConErrori"></p>
  	<p style='color:red;font-weight: bold;'><?php  _e("Correggere gli errori per continuare","albo-online");?></p>
</div>
<br class="clear" />
<div id="col-container">
<div id="col-right">
<div class="col-wrap">
<h3><?php _e("Elenco Enti","albo-online");?></h3>
<table class="widefat" id="elenco-enti"> 
    <thead>
    	<tr>
        	<th scope="col" style="text-align:center;"><?php _e("Enti","albo-online");?></th>
		</tr>
    </thead>
    <tbody id="the-list">
<?php 
$lista=ap_get_enti(); 
echo '<tr>
        	<td>
			<ul>';
$shift=1;
if ($lista){
	foreach($lista as $riga){
		echo'<li style="text-align:left;padding-left:1px;">';
	 	$Tab=0;
		$Testo_da=__("Confermi la cancellazione dell'Ente","albo-online")." ".stripslashes($riga->Nome). "?\n\n".__("Sei sicuro di voler proseguire con la CANCELLAZIONE?","albo-online");
		if($riga->IdEnte>0 and ap_num_enti_atto($riga->IdEnte)==0)
			echo '<span class="cancella"><a href="?page=enti&amp;action=delete-ente&amp;id='.$riga->IdEnte.'&amp;cancellaente='.wp_create_nonce('deleteente').'" rel="'.$Testo_da.'" class="confdel">
					<span class="dashicons dashicons-trash" title="'.__("Cancella ente","albo-online").'"></span>
					</a></span>';
		else
			$Tab=23;		
		echo '					<a href="?page=enti&amp;action=edit-ente&amp;id='.$riga->IdEnte.'&amp;modificaente='.wp_create_nonce('editente').'" rel="'.stripslashes($riga->Nome).'">
					<span class="dashicons dashicons-edit" title="'.__("Modifica ente","albo-online").'" style="margin-left:'.$Tab.'px;"></span>
					</a>';
		echo '<strong>'.stripslashes($riga->Nome).'</strong> (n&ordm; atti '.ap_num_enti_atto($riga->IdEnte).')';
		echo '</li>';
	}
} else {
		echo '<li>'.__("Nessun Ente Codificato","albo-online").'</li>';
}
echo '</td>
	</tr>
</ul>
	</tbody>
</table>
</div>
<div class="col-wrap">
<h3>Log</h3>';
$righe=ap_get_all_Oggetto_log(7);
echo'
	<table class="widefat">
	    <thead>
		<tr>
			<th style="font-size:1.2em;">'.__("Data","albo-online").'</th>
			<th style="font-size:1.2em;">'.__("Operazione","albo-online").'</th>
			<th style="font-size:1.2em;">'.__("Informazioni","albo-online").'</th>
		</tr>
	    </thead>
	    <tbody id="righe-log">';
foreach ($righe as $riga) {
	switch ($riga->TipoOperazione){
	 	case 1:
	 		$Operazione=__("Inserimento","albo-online");
	 		break;
	 	case 2:
	 		$Operazione=__("Modifica","albo-online");
			break;
	 	case 3:
	 		$Operazione=__("Cancellazione","albo-online");
			break;
	}
	echo '<tr  title="'.$riga->Utente.' da '.$riga->IPAddress.'">
			<td >'.$riga->Data.'</th>
			<td >'.$Operazione.'</th>
			<td >'.stripslashes($riga->Operazione).'</td>
		</tr>';
}
echo '    </tbody>
	</table>
</div>';
?>
</div><!-- /col-right -->

<div id="col-left">
<div class="form-wrap">
	<div class="Obbligatori">
		<span style="color:red;font-weight: bold;">*</span> <?php printf(__("i campi contrassegnati dall'asterisco sono %s obbligatori %s","albo-online"),"<strong>","</strong>");?>
	</div>
	<br />
	<form id="addtag" method="post" action="?page=enti" class="<?php if($edit) echo "edit"; else echo "validate"; ?>"  >
		<input type="hidden" name="action" value="<?php if($edit || (isset($_REQUEST['action']) And  $_REQUEST['action']=="edit_err")) echo "memo-ente"; else echo "add-ente"; ?>"/>
		<input type="hidden" name="action2" value="<?php echo htmlentities(isset($_REQUEST['action'])?$_REQUEST['action']:""); ?>"/>
		<input type="hidden" name="id" value="<?php echo (int)isset($_REQUEST['id'])?$_REQUEST['id']:0; ?>" />
		<input type="hidden" name="enti" value="<?php echo wp_create_nonce('enti')?>" />

		<div class="form-field form-required"  style="margin-bottom:0px;margin-top:0px;">
			<label for="ente-nome"><?php _e("Nome Ente","albo-online");?> <span style="color:red;font-weight: bold;">*</span></label>
			<input name="ente-nome" id="<?php _e("Nome Ente","albo-online");?>" type="text" value="<?php if($edit) echo stripslashes($risultato->Nome); else echo htmlentities((isset($_REQUEST['ente-nome'])?$_REQUEST['ente-nome']:"")); ?>" size="30" alt="Nome Ente" class="richiesto"/>
		</div>
		<div class="form-field"  style="margin-bottom:0px;margin-top:0px;">
			<label for="ente-indirizzo"><?php _e("Indirizzo","albo-online");?></label>
			<input name="ente-indirizzo" id="ente-indirizzo" type="text" value="<?php if($edit) echo stripslashes($risultato->Indirizzo); else echo htmlentities((isset($_REQUEST['ente-indirizzo'])?$_REQUEST['ente-indirizzo']:"")); ?>" size="150"/>
		</div>
		<div class="form-field form-required"  style="margin-bottom:0px;margin-top:0px;">
			<label for="ente-url"><?php _e("Url","albo-online");?></label>
			<input name="ente-url" id="ente-url" type="text" value='<?php if($edit) echo stripslashes($risultato->Url); else echo htmlentities((isset($_REQUEST['ente-url'])?$_REQUEST['ente-url']:""));?>' size="100"/>
		</div>
		<div class="form-field form-required"  style="margin-bottom:0px;margin-top:0px;">
			<label for="ente-email"><?php _e("Email","albo-online");?> <span style="color:red;font-weight: bold;">*</span></label>
			<input name="ente-email" id="<?php _e("Email","albo-online");?>" type="text" value="<?php if($edit) echo stripslashes($risultato->Email); else echo htmlentities((isset($_REQUEST['ente-email'])?$_REQUEST['ente-email']:""));?>" size="100" alt="Email" class="richiesto"/>
		</div>
		<div class="form-field form-required"  style="margin-bottom:0px;margin-top:0px;">
			<label for="ente-pec"><?php _e("Pec","albo-online");?> <span style="color:red;font-weight: bold;">*</span></label>
			<input name="ente-pec" id="<?php _e("Pec","albo-online");?>" type="text" value="<?php if($edit) echo stripslashes($risultato->Pec); else echo htmlentities((isset($_REQUEST['ente-pec'])?$_REQUEST['ente-pec']:""));?>" size="100" alt="Pec" class="richiesto"/>
		</div>
		<div class="form-field"  style="margin-bottom:0px;margin-top:0px;">
			<label for="ente-telefono"><?php _e("Telefono","albo-online");?></label>
			<input name="ente-telefono" id="ente-telefono" type="text" value="<?php if($edit) echo stripslashes($risultato->Telefono); else echo htmlentities((isset($_REQUEST['ente-telefono'])?$_REQUEST['ente-telefono']:"")); ?>" size="30"/>
		</div>
		<div class="form-field"  style="margin-bottom:0px;margin-top:0px;">
			<label for="ente-fax"><?php _e("Fax","albo-online");?></label>
			<input name="ente-fax" id="ente-fax" type="text" value="<?php if($edit) echo stripslashes($risultato->Fax); else echo htmlentities((isset($_REQUEST['ente-fax'])?$_REQUEST['ente-fax']:"")); ?>" size="30"/>
		</div>
		<div class="form-field"  style="margin-bottom:0px;margin-top:0px;">
			<label for="tag-description"><?php _e("Note","albo-online");?></label>
			<textarea name="ente-note" id="ente-note" rows="5" cols="40"><?php if($edit) echo stripslashes($risultato->Note); else echo htmlentities((isset($_REQUEST['ente-note'])?$_REQUEST['ente-note']:"")); ?></textarea>
			<p><?php _e("inserire eventuali informazioni aggiuntive","albo-online");?></p>
		</div>

<?php
if($edit) {
	echo '<input type="submit" name="SaveData" id="SaveData" class="button" value="'. __("Memorizza Modifiche Ente","albo-online").' '.stripslashes($risultato->Nome).'" rel="'.stripslashes($risultato->Nome).'" />';
}else{
 	if (isset($_REQUEST['action']) And $_REQUEST['action']=="edit_err")
		echo '<input type="submit" name="SaveData" id="SaveData" class="button" value="'. __("Memorizza Modifiche Ente","albo-online").' '.stripslashes($risultato->Nome).'" rel="'.htmlentities($_REQUEST['ente-nome']).'" />';
	else
		echo '<input type="submit" name="SaveData" id="SaveData" class="button" value="'. __("Aggiungi nuovo Ente","albo-online").'"  />';
}
?>
	</form>
</div>
</div><!-- /col-container -->
</div><!-- /wrap -->
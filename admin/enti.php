<?php
/**
 * WGestione Enti.
 * @link       http://www.eduva.org
 * @since      4.4.4
 *
 * @package    Albo On Line
 */

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

$messages[1] = __('Item added.');
$messages[2] = __('Item deleted.');
$messages[3] = __('Item updated.');
$messages[4] = "Elemento non Memorizzato";
$messages[5] = __('Item not updated.');
$messages[6] = __('Item not deleted.');
$messages[7] = __('Impossibile cancellare Enti che sono collegati ad Atti');
$messages[80] = 'ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l\'operazione &egrave; stata annullata';
?>
<div class="wrap nosubsub">
	<div class="HeadPage">
		<h2 class="wp-heading-inline"><span class="dashicons dashicons-awards" style="font-size: 1.1em;"></span> Enti
		<a href="?page=enti" class="add-new-h2">Aggiungi nuovo</a></h2>
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
//	print_r($risultato);exit;
	$edit=True;
}else{
	$edit=False;
}
?>
<div id="errori" title="Validazione Dati" style="display:none">
  <h3>Lista Campi con Errori:</h3><p id="ElencoCampiConErrori"></p><p style='color:red;font-weight: bold;'>Correggere gli errori per continuare</p>
</div>
<br class="clear" />
<div id="col-container">
<div id="col-right">
<div class="col-wrap">
<h3>Elenco Enti</h3>
<table class="widefat" id="elenco-enti"> 
    <thead>
    	<tr>
        	<th scope="col" style="text-align:center;">Enti</th>
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
		if($riga->IdEnte>0 and ap_num_enti_atto($riga->IdEnte)==0)
			echo '<span class="cancella"><a href="?page=enti&amp;action=delete-ente&amp;id='.$riga->IdEnte.'&amp;cancellaente='.wp_create_nonce('deleteente').'" rel="'.stripslashes($riga->Nome).'" class="dr">
					<span class="dashicons dashicons-trash" title="Cancella ente"></span>
					</a></span>';
		else
			$Tab=23;		
		echo '					<a href="?page=enti&amp;action=edit-ente&amp;id='.$riga->IdEnte.'&amp;modificaente='.wp_create_nonce('editente').'" rel="'.stripslashes($riga->Nome).'">
					<span class="dashicons dashicons-edit" title="Modifica ente" style="margin-left:'.$Tab.'px;"></span>
					</a>';
		echo '<strong>'.stripslashes($riga->Nome).'</strong> (n&ordm; atti '.ap_num_enti_atto($riga->IdEnte).')';
		echo '</li>';
	}
} else {
		echo '<li>Nessun Ente Codificato</li>';
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
			<th style="font-size:1.2em;">Data</th>
			<th style="font-size:1.2em;">Operazione</th>
			<th style="font-size:1.2em;">Informazioni</th>
		</tr>
	    </thead>
	    <tbody id="righe-log">';
foreach ($righe as $riga) {
	switch ($riga->TipoOperazione){
	 	case 1:
	 		$Operazione="Inserimento";
	 		break;
	 	case 2:
	 		$Operazione="Modifica";
			break;
	 	case 3:
	 		$Operazione="Cancellazione";
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
		<span style="color:red;font-weight: bold;">*</span> i campi contrassegnati dall'asterisco sono <strong>obbligatori</strong>
	</div>
	<form id="addtag" method="post" action="?page=enti" class="<?php if($edit) echo "edit"; else echo "validate"; ?>"  >
		<input type="hidden" name="action" value="<?php if($edit || (isset($_REQUEST['action']) And  $_REQUEST['action']=="edit_err")) echo "memo-ente"; else echo "add-ente"; ?>"/>
		<input type="hidden" name="action2" value="<?php echo htmlentities(isset($_REQUEST['action'])?$_REQUEST['action']:""); ?>"/>
		<input type="hidden" name="id" value="<?php echo (int)isset($_REQUEST['id'])?$_REQUEST['id']:0; ?>" />
		<input type="hidden" name="enti" value="<?php echo wp_create_nonce('enti')?>" />

<div class="form-field form-required"  style="margin-bottom:0px;margin-top:0px;">
	<label for="ente-nome">Nome Ente <span style="color:red;font-weight: bold;">*</span></label>
	<input name="ente-nome" id="ente-nome" type="text" value='<?php if($edit) echo stripslashes($risultato->Nome); else echo htmlentities((isset($_REQUEST['ente-nome'])?$_REQUEST['ente-nome']:"")); ?>' size="30" alt="Nome Ente" class="richiesto"/>
</div>
<div class="form-field"  style="margin-bottom:0px;margin-top:0px;">
	<label for="ente-indirizzo">Indirizzo</label>
	<input name="ente-indirizzo" id="ente-indirizzo" type="text" value='<?php if($edit) echo stripslashes($risultato->Indirizzo); else echo htmlentities((isset($_REQUEST['ente-indirizzo'])?$_REQUEST['ente-indirizzo']:"")); ?>' size="150"/>
</div>
<div class="form-field form-required"  style="margin-bottom:0px;margin-top:0px;">
	<label for="ente-url">Url</label>
	<input name="ente-url" id="ente-url" type="text" value='<?php if($edit) echo stripslashes($risultato->Url); else echo htmlentities((isset($_REQUEST['ente-url'])?$_REQUEST['ente-url']:""));?>' size="100"/>
</div>
<div class="form-field form-required"  style="margin-bottom:0px;margin-top:0px;">
	<label for="ente-email">Email <span style="color:red;font-weight: bold;">*</span></label>
	<input name="ente-email" id="ente-email" type="text" value='<?php if($edit) echo stripslashes($risultato->Email); else echo htmlentities((isset($_REQUEST['ente-email'])?$_REQUEST['ente-email']:""));?>' size="100" alt="Email" class="richiesto"/>
</div>
<div class="form-field form-required"  style="margin-bottom:0px;margin-top:0px;">
	<label for="ente-pec">Pec <span style="color:red;font-weight: bold;">*</span></label>
	<input name="ente-pec" id="ente-pec" type="text" value='<?php if($edit) echo stripslashes($risultato->Pec); else echo htmlentities((isset($_REQUEST['ente-pec'])?$_REQUEST['ente-pec']:""));?>' size="100" alt="Pec" class="richiesto"/>
</div>
<div class="form-field"  style="margin-bottom:0px;margin-top:0px;">
	<label for="ente-telefono">Telefono</label>
	<input name="ente-telefono" id="ente-telefono" type="text" value='<?php if($edit) echo stripslashes($risultato->Telefono); else echo htmlentities((isset($_REQUEST['ente-telefono'])?$_REQUEST['ente-telefono']:"")); ?>' size="30"/>
</div>
<div class="form-field"  style="margin-bottom:0px;margin-top:0px;">
	<label for="ente-fax">Fax</label>
	<input name="ente-fax" id="ente-fax" type="text" value='<?php if($edit) echo stripslashes($risultato->Fax); else echo htmlentities((isset($_REQUEST['ente-fax'])?$_REQUEST['ente-fax']:"")); ?>' size="30"/>
</div>
<div class="form-field"  style="margin-bottom:0px;margin-top:0px;">
	<label for="tag-description">Note</label>
	<textarea name="ente-note" id="ente-note" rows="5" cols="40"><?php if($edit) echo stripslashes($risultato->Note); else echo htmlentities((isset($_REQUEST['ente-note'])?$_REQUEST['ente-note']:"")); ?></textarea>
	<p>inserire eventuali informazioni aggiuntive</p>
</div>

<?php
if($edit) {
	echo '<input type="submit" name="SaveData" id="SaveData" class="button" value="Memorizza Modifiche" rel="'.stripslashes($risultato->Nome).'" />';
}else{
 	if (isset($_REQUEST['action']) And $_REQUEST['action']=="edit_err")
		echo '<input type="submit" name="SaveData" id="SaveData" class="button" value="Memorizza Modifiche" rel="'.htmlentities($_REQUEST['ente-nome']).'" />';
	else
		echo '<input type="submit" name="SaveData" id="SaveData" class="button" value="Aggiungi nuovo Ente"  />';	
}
?>
</form>
</div>
</div><!-- /col-container -->
</div><!-- /wrap -->
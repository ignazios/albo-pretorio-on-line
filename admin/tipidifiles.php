<?php
/**
 * Gestione Responsabili.
 * @link       http://www.eduva.org
 * @since      4.5.6
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
$messages[7] = __('Impossibile cancellare i Tipi di files che sono collegati ad Atti','albo-online');
$messages[8] = __('Impossibile creare il Tipo di file perchè mancano dati obbligatori','albo-online');
$messages[80] = __("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione è stata annullata","albo-online");
?>
<div id="errori" title="<?php _e("Validazione Dati","albo-online");?>" style="display:none">
  <h3><?php _e("Lista Campi con Errori","albo-online");?>:</h3>
  	<p id="ElencoCampiConErrori"></p>
  	<p style='color:red;font-weight: bold;'><?php  _e("Correggere gli errori per continuare","albo-online");?></p>
</div>
<div class="wrap nosubsub">
	<div class="HeadPage">
		<h2 class="wp-heading-inline"><span class="dashicons dashicons-paperclip"></span> <?php _e("Tipi di Files","albo-online");?>
		<a href="?page=tipifiles" class="add-new-h2"><?php _e("Aggiungi nuovo","albo-online");?></a></h2>
	</div>
<?php
$lista=ap_get_tipidifiles(); 
$NC="";
if (isset($_REQUEST['action']) And $_REQUEST['action']=="delete-tipidifiles"){
	if (!isset($_REQUEST['canctipfil'])) {
		$NC=$messages[80];
	}else{
		if (!wp_verify_nonce($_REQUEST['canctipfil'],'deletetipidifiles')){
			$NC=$messages[80];
		}else{
			$risultato=ap_del_tipidifiles((int)$_REQUEST['id']);
			if(is_array($risultato)){
				$NC=sprintf(__("Il Tipo di File non può essere cancellato perchè ci sono %s atti che lo utilizzano",$risultato["atti"]));
			}
		}
	}	
} 
if ( (isset($_REQUEST['message']) && ( $msg = (int) $_REQUEST['message'])) or $NC!="") {
	echo '<div id="message" class="updated"><p>'.$messages[$msg]. $NC;
	if (isset($_REQUEST['errore'])) 
		echo '<br />'.htmlentities($_REQUEST['errore']);
	echo '</p></div>';
	$_SERVER['REQUEST_URI'] = remove_query_arg(array('message'), $_SERVER['REQUEST_URI']);
}
if (isset($_REQUEST['action']) And $_REQUEST['action']=="edit"){
	$edit=True;
}else{
	$edit=False;
}

?>
<br class="clear" />
<div id="col-container">
<div id="col-right">
<div class="col-wrap">
<h3><?php  _e("Elenco Tipi di Files","albo-online");?> <a href="?page=tipifiles&action=set-default&tipifiles=<?php echo wp_create_nonce('elabtipifiles')?>" class="add-new-h2"><?php  _e("Reimposta Estensioni di Default","albo-online");?></a></h3>
<table class="widefat" id="elenco-tipidifiles"> 
    <thead>
    	<tr>
        	<th scope="col" style="text-align:center;"><?php  _e("Tipi di Files","albo-online");?></th>
		</tr>
    </thead>
    <tbody id="the-list">
<?php 
echo '<tr>
        	<td>
			<ul>';
if ($lista){
	$Tipi=ap_num_tipidifiles_atti();
	foreach($lista as $TipoFile =>$riga){
		echo'<li style="text-align:left;padding-left:1px;">';
		$Tab=0;
		if($Tipi[strtolower($TipoFile)]==0 and $TipoFile!="ndf")
			echo '<span class="cancella"><a href="?page=tipifiles&amp;action=delete-tipofile&amp;id='.$TipoFile.'&amp;canctipfil='.wp_create_nonce('deletetipofile').'" rel="'.$riga['Descrizione'].'" class="confdel">
			<span class="dashicons dashicons-trash" title="'.__('Cancella tipo file','albo-online').'"></span>
			</a></span>';
		else
			$Tab=23;
		echo '
			<a href="?page=tipifiles&amp;action=edit-tipofile&amp;id='.$TipoFile.'&amp;modtipfil='.wp_create_nonce('edittipofilee').'" rel="'.$riga['Descrizione'].'">
			<span class="dashicons dashicons-edit" title="'.__('Modifica tipo file','albo-online').'" style="margin-left:'.$Tab.'px;"></span>
			</a>
			('.$TipoFile.') '.$riga['Descrizione'] .($TipoFile!="ndf"?'(n&ordm; atti '.$Tipi[$TipoFile].')':"").'</li>'; 
	}
} else {
		echo '<li>'.__('Nessun Tipo File Codificato','albo-online').'</li>';
}
echo '</td>
	</tr>
</ul>
	</tbody>
</table>
</div>
<div class="col-wrap">
<h3>Log</h3>';
$righe=ap_get_all_Oggetto_log(8);
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
	<div class="Obbligatori">
		<span style="color:red;font-weight: bold;">*</span> <?php printf(__("i campi contrassegnati dall'asterisco sono %s obbligatori %s","albo-online"),"<strong>","</strong>");?>
	</div>
	<br />
<div class="form-wrap">
	<form id="addtag" method="post" action="?page=tipifiles" class="<?php if($edit) echo "edit"; else echo "validate"; ?>"  >
		<input type="hidden" name="action" value="<?php if($edit ||(isset($_REQUEST['action']) And  $_REQUEST['action']=="edit_err")) echo "memo-tipofile"; else echo "add-tipofile"; ?>"/>
		<input type="hidden" name="id" value="<?php echo isset($_REQUEST['id'])?$_REQUEST['id']:""; ?>" />
		<input type="hidden" name="tipifiles" value="<?php echo wp_create_nonce('elabtipifiles')?>" />
		<div class="form-required">
			<label for="estensione"><?php _e("Tipo File","albo-online");?><span style="color:red;font-weight: bold;">*</span></label>
			<input name="id" id="<?php _e("Tipo File","albo-online");?>" type="text" value="<?php if($edit) echo stripslashes($_REQUEST['id']);?>" size="6" aria-required="true" <?php echo ($edit?'Disabled':"");?> class="richiesto"/>
		</div>
		<div class="form-field form-required">
			<label for="descrizione"><?php _e("Descrizione","albo-online");?><span style="color:red;font-weight: bold;">*</span></label>
			<input name="descrizione" id="<?php _e("Descrizione","albo-online");?>" type="text" value="<?php if($edit) echo stripslashes($lista[$_REQUEST['id']]["Descrizione"]); ?>" size="60" aria-required="true" class="richiesto"/>
		</div>
		<div class="form-field form-required">
			<label for="icona"><?php _e("Icona","albo-online");?><span style="color:red;font-weight: bold;">*</span></label>
			<input name="icona" id="<?php _e("Icona","albo-online");?>" type="text" value="<?php if($edit) echo stripslashes($lista[$_REQUEST['id']]["Icona"]);?>" size="60" aria-required="true" class="richiesto"/>
				<div style="float:left;"><input id="icona_upload" class="button" type="button" value="Carica" />
					<br /><?php _e("Dimensione max 30x30","albo-online");?>
				</div>
				<div style="float:left;margin-left:10%;margin-top:5px;">
		<?php if(isset($_REQUEST['id']) And $lista[$_REQUEST['id']]["Icona"]){?>
					<img src="<?php if($edit) echo stripslashes($lista[$_REQUEST['id']]["Icona"]);?>" width="30" height="30" id="IconaTipoFile"/>
		<?php }?>
		</div>
</div>
	<div class="clear"></div>
	<div class="form-field form-required">
		<label for="verifica"><?php _e("Verifica","albo-online");?></label>
		<input name="verifica" id="verifica" type="text" value="<?php if($edit) echo stripslashes($lista[$_REQUEST['id']]["Verifica"]);?>" size="60" aria-required="true" />
	</div>

<?php
if($edit) {
	echo '<input type="submit" name="SaveData" id="SaveData" class="button" value="'. __("Memorizza Modifiche Formato File","albo-online").' '.$_REQUEST['id'].'" rel="'.$_REQUEST['id'].'" />';
}else{
 	if (isset($_REQUEST['action']) And $_REQUEST['action']=="edit_err")
		echo '<input type="submit" name="SaveData" id="SaveData" class="button" value="'. __("Memorizza Modifiche Formato File","albo-online").' '.htmlentities($_GET['resp-cognome']).'" rel="'.htmlentities($_GET['resp-cognome']).'" />';
	else
		echo '<input type="submit" name="SaveData" id="SaveData" class="button" value="'. __("Aggiungi nuovo Tipo File","albo-online").'"  />';	
}
?>
</form>
</div>
</div><!-- /col-container -->
</div><!-- /wrap -->
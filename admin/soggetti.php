<?php
/**
 * Gestione Soggetti Procedimento.
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
$messages[7] = __('Impossibile cancellare Soggetti che sono collegati ad Atti','albo-online');
$messages[80] = __("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione è stata annullata","albo-online");
?>
<div id="errori" title="<?php _e("Validazione Dati","albo-online");?>" style="display:none">
  <h3><?php _e("Lista Campi con Errori","albo-online");?>:</h3>
  	<p id="ElencoCampiConErrori"></p>
  	<p style='color:red;font-weight: bold;'><?php  _e("Correggere gli errori per continuare","albo-online");?></p>
</div>
<div class="wrap nosubsub">
	<div class="HeadPage">
		<h2 class="wp-heading-inline"><span class="dashicons dashicons-businessman" style="font-size: 1.1em;"></span> <?php _e("Soggetti Procedimento","albo-online");?>
		<a href="?page=soggetti" class="add-new-h2"><?php _e("Aggiungi nuovo","albo-online");?></a></h2>
	</div>
<?php
$SoggettiAtti=ap_get_NumAttiSoggetti();
$NC="";
if (isset($_REQUEST['action']) And $_REQUEST['action']=="delete-responsabile"){
	if (!isset($_REQUEST['cancresp'])) {
		$NC=$messages[80];
	}else{
		if (!wp_verify_nonce($_REQUEST['cancresp'],'deleteresponsabile')){
			$NC=$messages[80];
		}else{
			if(isset($SoggettiAtti[(int)$_REQUEST['id']]) And $SoggettiAtti[(int)$_REQUEST['id']]>0){
				$NC=__('Impossibile cancellare Soggetti che sono collegati ad Atti','albo-online');
			}else{
				$NC=ap_del_responsabile((int)$_REQUEST['id']);
			}
		}
	}	
} 
if ( (isset($_REQUEST['message']) && ( $msg = (int) $_REQUEST['message'])) or $NC!="") {
	echo '<div id="message" class="updated"><p>'.(isset($msg)?$messages[$msg]:""). $NC;
	if (isset($_REQUEST['errore'])) 
		echo '<br />'.htmlentities($_REQUEST['errore']);
	echo '</p></div>';
	$_SERVER['REQUEST_URI'] = remove_query_arg(array('message'), $_SERVER['REQUEST_URI']);
}
if (isset($_REQUEST['action']) And $_REQUEST['action']=="edit"){
	$risultato=ap_get_responsabile((int)$_REQUEST['id']);
	$edit=True;
}else{
	$edit=False;
}

?>
<br class="clear" />
<div id="col-container">
<div id="col-right">
<div class="col-wrap">
<h3><?php  _e("Elenco Soggetti","albo-online");?></h3>
<table class="widefat" id="elenco-responsabili"> 
    <thead>
    	<tr>
        	<th scope="col" style="text-align:center;"><?php  _e("Soggetti","albo-online");?></th>
		</tr>
    </thead>
    <tbody id="the-list">
<?php 
$lista=ap_get_responsabili(); 
echo '<tr>
        	<td>
			<ul>';
if ($lista){
	foreach($lista as $riga){
		$Funzione=ap_get_Funzione_Responsabile($riga->Funzione,"Descrizione");
		echo'<li style="text-align:left;padding-left:1px;">';
		$Tab=0;
		if(ap_get_NumAttiSoggetto($riga->IdResponsabile)==0)
			echo '<span class="cancella"><a href="?page=soggetti&amp;action=delete-responsabile&amp;id='.$riga->IdResponsabile.'&amp;cancresp='.wp_create_nonce('deleteresponsabile').'" rel="'.$riga->Cognome.'" class="dr">
			<span class="dashicons dashicons-trash" title="'.__("Cancella soggetto","albo-online").'"></span>
			</a></span>';
		else
			$Tab=23;
		echo '
			<a href="?page=soggetti&amp;action=edit-responsabile&amp;id='.$riga->IdResponsabile.'&amp;modresp='.wp_create_nonce('editresponsabile').'" rel="'.$riga->Cognome.'">
			<span class="dashicons dashicons-edit" title="'.__("Modifica soggetto","albo-online").'" style="margin-left:'.$Tab.'px;"></span>
			</a>
			('.$riga->IdResponsabile.') '.$riga->Cognome .' (n&ordm; atti '.(isset($SoggettiAtti[$riga->IdResponsabile])?$SoggettiAtti[$riga->IdResponsabile]:0).') <strong>'.$Funzione.'</strong>
			</li>'; 
	}
} else {
		echo '<li>'.__("Nessun Soggetto Codificato","albo-online").'</li>';
}
echo '</td>
	</tr>
</ul>
	</tbody>
</table>
</div>
<div class="col-wrap">
<h3>Log</h3>';
$righe=ap_get_all_Oggetto_log(4);
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
<div class="form-wrap">
<form id="addtag" method="post" action="?page=soggetti" class="<?php if($edit) echo "edit"; else echo "validate"; ?>"  >
	<input type="hidden" name="action" value="<?php if($edit ||(isset($_REQUEST['action']) And  $_REQUEST['action']=="edit_err")) echo "memo-responsabile"; else echo "add-responsabile"; ?>"/>
	<input type="hidden" name="id" value="<?php echo (int)isset($_REQUEST['id'])?$_REQUEST['id']:0; ?>" />
	<input type="hidden" name="responsabili" value="<?php echo wp_create_nonce('elabresponsabili')?>" />

<div class="form-field form-required">
	<label for="resp-cognome"><?php _e("Cognome","albo-online");?><span style="color:red;font-weight: bold;">*</span></label>
	<input name="resp-cognome" id="<?php _e("Cognome","albo-online");?>" type="text" value="<?php if($edit) echo stripslashes($risultato[0]->Cognome); else echo htmlentities((isset($_GET['resp-cognome'])?$_GET['resp-cognome']:"")); ?>" size="20" class="richiesto" />
</div>
<div class="form-field form-required">
	<label for="resp-nome"><?php _e("Nome","albo-online");?> <span style="color:red;font-weight: bold;">*</span></label>
	<input name="resp-nome" id="<?php _e("Nome","albo-online");?>" type="text" value="<?php if($edit) echo stripslashes($risultato[0]->Nome); else echo htmlentities((isset($_GET['resp-nome'])?$_GET['resp-nome']:"")); ?>" size="20" class="richiesto" />
</div>
<div class="form-field form-required">
	<label for="resp-funzione"><?php _e("Funzione","albo-online");?></label>
	<?php echo ap_get_Funzioni_Responsabili($Output="Select",$ID="resp-funzione",$Name="resp-funzione",$Selezionato=($edit)?$risultato[0]->Funzione:"");?>
<div class="form-field form-required">
	<label for="resp-email"><?php _e("Email","albo-online");?> <span style="color:red;font-weight: bold;">*</span></label>
	<input name="resp-email" id="<?php _e("Email","albo-online");?>" type="text" value="<?php if($edit) echo stripslashes($risultato[0]->Email); else echo htmlentities((isset($_GET['resp-email'])?$_GET['resp-email']:""));?>" size="100" class="richiesto" />
</div>
<div class="form-field form-required">
	<label for="resp-telefono"><?php _e("Telefono","albo-online");?></label>
	<input name="resp-telefono" id="resp-telefono" type="text" value="<?php if($edit) echo stripslashes($risultato[0]->Telefono); else echo htmlentities((isset($_GET['resp-telefono'])?$_GET['resp-telefono']:"")); ?>" size="30" aria-required="true" />
</div>
<div class="form-field form-required">
	<label for="resp-orario"><?php _e("Orario ricevimento","albo-online");?></label>
	<input name="resp-orario" id="resp-orario" type="text" value="<?php if($edit) echo stripslashes($risultato[0]->Orario);  else echo htmlentities((isset($_GET['resp-orario'])?$_GET['resp-orario']:""));?>" size="60" aria-required="true" />
</div>
<div class="form-field">
	<label for="resp-description"><?php _e("Note","albo-online");?></label>
	<textarea name="resp-note" id="resp-note" rows="5" cols="40"><?php if($edit) echo stripslashes($risultato[0]->Note); else echo htmlentities((isset($_GET['resp-note'])?$_GET['resp-note']:"")); ?></textarea>
	<p><?php _e("inserire eventuali informazioni aggiuntive","albo-online");?></p>
</div>

<?php
if($edit) {
	echo '<input type="submit" name="SaveData" id="SaveData" class="button" value="'. __("Memorizza Modifiche Soggetto","albo-online").' '.$risultato[0]->Cognome.'" rel="'.stripslashes($risultato[0]->Cognome).'" />';
}else{
 	if (isset($_REQUEST['action']) And $_REQUEST['action']=="edit_err")
		echo '<input type="submit" name="SaveData" id="SaveData" class="button" value="'. __("Memorizza Dati Soggetto","albo-online").' '.htmlentities($_GET['resp-cognome']).'" rel="'.htmlentities($_GET['resp-cognome']).'" />';
	else
		echo '<input type="submit" name="SaveData" id="SaveData" class="button" value="'. __("Aggiungi nuovo Soggetto","albo-online").'"  />';	
}
?>
</form>
</div>
</div><!-- /col-container -->
</div><!-- /wrap -->


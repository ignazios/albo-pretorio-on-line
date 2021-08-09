<?php
/**
 * Gestione Categorie.
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
$messages[7] = __('Impossibile cancellare Categorie che contengono Categorie Figlio. Cancellare prima i Figli','albo-online');
$messages[8] = __('Impossibile cancellare Categorie che sono collegate ad Atti','albo-online');
$messages[9] = __('Bisogna assegnare il nome alla nuova categoria','albo-online');
$messages[80] = __("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione è stata annullata","albo-online");
?>
<div id="errori" title="<?php _e("Validazione Dati","albo-online");?>" style="display:none">
  <h3><?php _e("Lista Campi con Errori","albo-online");?>:</h3>
  	<p id="ElencoCampiConErrori"></p>
  	<p style='color:red;font-weight: bold;'><?php  _e("Correggere gli errori per continuare","albo-online");?></p>
</div>
<div class="wrap nosubsub">
	<div class="HeadPage">
		<h2 class="wp-heading-inline"><span class="dashicons dashicons-category"></span> <?php _e("Categorie Atti","albo-online");?>
		<a href="?page=categorie" class="add-new-h2"><?php _e("Aggiungi nuovo","albo-online");?></a></h2>
	</div>
<?php 
if ( isset($_REQUEST['message']) && ( $msg = (int) $_REQUEST['message'] ) ) {
	echo '<div id="message" class="updated"><p>'.$messages[$msg].'</p></div>';
	$_SERVER['REQUEST_URI'] = remove_query_arg(array('message'), $_SERVER['REQUEST_URI']);
}
if (isset($_REQUEST['action']) And $_REQUEST['action']=="edit"){
	$risultato=ap_get_categoria($_REQUEST['id']);
//	print_r($risultato);
	$edit=True;
}else{
	$edit=False;
}
?>
<br class="clear" />
<div id="col-container">
<div id="col-right">
<div class="col-wrap">
<h3><?php _e("Elenco Categorie codificate","albo-online");?></h3>
<table class="widefat" id="elenco-categorie"> 
    <thead>
    	<tr>
        	<th scope="col" style="text-align:center;"><?php _e("Categorie","albo-online");?></th>
		</tr>
    </thead>
    <tbody id="the-list">
<?php 
$lista=ap_get_categorie_gerarchica(); 
echo '<tr>
        	<td>
			<ul>';
if ($lista){
	foreach($lista as $riga){
	 $shift=(((int)$riga[2])*30)+5;
	 echo'<li style="text-align:left;padding-left:'.$shift.'px;">';
	 $Tab=0;
	 $Testo_da=__("Confermi la cancellazione della Categoria","albo-online")." ".stripslashes($riga[1]). "?\n\n".__("Sei sicuro di voler proseguire con la CANCELLAZIONE?","albo-online");
 	 if (ap_num_atti_categoria($riga[0])==0)
		echo'<span class="cancella">
			<a href="?page=categorie&amp;action=delete-categorie&amp;id='.$riga[0].'&amp;canccategoria='.wp_create_nonce('delcategoria').'" rel="'.$Testo_da.'" class="confdel">			
			<span class="dashicons dashicons-trash" title="'.__("Cancella categoria","albo-online").'"></span>
		</a></span>
';
	 else
		$Tab=23;
	 echo'					
			<a href="?page=categorie&amp;action=edit-categorie&amp;id='.$riga[0].'&amp;modcategoria='.wp_create_nonce('editcategoria').'" rel="'.$riga[1].'">
			<span class="dashicons dashicons-edit" title="'.__("Modifica categoria","albo-online").'" style="margin-left:'.$Tab.'px;"></span>
			</a>
			('.$riga[0] .') '.$riga[1] .' (n&ordm; atti '.ap_num_atti_categoria($riga[0]).')
			</li>'; 
	}
} else {
		echo '<li>'.__("Nessuna Categoria Codificata","albo-online").'</li>';
}
echo '</ul>
		</td>
	 </tr>
      </tbody>
	</table>
</div>
<div class="col-wrap">
<h3>Log</h3>';
$righe=ap_get_all_Oggetto_log(2);
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
<div class="form-wrap">
	<form id="addtag" method="post" action="?page=categorie" class="<?php if($edit) echo "edit"; else echo "validate"; ?>"  >
		<input type="hidden" name="action" value="<?php if($edit) echo "memo-categoria"; else echo "add-categorie"; ?>"/>
		<input type="hidden" name="id" value="<?php echo (int)(isset($_REQUEST['id'])?$_REQUEST['id']:0); ?>" />
		<input type="hidden" name="categoria" value="<?php echo wp_create_nonce('categoria')?>" />

		<div class="form-field form-required">
			<label for="tag-name"><?php _e("Nome","albo-online");?> <span style="color:red;font-weight: bold;">*</span></label>
			<input name="cat-name" id="<?php _e("Nome","albo-online");?>" type="text" value="<?php if($edit) echo stripslashes($risultato[0]->Nome); ?>" size="40" class="richiesto"/>
			<p><?php _e("Nome della categoria.","albo-online");?></p>
		</div>
		<div class="form-field">
			<label for="parent"><?php _e("Parente di","albo-online");?>:</label>
			<?php 
			if($edit){
				echo ap_get_dropdown_categorie('cat-parente','cat-parente','','',$risultato[0]->Genitore);
			}else{
				echo ap_get_dropdown_categorie('cat-parente','cat-parente','postform','',0); 
			}
			?>
			<p><?php _e("Se si sta creando una sottocategoria, selezionare il genitore. Questo sistema permette di creare una struttura gerarchica di categorie.","albo-online");?></p>
		</div>
		<div class="form-field">
			<label for="tag-description"><?php _e("Descrizione","albo-online");?></label>
			<textarea name="cat-descrizione" id="cat-descrizione" rows="5" cols="40"><?php if($edit) echo stripslashes($risultato[0]->Descrizione); ?></textarea>
			<p><?php _e("Breve descrizione della categoria","albo-online");?></p>
		</div>
		<div class="form-field  form-required">
			<label for="tag-durata"><?php _e("Durata","albo-online");?> <span style="color:red;font-weight: bold;">*</span></label>
			<input name="cat-durata" id="<?php _e("Durata","albo-online");?>" type="text" value="<?php if($edit) echo $risultato[0]->Giorni; else echo "0"; ?>" size="4" alt="Durata Atto" class="richiesto ValValue(>0)"/>
			<p><?php _e("Durata di default, espressa in giorni, di validità degli atti di questa categoria","albo-online");?></p>
		</div>

<?php
if($edit) {
	echo '<input type="submit" name="SaveData" id="SaveData" class="button" value="'. __("Memorizza Modifiche Categoria","albo-online").' '.$risultato[0]->Nome.'" rel="'.stripslashes($risultato[0]->Nome).'" />';
}else{
	echo '<input type="submit" name="SaveData" id="SaveData" class="button" value="'. __("Aggiungi nuova Categoria","albo-online").'"  />';	
}
?>
	</form>
</div>
</div><!-- /col-container -->
</div><!-- /wrap -->


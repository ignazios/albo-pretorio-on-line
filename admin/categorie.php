<?php
/**
 * Gestione Categorie.
 * @link       http://www.eduva.org
 * @since      4.2
 *
 * @package    ALbo On Line
 */
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

$messages[1] = __('Item added.');
$messages[2] = __('Item deleted.');
$messages[3] = __('Item updated.');
$messages[4] = __('Item not added.');
$messages[5] = __('Item not updated.');
$messages[6] = __('Item not deleted.');
$messages[7] = __('Impossibile cancellare Categorie che contengono Categorie Figlio. Cancellare prima i Figli');
$messages[8] = __('Impossibile cancellare Categorie che sono collegate ad Atti');
$messages[9] = __('Bisogna assegnare il nome alla nuova categoria');
$messages[80] = 'ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l\'operazione &egrave; stata annullata';
?>
<div id="errori" title="Validazione Dati" style="display:none">
  <h3>Lista Campi con Errori:</h3><p id="ElencoCampiConErrori"></p><p style='color:red;font-weight: bold;'>Correggere gli errori per continuare</p>
</div>
<div class="wrap nosubsub">
	<div class="HeadPage">
		<h2 class="wp-heading-inline"><span class="dashicons dashicons-category"></span> Categorie Atti
		<a href="?page=categorie" class="add-new-h2">Aggiungi nuovo</a></h2>
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
<h3>Elenco Categorie codificate</h3>
<table class="widefat" id="elenco-categorie"> 
    <thead>
    	<tr>
        	<th scope="col" style="text-align:center;">Categorie</th>
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
	 	if (ap_num_atti_categoria($riga[0])==0)
			echo'<span class="cancella">
				<a href="?page=categorie&amp;action=delete-categorie&amp;id='.$riga[0].'&amp;canccategoria='.wp_create_nonce('delcategoria').'" rel="'.$riga[1].'" class="dc">			
				<span class="dashicons dashicons-trash" title="Cancella categoria"></span>
			</a></span>
	';
		else
			$Tab=23;
		echo'					
			<a href="?page=categorie&amp;action=edit-categorie&amp;id='.$riga[0].'&amp;modcategoria='.wp_create_nonce('editcategoria').'" rel="'.$riga[1].'">
			<span class="dashicons dashicons-edit" title="Modifica categoria" style="margin-left:'.$Tab.'px;"></span>
			</a>
			('.$riga[0] .') '.$riga[1] .' (n&ordm; atti '.ap_num_atti_categoria($riga[0]).')
			</li>'; 
	}
} else {
		echo '<li>Nessuna Categoria Codificata</li>';
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
	<div class="Obbligatori">
		<span style="color:red;font-weight: bold;">*</span> i campi contrassegnati dall'asterisco sono <strong>obbligatori</strong>
	</div>
<div class="form-wrap">
<form id="addtag" method="post" action="?page=categorie" class="<?php if($edit) echo "edit"; else echo "validate"; ?>"  >
	<input type="hidden" name="action" value="<?php if($edit) echo "memo-categoria"; else echo "add-categorie"; ?>"/>
<input type="hidden" name="id" value="<?php echo (int)(isset($_REQUEST['id'])?$_REQUEST['id']:0); ?>" />
	<input type="hidden" name="categoria" value="<?php echo wp_create_nonce('categoria')?>" />

<div class="form-field form-required">
	<label for="tag-name">Nome <span style="color:red;font-weight: bold;">*</span></label>
	<input name="cat-name" id="cat-name" type="text" value="<?php if($edit) echo stripslashes($risultato[0]->Nome); ?>" size="40" alt="Nome Categoria" class="richiesto"/>
	<p>Nome della categoria.</p>
</div>
<div class="form-field">
	<label for="parent">Parente di:</label>
	<?php 
	if($edit){
		echo ap_get_dropdown_categorie('cat-parente','cat-parente','','',$risultato[0]->Genitore);
	}else{
		echo ap_get_dropdown_categorie('cat-parente','cat-parente','postform','',0); 
	}
	?>
	<p>Se si sta creando una sottocategoria, selezionare il genitore. Questo sistema permette di creare una struttura gerarchica di categorie.</p>
</div>
<div class="form-field">
	<label for="tag-description">Descrizione</label>
	<textarea name="cat-descrizione" id="cat-descrizione" rows="5" cols="40"><?php if($edit) echo stripslashes($risultato[0]->Descrizione); ?></textarea>
	<p>Breve descrizione della categoria</p>
</div>
<div class="form-field  form-required">
	<label for="tag-durata">Durata <span style="color:red;font-weight: bold;">*</span></label>
	<input name="cat-durata" id="cat-durata" type="text" value="<?php if($edit) echo $risultato[0]->Giorni; else echo "0"; ?>" size="4" alt="Durata Atto" class="richiesto ValValue(>0)"/>
	<p>Durata di default, espressa in giorni, di validit&agrave; degli atti di questa categoria</p>
</div>

<?php
if($edit) {
	echo '<input type="submit" name="SaveData" id="SaveData" class="button" value="Memorizza Modifiche Categoria '.$risultato[0]->Nome.'" rel="'.stripslashes($risultato[0]->Nome).'" />';
}else{
	echo '<input type="submit" name="SaveData" id="SaveData" class="button" value="Aggiungi nuova Categoria"  />';	
}
?>
</form>
</div>
</div><!-- /col-container -->
</div><!-- /wrap -->


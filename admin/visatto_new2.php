<?php
/**
 * Gestione FrontEnd.
 * @link       http://www.eduva.org
 * @since      4.5.6
 *
 * @package    Albo On Line
 */

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

function Visualizza_Atto($Parametri){
	ob_start();
	if(isset($_GET["titolo"])){
		$Titolo=$_GET["titolo"];
	}else{
		if (isset($Parametri['titolo'])){
			$Titolo=$Parametri['titolo'];	
		}
	}
	if (isset($Parametri['numero']) And is_numeric($Parametri['numero'])){
		$Numero=$Parametri['numero'];	
	}else{
		if(isset($_GET["numero"]) And is_numeric($_GET["numero"])){
			$Numero=$_GET["numero"];
		}else{
			echo "Parametro Numero Atto non impostato";
			return ob_get_clean();		
		}
	}
	if (isset($Parametri['anno']) And is_numeric($Parametri['anno'])){
		$Anno=$Parametri['anno'];	
	}else{
		if(isset($_GET["anno"]) And is_numeric($_GET["anno"])){
			$Anno=$_GET["anno"];
		}else{
			echo "Parametro Anno Atto non impostato";
			return ob_get_clean();
		}
	}
	$risultato=ap_get_all_atti(0,$Numero,$Anno);
	if(count($risultato)==0){
		echo "Nessun atto trovato con questi parametri";
		return ob_get_clean();
	}
	$risultato=$risultato[0];
	$id=$risultato->IdAtto;
	$risultatocategoria=ap_get_categoria($risultato->IdCategoria);
	$risultatocategoria=$risultatocategoria[0];
	$Unitao=ap_get_unitaorganizzativa($risultato->IdUnitaOrganizzativa);
	$NomeResp=ap_get_responsabile($risultato->RespProc);
	if (count($NomeResp)>0)
		$NomeResp=$NomeResp[0];
	else
		$NomeResp="";
	$allegati=ap_get_all_allegati_atto($id);
	ap_insert_log(5,5,$id,"Visualizzazione");
	$coloreAnnullati=get_option('opt_AP_ColoreAnnullati');
	if($risultato->DataAnnullamento!='0000-00-00')
		$Annullato='<p style="background-color: '.$coloreAnnullati.';text-align:center;font-size:1.5em;">'.sprintf(__('Atto Annullato dal Responsabile del Procedimento %s Motivo: %s','albo-online'),'<br /><br />','<span style="font-size:1;font-style: italic;">'.stripslashes($risultato->MotivoAnnullamento).'</span>');
	else
		$Annullato='';
?>
<section  id="DatiAtto">
	<div class="container clearfix mb-3 pb-3">
		<button class="btn btn-primary" onclick="window.location.href='<?php echo $_SERVER['HTTP_REFERER'];?>'"><span class="fas fa-arrow-circle-left"></span> <?php _e("Torna alla Lista","albo-online");?></button>
		<h2 class="u-text-h2 pt-3 pl-2"><?php echo $Titolo;?></h2>
		<?php echo ($Annullato?"<h3>".$Annullato."</h3>":"");?>
	   	<div class="row">
	   		<div class="col-12">
				<table class="table table-striped table-hove">
				    <tbody id="dati-atto">
					<tr>
						<th class="w-25 text-right"><?php _e("Ente titolare dell'Atto","albo-online");?></th>
						<td class="align-middle"><?php echo stripslashes(ap_get_ente($risultato->Ente)->Nome);?></td>
					</tr>
					<tr>
						<th class="w-25 text-right"><?php _e("Numero Albo","albo-online");?></th>
						<td class="align-middle"><?php echo $risultato->Numero."/".$risultato->Anno;?></td>
					</tr>
					<tr>
						<th class="w-25 text-right"><?php _e("Codice di Riferimento","albo-online");?></th>
						<td class="align-middle"><?php echo stripslashes($risultato->Riferimento);?></td>
					</tr>
					<tr>
						<th class="w-25 text-right"><?php _e("Oggetto","albo-online");?></th>
						<td class="align-middle"><?php echo stripslashes($risultato->Oggetto);?></td>
					</tr>
					<tr>
						<th class="w-25 text-right"><?php _e("Data di registrazione","albo-online");?></th>
						<td class="align-middle"><?php echo ap_VisualizzaData($risultato->Data);?></td>
					</tr>
					<tr>
						<th class="w-25 text-right"><?php _e("Data inizio Pubblicazione","albo-online");?></th>
						<td class="align-middle"><?php echo ap_VisualizzaData($risultato->DataInizio);?></td>
					</tr>
					<tr>
						<th class="w-25 text-right"><?php _e("Data fine Pubblicazione","albo-online");?></th>
						<td class="align-middle"><?php echo ap_VisualizzaData($risultato->DataFine)?></td>
					</tr>
					<tr>
						<th class="w-25 text-right"><?php _e("Data oblio","albo-online");?></th>
						<td class="align-middle"><?php echo ap_VisualizzaData($risultato->DataOblio);?></td>
					</tr>
					<tr>
						<th class="w-25 text-right"><?php _e("Richiedente","albo-online");?></th>
						<td class="align-middle"><?php echo stripslashes($risultato->Richiedente);?></td>
					</tr>
					<tr>
						<th class="w-25 text-right"><?php _e("Unità Organizzativa Responsabile","albo-online");?></th>
						<td class="align-middle"><?php echo (isset($Unitao->Nome)?stripslashes($Unitao->Nome):"");?></td>
					</tr>
					<tr>
						<th class="w-25 text-right"><?php _e("Responsabile del procedimento amministrativo","albo-online");?></th>
						<td class="align-middle"><?php echo (is_object($NomeResp)?$NomeResp->Nome." ".$NomeResp->Cognome:$NomeResp);?></td>
					</tr>
					<tr>
						<th class="w-25 text-right"><?php _e("Categoria","albo-online");?></th>
						<td class="align-middle"><?php echo stripslashes($risultatocategoria->Nome)?></td>
					</tr>
<?php
$MetaDati=ap_get_meta_atto($id);
if($MetaDati!==FALSE){
	$Meta="";
	foreach($MetaDati as $Metadato){
		$Meta.="{".$Metadato->Meta."=".$Metadato->Value."} - ";
	}
	$Meta=substr($Meta,0,-3);?>
					<tr>
						<th class="w-25 text-right"><?php _e("Meta Dati","albo-online");?></th>
						<td style="vertical-align: middle;"><?php echo $Meta;?></td>
					</tr>
<?php }?>
					<tr>
						<th class="w-25 text-right"><?php _e("Note","albo-online");?></th>
						<td class="align-middle"><?php echo stripslashes($risultato->Informazioni);?></td>
					</tr>
		 	    </tbody>
			</table>
		</div>
<?php 		
$Soggetti=unserialize($risultato->Soggetti);
if(count($Soggetti)>0){
	$Soggetti=ap_get_alcuni_soggetti_ruolo(implode(",",$Soggetti));
	$Ruolo="";
	if($Soggetti){
				echo "<div class=\"col-8 ml-5 pl-5\">
	<h3 class=\"u-text-h2 pt-3 pl-2\">". __("Soggetti","albo-online")."</h3>";
	}
	foreach($Soggetti as $Soggetto){
		if(ap_get_Funzione_Responsabile($Soggetto->Funzione,"Display")=="No"){
			continue;
		}
		if($Soggetto->Funzione!=$Ruolo){
				$Ruolo=$Soggetto->Funzione;?>
				<div class="callout mycallout">
	  				<div class="callout-title"><?php echo ap_get_Funzione_Responsabile($Soggetto->Funzione,"Descrizione"); ?></div>
	 				<div>
						<?php echo $Soggetto->Cognome." ".$Soggetto->Nome;?><br />
	<?php	} 
		if ($Soggetto->Email)
			echo __("Email","albo-online").' <a href="mailto:'.$Soggetto->Email.'">'.$Soggetto->Email.'</a><br />';
		if ($Soggetto->Telefono)
			echo __("Telefono","albo-online")." ".$Soggetto->Telefono."<br />";
		if ($Soggetto->Orario)
			echo 	__("Orario ricevimento","albo-online")." ".$Soggetto->Orario.'<br />';
		if ($Soggetto->Note)
			echo __("Note","albo-online")." ".$Soggetto->Note;
	?>
					</div>	
			</div>
<?php	}?>
		</div>
<?php }?>
	</div>
<?php
$UrlSprite=get_option('opt_AP_UrlSprite');	   	
$TipidiFiles=ap_get_tipidifiles();
if (strpos(get_permalink(),"?")>0)
	$sep="&amp;";
else
	$sep="?";
$documenti=ap_get_documenti_atto($id);
if(count($documenti)>0){?>
	<div class="row">
	   	<div class="col">
			<h3 class="u-text-h2 pt-3 pb-2"><?php _e("Documenti firmati","albo-online");?></h3>
<?php
foreach ($documenti as $allegato) {
	$Estensione=ap_ExtensionType($allegato->Allegato);?>
			<div class="row border-dashed border-primary mb-1">
				<div class="col-1 icona-comunicazione">
<?php
	if(isset($allegato->TipoFile) and $allegato->TipoFile!="" and ap_isExtensioType($allegato->TipoFile)){
		$Estensione=ap_ExtensionType($allegato->TipoFile);
		echo '<img src="'.$TipidiFiles[$Estensione]['Icona'].'" alt="'.$TipidiFiles[$Estensione]['Descrizione'].'" height="30" width="30"/>';
	}else{
		echo '<img src="'.$TipidiFiles[strtolower($Estensione)]['Icona'].'" alt="'.$TipidiFiles[strtolower($Estensione)]['Descrizione'].'" height="30" width="30"allegato/>';
	}?>
				</div>
				<div class="col-11">  				
				<?php echo ($allegato->DocIntegrale!="1"?'<span class="evidenziato">'.__("Pubblicato per Estratto","albo-online")."</span><br />":"").'<strong>'.__("Descrizione","albo-online").'</strong>: '.strip_tags(($allegato->TitoloAllegato?$allegato->TitoloAllegato:basename( $allegato->Allegato))).'</strong><br /><strong>'.__("Impronta","albo-online").'</strong>: '.$allegato->Impronta.'<br /><strong>'.__("Dimensione file","albo-online").'</strong>: '.ap_Formato_Dimensione_File(is_file($allegato->Allegato)?filesize($allegato->Allegato):0)."<br /><br />";
						if (is_file($allegato->Allegato))
							echo '<a href="'.ap_DaPath_a_URL($allegato->Allegato).'" class="addstatdw noUnderLine" rel="'.get_permalink().$sep.'action=addstatall&amp;id='.$allegato->IdAllegato.'&amp;idAtto='.$id.'" title="'.__("Visualizza Allegato","albo-online").'" target="_blank"><svg class="icon"><use xlink:href="'.$UrlSprite.'#it-zoom-in"></use></svg></a> '.htmlspecialchars_decode($TipidiFiles[strtolower($Estensione)]['Verifica']).' <a href="'.get_permalink().$sep.'action=dwnalle&amp;id='.$allegato->IdAllegato.'&amp;idAtto='.$id.'" class="noUnderLine" title="'.__("Scarica allegato","albo-online").'"><svg class="icon"><use xlink:href="'.$UrlSprite.'#it-download"></use></svg></a>';	
	else
		echo basename( $allegato->Allegato).' '.__("File non trovato, il file è stato cancellato o spostato!","albo-online");?>
				</div>
			</div>
<?php	}?>
		</div>
	</div>
<?php	}
$allegati=ap_get_allegati_atto($id);
if(count($allegati)>0) { ?>
	<div class="row">
	   	<div class="col">
			<h3 class="u-text-h2 pt-3 pb-2"><?php _e("Allegati","albo-online");?></h3>
<?php
foreach ($allegati as $allegato) {
	$Estensione=ap_ExtensionType($allegato->Allegato);?>
			<div class="row border-dashed border-primary mb-1">
				<div class="col-1 icona-comunicazione">
<?php
	if(isset($allegato->TipoFile) and $allegato->TipoFile!="" and ap_isExtensioType($allegato->TipoFile)){
		$Estensione=ap_ExtensionType($allegato->TipoFile);
		echo '<img src="'.$TipidiFiles[$Estensione]['Icona'].'" alt="'.$TipidiFiles[$Estensione]['Descrizione'].'" height="30" width="30"/>';
	}else{
		echo '<img src="'.$TipidiFiles[strtolower($Estensione)]['Icona'].'" alt="'.$TipidiFiles[strtolower($Estensione)]['Descrizione'].'" height="30" width="30"allegato/>';
	}?>
				</div>
				<div class="col-11 break-word">  				
				<?php echo ($allegato->DocIntegrale!="1"?'<span class="evidenziato">'.__("Pubblicato per Estratto","albo-online")."</span><br />":"").'<strong>'.__("Descrizione","albo-online").'</strong>: '.strip_tags(($allegato->TitoloAllegato?$allegato->TitoloAllegato:basename( $allegato->Allegato))).'</strong><br /><strong>'.__("Impronta","albo-online").'</strong>: '.$allegato->Impronta.'<br /><strong>'.__("Dimensione file","albo-online").'</strong>: '.ap_Formato_Dimensione_File(is_file($allegato->Allegato)?filesize($allegato->Allegato):0)."<br />";
						if (is_file($allegato->Allegato))
							echo '<a href="'.ap_DaPath_a_URL($allegato->Allegato).'" class="addstatdw noUnderLine" rel="'.get_permalink().$sep.'action=addstatall&amp;id='.$allegato->IdAllegato.'&amp;idAtto='.$id.'" title="'.__("Visualizza Allegato","albo-online").'" target="_blank"><svg class="icon"><use xlink:href="'.$UrlSprite.'#it-zoom-in"></use></svg></a> '.htmlspecialchars_decode($TipidiFiles[strtolower($Estensione)]['Verifica']).' <a href="'.get_permalink().$sep.'action=dwnalle&amp;id='.$allegato->IdAllegato.'&amp;idAtto='.$id.'" class="noUnderLine" title="'.__("Scarica allegato","albo-online").'"><svg class="icon"><use xlink:href="'.$UrlSprite.'#it-download"></use></svg></a>';	
	else
		echo basename( $allegato->Allegato).' '.__("File non trovato, il file è stato cancellato o spostato!","albo-online");?>
				</div>
			</div>
<?php	}?>
		</div>
	</div>
<?php	}?>
	</div>	
	<div class="alert alert-info" role="alert">
	    <h3 class="u-text-h3"><?php _e("Informazioni","albo-online");?></h3>
	    <p class="u-text-p"><?php _e("L'impronta dei files è calcolata con algoritmo SHA256 al momento dell'upload","albo-online");?></p>
	</div>
</section>
<?php
return ob_get_clean();
}
?>
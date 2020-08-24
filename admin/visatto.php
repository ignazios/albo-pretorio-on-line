<?php
/**
 * Gestione FrontEnd.
 * @link       http://www.eduva.org
 * @since      4.4.5
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
			echo __("Parametro Numero Atto non impostato","albo-online");
			return ob_get_clean();		
		}
	}
	if (isset($Parametri['anno']) And is_numeric($Parametri['anno'])){
		$Anno=$Parametri['anno'];	
	}else{
		if(isset($_GET["anno"]) And is_numeric($_GET["anno"])){
			$Anno=$_GET["anno"];
		}else{
			echo __("Parametro Anno Atto non impostato","albo-online");
			return ob_get_clean();
		}
	}
	$risultato=ap_get_all_atti(0,$Numero,$Anno);
	if(count($risultato)==0){
		echo __("Nessun atto trovato con questi parametri","albo-online");
		return ob_get_clean();
	}
	$risultato=$risultato[0];
	$id=$risultato->IdAtto;
	$risultatocategoria=ap_get_categoria($risultato->IdCategoria);
	$risultatocategoria=$risultatocategoria[0];
	$allegati=ap_get_all_allegati_atto($id);
	ap_insert_log(5,5,$id,"Visualizzazione");
	$coloreAnnullati=get_option('opt_AP_ColoreAnnullati');
	$Unitao=ap_get_unitaorganizzativa($risultato->IdUnitaOrganizzativa);
	$NomeResp=ap_get_responsabile($risultato->RespProc);
	$NomeResp=$NomeResp[0];
	if($risultato->DataAnnullamento!='0000-00-00')
		$Annullato=sprint_f(__('%sAtto Annullato dal Responsabile del Procedimento %s Motivo: %s','albo-online'),'<p style="background-color: '.$coloreAnnullati.';text-align:center;font-size:1.5em;">','<br /><br />','<span style="font-size:1;font-style: italic;">'.stripslashes($risultato->MotivoAnnullamento).'</span></p>');
	else
		$Annullato='';
	$Stato="Scaduto";
	if ($risultato->DataFine>date("Y-m-d"))
		$Stato=__("In corso di Validità","albo-online");
echo '
<div class="Visalbo">
<h3>'.$Titolo.'</h3>
<p>'.$Annullato.'</p>
<table class="tabVisalbo">
	    <tbody id="dati-atto">
	    <tr>
	    	<th>'.__("Stato Atto","albo-online").'</th>
	    	<td style="font-weght: bold;font-size: 1.5em;vertical-align: middle;">'.$Stato.'
	    	</td>
	    </tr>
		<tr>
			<th>'.__("Ente titolare dell'Atto","albo-online").'</th>
			<td style="font-weght: bold;font-size: 1.5em;vertical-align: middle;">'.stripslashes(ap_get_ente($risultato->Ente)->Nome).'</td>
		</tr>
		<tr>
			<th>'.__("Numero Albo","albo-online").'</th>
			<td style="vertical-align: middle;">'.$risultato->Numero."/".$risultato->Anno.'</td>
		</tr>
		<tr>
			<th>'.__("Codice di Riferimento","albo-online").'</th>
			<td style="vertical-align: middle;">'.stripslashes($risultato->Riferimento).'</td>
		</tr>
		<tr>
			<th>'.__("Oggetto","albo-online").'</th>
			<td style="vertical-align: middle;">'.stripslashes($risultato->Oggetto).'</td>
		</tr>
		<tr>
			<th>'.__("Data di registrazione","albo-online").'</th>
			<td style="vertical-align: middle;">'.ap_VisualizzaData($risultato->Data).'</td>
		</tr>
		<tr>
			<th>'.__("Data inizio Pubblicazione","albo-online").'</th>
			<td style="vertical-align: middle;">'.ap_VisualizzaData($risultato->DataInizio).'</td>
		</tr>
		<tr>
			<th>'.__("Data fine Pubblicazione","albo-online").'</th>
			<td style="vertical-align: middle;">'.ap_VisualizzaData($risultato->DataFine).'</td>
		</tr>
		<tr>
			<th>'.__("Data oblio","albo-online").'</th>
			<td style="vertical-align: middle;">'.ap_VisualizzaData($risultato->DataOblio).'</td>
		</tr>
		<tr>
			<th>'.__("Richiedente","albo-online").'</th>
			<td style="vertical-align: middle;">'.stripslashes($risultato->Richiedente).'</td>
		</tr>
		<tr>
			<th>'.__("Unità Organizzativa Responsabile","albo-online").'</th>
			<td style="vertical-align: middle;">'.stripslashes($Unitao->Nome).'</td>
		</tr>
		<tr>
			<th>'.__("Responsabile del procedimento amministrativo","albo-online").'</th>
			<td style="vertical-align: middle;">'.stripslashes($NomeResp->Nome." ".$NomeResp->Cognome).'</td>
		</tr>
		<tr>
			<th>'.__("Categoria","albo-online").'</th>
			<td style="vertical-align: middle;">'.stripslashes($risultatocategoria->Nome).'</td>
		</tr>';
$MetaDati=ap_get_meta_atto($id);
if($MetaDati!==FALSE){
	$Meta="";
	foreach($MetaDati as $Metadato){
		$Meta.="{".$Metadato->Meta."=".$Metadato->Value."} - ";
	}
	$Meta=substr($Meta,0,-3);
		echo'
				<tr>
					<th>'.__("Meta Dati","albo-online").'</th>
					<td style="vertical-align: middle;">'.$Meta.'</td>
				</tr>';
}
echo'		<tr>
				<th>'.__("Note","albo-online").'</th>
				<td style="vertical-align: middle;">'.stripslashes($risultato->Informazioni).'</td>
			</tr>
 	    </tbody>
	</table>';
$Soggetti=unserialize($risultato->Soggetti);
$Soggetti=ap_get_alcuni_soggetti_ruolo(implode(",",$Soggetti));
$Ruolo="";
if($Soggetti){
	echo "		<h3 style=\"text-align:center;\">".__("Soggetti","albo-online")."</h3>";
}
foreach($Soggetti as $Soggetto){
	if(ap_get_Funzione_Responsabile($Soggetto->Funzione,"Display")=="No"){
		continue;
	}
	if($Soggetto->Funzione!=$Ruolo And $Ruolo!=""){
		echo '</div>';
	}
	if($Soggetto->Funzione!=$Ruolo){
		echo '<h4>'.ap_get_Funzione_Responsabile($Soggetto->Funzione,"Descrizione").'</h4>
	<div class="Visallegato">';
	}
	$Ruolo=$Soggetto->Funzione;
	echo'		<table class="tabVisResp">
	    		<tbody>
				<tr>
					<th>'.__("Persona","albo-online").'</th>
					<td style="vertical-align: middle;">'.$Soggetto->Cognome." ".$Soggetto->Nome.'</td>
				</tr>';
	if ($Soggetto->Email)
	echo'		<tr>
					<th>'.__("Email","albo-online").'</th>
					<td style="vertical-align: middle;"><a href="mailto:'.$Soggetto->Email.'">'.$Soggetto->Email.'</a></td>
				</tr>';
	if ($Soggetto->Telefono)
	echo'			<tr>
					<th>'.__("Telefono","albo-online").'</th>
					<td style="vertical-align: middle;">'.$Soggetto->Telefono.'</td>
				</tr>';
	if ($Soggetto->Orario)
	echo'		<tr>
					<th>'.__("Orario ricevimento","albo-online").'</th>
					<td style="vertical-align: middle;">'.$Soggetto->Orario.'</td>
				</tr>';
	if ($Soggetto->Note)
	echo'
				<tr>
					<th>'.__("Note","albo-online").'</th>
					<td style="vertical-align: middle;">'.$Soggetto->Note.'</td>
				</tr>';
echo'
			    </tbody>
			</table>';
}
if($Ruolo!=""){
	echo '</div>';
}
$TipidiFiles=ap_get_tipidifiles();
if (strpos(get_permalink(),"?")>0)
	$sep="&amp;";
else
	$sep="?";
$documenti=ap_get_documenti_atto($id);
if(count($documenti)>0){
	echo '<div class="postbox" style="padding:0 10px 10px 10px;">
		<h3>'. __("Documenti firmati","albo-online").'</h3>';
	foreach ($documenti as $allegato) {
		$Estensione=ap_ExtensionType($allegato->Allegato);
		echo '<div class="Visallegato">
				<div class="Allegato">
					<img src="'.$TipidiFiles[strtolower($Estensione)]['Icona'].'" alt="'.$TipidiFiles[strtolower($Estensione)]['Descrizione'].'" height="30" width="30"allegato/>
				</div>
				<div>
					<p class="secondaColonna">'.($allegato->DocIntegrale!="1"?'<span class="evidenziato">'.__("Pubblicato per Estratto","albo-online")."</span><br />":"").'<strong>'.__("Descrizione","albo-online").'</strong>: '.strip_tags($allegato->TitoloAllegato).'<br /><strong>'.__("Impronta","albo-online").'</strong>: '.$allegato->Impronta.'<br />';
				if (is_file($allegato->Allegato))
					echo '        <a href="'.ap_DaPath_a_URL($allegato->Allegato).'" class="addstatdw" rel="'.get_permalink().$sep.'action=addstatall&amp;id='.$allegato->IdAllegato.'&amp;idAtto='.$id.'" target="_blank" title="'.__("Visualizza Allegato","albo-online").'">'. basename( $allegato->Allegato).'</a> ('.ap_Formato_Dimensione_File(is_file($allegato->Allegato)?filesize($allegato->Allegato):0).')<br />'.htmlspecialchars_decode($TipidiFiles[strtolower($Estensione)]['Verifica']).' <a href="'.get_permalink().$sep.'action=dwnalle&amp;id='.$allegato->IdAllegato.'&amp;idAtto='.$id.'" >'.__("Scarica allegato","albo-online").'</a>';
				else
					echo basename( $allegato->Allegato).' '.__("File non trovato, il file è stato cancellato o spostato!","albo-online");
		echo'				</p>
				</div>
			</div>
			';
		}
	echo '</div>';
}	
$allegati=ap_get_allegati_atto($id);
if(count($allegati)>0){
	echo '<div class="postbox" style="padding:0 10px 10px 10px;">
		<h3>'. __("Allegati","albo-online").'</h3>';
	foreach ($allegati as $allegato) {
		$Estensione=ap_ExtensionType($allegato->Allegato);
		echo '<div class="Visallegato">
				<div class="Allegato">
					<img src="'.$TipidiFiles[strtolower($Estensione)]['Icona'].'" alt="'.$TipidiFiles[strtolower($Estensione)]['Descrizione'].'" height="30" width="30"allegato/>
				</div>
				<div>
					<p class="secondaColonna">'.($allegato->DocIntegrale!="1"?'<span class="evidenziato">'.__("Pubblicato per Estratto","albo-online")."</span><br />":"").'<strong>'.__("Descrizione","albo-online").'</strong>: '.strip_tags($allegato->TitoloAllegato).'<br /><strong>'.__("Impronta","albo-online").'</strong>: '.$allegato->Impronta.'<br />';
				if (is_file($allegato->Allegato))
					echo '        <a href="'.ap_DaPath_a_URL($allegato->Allegato).'" class="addstatdw" rel="'.get_permalink().$sep.'action=addstatall&amp;id='.$allegato->IdAllegato.'&amp;idAtto='.$id.'" target="_blank" title="'.__("Visualizza Allegato","albo-online").'">'. basename( $allegato->Allegato).'</a> ('.ap_Formato_Dimensione_File(is_file($allegato->Allegato)?filesize($allegato->Allegato):0).')<br />'.htmlspecialchars_decode($TipidiFiles[strtolower($Estensione)]['Verifica']).' <a href="'.get_permalink().$sep.'action=dwnalle&amp;id='.$allegato->IdAllegato.'&amp;idAtto='.$id.'" >'.__("Scarica allegato","albo-online").'</a>';
				else
					echo basename( $allegato->Allegato).' '.__("File non trovato, il file è stato cancellato o spostato!","albo-online");
				echo'				</p>
				</div>
			</div>
			';
		}
	echo '</div>';
}	
echo '
	<div class="VisInfo">
	    <p class="text-1"><strong><span class="dashicons dashicons-info"></span> '.__("Informazioni","albo-online").'</strong>: '.__("L'impronta dei files è calcolata con algoritmo SHA256 al momento dell'upload","albo-online").'</p>
	</div>
</div>';	
return ob_get_clean();
}
?>
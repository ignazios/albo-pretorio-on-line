<?php
/**
 * Gestione FrontEnd.
 * @link       http://www.eduva.org
 * @since      4.3
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
	$allegati=ap_get_all_allegati_atto($id);
	$responsabile=ap_get_responsabile($risultato->RespProc);
	$responsabile=$responsabile[0];
	ap_insert_log(5,5,$id,"Visualizzazione");
	$coloreAnnullati=get_option('opt_AP_ColoreAnnullati');
	if($risultato->DataAnnullamento!='0000-00-00')
		$Annullato='<p style="background-color: '.$coloreAnnullati.';text-align:center;font-size:1.5em;">Atto Annullato dal Responsabile del Procedimento<br /><br />Motivo: <span style="font-size:1;font-style: italic;">'.stripslashes($risultato->MotivoAnnullamento).'</span></p>';
	else
		$Annullato='';
	$Stato="Scaduto";
	if ($risultato->DataFine>date("Y-m-d"))
		$Stato="In corso di Validità";
echo '
<div>
	<p style="margin-bottom:1.5em;">
	'.$Annullato.'
	</p>
	<h3>'.$Titolo.'</h3>
	<div class="Grid Grid--withGutter u-padding-all-l">
		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">Stato Atto</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs">'.$Stato.'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">Ente titolare dell\'Atto</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs">'.stripslashes(ap_get_ente($risultato->Ente)->Nome).'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">Numero Albo</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs">'.$risultato->Numero."/".$risultato->Anno.'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">Codice di Riferimento</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs">'.stripslashes($risultato->Riferimento).'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">Oggetto</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-margin-top-xs u-padding-left-m u-padding-right-m u-padding-top-xxs u-padding-bottom-s u-border-bottom-xxs">'.stripslashes($risultato->Oggetto).'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">Data inizio Pubblicazione</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs">'.ap_VisualizzaData($risultato->DataInizio).'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">Data fine Pubblicazione</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs">'.ap_VisualizzaData($risultato->DataFine).'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">Data oblio</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs">'.ap_VisualizzaData($risultato->DataOblio).'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xxs u-borderRadius-m u-padding-all-m">Note</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-margin-top-xs u-padding-left-m u-padding-right-m u-padding-top-xxs u-padding-bottom-s u-border-bottom-xxs">'.(strlen(stripslashes($risultato->Informazioni))>0?stripslashes($risultato->Informazioni):"&nbsp;&nbsp;").'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">Categoria</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs">'.stripslashes($risultatocategoria->Nome).'</div>
		</div>';
$MetaDati=ap_get_meta_atto($id);
if($MetaDati!==FALSE){
	$Meta="";
	foreach($MetaDati as $Metadato){
		$Meta.="{".$Metadato->Meta."=".$Metadato->Value."} - ";
	}
	$Meta=substr($Meta,0,-3);
		echo'	
		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">Meta Dati</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs">'.$Meta.'</div>
		</div>';
}
echo'</div>
</div>';
$Soggetti=unserialize($risultato->Soggetti);
$Soggetti=ap_get_alcuni_soggetti_ruolo(implode(",",$Soggetti));
$Ruolo="";
if($Soggetti){
	echo "		<h3 style=\"text-align:center;\">Soggetti</h3>";
}
foreach($Soggetti as $Soggetto){
	if(ap_get_Funzione_Responsabile($Soggetto->Funzione,"Display")=="No"){
		continue;
	}
	if($Soggetto->Funzione!=$Ruolo And $Ruolo!=""){
		echo '</div>';
	}
	if($Soggetto->Funzione!=$Ruolo){
		echo '<div>
	<h4>'.ap_get_Funzione_Responsabile($Soggetto->Funzione,"Descrizione").'</h4>
	<div class="Grid Grid--withGutter u-padding-all-l">';
	}
	$Ruolo=$Soggetto->Funzione;
	echo'		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">Persona</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs">'.$Soggetto->Cognome." ".$Soggetto->Nome.'</div>
		</div>';		
	if ($Soggetto->Email)
	echo'				<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">email</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs"><a href="mailto:'.$Soggetto->Email.'">'.$Soggetto->Email.'</a></div>
		</div>';
	if ($Soggetto->Telefono)
	echo'					<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">Telefono</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs">'.$Soggetto->Telefono.'</div>
		</div>		
		<tr>';
	if ($Soggetto->Orario)
	echo'				<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">Orario ricevimento</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs">'.$Soggetto->Orario.'</div>
		</div>
		<tr>';
	if ($Soggetto->Note)
	echo'		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">Note</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs">'.$Soggetto->Note.'</div>
		</div>';
echo'</div>';
}
if($Ruolo!=""){
	echo '</div>';
}

echo '
<div>
	<h3>Allegati</h3>
		<div class="Grid Grid--withGutter u-padding-all-l">';
//print_r($_SERVER);
$TipidiFiles=ap_get_tipidifiles();
foreach ($allegati as $allegato) {
	$Estensione=ap_ExtensionType($allegato->Allegato);
	echo'
		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAllegati">
			<div class="u-margin-bottom-xs u-borderRadius-m u-padding-all-m u-border-all-xxs">';
		if(isset($allegato->TipoFile) and $allegato->TipoFile!="" and ap_isExtensioType($allegato->TipoFile)){
			$Estensione=ap_ExtensionType($allegato->TipoFile);
			echo '<img src="'.$TipidiFiles[$Estensione]['Icona'].'" alt="'.$TipidiFiles[$Estensione]['Descrizione'].'" height="30" width="30"/>';
		}else{
			echo '<img src="'.$TipidiFiles[strtolower($Estensione)]['Icona'].'" alt="'.$TipidiFiles[strtolower($Estensione)]['Descrizione'].'" height="30" width="30"allegato/>';
		}
	echo " ".strip_tags(($allegato->TitoloAllegato?$allegato->TitoloAllegato:basename( $allegato->Allegato))).'('.ap_Formato_Dimensione_File(filesize($allegato->Allegato)).')</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2 FunctionAllegati">
			<div class="u-margin-bottom-xs u-borderRadius-m u-padding-all-m">';
			if (strpos(get_permalink(),"?")>0)
				$sep="&amp;";
			else
				$sep="?";
			if (is_file($allegato->Allegato))
				echo '<a href="'.ap_DaPath_a_URL($allegato->Allegato).'" class="addstatdw noUnderLine" rel="'.get_permalink().$sep.'action=addstatall&amp;id='.$allegato->IdAllegato.'&amp;idAtto='.$id.'" title="Visualizza Allegato" target="_blank"><span class="u-text-r-l Icon Icon-zoom-in"></span></a> '.htmlspecialchars_decode($TipidiFiles[strtolower($Estensione)]['Verifica']).' <a href="'.get_permalink().$sep.'action=dwnalle&amp;id='.$allegato->IdAllegato.'&amp;idAtto='.$id.'" class="noUnderLine" title="Scarica Allegato"><span class="u-text-r-l Icon Icon-download"></span></a>';
				
			else
				echo basename( $allegato->Allegato)." File non trovato, il file &egrave; stato cancellato o spostato!";
	echo '</div>
		</div>';
	}
echo '</div>
</div>';
return ob_get_clean();
}
?>
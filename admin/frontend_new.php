<?php
/**
 * Gestione FrontEnd.
 * @link       http://www.eduva.org
 * @since      4.4.5
 *
 * @package    Albo On Line
 */

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

ob_start();

if(isset($_REQUEST['id']) And !is_numeric($_REQUEST['id'])){
	$_REQUEST['id']=0;
	echo "<br />".sprintf(__("%sATTENZIONE.%s E' stato indicato un VALORE non valido per il parametro %s","albo-online"),'<span style="color:red;">',"</span>",'<span style="color:red;">ID</span>');
}
if(isset($_REQUEST['action']) And $_REQUEST['action']!=wp_strip_all_tags($_REQUEST['action'])){
	unset($_REQUEST['action']);
	echo "<br />".sprintf(__("%sATTENZIONE.%s E' stato indicato un VALORE non valido per il parametro %s","albo-online"),'<span style="color:red;">',"</span>",'<span style="color:red;">Action</span>');
	return;
}
if(isset($_REQUEST['categoria']) And !is_numeric($_REQUEST['categoria'])){
	$_REQUEST['categoria']=0;
	echo "<br />".sprintf(__("%sATTENZIONE.%s E' stato indicato un VALORE non valido per il parametro %s","albo-online"),'<span style="color:red;">',"</span>",'<span style="color:red;">Categoria</span>');
}
if(isset($_REQUEST['numero']) And $_REQUEST['numero']!="" AND !is_numeric($_REQUEST['numero'])){
	$_REQUEST['numero']="";
	echo "<br />".sprintf(__("%sATTENZIONE.%s E' stato indicato un VALORE non valido per il parametro %s","albo-online"),'<span style="color:red;">',"</span>",'<span style="color:red;">Numero</span>');
}
if(isset($_REQUEST['anno']) And !is_numeric($_REQUEST['anno'])){
	$_REQUEST['anno']=0;
	echo "<br />".sprintf(__("%sATTENZIONE.%s E' stato indicato un VALORE non valido per il parametro %s","albo-online"),'<span style="color:red;">',"</span>",'<span style="color:red;">Anno</span>');
}
if(isset($_REQUEST['ente']) And !is_numeric($_REQUEST['ente'])){
	$_REQUEST['ente']="-1";
	echo "<br />".sprintf(__("%sATTENZIONE.%s E' stato indicato un VALORE non valido per il parametro %s","albo-online"),'<span style="color:red;">',"</span>",'<span style="color:red;">Ente</span>');
}
if(isset($_REQUEST['Pag']) And !is_numeric($_REQUEST['Pag'])){
	$_REQUEST['Pag']=1;
	echo "<br />".sprintf(__("%sATTENZIONE.%s E' stato indicato un VALORE non valido per il parametro %s","albo-online"),'<span style="color:red;">',"</span>",'<span style="color:red;">Pag</span>');
}
if(isset($_REQUEST['oggetto']) And $_REQUEST['oggetto']!=wp_strip_all_tags($_REQUEST['oggetto'])){
	$_REQUEST['oggetto']="";
	echo "<br />".sprintf(__("%sATTENZIONE.%s E' stato indicato un VALORE non valido per il parametro %s","albo-online"),'<span style="color:red;">',"</span>",'<span style="color:red;">Oggetto</span>');
}
if(isset($_REQUEST['riferimento']) And $_REQUEST['riferimento']!=wp_strip_all_tags($_REQUEST['riferimento'])){
	$_REQUEST['riferimento']="";
	echo "<br />".sprintf(__("%sATTENZIONE.%s E' stato indicato un VALORE non valido per il parametro %s","albo-online"),'<span style="color:red;">',"</span>",'<span style="color:red;">Riferimento</span>');
}
if(isset($_REQUEST['DataInizio']) And $_REQUEST['DataInizio']!=wp_strip_all_tags($_REQUEST['DataInizio'])){
	$_REQUEST['DataInizio']="";
	echo "<br />".sprintf(__("%sATTENZIONE.%s E' stato indicato un VALORE non valido per il parametro %s","albo-online"),'<span style="color:red;">',"</span>",'<span style="color:red;">Da Data</span>');
}
if(isset($_REQUEST['DataFine']) And $_REQUEST['DataFine']!=wp_strip_all_tags($_REQUEST['DataFine'])){
	$_REQUEST['DataFine']="";
	echo "<br />".sprintf(__("%sATTENZIONE.%s E' stato indicato un VALORE non valido per il parametro %s","albo-online"),'<span style="color:red;">',"</span>",'<span style="color:red;">A Data</span>');
}
if(isset($_REQUEST['filtra']) And ($_REQUEST['filtra']!=__("Filtra","albo-online") And $_REQUEST['filtra']!=__("Annulla Filtro","albo-online"))){
	$_REQUEST['filtra']="Filtra";
	echo "<br />".sprintf(__("%sATTENZIONE.%s E' stato indicato un VALORE non valido per il parametro %s","albo-online"),'<span style="color:red;">',"</span>",'<span style="color:red;">filtra</span>');
}
if(isset($_REQUEST['vf']) And ($_REQUEST['vf']!="s" And $_REQUEST['vf']!="h" And $_REQUEST['vf']!="undefined")){
	$_REQUEST['vf']="undefined";
	echo "<br />".sprintf(__("%sATTENZIONE.%s E' stato indicato un VALORE non valido per il parametro %s","albo-online"),'<span style="color:red;">',"</span>",'<span style="color:red;">vf</span>');
}
foreach($_REQUEST as $Key => $Val){
	$_REQUEST[$Key]=htmlspecialchars(wp_strip_all_tags($_REQUEST[$Key]));
}

include_once(dirname (__FILE__) .'/frontend_filtro_new.php');

if(isset($_REQUEST['action'])){
	switch ($_REQUEST['action']){
        case 'printatto':
            if (is_numeric($_REQUEST['id'])) {
                if ($_REQUEST['pdf'] == 'c') {
                    StampaAtto($_REQUEST['id'], 'c');
                } elseif ($_REQUEST['pdf'] == 'a') {
                    StampaAtto($_REQUEST['id'], 'a');
                }
            }else{
				echo sprintf(__("ATTENZIONE:%sE' stato indicato un parametro non valido che può rappresentare un ATTACCO INFORMATICO AL SITO","albo-online"),"<br />");
			}
            break;
		case 'visatto':
			if(is_numeric($_REQUEST['id']))
				$ret=VisualizzaAtto($_REQUEST['id']);
			else{
				echo sprintf(__("ATTENZIONE:%sE' stato indicato un parametro non valido che può rappresentare un ATTACCO INFORMATICO AL SITO","albo-online"),"<br />");
			}
			break;
		case 'addstatall':
			if(is_numeric($_GET['id']) and is_numeric($_GET['idAtto']))
				ap_insert_log(5,5,(int)$_GET['id'],"Visualizzazione",(int)$_GET['idAtto']);
			break;
		default: 
			if (isset($_REQUEST['filtra'])){
				if(!is_numeric($_REQUEST['categoria']) OR
				   !is_numeric($_REQUEST['numero']) OR
				   !is_numeric($_REQUEST['anno']) OR
				   !is_numeric($_REQUEST['ente'])){
						echo sprintf(__("ATTENZIONE:%sE' stato indicato un parametro non valido che può rappresentare un ATTACCO INFORMATICO AL SITO","albo-online"),"<br />");
						break;
				}
			if($_REQUEST['oggetto']!=wp_strip_all_tags($_REQUEST['oggetto'])){
				echo sprintf(__("ATTENZIONE:%sE' stato indicato un parametro non valido che può rappresentare un ATTACCO INFORMATICO AL SITO","albo-online"),"<br />");
				break;
			}
			if($_REQUEST['riferimento']!=wp_strip_all_tags($_REQUEST['riferimento'])){
				echo sprintf(__("ATTENZIONE:%sE' stato indicato un parametro non valido che può rappresentare un ATTACCO INFORMATICO AL SITO","albo-online"),"<br />");
				break;
			}
	 		$ret=Lista_Atti($Parametri,$_REQUEST['categoria'],(int)$_REQUEST['numero'],(int)$_REQUEST['anno'], htmlentities($_REQUEST['oggetto']),htmlentities($_REQUEST['DataInizio']),htmlentities($_REQUEST['DataFine']), htmlentities($_REQUEST['riferimento']),$_REQUEST['ente']);
			}else if(isset($_REQUEST['annullafiltro'])){
					 unset($_REQUEST['categoria']);
					 unset($_REQUEST['numero']);
					 unset($_REQUEST['anno']);
					 unset($_REQUEST['oggetto']);
					 unset($_REQUEST['riferimento']);
					 unset($_REQUEST['DataInizio']);
					 unset($_REQUEST['DataFine']);
					 unset($_REQUEST['ente']);
					 $ret=Lista_Atti($Parametri);
				}else{
					$ret=Lista_Atti($Parametri);
				}
		}	
	}else{
		if (isset($_REQUEST['filtra'])){
			if((isset($_REQUEST['categoria']) And !is_numeric($_REQUEST['categoria'])) OR
			   (isset($_REQUEST['numero']) And $_REQUEST['numero']!="" AND !is_numeric($_REQUEST['numero'])) OR
			   (isset($_REQUEST['anno']) And !is_numeric($_REQUEST['anno'])) OR
			   (isset($_REQUEST['ente']) And !is_numeric($_REQUEST['ente']))){
					echo sprintf(__("ATTENZIONE:%sE' stato indicato un parametro non valido che può rappresentare un ATTACCO INFORMATICO AL SITO","albo-online"),"<br />");
					return;
			}
			if($_REQUEST['oggetto']!=wp_strip_all_tags($_REQUEST['oggetto'])){
				echo sprintf(__("ATTENZIONE:%sE' stato indicato un parametro non valido che può rappresentare un ATTACCO INFORMATICO AL SITO","albo-online"),"<br />");
				return;
			}
			if($_REQUEST['riferimento']!=wp_strip_all_tags($_REQUEST['riferimento'])){
				echo sprintf(__("ATTENZIONE:%sE' stato indicato un parametro non valido che può rappresentare un ATTACCO INFORMATICO AL SITO","albo-online"),"<br />");
				return;
			}
			$ret=Lista_Atti($Parametri,(int)$_REQUEST['categoria'],(int)$_REQUEST['numero'],(int)$_REQUEST['anno'], htmlentities($_REQUEST['oggetto']),htmlentities($_REQUEST['DataInizio']),htmlentities($_REQUEST['DataFine']), htmlentities($_REQUEST['riferimento']),(int)$_REQUEST['ente']);			
		}else 
			if(isset($_REQUEST['annullafiltro'])){
				 unset($_REQUEST['categoria']);
				 unset($_REQUEST['numero']);
				 unset($_REQUEST['anno']);
				 unset($_REQUEST['oggetto']);
				 unset($_REQUEST['riferimento']);
				 unset($_REQUEST['DataInizio']);
				 unset($_REQUEST['ente']);
				 $ret=Lista_Atti($Parametri);
			}else{
				$ret=Lista_Atti($Parametri);
			}
	}
	
function VisualizzaAtto($id){
	$risultato=ap_get_atto($id);
	$risultato=$risultato[0];
	$risultatocategoria=ap_get_categoria($risultato->IdCategoria);
	$risultatocategoria=$risultatocategoria[0];
	$allegati=ap_get_all_allegati_atto($id);
	ap_insert_log(5,5,$id,"Visualizzazione");
	$coloreAnnullati=get_option('opt_AP_ColoreAnnullati');
	$Unitao=ap_get_unitaorganizzativa($risultato->IdUnitaOrganizzativa);
	$NomeResp=ap_get_responsabile($risultato->RespProc);
	$NomeResp=$NomeResp[0];
echo '
<div>
	<button class="h" onclick="window.location.href=\''.$_SERVER['HTTP_REFERER'].'\'"><span class="dashicons dashicons-controls-back"></span>'.__("Torna alla Lista","albo-online").'</button>
	<h3>'.__("Dati atto","albo-online").'</h3>
	<div class="Grid Grid--withGutter u-padding-all-l">';
	if($risultato->DataAnnullamento!='0000-00-00'){
		echo '<div class="Grid-cell u-padding-bottom-m" >
	<p style="text-align:center;font-size:1.5em;background-color: '.$coloreAnnullati.'">';
		echo sprintf(__('Atto Annullato dal Responsabile del Procedimento %s Motivo: %s','albo-online'),'<br /><br />','<span style="font-size:1;font-style: italic;">'.stripslashes($risultato->MotivoAnnullamento).'</span>');
		echo '    </p>
	</div>';
	}
echo '
		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">'.__("Ente titolare dell'Atto","albo-online").'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs">'.stripslashes(ap_get_ente($risultato->Ente)->Nome).'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">'.__("Numero Albo","albo-online").'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs">'.$risultato->Numero."/".$risultato->Anno.'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">'.__("Codice di Riferimento","albo-online").'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs">'.stripslashes($risultato->Riferimento).'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">'.__("Oggetto","albo-online").'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-margin-top-xs u-padding-left-m u-padding-right-m u-padding-top-xxs u-padding-bottom-s u-border-bottom-xxs">'.stripslashes($risultato->Oggetto).'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">'.__("Data di registrazione","albo-online").'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs">'.ap_VisualizzaData($risultato->Data).'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">'.__("Data inizio Pubblicazione","albo-online").'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs">'.ap_VisualizzaData($risultato->DataInizio).'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">'.__("Data fine Pubblicazione","albo-online").'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs">'.ap_VisualizzaData($risultato->DataFine).'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">'.__("Data oblio","albo-online").'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs">'.ap_VisualizzaData($risultato->DataOblio).'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">'.__("Richiedente","albo-online").'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs">'.stripslashes($risultato->Richiedente).'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">'.__("Unità Organizzativa Responsabile","albo-online").'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs">'.stripslashes($Unitao->Nome).'</div>
		</div>		
		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">'.__("Responsabile del procedimento amministrativo","albo-online").'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs">'.stripslashes($NomeResp->Nome." ".$NomeResp->Cognome).'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">'.__("Categoria","albo-online").'</div>
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
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">'.__("Meta Dati","albo-online").'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs">'.$Meta.'</div>
		</div>';
}
echo'		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
				<div class="u-background-50 u-color-white u-margin-bottom-xxs u-borderRadius-m u-padding-all-m">'.__("Note","albo-online").'</div>
			</div>
			<div class="Grid-cell u-size1of2 u-lg-size1of2">
				<div class="u-margin-bottom-xs u-margin-top-xs u-padding-left-m u-padding-right-m u-padding-top-xxs u-padding-bottom-s u-border-bottom-xxs">'.(strlen(stripslashes($risultato->Informazioni))>0?stripslashes($risultato->Informazioni):"&nbsp;&nbsp;").'</div>
			</div>
		</div>
</div>';
$Soggetti=unserialize($risultato->Soggetti);
$Ruolo="";
if($Soggetti){
	$Soggetti=ap_get_alcuni_soggetti_ruolo(implode(",",$Soggetti));
	echo "		<h3 style=\"text-align:center;\">".__("Soggetti","albo-online")."</h3>";
}else{
	$Soggetti=array();
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
	<h4>'.ap_get_Funzione_Responsabile($Soggetto->Funzione,"Descrizione").'</h4>';
	}
	$Ruolo=$Soggetto->Funzione;
	echo'<div class="Grid Grid--withGutter u-padding-all-l">		
		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">'.__("Persona","albo-online").'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs">'.$Soggetto->Cognome." ".$Soggetto->Nome.'</div>
		</div>';		
	if ($Soggetto->Email)
	echo'
		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">'.__("Email","albo-online").'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs"><a href="mailto:'.$Soggetto->Email.'">'.$Soggetto->Email.'</a></div>
		</div>';
	if ($Soggetto->Telefono)
	echo'
		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">'.__("Telefono","albo-online").'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs">'.$Soggetto->Telefono.'</div>
		</div>		
		<tr>';
	if ($Soggetto->Orario)
	echo'
		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">'.__("Orario ricevimento","albo-online").'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs">'.$Soggetto->Orario.'</div>
		</div>
		<tr>';
	if ($Soggetto->Note)
	echo'		
		<div class="Grid-cell u-size1of2 u-lg-size1of2 HeadAtto">
			<div class="u-background-50 u-color-white u-margin-bottom-xs u-borderRadius-m u-padding-all-m">'.__("Note","albo-online").'</div>
		</div>
		<div class="Grid-cell u-size1of2 u-lg-size1of2">
			<div class="u-margin-bottom-xs u-padding-all-m u-border-bottom-xxs">'.$Soggetto->Note.'</div>
		</div>';
	echo'</div>';
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
	echo '
		<h3>'. __("Documenti firmati","albo-online").'</h3>
			<div class="Grid Grid--withGutter u-padding-all-l">';
	//print_r($_SERVER);
	foreach ($documenti as $allegato) {
		$Estensione=ap_ExtensionType($allegato->Allegato);
		echo'
			<div class="Grid-cell HeadAllegati">
				<div class="u-margin-bottom-xs u-borderRadius-m u-padding-all-m u-border-all-xxs">
					<img src="'.$TipidiFiles[strtolower($Estensione)]['Icona'].'" alt="'.$TipidiFiles[strtolower($Estensione)]['Descrizione'].'" height="30" width="30"allegato/> '.($allegato->DocIntegrale!="1"?'<span class="evidenziato">'.__("Pubblicato per Estratto","albo-online")."</span><br />":"").'<strong>'.__("Descrizione","albo-online").'</strong>: '.strip_tags(($allegato->TitoloAllegato?$allegato->TitoloAllegato:basename( $allegato->Allegato))).'</strong><br /><strong>'.__("Impronta","albo-online").'</strong>: '.$allegato->Impronta.'<br /><strong>'.__("Dimensione file","albo-online").'</strong>: '.ap_Formato_Dimensione_File(is_file($allegato->Allegato)?filesize($allegato->Allegato):0)."<br /><br />";
				if (is_file($allegato->Allegato))
					echo '<a href="'.ap_DaPath_a_URL($allegato->Allegato).'" class="addstatdw noUnderLine" rel="'.get_permalink().$sep.'action=addstatall&amp;id='.$allegato->IdAllegato.'&amp;idAtto='.$id.'" title="'.__("Visualizza Allegato","albo-online").'" target="_blank"><span class="u-text-r-l Icon Icon-zoom-in"></span></a> '.htmlspecialchars_decode($TipidiFiles[strtolower($Estensione)]['Verifica']).' <a href="'.get_permalink().$sep.'action=dwnalle&amp;id='.$allegato->IdAllegato.'&amp;idAtto='.$id.'" class="noUnderLine" title="'.__("Scarica allegato","albo-online").'"><span class="u-text-r-l Icon Icon-download"></span></a>';	
				else
					echo basename( $allegato->Allegato).' '.__("File non trovato, il file è stato cancellato o spostato!","albo-online");
		echo '</div>
			</div>
		';
		}
	echo '</div>';
}
$allegati=ap_get_allegati_atto($id);
if(count($allegati)>0){
	echo '
		<h3>'. __("Allegati","albo-online").'</h3>
			<div class="Grid Grid--withGutter u-padding-all-l">';
	//print_r($_SERVER);
	foreach ($allegati as $allegato) {
		$Estensione=ap_ExtensionType($allegato->Allegato);
		echo'
			<div class="Grid-cell HeadAllegati">
				<div class="u-margin-bottom-xs u-borderRadius-m u-padding-all-m u-border-all-xxs">
					<img src="'.$TipidiFiles[strtolower($Estensione)]['Icona'].'" alt="'.$TipidiFiles[strtolower($Estensione)]['Descrizione'].'" height="30" width="30"allegato/> '.($allegato->DocIntegrale!="1"?'<span class="evidenziato">'.__("Pubblicato per Estratto","albo-online")."</span><br />":"").'<strong>'.__("Descrizione","albo-online").'</strong>: '.strip_tags(($allegato->TitoloAllegato?$allegato->TitoloAllegato:basename( $allegato->Allegato))).'</strong><br /><strong>'.__("Impronta","albo-online").'</strong>: '.$allegato->Impronta.'<br /><strong>'.__("Dimensione file","albo-online").'</strong>: '.ap_Formato_Dimensione_File(is_file($allegato->Allegato)?filesize($allegato->Allegato):0)."<br /><br />";
				if (is_file($allegato->Allegato))
					echo '<a href="'.ap_DaPath_a_URL($allegato->Allegato).'" class="addstatdw noUnderLine" rel="'.get_permalink().$sep.'action=addstatall&amp;id='.$allegato->IdAllegato.'&amp;idAtto='.$id.'" title="'.__("Visualizza Allegato","albo-online").'" target="_blank"><span class="u-text-r-l Icon Icon-zoom-in"></span></a> '.htmlspecialchars_decode($TipidiFiles[strtolower($Estensione)]['Verifica']).' <a href="'.get_permalink().$sep.'action=dwnalle&amp;id='.$allegato->IdAllegato.'&amp;idAtto='.$id.'" class="noUnderLine" title="'.__("Scarica allegato","albo-online").'"><span class="u-text-r-l Icon Icon-download"></span></a>';	
				else
					echo basename( $allegato->Allegato).' '.__("File non trovato, il file è stato cancellato o spostato!","albo-online");
		echo '</div>
			</div>
		';
		}
	echo '</div>
<div class="Prose Alert Alert--info Alert--withIcon" role="alert">
    <h3 class="u-text-h3">'.__("Informazioni","albo-online").'</h3>
    <p class="u-text-p">'.__("L'impronta dei files è calcolata con algoritmo SHA256 al momento dell'upload","albo-online").'</p>
</div>';
}
}

function Lista_Atti($Parametri,$Categoria=0,$Numero=0,$Anno=0,$Oggetto='',$Dadata=0,$Adata=0,$Riferimento='',$Ente=-1){
		switch ($Parametri['stato']){
			case 0:
				$TitoloAtti=__("Tutti gli atti","albo-online");
				break;
			case 1:
				$TitoloAtti=__("Atti in corso di Validità","albo-online");
				break;
			case 2:
				$TitoloAtti=__("Atti Scaduti","albo-online");
				break;
			case 3:
				$TitoloAtti=__("Atti da Pubblicare","albo-online");
				break;
	}
	if (isset($Parametri['per_page'])){
		$N_A_pp=$Parametri['per_page'];	
	}else{
		$N_A_pp=10;
	}
	if (isset($Parametri['cat']) and $Parametri['cat']!=0){
		$DesCategorie="";
		$Categoria="";
		$Categorie=explode(",",$Parametri['cat']);
		foreach($Categorie as $Cate){
			$DesCat=ap_get_categoria($Cate);
			$DesCategorie.=$DesCat[0]->Nome.",";
			$Categoria.=$Cate.",";
		}
		$DesCategorie= substr($DesCategorie,0, strlen($DesCategorie)-1);
		$TitoloAtti.=" Categorie ".$DesCategorie;
		$Categoria=substr($Categoria,0, strlen($Categoria)-1);
		$cat=1;
	}else{
		$Categorie=$Categoria;
		$cat=0;
	}
	if (!isset($_REQUEST['Pag'])){
		$Da=0;
		$A=$N_A_pp;
	}else{
		if(is_numeric($_REQUEST['Pag'])){
			$Da=($_REQUEST['Pag']-1)*$N_A_pp;
			$A=$N_A_pp;
		}else{
			echo sprintf(__("ATTENZIONE:%sE' stato indicato un parametro non valido che può rappresentare un ATTACCO INFORMATICO AL SITO","albo-online"),"<br />");
		}
	}
	if (!isset($_REQUEST['ente'])){
	         $Ente = '-1';
	}else{
	        $Ente = $_REQUEST['ente'];
	}
	$TotAtti=ap_get_all_atti($Parametri['stato'],$Numero,$Anno,$Categorie,$Oggetto,$Dadata,$Adata,'',0,0,true,false,$Riferimento,$Ente);
	$lista=ap_get_all_atti($Parametri['stato'],$Numero,$Anno,$Categorie,$Oggetto,$Dadata,$Adata,'Anno DESC,Numero DESC',$Da,$A,false,false,$Riferimento,$Ente); 
	$titEnte=get_option('opt_AP_LivelloTitoloEnte');
	if ($titEnte=='')
		$titEnte="h2";
	$titPagina=get_option('opt_AP_LivelloTitoloPagina');
	if ($titPagina=='')
		$titPagina="h3";
	$coloreAnnullati=get_option('opt_AP_ColoreAnnullati');
	$VisFiltro="";
	if(isset($Parametri['minfiltri']) And $Parametri['minfiltri']=="si"){
		if(isset($_REQUEST['vf']) and  $_REQUEST['vf']=="s"){
//			$VisFiltro='<img src="'.Albo_URL.'img/minimize.png" id="maxminfiltro" class="s" alt="icona minimizza finestra filtri"/>';
			$VisFiltro='<button id="maxminfiltro" class="s"><span class="dashicons dashicons-filter"></span> '.__("Chiudi Ricerca atti mediante filtri","albo-online").'</button>';
		}else{
//			$VisFiltro='<img src="'.Albo_URL.'img/maximize.png" id="maxminfiltro" class="h" alt="icona massimizza finestra filtri"/>';
			$VisFiltro='<button id="maxminfiltro" class="h"><span class="dashicons dashicons-filter"></span> '.__("Apri Ricerca atti mediante filtri","albo-online").'</button>';
		}
	}
echo ' <div class="Visalbo">
<a name="dati"></a> ';
if (get_option('opt_AP_VisualizzaEnte')=='Si')
		echo '<'.$titEnte.' ><span  class="titoloEnte">'.stripslashes(get_option('opt_AP_Ente')).'</span></'.$titEnte.'>';
echo'<'.$titPagina.'><span  class="titoloPagina">'.$TitoloAtti.'</span></'.$titPagina.'>';
if (!isset($Parametri['filtri']) Or $Parametri['filtri']=="si")
	echo'<h4 class="filtri">'.$VisFiltro.'</h4>'.VisualizzaRicerca($Parametri['stato'],$Categoria,$Parametri['minfiltri']);
//$Contenuto.=  $nascondi;
if ($TotAtti>$N_A_pp){
		$appo=$_REQUEST;
		unset($appo["Pag"]);
		unset($appo["vf"]);	
	    $Para=http_build_query($appo);
		if ($Para=='')
			$Para="?Pag=";
		else
			$Para="?".$Para."&amp;Pag=";
		$Npag=(int)($TotAtti/$N_A_pp);
		if ($TotAtti%$N_A_pp>0){
			$Npag++;
		}
		echo '<div style="float:right;" id="risultati">
	<nav role="navigation" aria-label="Navigazione paginata" class="u-layout-prose">
		<ul class="Grid Grid--fit u-text-r-xxs">';
		if (isset($_REQUEST['Pag']) And $_REQUEST['Pag']>1 ){
			$Pagcur=$_REQUEST['Pag'];
			$PagPre=$Pagcur-1;
			$PagSuc=$Pagcur+1;
			echo'	
				<li class="Grid-cell u-textCenter">
					<a href="'.$Para.'1" class="u-color-50 u-textClean u-block" title="'.__("Vai alla Prima pagina","albo-online").'">
						<span class="u-text-r-m">&laquo;</span>
					</a>
				</li>
				<li class="Grid-cell u-textCenter u-block">
					<a href="'.$Para.$PagPre.'" class="u-color-50 u-textClean u-block" title="'.__("Vai alla Pagina precedente","albo-online").'">
						<span class="u-text-r-m">&lsaquo;</span>
					</a>
				</li>';			
		}else{
			$Pagcur=$PagPre=1;
			$PagSuc=$Pagcur+1;
			echo'	
				<li class="Grid-cell u-textCenter u-block">
					<span class="u-text-r-m u-color-5">&laquo;</span>
				</li>
				<li class="Grid-cell u-textCenter u-block">
					<span class="u-color-5 u-textClean u-block">&lsaquo;</span>
				</li>';	

		}
		$DisCP=$Pagcur.'/'.$Npag;
		$MR=strlen($DisCP)*0.5;
		echo'	<li class="Grid-cell u-textCenter u-block" aria-hidden="true" style="margin-right:'.$MR.'em;">
				<span class="u-block u-color-black">
					<span class="u-text-r-s">'.$DisCP.'</span>
				</span>
			</li>
			<li class="Grid-cell u-textCenter u-block">';
		if($Pagcur==$Npag){
			echo'					
				<span class="u-text-r-m u-color-5">&rsaquo;</span>	
			</li>
			<li class="Grid-cell u-textCenter u-block">
					<span class="u-text-r-m u-color-5">&raquo;</span>
				</a>
			</li>';
		}else{
			$PagSuc=$Pagcur+1;
			echo'				 
					<a href="'.$Para.$PagSuc.'" class="u-color-50 u-textClean u-block" title="'.__("Vai alla Prima successiva","albo-online").'">
					<span class="u-text-r-m">&rsaquo;</span>
				</a>
			</li>
			<li class="Grid-cell u-textCenter u-block">
				<a href="'.$Para.$Npag.'" class="u-color-50 u-textClean u-block" title="'.__("Vai all'Ultima pagina","albo-online").'">
					<span class="u-text-r-m">&raquo;</span>
				</a>
			</li>';
		}
		echo'			</ul>
	</nav>
</div>';
	}	
$FEColsOption=get_option('opt_AP_ColonneFE',array(
									"Ente"=>0,
									"Riferimento"=>0,
									"Oggetto"=>0,
									"Validita"=>0,
									"Categoria"=>0,
									"Note"=>0,
									"RespProc"=>0,
									"DataOblio"=>0));
if(!is_array($FEColsOption)){
$FEColsOption=json_decode($FEColsOption,TRUE);
}	
echo '	<div class="tabalbo">                               
		<table id="elenco-atti" class="Table Table--withBorder u-text-r-xs js-TableResponsive tablesaw tablesaw-stack" data-tablesaw-mode="stack">	    <caption class="u-hiddenVisually">'.__("Atti","albo-online").'</caption>
		<thead>
	    	<tr class="u-border-bottom-xs">
	        	<th scope="col">'.__("Numero Atto","albo-online").'</th>';
foreach($FEColsOption as $Opzione => $Valore){
		if($Valore==1){
			echo '			<th scope="col">'.__(($Opzione=="Validita"?"Validità":$Opzione),"albo-online").'</th>';
		}
}
echo '	</tr>
	    </thead>
	    <tbody>';
	    $CeAnnullato=false;
	if ($lista){
	 	$pari=true;
		if (strpos(get_permalink(),"?")>0)
			$sep="&amp;";
		else
			$sep="?";
		foreach($lista as $riga){
			$Link='<a href="'.get_permalink().$sep.'action=visatto&amp;id='.$riga->IdAtto.'"  style="text-decoration: underline;">';
			$categoria=ap_get_categoria($riga->IdCategoria);
			$cat=$categoria[0]->Nome;
			$NumeroAtto=$riga->Numero;
			$classe='';
			if ($pari) 
				$classe='class="u-background-white"';
			if (!$pari)
				$classe='class="u-background-grey-10"';
			$pari=!$pari;
			if($riga->DataAnnullamento!='0000-00-00'){
				$classe='style="background-color: '.$coloreAnnullati.';"';
				$CeAnnullato=true;
			}
			echo '<tr >
			        <td '.$classe.'>'.$Link.$NumeroAtto.'/'.$riga->Anno .'</a> 
					</td>';
			if (isset($FEColsOption['Data']) And $FEColsOption['Data']==1)
				echo'
					<td '.$classe.'>
						'.$Link.ap_VisualizzaData($riga->Data) .'</a>
					</td>';
			if (isset($FEColsOption['Ente']) And $FEColsOption['Ente']==1)
				echo'
					<td '.$classe.'>
						'.$Link.$Link.stripslashes(ap_get_ente($riga->Ente)->Nome) .'</a>
					</td>';
			if (isset($FEColsOption['Riferimento']) And $FEColsOption['Riferimento']==1)
				echo'
					<td '.$classe.'>
						'.$Link.stripslashes($riga->Riferimento) .'</a>
					</td>';
			if (isset($FEColsOption['Oggetto']) And $FEColsOption['Oggetto']==1)
				echo '			
					<td '.$classe.'>
						'.$Link.stripslashes($riga->Oggetto) .'</a>
					</td>';
			if (isset($FEColsOption['Validita']) And $FEColsOption['Validita']==1)
				echo '								
					<td '.$classe.'>
						'.$Link.ap_VisualizzaData($riga->DataInizio) .'<br />'.ap_VisualizzaData($riga->DataFine) .'</a>  
					</td>';
			if (isset($FEColsOption['Categoria']) And $FEColsOption['Categoria']==1)
				echo'								
					<td '.$classe.'>
						'.$Link.$cat .'</a>  
					</td>';
			if (isset($FEColsOption['Note']) And $FEColsOption['Note']==1)
				echo'
					<td '.$classe.'>
						'.$Link.stripslashes($riga->Informazioni) .'</a>
					</td>';
			if (isset($FEColsOption['DataOblio']) And $FEColsOption['DataOblio']==1)
				echo'
					<td '.$classe.'>
						'.$Link.ap_VisualizzaData($riga->DataOblio) .'</a>
					</td>';
		echo'	
				</tr>'; 
			}
	} else {
			echo '<tr>
					<td colspan="6">'.__("Nessun Atto Codificato","albo-online").'</td>
				  </tr>';
	}
	echo '
     </tbody>
    </table>';
echo '</div>';
	if ($CeAnnullato) 
		echo '<p>'. sprintf(__('Le righe evidenziate con questo sfondo %s indicano Atti Annullati','albo-online'),' <span style="background-color: '.$coloreAnnullati.';">&nbsp;&nbsp;&nbsp;</span>').'</p>';
	echo '</div><!-- /wrap -->	';
	return ob_get_clean();
}
?>
<?php
/**
 * Gestione FrontEnd.
 * @link       http://www.eduva.org
 * @since      4.5.6
 *
 * @package    Albo On Line
 */

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }
ob_start();

if(isset($_REQUEST['id']) And !is_numeric($_REQUEST['id'])){
	$_REQUEST['id']=0;
	echo "<br />".sprintf(__("%sATTENZIONE.%s E' stato indicato un VALORE non valido per il parametro %s","albo-online"),'<span style="color:red;">',"</span>",'<span style="color:red;">ID</span>');
	return;
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
if(isset($_REQUEST['filtra']) And ($_REQUEST['filtra']!=__("Filtra","albo-online") And $_REQUEST['filtra']!=__("Annulla Filtro","albo-online"))){	$_REQUEST['filtra']='.__("Filtra","albo-online").';
	echo "<br />".sprintf(__("%sATTENZIONE.%s E' stato indicato un VALORE non valido per il parametro %s","albo-online"),'<span style="color:red;">',"</span>",'<span style="color:red;">filtra</span>');
}
if(isset($_REQUEST['vf']) And ($_REQUEST['vf']!="s" And $_REQUEST['vf']!="h" And $_REQUEST['vf']!="undefined")){
	$_REQUEST['vf']="undefined";
	echo "<br />".sprintf(__("%sATTENZIONE.%s E' stato indicato un VALORE non valido per il parametro %s","albo-online"),'<span style="color:red;">',"</span>",'<span style="color:red;">vf</span>');
}
foreach($_REQUEST as $Key => $Val){
	$_REQUEST[$Key]=htmlspecialchars(wp_strip_all_tags($_REQUEST[$Key]));
}

include_once(dirname (__FILE__) .'/frontend_filtro.php');

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
	$Unitao=ap_get_unitaorganizzativa($risultato->IdUnitaOrganizzativa);
	if (isset($Unitao))
		$UnitaoNome=$Unitao->Nome;
	else
		$UnitaoNome="";
	$NomeResp=ap_get_responsabile($risultato->RespProc);
	if (count($NomeResp)>0) {
		$NomeResp=$NomeResp[0];
		$NomeResp=stripslashes($NomeResp->Nome." ".$NomeResp->Cognome);
	}
	else
		$NomeResp="";
	ap_insert_log(5,5,$id,"Visualizzazione");
	$coloreAnnullati=get_option('opt_AP_ColoreAnnullati');
echo '
<div class="Visalbo">
	<button class="h" onclick="window.location.href=\''.$_SERVER['HTTP_REFERER'].'\'"><span class="dashicons dashicons-controls-back"></span>'.__("Torna alla Lista","albo-online").'</button> 
	<h3>'.__("Dati atto","albo-online").' </h3>';

	if($risultato->DataAnnullamento!='0000-00-00'){
		echo '<p style="text-align:center;font-size:1.5em;background-color: '.$coloreAnnullati.'">';
		echo sprintf(__('Atto Annullato dal Responsabile del Procedimento %s Motivo: %s','albo-online'),'<br /><br />','<span style="font-size:1;font-style: italic;">'.stripslashes($risultato->MotivoAnnullamento).'</span>');
		echo '</p>';
	}
echo '
	<table class="tabVisalbo">
	    <tbody id="dati-atto">
		<tr>
			<th>'.__("Ente titolare dell'Atto","albo-online").'</th>
			<td style="font-style: italic;font-size: 1.5em;vertical-align: middle;">'.stripslashes(ap_get_ente($risultato->Ente)->Nome).'</td>
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
			<td style="vertical-align: middle;">'.stripslashes($UnitaoNome).'</td>
		</tr>
		<tr>
			<th>'.__("Responsabile del procedimento amministrativo","albo-online").'</th>
			<td style="vertical-align: middle;">'.stripslashes($NomeResp).'</td>
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
		$Meta=substr($Meta,0,-3);
	}
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
	if ($Soggetto->Email){
		echo'		<tr>
					<th>'.__("Email","albo-online").'</th>
					<td style="vertical-align: middle;"><a href="mailto:'.$Soggetto->Email.'">'.$Soggetto->Email.'</a></td>
				</tr>';
	}
	if ($Soggetto->Telefono){
		echo'			<tr>
					<th>'.__("Telefono","albo-online").'</th>
					<td style="vertical-align: middle;">'.$Soggetto->Telefono.'</td>
				</tr>';
	}
	if ($Soggetto->Orario){
		echo'		<tr>
					<th>'.__("Orario ricevimento","albo-online").'</th>
					<td style="vertical-align: middle;">'.$Soggetto->Orario.'</td>
				</tr>';
	}
	if ($Soggetto->Note){
		echo'
				<tr>
					<th>'.__("Note","albo-online").'</th>
					<td style="vertical-align: middle;">'.$Soggetto->Note.'</td>
				</tr>';
	}
echo'
			    </tbody>
			</table>';
}
if($Ruolo!=""){
	echo '</div>';
}
$TipidiFiles=ap_get_tipidifiles();
if (strpos(get_permalink(),"?")>0){
	$sep="&amp;";
}else{
	$sep="?";
}
$documenti=ap_get_documenti_atto($id);
if(count($documenti)>0){
	echo '<div class="postbox break-word" style="padding:0 10px 10px 10px;">
		<h3>'. __("Documenti firmati","albo-online").'</h3>';
	foreach ($documenti as $allegato) {
		$Estensione=ap_ExtensionType($allegato->Allegato);
		echo '<div class="Visallegato">
				<div class="Allegato">
					<img src="'.$TipidiFiles[strtolower($Estensione)]['Icona'].'" alt="'.$TipidiFiles[strtolower($Estensione)]['Descrizione'].'" height="30" width="30"allegato/>
				</div>
				<div>';
		if(!is_file($allegato->Allegato) And $allegato->Note!=""){
			echo '<p class="secondaColonna">'.($allegato->DocIntegrale!="1"?'<span class="evidenziato">'.__("Pubblicato per Estratto","albo-online")."</span><br />":"").'<strong>'.__("Descrizione","albo-online").'</strong>: '.strip_tags($allegato->TitoloAllegato).'<br /><strong>'.__("Documento rimosso","albo-online").'</strong>: '.$allegato->Note.'<br />';
		}else{
			echo' 				
					<p class="secondaColonna">'.($allegato->DocIntegrale!="1"?'<span class="evidenziato">'.__("Pubblicato per Estratto","albo-online")."</span><br />":"").'<strong>'.__("Descrizione","albo-online").'</strong>: '.strip_tags($allegato->TitoloAllegato).'<br /><strong>'.__("Impronta","albo-online").'</strong>: '.$allegato->Impronta.'<br />';
				if (is_file($allegato->Allegato)){
					echo '        <a href="'.ap_DaPath_a_URL($allegato->Allegato).'" class="addstatdw" rel="'.get_permalink().$sep.'action=addstatall&amp;id='.$allegato->IdAllegato.'&amp;idAtto='.$id.'" target="_blank" title="'.__("Visualizza Allegato","albo-online").'">'. basename( $allegato->Allegato).'</a> ('.ap_Formato_Dimensione_File(is_file($allegato->Allegato)?filesize($allegato->Allegato):0).')<br />'.htmlspecialchars_decode($TipidiFiles[strtolower($Estensione)]['Verifica']).' <a href="'.get_permalink().$sep.'action=dwnalle&amp;id='.$allegato->IdAllegato.'&amp;idAtto='.$id.'" >'.__("Scarica allegato","albo-online").'</a>';
				}else{
					echo basename( $allegato->Allegato).' '.__("File non trovato, il file è stato cancellato o spostato!","albo-online");
				}
		}
		echo'				</p>
				</div>
			</div>
			';
		}
	echo '</div>';
}	
$allegati=ap_get_allegati_atto($id);
if(count($allegati)>0){
	echo '<div class="postbox break-word" style="padding:0 10px 10px 10px;">
		<h3>'. __("Allegati","albo-online").'</h3>';
	foreach ($allegati as $allegato) {
		$Estensione=ap_ExtensionType($allegato->Allegato);
		echo '<div class="Visallegato">
				<div class="Allegato">
					<img src="'.$TipidiFiles[strtolower($Estensione)]['Icona'].'" alt="'.$TipidiFiles[strtolower($Estensione)]['Descrizione'].'" height="30" width="30"allegato/>
				</div>
				<div>';
		if(!is_file($allegato->Allegato) And $allegato->Note!=""){
			echo '<p class="secondaColonna">'.($allegato->DocIntegrale!="1"?'<span class="evidenziato">'.__("Pubblicato per Estratto","albo-online")."</span><br />":"").'<strong>'.__("Descrizione","albo-online").'</strong>: '.strip_tags($allegato->TitoloAllegato).'<br /><strong>'.__("Allegato rimosso","albo-online").'</strong>: '.$allegato->Note.'<br />';
		}else{		
			echo '<p class="secondaColonna">'.($allegato->DocIntegrale!="1"?'<span class="evidenziato">'.__("Pubblicato per Estratto","albo-online")."</span><br />":"").'<strong>'.__("Descrizione","albo-online").'</strong>: '.strip_tags($allegato->TitoloAllegato).'<br /><strong>'.__("Impronta","albo-online").'</strong>: '.$allegato->Impronta.'<br />';
				if (is_file($allegato->Allegato))
					echo '        <a href="'.ap_DaPath_a_URL($allegato->Allegato).'" class="addstatdw" rel="'.get_permalink().$sep.'action=addstatall&amp;id='.$allegato->IdAllegato.'&amp;idAtto='.$id.'" target="_blank" title="'.__("Visualizza Allegato","albo-online").'">'. basename( $allegato->Allegato).'</a> ('.ap_Formato_Dimensione_File(is_file($allegato->Allegato)?filesize($allegato->Allegato):0).')<br />'.htmlspecialchars_decode($TipidiFiles[strtolower($Estensione)]['Verifica']).' <a href="'.get_permalink().$sep.'action=dwnalle&amp;id='.$allegato->IdAllegato.'&amp;idAtto='.$id.'" >'.__("Scarica allegato","albo-online").'</a>';
				else
					echo basename( $allegato->Allegato).' '.__("File non trovato, il file è stato cancellato o spostato!","albo-online");
		}
		echo'				</p>
				</div>
			</div>
			';
		}
	echo '</div>';
}	
echo '</div>
<div class="VisInfo">
    <p class="text-1"><strong><span class="dashicons dashicons-info"></span> '.__("Informazioni","albo-online").'</strong>: '.__("L'impronta dei files è calcolata con algoritmo SHA256 al momento dell'upload","albo-online").'</p>
</div>';
return ob_get_clean();
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
			return ob_get_clean();
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
	$titFiltri= get_option('opt_AP_LivelloTitoloFiltri');
	if ($titFiltri=='')
		$titFiltri="h4";
	$coloreAnnullati=get_option('opt_AP_ColoreAnnullati');
	$colorePari=get_option('opt_AP_ColorePari');
	$coloreDispari=get_option('opt_AP_ColoreDispari');
	$VisFiltro="";
	if(isset($Parametri['minfiltri']) And $Parametri['minfiltri']=="si"){
		if(isset($_REQUEST['vf']) and  $_REQUEST['vf']=="s"){
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
echo '<'.$titPagina.'><span  class="titoloPagina">'.$TitoloAtti.'</span></'.$titPagina.'>';
if (!isset($Parametri['filtri']) Or $Parametri['filtri']=="si")
	echo '<'.$titFiltri.' class="filtri">'.$VisFiltro.'</'.$titFiltri.'>'.VisualizzaRicerca($Parametri['stato'],$Categoria,$Parametri['minfiltri']);
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
		echo ' 
		<div class="tablenav" style="float:right;" id="risultati">
		<div class="tablenav-pages">
    		<p><strong>N. Atti '.$TotAtti.'</strong>&nbsp;&nbsp; Pagine';
    	if (isset($_REQUEST['Pag']) And $_REQUEST['Pag']>1 ){
			$Pagcur=$_REQUEST['Pag'];
			$PagPre=$Pagcur-1;
				echo '&nbsp;<a href="'.$Para.'1" class="page-numbers numero-pagina" title="Vai alla prima pagina">&laquo;</a>
&nbsp;<a href="'.$Para.$PagPre.'" class="page-numbers numero-pagina" title="'.__("Vai alla pagina precedente","albo-online").'">&lsaquo;</a> ';
		}else{
			$Pagcur=1;
			echo '&nbsp;<span class="page-numbers current" title="'.__("Sei già nella prima pagina","albo-online").'">&laquo;</span>
&nbsp;<span class="page-numbers current" title="Sei gi&agrave; nella prima pagina">&lsaquo;</span> ';
		}
		echo '&nbsp;<span class="page-numbers current">'.$Pagcur.'/'.$Npag.'</span>';
		$PagSuc=$Pagcur+1;
	   	if ($PagSuc<=$Npag){
			echo '&nbsp;<a href="'.$Para.$PagSuc.'" class="page-numbers numero-pagina" title="'.__("Vai alla pagina successiva","albo-online").'">&rsaquo;</a>
&nbsp;<a href="'.$Para.$Npag.'" class="page-numbers numero-pagina" title="'.__("Vai all'ultima pagina","albo-online").'">&raquo;</a>';
		}else{
			echo '&nbsp;<span class="page-numbers current" title="'.__("Se nell'ultima pagina non puoi andare oltre","albo-online").'">&rsaquo;</span>';			
		}
	echo '			</p>
    	</div>
	</div>';
	}	
$FEColsOption=get_option('opt_AP_ColonneFE',array(
									"Data"=>0,
									"Ente"=>0,
									"Riferimento"=>0,
									"Oggetto"=>0,
									"Validita"=>0,
									"Categoria"=>0,
									"Note"=>0,
									"RespProc"=>0,
									"DataOblio"=>0));
if(!is_array($FEColsOption)){
	$FEColsOption=shortcode_atts(array(
				"Data"=>0,
				"Ente"=>0,
				"Riferimento"=>0,
				"Oggetto"=>0,
				"Validita"=>0,
				"Categoria"=>0,
				"Note"=>0,
				"RespProc"=>0,
				"DataOblio"=>0), json_decode($FEColsOption,TRUE),"");
}	
echo '	<div class="tabalbo">                               
		<table id="elenco-atti-OldStyle" class="tabella-dati-albo" summary="'.__("atti validi per riferimento, oggetto e categoria","albo-online").'"> 
	    <caption>'.__("Atti","albo-online").'</caption>
		<thead>
	    	<tr>
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
			if ($pari And $coloreDispari) 
				$classe='style="background-color: '.$coloreDispari.';"';
			if (!$pari And $colorePari)
				$classe='style="background-color: '.$colorePari.';"';
			$pari=!$pari;
			if($riga->DataAnnullamento!='0000-00-00'){
				$classe='style="background-color: '.$coloreAnnullati.';"';
				$CeAnnullato=true;
			}
			echo '<tr >
			        <td '.$classe.'>'.$Link.$NumeroAtto.'/'.$riga->Anno .'</a> 
					</td>';
			if ($FEColsOption['Data']==1)
				echo '
					<td '.$classe.'>
						'.$Link.ap_VisualizzaData($riga->Data) .'</a>
					</td>';
			if ($FEColsOption['Ente']==1)
				echo '
					<td '.$classe.'>
						'.$Link.$Link.stripslashes(ap_get_ente($riga->Ente)->Nome) .'</a>
					</td>';
			if ($FEColsOption['Riferimento']==1)
				echo '
					<td '.$classe.'>
						'.$Link.stripslashes($riga->Riferimento) .'</a>
					</td>';
			if ($FEColsOption['Oggetto']==1)
				echo '			
					<td '.$classe.'>
						'.$Link.stripslashes($riga->Oggetto) .'</a>
					</td>';
			if ($FEColsOption['Validita']==1)
				echo '								
					<td '.$classe.'>
						'.$Link.ap_VisualizzaData($riga->DataInizio) .'<br />'.ap_VisualizzaData($riga->DataFine) .'</a>  
					</td>';
			if ($FEColsOption['Categoria']==1)
				echo '								
					<td '.$classe.'>
						'.$Link.$cat .'</a>  
					</td>';
			if ($FEColsOption['Note']==1)
				echo '
					<td '.$classe.'>
						'.$Link.stripslashes($riga->Informazioni) .'</a>
					</td>';
			if ($FEColsOption['DataOblio']==1)
				echo '
					<td '.$classe.'>
						'.$Link.ap_VisualizzaData($riga->DataOblio) .'</a>
					</td>';
		echo '	
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
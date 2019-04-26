<?php
/**
 * Gestione FrontEnd.
 * @link       http://www.eduva.org
 * @since      4.3
 *
 * @package    Albo On Line
 */

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

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
            }
            break;
		case 'visatto':
			if(is_numeric($_REQUEST['id']))
				VisualizzaAtto($_REQUEST['id']);
			else{
				$ret=Lista_Atti($Parametri);
			}
				
			break;
		case 'addstatall':
			if(is_numeric($_GET['id']) and is_numeric($_GET['idAtto']))
				ap_insert_log(6,5,(int)$_GET['id'],"Download",(int)$_GET['idAtto']);
			break;
		default: 
			if (isset($_REQUEST['filtra'])){
						
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
	 		$ret=Lista_Atti($Parametri,$_REQUEST['categoria'],(int)$_REQUEST['numero'],(int)$_REQUEST['anno'], htmlentities($_REQUEST['oggetto']),htmlentities($_REQUEST['DataInizio']),htmlentities($_REQUEST['DataFine']), htmlentities($_REQUEST['riferimento']),$_REQUEST['ente']);			
		}else if(isset($_REQUEST['annullafiltro'])){
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
	$responsabile=ap_get_responsabile($risultato->RespProc);
	$responsabile=$responsabile[0];
	ap_insert_log(5,5,$id,"Visualizzazione");
	$coloreAnnullati=get_option('opt_AP_ColoreAnnullati');
	if($risultato->DataAnnullamento!='0000-00-00')
		$Annullato='<p style="background-color: '.$coloreAnnullati.';text-align:center;font-size:1.5em;">Atto Annullato dal Responsabile del Procedimento<br /><br />Motivo: <span style="font-size:1;font-style: italic;">'.stripslashes($risultato->MotivoAnnullamento).'</span></p>';
	else
		$Annullato='';
echo '
<div class="Visalbo">
<button class="h" onclick="window.location.href=\''.$_SERVER['HTTP_REFERER'].'\'"><span class="dashicons dashicons-controls-back"></span> Torna alla Lista</button> <h3>Dati atto </h3>
<p>'.$Annullato.'</p>
<table class="tabVisalbo">
	    <tbody id="dati-atto">
		<tr>
			<th>Ente titolare dell\'Atto</th>
			<td style="font-style: italic;font-size: 1.5em;vertical-align: middle;">'.stripslashes(ap_get_ente($risultato->Ente)->Nome).'</td>
		</tr>
		<tr>
			<th>Numero Albo</th>
			<td style="vertical-align: middle;">'.$risultato->Numero."/".$risultato->Anno.'</td>
		</tr>
		<tr>
			<th>Codice di Riferimento</th>
			<td style="vertical-align: middle;">'.stripslashes($risultato->Riferimento).'</td>
		</tr>
		<tr>
			<th>Oggetto</th>
			<td style="vertical-align: middle;">'.stripslashes($risultato->Oggetto).'</td>
		</tr>
		<tr>
			<th>Data inizio Pubblicazione</th>
			<td style="vertical-align: middle;">'.ap_VisualizzaData($risultato->DataInizio).'</td>
		</tr>
		<tr>
			<th>Data fine Pubblicazione</th>
			<td style="vertical-align: middle;">'.ap_VisualizzaData($risultato->DataFine).'</td>
		</tr>
		<tr>
			<th>Data oblio</th>
			<td style="vertical-align: middle;">'.ap_VisualizzaData($risultato->DataOblio).'</td>
		</tr>
		<tr>
			<th>Note</th>
			<td style="vertical-align: middle;">'.stripslashes($risultato->Informazioni).'</td>
		</tr>
		<tr>
			<th>Categoria</th>
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
					<th>Meta Dati</th>
					<td style="vertical-align: middle;">'.$Meta.'</td>
				</tr>';
}
echo' 	    </tbody>
	</table>';
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
		echo '<h4>'.ap_get_Funzione_Responsabile($Soggetto->Funzione,"Descrizione").'</h4>
	<div class="Visallegato">';
	}
	$Ruolo=$Soggetto->Funzione;
	echo'		<table class="tabVisResp">
	    		<tbody>
				<tr>
					<th>Persona</th>
					<td style="vertical-align: middle;">'.$Soggetto->Cognome." ".$Soggetto->Nome.'</td>
				</tr>';
	if ($Soggetto->Email)
	echo'		<tr>
					<th>email</th>
					<td style="vertical-align: middle;"><a href="mailto:'.$Soggetto->Email.'">'.$Soggetto->Email.'</a></td>
				</tr>';
	if ($Soggetto->Telefono)
	echo'			<tr>
					<th>Telefono</th>
					<td style="vertical-align: middle;">'.$Soggetto->Telefono.'</td>
				</tr>';
	if ($Soggetto->Orario)
	echo'		<tr>
					<th>Orario ricevimento</th>
					<td style="vertical-align: middle;">'.$Soggetto->Orario.'</td>
				</tr>';
	if ($Soggetto->Note)
	echo'
				<tr>
					<th>Note</th>
					<td style="vertical-align: middle;">'.$Soggetto->Note.'</td>
				</tr>';
echo'
			    </tbody>
			</table>';
}
if($Ruolo!=""){
	echo '</div>';
}
echo '<h3>Allegati</h3>';
//print_r($_SERVER);
$TipidiFiles=ap_get_tipidifiles();
foreach ($allegati as $allegato) {
	$Estensione=ap_ExtensionType($allegato->Allegato);
	echo '<div class="Visallegato">
			<div class="Allegato">';
	if(isset($allegato->TipoFile) and $allegato->TipoFile!="" and ap_isExtensioType($allegato->TipoFile)){
		$Estensione=ap_ExtensionType($allegato->TipoFile);
		echo '<img src="'.$TipidiFiles[$Estensione]['Icona'].'" alt="'.$TipidiFiles[$Estensione]['Descrizione'].'" height="30" width="30"/>';
	}else{
		echo '<img src="'.$TipidiFiles[strtolower($Estensione)]['Icona'].'" alt="'.$TipidiFiles[strtolower($Estensione)]['Descrizione'].'" height="30" width="30"allegato/>';
	}
	echo '</div>
			<div>
				<p>
					'.strip_tags($allegato->TitoloAllegato);
			if (strpos(get_permalink(),"?")>0)
				$sep="&amp;";
			else
				$sep="?";
			if (is_file($allegato->Allegato))
				echo '        <a href="'.ap_DaPath_a_URL($allegato->Allegato).'" class="addstatdw" rel="'.get_permalink().$sep.'action=addstatall&amp;id='.$allegato->IdAllegato.'&amp;idAtto='.$id.'" target="_blank">'. basename( $allegato->Allegato).'</a> ('.ap_Formato_Dimensione_File(filesize($allegato->Allegato)).')<br />'.htmlspecialchars_decode($TipidiFiles[strtolower($Estensione)]['Verifica']).' <a href="'.get_permalink().$sep.'action=dwnalle&amp;id='.$allegato->IdAllegato.'&amp;idAtto='.$id.'" >Scarica allegato</a>';
				
			else
				echo basename( $allegato->Allegato)." File non trovato, il file &egrave; stato cancellato o spostato!";
echo'				</p>
			</div>
			<div style="clear:both;"></div>
		</div>
		';
	}
echo '
</div>
';	
}

function Lista_Atti($Parametri,$Categoria=0,$Numero=0,$Anno=0,$Oggetto='',$Dadata=0,$Adata=0,$Riferimento='',$Ente=-1){
	ob_start();
	switch ($Parametri['stato']){
			case 0:
				$TitoloAtti="Tutti gli Atti";
				break;
			case 1:
				$TitoloAtti="Atti in corso di Validit&agrave;";
				break;
			case 2:
				$TitoloAtti="Atti Scaduti";
				break;
			case 3:
				$TitoloAtti="Atti da Pubblicare";
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
		$Da=($_REQUEST['Pag']-1)*$N_A_pp;
		$A=$N_A_pp;
	}
	if (!isset($_REQUEST['ente'])){
         $Ente = '-1';
	}else{
        $Ente = $_REQUEST['ente'];
	}
	$TotAtti=ap_get_all_atti($Parametri['stato'],$Numero,$Anno,$Categorie,$Oggetto,$Dadata,$Adata,'',0,0,true,true,$Riferimento,$Ente);
	$lista=ap_get_all_atti($Parametri['stato'],$Numero,$Anno,$Categorie,$Oggetto,$Dadata,$Adata,'Anno DESC,Numero DESC',$Da,$A,false,true,$Riferimento,$Ente); 
	$titEnte=get_option('opt_AP_LivelloTitoloEnte');
	if ($titEnte=='')
		$titEnte="h2";
	$titPagina=get_option('opt_AP_LivelloTitoloPagina');
	if ($titPagina=='')
		$titPagina="h3";
	$coloreAnnullati=get_option('opt_AP_ColoreAnnullati');
	$colorePari=get_option('opt_AP_ColorePari');
	$coloreDispari=get_option('opt_AP_ColoreDispari');
	$VisFiltro="";
	if(isset($Parametri['minfiltri']) And $Parametri['minfiltri']=="si"){
		if(isset($_REQUEST['vf']) and  $_REQUEST['vf']=="s"){
			$VisFiltro='<button id="maxminfiltro" class="s"><span class="dashicons dashicons-filter"></span> Chiudi Ricerca atti mediante filtri</button>';
		}else{
//			$VisFiltro='<img src="'.Albo_URL.'img/maximize.png" id="maxminfiltro" class="h" alt="icona massimizza finestra filtri"/>';
			$VisFiltro='<button id="maxminfiltro" class="h"><span class="dashicons dashicons-filter"></span> Apri Ricerca atti mediante filtri</button>';
		}
	}
echo ' <div class="Visalbo">
<a name="dati"></a> ';
if (get_option('opt_AP_VisualizzaEnte')=='Si')
		echo '<'.$titEnte.' ><span  class="titoloEnte">'.stripslashes(get_option('opt_AP_Ente')).'</span></'.$titEnte.'>';
echo '<'.$titPagina.'><span  class="titoloPagina">'.$TitoloAtti.'</span></'.$titPagina.'>';
if (!isset($Parametri['filtri']) Or $Parametri['filtri']=="si")
	echo '<h4 class="filtri">'.$VisFiltro.'</h4>'.VisualizzaRicerca($Parametri['stato'],$Categoria,$Parametri['minfiltri']);
//$Contenuto.=  $nascondi;
if ($TotAtti>$N_A_pp){
	    $Para='';
	    foreach ($_REQUEST as $k => $v){
			if ($k!="Pag" and $k!="vf")
				if ($Para=='')
					$Para.=$k.'='.$v;
				else
					$Para.='&amp;'.$k.'='.$v;
		}
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
&nbsp;<a href="'.$Para.$PagPre.'" class="page-numbers numero-pagina" title="Vai alla pagina precedente">&lsaquo;</a> ';
		}else{
			$Pagcur=1;
			echo '&nbsp;<span class="page-numbers current" title="Sei gi&agrave; nella prima pagina">&laquo;</span>
&nbsp;<span class="page-numbers current" title="Sei gi&agrave; nella prima pagina">&lsaquo;</span> ';
		}
		echo '&nbsp;<span class="page-numbers current">'.$Pagcur.'/'.$Npag.'</span>';
		$PagSuc=$Pagcur+1;
	   	if ($PagSuc<=$Npag){
			echo '&nbsp;<a href="'.$Para.$PagSuc.'" class="page-numbers numero-pagina" title="Vai alla pagina successiva">&rsaquo;</a>
&nbsp;<a href="'.$Para.$Npag.'" class="page-numbers numero-pagina" title="Vai all\'ultima pagina">&raquo;</a>';
		}else{
			echo '&nbsp;<span class="page-numbers current" title="Se nell\'ultima pagina non puoi andare oltre">&rsaquo;</span>
&nbsp;<span class="page-numbers current" title="Se nell\'ultima pagina non puoi andare oltre">&raquo;</span>';			
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
		<table id="elenco-atti-OldStyle" class="tabella-dati-albo" summary="atti validi per riferimento, oggetto e categoria"> 
	    <caption>Atti</caption>
		<thead>
	    	<tr>
	        	<th scope="col">Prog.</th>';
foreach($FEColsOption as $Opzione => $Valore){
		if($Valore==1){
			echo '			<th scope="col">'.$Opzione.'</th>';
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
			$responsabileprocedura=ap_get_responsabile($riga->RespProc);
			$respproc=$responsabileprocedura[0]->Cognome." ".$responsabileprocedura[0]->Nome;
			$NumeroAtto=ap_get_num_anno($riga->IdAtto);
	//		Bonifica_Url();
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
			if ($FEColsOption['RespProc']==1)
				echo '
					<td '.$classe.'>
						'.$Link.$respproc .'</a>
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
					<td colspan="6">Nessun Atto Codificato</td>
				  </tr>';
	}
	echo '
     </tbody>
    </table>';
echo '</div>';
	if ($CeAnnullato) 
		echo '<p>Le righe evidenziate con questo sfondo <span style="background-color: '.$coloreAnnullati.';">&nbsp;&nbsp;&nbsp;</span> indicano Atti Annullati</p>';
echo '</div><!-- /wrap -->	';
return ob_get_clean();
}
?>
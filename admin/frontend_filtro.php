<?php
/**
 * Gestione Filtri FrontEnd.
 * @link       http://www.eduva.org
 * @since      4.2
 *
 * @package    ALbo On Line
 */
function VisualizzaRicerca($Stato=1,$cat=0,$StatoFinestra="si"){
	$anni=ap_get_dropdown_anni_atti('anno','anno','postform','',(isset($_REQUEST['anno'])?$_REQUEST['anno']:date("Y")),$Stato); 
	$categorie=ap_get_dropdown_ricerca_categorie('categoria','categoria','postform','',(isset($_REQUEST['categoria'])?$_REQUEST['categoria']:0),$Stato); 
	ap_Bonifica_Url();
	if (strpos($_SERVER['REQUEST_URI'],"?")>0)
		$sep="&amp;";
	else
		$sep="?";
	$titFiltri=get_option('opt_AP_LivelloTitoloFiltri');
	if ($titFiltri=='')
		$titFiltri="h3";
	//$HTML='<div class="ricerca">';
	$HTML='';
	//		<'.$titFiltri.' style="margin-bottom:10px;">Filtri</'.$titFiltri.'>
	$HTML.='		<form id="filtro-atti" action="'.htmlentities($_SERVER['REQUEST_URI']).'" method="post">
	';
			if (strpos(htmlentities($_SERVER['REQUEST_URI']),'page_id')>0){
				$HTML.= '<input type="hidden" name="page_id" value="'.ap_Estrai_PageID_Url().'" />';
			}	
	$HTML.= '<input type="hidden" name="categoria" value="'.$cat.'" />
				<table id="tabella-filtro-atti" class="tabella-dati-albo" >';
/*	if($cat!=0){
		$HTML.= '				
					<tr>
						<th scope="row" >Categorie</th>
						<td>'.$categorie.'</td>
					</tr>';
	}*/
	$HTML.= '
					<tr>
						<th scope="row"><label for="ente">Ente</label></th>
						<td>'.ap_get_dropdown_enti("ente","ente","","",(isset($_REQUEST['ente'])?$_REQUEST['ente']:"")).'</td>
					</tr>
					<tr>
						<th scope="row"><label for="numero">Atto N&deg;/Anno</label></th>
						<td><input type="text" size="10" maxlength="10" name="numero" id ="numero" value="'.(isset($_REQUEST['numero'])?$_REQUEST['numero']:"").'"/>/
						'.$anni.'</td>
					</tr>
					<tr>
						<th scope="row"><label for="riferimento">Riferimento</label></th>
						<td><input type="text" size="40" style="width:100%;" name="riferimento" id ="riferimento" value="'.(isset($_REQUEST['riferimento'])?$_REQUEST['riferimento']:"").'"/></td>
					</tr>
					<tr>
						<th scope="row"><label for="oggetto">Oggetto</label></th>
						<td><input type="text" size="40" style="width:100%;" name="oggetto" id ="oggetto" value="'.(isset($_REQUEST['oggetto'])?$_REQUEST['oggetto']:"").'"/></td>
					</tr>
					<tr>
						<th scope="row"><label for="Calendario1">da Data</label></th>
						<td><input name="DataInizio" id="Calendario1" type="text" value="'.htmlentities((isset($_REQUEST['DataInizio'])?$_REQUEST['DataInizio']:"")).'" size="10" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="Calendario2">a Data</label></th>
						<td><input name="DataFine" id="Calendario2" type="text" value="'.htmlentities((isset($_REQUEST['DataFine'])?$_REQUEST['DataFine']:"")).'" size="10" /></td>
					</tr>
					<tr>
						<td style="text-align:center;"><input type="submit" name="filtra" id="filtra" class="bottoneFE" value="Filtra"  /></td>
						<td style="text-align:center;"><input type="submit" name="annullafiltro" id="annullafiltro" class="bottoneFE" value="Annulla Filtro"  /></td>
					</tr>		
				</table>
			</form>
	';
	if($StatoFinestra=="si")
		$stile='style="display:none;"';
	else
		$stile="";
	$HTMLC='<div id="fe-tabs-container" '.$stile.'>
					<ul>
						<li><a href="#fe-tab-1">Parametri</a></li>';
	if($cat==0){
		$HTMLC.='
						<li><a href="#fe-tab-2">Categorie</a></li>';
	}
	$HTMLC.='
					</ul>
					<div id="fe-tab-1">';
	$HTMLC.=$HTML;
	$lista=ap_get_categorie_gerarchica();
	$HTMLL='
	          <div class="ricercaCategoria">
	              <ul style="list-style-type: none;">';
	if ($lista){
		foreach($lista as $riga){
		 	$shift=(((int)$riga[2])*15);
	   		$numAtti=ap_num_atti_categoria($riga[0],$Stato);
		 	if (strpos(get_permalink(),"?")>0)
		  		$sep="&amp;";
	   		else
		   		$sep="?";
	   		if ($numAtti>0)
	      		$HTMLL.='               <li style="text-align:left;padding-left:'.$shift.'px;font-weight: bold;"><a href="'.get_permalink().$sep.'filtra=Filtra&amp;categoria='.$riga[0].'"  >'.$riga[1].'</a> '.$numAtti.'</li>'; 
		}
	}else{
		$HTMLL.= '                <li>Nessuna Categoria Codificata</li>';
	}
	$HTMLL.='             </ul>
	          </div>';
	$HTMLC.= '
	      </div>';
	if($cat==0){	
		$HTMLC.= '
				<div id="fe-tab-2">'.$HTMLL.'
					</div>';			
	}
	$HTMLC.= '
				</div>
	<br class="clear" />';
	return $HTMLC;
}
?>
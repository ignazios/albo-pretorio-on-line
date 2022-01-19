<?php
/**
 * Gestione Filtri FrontEnd.
 * @link       http://www.eduva.org
 * @since      4.5.7
 *
 * @package    Albo On Line
 */
function VisualizzaRicerca($Stato=1,$cat=0,$StatoFinestra="si"){
	$anni=ap_get_dropdown_anni_atti('anno','anno','postform','',(isset($_REQUEST['anno'])?$_REQUEST['anno']:0),$Stato); 
	$categorie=ap_get_dropdown_ricerca_categorie('categoria','categoria','postform','',(isset($_REQUEST['categoria'])?$_REQUEST['categoria']:0),$Stato); 
	ap_Bonifica_Url();
	if (strpos($_SERVER['REQUEST_URI'],"?")>0)
		$sep="&amp;";
	else
		$sep="?";
	$titFiltri=get_option('opt_AP_LivelloTitoloFiltri');
	if ($titFiltri=='')
		$titFiltri="h3";
	$HTML='		<form id="filtro-atti" action="'.htmlentities(wp_strip_all_tags($_SERVER['REQUEST_URI'])).'" method="post">
	';
			if (strpos(htmlentities($_SERVER['REQUEST_URI']),'page_id')>0){
				$HTML.= '<input type="hidden" name="page_id" value="'.ap_Estrai_PageID_Url().'" />';
			}	
	$HTML.= '<input type="hidden" name="categoria" value="'.$cat.'" />
				<table id="tabella-filtro-atti" class="tabella-dati-albo" >
					<tr>
						<th scope="row"><label for="ente">'.__("Ente","albo-online").'</label></th>
						<td>'.ap_get_dropdown_enti("ente","ente","","",wp_strip_all_tags((isset($_REQUEST['ente'])?$_REQUEST['ente']:""))).'</td>
					</tr>
					<tr>
						<th scope="row"><label for="numero">'.__("Atto N./Anno","albo-online").'</label></th>
						<td><input type="text" size="10" maxlength="10" name="numero" id ="numero" value="'.wp_strip_all_tags((isset($_REQUEST['numero'])?$_REQUEST['numero']:"")).'"/>/
						'.$anni.'</td>
					</tr>
					<tr>
						<th scope="row"><label for="riferimento">'.__("Riferimento","albo-online").'</label></th>
						<td><input type="text" size="40" style="width:100%;" name="riferimento" id ="riferimento" value="'.wp_strip_all_tags((isset($_REQUEST['riferimento'])?$_REQUEST['riferimento']:"")).'"/></td>
					</tr>
					<tr>
						<th scope="row"><label for="oggetto">'.__("Oggetto","albo-online").'</label></th>
						<td><input type="text" size="40" style="width:100%;" name="oggetto" id ="oggetto" value="'.wp_strip_all_tags((isset($_REQUEST['oggetto'])?$_REQUEST['oggetto']:"")).'"/></td>
					</tr>
					<tr>
						<th scope="row"><label for="Calendario1">'.__("da Data","albo-online").'</label></th>
						<td><input name="DataInizio" id="Calendario1" type="text" value="'.wp_strip_all_tags((isset($_REQUEST['DataInizio'])?$_REQUEST['DataInizio']:"")).'" size="10" maxlength="10" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="Calendario2">'.__("a Data","albo-online").'</label></th>
						<td><input name="DataFine" id="Calendario2" type="text" value="'.wp_strip_all_tags((isset($_REQUEST['DataFine'])?$_REQUEST['DataFine']:"")).'" size="10" maxlength="10" /></td>
					</tr>
					<tr>
						<td style="text-align:center;"><input type="submit" name="filtra" id="filtra" class="bottoneFE" value="'.__("Filtra","albo-online").'"  /></td>
						<td style="text-align:center;"><input type="submit" name="annullafiltro" id="annullafiltro" class="bottoneFE" value="'.__("Annulla Filtro","albo-online").'"  /></td>
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
						<li><a href="#fe-tab-1">'.__("Parametri","albo-online").'</a></li>
						<li><a href="#fe-tab-2">'.__("Categorie","albo-online").'</a></li>
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
		$HTMLL.= '                <li>'.__("Nessuna Categoria Codificata","albo-online").'</li>';
	}
	$HTMLL.='             </ul>
	          </div>';
	$HTMLC.= '
	      </div>
				<div id="fe-tab-2">'.$HTMLL.'
				</div>
		</div>
	<br class="clear" />';
	return $HTMLC;
}
?>
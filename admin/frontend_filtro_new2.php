<?php
/**
 * Gestione Filtri FrontEnd.
 * @link       http://www.eduva.org
 * @since      4.5.7
 *
 * @package    Albo On Line
 */
function get_FiltriParametri($Stato=1,$cat=0,$StatoFinestra="si"){
	$anni=ap_get_dropdown_anni_atti('anno','anno','d-inline','',(isset($_REQUEST['anno'])?$_REQUEST['anno']:0),$Stato); 
	$categorie=ap_get_dropdown_ricerca_categorie('categoria','categoria','postform','',(isset($_REQUEST['categoria'])?$_REQUEST['categoria']:0),$Stato); 
	ap_Bonifica_Url();
	if (strpos($_SERVER['REQUEST_URI'],"?")>0)
		$sep="&amp;";
	else
		$sep="?";
	$titFiltri=get_option('opt_AP_LivelloTitoloFiltri');
	if ($titFiltri=='')
		$titFiltri="h3";
	$HTML='<form id="filtro-atti" action="'.htmlentities($_SERVER['REQUEST_URI']).'" method="post">';
	if (strpos(htmlentities($_SERVER['REQUEST_URI']),'page_id')>0){
		$HTML.= '<input type="hidden" name="page_id" value="'.ap_Estrai_PageID_Url().'" />';
	}	
	$HTML.= '<input type="hidden" name="categoria" value="'.$cat.'" />
		<div class="container">
        	<div class="row mb-2">
        		<div class="col-12 col-lg-4 etichetta_filtri">
					<label for="ente" class="font-weight-bold">Ente</label>
				</div>
        		<div class="col-12 col-lg-8">				
					'.ap_get_dropdown_enti("ente","ente","form-control","",(isset($_REQUEST['ente'])?$_REQUEST['ente']:"")).'
				</div>
        	</div>
        	<div class="row mb-2">
       			<div class="col-12 col-lg-4 etichetta_filtri">
					<label for="numero" class="font-weight-bold">'.__("Atto", 'wpscuola').'</label>
				</div>
        		<div class="col-12 col-lg-8">				
					<input class="w-50 d-inline" placeholder="N&deg; Atto" type="number" id="numero" name="numero" value="'.(isset($_REQUEST['numero'])?$_REQUEST['numero']:"").'" />
				</div>
			</div>

        	<div class="row mb-2">
       			<div class="col-12 col-lg-4 etichetta_filtri">
					<label for="anno" class="font-weight-bold">'.__("Anno", 'wpscuola').'</label> 
				</div>
				<div class="col-12 col-lg-8">				
					'.$anni.'
				</div>
        	</div>	

       		<div class="row mb-2">
       			<div class="col-12 col-lg-4 etichetta_filtri">
					<label for="riferimento" class="font-weight-bold">'.__("Riferimento", 'wpscuola').'</label>
				</div>
        		<div class="col-12 col-lg-8">				
					<input type="text" size="40" name="riferimento" id ="riferimento" value="'.(isset($_REQUEST['riferimento'])?$_REQUEST['riferimento']:"").'"/>
				</div>
			</div>
       		<div class="row mb-2">
       			<div class="col-12 col-lg-4 etichetta_filtri">
					<label for="oggetto" class="font-weight-bold">'.__("Oggetto", 'wpscuola').'</label>
				</div>
        		<div class="col-12 col-lg-8">				
					<input type="text" size="40" name="oggetto" id ="oggetto" value="'.(isset($_REQUEST['oggetto'])?$_REQUEST['oggetto']:"").'"/>
				</div>
			</div>
       		<div class="row mb-2">
       			<div class="col-12 col-lg-4 etichetta_filtri">
					<label for="DataInizio" class="font-weight-bold">'.__("da Data", 'wpscuola').'</label>
				</div>
        		<div class="col-12 col-lg-8">				
					<input name="DataInizio" id="DataInizio" type="date" value="'.htmlentities((isset($_REQUEST['DataInizio'])?$_REQUEST['DataInizio']:"")).'" size="10"/>
				</div>
			</div>
       		<div class="row mb-2">
       			<div class="col-12 col-lg-4 etichetta_filtri">
					<label for="DataFine" class="font-weight-bold">'.__("a Data", 'wpscuola').'</label>
				</div>
        		<div class="col-12 col-lg-8">				
					<input name="DataFine" id="DataFine" type="date" value="'.htmlentities((isset($_REQUEST['DataFine'])?$_REQUEST['DataFine']:"")).'" size="10"/>
				</div>
			</div>
      		<div class="row mt-2">
       			<div class="col col-12 col-lg-6 d-flex justify-content-center">
			      <button type="submit" class="btn btn-primary" name="filtra" id="filtra" value="Filtra">'.__("Filtra", 'wpscuola').'</button>
			    </div>
       			<div class="col col-12 col-lg-6 d-flex justify-content-center">
			      <button type="submit" class="btn btn-outline-primary" name="annullafiltro" id="annullafiltro" value="Annulla Filtro">'.__("Annulla Filtro", 'wpscuola').'</button>
			    </div>
			</div>
 		</div>
	</form>';
	return $HTML;
}

function get_FiltriCategorie($Stato=1){
	$lista=ap_get_categorie_gerarchica();
	$HTMLL='<div class="ricercaCategoria">
		<ul class="link-sublist" id="ListaCategorieAlbo">';
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
		$HTMLL.= '                <li>'.__("Nessuna Categoria Codificata", 'wpscuola').'</li>';
	}
	$HTMLL.='             </ul>
	</div>';
	return $HTMLL;
}
?>
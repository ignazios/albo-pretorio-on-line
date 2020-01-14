<?php
/**
 * Gestione FrontEnd.
 * @link       http://www.eduva.org
 * @since      4.4.4
 *
 * @package    Albo On Line
 */

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

$ret=Lista_AttiGruppo($Parametri);								  
function Lista_AttiGruppo($Parametri){
	ob_start();
	$lista=ap_get_GruppiAtti($Parametri['meta'],$Parametri['valore']); 
	$coloreAnnullati=get_option('opt_AP_ColoreAnnullati');
	$colorePari=get_option('opt_AP_ColorePari');
	$coloreDispari=get_option('opt_AP_ColoreDispari');
    $FEColsOption=get_option('opt_AP_ColonneFE',array(
									"Ente"=>0,
									"Riferimento"=>0,
									"Oggetto"=>0,
									"Validita"=>0,
									"Categoria"=>0,
									"Note"=>0,
									"DataOblio"=>0));
  	$PaginaAttiCor=get_option('opt_AP_PAttiCor');
  	$PaginaAttiSto=get_option('opt_AP_PAttiSto');
	if(!is_array($FEColsOption)){
		$FEColsOption=json_decode($FEColsOption,TRUE);
	}	
	echo '	<div class="tabalbo" style="margin-bottom:10px;">    
		<h3>'.$Parametri['titolo'].'</h3>                    
		<table id="elenco-atti-OldStyle" class="tabella-dati-albo" summary="atti validi per riferimento, oggetto e categoria"> 
	    <caption>Atti</caption>
		<thead>
	    	<tr>
				<th scope="col">Stato</th>
	        	<th scope="col">Prog.</th>';
	foreach($FEColsOption as $Opzione => $Valore){
		if($Valore==1){
			echo '			<th scope="col">'.$Opzione.'</th>';
		}
	}
	echo '	</tr></thead><tbody>';
	    $CeAnnullato=false;
	if ($lista){
	 	$pari=true;
		if (strpos(get_permalink(),"?")>0)
			$sep="&amp;";
		else
			$sep="?";
		foreach($lista as $riga){
			$categoria=ap_get_categoria($riga->IdCategoria);
			$cat=$categoria[0]->Nome;
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
			$Stato="Scaduto";
			if ($riga->DataFine>date("Y-m-d")){
				$Stato="Corrente";
				$Link='<a href="'.$PaginaAttiCor.$sep.'action=visatto&amp;id='.$riga->IdAtto.'"  style="text-decoration: underline;">';
			}else{
				$Link='<a href="'.$PaginaAttiSto.$sep.'action=visatto&amp;id='.$riga->IdAtto.'"  style="text-decoration: underline;">';
			}
			echo '<tr >
					<td '.$classe.'>'.$Stato.'</td>
			        <td '.$classe.'>'.$Link.$NumeroAtto.'/'.$riga->Anno .'</a> 
					</td>';
			if (isset($FEColsOption['Data']) And $FEColsOption['Data']==1)
				echo '
					<td '.$classe.'>
						'.ap_VisualizzaData($riga->Data) .'</a>
					</td>';
			if (isset($FEColsOption['Ente']) And $FEColsOption['Ente']==1)
				echo '
					<td '.$classe.'>
						'.$Link.$Link.stripslashes(ap_get_ente($riga->Ente)->Nome) .'</a>
					</td>';
			if (isset($FEColsOption['Riferimento']) And $FEColsOption['Riferimento']==1)
				echo '
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
				echo '								
					<td '.$classe.'>
						'.$Link.$cat .'</a>  
					</td>';
			if (isset($FEColsOption['Note']) And $FEColsOption['Note']==1)
				echo '
					<td '.$classe.'>
						'.$Link.stripslashes($riga->Informazioni) .'</a>
					</td>';
			if (isset($FEColsOption['DataOblio']) And $FEColsOption['DataOblio']==1)
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
return ob_get_clean();
}
?>
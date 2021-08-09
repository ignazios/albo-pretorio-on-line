<?php
/**
 * Gestione Stampe.
 * @link       http://www.eduva.org
 * @since      4.5.6
 *
 * @package    Albo On Line
 */
 
function LinkStampaAtto($id) {/* mr */
    if (current_user_can('admin_albo')) {
        echo '<a class="stpdf" href="' . get_permalink() . '?action=printatto&id=' . $id . '&pdf=a">'.__("Visualizza AVVISO DI INIZIO AFFISSIONE","albo-online").'</a><a href="' . get_permalink() . '?action=printatto&id=' . $id . '&pdf=c" class="stpdf">'.__("Visualizza CERTIFICATO DI PUBBLICAZIONE","albo-online").'</a>';
    }
}

function StampaAtto($id, $tipo) {
    $risultato = ap_get_atto($id);
    $resp_pub = ap_get_all_Oggetto_log(1, $id);
    $risultato = $risultato[0];
    $risultatocategoria = ap_get_categoria($risultato->IdCategoria);
    $risultatocategoria = $risultatocategoria[0];
    $allegati = ap_get_all_allegati_atto($id);
    ap_insert_log(5, 5, $id, "Visualizzazione");
    $coloreAnnullati = get_option('opt_AP_ColoreAnnullati');
    $IconaDocumenti=get_option('opt_AP_IconaDocumenti');
    if ($risultato->DataAnnullamento != '0000-00-00')
        $Annullato = '<p style="background-color: ' . $coloreAnnullati . ';text-align:center;font-size:1.5em;padding:5px;">'.__("Atto Annullato dal Responsabile del Procedimento","albo-online").'<br /><br />'.__("Motivo","albo-online").': <span style="font-size:1;font-style: italic;">' . stripslashes($risultato->MotivoAnnullamento) . '</span></p>';
    else
        $Annullato = '';
    ?>
    <script type="text/javascript">
        // <![CDATA[

        function printContent(div_id)
        {

            var DocumentContainer = document.getElementById(div_id);
            var pdf = '<html><title>Stampa</title><body style="background:#ffffff;">' +
                    DocumentContainer.innerHTML +
                    '</body></html>';
            var WindowObject = window.open('', 'PrintWindow',
                    'width=750,height=650,top=50,left=50,toolbars=no,scrollbars=yes,status=no,resizable=yes');
            WindowObject.document.writeln(pdf);
            WindowObject.document.close();
            WindowObject.focus();
            WindowObject.print();
            WindowObject.close();
            document.getElementById('print_link').style.display = 'block';

        }
        // ]]>
    </script><?php
    $rigadata = '<tr><td style="background: #fff; border: none; width: 50%; "><h4 style="background: #fff; border: none; font-size: 120%;">'.__("Data di inizio affissione","albo-online").': <span style="font-weight:normal">' . ap_VisualizzaData($risultato->DataInizio) . '</span></h4></td>'
            . '<td style="background: #fff; border: none; width: 50%;"><h4 style="background: #fff; border: none; font-size: 120%;">'.__("Data di fine affissione","albo-online").': <span style="font-weight:normal">' . ap_VisualizzaData($risultato->DataFine) . '</span></h4></td></tr>'
            . '<tr><td colspan="2"style="background: #fff; border: none; margin:0;padding:0;"><p style="border-top: 3px solid #808080;margin:0;"></p></td></tr>';
//    $user = get_user_by('login', $resp_pub[0]->Utente);
     if ($tipo == 'c') {
        $riga_tipo = __("CERTIFICATO DI PUBBLICAZIONE","albo-online");
        $idtipo = 'printCertificato';
        $pubblicatoda = '';
    } elseif ($tipo == 'a') {
        $riga_tipo = __("AVVISO DI INIZIO AFFISSIONE","albo-online");
        $idtipo = 'printAvviso';
    }
    echo '<button class="h" onclick="printContent(\'' . $idtipo . '\')"><span class="dashicons dashicons-migrate"></span> '.__("Stampa","albo-online").' ' . $label . '</button>';
	$Soggetti=unserialize($risultato->Soggetti);
	$Soggetti=ap_get_alcuni_soggetti_ruolo(implode(",",$Soggetti));
	$DatiSeg=ap_get_Funzione_StampaCertificatoSX();
	$Segretario="";
	$ResponsabilePub="";
	$PaginaAttiCor=get_option('opt_AP_PAttiCor');
	if($PaginaAttiCor===FALSE){
		$PaginaAttiCor="";		
	}
	$NomeResp=ap_get_responsabile($risultato->RespProc);
	$Soggetto=$NomeResp[0];
	$Responsabile=$Soggetto->Cognome." ".$Soggetto->Nome;
	$Unitao=ap_get_unitaorganizzativa($risultato->IdUnitaOrganizzativa);
	foreach($Soggetti as $Soggetto){
		if($Soggetto->Funzione==$DatiSeg[0]){
			$Segretario=$Soggetto->Cognome." ".$Soggetto->Nome;
		}  
/*		if($Soggetto->Funzione=="RP"){
			$Responsabile=$Soggetto->Cognome." ".$Soggetto->Nome;
		}
*/		if($Soggetto->Funzione=="RB"){
			$ResponsabilePub=$Soggetto->Cognome." ".$Soggetto->Nome;
		}
	}  
//    $custom_logo_id = get_theme_mod( 'custom_logo' );
//    $image = wp_get_attachment_image_src( $custom_logo_id , 'thumb' );
    $Testi=json_decode(get_option('opt_AP_Testi'),TRUE);
	if(!is_array($Testi)){
	  	$Testi=array("NoResp"=>"",
	  	             "CertPub"=>__("Si attesta l'avvenuta pubblicazione del documento all'albo pretorio sopra indicato per il quale non sono pervenute osservazioni","albo-online"));
	  }
    ?>
    <div class="printalbo" id="<?php echo $idtipo; ?>" style="width: 90%;">
        <table style="text-align:center; width:100%; background: #fff; border: none; font-family: Times New Roman">
            <caption style="text-align:center; font-size: 120%; border-bottom: 3px solid #808080; font-weight: bold;">
                <?php echo $riga_tipo . ' n. reg. ' . $risultato->Numero . "/" . $risultato->Anno . ' del ' . ap_VisualizzaData($risultato->DataInizio); ?>
            </caption>
            <thead>
                <tr><td colspan="2" style="background: #fff; border: none">
                        <table style="text-align:center; width:100%; border: none">
                            <tr>
                                <td style="background: #fff; border: none; text-align: right; width:40%">
                                    <img src="<?php echo $IconaDocumenti;?>" width="75px"/>
                                </td>
                                <td style="background: #fff; border: none; vertical-align: middle; width:60%; text-align: left">
                                    <h1 style="font-size: 250%;">
                                        <?php echo get_option('blogname'); ?>
                                    </h1>
                                </td>
                            </tr>                            
                        </table>
                    </td></tr>
                <tr>
                    <td colspan="2" style="background: #fff; border: none">
                        <h2 style="font-size: 210%;"><?php  _e("Albo Pretorio","albo-online");?></h2>
                    </td></tr>
                <tr>
                    <td colspan="2" style="background: #fff; border: none;">
                        <ul style="list-style: none;">
                        	<li><h4 style="font-size: 120%;"><?php  _e("Richidente","albo-online");?>: <span style="font-weight:normal"><?php echo $risultato->Richiedente ?></span></h4></li>
                        	<li><h4 style="font-size: 120%;"><?php  _e("Unità Organizzativa Responsabile","albo-online");?>: <span style="font-weight:normal"><?php echo stripslashes($Unitao->Nome); ?></span></h4></li>
                         	<li><h4 style="font-size: 120%;"><?php  _e("Responsabile Del Procedimento Amministrativo","albo-online");?>: <span style="font-weight:normal"><?php echo $Responsabile; ?></span></h4></li>   
                         </ul>                
                    </td>
                </tr>
                <?php if ($tipo == 'a') echo $rigadata; ?>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2" style="background: #fff; border: none">
                        <table style=" width:100%; border: none; margin-top: 10px; text-align: left; font-size: 130%; padding: 0">                            
                            <tr>
                                <td colspan="2" style="text-align:center;background: #fff; border: none;">
                                    <h3 style="font-weight: normal; font-size:130%; "><?php  _e("Sezione","albo-online");?>: <?php echo stripslashes($risultatocategoria->Nome); ?></h3>
                                </td>
                            </tr>
                            <?php if ($tipo == 'c'): ?>
                                <tr>
                                    <td colspan="2" style="text-align:center;background: #fff; border: none; margin-bottom: 30px;">
                                        <?php  _e("Estremi del documento pubblicato","albo-online");?>:
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <td style="font-weight: bold; text-align: right; width: 35%; padding: 5px; background: #efefef; border: none"><?php  _e("Ente titolare dell'Atto","albo-online");?></td>
                                <td style="text-align: left; width: 65%; padding: 5px; vertical-align: middle; background: #efefef; border: none"><?php echo stripslashes(ap_get_ente($risultato->Ente)->Nome); ?></td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; text-align: right; padding: 5px; background: #f6f6f6; border: none"><?php  _e("Numero Albo","albo-online");?></td>
                                <td style="text-align: left; padding: 5px;vertical-align: middle; background: #f6f6f6; border: none"><?php echo $risultato->Numero . "/" . $risultato->Anno; ?></td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; text-align: right; padding: 5px; background: #efefef; border: none"><?php  _e("Codice di Riferimento","albo-online");?></td>
                                <td style="text-align: left; padding: 5px; vertical-align: middle;background: #efefef; border: none"><?php echo stripslashes($risultato->Riferimento); ?></td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; text-align: right; padding: 5px; background: #efefef; border: none"><?php  _e("Data atto","albo-online");?></td>
                                <td style="text-align: left; padding: 5px; vertical-align: middle;background: #efefef; border: none"><?php echo ap_VisualizzaData($risultato->Data); ?></td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; text-align: right; padding: 5px; background: #f6f6f6; border: none"><?php  _e("Oggetto","albo-online");?></td>
                                <td style="text-align: left; padding: 5px;vertical-align: middle; background: #f6f6f6; border: none"><?php echo stripslashes($risultato->Oggetto); ?></td>
                            </tr>
                            <?php if ($risultato->Informazioni): ?>
                                <tr>
                                    <td style="font-weight: bold; text-align: right; padding: 5px; background: #efefef; border: none"><?php  _e("Soggetti","albo-online");?>Note</td>
                                    <td style="text-align: left; padding: 5px; vertical-align: middle;background: #efefef; border: none"><?php echo stripslashes($risultato->Informazioni); ?></td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <td colspan="2" style="padding: 5px; background: #efefef; border: none">url: <?php echo $PaginaAttiCor . '?action=visatto&id=' . $id; ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <?php //if ($tipo == 'p') echo $rigadata; ?>
                <?php if ($tipo == 'c'): ?>
                    <?php echo $rigadata; ?>
                    <tr>
                        <td colspan="2" style="font-size: 120%;background: #fff; border:0; padding-top:20px"><?php echo $Testi["CertPub"];?>
                        </td>
                    </tr>                
                <?php endif; ?>
                <tr>
                    <td style="font-size:130%; background: #fff; border: none; text-align: left; vertical-align: bottom">
                        <?php if ($tipo == 'c'): ?><strong><?php echo $DatiSeg[1];?></strong><br /><br />
                            <?php
                           echo $Segretario;
                            ?><?php endif; ?>
                    </td>
                    <td style="font-size:130%; background: #fff; border: none; width:60%; padding-top:50px; padding-right:20px; text-align:right">
                        <strong><?php  _e("Il responsabile della pubblicazione","albo-online");?></strong><br /><br />
                        <em><?php 
                        if($ResponsabilePub==''){ 
                            echo $Testi["NoResp"];
    }else
                        echo $ResponsabilePub; ?></em>
                    </td>
                </tr>
            </tbody>
        </table>
        <div style="clear:both;"></div>
    </div>
<?php } ?>
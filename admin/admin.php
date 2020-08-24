<?php
/**
 * Amministrazione richieste delle singole pagine.
 * @link       http://www.eduva.org
 * @since      4.4.5
 *
 * @package    Albo On Line
 */

// require_once(ABSPATH . 'wp-includes/pluggable.php'); 
add_action( 'init', 'albo_post');

function DownloadFile($filename){
//	echo $filename."  ".basename($filename);die();
	$fn=basename($filename);
	if(!empty($fn)){
    // Check file is exists on given path.
    if(file_exists($filename))
    {
      header('Content-Disposition: attachment; filename=' . basename($filename));  
      readfile($filename); 
      exit;
    }
    else
    {
      wp_die(basename($filename)." ".__("File non trovato","albo-online"));
    }
 }
}

function albo_post() {
	if(isset($_REQUEST['action'] )){
		switch ( $_REQUEST['action'] ) {		
			case "ToCsv":
				$Testata=preg_replace ("/[^a-zA-Z0-9 -]/", "",__("Nome Ente","albo-online")).";".
				         preg_replace ("/[^a-zA-Z0-9 -]/", "",__("Numero Atto","albo-online")).";".
				         preg_replace ("/[^a-zA-Z0-9 -]/", "",__("Riferimento","albo-online")).";".
				         preg_replace ("/[^a-zA-Z0-9 -]/", "",__("Oggetto","albo-online")).";".
				         preg_replace ("/[^a-zA-Z0-9 -]/", "",__("Data Inizio","albo-online")).";".
				         preg_replace ("/[^a-zA-Z0-9 -]/", "",__("Data Fine","albo-online")).";".
				         preg_replace ("/[^a-zA-Z0-9 -]/", "",__("Data Annullamento","albo-online")).";".
				         preg_replace ("/[^a-zA-Z0-9 -]/", "",__("Motivo Annullamento","albo-online")).";".
				         preg_replace ("/[^a-zA-Z0-9 -]/", "",__("Richiedente","albo-online")).";".
				         preg_replace ("/[^a-zA-Z0-9 -]/", "",__("Unità Organizzativa Responsabile","albo-online")).";".
				         preg_replace ('/[^a-zA-Z0-9 -]/', "",__("Responsabile del procedimento amministrativo","albo-online")).";".
				         preg_replace ("/[^a-zA-Z0-9 -]/", "",__("Categoria","albo-online")).";".
				         preg_replace ("/[^a-zA-Z0-9 -]/", "",__("Informazioni","albo-online")).";";
				$Atti="";
				$Righe=ap_Repertorio($_REQUEST['Anno'],FALSE);
				foreach($Righe as $Riga){
					$Atti.=stripcslashes($Riga->NomeEnte).";";
					$Atti.=$Riga->Numero.";";
					$Atti.=str_replace("  "," ",preg_replace ("[.^A-Za-z0-9 ]", "",stripslashes(wp_strip_all_tags(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $Riga->Riferimento),TRUE)))).";";
					$Atti.=str_replace("  "," ", preg_replace("[.^A-Za-z0-9 ]", "",stripslashes(wp_strip_all_tags(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $Riga->Oggetto),TRUE)))).";";
					$Atti.=$Riga->DataInizio.";";
					$Atti.=$Riga->DataFine.";";
					$Atti.=$Riga->DataAnnullamento.";";
					$Atti.=str_replace("  "," ",preg_replace ("[.^A-Za-z0-9 ]", "",stripslashes(wp_strip_all_tags(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $Riga->MotivoAnnullamento),TRUE)))).";";	
					$Atti.=str_replace("  "," ",preg_replace ("[.^A-Za-z0-9 ]", "",stripslashes(wp_strip_all_tags(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $Riga->Richiedente),TRUE)))).";";
					$Atti.=str_replace("  "," ",preg_replace ("/[^a-zA-Z0-9 -]/", "",stripslashes(wp_strip_all_tags(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $Riga->UnitaOrganizzativa),TRUE)))).";";
					$Atti.=str_replace("  "," ",preg_replace ("/[^a-zA-Z0-9 -]/", "",stripslashes(wp_strip_all_tags(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $Riga->ResponsabileProcedimento),TRUE)))).";";
					$Atti.=str_replace("  "," ",preg_replace ("/[^a-zA-Z0-9 -]/", "",stripslashes(wp_strip_all_tags(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $Riga->Categoria),TRUE)))).";";
					$Atti.=str_replace("  "," ",preg_replace ("/[^a-zA-Z0-9 -]/", "",stripslashes(wp_strip_all_tags(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $Riga->Informazioni),TRUE)))).";\n";
				}	
//				echo $Atti;die();
				$Dir=str_replace("\\","/",Albo_DIR.'/Repertori');
				if (!is_dir ( $Dir))
					if (!mkdir($Dir, 0744)) 
						break;
				$file_path=$Dir."/repertorio_".$_REQUEST['Anno'].".csv";
				$file = fopen($file_path, "w") or die;
				fwrite($file, $Testata."\n".$Atti);
				fclose($file);
				DownloadFile($file_path);
				break;
			case "ToXML":
				$xml=new SimpleXMLElement('<?xml version="1.0" encoding="utf-8" standalone="yes" ?><'.preg_replace ('/[^a-zA-Z0-9]/', "_",__("Repertorio","albo-online")).'></'.preg_replace ('/[^a-zA-Z0-9]/', "_",__("Repertorio","albo-online")).'>'); 
				$MetaData=$xml->addChild(preg_replace ('/[^a-zA-Z0-9]/', "_",__("Meta dati","albo-online")));
				$MetaData->addChild(preg_replace ('/[^a-zA-Z0-9]/', "_",__("Anno","albo-online")),$_REQUEST['Anno']);
				$Righe=ap_Repertorio($_REQUEST['Anno'],FALSE);
				$Atti=$xml->addChild(preg_replace ('/[^a-zA-Z0-9]/', "_",__("Atti","albo-online")));
				foreach($Righe as $Riga){
					$Atto=$Atti->addChild(preg_replace ('/[^a-zA-Z0-9]/', "_",__("Atto","albo-online")));
					$riga=$Atto->addChild(preg_replace ('/[^a-zA-Z0-9]/', "_",__("Nome Ente","albo-online")), stripslashes(wp_strip_all_tags($Riga->NomeEnte)));
					$riga=$Atto->addChild(preg_replace ('/[^a-zA-Z0-9]/', "_",__("Numero Atto","albo-online")), $Riga->Numero);
					$riga=$Atto->addChild(preg_replace ('/[^a-zA-Z0-9]/', "_",__("Riferimento","albo-online")), stripslashes(wp_strip_all_tags($Riga->Riferimento)));
					$riga=$Atto->addChild(preg_replace ('/[^a-zA-Z0-9]/', "_",__("Oggetto","albo-online")), stripslashes(wp_strip_all_tags($Riga->Oggetto)));
					$riga=$Atto->addChild(preg_replace ('/[^a-zA-Z0-9]/', "_",__("Data di registrazione","albo-online")), $Riga->Data);
					$riga=$Atto->addChild(preg_replace ('/[^a-zA-Z0-9]/', "_",__("Data Inizio","albo-online")), $Riga->DataInizio);
					$riga=$Atto->addChild(preg_replace ('/[^a-zA-Z0-9]/', "_",__("Data Fine","albo-online")), $Riga->DataFine);
					$riga=$Atto->addChild(preg_replace ('/[^a-zA-Z0-9]/', "_",__("Data Annullamento","albo-online")),$Riga->DataAnnullamento);
					$riga=$Atto->addChild(preg_replace ('/[^a-zA-Z0-9]/', "_",__("Motivo Annullamento","albo-online")),stripslashes(wp_strip_all_tags($Riga->MotivoAnnullamento)));					
					$riga=$Atto->addChild(preg_replace ('/[^a-zA-Z0-9]/', "_",__("Richiedente","albo-online")),stripslashes(wp_strip_all_tags($Riga->Richiedente)));
					$riga=$Atto->addChild(preg_replace ('/[^a-zA-Z0-9]/', "_",__("Unità Organizzativa Responsabile","albo-online")),stripslashes(wp_strip_all_tags($Riga->UnitaOrganizzativa)));
					$riga=$Atto->addChild(preg_replace ('/[^a-zA-Z0-9]/', "_",__("Responsabile del procedimento amministrativo","albo-online")),stripslashes(wp_strip_all_tags($Riga->ResponsabileProcedimento)));
					$riga=$Atto->addChild(preg_replace ('/[^a-zA-Z0-9]/', "_",__("Categoria","albo-online")),stripslashes(wp_strip_all_tags($Riga->Categoria)));
					$riga=$Atto->addChild(preg_replace ('/[^a-zA-Z0-9]/', "_",__("Informazioni","albo-online")),wp_strip_all_tags($Riga->Informazioni));
				}	
				$Dir=str_replace("\\","/",Albo_DIR.'/Repertori');
				if (!is_dir ( $Dir))
					if (!mkdir($Dir, 0755)) 
						break;
				$file_path=$Dir."/repertorio_".$_REQUEST['Anno'].".xml";
				$file = fopen($file_path, "w") or die;
				fwrite($file, $xml->asXML());
				fclose($file);
				DownloadFile($file_path);
				break;
			case "ToJson": 
				$Repertorio=ap_Repertorio($_REQUEST['Anno'],FALSE);
				$Dir=str_replace("\\","/",Albo_DIR.'/Repertori');
				if (!is_dir ( $Dir))
					if (!mkdir($Dir, 0755)) 
						break;
				$file_path=$Dir."/repertorio_".$_REQUEST['Anno'].".json";
				$file = fopen($file_path, "w") or die;
				$txt = json_encode($Repertorio);
				fwrite($file, $txt);
				fclose($file);
				DownloadFile($file_path);
				break;
/*			case "ToPdf":
				if (isset($_GET['Anno']))
					$AnnoRepertorio=$_GET['Anno'];
				else
					$AnnoRepertorio=date("Y");
				$ToPdf= new ap_cls_Repertorio("Portrait","mm","A4");
				$ToPdf->ToTable($AnnoRepertorio);
				break;			
*/			case "delete_bulk_atti":
		        if ( isset( $_GET['_wpnonce'] ) && ! empty( $_GET['_wpnonce'] ) ) {
	            	$nonce  = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING );
	            	$action = 'bulk-atti' ;
		            if ( ! wp_verify_nonce( $nonce, $action ) )
		                wp_die( __("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione è stata annullata","albo-online") );
		        }
			 	$Msg=ap_oblio_atti($_GET['IdAtto']);
			 	$location = "?page=atti&stato_atti=Eliminare&message=".urlencode($Msg);
				wp_redirect( $location );
				break;
 			case "avviso_affissione-atto":
				if ( isset( $_GET['avvisoatto'] ) && ! empty( $_GET['avvisoatto'] ) ) {
		            $nonce  = filter_input( INPUT_GET, 'avvisoatto', FILTER_SANITIZE_STRING );
		            $action = 'operazioneavviso_affissione';
		            if ( ! wp_verify_nonce( $nonce, $action ) )
		                wp_die( __("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione è stata annullata","albo-online") ,__("Problemi di sicurezza","albo-online"),array("back_link" => "?page=atti&stato_atti=Correnti") );
			 		$location = "?page=atti&stato_atti=Correnti" ;
			 		include ('stampe.php');
			 		if (is_numeric($_REQUEST['id'])) {
 	                    StampaAtto($_REQUEST['id'], 'a');
					}
					wp_die();
				}else
					wp_die( __("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione è stata annullata","albo-online") ,__("Problemi di sicurezza","albo-online"),array("back_link" => "?page=atti") );					
			break;
 			case "certificato_pubblicazione-atto":
				if ( isset( $_GET['certificatoatto'] ) && ! empty( $_GET['certificatoatto'] ) ) {
		            $nonce  = filter_input( INPUT_GET, 'certificatoatto', FILTER_SANITIZE_STRING );
		            $action = 'operazionecertificato_pubblicazione';
		            if ( ! wp_verify_nonce( $nonce, $action ) )
		                wp_die( __("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione è stata annullata","albo-online") ,__("Problemi di sicurezza","albo-online"),array("back_link" => "?page=atti&stato_atti=Correnti") );
			 		$location = "?page=atti&stato_atti=Correnti" ;
			 		include ('stampe.php');
			 		if (is_numeric($_REQUEST['id'])) {
 	                    StampaAtto($_REQUEST['id'], 'c');
					}
					wp_die();
				}else
					wp_die( __("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione è stata annullata","albo-online") ,__("Problemi di sicurezza","albo-online"),array("back_link" => "?page=atti") );					
			break;
 			case "elimina-atto":
				if ( isset( $_GET['cancellatto'] ) && ! empty( $_GET['cancellatto'] ) ) {
		            $nonce  = filter_input( INPUT_GET, 'cancellatto', FILTER_SANITIZE_STRING );
		            $action = 'operazionecancelaatto';
		            if ( ! wp_verify_nonce( $nonce, $action ) )
		               wp_die( __("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione è stata annullata","albo-online") ,__("Problemi di sicurezza","albo-online"),array("back_link" => "?page=atti") );
			 		$location = "?page=atti&stato_atti=Eliminare" ;
			 		$MessaggiRitorno=ap_oblio_atti((int)$_GET['id']);
					$location = add_query_arg( 'message',$MessaggiRitorno["Message"], $location );
					$location = add_query_arg( 'message2',$MessaggiRitorno["Message2"], $location );
					wp_redirect( $location );
				}else
					wp_die( __("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione è stata annullata","albo-online") ,__("Problemi di sicurezza","albo-online"),array("back_link" => "?page=atti") );					
			break;
		case "annulla-atto":
			if (!isset($_REQUEST['annatto'])) {
				Go_Atti();
				break;	
			}
			if (!wp_verify_nonce($_REQUEST['annatto'],'annatto')){
				Go_Atti();
				break;
			} 
			if (filter_input(INPUT_POST,'submit')== 'Annulla Pubblicazione Atto'){
				if ($_REQUEST['Motivo']=="null") {
					$NumMsg=8;
				}else{
					$Allegati=array();
					foreach($_REQUEST as $Parametro=>$ValoreParametro){
						if(substr($Parametro,0,5)=="Alle:")
							$Allegati[]=$ValoreParametro;
					}
					$Risultato=ap_annulla_atto((int)$_REQUEST['id'],$_REQUEST['Motivo'],$Allegati);
				}				
			}else{
				$Risultato=wp_die( __("Operazione Annullata","albo-online"));
			}
	 		$location = "?page=atti&stato_atti=Correnti" ;
			$location = add_query_arg( 'message', $Risultato, $location );
			wp_redirect( $location );
			break;
		case "ExportBackupData":
			if (!isset($_REQUEST['exportbckdata'])) {
				Go_Utility();
				break;	
			}
			if (!wp_verify_nonce($_REQUEST['exportbckdata'],'EsportaBackupDatiAlbo')){
				Go_Utility();
				break;
			} 		
			DownloadFile(WP_CONTENT_DIR ."/AlboOnLine/BackupDatiAlbo/".$_REQUEST['elenco_Backup_Expo']);
			break;
		case "delete-allegato-atto" :
			$location = "?page=atti" ;
			ap_del_allegato_atto((int)$_REQUEST['idAllegato'],(int)$_REQUEST['idAtto'],htmlentities($_REQUEST['Allegato']));
			$_SERVER['REQUEST_URI'] = remove_query_arg(array('message'), $_SERVER['REQUEST_URI']);
			$_SERVER['REQUEST_URI'] = remove_query_arg(array('action'), $_SERVER['REQUEST_URI']);
			$_SERVER['REQUEST_URI'] = remove_query_arg(array('idAllegato'), $_SERVER['REQUEST_URI']);
			$_SERVER['REQUEST_URI'] = remove_query_arg(array('Allegato'), $_SERVER['REQUEST_URI']);
			$location= add_query_arg( array ( 'action' => 'allegati-atto', 
									          'id' => $_REQUEST['idAtto'],
									          'allegatoatto'=>wp_create_nonce('gestallegatiatto')));
			wp_redirect( $location );
			break;
		case 'add-responsabile':
			if (!isset($_REQUEST['responsabili'])) {
				Go_Responsabili();
				break;	
			}
			if (!wp_verify_nonce($_REQUEST['responsabili'],'elabresponsabili')){
				Go_Responsabili();
				break;
			} 	
			$location = "?page=soggetti" ;
			if (!is_email( $_REQUEST['resp-email']) or $_POST['resp-cognome']==''){
				$location = add_query_arg( 'errore', !is_email( $_REQUEST['resp-email']) ? 'Email non valida': "Bisogna valorizzare il Cognome del Responsabile", $location );
				$location = add_query_arg( 'message', 4, $location );
				$location = add_query_arg( 'resp-cognome', $_POST['resp-cognome'], $location );
				$location = add_query_arg( 'resp-nome', $_POST['resp-nome'], $location );
				$location = add_query_arg( 'resp-funzione', $_POST['resp-funzione'], $location );
				$location = add_query_arg( 'resp-email', $_POST['resp-email'], $location );
				$location = add_query_arg( 'resp-telefono', $_POST['resp-telefono'], $location );
				$location = add_query_arg( 'resp-orario', $_POST['resp-orario'], $location );
				$location = add_query_arg( 'resp-note', $_POST['resp-note'], $location );
				$location = add_query_arg( 'action', 'add', $location );
			}
			else{
				$ret=ap_insert_responsabile(strip_tags($_POST['resp-cognome']),strip_tags($_POST['resp-nome']),strip_tags($_POST['resp-funzione']),strip_tags($_POST['resp-email']),strip_tags($_POST['resp-telefono']),strip_tags($_POST['resp-orario']),strip_tags($_POST['resp-note']));
				if ( !$ret && !is_wp_error( $ret ) )
					$location = add_query_arg( 'message', 1, $location );
				else
					$location = add_query_arg( 'message', 4, $location );
			}
			wp_redirect( $location );
			break;
		case 'edit-responsabile':
			if (!isset($_REQUEST['modresp'])) {
				Go_Responsabili();
				break;	
			}
			if (!wp_verify_nonce($_REQUEST['modresp'],'editresponsabile')){
				Go_Responsabili();
				break;
			} 		
			$location = "?page=soggetti" ;
			$location = add_query_arg( 'id', (int)$_GET['id'], $location );
			$location = add_query_arg( 'action', 'edit', $location );
			wp_redirect( $location );
			break;
		case 'edit-tipofile':
			if (!isset($_REQUEST['modtipfil'])) {
				Go_Responsabili();
				break;	
			}
			if (!wp_verify_nonce($_REQUEST['modtipfil'],'edittipofilee')){
				Go_Responsabili();
				break;
			} 		
			$location = "?page=tipifiles" ;
			$location = add_query_arg( 'id', $_GET['id'], $location );
			$location = add_query_arg( 'action', 'edit', $location );
			wp_redirect( $location );
			break;			
		case 'set-default':
//			var_dump($_REQUEST);die();
			if (!isset($_REQUEST['tipifiles'])) {
				Go_TipiFiles();
				break;	
			}
			if (!wp_verify_nonce($_REQUEST['tipifiles'],'elabtipifiles')){
				Go_TipiFiles();
				break;
			} 		
			$location = "?page=tipifiles" ;
			$TipidiFiles=array();
			$TipidiFiles["ndf"]= array("Descrizione"=>__('Tipo file non definito','albo-online'),"Icona"=>Albo_URL."img/notipofile.png","Verifica"=>"");
			$TipidiFiles["pdf"]= array("Descrizione"=>__('File Pdf','albo-online'),"Icona"=>Albo_URL."img/Pdf.png","Verifica"=>"");
			$TipidiFiles["p7m"]= array("Descrizione"=>__('File firmato digitalmente','albo-online'),"Icona"=>Albo_URL."img/firmato.png","Verifica"=>htmlspecialchars("<a href=\"http://vol.ca.notariato.it/\" onclick=\"window.open(this.href);return false;\">".__('Verifica firma con servizio fornito da Consiglio Nazionale del Notariato','albo-online')."</a>"));
			update_option('opt_AP_TipidiFiles', $TipidiFiles);
			wp_redirect( $location );
			break;			
		case 'delete-tipofile':
//			var_dump($_REQUEST);die();
			if (!isset($_REQUEST['canctipfil'])) {
				Go_TipiFiles();
				break;	
			}
			if (!wp_verify_nonce($_REQUEST['canctipfil'],'deletetipofile')){
				Go_TipiFiles();
				break;
			} 		
			$location = "?page=tipifiles" ;
			if(ap_delete_tipofiles($_REQUEST['id'])){
				$location = add_query_arg( 'message', 2, $location );
			}else{
				$location = add_query_arg( 'message', 6, $location );
			}
			wp_redirect( $location );
			break;	
		case 'add-tipofile':
//			var_dump($_REQUEST);die();
			if (!isset($_REQUEST['tipifiles'])) {
				Go_TipiFiles();
				break;	
			}
			if (!wp_verify_nonce($_REQUEST['tipifiles'],'elabtipifiles')){
				Go_TipiFiles();
				break;
			} 	
			$location = "?page=tipifiles" ;
			if((!isset($_REQUEST['id']) OR $_REQUEST['id']=='') OR
			   (!isset($_REQUEST['descrizione']) OR $_REQUEST['descrizione']=='') OR 
			   (!isset($_REQUEST['icona']) OR $_REQUEST['icona']=='')){
				$location = add_query_arg( 'message', 8, $location );
			}else{
				if(ap_add_tipofiles($_REQUEST['id'],$_REQUEST['descrizione'],$_REQUEST['icona'],$_REQUEST['verifica'])){
					$location = add_query_arg( 'message', 1, $location );
				}else{
					$location = add_query_arg( 'message', 4, $location );
				}				
			}
			wp_redirect( $location );
			break;	
		case 'memo-tipofile':
			if (!isset($_REQUEST['tipifiles'])) {
				Go_TipiFiles();
				break;	
			}
			if (!wp_verify_nonce($_REQUEST['tipifiles'],'elabtipifiles')){
				Go_TipiFiles();
				break;
			} 		
//			var_dump($_REQUEST);die();
			$location = "?page=tipifiles" ;
			if (!isset( $_REQUEST['id'] )){
				$location = add_query_arg( 'errore', __('Tipo file non definito','albo-online'), $location );
				$location = add_query_arg( 'message', 5, $location );
				$location = add_query_arg( 'Descrizione', $_REQUEST['descrizione'], $location );
				$location = add_query_arg( 'Icona', $_REQUEST['icona'], $location );
				$location = add_query_arg( 'Verifica', $_REQUEST['verifica'], $location );
				$location = add_query_arg( 'action', 'edit_err', $location );
				$location = add_query_arg( 'id', $_REQUEST['id'], $location );
			}else
				if (ap_memo_tipofiles($_REQUEST['id'],$_REQUEST['descrizione'],$_REQUEST['icona'],$_REQUEST['verifica']))
					$location = add_query_arg( 'message', 3, $location );
				else
					$location = add_query_arg( 'message', 5, $location );
			wp_redirect( $location );
			break;
		case 'memo-responsabile':
			if (!isset($_REQUEST['responsabili'])) {
				Go_Responsabili();
				break;	
			}
			if (!wp_verify_nonce($_REQUEST['responsabili'],'elabresponsabili')){
				Go_Responsabili();
				break;
			} 		
			$location = "?page=soggetti" ;
			if (!is_email( $_REQUEST['resp-email'] )){
				$location = add_query_arg( 'errore', __('Email non valida','albo-online'), $location );
				$location = add_query_arg( 'message', 5, $location );
				$location = add_query_arg( 'resp-cognome', $_REQUEST['resp-cognome'], $location );
				$location = add_query_arg( 'resp-nome', $_REQUEST['resp-nome'], $location );
				$location = add_query_arg( 'resp-funzione', $_REQUEST['resp-funzione'], $location );				
				$location = add_query_arg( 'resp-email', $_REQUEST['resp-email'], $location );
				$location = add_query_arg( 'resp-telefono', $_REQUEST['resp-telefono'], $location );
				$location = add_query_arg( 'resp-orario', $_REQUEST['resp-orario'], $location );
				$location = add_query_arg( 'resp-note', $_REQUEST['resp-note'], $location );
				$location = add_query_arg( 'action', 'edit_err', $location );
				$location = add_query_arg( 'id', (int)$_REQUEST['id'], $location );
			}
			else
				if (!is_wp_error(ap_memo_responsabile((int)$_REQUEST['id'],
									  strip_tags($_REQUEST['resp-cognome']),
									  strip_tags($_REQUEST['resp-nome']),
									  strip_tags($_REQUEST['resp-funzione']),
									  strip_tags($_REQUEST['resp-email']),
									  strip_tags($_REQUEST['resp-telefono']),
									  strip_tags($_REQUEST['resp-orario']),
									  strip_tags($_REQUEST['resp-note']))))
					$location = add_query_arg( 'message', 3, $location );
				else
					$location = add_query_arg( 'message', 5, $location );
	//		global $wpdb;
	//		echo $wpdb->last_query;exit; 
			wp_redirect( $location );
			break;
		case 'delete-ente':
			if (!isset($_REQUEST['cancellaente'])) {
				Go_Enti();
				break;	
			}
			if (!wp_verify_nonce($_REQUEST['cancellaente'],'deleteente')){
				Go_Enti();
				break;
			} 			
			$location = "?page=enti" ;
			$res=ap_del_ente((int)$_GET['id']);
			if (!is_array($res))
				$location = add_query_arg( 'message', 2, $location );
			else{
				if ($res['atti']>0)
					$location = add_query_arg( 'message', 7, $location );
				else
					$location = add_query_arg( 'message', 6, $location );
			}
			wp_redirect( $location );
			break;
		case 'add-ente':
			if (!isset($_REQUEST['enti'])) {
				Go_Enti();
				break;	
			}
			if (!wp_verify_nonce($_REQUEST['enti'],'enti')){
				Go_Enti();
				break;
			} 		
			$location = "?page=enti" ;
			$errore="";
			if ($_REQUEST['ente-nome']=='') $errore.=__("Bisogna valorizzare il Nome dell'Ente",'albo-online')." <br />";
			if (!is_email( $_REQUEST['ente-email'])) $errore.=__('Email non valida','albo-online')." <br />"; 
			if (!is_email( $_REQUEST['ente-pec'])) $errore.="Pec non valida <br />"; 
			if (strlen($errore)>0){
				$location = add_query_arg( 'errore', $errore, $location );
				$location = add_query_arg( 'message', 4, $location );
				$location = add_query_arg( 'ente-nome', $_REQUEST['ente-nome'], $location );
				$location = add_query_arg( 'ente-indirizzo', $_REQUEST['ente-indirizzo'], $location );
				$location = add_query_arg( 'ente-url', $_REQUEST['ente-url'], $location );
				$location = add_query_arg( 'ente-email', $_REQUEST['ente-email'], $location );
				$location = add_query_arg( 'ente-pec', $_REQUEST['ente-pec'], $location );
				$location = add_query_arg( 'ente-telefono', $_REQUEST['ente-telefono'], $location );
				$location = add_query_arg( 'ente-fax', $_REQUEST['ente-fax'], $location );
				$location = add_query_arg( 'ente-note', $_REQUEST['ente-note'], $location );
				$location = add_query_arg( 'action', 'add', $location );
			}
			else{
				$ret=ap_insert_ente(strip_tags($_REQUEST['ente-nome']),strip_tags($_REQUEST['ente-indirizzo']),strip_tags($_REQUEST['ente-url']),strip_tags($_REQUEST['ente-email']),strip_tags($_REQUEST['ente-pec']),strip_tags($_REQUEST['ente-telefono']),strip_tags($_REQUEST['ente-fax']),strip_tags($_REQUEST['ente-note']));
				if ( !$ret && !is_wp_error( $ret ) )
					$location = add_query_arg( 'message', 1, $location );
				else
					$location = add_query_arg( 'message', 4, $location );
			}
			wp_redirect( $location );
			break;
		case 'edit-ente':
			if (!isset($_REQUEST['modificaente'])) {
				Go_Enti();
				break;	
			}
			if (!wp_verify_nonce($_REQUEST['modificaente'],'editente')){
				Go_Enti();
				break;
			} 		
			$location = "?page=enti" ;
			$location = add_query_arg( 'id', (int)$_GET['id'], $location );
			$location = add_query_arg( 'action', 'edit', $location );
			wp_redirect( $location );
			break;
		case 'memo-ente':
			if (!isset($_REQUEST['enti'])) {
				Go_Enti();
				break;	
			}
			if (!wp_verify_nonce($_REQUEST['enti'],'enti')){
				Go_Enti();
				break;
			} 			
			$location = "?page=enti" ;
			$errore="";
			if ($_REQUEST['ente-nome']=='') $errore.=__("Bisogna valorizzare il Nome dell'Ente",'albo-online')." ";
			if (!is_email( $_REQUEST['ente-email'])) $errore.=__('Email non valida','albo-online')." "; 
			if (!is_email( $_REQUEST['ente-pec'])) $errore.=__('PEC non valida','albo-online')." "; 
			if (strlen($errore)>0){
				$location = add_query_arg( 'errore', $errore, $location );
				$location = add_query_arg( 'message', 4, $location );
				$location = add_query_arg( 'ente-nome', $_REQUEST['ente-nome'], $location );
				$location = add_query_arg( 'ente-indirizzo', $_REQUEST['ente-indirizzo'], $location );
				$location = add_query_arg( 'ente-url', $_REQUEST['ente-url'], $location );
				$location = add_query_arg( 'ente-email', $_REQUEST['ente-email'], $location );
				$location = add_query_arg( 'ente-pec', $_REQUEST['ente-pec'], $location );
				$location = add_query_arg( 'ente-telefono', $_REQUEST['ente-telefono'], $location );
				$location = add_query_arg( 'ente-fax', $_REQUEST['ente-fax'], $location );
				$location = add_query_arg( 'ente-note', $_REQUEST['ente-note'], $location );
				$location = add_query_arg( 'action', $_REQUEST['action2'], $location );
				$location = add_query_arg( 'id', $_REQUEST['id'], $location );
			}
			else
				if (!is_wp_error(ap_memo_ente((int)$_REQUEST['id'],
									  strip_tags($_REQUEST['ente-nome']),
									  strip_tags($_REQUEST['ente-indirizzo']),
									  strip_tags($_REQUEST['ente-url']),
									  strip_tags($_REQUEST['ente-email']),
									  strip_tags($_REQUEST['ente-pec']),
									  strip_tags($_REQUEST['ente-telefono']),
									  strip_tags($_REQUEST['ente-fax']),
									  strip_tags($_REQUEST['ente-note']))))
					$location = add_query_arg( 'message', 3, $location );
				else
					$location = add_query_arg( 'message', 5, $location );
	//		global $wpdb;
	//		echo $wpdb->last_query;exit; 
			wp_redirect( $location );
			break;
		case 'delete-unitao':
			if (!isset($_REQUEST['cancellaunitao'])) {
				Go_Enti();
				break;	
			}
			if (!wp_verify_nonce($_REQUEST['cancellaunitao'],'deleteunitao')){
				Go_Enti();
				break;
			} 			
			$location = "?page=unitao" ;
			$res=ap_del_unitao((int)$_GET['id']);
			if (!is_array($res))
				$location = add_query_arg( 'message', 2, $location );
			else{
				if ($res['unitao']>0)
					$location = add_query_arg( 'message', 7, $location );
				else
					$location = add_query_arg( 'message', 6, $location );
			}
			wp_redirect( $location );
			break;
		case 'add-unitao':
			if (!isset($_REQUEST['unitao'])) {
				Go_Enti();
				break;	
			}
			if (!wp_verify_nonce($_REQUEST['unitao'],'unitao')){
				Go_Enti();
				break;
			} 		
			$location = "?page=unitao" ;
			$errore="";
			if ($_REQUEST['unitao-nome']=='') $errore.=__("Bisogna valorizzare il Nome dell'Unità Organizzativa",'albo-online')." <br />";
			if (!is_email( $_REQUEST['unitao-email'])) $errore.=__('Email non valida','albo-online')." <br />"; 
			if (strlen($errore)>0){
				$location = add_query_arg( 'errore', $errore, $location );
				$location = add_query_arg( 'message', 4, $location );
				$location = add_query_arg( 'unitao-nome', $_REQUEST['unitao-nome'], $location );
				$location = add_query_arg( 'unitao-indirizzo', $_REQUEST['unitao-indirizzo'], $location );
				$location = add_query_arg( 'unitao-url', $_REQUEST['unitao-url'], $location );
				$location = add_query_arg( 'unitao-email', $_REQUEST['unitao-email'], $location );
				$location = add_query_arg( 'unitao-pec', $_REQUEST['unitao-pec'], $location );
				$location = add_query_arg( 'unitao-telefono', $_REQUEST['unitao-telefono'], $location );
				$location = add_query_arg( 'unitao-fax', $_REQUEST['unitao-fax'], $location );
				$location = add_query_arg( 'unitao-note', $_REQUEST['unitao-note'], $location );
				$location = add_query_arg( 'action', 'add', $location );
			}
			else{
				$ret=ap_insert_unitao(strip_tags($_REQUEST['unitao-nome']),strip_tags($_REQUEST['unitao-indirizzo']),strip_tags($_REQUEST['unitao-url']),strip_tags($_REQUEST['unitao-email']),strip_tags($_REQUEST['unitao-pec']),strip_tags($_REQUEST['unitao-telefono']),strip_tags($_REQUEST['unitao-fax']),strip_tags($_REQUEST['unitao-note']));
				if ( !$ret && !is_wp_error( $ret ) )
					$location = add_query_arg( 'message', 1, $location );
				else
					$location = add_query_arg( 'message', 4, $location );
			}
			wp_redirect( $location );
			break;
		case 'edit-unitao':
			if (!isset($_REQUEST['modificaunitao'])) {
				Go_Unitao();
				break;	
			}
			if (!wp_verify_nonce($_REQUEST['modificaunitao'],'ediunitao')){
				Go_Unitao();
				break;
			} 		
			$location = "?page=unitao" ;
			$location = add_query_arg( 'id', (int)$_GET['id'], $location );
			$location = add_query_arg( 'action', 'edit', $location );
			wp_redirect( $location );
			break;
		case 'memo-unitao':
			if (!isset($_REQUEST['unitao'])) {
				Go_Unitao();
				break;	
			}
			if (!wp_verify_nonce($_REQUEST['unitao'],'unitao')){
				Go_Unitao();
				break;
			} 			
			$location = "?page=unitao" ;
			$errore="";
			if ($_REQUEST['unitao-nome']=='') $errore.=__("Bisogna valorizzare il Nome dell'Unità Organizzativa",'albo-online')." ";
			if (!is_email( $_REQUEST['unitao-email'])) $errore.=__('Email non valida','albo-online')." "; 
			if (strlen($errore)>0){
				$location = add_query_arg( 'errore', $errore, $location );
				$location = add_query_arg( 'message', 4, $location );
				$location = add_query_arg( 'unitao-nome', $_REQUEST['unitao-nome'], $location );
				$location = add_query_arg( 'unitao-indirizzo', $_REQUEST['unitao-indirizzo'], $location );
				$location = add_query_arg( 'unitao-url', $_REQUEST['unitao-url'], $location );
				$location = add_query_arg( 'unitao-email', $_REQUEST['unitao-email'], $location );
				$location = add_query_arg( 'unitao-pec', $_REQUEST['unitao-pec'], $location );
				$location = add_query_arg( 'unitao-telefono', $_REQUEST['unitao-telefono'], $location );
				$location = add_query_arg( 'unitao-fax', $_REQUEST['unitao-fax'], $location );
				$location = add_query_arg( 'unitao-note', $_REQUEST['unitao-note'], $location );
				$location = add_query_arg( 'action', $_REQUEST['action2'], $location );
				$location = add_query_arg( 'id', $_REQUEST['id'], $location );
			}
			else
				if (!is_wp_error(ap_memo_unitao((int)$_REQUEST['id'],
									  strip_tags($_REQUEST['unitao-nome']),
									  strip_tags($_REQUEST['unitao-indirizzo']),
									  strip_tags($_REQUEST['unitao-url']),
									  strip_tags($_REQUEST['unitao-email']),
									  strip_tags($_REQUEST['unitao-pec']),
									  strip_tags($_REQUEST['unitao-telefono']),
									  strip_tags($_REQUEST['unitao-fax']),
									  strip_tags($_REQUEST['unitao-note']))))
					$location = add_query_arg( 'message', 3, $location );
				else
					$location = add_query_arg( 'message', 5, $location );
	//		global $wpdb;
	//		echo $wpdb->last_query;exit; 
			wp_redirect( $location );
			break;
		case 'add-categorie':
			if (!isset($_POST['categoria'])) {
				Go_Categorie();
				break;	
			}
			if (!wp_verify_nonce($_POST['categoria'],'categoria')){
				Go_Categorie();
				break;
			} 		
			$location = "?page=categorie" ;
			if ($_POST['cat-name']=='')
				$location = add_query_arg( 'message', 9, $location );
			else{
				$ret=ap_insert_categoria($_POST['cat-name'],$_POST['cat-parente'],$_POST['cat-descrizione'],$_POST['cat-durata']);
				if ( !$ret && !is_wp_error( $ret ) )
					$location = add_query_arg( 'message', 1, $location );
				else
					$location = add_query_arg( 'message', 4, $location );
			}			
			wp_redirect( $location );
			break;
		case 'delete-categorie':
			if (!isset($_GET['canccategoria'])) {
				Go_Categorie();
				break;	
			}
			if (!wp_verify_nonce($_GET['canccategoria'],'delcategoria')){
				Go_Categorie();
				break;
			} 		
			$location = "?page=categorie" ;
			$res=ap_del_categorie((int)$_GET['id']);
			if (!is_array($res))
				$location = add_query_arg( 'message', 2, $location );
			else{
				if ($res['atti']>0) {
					$location = add_query_arg( 'message', 8, $location );
				}else{
					if ($res['figli']>0) {
						$location = add_query_arg( 'message', 7, $location );
					}
				}
			}
			wp_redirect( $location );
			break;
		case 'edit-categorie':
			if (!isset($_GET['modcategoria'])) {
				Go_Categorie();
				break;	
			}
			if (!wp_verify_nonce($_GET['modcategoria'],'editcategoria')){
				Go_Categorie();
				break;
			} 		
			$location = "?page=categorie" ;
			$location = add_query_arg( 'id', (int)$_GET['id'], $location );
			$location = add_query_arg( 'action', 'edit', $location );
			wp_redirect( $location );
			break;
		case 'memo-categoria':
			if (!isset($_POST['categoria'])) {
				Go_Categorie();
				break;	
			}
			if (!wp_verify_nonce($_POST['categoria'],'categoria')){
				Go_Categorie();
				break;
			} 		
			$location = "?page=categorie" ;
			if (!is_wp_error( ap_memo_categorie((int)$_REQUEST['id'],
								  $_REQUEST['cat-name'],
								  $_REQUEST['cat-parente'],
								  $_REQUEST['cat-descrizione'],
								  $_REQUEST['cat-durata'])))
				$location = add_query_arg( 'message', 3, $location );
			else
				$location = add_query_arg( 'message', 5, $location );
			wp_redirect( $location );
			break;
	 	case "delete-atto":
			if (!isset($_GET['cancellaatto'])) {
				Go_Atti();
				break;	
			}
			if (!wp_verify_nonce($_GET['cancellaatto'],'deleteatto')){
				Go_Atti();
				break;
			} 		
			$location = "?page=atti&stato_atti=Nuovi" ;
			if(ap_del_allegati_atto((int)$_GET['id']))
				$location = add_query_arg( 'message2',10, $location );
			else
				$location = add_query_arg( 'message2',11, $location );
			$res=ap_del_atto($_GET['id']);
			if (!is_array($res))
				$location = add_query_arg( 'message', 2, $location );
			else{
				if ($res['allegati']>0) {
					$location = add_query_arg( 'message', 7, $location );
				}else
					$location = add_query_arg( 'message', 6, $location );
			}
			wp_redirect( $location );
			break;
		case "add-atto" :
			if (!isset($_POST['nuovoatto'])) {
				Go_Atti();
				break;	
			}
			if (!wp_verify_nonce($_POST['nuovoatto'],'nuovoatto')){
				Go_Atti();
				break;
			} 
			if(isset($_POST['Soggetto'])){
				$Soggetti=serialize($_POST['Soggetto']);
			}else{
				$Soggetti=serialize(array());
			}
			$location = "?page=atti&stato_atti=Nuovi" ;
			$NewIDAtto=ap_insert_atto($_POST['Ente'],
					            $_POST['Data'],
			                    $_POST['Riferimento'],
								$_POST['Oggetto'],
								$_POST['DataInizio'],
								$_POST['DataFine'],
								$_POST['DataOblio'],
								$_POST['Note'],
								$_POST['Categoria'],
								$_POST['Responsabile'],
								$Soggetti,
								$_POST['Unitao'],
								$_POST['Richiedente']);
			if ( is_numeric( $NewIDAtto ))
				$location = add_query_arg( 'message', 1, $location );
			else{
				$location = add_query_arg( 'message', 4, $location );
				$location = add_query_arg( 'errore', $ret , $location );		
			}
			if(is_array($_REQUEST['newMetaName'])){
				for($i=0;$i<count($_REQUEST['newMetaName']);$i++){
					ap_add_attimeta($NewIDAtto,$_REQUEST['newMetaName'][$i],$_REQUEST['newValue'][$i]);
				}				
			}
			wp_redirect( $location );
			break;
		case "memo-atto" :
			if (!isset($_REQUEST['modificaatto'])) {
				Go_Atti();
				break;	
			}
			if (!wp_verify_nonce($_REQUEST['modificaatto'],'editatto')){
				Go_Atti();
				break;
			}	
			if(isset($_POST['Soggetto'])){
				$Soggetti=serialize($_POST['Soggetto']);
			}else{
				$Soggetti=serialize(array());
			}
			$location = "?page=atti&stato_atti=Nuovi" ;
			$ret=ap_memo_atto((int)$_REQUEST['id'],
							  $_REQUEST['Ente'],
			                  $_POST['Data'],
			                  $_POST['Riferimento'],
							  $_POST['Oggetto'],
							  $_POST['DataInizio'],
							  $_POST['DataFine'],
							  $_POST['DataOblio'],
							  $_POST['Note'],
							  $_POST['Categoria'], 
							  $_POST['Responsabile'],
							  $Soggetti,
							  $_POST['Unitao'],
							  $_POST['Richiedente']);
			if ( !$ret && !is_wp_error( $ret ) )
				$location = add_query_arg( 'message', 3, $location );
			else
				$location = add_query_arg( 'message', 5, $location );
			ap_remove_metasatto((int)$_REQUEST['id'],(is_array($_REQUEST['newMetaName'])?$_REQUEST['newMetaName']:""));
			if(is_array($_REQUEST['newMetaName'])){
				for($i=0;$i<count($_REQUEST['newMetaName']);$i++){
					ap_add_attimeta((int)$_REQUEST['id'],$_REQUEST['newMetaName'][$i],$_REQUEST['newValue'][$i]);
				}				
			}
			wp_redirect( $location );
			break;
		case "memo_metadati_atto":
			if (!isset($_REQUEST['mmda'])) {
				Go_Atti();
				break;	
			}
			if (!wp_verify_nonce($_REQUEST['mmda'],'editmetadatiattoatto')){
				Go_Atti();
				break;
			}		
			$location = "?page=atti&stato_atti=".filter_input(INPUT_POST,"stato_atti");	
			$location = add_query_arg( 'message',12, $location );
			if(is_array($_REQUEST['newMetaName']) And count($_REQUEST['newMetaName'])>0){
				for($i=0;$i<count($_REQUEST['newMetaName']);$i++){
					ap_add_attimeta((int)$_REQUEST['id'],$_REQUEST['newMetaName'][$i],$_REQUEST['newValue'][$i]);
				}				
			}
			ap_remove_metasatto((int)$_REQUEST['id'],$_REQUEST['newMetaName']);
			wp_redirect( $location );
			break;
		case "memo-allegato-atto-associato":
			$location='?page=atti&action=allegati-atto&id='.(int)$_REQUEST['id'].'&allegatoatto='.wp_create_nonce('gestallegatiatto');

			if (!isset($_REQUEST['secure'])) {
				Go_Atti();
				break;	
			}
			if (!wp_verify_nonce($_REQUEST['secure'],'uploallegatoassociato')){
				Go_Atti();
				break;
			}
			if (isset($_REQUEST['annulla'])){
				wp_redirect( $location );
			}else{
				$messaggio =addslashes(str_replace(" ","%20",Memo_allegato_atto_collegato()));
				if (isset($_REQUEST['ref']))
					$location = add_query_arg(
						array ( 'action' => $_REQUEST['ref'], 
								'messaggio' => $messaggio,
								'allegatoatto'=>wp_create_nonce('gestallegatiatto'),
								'id' => (int)$_REQUEST['id']) , 
						$location );
				else
					$location = add_query_arg(
						array ( 'action' => 'allegati-atto', 
				                'messaggio' => $messaggio,
								'allegatoatto'=>wp_create_nonce('gestallegatiatto'),
								'id' => (int)$_REQUEST['id']) , 
						$location );
			}
			wp_redirect( $location );
			break;
		case "memo-allegato-atto":
			$location='?page=atti&action=allegati-atto&id='.(int)$_REQUEST['id'].'&allegatoatto='.wp_create_nonce('gestallegatiatto');
			if (!isset($_REQUEST['uploallegato'])) {
				Go_Atti();
				break;	
			}
			if (!wp_verify_nonce($_REQUEST['uploallegato'],'uploadallegati')){
				Go_Atti();
				break;
			}
			if (isset($_REQUEST['annulla'])){
				wp_redirect( $location );
			}else{
				$messaggio =addslashes(str_replace(" ","%20",Memo_allegato_atto()));
				if (isset($_REQUEST['ref']))
					$location = add_query_arg(
						array ( 'action' => $_REQUEST['ref'], 
								'messaggio' => $messaggio,
								'allegatoatto'=>wp_create_nonce('gestallegatiatto'),
								'id' => (int)$_REQUEST['id']) , 
						$location );
				else
					$location = add_query_arg(
						array ( 'action' => 'allegati-atto', 
				                'messaggio' => $messaggio,
								'allegatoatto'=>wp_create_nonce('gestallegatiatto'),
								'id' => (int)$_REQUEST['id']) , 
						$location );
			}
			wp_redirect( $location );	
			break;	
		case "memo-allegati-atto":
			$location='?page=atti&action=allegati-atto&id='.(int)$_REQUEST['id'].'&allegatoatto='.wp_create_nonce('gestallegatiatto');
			if (!isset($_REQUEST['uploallegato'])) {
				Go_Atti();
				break;	
			}
			if (!wp_verify_nonce($_REQUEST['uploallegato'],'uploadallegati')){
				Go_Atti();
				break;
			}
			if (isset($_REQUEST['annulla'])){
				wp_redirect( $location );
			}else{
				$messaggio =addslashes(str_replace(" ","%20",Memo_allegato_atto()));
				if (isset($_REQUEST['ref']))
					$location = add_query_arg(array ( 'action' => $_REQUEST['ref'], 
												  'messaggio' => $messaggio,
												  'allegatoatto'=>wp_create_nonce('gestallegatiatto'),
												  'id' => (int)$_REQUEST['id']) , $location );
				else
					$location = add_query_arg(array ( 'action' => 'allegati-atto', 
				                                  'messaggio' => $messaggio,
												  'allegatoatto'=>wp_create_nonce('gestallegatiatto'),
												  'id' => (int)$_REQUEST['id']) , $location );
			}
			wp_redirect( $location );	
			break;	
		case "update-allegato-atto":
			if (!isset($_REQUEST['modificaallegatoatto'])) {
				Go_Atti();
				break;	
			}
			if (!wp_verify_nonce($_REQUEST['modificaallegatoatto'],'editallegatoatto')){
				Go_Atti();
				break;
			}		
			$location='?page=atti&action=allegati-atto&id='.(int)$_REQUEST['id'].'&allegatoatto='.wp_create_nonce('gestallegatiatto');
			if ($_REQUEST['submit']=="Annulla"){
				wp_redirect( $location );
			}else{
//				var_dump($_REQUEST);wp_die();
				$ret=ap_memo_allegato($_REQUEST['idAlle'],$_REQUEST['titolo'],(int)$_REQUEST['id'],(int)$_REQUEST['Integrale'], $_REQUEST['Natura']);
				if ( is_object($ret)){
					$location = add_query_arg( 'messaggio', str_replace(' ',"%20",$ret->get_error_message()), $location );	
				}
				else{
				 	$location = add_query_arg( 'messaggio', "Allegato%20Aggiornato", $location );
				}
				wp_redirect( $location );		
			}
			break;
	}		
}
}
function Memo_allegato_atto_collegato(){
	if ($_REQUEST["operazione"]=="associa_allegato"){
		if (!isset($_REQUEST['secure'])) {
			return __("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione è stata annullata","albo-online");
		}
		if (!wp_verify_nonce($_REQUEST['secure'],'uploallegatoassociato')){
			return __("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione è stata annullata","albo-online");
		} 		
		$destination_path =AP_BASE_DIR.get_option('opt_AP_FolderUpload').'/';
		$targetfile = $_REQUEST['AllegatiSpuri'];
		$Impronta=ap_insert_allegato($_POST['Descrizione'],str_replace("//","/",str_replace("\\","/",$targetfile)),$_POST['id'], $_REQUEST['Integrale'],$_REQUEST['Natura']);
	}
	return __("File associato","albo-online")."%25%25br%25%25Nome: " . basename( $targetfile)." %25%25br%25%25".__("Percorso completo","albo-online")." : ".str_replace("//","/",str_replace("\\","/",$targetfile))." %25%25br%25%25".__("Impronta","albo-online")." : ".$Impronta."%25%25br%25%25".__("Documento Integrale","albo-online").": " .(isset($_REQUEST['Integrale'])?"Si":"No")."%25%25br%25%25".__("Natura documento","albo-online").": " .($_REQUEST['Natura']=="D"?"Documento firmato":"Allegato");
}

function Memo_allegato_atto(){
	if ($_REQUEST["operazione"]=="upload"){
		if (!isset($_REQUEST['uploallegato'])) {
			return __("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione è stata annullata","albo-online");
		}
		if (!wp_verify_nonce($_REQUEST['uploallegato'],'uploadallegati')){
			return __("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione è stata annullata","albo-online");
		} 		
//		var_dump($_FILES);var_dump($_FILES["files"]['name']);wp_die();
		$numAllegati=count($_FILES["files"]['name']);
		$retMessages=__("Allegati caricati n.","albo-online").$numAllegati."%25%25br%25%25";
		for ($i=0;$i<$numAllegati;$i++) {
			if ((($_FILES["files"]["size"][$i] / 1024)/1024)<1) {
				$DimFile=number_format($_FILES["files"]["size"][$i] / 1024,2);
				$UnitM=" KB";
			}else{
				$DimFile=number_format(($_FILES["files"]["size"][$i] / 1024)/1024,2);	
				$UnitM=" MB";
			}
		    $dime= __("Dimensione","albo-online").": " . $DimFile . " ".$UnitM;
			if ($_FILES['files']['tmp_name'][$i]==''){
				$messages[4]= __("File non selezionato Oppure operazione annullata","albo-online");
			}else{
				if (!ap_isAllowedExtension(strtolower($_FILES["files"]["name"][$i]))){
					$messages= __("Tipo file non valido","albo-online");
				}else{
					if (($DimFile>(int)ini_get('upload_max_filesize')) and ($UnitM==" MB")){
						$messages= sprintf(__("Il file caricato è di %s Mb, il limite massimo è di %s Mb","albo-online"),$DimFile,ini_get('upload_max_filesize'));
					}else{
					  	if ($_FILES["files"]["error"][$i] > 0){
							$messages= __("Errore","albo-online").": " . $_FILES["file"]["error"][$i];
			    		}else{
							if (get_option( 'opt_AP_FolderUploadMeseAnno' )=="") {
								$destination_path =AP_BASE_DIR.get_option('opt_AP_FolderUpload').'/';
							}else{
								$destination_path =ap_get_PathAllegati($_POST['id'])."/";
							}
					   		$result = 0;
						   	$target_path = ap_UniqueFileName($destination_path . basename(sanitize_file_name(remove_accents ( $_FILES['files']['name'][$i]))));
							if(@move_uploaded_file($_FILES['files']['tmp_name'][$i],$target_path)){
			    				$messages= __("File caricato","albo-online")."%25%25br%25%25".__("Nome","albo-online").": " . basename( $target_path)." %25%25br%25%25".__("Percorso completo","albo-online")." : ".str_replace("\\","/",$target_path);
			    				$Natura=(isset($_POST['Natura'][$i])?"D":"A");
			    				$Integrale=(isset($_POST['Integrale'][$i])?1:0);
			    				$Impronta=ap_insert_allegato($_POST['Descrizione'][$i],str_replace("\\","/",$target_path),$_POST['id'],$Integrale,$Natura);
			    				$messages.= "%25%25br%25%25".__("Impronta","albo-online").": " .$Impronta;
			    				$messages.= "%25%25br%25%25".__("Documento Integrale","albo-online").": " .(isset($_POST['Integrale'][$i])?"Si":"No");
			    				$messages.= "%25%25br%25%25".__("Natura documento","albo-online").": " .(isset($_POST['Natura'][$i])?"Documento firmato":"Allegato");
					   		}else{
								$messages= __("Il File non caricato","albo-online").": " .str_replace("\\","/",$target_path)."%25%25br%25%25 ".__("Errore","albo-online").":".$_FILES['file']['error'];
							}
						}
			  		}
			  	}
			}
			$retMessages.=$messages.(($dime!="")?"%25%25br%25%25".($dime): "")."%25%25br%25%25";
		}	
	}
	return $retMessages;
}
function Go_Atti(){
	$location = "?page=atti&p=1" ;
	$location = add_query_arg( 'message', 80, $location );
	wp_redirect( $location );
}
function Go_Enti(){
	$location = "?page=enti" ;
	$location = add_query_arg( 'message', 80, $location );
	wp_redirect( $location );
}
function Go_Unitao(){
	$location = "?page=unitao" ;
	$location = add_query_arg( 'message', 80, $location );
	wp_redirect( $location );
}
function Go_Categorie(){
	$location = "?page=categorie" ;
	$location = add_query_arg( 'message', 80, $location );
	wp_redirect( $location );
}
function Go_Responsabili(){
	$location = "?page=soggetti" ;
	$location = add_query_arg( 'message', 80, $location );
	wp_redirect( $location );
}
function Go_TipiFiles(){
	$location = "?page=tipifiles" ;
	$location = add_query_arg( 'message', 80, $location );
	wp_redirect( $location );
}
function Go_Utility(){
	$location = "?page=utilityAlboP" ;
	$location = add_query_arg( 'message', 80, $location );
	wp_redirect( $location );
}
?>
<?php
/**
 * Gestione Atti.
 * @link       http://www.eduva.org
 * @since      4.2
 *
 * @package    ALbo On Line
 */
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

if (!class_exists('WP_List_Table')) {
 require_once(ABSPATH.'wp-admin/includes/class-wp-list-table.php');
}

class AdminTableAtti extends WP_List_Table
{
/*		 1 - in corso di validità 	"Correnti"
		 2 - scaduti				"Scaduti"	
		 3 - da pubblicare			"DaPubblicare"
		 4 - da cancellare			"Eliminare"
		 5 - cerca "Cerca" mr
*/
  public $stato_atti="Tutti";
  public $Atti_DaPubblicare; 
  public $Atti_Correnti; 
  public $Atti_Scaduti; 
  public $Atti_Eliminare; 
  public $Atti_Tutti; 
  public $AzioneDefault;
  public $Cerca; /* mr */
 
  function Codstato_atti(){
  	switch ($this->stato_atti){
		case "Correnti":$Ret=1;break;
		case "Scaduti":$Ret=2;break;
		case "DaPubblicare":$Ret=3;break;
		case "Eliminare":$Ret=4;break;
		case "Cerca":$Ret=5;break; /* mr */
 		default: $Ret=0;break;
	}
	return $Ret;
  }

  function __construct() {
  	$this->Atti_DaPubblicare=ap_get_all_atti(3,0,0,0,'', 0,0,"",0,0,true);
  	$this->Atti_Correnti=ap_get_all_atti(1,0,0,0,'', 0,0,"",0,0,true); 
  	$this->Atti_Scaduti=ap_get_all_atti(2,0,0,0,'', 0,0,"",0,0,true); 
  	$this->Atti_Eliminare=ap_get_all_atti(4,0,0,0,'', 0,0,"",0,0,true);
  	$this->Atti_Tutti=ap_get_all_atti(0,0,0,0,'', 0,0,"",0,0,true);
    $this->Atti_Cerca=ap_get_all_atti(5,0,0,0,(isset($_REQUEST['s'])?$_REQUEST['s']:""), 0,0,"",0,0,true);
    parent::__construct(array('singular'=>'Atto','plural'=>'Atti'));
  }

	function get_views() {
	    $status_links = array(
	        "Tutti"		  => "<a href='?page=atti&amp;stato_atti=Tutti'><strong>Tutti (".$this->Atti_Tutti.")</strong></a>",
	        "nuovi"       => "<a href='?page=atti&amp;stato_atti=Nuovi'>da Pubblicare(".$this->Atti_DaPubblicare.")</a>",
	        "correnti"    => "<a href='?page=atti&amp;stato_atti=Correnti'>Correnti(".$this->Atti_Correnti.")</a>",
	        "storico"     => "<a href='?page=atti&amp;stato_atti=Scaduti'>Scaduti(".$this->Atti_Scaduti.")</a>",
	        "oblio"       => "<a href='?page=atti&amp;stato_atti=Eliminare'>da Eliminare(".$this->Atti_Eliminare.")</a>",
	    );
	    return $status_links;
	}
  // Funzione per la preparazione dei campi da visualizzare
  // e la query SQL principale che deve essere eseguita 

  function prepare_items()
  {
    global $wpdb;
 
    // Calcolo elenco de dei campi per le differenti
    // sezioni e memorizzo tutto in array separati

    $columns  = $this->get_columns();
    $hidden   = $this->get_columns_hidden();
    $sortable = $this->get_columns_sortable();

    // Bisogna memorizzare tre array che devono contenere i campi da 
    // visualizzare, quelli nascosti e quelli per eseguire l'ordinamento


    $this->_column_headers = array($columns,$hidden,$sortable);

    // Preparazione delle variabili che devono essere utilizzate
    // nella preparazione della query con gli ordinamenti e la posizione
	$user = get_current_user_id();
	$screen = get_current_screen();
	$screen_option = $screen->get_option('per_page', 'option');
	$per_page = get_user_meta($user, $screen_option, true);
	if ( empty ( $per_page) || $per_page < 1 ) {
	    $per_page = $screen->get_option( 'per_page', 'default' );
	}
	if (!is_numeric($per_page))
		$per_page = 10;

    if (!isset($_REQUEST['paged'])) 
    	$paged = 0;
      else $paged = max(0,(intval($_REQUEST['paged'])-1)*$per_page);

    if (isset($_REQUEST['orderby'])and in_array($_REQUEST['orderby'],array_keys($sortable)))
    	$orderby = $_REQUEST['orderby']; 
    else
    	$orderby ="Anno DESC, Numero DESC , Data DESC";

    if (isset($_REQUEST['order']) and in_array($_REQUEST['order'],array('asc','desc')))
    	$order = $_REQUEST['order']; 
    else $order = '';

    // Calcolo le variabili che contengono il numero dei record totali
    // e l'elenco dei record da visualizzare per una singola pagina
    $total_items = ap_get_all_atti($this->Codstato_atti(),0,0,0,'', 0,0,"",0,0,true);
    $this->items = ap_get_all_atti($this->Codstato_atti(),0,0,0,'', 0,0,$orderby." ".$order ,$paged,$per_page);
    $this->set_pagination_args(array(
    'total_items' => $total_items,
    'per_page'    => $per_page,
    'total_pages' => ceil($total_items/$per_page)
  ));
  }

  // Funzione per la definizione dei campi che devono
  // essere visualizzati nella lista da visualizzare

	function get_columns()
	{
	  switch ($this->stato_atti){
	  	case "Tutti":
	  	case "Correnti": 
	  	case "Scaduti":
	  		$columns = array(
		    'Stato'			 	 => 'Stato',
		    'Numero'             => 'Numero',
		    'Riferimento'        => 'Riferimento',
		    'Oggetto'          	 => 'Oggetto',
		    'Ente'               => 'Ente',
			'MetaDati'           => 'Meta Dati',	
		    'Data'          	 => 'Del',
		    'validita'           => 'Validità Dal/Al',
		    'dataoblio'        	 => 'Oblio',
		    'Idcategoria'     	 => 'Categoria');
		    break;
	  	case "DaPubblicare": 
	  		$columns = array(
		    'Stato'			 	 => 'Stato',
		    'Riferimento'        => 'Riferimento',
		    'Oggetto'          	 => 'Oggetto',
		    'Ente'               => 'Ente',
			'MetaDati'           => 'Meta Dati',	
		    'Data'          	 => 'Del',
		    'Idcategoria'     	 => 'Categoria');
		    break;
	  	case "Eliminare": 
	  		$columns = array(
	    	'cb'                 => '<input type="checkbox"/>',
		    'Stato'			 	 => 'Stato',
		    'Numero'             => 'Numero',
		    'Riferimento'        => 'Riferimento',
		    'Oggetto'          	 => 'Oggetto',
		    'Ente'               => 'Ente',
			'MetaDati'           => 'Meta Dati',	
		    'Data'          	 => 'Del',
		    'validita'           => 'Validità Dal/Al',
		    'dataoblio'        	 => 'Oblio',
		    'Idcategoria'     	 => 'Categoria');
		    break;
        case "Cerca": 
        	$columns = array(
		    'Stato'			 	 => 'Stato',
		    'Numero'             => 'Numero',
		    'Riferimento'        => 'Riferimento',
		    'Oggetto'          	 => 'Oggetto',
		    'Ente'               => 'Ente',
		    'Data'          	 => 'Del',
		    'validita'           => 'Validità Dal/Al',
		    'dataoblio'        	 => 'Oblio',
		    'Idcategoria'     	 => 'Categoria');
		    break;
	  }
	  return $columns;
	}

  // Funzione per la definizione dei campi che possono
  // essere utilizzati per eseguire la funzione di ordinamento

  function get_columns_sortable()
  {
	if (isset($_REQUEST['s'])){ /* mr */
		$sortable_columns = array(
      		'Data'       => array('Data',true),
            'Numero'      => array('Numero',true),             
      		'DataInizio'  => array('DataInizio',true),
      		'DataFine'    => array('DataFine',false));
    }else{	
   		$sortable_columns = array(
   			'Data'       => array('Data',true),
      		'DataInizio' => array('DataInizio',true),
      		'DataFine' 	=> array('DataFine',false));
	}
    return $sortable_columns;
  }

  // Funzione per la definizione dei campi che devono 
  // essere calcolati dalla query ma non visualizzati

  function get_columns_hidden() {
	  return array();  
  }

  // Funzione per reperire il valore di un campo in
  // maniera standard senza una personalizzazione di output

  function column_default($item,$column_name) { 
    return $item->$column_name; 
  }

  // Dato che alcuni campi hanno bisogno di output 
  // personalizzato bisogna creare una funzione per campo
  function column_Stato($item) { 

	$Msg="";
	if ( $item->DataAnnullamento != '0000-00-00' ) {
			$Annullato = true;
		} else {
			$Annullato = false;
		}

		if ((ap_cvdate($item->DataInizio) <= ap_cvdate(date("Y-m-d"))) and (ap_cvdate($item->DataFine) >= ap_cvdate(date("Y-m-d"))))
			$Scaduto=False;
		else	
			$Scaduto=True;

   	  $actions = array(
	    'visualizza'   => '<a href="?page=atti&amp;action=view-atto&amp;id='.$item->IdAtto.'&amp;stato_atti='.$this->stato_atti.'"  >
						<span class="dashicons dashicons-search" title="Visualizza dati atto"></span>
					</a>');
	$this->AzioneDefault='<a href="?page=atti&amp;action=view-atto&amp;id='.$item->IdAtto.'&amp;stato_atti='.$this->stato_atti.'" >';
	switch($this->stato_atti){
		case "Tutti":
			$Msg="";
			$Msg.=($Scaduto?'<span style="color: rgb(23, 5, 161);font-weight: bold;">Scaduto</span>':'<span style="color: green;font-weight: bold;">Corrente</span>');
			$Msg.=($Annullato?' <span style="color: red;font-weight: bold;">Annullato</span>':"");
			break;		
		case "DaPubblicare":
			$actions['cancella'] ='<span class="trash"><a href="?page=atti&amp;action=delete-atto&amp;id='.$item->IdAtto.'&amp;cancellaatto='.
				wp_create_nonce('deleteatto').'" rel="'.strip_tags($item->Oggetto).'" tag="" class="ac">
						<span class="dashicons dashicons-trash" title="Cancella Atto"></span>
					</a></span>';
			$this->AzioneDefault='<a href="?page=atti&amp;action=edit-atto&amp;id='.$item->IdAtto.'&amp;modificaatto='.
			wp_create_nonce('editatto').'">';
			$actions['modifica'] ='<a href="?page=atti&amp;action=edit-atto&amp;id='.$item->IdAtto.'&amp;modificaatto='.
			wp_create_nonce('editatto').'">
						<span class="dashicons dashicons-edit" title="Modifica atto"></span>
					</a>';
			$actions['allegati'] ='<a href="?page=atti&amp;action=allegati-atto&amp;id='.$item->IdAtto.'&amp;allegatoatto='.
			wp_create_nonce('gestallegatiatto').'">
						<span class="dashicons dashicons-upload" title="Allegati"></span>
					</a>';
			if (current_user_can('editore_atti_albo')){
				$actions['pubblica'] ='<a href="?page=atti&amp;action=approva-atto&amp;id='.$item->IdAtto.'"  >
	<span class="dashicons dashicons-share-alt" title="Pubblica atto"></span>
					</a>';
			}
			$Msg='<span style="color: green;font-weight: bold;">Da Pubblicare</span>';
			break;
		case "Correnti":
			$Msg=($Annullato?'<span style="color: red;font-weight: bold;">Annullato</span>':'<span style="color: green;font-weight: bold;">Pubblicato</span>');
			$actions['meta'] ='<a href="?page=atti&amp;action=metadati-atto&amp;id='.$item->IdAtto.'&amp;metaatto='.wp_create_nonce('operazionemetaatto').'&stato_atti=Correnti">
				<span class="dashicons dashicons-screenoptions" title="Gestisci metadati Atto"></span>
			</a>';
			if (current_user_can('editore_atti_albo')){
				$actions['avviso'] ='<a href="?page=atti&amp;action=avviso_affissione-atto&amp;id='.$item->IdAtto.'&amp;avvisoatto='.wp_create_nonce('operazioneavviso_affissione').'&stato_atti=Correnti">
				<span class="dashicons dashicons-media-text" title="Stampa Avviso di Affissione"></span>
			</a>';
			}
		break;
		case "Scaduti":
			$Msg=($Annullato?'<span style="color: red;font-weight: bold;">Annullato</span>':'<span style="color: rgb(23, 5, 161);font-weight: bold;">Scaduto</span>');			
			$actions['meta'] ='<a href="?page=atti&amp;action=metadati-atto&amp;id='.$item->IdAtto.'&amp;metaatto='.wp_create_nonce('operazionemetaatto').'&stato_atti=Scaduti">
				<span class="dashicons dashicons-screenoptions" title="Gestisci metadati Atto"></span>
			</a>';
				$actions['certificato'] ='<a href="?page=atti&amp;action=certificato_pubblicazione-atto&amp;id='.$item->IdAtto.'&amp;certificatoatto='.wp_create_nonce('operazionecertificato_pubblicazione').'&stato_atti=Correnti">
				<span class="dashicons dashicons-media-spreadsheet" title="Stampa Certificato Pubblicazione"></span>
			</a>';
			break;
		case "Eliminare":
			if (current_user_can('editore_atti_albo')){
				$actions['delete'] ='<span class="trash"><a href="?page=atti&amp;action=elimina-atto&amp;id='.$item->IdAtto.'&amp;cancellatto='.
				wp_create_nonce('operazionecancelaatto').'">
				<span class="dashicons dashicons-trash" title="Oblio Atto"></span>
			</a></span>';
			}
			$Msg='<span style="color: red;font-weight: bold;">Oblio</span>';			
			break;			
        case "Cerca": /* mr */
            if( $item->Numero == 0 ){    
				$Msg=('<span style="color: green;font-weight: bold;">Da Pubblicare</span>');
                $actions['cancella'] ='<span class="trash"><a href="?page=atti&amp;action=delete-atto&amp;id='.$item->IdAtto.'&amp;cancellaatto='.
			wp_create_nonce('deleteatto').'" rel="'.strip_tags($item->Oggetto).'" tag="" class="ac">
					<span class="delete dashicons dashicons-trash" title="Cancella Atto"></span>
				</a></span>';
				$this->AzioneDefault='<a href="?page=atti&amp;action=edit-atto&amp;id='.$item->IdAtto.'&amp;modificaatto='.
				wp_create_nonce('editatto').'">';
				$actions['modifica'] ='<a href="?page=atti&amp;action=edit-atto&amp;id='.$item->IdAtto.'&amp;modificaatto='.
				wp_create_nonce('editatto').'">
					<span class="dashicons dashicons-edit" title="Modifica atto"></span>
				</a>';
				$actions['allegati'] ='<a href="?page=atti&amp;action=allegati-atto&amp;id='.$item->IdAtto.'&amp;allegatoatto='.
				wp_create_nonce('gestallegatiatto').'">
					<span class="dashicons dashicons-upload" title="Allegati"></span>
				</a>';
				if (current_user_can('editore_atti_albo')){
				$actions['pubblica'] ='<a href="?page=atti&amp;action=approva-atto&amp;id='.$item->IdAtto.'"  >
	<span class="dashicons dashicons-share-alt" title="Pubblica atto"></span>
					</a>';
				}         
            }else{
                $Msg=($Annullato?'<span style="color: red;font-weight: bold;">Annullato</span>':'<span style="color: green;font-weight: bold;">Pubblicato</span>');    
                }
                break;
	}
	if( !$Scaduto and $Annullato=='' and ($this->stato_atti=="Correnti" || $this->stato_atti=="Cerca") and current_user_can('editore_atti_albo')){
		$actions['annulla'] ='<span class="trash"><a class="annullaatto" href="?page=atti&amp;action=annullamento-atto&amp;id='.$item->IdAtto.'">
				<span class="dashicons dashicons-dismiss" title="Annulla atto"></span>
			</a></span>';
	}
	return sprintf('%1$s %2$s',$Msg,$this->row_actions($actions));
  }  
  function column_Ente($item) { 
  	$Ente=ap_get_ente($item->Ente);
  	if($Ente===FALSE){
		return "<spam style=\"color:red;\">Ente non definito</spam>";
	}else{
    return stripslashes($Ente->Nome); 	
	}
  }  
   function column_MetaDati($item) { 
	$MetaDati=ap_get_meta_atto($item->IdAtto);
	$Meta="";
	if($MetaDati!==FALSE){
		foreach($MetaDati as $Metadato){
			$Meta.=$Metadato->Meta."=".$Metadato->Value."<br />";
		}
		$Meta=substr($Meta,0,-6);
	}
    return stripslashes($Meta); 
  }  
 function column_Numero($item) { 
    return $this->AzioneDefault.$item->Numero."/".$item->Anno."</a>"; 
  }  
  function column_Data($item) { 
    return ap_VisualizzaData($item->Data); 
  }  
  function column_Riferimento($item) { 
    return $this->AzioneDefault.stripslashes($item->Riferimento)."</a>"; 
  }  
  function column_Oggetto($item) { 
  	$Oggetto=stripslashes($item->Oggetto);
  	if ( strlen( $Oggetto ) > 120 ) {
			$Oggetto = substr( $Oggetto, 0, 120 ) . " ...";
		}
	return $this->AzioneDefault.$Oggetto."</a>"; 
  }   
  function column_validita($item) { 
    return ap_VisualizzaData($item->DataInizio)."<br />".ap_VisualizzaData($item->DataFine); 
  }  
  function column_Idcategoria($item) {
	if ($item->IdCategoria>0){
		$Cate=ap_get_categoria($item->IdCategoria);
		return $Cate[0]->Nome;
	}else{
		return "Non Definita";
	}
  }   
  function column_dataoblio($item) { 
    return ap_VisualizzaData($item->DataOblio); 
  }  

// Definire la nuova funzione per indicare le
// azioni che devo essere presenti sul menu a tendina

	function get_bulk_actions() {
	  if (isset($_GET['stato_atti']) And $_GET['stato_atti']=="Eliminare" And current_user_can('editore_atti_albo'))	
	  	return array('delete_bulk_atti' => 'Elimina');
	}

	// Funzione per la prima colonna che non sarà più il 
	// numero di tessera ma un campo di checkbox per la selezione

	function column_cb($item) {
	  if (current_user_can('editore_atti_albo')){
		  return sprintf('<input type="checkbox" name="IdAtto[]" value="%s"/>',$item->IdAtto);
	  }
	}
 
}

if(isset($_REQUEST['action'])){
	switch ($_REQUEST['action']){
		case "metadati-atto":
			Gestione_Metadati((int)$_REQUEST['id']);
			break;
		case "logatto" :
			echo json_encode(CreaLog(1,$IdAtto,0));
			die();
			break;
		case "view-atto" :
			View_atto((int)$_REQUEST['id']);
			break;
		case "annullamento-atto" :
			Annulla_Atto((int)$_REQUEST['id']);
			break;
		case "new-atto" :
			Nuovo_atto();
			break;
		case "edit-atto" :
			if (!isset($_REQUEST['modificaatto'])) {
				Go_Atti();
				break;	
			}
			if (!wp_verify_nonce($_REQUEST['modificaatto'],'editatto')){
				Go_Atti();
				break;
			} 		
			Edit_atto((int)$_REQUEST['id']);
			break;
		case "pubblica-atto":
			Lista_Atti(ap_approva_atto((int)$_REQUEST['id']));
			break;
		case "setta-anno":
			update_option('opt_AP_AnnoProgressivo',date("Y") );
		  	update_option('opt_AP_NumeroProgressivo',1 );
			PreApprovazione((int)$_REQUEST['id'],"Anno Albo settato a ".date("Y")." Numero prograssivo settato a 0");
			break;
		case "approva-atto" :
			$ret="";
			if (isset($_REQUEST['apa'])){
				$ret=ap_update_selettivo_atto((int)$_REQUEST['id'],array('Anno' => $_REQUEST['apa']),array('%s'),"Modifica in Approvazione\n");
			}
			if (isset($_REQUEST['pnp'])){
				update_option( 'opt_AP_NumeroProgressivo', (int)$_REQUEST['pnp']);
			}
			if (isset($_REQUEST['udi'])){
				$ret=ap_update_selettivo_atto((int)$_REQUEST['id'],array('DataInizio' => $_REQUEST['udi']),array('%s'),"Modifica in Approvazione\n");	
			}
			if (isset($_REQUEST['udf'])){
				$ret=ap_update_selettivo_atto((int)$_REQUEST['id'],array('DataFine' => $_REQUEST['udf']),array('%s'),"Modifica in Approvazione\n");	
			}
			if (isset($_REQUEST['udo'])){
				$ret=ap_update_selettivo_atto((int)$_REQUEST['id'],array('DataOblio' => $_REQUEST['udo']),array('%s'),"Modifica in Approvazione\n");	
			}
			if(isset($_REQUEST['id']))
				$id=(int)$_REQUEST['id'];
			else
				$id=0;
			PreApprovazione($id,$ret);
			break;
		case "allegati-atto" :
			if (!isset($_REQUEST['allegatoatto'])) {
				Lista_Atti("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione &egrave; stata annullata");
				break;	
			}
			if (!wp_verify_nonce($_REQUEST['allegatoatto'],'gestallegatiatto')){
				Lista_Atti("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione &egrave; stata annullata");
				break;
			} 		
			Allegati_atto((int)$_REQUEST['id'],(isset($_REQUEST['messaggio'])?$_REQUEST['messaggio']:""));
			break;
		case "edit-allegato-atto" :
			if (!isset($_REQUEST['modificaallegatoatto'])) {
				Lista_Atti("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione &egrave; stata annullata");
				break;	
			}
			if (!wp_verify_nonce($_REQUEST['modificaallegatoatto'],'editallegatoatto')){
				Lista_Atti("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione &egrave; stata annullata");
				break;
			} 				
			Allegati_atto((int)$_REQUEST['id'],(isset($_REQUEST['messaggio'])?$_REQUEST['messaggio']:""),(int)$_REQUEST['idAlle']);
			break;
		case "UpAllegati":
			include_once ( dirname (__FILE__) . '/allegati_multi.php' );
			break;
		case "AssAllegati":
			include_once ( dirname (__FILE__) . '/allegati.php' );
			break;
		default:
			if(isset($_REQUEST['message'])){
				if (is_numeric($_REQUEST['message']))
					$message=$_REQUEST['message'];
				elseif(strlen($_REQUEST['message'])>0)
						$message=$_REQUEST['message'];
				else $message="";
			}else
				$message="";

			Lista_Atti($message);
			break;
	}	
}else{
	if(isset($_REQUEST['message'])){
		if (is_numeric($_REQUEST['message']))
			$message=$_REQUEST['message'];
		elseif(strlen($_REQUEST['message'])>0)
				$message=urldecode($_REQUEST['message']);
	}else{
		$message="";
	}
Lista_Atti($message);
}

unset($_REQUEST['action']);

function Gestione_Metadati($IdAtto){
	global $AP_OnLine;
	$risultato=ap_get_atto($IdAtto);
	$risultato=$risultato[0];
	$risultatocategoria=ap_get_categoria($risultato->IdCategoria);
	$risultatocategoria=$risultatocategoria[0];
	$NomeEnte=ap_get_ente($risultato->Ente);
	$NomeEnte=stripslashes($NomeEnte->Nome);
?>
<div class="wrap nosubsub">
	<div class="HeadPage">
		<h2 class="wp-heading-inline"><span class="dashicons dashicons-portfolio"></span> Atti</h2>
		<a href="<?php echo site_url();?>/wp-admin/admin.php?page=atti&stato_atti=<?php echo filter_input(INPUT_GET,"stato_atti");?>" class="add-new-h2 tornaindietro">Torna indietro</a>
		<h3>Dati Atto</h3>	
	</div>
	<div class="clear"><br /></div>
	<div id="col-container">
		<div id="col-right">
			<form id="memo_metadati_atto" method="post" action="?page=atti" class="validate">
			<input type="hidden" name="action" value="memo_metadati_atto" />
			<input type="hidden" name="id" value="<?php echo $IdAtto;?>" />
			<input type="hidden" name="stato_atti" value="<?php echo filter_input(INPUT_GET,"stato_atti")?>" />
			<input type="hidden" name="mmda" value="<?php echo wp_create_nonce('editmetadatiattoatto')?>" />

			<div class="col-wrap postbox" style="padding:0 10px 10px 10px;margin-left:10px;" id="MetaDati">
			<h2 class="hndle"><span>Meta Dati Personalizzati</span> <button type="button" id="AddMeta" class="setta-def-data">Aggiungi Meta Valore</button></h2>
				<div style="display:none;" id="newMeta">
					<label for="listaAttiMeta">Meta già codificati</label> <?php echo ap_get_elenco_attimeta("Select","listaAttiMeta","ListaAttiMeta","Si");?>
					<label for="newMetaName">Nome Meta</label> <input name="newMetaName" id="newMetaName"/>
					<label for="newValue">Valore Meta</label> <input name="newValue" id="newValue">
					<button type="button"class="setta-def-data" id="AddNewMeta">Aggiungi</button> <button type="button"class="setta-def-data" id="UndoNewMedia">Anulla</button>
				</div>
<?php				echo ap_get_elenco_attimeta("Div","","","",$IdAtto);			?>
			</div>
			<div class="col-wrap postbox" style="padding:10px;margin-left:10px;">
				<input type="submit" name="AggiornaMetaDati" id="AggiornaMetaDati" style="margin:auto;" class="button button-primary button-large" value="Memorizza Modifiche MetaDati Atto" />
			</form>
			</div>
		</div><!-- /post-body-content -->
	</div>
	<div id="col-left">
		<div class="col-wrap postbox" style="padding:0 10px 10px 10px;margin-left:10px;">
			<h3>Dati atto</h3>
			<hr />
			<table class="widefat" style="border:0;">
				<tbody id="dati-atto">
				<tr>
					<th style="width:20%;">Ente emittente</th>
					<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;"><?php echo $NomeEnte;?></td>
				</tr>
<?php
		if($risultato->DataAnnullamento!='0000-00-00')		
			echo '		<tr>
				<th style="width:20%;">Data Annullamento</th>
				<td style="font-size:14px;font-weight: bold;color: Red;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataAnnullamento).'</td>
			</tr>
	    	<tr>
				<th style="width:20%;">Motivo Annullamento</th>
				<td style="font-size:14px;font-weight: bold;color: Red;vertical-align:top;">'.stripslashes($risultato->MotivoAnnullamento).'</td>
			</tr>';
		echo '		<tr>
				<th style="width:20%;">Numero Albo</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.$risultato->Numero."/".$risultato->Anno.'</td>
			</tr>
			<tr>
				<th>Data</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->Data).'</td>
			</tr>
			<tr>
				<th>Codice di Riferimento</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Riferimento).'</td>
			</tr>
			<tr>
				<th>Oggetto</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Oggetto).'</td>
			</tr>
			<tr>
				<th>Data inizio Pubblicazione</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataInizio).'</td>
			</tr>
			<tr>
				<th>Data fine Pubblicazione</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataFine).'</td>
			</tr>
			<tr>
				<th>Data Oblio</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataOblio).'</td>
			</tr>
			<tr>
				<th>Note</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Informazioni).'</td>
			</tr>
			<tr>
				<th>Categoria</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultatocategoria->Nome).'</td>
			</tr>
				<tr>
					<th>Soggetti</th>
						<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">	
					<ul>';
	$Soggetti=unserialize($risultato->Soggetti);
	$Soggetti=ap_get_alcuni_soggetti_ruolo(implode(",",$Soggetti));
	foreach($Soggetti as $Soggetto){
		echo "
			<li><strong>".ap_get_Funzione_Responsabile($Soggetto->Funzione,"Descrizione")."</strong> ".$Soggetto->Nome." ".$Soggetto->Cognome." 
			</li>";
	}
echo'				
				</ul>
					</td>
				</tr>
		    </tbody>
		</table>
	</div>';
echo '<div class="postbox" style="padding:0 10px 10px 10px;margin-left:10px;">
	<h3>Allegati</h3>
	<div class="Visalbo">';
$allegati=ap_get_all_allegati_atto($IdAtto);
$TipidiFiles=ap_get_tipidifiles();
foreach ($allegati as $allegato) {
	$Estensione=ap_ExtensionType($allegato->Allegato);	
	echo '<div style="border: thin dashed;font-size: 1em;">
			<div style="float: left;display: inline;width: 40px;height: 40px;padding-top:5px;padding-left:5px;">';
	if(isset($allegato->TipoFile) and $allegato->TipoFile!="" and ap_isExtensioType($allegato->TipoFile)){
		$Estensione=ap_ExtensionType($allegato->TipoFile);	
		echo '<img src="'.$TipidiFiles[$Estensione]['Icona'].'" alt="'.$TipidiFiles[$Estensione]['Descrizione'].'" height="30" width="30"/>';
	}else{
		echo '<img src="'.$TipidiFiles[strtolower($Estensione)]['Icona'].'" alt="'.$TipidiFiles[strtolower($Estensione)]['Descrizione'].'" height="30" width="30"allegato/>';
	}
	echo'</div>
			<div style="margin-top:0;">
				<p style="margin-top:0;">'.strip_tags($allegato->TitoloAllegato).' <br />';
			if (is_file($allegato->Allegato))
				echo '        <a href="'.ap_DaPath_a_URL($allegato->Allegato).'" >'. basename( $allegato->Allegato).'</a> ('.ap_Formato_Dimensione_File(filesize($allegato->Allegato)).')<br />'.htmlspecialchars_decode($TipidiFiles[strtolower($Estensione)]['Verifica']);
			else
				echo basename( $allegato->Allegato)." File non trovato, il file &egrave; stato cancellato o spostato!";
echo'				</p>
			</div>
			<div style="clear:both;"></div>
		</div>';
	}
echo '</div>
	</div>

</div>';	
}

function PreApprovazione($id,$ret=''){
global $wpdb;
if (!current_user_can('editore_atti_albo')){
	echo '<div id="message" class="updated"><p>Questa Operazione non ti &egrave; consentita, operazione di pertinenza dell\'amministratore dell\' Albo o del redattore</p></div>';
	return;
}
if ($ret!=""){
	$ret=str_replace("%%br%%","<br />",$ret);
}
	$NumeroDaDb=ap_get_last_num_anno(date("Y"));
	$atto=ap_get_atto($id);
	$atto=$atto[0];
	//$dif=ap_datediff("d",ap_cvdate($atto->DataInizio),ap_cvdate($atto->DataFine));
	$NumeroOpzione=get_option('opt_AP_NumeroProgressivo');
	$NumAttiPubblicati=ap_get_all_atti(9,0,0,0,"",0,0,"",0,0,TRUE);
	if($NumAttiPubblicati==0) 
		$AppPostMigrazione=" <span style='color:red;'>Validato perchè primo atto dopo la MIGRAZIONE </span>";	 else
		$AppPostMigrazione="";
echo'
<div class="wrap">
	<div class="HeadPage">
		<h2 class="wp-heading-inline"><span class="dashicons dashicons-portfolio"></span> Atti</h2>
		<a href="'.site_url().'/wp-admin/admin.php?page=atti" class="add-new-h2 tornaindietro">Torna indietro</a>';
	if ( $ret!="" ) {
		echo '<div id="message" class="updated"><p>'.$ret.'</p></div>';
	}
	echo '
		<h3>Approvazione Atto</h3>	
	</div>
	<br class="clear" />';
if(get_option('opt_AP_AnnoProgressivo')!=date("Y")){
	echo '<div style="border: medium groove Blue;margin-top:10px;">
			<div style="float:none;width:200px;margin-left:auto;margin-right:auto;">
				<form id="agg_anno_progressivo" method="post" action="?page=atti">
				<input type="hidden" name="action" value="setta-anno" />
				<input type="hidden" name="id" value="'.$id.'" />
				<input type="submit" name="submit" id="submit" class="button" value="Aggiorna Anno Albo ed Azzera numero Progressivo"  />
				</form>
			</div>
		</div>';
}else
{
echo'<br />
<table class="widefat">
	<thead>	
	<tr>
		<th colspan="2" style="text-align:center;font-size:2em;">Informazioni</th>
		<th>Stato</th>
		<th>Operazioni</th>
	</tr>
	</thead>
    <tbody id="dati-atto">
	<tr>
		<td>Anno Atto</td>
		<td>'.$atto->Anno.'</td>';
		if ($atto->Anno==date("Y")){
		 	$Passato=true;
			echo '<td colspan="2">Ok</td>';
		}else{
		 	$Passato=false;
			echo '<td>Verificata incongruenza, bisogna rimediare prima di proseguire</td>
			      <td><a href="?page=atti&amp;action=approva-atto&amp;id='.$id.'&amp;apa='.date("Y").'" class="add-new-h2">Imposta Anno Pubblicazione a '.date("Y").'</td>';
		}
		echo '</tr>';
		if($Passato){
			echo '<tr>
			<td>Numero Atto</td>
			<td>da Parametri '.get_option('opt_AP_NumeroProgressivo').' Progressivo da ultima pubblicazione '.$NumeroDaDb.$AppPostMigrazione.'</td>';
			if (($NumeroDaDb==$NumeroOpzione) Or $NumAttiPubblicati==0){
			 	$Passato=true;
				echo '<td colspan="2">Ok</td>';
			}else{
			 	$Passato=false;
				echo '<td>Verificata incongruenza, bisogna rimediare prima di proseguire</td>
				      <td><a href="?page=atti&amp;action=approva-atto&amp;id='.$id.'&amp;pnp='.$NumeroDaDb.'" class="add-new-h2">Imposta Parametro a '.$NumeroDaDb.'</td>';
			}
			echo '</tr>';
		}
		if($Passato){
			echo '<tr>
					<td>Data Inizio Pubblicazione</td>
					<td>'.$atto->DataInizio.'</td>';
			if($atto->DataInizio==ap_oggi()){
				$Passato=true;
				echo '<td colspan="2">Ok</td>';
			}else{
	 			$Passato=false;
	   			echo '<td>Aggiornare la data di Inizio Pubblicazione</td>
			      <td><a href="?page=atti&amp;action=approva-atto&amp;id='.$id.'&amp;udi='.ap_oggi().'" class="add-new-h2">Aggiorna a '.ap_oggi().'</td>';
			}
			echo "</tr>";
		}
		if($Passato){
 			$categoria=ap_get_categoria($atto->IdCategoria);
 			$incrementoStandard=$categoria[0]->Giorni;
 			$newDataFine=ap_DateAdd($atto->DataInizio,$incrementoStandard);
 			$differenza=ap_datediff("d", $atto->DataInizio, $atto->DataFine);
			$differenza=($differenza==-1) ? 0 : $differenza;
			echo '<tr>
					<td>Data Fine Pubblicazione</td>
					<td>'.$atto->DataFine.' Giorni Pubblicazione Atto '.$differenza .' Giorni Pubblicazione standard Categoria '.$categoria[0]->Giorni.'</td>';
				//	echo $atto->DataFine.' '.$atto->DataInizio. ' '.SeDate("<=",$atto->DataFine,$atto->DataInizio);
			if(ap_SeDate(">=",$atto->DataFine,$atto->DataInizio)){
				$Passato=true;
				if (ap_datediff("d", $atto->DataInizio, $atto->DataFine)== $categoria[0]->Giorni){
					echo '<td colspan="2">Ok</td>';
				}else{
					echo '<td>Ok</td>';
					echo '<td><a href="?page=atti&amp;action=approva-atto&amp;id='.$id.'&amp;udf='.$newDataFine.'" class="add-new-h2">Aggiorna a '.$newDataFine.'</a></td>';
				}
			}else{
	 			$Passato=false;
	   			echo '<td>Aggiornare la data di Fine Pubblicazione</td>
			      <td><a href="?page=atti&amp;action=approva-atto&amp;id='.$id.'&amp;udf='.$newDataFine.'" class="add-new-h2">Aggiorna a '.$newDataFine.'</a></td>';
			}
			echo '</tr>';
		}

		if($Passato){
  			$incrementoStandard=get_option('opt_AP_GiorniOblio');
 			$DataOblioStandard=(date("Y")+6)."-01-01";
 			//echo $atto->DataInizio."   -  ".$incrementoStandard;
			echo '<tr>
					<td>Data Oblio</td>
					<td> Data Oblio impostata '.$atto->DataOblio.' - Data Oblio da Decreto n. 33/2013 art. 8 '.$DataOblioStandard.'</td>';
				//	echo $atto->DataFine.' '.$atto->DataInizio. ' '.SeDate("<=",$atto->DataFine,$atto->DataInizio);
			if(ap_SeDate("=",$atto->DataOblio,$DataOblioStandard)){
				$Passato=true;
				echo '<td colspan="2">Ok</td>';
			}else{
				echo '<td>Ok</td>';
				echo '<td><a href="?page=atti&amp;action=approva-atto&amp;id='.$id.'&amp;udo='.$DataOblioStandard.'" class="add-new-h2">Aggiorna a '.$DataOblioStandard.'</a></td>';
			}
		echo '</tr>';
		}
		if($Passato){
 			$numAllegati=ap_get_num_allegati($id);
			echo '<tr>
					<td>Allegati</td>
					<td>N. '.$numAllegati.'</td>';
			if($numAllegati>0){
				$Passato=true;
					echo '<td colspan="2">Ok</td>';
				}else{
					$Passato=false;
					echo '<td>Da revisionare</td>
					      <td><a href="?page=atti&amp;id='.$id.'&amp;action=UpAllegati&amp;ref=approva-atto" class="add-new-h2">Inserisci Allegato</a></td>';
				}
			echo '</tr>';
		}
echo '</tbody>
	</table>';
if ($Passato){
echo'
<div style="border: medium groove Blue;margin-top:10px;">
	<div style="float:none;width:200px;margin-left:auto;margin-right:auto;">
		<form id="approva-atto" method="post" action="?page=atti">
		<input type="hidden" name="action" value="pubblica-atto" />
		<input type="hidden" name="id" value="'.$id.'" />
		<input type="hidden" name="stato_atti" value="Correnti" />
		<input type="submit" name="submit" id="submit" class="button" value="Pubblica Atto"  />
		</form>
	</div>
</div>
<div id="col-right">
<div class="col-wrap">
<h3>Allegati</h3>';
$righe=ap_get_all_allegati_atto($id);
echo'
	<table class="widefat">
	    <thead>
		<tr>
			<th style="font-size:2em;">Operazioni</th>
			<th style="font-size:2em;">Allegato</th>
			<th style="font-size:2em;">File</th>
		</tr>
	    </thead>
	    <tbody id="righe-log">';
foreach ($righe as $riga) {
	echo '<tr>
			<td>	
					<a href="'.ap_DaPath_a_URL($riga->Allegato).'" target="_parent">
						<span class="dashicons dashicons-search" title="Visualizza dati atto"></span>
					</a>
			</td>
			<td >'.$riga->TitoloAllegato.'</td>
			<td >'. basename( $riga->Allegato).'</td>
		</tr>';
}
echo '    </tbody>
	</table>
</div>
</div>
<div id="col-left">
<div class="col-wrap">
<h3>Dati Atto</h3>
	<table class="widefat">
	    <thead>
		<tr>
			<th colspan="2" style="text-align:center;font-size:2em;">Dati atto</th>
		</tr>
	    </thead>
	    <tbody id="dati-atto">
		<tr>
			<th style="width:20%;">Numero Albo</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.$atto->Numero."/".$atto->Anno.'</td>
		</tr>
		<tr>
			<th>Data</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.$atto->Data.'</td>
		</tr>
		<tr>
			<th>Codice di Riferimento</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($atto->Riferimento).'</td>
		</tr>
		<tr>
			<th>Oggetto</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($atto->Oggetto).'</td>
		</tr>
		<tr>
			<th>Data inizio Pubblicazione</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.$atto->DataInizio.'</td>
		</tr>
		<tr>
			<th>Data fine Pubblicazione</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.$atto->DataFine.'</td>
		</tr>
		<tr>
			<th>Note</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($atto->Informazioni).'</td>
		</tr>
		<tr>
			<th>Categoria</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($categoria[0]->Nome).'</td>
		</tr>
	    </tbody>
	</table></div>
</div>';
}
}
echo '</div>';
}


function Nuovo_atto(){
/*	$risultatocategoria=ap_get_categoria($risultato->IdCategoria);
	$risultatocategoria=$risultatocategoria[0];*/
	if (isset($_REQUEST['Data']) And $_REQUEST['Data']!="")
		$dataCorrente=$_REQUEST['Data'];
	else
		$dataCorrente=date("d/m/Y");
	if (isset($_REQUEST['Ente']))
		$defEnte=$_REQUEST['Ente'];
	else
		$defEnte=get_option('opt_AP_Ente');
	if (isset($_REQUEST['Riferimento']) )
		$Riferimento=htmlentities($_REQUEST['Riferimento']);
	else
		$Riferimento="";
	if (isset($_REQUEST['Oggetto']))
		$Oggetto=htmlentities($_REQUEST['Oggetto']);
	else
		$Oggetto="";
/*	if ($_REQUEST['DataInizio'])
		$DataI=$_REQUEST['DataInizio'];
	else*/
	$DataI=date("d/m/Y");
	if (isset($_REQUEST['DataFine']))
		$DataF=htmlentities($_REQUEST['DataFine']);
	else
		$DataF=date("d/m/Y");
	if (isset($_REQUEST['DataOblio']))
		$DataO=htmlentities($_REQUEST['DataOblio']);
	else
		$DataO=ap_VisualizzaData((date("Y")+6)."-01-01");
	if (isset($_REQUEST['Note']))
		$Note=$_REQUEST['Note'];
	else	
		$Note="";
	if (isset($_REQUEST['Categoria']))
		$Categoria=$_REQUEST['Categoria'];
	else
		$Categoria=0;
	if (isset($_REQUEST['Responsabile']))
		$Responsabile=$_REQUEST['Responsabile'];
	else{
		$Resp=ap_get_responsabili();
		if (count($Resp)>0)
			$Responsabile=$Resp[0]->IdResponsabile;
		else
			$Responsabile=0;	
	}
	$DefaultSoggetti=get_option('opt_AP_DefaultSoggetti',
								array("RP"=>0,
	  								  "RB"=>0,
	  								  "AM"=>0));
	if(!is_array($DefaultSoggetti)){
		$DefaultSoggetti=json_decode($DefaultSoggetti,TRUE);
	}
$DataOblioStandard=(date("Y")+6)."-01-01";		
?>
<div id="errori" title="Validazione Dati" style="display:none">
  <h3>Lista Campi con Errori:</h3><p id="ElencoCampiConErrori"></p><p style='color:red;font-weight: bold;'>Correggere gli errori per continuare</p>
</div>

<div class="wrap">
	<div class="HeadPage">
		<h2 class="wp-heading-inline"><span class="dashicons dashicons-portfolio"></span> Atti</h2>
		<a href="<?php echo site_url().'/wp-admin/admin.php?page=atti';?>" class="add-new-h2 tornaindietro">Torna indietro</a>
		<div class="Obbligatori">
		<span style="color:red;font-weight: bold;">*</span> i campi contrassegnati dall'asterisco sono <strong>obbligatori</strong>
		</div>
		<h3 >Nuovo Atto</h3>	
	</div>
		<form id="addatto" method="post" action="?page=atti" class="validate">
		<input type="hidden" name="action" value="add-atto" />
		<input type="hidden" name="id" value="<?php echo(int)(isset($_REQUEST['id'])?$_REQUEST['id']:0);?>" />
		<input type="hidden" name="nuovoatto" value="<?php echo wp_create_nonce('nuovoatto')?>" />

	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div id="riferimentodiv">
					<h2>Riferimento<span style="color:red;font-weight: bold;">*</span></h2>
					<textarea name="Riferimento" id="riferimento-atto" rows="2" cols="255"  class="richiesto" style="width: 100%"><?php echo stripslashes($Riferimento);?></textarea>
				<label for="Riferimento" style="font-style: italic;">Codice di riferimento dell'atto, es. N. Protocollo </label>
				</div><!-- /riferimentodiv -->
				<div id="riferimentowrap">
					<h2>Oggetto<span style="color:red;font-weight: bold;">*</span></h2>
					<textarea name="Oggetto" id="oggetto-atto" rows="10" cols="255"  class="richiesto" style="width: 100%"><?php echo stripslashes($Oggetto);?></textarea>
				<label for="Riferimento" style="font-style: italic;">Descrizione sintetica dell'atto </label>
				</div><!-- /riferimentowrap -->

				<div id="notewrap">
					<h2>Note</h2>
					<div id="note-wrap">
						<?php wp_editor( stripslashes($Note), 'note_txt',
									array('wpautop'=>true,
										  'textarea_name' => 'Note',
										  'textarea_rows' => 10,
										  'teeny' => TRUE,
										  'media_buttons' => false)
										)?>
						<span style="font-style: italic;font-size: 0.8em;">Eventuali note a corredo dell'atto</span>
					</div>
					</div><!-- /notewrap -->
				<div class="notewrap postbox" id="MetaDati">
				<h2 class='hndle'><span>Meta Dati Personalizzati</span> <button type="button" id="AddMeta" class="setta-def-data">Aggiungi Meta Valore</button></h2>
					<div style="display:none;" id="newMeta">
						<label for="listaAttiMeta">Meta già codificati</label> <?php echo ap_get_elenco_attimeta("Select","listaAttiMeta","ListaAttiMeta","Si");?>
						<label for="newMetaName">Nome Meta</label> <input name="newMetaName" id="newMetaName"/>
						<label for="newValue">Valore Meta</label> <input name="newValue" id="newValue">
						<button type="button"class="setta-def-data" id="AddNewMeta">Aggiungi</button> <button type="button"class="setta-def-data" id="UndoNewMedia">Anulla</button>
					</div>
<?php				//echo ap_get_elenco_attimeta("Div");			?>
				</div>
			</div><!-- /post-body-content -->

		<div id="postbox-container-1" class="postbox-container">
			<div id="postimagediv" class="postbox " >
				<h2 class='hndle'><span>Memorizza</span></h2>
				<div class="inside">
					<p>Numero Albo: 
						<span style="font-weight: bold;">00000/<?php echo date("Y");?></span>
					</p>
					<p class="hide-if-no-js">
					<input type="submit" name="MemorizzaDati" id="MemorizzaDati" class="button button-primary button-large" value="Memorizza Atto">
					</p>
				</div>
			</div>
			<div id="datediv" class="postbox " >
				<h2 class='hndle'><span>Date</span></h2>
				<div class="inside">
					<p>Data di codifica dell'atto:
						<input name="Data" type="text" id="CalendarioMO" value="<?php echo ap_VisualizzaData($dataCorrente);?>" maxlength="10" size="10" />					
					</p>
					<p><abbr title="Data in cui inizia a validità legale dell'atto. Viene impostata automaticamente in fase di pubblicazione">Data inizio Pubblicazione</abbr>:
						<input name="DataInizio" type="hidden" value="<?php echo $DataI;?>" />
					</p>
					<p><abbr title="Data fine validità legale dell'atto">Data fine Pubblicazione</abbr>:
						<input name="DataFine" id="Calendario3" type="text" value="<?php echo $DataF;?>" maxlength="10" size="10" />		
					</p>		
					<p><abbr title="Data in cui l'atto viene eliminato dall'archivio, in base al Decreto n. 33/2013 art.8:<br />5 anni, decorrenti dal 1° gennaio dell'anno successivo a quello
da cui decorre l'obbligo di pubblicazione, e comunque fino a che gli atti pubblicati producono i loro effetti,
fatti salvi i diversi termini previsti dalla normativa in materia di trattamento dei dati personali e quanto
previsto dagli articoli 14, comma 2, e 15, comma 4">Data Oblio</abbr>:
						<input name="DataOblio" id="Calendario4" type="text" value="<?php echo $DataO;?>" maxlength="10" size="10" /><button type="button" id="setta-def-data-o" class="setta-def-data" name="<?php echo ap_VisualizzaData($DataOblioStandard);?>" style="margin-top: 5px;margin-left:10px;"> Aggiorna a <?php echo ap_VisualizzaData($DataOblioStandard);?></button>	
					</p>				
				</div>
			</div>
			<div id="metadiv" class="postbox " >
				<h2 class='hndle'><span>Meta dati</span></h2>
				<div class="inside">
					<p><abbr title="Ente che pubblica l'atto; potrebbe essere diverso dall'ente titolare del sito web se la pubblicazione avviene per conto di altro ente">Ente<span style="color:red;font-weight: bold;">*</span></abbr>: 
						<?php echo ap_get_dropdown_enti('Ente','Ente','postform maxdime richiesto ValValue(>-1)','',$defEnte);?>
					</p>
					<p><abbr title="Categoria in cui viene collocato l'atto, questo sistema permette di ragguppare gli oggetti in base alla lor natura">Categoria<span style="color:red;font-weight: bold;">*</span></abbr>:
						<?php echo ap_get_dropdown_categorie('Categoria','Categoria','postform maxdime richiesto ValValue(>0)','',$Categoria);?>					
					</p>
				</div>
			</div>
			<div id="metadiv" class="postbox " >
				<h2 class='hndle'><span>Soggetti</span></h2>
				<div class="inside">
					<p>In questo spazio bisogna codificare i soggetti che sono coinvolti in questo atto possono essere specificati più soggetti
					</p>
					<ul>
<?php
		$Ana_Soggetti=ap_get_responsabili();
		foreach($Ana_Soggetti as $Soggetto){
			$Sel="";
			if(is_array($DefaultSoggetti)And in_array($Soggetto->IdResponsabile,$DefaultSoggetti)){
				$Sel=" checked ";
			}
			echo "
			<li>
				<input type=\"checkbox\" name=\"Soggetto[]\" value=\"$Soggetto->IdResponsabile\"  $Sel/>".$Soggetto->Cognome." ".$Soggetto->Nome." <strong><em>".ap_get_Funzione_Responsabile($Soggetto->Funzione,"Descrizione")."</em></strong>
			</li>
			";
		}
?>						
					</ul>
				</div>
			</div>
	</div>
	</div><!-- /post-body-content -->	
	</div>
	</form>
</div>
<?php
}


function Edit_atto($id){
$atto=ap_get_atto($id);
$atto=$atto[0];
$DataOblioStandard=(date("Y")+6)."-01-01";
?>
<div id="errori" title="Validazione Dati" style="display:none">
  <h3>Lista Campi con Errori:</h3><p id="ElencoCampiConErrori"></p><p style='color:red;font-weight: bold;'>Correggere gli errori per continuare</p>
</div>

<div class="wrap">
	<div class="HeadPage">
		<h2 class="wp-heading-inline"><span class="dashicons dashicons-portfolio"></span> Atti</h2>
		<a href="<?php echo site_url().'/wp-admin/admin.php?page=atti';?>" class="add-new-h2 tornaindietro">Torna indietro</a>
		<div class="Obbligatori">
		<span style="color:red;font-weight: bold;">*</span> i campi contrassegnati dall'asterisco sono <strong>obbligatori</strong>
		</div>
		<h3 >Modifica Atto</h3>	
	</div>

	<form id="addatto" method="post" action="?page=atti" class="validate">
		<input type="hidden" name="action" value="memo-atto" />
		<input type="hidden" name="id" value="<?php echo (int)$_REQUEST['id'];?>" />
		<input type="hidden" name="modificaatto" value="<?php echo wp_create_nonce('editatto')?>" />

	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div id="riferimentodiv">
					<h2>Riferimento<span style="color:red;font-weight: bold;">*</span></h2>
					<textarea name="Riferimento" id="riferimento-atto" rows="2" cols="255"  class="richiesto" style="width: 100%"><?php echo stripslashes($atto->Riferimento);?></textarea>
				<label for="Riferimento" style="font-style: italic;">Codice di riferimento dell'atto, es. N. Protocollo </label>
				</div><!-- /riferimentodiv -->
				<div id="riferimentowrap">
					<h2>Oggetto<span style="color:red;font-weight: bold;">*</span></h2>
					<textarea name="Oggetto" id="oggetto-atto" rows="10" cols="255"  class="richiesto" style="width: 100%"><?php echo stripslashes($atto->Oggetto);?></textarea>
				<label for="Riferimento" style="font-style: italic;">Descrizione sintetica dell'atto </label>
				</div><!-- /riferimentowrap -->

				<div id="notewrap">
					<h2>Note</h2>
					<div id="note-wrap">
						<?php wp_editor( stripslashes($atto->Informazioni), 'note_txt',
									array('wpautop'=>true,
										  'textarea_name' => 'Note',
										  'textarea_rows' => 10,
										  'teeny' => TRUE,
										  'media_buttons' => false)
										)?>
						<span style="font-style: italic;font-size: 0.8em;">Eventuali note a corredo dell'atto</span>
					</div>
					</div><!-- /notewrap -->
				<div class="notewrap postbox" id="MetaDati">
				<h2 class='hndle'><span>Meta Dati Personalizzati</span> <button type="button" id="AddMeta" class="setta-def-data">Aggiungi Meta Valore</button></h2>
					<div style="display:none;" id="newMeta">
						<label for="listaAttiMeta">Meta già codificati</label> <?php echo ap_get_elenco_attimeta("Select","listaAttiMeta","ListaAttiMeta","Si");?>
						<label for="newMetaName">Nome Meta</label> <input name="newMetaName" id="newMetaName"/>
						<label for="newValue">Valore Meta</label> <input name="newValue" id="newValue">
						<button type="button"class="setta-def-data" id="AddNewMeta">Aggiungi</button> <button type="button"class="setta-def-data" id="UndoNewMedia">Anulla</button>
					</div>
<?php				echo ap_get_elenco_attimeta("Div","","","",$id);			?>
				</div>
			</div><!-- /post-body-content -->

		<div id="postbox-container-1" class="postbox-container">
			<div id="postimagediv" class="postbox " >
				<h2 class='hndle'><span>Memorizza</span></h2>
				<div class="inside">
					<p>Numero Albo: 
						<span style="font-weight: bold;">00000/<?php echo $atto->Anno;?></span>
					</p>
					<p class="hide-if-no-js">
						<input type="submit" name="MemorizzaDati" id="MemorizzaDati" class="button button-primary button-large" value="Memorizza Modifiche Atto" />
					</p>
				</div>
			</div>
			<div id="datediv" class="postbox " >
				<h2 class='hndle'><span>Date</span></h2>
				<div class="inside">
					<p><abbr title="viene inserita automaticamente nel momento in cui viene creato.">Data di codifica dell'atto</abbr>: 
						<input name="Data" type="text" id="CalendarioMO" value="<?php echo ap_VisualizzaData($atto->Data);?>" maxlength="10" size="10" />
					</p>
					<p><abbr title="Data in cui inizia a validità legale dell'atto. Viene impostata automaticamente in fase di pubblicazione">Data inizio Pubblicazione</abbr>:
						<input name="DataInizio" type="hidden" value="<?php echo ap_VisualizzaData($atto->DataInizio);?>" />
						<em><strong><?php echo ap_VisualizzaData($atto->DataInizio);?></strong></em>					
					</p>
					<p><abbr title="Data fine validità legale dell'atto">Data fine Pubblicazione</abbr>:
						<input name="DataFine" id="Calendario3" type="text" value="<?php echo ap_VisualizzaData($atto->DataFine);?>" maxlength="10" size="10" />		
					</p>		
					<p><abbr title="Data in cui l'atto viene eliminato dall'archivio, in base al Decreto n. 33/2013 art.8:<br />5 anni, decorrenti dal 1° gennaio dell'anno successivo a quello
da cui decorre l'obbligo di pubblicazione, e comunque fino a che gli atti pubblicati producono i loro effetti,
fatti salvi i diversi termini previsti dalla normativa in materia di trattamento dei dati personali e quanto
previsto dagli articoli 14, comma 2, e 15, comma 4">Data Oblio</abbr>:
						<input name="DataOblio" id="Calendario4" type="text" value="<?php echo ap_VisualizzaData($atto->DataOblio);?>" maxlength="10" size="10" /><button type="button" id="setta-def-data-o" class="setta-def-data" name="<?php echo ap_VisualizzaData($DataOblioStandard);?>" style="margin-top: 5px;margin-left:10px;"> Aggiorna a <?php echo ap_VisualizzaData($DataOblioStandard);?></button>	
					</p>				
				</div>
			</div>
			<div id="metadiv" class="postbox " >
				<h2 class='hndle'><span>Meta dati</span></h2>
				<div class="inside">
					<p><abbr title="Ente che pubblica l'atto; potrebbe essere diverso dall'ente titolare del sito web se la pubblicazione avviene per conto di altro ente">Ente<span style="color:red;font-weight: bold;">*</span></abbr>: 
						<?php echo ap_get_dropdown_enti('Ente','Ente','postform maxdime richiesto ValValue(>-1)','',$atto->Ente);?>
					</p>
					<p><abbr title="Categoria in cui viene collocato l'atto, questo sistema permette di ragguppare gli oggetti in base alla lor natura">Categoria<span style="color:red;font-weight: bold;">*</span></abbr>:
						<?php echo ap_get_dropdown_categorie('Categoria','Categoria','postform maxdime richiesto ValValue(>0)','',$atto->IdCategoria);?>					
					</p>
				</div>
			</div>
			<div id="metadiv" class="postbox " >
				<h2 class='hndle'><span>Soggetti</span></h2>
				<div class="inside">
					<p>In questo spazio bisogna codificare i soggetti che sono coinvolti in questo atto possono essere specificati più soggetti
					</p>
					<ul>
<?php
		$Soggetti=unserialize($atto->Soggetti);
		$Ana_Soggetti=ap_get_responsabili();
		foreach($Ana_Soggetti as $Soggetto){
			$Selected="";
			if(is_array($Soggetti) And in_array($Soggetto->IdResponsabile,$Soggetti)){
				$Selected="checked";
			}
			echo "
			<li>
				<input type=\"checkbox\" name=\"Soggetto[]\" value=\"$Soggetto->IdResponsabile\" $Selected />".$Soggetto->Cognome." ".$Soggetto->Nome." <strong><em>".ap_get_Funzione_Responsabile($Soggetto->Funzione,"Descrizione")."</em></strong> 
			</li>
			";
		}
?>						
					</ul>
				</div>
			</div>
	</div>
	</div><!-- /post-body-content -->	
	</div>
	</form>
</div>
<?php	
}

function Allegati_atto($IdAtto,$messaggio="",$IdAllegato=0){
	$risultato=ap_get_atto($IdAtto);
	$risultato=$risultato[0];
	$risultatocategoria=ap_get_categoria($risultato->IdCategoria);
	$risultatocategoria=$risultatocategoria[0];
	$dirUpload =  get_option('opt_AP_FolderUpload').'/';
	echo '
<div class="wrap">

	<div class="HeadPage">
		<h2 class="wp-heading-inline"><span class="dashicons dashicons-portfolio"></span> Atti</h2>
		<a href="'. site_url().'/wp-admin/admin.php?page=atti&stato_atti=Nuovi" class="add-new-h2 tornaindietro">Torna indietro</a>
		<h3>Allegati Atto</h3>	
	</div>';
if ( $messaggio!="" ) {
	 	$messaggio=str_replace("%%br%%", "<br />", $messaggio);
		print('<div id="message" class="updated"><p>'.$messaggio.'</p></div>');
		$_SERVER['REQUEST_URI'] = remove_query_arg(array('messaggio'), $_SERVER['REQUEST_URI']);
	}
echo'
<div id="col-container">
<div id="col-right">
<div class="col-wrap">';
if ($IdAllegato!=0){
 	$allegato=ap_get_allegato_atto($IdAllegato);
 	$allegato=$allegato[0];
	echo '<h3>Modifica Allogato</h3>
	<form id="allegato"  method="post" action="?page=atti" class="validate">
	<input type="hidden" name="action" value="update-allegato-atto" />
	<input type="hidden" name="id" value="'.$IdAtto.'" />
	<input type="hidden" name="idAlle" value="'.$IdAllegato.'" />
	<input type="hidden" name="modificaallegatoatto" value="'.wp_create_nonce("editallegatoatto").'" />
	<table class="widefat">
	    <thead>
		<tr>
			<th colspan="3" style="text-align:center;font-size:1.2em;">Dati Allegato</th>
		</tr>
	    </thead>
	    <tbody id="dati-allegato">
		<tr>
			<th>Descrizione Allegato</th>
			<td><textarea  name="titolo" rows="4" cols="50" wrap="ON" maxlength="255">'.$allegato->TitoloAllegato.'</textarea></td>
		</tr>
		<tr>
			<th>File:</th>
			<td>'.$allegato->Allegato.'</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" id="submit" class="button" value="Aggiorna Allegato"  />&nbsp;&nbsp;
			    <input type="submit" name="annulla" id="annulla" class="button" value="Annulla Operazione"  />
		    </td>
		</tr>
	    </tbody>
	</table>
	</form>';	
}else{
	echo'
	<h3 style="margin-top:50px;">Allegati <a href="'.site_url().'/wp-admin/admin.php?page=atti&amp;id='.$IdAtto.'&amp;action=UpAllegati" class="add-new-h2">Aggiungi nuovo</a> <a href="'.site_url().'/wp-admin/admin.php?page=atti&amp;id='.$IdAtto.'&amp;action=AssAllegati" class="add-new-h2">Associa file</a></h3>';
	$righe=ap_get_all_allegati_atto($IdAtto);
	echo'
		<table class="widefat">
		    <thead>
			<tr>
				<th style="font-size:1.2em;">Operazioni</th>
				<th style="font-size:1.2em;">Allegato</th>
				<th style="font-size:1.2em;">File</th>
			</tr>
		    </thead>
		    <tbody id="righe-log">';
	foreach ($righe as $riga) {
		echo '<tr>
				<td>	
					<a href="?page=atti&amp;action=delete-allegato-atto&amp;idAllegato='.$riga->IdAllegato.'&amp;idAtto='.$IdAtto.'&amp;Allegato='.$riga->TitoloAllegato.'&amp;cancellaallegatoatto='.wp_create_nonce('deleteallegatoatto').'" rel="'.strip_tags($riga->TitoloAllegato).'" class="da">
						<span class="dashicons dashicons-trash" title="Cancella Atto"></span>
					</a>
					<a href="?page=atti&amp;action=edit-allegato-atto&amp;id='.$IdAtto.'&amp;idAlle='.$riga->IdAllegato.'&amp;modificaallegatoatto='.wp_create_nonce('editallegatoatto').'" rel="'.strip_tags($riga->TitoloAllegato).'">
						 <span class="dashicons dashicons-edit" title="Modifica atto"></span>
					</a>
					<a href="'.ap_DaPath_a_URL($riga->Allegato).'" target="_blank">
							<span class="dashicons dashicons-search" title="Visualizza dati atto"></span>
					</a>
				</td>
				<td >'.$riga->TitoloAllegato.'</td>
				<td >'. basename( $riga->Allegato).'</td>
			</tr>';
	}
	echo '    </tbody>
		</table>';
}
echo'</div>
</div>
<div id="col-left">
<div class="col-wrap">
<h3>Dati Atto</h3>
	<table class="widefat">
	    <thead>
		<tr>
			<th colspan="2" style="text-align:center;font-size:1.2em;">Dati atto</th>
		</tr>
	    </thead>
	    <tbody id="dati-atto">
		<tr>
			<th style="width:20%;">Numero Albo</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.$risultato->Numero."/".$risultato->Anno.'</td>
		</tr>
		<tr>
			<th>Data</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->Data).'</td>
		</tr>
		<tr>
			<th>Codice di Riferimento</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Riferimento).'</td>
		</tr>
		<tr>
			<th>Oggetto</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Oggetto).'</td>
		</tr>
		<tr>
			<th>Data inizio Pubblicazione</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataInizio).'</td>
		</tr>
		<tr>
			<th>Data fine Pubblicazione</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataFine).'</td>
		</tr>
		<tr>
			<th>Data oblio</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataOblio).'</td>
		</tr>
		<tr>
			<th>Note</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Informazioni).'</td>
		</tr>
		<tr>
			<th>Categoria</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultatocategoria->Nome).'</td>
		</tr>
			<tr>
				<th>Soggetti</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">
				<ul>';
	$Soggetti=unserialize($risultato->Soggetti);
	$Soggetti=ap_get_alcuni_soggetti_ruolo(implode(",",$Soggetti));
	foreach($Soggetti as $Soggetto){
		echo "
			<li><strong>".ap_get_Funzione_Responsabile($Soggetto->Funzione,"Descrizione")."</strong> ".$Soggetto->Nome." ".$Soggetto->Cognome." 
			</li>";
	}
echo'				
				</ul>
				</td>
			</tr>	    
	    </tbody>
	</table></div>
</div>
</div>
</div>';	
}
function View_atto($IdAtto){
	global $AP_OnLine;
if (isset($_REQUEST['stato_atti']))
	$Prov=$_REQUEST['stato_atti'];
else
	$Prov="DaPubblicare";
	$risultato=ap_get_atto($IdAtto);
	$risultato=$risultato[0];
	$risultatocategoria=ap_get_categoria($risultato->IdCategoria);
	$risultatocategoria=$risultatocategoria[0];
	$NomeEnte=ap_get_ente($risultato->Ente);
	$NomeEnte=stripslashes($NomeEnte->Nome);
	echo '
<div class="wrap nosubsub">
	<div class="HeadPage">
		<h2 class="wp-heading-inline"><span class="dashicons dashicons-portfolio"></span> Atti</h2>
		<a href="'.site_url().'/wp-admin/admin.php?page=atti&stato_atti='.filter_input(INPUT_GET,"stato_atti").'" class="add-new-h2 tornaindietro">Torna indietro</a>
		<h3 >Visualizza dati Atto</h3>	
	</div>
		<div class="clear"><br /></div>
		<div id="col-container">
		<div id="col-right">
				<div class="col-wrap postbox" style="padding:0 10px 10px 10px;margin-left:10px;">
				<h3>Log</h3>
				<hr />
					<div id="utility-tabs-container">
						<ul>
							<li><a href="#log-tab-1">Atto</a></li>
							<li><a href="#log-tab-2">Allegati</a></li>
							<li><a href="#log-tab-3">Statistiche Visite</a></li>
							<li><a href="#log-tab-4">Statistiche Download</a></li>
						</ul>
						<div id="log-tab-1">
							<div id="DatiLog">'.$AP_OnLine->CreaLog(1,$IdAtto,0).'</div>
						</div>
						<div id="log-tab-2">
							<div id="DatiLog">'.$AP_OnLine->CreaLog(3,$IdAtto,0).'</div>
						</div>
						<div id="log-tab-3">
							<div id="DatiLog">'.$AP_OnLine->CreaLog(5,$IdAtto,0).'</div>
						</div>
						<div id="log-tab-4">
							<div id="DatiLog">'.$AP_OnLine->CreaLog(6,$IdAtto,0).'</div>
						</div>
					 </div>
				</div>
	</div>
<div id="col-left">
	<div class="col-wrap postbox" style="padding:0 10px 10px 10px;margin-left:10px;">
		<h3>Dati atto</h3>
		<hr />
		<table class="widefat" style="border:0;">
		    <tbody id="dati-atto">
			<tr>
				<th style="width:20%;">Ente emittente</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.$NomeEnte.'</td>
			</tr>';
		if($risultato->DataAnnullamento!='0000-00-00')		
			echo '		<tr>
				<th style="width:20%;">Data Annullamento</th>
				<td style="font-size:14px;font-weight: bold;color: Red;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataAnnullamento).'</td>
			</tr>
	    	<tr>
				<th style="width:20%;">Motivo Annullamento</th>
				<td style="font-size:14px;font-weight: bold;color: Red;vertical-align:top;">'.stripslashes($risultato->MotivoAnnullamento).'</td>
			</tr>';
		echo '		<tr>
				<th style="width:20%;">Numero Albo</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.$risultato->Numero."/".$risultato->Anno.'</td>
			</tr>
			<tr>
				<th>Data</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->Data).'</td>
			</tr>
			<tr>
				<th>Codice di Riferimento</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Riferimento).'</td>
			</tr>
			<tr>
				<th>Oggetto</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Oggetto).'</td>
			</tr>
			<tr>
				<th>Data inizio Pubblicazione</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataInizio).'</td>
			</tr>
			<tr>
				<th>Data fine Pubblicazione</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataFine).'</td>
			</tr>
			<tr>
				<th>Data Oblio</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataOblio).'</td>
			</tr>
			<tr>
				<th>Note</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Informazioni).'</td>
			</tr>
			<tr>
				<th>Categoria</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultatocategoria->Nome).'</td>
			</tr>';
$MetaDati=ap_get_meta_atto($IdAtto);
if($MetaDati!==FALSE){
	$Meta="";
	foreach($MetaDati as $Metadato){
		$Meta.="{".$Metadato->Meta."=".$Metadato->Value."} - ";
	}
	$Meta=substr($Meta,0,-3);
		echo'
				<tr>
					<th>Meta Dati</th>
					<td style="vertical-align: middle;color: Red;">'.$Meta.'</td>
				</tr>';
}
echo'
			<tr>
				<th>Soggetti</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">
				<ul>';
	$Soggetti=unserialize($risultato->Soggetti);
	$Soggetti=ap_get_alcuni_soggetti_ruolo(implode(",",$Soggetti));
	foreach($Soggetti as $Soggetto){
		echo "
			<li><strong>".ap_get_Funzione_Responsabile($Soggetto->Funzione,"Descrizione")."</strong> ".$Soggetto->Nome." ".$Soggetto->Cognome." 
			</li>";
	}
echo'				
				</ul>
				</td>
			</tr>	    
			</tbody>
		</table>
	</div>';
echo '<div class="postbox" style="padding:0 10px 10px 10px;margin-left:10px;">
	<h3>Allegati</h3>
	<div class="Visalbo">';
$allegati=ap_get_all_allegati_atto($IdAtto);
$TipidiFiles=ap_get_tipidifiles();
foreach ($allegati as $allegato) {
	$Estensione=ap_ExtensionType($allegato->Allegato);	
	echo '<div style="border: thin dashed;font-size: 1em;">
			<div style="float: left;display: inline;width: 40px;height: 40px;padding-top:5px;padding-left:5px;">';
	if(isset($allegato->TipoFile) and $allegato->TipoFile!=""  and ap_isExtensioType($allegato->TipoFile)){
		$Estensione=ap_ExtensionType($allegato->TipoFile);	
		echo '<img src="'.$TipidiFiles[$Estensione]['Icona'].'" alt="'.$TipidiFiles[$Estensione]['Descrizione'].'" height="30" width="30"/>';
	}else{
		echo '<img src="'.$TipidiFiles[strtolower($Estensione)]['Icona'].'" alt="'.$TipidiFiles[strtolower($Estensione)]['Descrizione'].'" height="30" width="30"allegato/>';
	}
	echo '</div>
			<div style="margin-top:0;">
				<p style="margin-top:0;">'.strip_tags($allegato->TitoloAllegato).' <br />';
			if (is_file($allegato->Allegato))
				echo '        <a href="'.ap_DaPath_a_URL($allegato->Allegato).'" >'. basename( $allegato->Allegato).'</a> ('.ap_Formato_Dimensione_File(filesize($allegato->Allegato)).')<br />'.htmlspecialchars_decode($TipidiFiles[strtolower($Estensione)]['Verifica']);
			else
				echo basename( $allegato->Allegato)." File non trovato, il file &egrave; stato cancellato o spostato!";
echo'				</p>
			</div>
			<div style="clear:both;"></div>
		</div>';
	}
echo '</div>
	</div>
		</div>
	</div>
</div>';	
}

function Annulla_Atto($IdAtto){
	global $AP_OnLine;
	$risultato=ap_get_atto($IdAtto);
	$risultato=$risultato[0];
	$risultatocategoria=ap_get_categoria($risultato->IdCategoria);
	$risultatocategoria=$risultatocategoria[0];
	$NomeEnte=ap_get_ente($risultato->Ente);
	$NomeEnte=stripslashes($NomeEnte->Nome);
	echo '
<div class="wrap">
	<div class="HeadPage">
		<h2 class="wp-heading-inline"><span class="dashicons dashicons-portfolio"></span> Atti</h2>
		<a href="'.site_url().'/wp-admin/admin.php?page=atti&amp;stato_atti=Correnti" class="add-new-h2 tornaindietro">Torna indietro</a>
		<h3>Annulla Atto</h3>	
	</div>
	<div id="col-container">
		<div class="clear"><br /></div>
		<form id="annullaatto" method="post" action="?page=atti" class="validate">
		<input type="hidden" name="action" value="annulla-atto" />
		<input type="hidden" name="id" value="'.(int)$_REQUEST['id'].'" />
		<input type="hidden" name="annatto" value="'.wp_create_nonce('annatto').'" />
		<table class="widefat">
		    <thead>
		    <tr>
				<th style="text-align:center;font-size:1.2em;width:50%;">Dati atto</th>
				<th style="font-size:1.2em;">Allegati atto</th>
			</tr>
		    </thead>
		    <tbody>
		    <tr>
		    <td style="border-right-style: groove;border-right-width: thin;">
		    	<table>
				<tr>
					<th style="width:20%;">Ente emittente</th>
					<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.$NomeEnte.'</td>
				</tr>
				<tr>
					<th style="width:20%;">Numero Albo</th>
					<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.$risultato->Numero."/".$risultato->Anno.'</td>
				</tr>
				<tr>
					<th>Data</th>
					<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->Data).'</td>
				</tr>
				<tr>
					<th>Codice di Riferimento</th>
					<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Riferimento).'</td>
				</tr>
				<tr>
					<th>Oggetto</th>
					<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Oggetto).'</td>
				</tr>
				<tr>
					<th>Data inizio Pubblicazione</th>
					<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataInizio).'</td>
				</tr>
				<tr>
					<th>Data fine Pubblicazione</th>
					<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataFine).'</td>
				</tr>
				<tr>
					<th>Data Oblio</th>
					<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataOblio).'</td>
				</tr>
				<tr>
					<th>Note</th>
					<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Informazioni).'</td>
				</tr>
				<tr>
					<th>Categoria</th>
					<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultatocategoria->Nome).'</td>
				</tr>
				<tr>
					<th>Soggetti</th>
						<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">	
					<ul>';
	$Soggetti=unserialize($risultato->Soggetti);
	$Soggetti=ap_get_alcuni_soggetti_ruolo(implode(",",$Soggetti));
	foreach($Soggetti as $Soggetto){
		echo "
			<li><strong>".ap_get_Funzione_Responsabile($Soggetto->Funzione,"Descrizione")."</strong> ".$Soggetto->Nome." ".$Soggetto->Cognome." 
			</li>";
	}
echo'				
				</ul>
					</td>
				</tr>
				</table>	    
			</td>
			<td>
			<p style="color:red;font-weight: bold;">Selezionare gli allegati che devono essere cancellati per violazione di legge<br />NB: verrà cancellato solo il file, mentre sarà mantenuto il nome del file nell\'elenco degli allegati</p>';
$allegati=ap_get_all_allegati_atto($IdAtto);
$TipidiFiles=ap_get_tipidifiles();
foreach ($allegati as $allegato) {
	$Estensione=ap_ExtensionType($allegato->Allegato);	
	echo '<div style="float: left;display: inline;width: 40px;height: 40px;padding-top:5px;padding-left:5px;">
			<input type="checkbox" name="Alle:'.$allegato->IdAllegato.'" value="'.$allegato->IdAllegato.'">
		  </div>
			<div style="float: left;display: inline;width: 40px;height: 40px;padding-top:5px;padding-left:5px;">';
	if(isset($allegato->TipoFile) and $allegato->TipoFile!="" and ap_isExtensioType($allegato->TipoFile)){
		$Estensione=ap_ExtensionType($allegato->TipoFile);	
		echo '<img src="'.$TipidiFiles[$Estensione]['Icona'].'" alt="'.$TipidiFiles[$Estensione]['Descrizione'].'" height="30" width="30"/>';
	}else{
		echo '<img src="'.$TipidiFiles[strtolower($Estensione)]['Icona'].'" alt="'.$TipidiFiles[strtolower($Estensione)]['Descrizione'].'" height="30" width="30"allegato/>';
	}
	echo'</div>
			<div style="margin-top:0;">
				<p style="margin-top:0;">'.strip_tags($allegato->TitoloAllegato).' <br />';
			if (is_file($allegato->Allegato))
				echo '        <a href="'.ap_DaPath_a_URL($allegato->Allegato).'" >'. basename( $allegato->Allegato).'</a> ('.ap_Formato_Dimensione_File(filesize($allegato->Allegato)).')<br />'.htmlspecialchars_decode($TipidiFiles[strtolower($Estensione)]['Verifica']);
			else
				echo basename( $allegato->Allegato)." File non trovato, il file &egrave; stato cancellato o spostato!";
echo'				</p>
			</div>';
	}			
echo'			</td>
			</tr>
			<tr>
				<td colspan="2" style="text-align:center;border-top-style: groove;border-top-width: thin;">
				<span style="color:red;font-size:2em;font-weight: bold;">Motivo Annullamento</span><br />
					<textarea rows="4" cols="100"  maxlength="255" placeholder="Inserisci il motivo, massimo 255 caratteri" id="Motivo" name="Motivo" ></textarea>
				</td>
			</tr>
			<tr>
				<td colspan="2" style="text-align:center;">
					<input type="submit" name="submit" id="submit" class="button" value="Annulla Pubblicazione Atto"  />
					<input type="submit" name="submit" id="submit" class="button" value="Annulla Operazione"  />
				<td>
			</tr>
			</tbody>
		</table>
		</form>
		</div>
		<div class="col-wrap">
			<h3>Log</h3>
					<div id="utility-tabs-container">
						<ul>
							<li><a href="#log-tab-1">Atto</a></li>
							<li><a href="#log-tab-2">Allegati</a></li>
							<li><a href="#log-tab-3">Statistiche Visite</a></li>
							<li><a href="#log-tab-4">Statistiche Download</a></li>
						</ul>
						<div id="log-tab-1">
							<div id="DatiLog">'.$AP_OnLine->CreaLog(1,$IdAtto,0).'</div>
						</div>
						<div id="log-tab-2">
							<div id="DatiLog">'.$AP_OnLine->CreaLog(3,$IdAtto,0).'</div>
						</div>
						<div id="log-tab-3">
							<div id="DatiLog">'.$AP_OnLine->CreaLog(5,$IdAtto,0).'</div>
						</div>
						<div id="log-tab-4">
							<div id="DatiLog">'.$AP_OnLine->CreaLog(6,$IdAtto,0).'</div>
						</div>
					 </div>
		</div>
</div>';	
}

function Lista_Atti($Msg_op=""){
if (isset($_REQUEST['p']))
	$Pag=$_REQUEST['p'];
else
	$Pag=0;
$Message[0] = "Messaggio non definito";
$messages[1] = "Atto Aggiunto";
$messages[2] = "Atto Cancellato";
$messages[3] = "Atto Aggiornato";
$messages[4] = "Atto non Aggiunto";
$messages[5] = "Atto non Aggiornato";
$messages[6] = "Atto non Cancellato";
$messages[7] = 'Impossibile cancellare un Atto che contiene Allegati<br />Cancellare prima gli Allegati e poi riprovare';
$messages[8] = 'Impossibile ANULLARE l\'Atto';
$messages[9] = 'Atto ANNULLATO';
$messages[10] = 'Allegati all\'Atto Cancellati';
$messages[11] = 'Allegati all\'Atto NON Cancellati';
$messages[12] = 'Metadati  dell\'Atto Memorizzati';
$messages[13] = 'Metadati  dell\'Atto NON Memorizzati';
$messages[80] = 'ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l\'operazione &egrave; stata annullata';
$messages[99] = 'OPERAZIONE NON AMMESSA!<br />l\'atto non è ancora da eliminare';
//Gestione Messaggi di stato
if (isset($_REQUEST['message'])) 
	$msg = (int) $_REQUEST['message'];
if (isset($_REQUEST['message2'])) 
	$msg2 = (int) $_REQUEST['message2'];
if (isset($_REQUEST['errore']))
	$Errore=$_REQUEST['errore'];
if ($Msg_op!=""){
	if (is_numeric($Msg_op))
		$msg=$Msg_op;
	else{
		$msg =9;
		$messages[9]=(str_replace("%%br%%","<br />",$Msg_op));	
	}
}
?>
<div id="ConfermaCancellazione" title="Conferma Cancellazione" style="display:none;">
	<input type="hidden" value="" id="UrlDest" />
  <h3>Atto <span id="oggetto"></span></span></h3><p style='color:red;font-weight: bold;'>Confermi la cancellazione dell'atto?</p>
</div>
<?php
echo' <div class="wrap">
	<div class="HeadPage">
		<h2 class="wp-heading-inline"><span class="dashicons dashicons-portfolio"></span> Atti';
$HtmlNP="";
if (ap_get_num_categorie()==0){
	$HtmlNP.='<p> </p>
			<div class="widefat" >
				<p style="text-align:center;font-size:1.2em;font-weight: bold;color: green;">
				Non risultano categorie codificate, se vuoi posso impostare le categorie di default &ensp;&ensp;<a href="?page=utilityAlboP&amp;action=creacategorie">Crea Categorie di Default</a>
				</p>
			</div>';
}
if (ap_num_responsabili()==0){
	$HtmlNP.='<p> </p>
			<div class="widefat" >
				<p style="text-align:center;font-size:1.2em;font-weight: bold;color: green;">
				Non risultano <strong>Responsabili</strong> codificati, devi crearne almeno uno prima di iniziare a codificare gli Atti &ensp;&ensp;<a href="?page=responsabili">Crea Responsabile</a>
				</p>
			</div>';
}
if ($HtmlNP!=""){
	echo '</h2>
	<div class="clear"></div>
	<div class="postbox-container" style="width:80%;margin-top:20px;">'.
	$HtmlNP.'
	</div>
</div><!-- /wrap -->	';
	return;	
}
echo'
	<a href="?page=atti&amp;action=new-atto" class="add-new-h2">Aggiungi nuovo</a></h2>';
	if ( isset($msg) or isset($msg2) or isset($Errore) ){
		$stato="";
		if (isset($_GET['stato_atti']))
			$stato="&stato_atti=".$_GET['stato_atti'];
		if($Msg_op=="Atto PUBBLICATO"){
			$stato="&stato_atti=Correnti";
		}
		if(substr($Msg_op,0,19)=="Atto non PUBBLICATO"){
			$stato="&stato_atti=Nuovi";
		}
		echo '<div id="message" class="updated"><p>'.(isset($msg)?$messages[$msg]:"").(isset($msg2)?"<br />".$messages[$msg2]:"").'<br /><br />'.(isset($Errore)?$Errore:"").'</p></div>
		      <meta http-equiv="refresh" content="2;url=admin.php?page=atti'.$stato.'"/>';
		      return;
	} 
	if (isset($_REQUEST['stato_atti']))
		switch($_REQUEST['stato_atti']){
			case "Tutti": $Titolo="Tutti gli atti";$Azione="Tutti";break;
			case "Nuovi": $Titolo="Atti da pubblicare";$Azione="DaPubblicare";break;
			case "Correnti": $Titolo="Atti in corso di Validità";$Azione="Correnti";break;
			case "Scaduti":  $Titolo="Atti Scaduti";$Azione="Scaduti";break;
			case "Eliminare":  $Titolo="Atti da Eliminare (Oblio)";$Azione="Eliminare";break;
            case "Cerca":  $Titolo="Cerca Atti";$Azione="Cerca";break; /* mr */
			default: $Titolo="Atti da Pubblicare";$Azione="DaPubblicare";break;
		}
	else{
			$Titolo="Tutti gli Atti";
			$Azione="Tutti";
	}
  	$tablenew = new AdminTableAtti(); // Il codice della classe a seguire
   	$tablenew->stato_atti=$Azione;
  	$tablenew->prepare_items(); // Metodo per elenco campi
  	$page = filter_input(INPUT_GET,'page' ,FILTER_SANITIZE_STRIPPED);
  	$paged = filter_input(INPUT_GET,'paged',FILTER_SANITIZE_NUMBER_INT);
	echo '<h3>'.$Titolo.'</h3>	
		</div>
		<div class="wrap">
	  	<form method="get">
	  		<input type="hidden" name="page" value="'.$page. '"/>
	  		<input type="hidden" name="stato_atti" value="Cerca"/>'; /* mr */
	    	$tablenew->search_box('Cerca in Oggetto','search_id'); /* mr  */
	    	$tablenew->views();
	echo '</form>
        <form id="persons-table" method="GET">
            <input type="hidden" name="page" value="'.$_REQUEST['page'].'" />
		  	<input type="hidden" name="paged" value="'.$paged.'"/>';
	$tablenew->display(); // Metodo per visualizzare elenco records
	echo '</form>
	</div>
</div>
';
}
?>
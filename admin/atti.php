<?php
/**
 * Gestione Atti.
 * @link       http://www.eduva.org
 * @since      4.5.6
 *
 * @package    Albo On Line
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
	        "Tutti"		  => "<a href='?page=atti&amp;stato_atti=Tutti'><strong>".__("Tutti","albo-online")." (".$this->Atti_Tutti.")</strong></a>",
	        "nuovi"       => "<a href='?page=atti&amp;stato_atti=Nuovi'>".__("da Pubblicare","albo-online")."(".$this->Atti_DaPubblicare.")</a>",
	        "correnti"    => "<a href='?page=atti&amp;stato_atti=Correnti'>".__("Correnti","albo-online")."(".$this->Atti_Correnti.")</a>",
	        "storico"     => "<a href='?page=atti&amp;stato_atti=Scaduti'>".__("Scaduti","albo-online")."(".$this->Atti_Scaduti.")</a>",
	        "oblio"       => "<a href='?page=atti&amp;stato_atti=Eliminare'>".__("da Eliminare","albo-online")."(".$this->Atti_Eliminare.")</a>",
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
		    'Stato'			 	 => __('Stato','albo-online'),
		    'Numero'             => __('Numero','albo-online'),
		    'Riferimento'        => __('Riferimento','albo-online'),
		    'Oggetto'          	 => __('Oggetto','albo-online'),
		    'Ente'               => __('Ente','albo-online'),
			'MetaDati'           => __('Meta Dati','albo-online'),
		    'Data'          	 => __('Del','albo-online'),
		    'validita'           => __('Validità Dal/Al','albo-online'),
		    'dataoblio'        	 => __('Oblio','albo-online'),
		    'Idcategoria'     	 => __('Categoria','albo-online'));
		    break;
	  	case "DaPubblicare": 
	  		$columns = array(
		    'Stato'			 	 => __('Stato','albo-online'),
		    'Riferimento'        => __('Riferimento','albo-online'),
		    'Oggetto'          	 => __('Oggetto','albo-online'),
		    'Ente'               => __('Ente','albo-online'),
			'MetaDati'           => __('Meta Dati','albo-online'),
		    'Data'          	 => __('Del','albo-online'),
		    'Idcategoria'     	 => __('Categoria','albo-online'));
		    break;
	  	case "Eliminare": 
	  		$columns = array(
	    	'cb'                 => '<input type="checkbox"/>',
		    'Stato'			 	 => __('Stato','albo-online'),
		    'Numero'             => __('Numero','albo-online'),
		    'Riferimento'        => __('Riferimento','albo-online'),
		    'Oggetto'          	 => __('Oggetto','albo-online'),
		    'Ente'               => __('Ente','albo-online'),
			'MetaDati'           => __('Meta Dati','albo-online'),	
		    'Data'          	 => __('Del','albo-online'),
		    'validita'           => __('Validità Dal/Al','albo-online'),
		    'dataoblio'        	 => __('Oblio','albo-online'),
		    'Idcategoria'     	 => __('Categoria','albo-online'));
		    break;
        case "Cerca": 
        	$columns = array(
		    'Stato'			 	 => __('Stato','albo-online'),
		    'Numero'             => __('Numero','albo-online'),
		    'Riferimento'        => __('Riferimento','albo-online'),
		    'Oggetto'          	 => __('Oggetto','albo-online'),
		    'Ente'               => __('Ente','albo-online'),
		    'Data'          	 => __('Del','albo-online'),
		    'validita'           => __('Validità Dal/Al','albo-online'),
		    'dataoblio'        	 => __('Oblio','albo-online'),
		    'Idcategoria'     	 => __('Categoria','albo-online'));
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
						<span class="dashicons dashicons-search" title="'.__('Visualizza dati atto','albo-online').'"></span>
					</a>');
	$this->AzioneDefault='<a href="?page=atti&amp;action=view-atto&amp;id='.$item->IdAtto.'&amp;stato_atti='.$this->stato_atti.'" >';
	switch($this->stato_atti){
		case "Tutti":
			$Msg="";
			$Msg.=($Scaduto?'<span style="color: rgb(23, 5, 161);font-weight: bold;">'.__('Scaduto','albo-online').'</span>':'<span style="color: green;font-weight: bold;">'.__('Corrente','albo-online').'</span>');
			$Msg.=($Annullato?' <span style="color: red;font-weight: bold;">'.__('Annullato','albo-online').'</span>':"");
			break;		
		case "DaPubblicare":
			$actions['cancella'] ='<span class="trash"><a href="?page=atti&amp;action=delete-atto&amp;id='.$item->IdAtto.'&amp;cancellaatto='.
				wp_create_nonce('deleteatto').'" rel="'.strip_tags($item->Oggetto).'" tag="" class="ac">
						<span class="dashicons dashicons-trash" title="'.__('Cancella Atto','albo-online').'"></span>
					</a></span>';
			$this->AzioneDefault='<a href="?page=atti&amp;action=edit-atto&amp;id='.$item->IdAtto.'&amp;modificaatto='.
			wp_create_nonce('editatto').'">';
			$actions['modifica'] ='<a href="?page=atti&amp;action=edit-atto&amp;id='.$item->IdAtto.'&amp;modificaatto='.
			wp_create_nonce('editatto').'">
						<span class="dashicons dashicons-edit" title="'.__('Modifica atto','albo-online').'"></span>
					</a>';
			$actions['allegati'] ='<a href="?page=atti&amp;action=allegati-atto&amp;id='.$item->IdAtto.'&amp;allegatoatto='.
			wp_create_nonce('gestallegatiatto').'">
						<span class="dashicons dashicons-upload" title="'.__('Allegati','albo-online').'"></span>
					</a>';
			if (current_user_can('editore_atti_albo')){
				$actions['pubblica'] ='<a href="?page=atti&amp;action=approva-atto&amp;id='.$item->IdAtto.'"  >
	<span class="dashicons dashicons-share-alt" title="'.__('Pubblica atto','albo-online').'"></span>
					</a>';
			}
			$Msg='<span style="color: green;font-weight: bold;">'.__('Da Pubblicare','albo-online').'</span>';
			break;
		case "Correnti":
			$Msg=($Annullato?'<span style="color: red;font-weight: bold;">Annullato</span>':'<span style="color: green;font-weight: bold;">Pubblicato</span>');
			$actions['meta'] ='<a href="?page=atti&amp;action=metadati-atto&amp;id='.$item->IdAtto.'&amp;metaatto='.wp_create_nonce('operazionemetaatto').'&stato_atti=Correnti">
				<span class="dashicons dashicons-screenoptions" title="'.__('Gestisci metadati Atto','albo-online').'"></span>
			</a>';
			if (current_user_can('editore_atti_albo')){
				$actions['avviso'] ='<a href="?page=atti&amp;action=avviso_affissione-atto&amp;id='.$item->IdAtto.'&amp;avvisoatto='.wp_create_nonce('operazioneavviso_affissione').'&stato_atti=Correnti">
				<span class="dashicons dashicons-media-text" title="'.__('Stampa Avviso di Affissione','albo-online').'"></span>
			</a>';
			}
		break;
		case "Scaduti":
			$Msg=($Annullato?'<span style="color: red;font-weight: bold;">'.__('Annullato','albo-online').'</span>':'<span style="color: rgb(23, 5, 161);font-weight: bold;">Scaduto</span>');			
			$actions['meta'] ='<a href="?page=atti&amp;action=metadati-atto&amp;id='.$item->IdAtto.'&amp;metaatto='.wp_create_nonce('operazionemetaatto').'&stato_atti=Scaduti">
				<span class="dashicons dashicons-screenoptions" title="'.__('Gestisci metadati Atto','albo-online').'"></span>
			</a>';
			$actions['certificato'] ='<a href="?page=atti&amp;action=certificato_pubblicazione-atto&amp;id='.$item->IdAtto.'&amp;certificatoatto='.wp_create_nonce('operazionecertificato_pubblicazione').'&stato_atti=Scaduti">
				<span class="dashicons dashicons-media-spreadsheet" title="'.__('Stampa Certificato Pubblicazione','albo-online').'"></span>
			</a>';
			$actions['oblioallegati'] ='<a href="?page=atti&amp;action=oblio-allegati-atto&amp;id='.$item->IdAtto.'&amp;oaatto='.wp_create_nonce('operazioneoblioallegati').'&stato_atti=Scaduti">
				<span class="dashicons dashicons-editor-unlink" title="'.__('Cancella Allegati Atto','albo-online').'"></span>
			</a>';
			$actions['oblioatto'] ='<a href="?page=atti&amp;action=oblia-atto&amp;id='.$item->IdAtto.'&amp;oatto='.wp_create_nonce('operazionebliaatto').'&stato_atti=Scaduti">
				<span class="dashicons dashicons-hammer" title="'.__('Imposta la data di Oblio dell\'atto ad oggi','albo-online').'"></span>
			</a>';
			break;
		case "Eliminare":
			if (current_user_can('editore_atti_albo')){
				$actions['delete'] ='<span class="trash"><a href="?page=atti&amp;action=elimina-atto&amp;id='.$item->IdAtto.'&amp;cancellatto='.
				wp_create_nonce('operazionecancelaatto').'">
				<span class="dashicons dashicons-trash" title="'.__('Oblio Atto','albo-online').'"></span>
			</a></span>';
			}
			$Msg='<span style="color: red;font-weight: bold;">'.__('Oblio','albo-online').'</span>';			
			break;			
        case "Cerca": /* mr */
            if( $item->Numero == 0 ){    
				$Msg=('<span style="color: green;font-weight: bold;">'.__('Da Pubblicare','albo-online').'</span>');
                $actions['cancella'] ='<span class="trash"><a href="?page=atti&amp;action=delete-atto&amp;id='.$item->IdAtto.'&amp;cancellaatto='.
			wp_create_nonce('deleteatto').'" rel="'.strip_tags($item->Oggetto).'" tag="" class="ac">
					<span class="delete dashicons dashicons-trash" title="'.__('Cancella Atto','albo-online').'"></span>
				</a></span>';
				$this->AzioneDefault='<a href="?page=atti&amp;action=edit-atto&amp;id='.$item->IdAtto.'&amp;modificaatto='.
				wp_create_nonce('editatto').'">';
				$actions['modifica'] ='<a href="?page=atti&amp;action=edit-atto&amp;id='.$item->IdAtto.'&amp;modificaatto='.
				wp_create_nonce('editatto').'">
					<span class="dashicons dashicons-edit" title="'.__('Modifica atto','albo-online').'"></span>
				</a>';
				$actions['allegati'] ='<a href="?page=atti&amp;action=allegati-atto&amp;id='.$item->IdAtto.'&amp;allegatoatto='.
				wp_create_nonce('gestallegatiatto').'">
					<span class="dashicons dashicons-upload" title="'.__('Allegati','albo-online').'"></span>
				</a>';
				if (current_user_can('editore_atti_albo')){
				$actions['pubblica'] ='<a href="?page=atti&amp;action=approva-atto&amp;id='.$item->IdAtto.'"  >
	<span class="dashicons dashicons-share-alt" title="'.__('Pubblica atto','albo-online').'"></span>
					</a>';
				}         
            }else{
                $Msg=($Annullato?'<span style="color: red;font-weight: bold;">'.__('Annullato','albo-online').'</span>':'<span style="color: green;font-weight: bold;">'.__('Pubblicato','albo-online').'</span>');    
                }
                break;
	}
	if( !$Scaduto and $Annullato=='' and ($this->stato_atti=="Correnti" || $this->stato_atti=="Cerca") and current_user_can('editore_atti_albo')){
		$actions['annulla'] ='<span class="trash"><a class="annullaatto" href="?page=atti&amp;action=annullamento-atto&amp;id='.$item->IdAtto.'">
				<span class="dashicons dashicons-dismiss" title="'.__('Annulla atto','albo-online').'"></span>
			</a></span>';
	}
	return sprintf('%1$s %2$s',$Msg,$this->row_actions($actions));
  }  
  function column_Ente($item) { 
  	$Ente=ap_get_ente($item->Ente);
  	if($Ente===FALSE){
		return "<spam style=\"color:red;\">".__('Ente non definito','albo-online')."</spam>";
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
		return __('Non Definita','albo-online');
	}
  }   
  function column_dataoblio($item) { 
    return ap_VisualizzaData($item->DataOblio); 
  }  

// Definire la nuova funzione per indicare le
// azioni che devo essere presenti sul menu a tendina

	function get_bulk_actions() {
	  if (isset($_GET['stato_atti']) And $_GET['stato_atti']=="Eliminare" And current_user_can('editore_atti_albo'))	
	  	return array('delete_bulk_atti' => __('Elimina','albo-online'));
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
			
			case "oblio-allegati-atto":
				if ( isset( $_GET['oaatto'] ) && ! empty( $_GET['oaatto'] ) ) {
		            $nonce  = filter_input( INPUT_GET, 'oaatto', FILTER_SANITIZE_STRING );
		            $action = 'operazioneoblioallegati';
		            if ( ! wp_verify_nonce( $nonce, $action ) )
		                wp_die( __("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione è stata annullata","albo-online") ,__("Problemi di sicurezza","albo-online"),array("back_link" => "?page=atti&stato_atti=Correnti") );
			 		if (is_numeric($_REQUEST['id'])) {
 	                    $MessaggiRitorno=CancellaAllegatiAtto((int)$_REQUEST['id']);
					}
				}else
					wp_die( __("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione è stata annullata","albo-online") ,__("Problemi di sicurezza","albo-online"),array("back_link" => "?page=atti") );					
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
			PreApprovazione((int)$_REQUEST['id'],sprintf(__('Anno Albo settato a %s Numero progressivo settato a 0','albo-online'),date("Y")));
			break;
		case "approva-atto" :
			$ret="";
			if (isset($_REQUEST['apa'])){
				$ret=ap_update_selettivo_atto((int)$_REQUEST['id'],array('Anno' => $_REQUEST['apa']),array('%s'),__('Modifica in Approvazione','albo-online')."\n");
			}
			if (isset($_REQUEST['pnp'])){
				update_option( 'opt_AP_NumeroProgressivo', (int)$_REQUEST['pnp']);
			}
			if (isset($_REQUEST['udi'])){
				$ret=ap_update_selettivo_atto((int)$_REQUEST['id'],array('DataInizio' => $_REQUEST['udi']),array('%s'),__('Modifica in Approvazione','albo-online')."\n");	
			}
			if (isset($_REQUEST['udf'])){
				$ret=ap_update_selettivo_atto((int)$_REQUEST['id'],array('DataFine' => $_REQUEST['udf']),array('%s'),__('Modifica in Approvazione','albo-online')."\n");	
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
				Lista_Atti(__("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione è stata annullata","albo-online"));
				break;	
			}
			if (!wp_verify_nonce($_REQUEST['allegatoatto'],'gestallegatiatto')){
				Lista_Atti(__("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione è stata annullata","albo-online"));
				break;
			} 		
			Allegati_atto((int)$_REQUEST['id'],(isset($_REQUEST['messaggio'])?$_REQUEST['messaggio']:""));
			break;
		case "edit-allegato-atto" :
			if (!isset($_REQUEST['modificaallegatoatto'])) {
				Lista_Atti(__("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione è stata annullata","albo-online"));
				break;	
			}
			if (!wp_verify_nonce($_REQUEST['modificaallegatoatto'],'editallegatoatto')){
				Lista_Atti(__("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione è stata annullata","albo-online"));
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
		<a href="<?php echo site_url();?>/wp-admin/admin.php?page=atti&stato_atti=<?php echo filter_input(INPUT_GET,"stato_atti");?>" class="add-new-h2 tornaindietro"><?php _e("Torna indietro","albo-online");?></a>
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
			<h2 class="hndle"><span><?php _e("Meta Dati Personalizzati","albo-online");?></span> <button type="button" id="AddMeta" class="setta-def-data">Aggiungi Meta Valore</button></h2>
				<div style="display:none;" id="newMeta">
					<label for="listaAttiMeta"><?php _e("Meta già codificati","albo-online");?></label> <?php echo ap_get_elenco_attimeta("Select","listaAttiMeta","ListaAttiMeta","Si");?>
					<label for="newMetaName"><?php _e("Nome Meta","albo-online");?></label> <input name="newMetaName" id="newMetaName"/>
					<label for="newValue"><?php _e("Valore Meta","albo-online");?></label> <input name="newValue" id="newValue">
					<button type="button"class="setta-def-data" id="AddNewMeta">Aggiungi</button> <button type="button"class="setta-def-data" id="UndoNewMedia">Anulla</button>
				</div>
<?php				echo ap_get_elenco_attimeta("Div","","","",$IdAtto);			?>
			</div>
			<div class="col-wrap postbox" style="padding:10px;margin-left:10px;">
				<input type="submit" name="AggiornaMetaDati" id="AggiornaMetaDati" style="margin:auto;" class="button button-primary button-large" value="<?php _e("Memorizza Modifiche Meta Dati Atto","albo-online");?>" />
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
					<th style="width:20%;"><?php _e("Ente emittente","albo-online");?></th>
					<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;"><?php echo $NomeEnte;?></td>
				</tr>
<?php
		if($risultato->DataAnnullamento!='0000-00-00')		
			echo '		<tr>
				<th style="width:20%;">'.__("Data Annullamento","albo-online").'</th>
				<td style="font-size:14px;font-weight: bold;color: Red;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataAnnullamento).'</td>
			</tr>
	    	<tr>
				<th style="width:20%;">'.__("Motivo Annullamento","albo-online").'</th>
				<td style="font-size:14px;font-weight: bold;color: Red;vertical-align:top;">'.stripslashes($risultato->MotivoAnnullamento).'</td>
			</tr>';
		echo '		<tr>
				<th style="width:20%;">'.__("Numero Albo","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.$risultato->Numero."/".$risultato->Anno.'</td>
			</tr>
			<tr>
				<th>'.__("Data","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->Data).'</td>
			</tr>
			<tr>
				<th>'.__("Codice di Riferimento","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Riferimento).'</td>
			</tr>
			<tr>
				<th>'.__("Oggetto","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Oggetto).'</td>
			</tr>
			<tr>
				<th>'.__("Data inizio Pubblicazione","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataInizio).'</td>
			</tr>
			<tr>
				<th>'.__("Data fine Pubblicazione","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataFine).'</td>
			</tr>
			<tr>
				<th>'.__("Data Oblio","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataOblio).'</td>
			</tr>
			<tr>
				<th>'.__("Note","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Informazioni).'</td>
			</tr>
			<tr>
				<th>'.__("Categoria","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultatocategoria->Nome).'</td>
			</tr>
				<tr>
					<th>'.__("Soggetti","albo-online").'</th>
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
	<h3>'.__("Allegati","albo-online").'</h3>
	<div class="Visalbo">';
$allegati=ap_get_all_allegati_atto($IdAtto);
$TipidiFiles=ap_get_tipidifiles();
foreach ($allegati as $allegato) {
	$Estensione=ap_ExtensionType($allegato->Allegato);	
	echo '<div style="border: thin dashed;font-size: 1em;">
			<div style="float: left;display: inline;width: 40px;height: 40px;padding-top:5px;padding-left:5px;">
				<img src="'.$TipidiFiles[strtolower($Estensione)]['Icona'].'" alt="'.$TipidiFiles[strtolower($Estensione)]['Descrizione'].'" height="30" width="30"allegato/>
			</div>
			<div style="margin-top:0;">
				<p style="margin-top:0;">'.strip_tags($allegato->TitoloAllegato).' <br />';
			if (is_file($allegato->Allegato))
				echo '        <a href="'.ap_DaPath_a_URL($allegato->Allegato).'" >'. basename( $allegato->Allegato).'</a> ('.ap_Formato_Dimensione_File(filesize($allegato->Allegato)).')<br />'.htmlspecialchars_decode($TipidiFiles[strtolower($Estensione)]['Verifica']);
			else
				echo basename( $allegato->Allegato).__("File non trovato, il file è stato cancellato o spostato!","albo-online");
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
	echo '<div id="message" class="updated"><p>'.__("Questa Operazione non ti è consentita, operazione di pertinenza dell'amministratore dell'Albo o del redattore","albo-online").'</p></div>';
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
		$AppPostMigrazione=" <span style='color:red;'>".__("Validato perchè primo atto dopo l'INSTALLAZIONE","albo-online")." </span>";
	else
		$AppPostMigrazione="";
echo'
<div class="wrap">
	<div class="HeadPage">
		<h2 class="wp-heading-inline"><span class="dashicons dashicons-portfolio"></span> Atti</h2>
		<a href="'.site_url().'/wp-admin/admin.php?page=atti" class="add-new-h2 tornaindietro">'.__("Torna indietro","albo-online").'</a>';
	if ( $ret!="" ) {
		echo '<div id="message" class="updated"><p>'.$ret.'</p></div>';
	}
	echo '
		<h3>'.__("Approvazione Atto","albo-online").'</h3>	
	</div>
	<br class="clear" />';
if(get_option('opt_AP_AnnoProgressivo')!=date("Y")){
	echo '<div style="border: medium groove Blue;margin-top:10px;">
			<div style="float:none;width:200px;margin-left:auto;margin-right:auto;">
				<form id="agg_anno_progressivo" method="post" action="?page=atti">
				<input type="hidden" name="action" value="setta-anno" />
				<input type="hidden" name="id" value="'.$id.'" />
				<input type="submit" name="submit" id="submit" class="button" value="'.__("Aggiorna Anno Albo ed Azzera numero Progressivo","albo-online").'"  />
				</form>
			</div>
		</div>';
}else
{
echo'<br />
<table class="widefat">
	<thead>	
	<tr>
		<th colspan="2" style="text-align:center;font-size:1.5em;">'.__("Informazioni","albo-online").'</th>
		<th style="text-align:center;font-size:1.5em;">'.__("Stato","albo-online").'</th>
		<th style="text-align:center;font-size:1.5em;">'.__("Operazioni","albo-online").'</th>
		<th style="text-align:center;font-size:1.5em;">'.__("Operazioni","albo-online").'</th>
	</tr>
	</thead>
    <tbody id="dati-atto">
	<tr>
		<td>'.__("Anno Atto","albo-online").'</td>
		<td>'.$atto->Anno.'</td>';
		if ($atto->Anno==date("Y")){
		 	$Passato=true;
			echo '<td colspan="2">'.__("Ok","albo-online").'</td>';
		}else{
		 	$Passato=false;
			echo '<td>'.__("Verificata incongruenza, bisogna rimediare prima di proseguire","albo-online").'</td>
			      <td><a href="?page=atti&amp;action=approva-atto&amp;id='.$id.'&amp;apa='.date("Y").'" class="add-new-h2">Imposta Anno Pubblicazione a '.date("Y").'</td>';
		}
		echo '</tr>';
		if($Passato){
			echo '<tr>
			<td>'.__("Numero Atto","albo-online").'</td>
			<td>'.sprintf(__("da Parametri %s Progressivo da ultima pubblicazione","albo-online"),get_option('opt_AP_NumeroProgressivo')).' '.$NumeroDaDb.$AppPostMigrazione.'</td>';
			if (($NumeroDaDb==$NumeroOpzione) Or $NumAttiPubblicati==0){
			 	$Passato=true;
				echo '<td colspan="2">'.__("Ok","albo-online").'</td>';
			}else{
			 	$Passato=false;
				echo '<td>'.__("Verificata incongruenza, bisogna rimediare prima di proseguire","albo-online").'</td>
				      <td><a href="?page=atti&amp;action=approva-atto&amp;id='.$id.'&amp;pnp='.$NumeroDaDb.'" class="add-new-h2">'.__("Imposta Parametro a","albo-online").' '.$NumeroDaDb.'</td>';
			}
			echo '</tr>';
		}
		if($Passato){
			echo '<tr>
					<td>'.__("Data Inizio Pubblicazione","albo-online").'</td>
					<td>'.$atto->DataInizio.'</td>';
			if($atto->DataInizio==ap_oggi()){
				$Passato=true;
				echo '<td colspan="2">'.__("Ok","albo-online").'</td>';
			}else{
	 			$Passato=false;
	   			echo '<td>'.__("Aggiornare la data di Inizio Pubblicazione","albo-online").'</td>
			      <td><a href="?page=atti&amp;action=approva-atto&amp;id='.$id.'&amp;udi='.ap_oggi().'" class="add-new-h2">'.__("Aggiorna a","albo-online").' '.ap_oggi().'</td>';
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
					<td>'.__("Data Fine Pubblicazione","albo-online").'</td>
					<td>'.sprintf(__("%s Giorni Pubblicazione Atto %s Giorni Pubblicazione standard Categoria %s","albo-online"),$atto->DataFine,$differenza,$categoria[0]->Giorni).'</td>';
			if(ap_SeDate(">=",$atto->DataFine,$atto->DataInizio)){
				$Passato=true;
				if (ap_datediff("d", $atto->DataInizio, $atto->DataFine)== $categoria[0]->Giorni){
					echo '<td colspan="2">'.__("Ok","albo-online").'</td>';
				}else{
					echo '<td>'.__("Ok","albo-online").'</td>';
					echo '<td><a href="?page=atti&amp;action=approva-atto&amp;id='.$id.'&amp;udf='.$newDataFine.'" class="add-new-h2">Aggiorna a '.$newDataFine.'</a></td>';
				}
			}else{
	 			$Passato=false;
	   			echo '<td>'.__("Aggiornare la data di Fine Pubblicazione","albo-online").'</td>
			      <td><a href="?page=atti&amp;action=approva-atto&amp;id='.$id.'&amp;udf='.$newDataFine.'" class="add-new-h2">'.__("Aggiorna a","albo-online").' '.$newDataFine.'</a></td>';
			}
			echo '</tr>';
		}
		if($Passato){
  			$incrementoStandard=get_option('opt_AP_GiorniOblio');
 			$DataOblioStandard=(date("Y")+6)."-01-01";
 			//echo $atto->DataInizio."   -  ".$incrementoStandard;
			echo '<tr>
					<td>'.__("Data Oblio","albo-online").'</td>
					<td>'.sprintf(__("%s - Data Oblio da Decreto n. 33/2013 art. 8 %s","albo-online"),$atto->DataOblio,$DataOblioStandard).'</td>';
				//	echo $atto->DataFine.' '.$atto->DataInizio. ' '.SeDate("<=",$atto->DataFine,$atto->DataInizio);
			if(ap_SeDate("=",$atto->DataOblio,$DataOblioStandard)){
				$Passato=true;
				echo '<td colspan="2">'.__("Ok","albo-online").'</td>';
			}else{
				echo '<td>'.__("Ok","albo-online").'</td>';
				echo '<td><a href="?page=atti&amp;action=approva-atto&amp;id='.$id.'&amp;udo='.$DataOblioStandard.'" class="add-new-h2">'.__("Aggiorna a","albo-online").' '.$DataOblioStandard.'</a></td>';
			}
		echo '</tr>';
		}
		if($Passato){
 			$numAllegati=ap_get_num_allegati($id);
			echo '<tr>
					<td>'.__("Allegati","albo-online").'</td>
					<td>'.__("N.","albo-online").' '.$numAllegati.'</td>';
			if($numAllegati>0){
				$Passato=true;
					echo '<td colspan="2">'.__("Ok","albo-online").'</td>';
				}else{
					$Passato=false;
					echo '<td>'.__("Da revisionare","albo-online").'</td>
					      <td><a href="?page=atti&amp;id='.$id.'&amp;action=UpAllegati&amp;ref=approva-atto" class="add-new-h2">'.__("Inserisci Allegato","albo-online").'</a></td>';
				}
			echo '</tr>';
		}
		if($Passato){
			if(strlen($atto->Richiedente)<1){
				$Passato=false;
				echo '<tr>
					<td>'.__("Richiedente","albo-online").'</td>
					<td>'.__("Richiesto","albo-online").'</td>
					<td>'.__("Da revisionare","albo-online").'</td>
				      <td><a href="?page=atti&action=edit-atto&id='.$id.'&amp;modificaatto='.wp_create_nonce('editatto').'" class="add-new-h2">'.__("Modifica atto","albo-online").'</a></td>';
			}
			echo '</tr>';
		}
		if($Passato){
			if($atto->IdUnitaOrganizzativa==0){
				$Passato=false;
				echo '<tr>
					<td>'.__("Unità Organizzativa Responsabile","albo-online").'</td>
					<td>'.__("Richiesto","albo-online").'</td>
					<td>'.__("Da revisionare","albo-online").'</td>
				      <td><a href="?page=atti&action=edit-atto&id='.$id.'&amp;modificaatto='.wp_create_nonce('editatto').'" class="add-new-h2">'.__("Modifica atto","albo-online").'</a></td>';
			}
			echo '</tr>';
		}
		if($Passato){
			if($atto->RespProc==0){
				$Passato=false;
				echo '<tr>
					<td>'.__("Responsabile del procedimento amministrativo","albo-online").'</td>
					<td>'.__("Richiesto","albo-online").'</td>
					<td>'.__("Da revisionare","albo-online").'</td>
				      <td><a href="?page=atti&action=edit-atto&id='.$id.'&amp;modificaatto='.wp_create_nonce('editatto').'" class="add-new-h2">'.__("Modifica atto","albo-online").'</a></td>';
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
<h3>'.__("Documenti/Allegati","albo-online").'</h3>';
$righe=ap_get_all_allegati_atto($id,array("Natura","IdAllegato"),array("DESC","ASC"));
$Ente=ap_get_ente($atto->Ente);
$Unitao=ap_get_unitaorganizzativa($atto->IdUnitaOrganizzativa);
$NomeResp=ap_get_responsabile($atto->RespProc);
$NomeResp=$NomeResp[0];
echo'
	<table class="widefat">
	    <thead>
		<tr>
			<th style="font-size:1.5em;">'.__("Operazioni","albo-online").'</th>
			<th style="font-size:1.5em;">'. __("Natura doc.","albo-online").'</th>
			<th style="font-size:1.5em;">'.__("Allegato","albo-online").'</th>
			<th style="font-size:1.5em;">'.__("File","albo-online").'</th>
			<th style="font-size:1.5em;">'. __("Doc. Integrale","albo-online").'</th>
		</tr>
	    </thead>
	    <tbody id="righe-log">';
foreach ($righe as $riga) {
	echo '<tr>
			<td>	
					<a href="'.ap_DaPath_a_URL($riga->Allegato).'" target="_parent">
						<span class="dashicons dashicons-search" title="'.__("Visualizza dati atto","albo-online").'"></span>
					</a>
			</td>
			<td >'. basename( $riga->Natura=="A"?__("Allegato","albo-online"):__("Doc. Firmato","albo-online")).'</td>
			<td >'.$riga->TitoloAllegato.'</td>
			<td >'. basename( $riga->Allegato).'</td>
			<td >'. basename( $riga->DocIntegrale==1?__("Si","albo-online"):__("No","albo-online")).'</td>
		</tr>';
}
echo '    </tbody>
	</table>
</div>
</div>
<div id="col-left">
<div class="col-wrap">
<h3>'.__("Atto","albo-online").'</h3>
	<table class="widefat">
	    <thead>
		<tr>
			<th colspan="2" style="text-align:center;font-size:1.5em;">'.__("Dati atto","albo-online").'</th>
		</tr>
	    </thead>
	    <tbody id="dati-atto">
		<tr>
			<th>'.__("Ente emittente","albo-online").'</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($Ente->Nome).'</td>
		</tr>
		<tr>
			<th style="width:50%;">'.__("Numero Albo","albo-online").'</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.$atto->Numero."/".$atto->Anno.'</td>
		</tr>
		<tr>
			<th>'.__("Codice di Riferimento","albo-online").'</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($atto->Riferimento).'</td>
		</tr>
		<tr>
			<th>'.__("Oggetto","albo-online").'</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($atto->Oggetto).'</td>
		</tr>
		<tr>
			<th>'.__("Data di registrazione","albo-online").'</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.$atto->Data.'</td>
		</tr>
		<tr>
			<th>'.__("Data inizio Pubblicazione","albo-online").'</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.$atto->DataInizio.'</td>
		</tr>
		<tr>
			<th>'.__("Data fine Pubblicazione","albo-online").'</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.$atto->DataFine.'</td>
		</tr>
		<tr>
			<th>'.__("Data oblio","albo-online").'</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.$atto->DataOblio.'</td>
		</tr>
		<tr>
			<th>'.__("Richiedente","albo-online").'</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($atto->Richiedente).'</td>
		</tr>
		<tr>
			<th>'.__("Unità Organizzativa Responsabile","albo-online").'</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($Unitao->Nome).'</td>
		</tr>
		<tr>
			<th>'.__("Responsabile del procedimento amministrativo","albo-online").'</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($NomeResp->Nome." ".$NomeResp->Cognome).'</td>
		</tr>
		<tr>
			<th>'.__("Categoria","albo-online").'</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($categoria[0]->Nome).'</td>
		</tr>
		<tr>
			<th>'.__("Note","albo-online").'</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($atto->Informazioni).'</td>
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
					<th>'. __("Meta Dati","albo-online").'</th>
					<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.$Meta.'</td>
				</tr>';
	}
	echo'
			<tr>
				<th>'. __("Soggetti","albo-online").'</th>
				<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">
				<ul>';
	$Soggetti=unserialize($atto->Soggetti);
	if ($Soggetti){
		$Soggetti=ap_get_alcuni_soggetti_ruolo(implode(",",$Soggetti));
		foreach($Soggetti as $Soggetto){
			echo "
				<li><strong>".ap_get_Funzione_Responsabile($Soggetto->Funzione,"Descrizione")."</strong> <br />".$Soggetto->Nome." ".$Soggetto->Cognome." 
				</li>";
		}
	}
	echo'				
					</ul>
					</td>
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
		$defEnte=get_option('opt_AP_DefaultEnte');
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
	if (isset($_REQUEST['Unitao']))
		$Unitao=$_REQUEST['Unitao'];
	else
		$Unitao=0;
	if (isset($_REQUEST['Responsabile']))
		$Responsabile=$_REQUEST['Responsabile'];
	else{
		$Resp=ap_get_responsabili();
		if (count($Resp)>0)
			$Responsabile=$Resp[0]->IdResponsabile;
		else
			$Responsabile=0;	
	}
	if (isset($_REQUEST['Richiedente']))
		$Richiedente=$_REQUEST['Richiedente'];
	else	
		$Richiedente="";
	
	$DefaultSoggetti=get_option('opt_AP_DefaultSoggetti',
								array("RP"=>0,
	  								  "RB"=>0,
	  								  "AM"=>0));
	if(!is_array($DefaultSoggetti)){
		$DefaultSoggetti=json_decode($DefaultSoggetti,TRUE);
	}
$DataOblioStandard=(date("Y")+6)."-01-01";		
?>
<div id="errori" title="<?php _e("Validazione Dati","albo-online");?>" style="display:none">
  <h3><?php _e("Lista Campi con Errori","albo-online");?>:</h3><p id="ElencoCampiConErrori"></p><p style='color:red;font-weight: bold;'><?php _e("Correggere gli errori per continuare","albo-online");?></p>
</div>

<div class="wrap">
	<div class="HeadPage">
		<h2 class="wp-heading-inline"><span class="dashicons dashicons-portfolio"></span> <?php _e("Atti","albo-online");?></h2>
		<a href="<?php echo site_url().'/wp-admin/admin.php?page=atti';?>" class="add-new-h2 tornaindietro"><?php _e("Torna indietro","albo-online");?></a>
		<div class="Obbligatori">
		<span style="color:red;font-weight: bold;">*</span> <?php _e("i campi contrassegnati dall'asterisco sono <strong>obbligatori</strong>","albo-online");?>
		</div>
		<h3><?php _e("Nuovo Atto","albo-online");?></h3>	
	</div>
	<input type="hidden" id="NonVal" value="<?php _e("Non Valorizzato","albo-online");?>" />
	<input type="hidden" id="NonSOg" value="<?php _e("Nessun Soggetto selezionato, ne devi selezionare almeno UNO","albo-online");?>" />

		<form id="addatto" method="post" action="?page=atti" class="validate">
		<input type="hidden" name="action" value="add-atto" />
		<input type="hidden" name="id" value="<?php echo(int)(isset($_REQUEST['id'])?$_REQUEST['id']:0);?>" />
		<input type="hidden" name="nuovoatto" value="<?php echo wp_create_nonce('nuovoatto')?>" />

	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div id="riferimentodiv">
					<h2><?php _e("Riferimento","albo-online");?><span style="color:red;font-weight: bold;">*</span></h2>
					<textarea name="Riferimento" id="<?php echo _e("Riferimento","albo-online");?>"" rows="2" cols="255"  class="richiesto" style="width: 100%"><?php echo stripslashes($Riferimento);?></textarea>
				<label for="Riferimento" style="font-style: italic;"><?php _e("Codice di riferimento dell'atto, es. N. Protocollo","albo-online");?> </label>
				</div><!-- /riferimentodiv -->
				<div id="riferimentowrap">
					<h2><?php _e("Oggetto","albo-online");?><span style="color:red;font-weight: bold;">*</span></h2>
					<textarea name="Oggetto" id="<?php echo _e("Oggetto","albo-online");?>"" rows="10" cols="255"  class="richiesto" style="width: 100%"><?php echo stripslashes($Oggetto);?></textarea>
				<label for="Riferimento" style="font-style: italic;"><?php _e("Descrizione sintetica dell'atto","albo-online");?> </label>
				</div><!-- /riferimentowrap -->
				<div id="richiedente">
					<h2><?php _e("Richiedente","albo-online");?><span style="color:red;font-weight: bold;">*</span></h2>
					<input type="text" name="Richiedente" id="<?php echo _e("Richiedente","albo-online");?>" class="richiesto" style="width: 100%" value="<?php echo stripslashes($Richiedente);?>" />
				<label for="Richiedente" style="font-style: italic;"><?php _e("Dati identificativi (Nome Cognome) della persona che richiede la pubblicazione","albo-online");?> </label>
				</div><!-- /riferimentowrap -->
				<div id="notewrap">
					<h2><?php _e("Note","albo-online");?></h2>
					<div id="note-wrap">
						<?php wp_editor( stripslashes($Note), 'note_txt',
									array('wpautop'=>true,
										  'textarea_name' => 'Note',
										  'textarea_rows' => 10,
										  'teeny' => TRUE,
										  'media_buttons' => false)
										)?>
						<span style="font-style: italic;font-size: 0.8em;"><?php _e("Eventuali note a corredo dell'atto","albo-online");?></span>
					</div>
					</div><!-- /notewrap -->
				<div class="notewrap postbox" id="MetaDati">
				<h2 class='hndle'><span><?php _e("Meta Dati Personalizzati","albo-online");?></span> <button type="button" id="AddMeta" class="setta-def-data"><?php _e("Aggiungi Meta Valore","albo-online");?></button></h2>
					<div style="display:none;" id="newMeta">
						<label for="listaAttiMeta"><?php _e("Meta già codificati","albo-online");?></label> <?php echo ap_get_elenco_attimeta("Select","listaAttiMeta","ListaAttiMeta","Si");?>
						<label for="newMetaName"><?php _e("Nome Meta","albo-online");?></label> <input name="newMetaName" id="newMetaName"/>
						<label for="newValue"><?php _e("Valore Meta","albo-online");?></label> <input name="newValue" id="newValue">
						<button type="button"class="setta-def-data" id="AddNewMeta"><?php _e("Aggiungi","albo-online");?></button> <button type="button"class="setta-def-data" id="UndoNewMedia"><?php _e("Anulla","albo-online");?></button>
					</div>
<?php				//echo ap_get_elenco_attimeta("Div");			?>
				</div>
			</div><!-- /post-body-content -->

		<div id="postbox-container-1" class="postbox-container">
			<div id="postimagediv" class="postbox " >
				<h2 class='hndle'><span><?php _e("Memorizza","albo-online");?>Memorizza</span></h2>
				<div class="inside">
					<p><?php _e("Numero Albo","albo-online");?>: 
						<span style="font-weight: bold;">0000000/<?php echo date("Y");?></span>
					</p>
					<p class="hide-if-no-js">
					<input type="submit" name="MemorizzaDati" id="MemorizzaDati" class="button button-primary button-large" value="<?php _e("Memorizza Atto","albo-online");?>">
					</p>
				</div>
			</div>
			<div id="datediv" class="postbox " >
				<h2 class='hndle'><span><?php _e("Date","albo-online");?></span></h2>
				<div class="inside">
					<p><?php _e("Data di registrazione","albo-online");?>:
						<input name="Data" type="text" id="CalendarioMO" value="<?php echo ap_VisualizzaData($dataCorrente);?>" maxlength="10" size="10" />					
					</p>
					<p><abbr title="<?php _e("Data in cui inizia a validità legale dell'atto. Viene impostata automaticamente in fase di pubblicazione","albo-online");?>"><?php _e("Data inizio Pubblicazione","albo-online");?></abbr>:
						<input name="DataInizio" type="hidden" value="<?php echo $DataI;?>" />
					</p>
					<p><abbr title="<?php _e("Data fine validità legale dell'atto","albo-online");?>"><?php _e("Data fine Pubblicazione","albo-online");?></abbr>:
						<input name="DataFine" id="Calendario3" type="text" value="<?php echo $DataF;?>" maxlength="10" size="10" />	
					</p>		
					<p><abbr title="<?php _e("Data in cui l'atto viene eliminato dall'archivio, in base al Decreto n. 33/2013 art.8:<br />5 anni, decorrenti dal 1° gennaio dell'anno successivo a quello
da cui decorre l'obbligo di pubblicazione, e comunque fino a che gli atti pubblicati producono i loro effetti,
fatti salvi i diversi termini previsti dalla normativa in materia di trattamento dei dati personali e quanto
previsto dagli articoli 14, comma 2, e 15, comma 4","albo-online");?>"><?php _e("Data Oblio","albo-online");?></abbr>:
						<input name="DataOblio" id="Calendario4" type="text" value="<?php echo $DataO;?>" maxlength="10" size="10" />
						<button type="button" id="setta-def-data-o" class="setta-def-data" name="<?php echo ap_VisualizzaData($DataOblioStandard);?>" style="margin-top: 5px;margin-left:10px;"> <?php _e("Aggiorna a","albo-online");?> <?php echo ap_VisualizzaData($DataOblioStandard);?></button>	
					</p>				
				</div>
			</div>
			<div id="metadiv" class="postbox " >
				<h2 class='hndle'><span><?php _e("Meta dati","albo-online");?></span></h2>
				<div class="inside">
					<p><abbr title="<?php _e("Ente che pubblica l'atto; potrebbe essere diverso dall'ente titolare del sito web se la pubblicazione avviene per conto di altro ente","albo-online");?>"><?php _e("Ente","albo-online");?><span style="color:red;font-weight: bold;">*</span></abbr>: 
						<?php echo ap_get_dropdown_enti('Ente',__('Ente','albo-online'),'postform maxdime richiesto ValValue(>-1)','',$defEnte);?>
					</p>
					<p><abbr title="<?php _e("Categoria in cui viene collocato l'atto, questo sistema permette di raggruppare gli oggetti in base alla lor natura","albo-online");?>"><?php _e("Categoria","albo-online");?><span style="color:red;font-weight: bold;">*</span></abbr>:
						<?php echo ap_get_dropdown_categorie('Categoria',__('Categoria','albo-online'),'postform maxdime richiesto ValValue(>0)','',$Categoria);?>					
					</p>
					<p><abbr title="<?php _e("Unità Organizzativa responsabile del procedimento amministrativo","albo-online");?>"><?php _e("Unità Organizzativa Responsabile","albo-online");?><span style="color:red;font-weight: bold;">*</span></abbr>:
						<?php echo ap_get_dropdown_unitao('Unitao',__("Unità Organizzativa Responsabile","albo-online"),'postform maxdime richiesto ValValue(>0)','',$Unitao);?>					
					</p>		
					<p><?php _e("Responsabile del procedimento amministrativo","albo-online");?><span style="color:red;font-weight: bold;">*</span>:
						<?php echo ap_get_dropdown_responsabili("Responsabile",__("Responsabile del procedimento amministrativo","albo-online"),"postform maxdime richiesto ValValue(>0)","",(isset($DefaultSoggetti["RP"])?$DefaultSoggetti["RP"]:0),"RP");?>					
					</p>							
				</div>
			</div>
			<div id="metadiv" class="postbox " >
				<h2 class='hndle'><span><?php _e("Soggetti","albo-online");?></span></h2>
				<div class="inside">
					<p><?php _e("In questo spazio bisogna codificare i soggetti che sono coinvolti in questo atto, possono essere specificati più soggetti","albo-online");?>
					</p>
					<ul>
<?php
		$Ana_Soggetti=ap_get_responsabili();
		foreach($Ana_Soggetti as $Soggetto){
			if($Soggetto->Funzione!="RP"){
				$Sel="";
				if(is_array($DefaultSoggetti)And in_array($Soggetto->IdResponsabile,$DefaultSoggetti)){
					$Sel=" checked ";
				}
				echo "
				<li>
					<input type=\"checkbox\" name=\"Soggetto[]\" value=\"$Soggetto->IdResponsabile\"  $Sel/>".$Soggetto->Cognome." ".$Soggetto->Nome." <strong><em>".ap_get_Funzione_Responsabile($Soggetto->Funzione,"Descrizione")."</em></strong>
				</li>";				
			}

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
<div id="errori" title="<?php _e("Validazione Dati","albo-online");?>" style="display:none">
  <h3><?php _e("Lista Campi con Errori","albo-online");?>:</h3>
  	<p id="ElencoCampiConErrori"></p>
  	<p style='color:red;font-weight: bold;'><?php _e("Correggere gli errori per continuare","albo-online");?></p>
</div>
<div class="wrap">
	<div class="HeadPage">
		<h2 class="wp-heading-inline"><span class="dashicons dashicons-portfolio"></span> <?php _e("Atti","albo-online");?></h2>
		<a href="<?php echo site_url().'/wp-admin/admin.php?page=atti';?>" class="add-new-h2 tornaindietro"><?php _e("Torna indietro","albo-online");?></a>
		<div class="Obbligatori">
		<span style="color:red;font-weight: bold;">*</span> <?php _e("i campi contrassegnati dall'asterisco sono <strong>obbligatori</strong>","albo-online");?>
		</div>
		<h3><?php _e("Modifica Atto","albo-online");?></h3>	
	</div>
	<input type="hidden" id="NonVal" value="<?php _e("Non Valorizzato","albo-online");?>" />
	<input type="hidden" id="NonSOg" value="<?php _e("Nessun Soggetto selezionato, ne devi selezionare almeno UNO","albo-online");?>" />

	<form id="addatto" method="post" action="?page=atti" class="validate">
		<input type="hidden" name="action" value="memo-atto" />
		<input type="hidden" name="id" value="<?php echo (int)$_REQUEST['id'];?>" />
		<input type="hidden" name="modificaatto" value="<?php echo wp_create_nonce('editatto')?>" />
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div id="riferimentodiv">
					<h2><?php _e("Riferimento","albo-online");?><span style="color:red;font-weight: bold;">*</span></h2>
					<textarea name="Riferimento" id="<?php echo _e("Riferimento","albo-online");?>" rows="2" cols="255"  class="richiesto" style="width: 100%" alt="<?php echo _e("Riferimento","albo-online");?>"><?php echo stripslashes($atto->Riferimento);?></textarea>
				<label for="Riferimento" style="font-style: italic;"><?php _e("Codice di riferimento dell'atto, es. N. Protocollo","albo-online");?> </label>
				</div><!-- /riferimentodiv -->
				<div id="riferimentowrap">
					<h2><?php _e("Oggetto","albo-online");?><span style="color:red;font-weight: bold;">*</span></h2>
					<textarea name="Oggetto" id="<?php echo _e("Oggetto","albo-online");?>" rows="10" cols="255"  class="richiesto" style="width: 100%"><?php echo stripslashes($atto->Oggetto);?></textarea>
				<label for="Riferimento" style="font-style: italic;"><?php _e("Descrizione sintetica dell'atto","albo-online");?> </label>
				</div><!-- /riferimentowrap -->
				<div id="richiedente">
					<h2><?php _e("Richiedente","albo-online");?><span style="color:red;font-weight: bold;">*</span></h2>
					<input type="text" name="Richiedente" id="<?php echo _e("Richiedente","albo-online");?>" class="richiesto" style="width: 100%" value="<?php echo stripslashes($atto->Richiedente);?>" />
				<label for="Richiedente" style="font-style: italic;"><?php _e("Dati identificativi (Nome Cognome) della persona che richiede la pubblicazione","albo-online");?> </label>
				</div><!-- /riferimentowrap -->
				<div id="notewrap">
					<h2><?php _e("Note","albo-online");?></h2>
					<div id="note-wrap">
						<?php wp_editor( stripslashes($atto->Informazioni), 'note_txt',
									array('wpautop'=>true,
										  'textarea_name' => 'Note',
										  'textarea_rows' => 10,
										  'teeny' => TRUE,
										  'media_buttons' => false)
										)?>
						<span style="font-style: italic;font-size: 0.8em;"><?php _e("Note","albo-online");?>Eventuali note a corredo dell'atto</span>
					</div>
					</div><!-- /notewrap -->
				<div class="notewrap postbox" id="MetaDati">
				<h2 class='hndle'><span><?php _e("Meta Dati Personalizzati","albo-online");?></span> <button type="button" id="AddMeta" class="setta-def-data"><?php _e("Aggiungi Meta Valore","albo-online");?></button></h2>
					<div style="display:none;" id="newMeta">
						<label for="listaAttiMeta"><?php _e("Meta già codificati","albo-online");?></label> <?php echo ap_get_elenco_attimeta("Select","listaAttiMeta","ListaAttiMeta","Si");?>
						<label for="newMetaName"><?php _e("Nome Meta","albo-online");?></label> <input name="newMetaName" id="newMetaName"/>
						<label for="newValue"><?php _e("Valore Meta","albo-online");?></label> <input name="newValue" id="newValue">
						<button type="button"class="setta-def-data" id="AddNewMeta"><?php _e("Aggiungi","albo-online");?></button> <button type="button"class="setta-def-data" id="UndoNewMedia"><?php _e("Anulla","albo-online");?></button>
					</div>
<?php				echo ap_get_elenco_attimeta("Div","","","",$id);			?>
				</div>
			</div><!-- /post-body-content -->

		<div id="postbox-container-1" class="postbox-container">
			<div id="postimagediv" class="postbox " >
				<h2 class='hndle'><span><?php _e("Memorizza","albo-online");?></span></h2>
				<div class="inside">
					<p><?php _e("Numero Albo","albo-online");?>: 
						<span style="font-weight: bold;">0000000/<?php echo $atto->Anno;?></span>
					</p>
					<p class="hide-if-no-js">
						<input type="submit" name="MemorizzaDati" id="MemorizzaDati" class="button button-primary button-large" value="<?php _e("Memorizza Modifiche Atto","albo-online");?>" />
					</p>
				</div>
			</div>
			<div id="datediv" class="postbox " >
				<h2 class='hndle'><span><?php _e("Date","albo-online");?></span></h2>
				<div class="inside">
					<p><abbr title="<?php _e("viene inserita automaticamente nel momento in cui viene creato","albo-online");?>."><?php _e("Data di registrazione","albo-online");?></abbr>: 
						<input name="Data" type="text" id="CalendarioMO" value="<?php echo ap_VisualizzaData($atto->Data);?>" maxlength="10" size="10" />
					</p>
					<p><abbr title="<?php _e("Data in cui inizia a validità legale dell'atto. Viene impostata automaticamente in fase di pubblicazione","albo-online");?>"><?php _e("Data inizio Pubblicazione","albo-online");?></abbr>:
						<input name="DataInizio" type="hidden" value="<?php echo ap_VisualizzaData($atto->DataInizio);?>" />
						<em><strong><?php echo ap_VisualizzaData($atto->DataInizio);?></strong></em>					
					</p>
					<p><abbr title="<?php _e("Data fine validità legale dell'atto","albo-online");?>"><?php _e("Data fine Pubblicazione","albo-online");?></abbr>:
						<input name="DataFine" id="Calendario3" type="text" value="<?php echo ap_VisualizzaData($atto->DataFine);?>" maxlength="10" size="10" />		
					</p>		
					<p><abbr title="<?php _e("Data in cui l'atto viene eliminato dall'archivio, in base al Decreto n. 33/2013 art.8:<br />5 anni, decorrenti dal 1° gennaio dell'anno successivo a quello
da cui decorre l'obbligo di pubblicazione, e comunque fino a che gli atti pubblicati producono i loro effetti,
fatti salvi i diversi termini previsti dalla normativa in materia di trattamento dei dati personali e quanto
previsto dagli articoli 14, comma 2, e 15, comma 4","albo-online");?>"><?php _e("Data Oblio","albo-online");?></abbr>:
						<input name="DataOblio" id="Calendario4" type="text" value="<?php echo ap_VisualizzaData($atto->DataOblio);?>" maxlength="10" size="10" />
						<button type="button" id="setta-def-data-o" class="setta-def-data" name="<?php echo ap_VisualizzaData($DataOblioStandard);?>" style="margin-top: 5px;margin-left:10px;"> <?php _e("Aggiorna a","albo-online");?> <?php echo ap_VisualizzaData($DataOblioStandard);?></button>	
					</p>				
				</div>
			</div>
			<div id="metadiv" class="postbox " >
				<h2 class='hndle'><span><?php _e("Meta dati","albo-online");?></span></h2>
				<div class="inside">
					<p><abbr title="<?php _e("Ente che pubblica l'atto; potrebbe essere diverso dall'ente titolare del sito web se la pubblicazione avviene per conto di altro ente","albo-online");?>"><?php _e("Ente","albo-online");?><span style="color:red;font-weight: bold;">*</span></abbr>: 
						<?php echo ap_get_dropdown_enti('Ente',__("Ente","albo-online"),'postform maxdime richiesto ValValue(>-1)','',$atto->Ente);?>
					</p>
					<p><abbr title="<?php _e("Categoria in cui viene collocato l'atto, questo sistema permette di raggruppare gli oggetti in base alla lor natura","albo-online");?>"><?php _e("Categoria","albo-online");?><span style="color:red;font-weight: bold;">*</span></abbr>:
						<?php echo ap_get_dropdown_categorie('Categoria',__("Categoria","albo-online"),'postform maxdime richiesto ValValue(>0)','',$atto->IdCategoria);?>					
					</p>
					<p><abbr title="<?php _e("Unità Organizzativa responsabile del procedimento amministrativo","albo-online");?>"><?php _e("Unità Organizzativa Responsabile","albo-online");?><span style="color:red;font-weight: bold;">*</span></abbr>:
						<?php echo ap_get_dropdown_unitao('Unitao',__("Unità Organizzativa Responsabile","albo-online"),'postform maxdime richiesto ValValue(>0)','',$atto->IdUnitaOrganizzativa);?>					
					</p>
					<p><?php _e("Responsabile del procedimento amministrativo","albo-online");?><span style="color:red;font-weight: bold;">*</span>:
						<?php echo ap_get_dropdown_responsabili("Responsabile",__("Responsabile del procedimento amministrativo","albo-online"),"postform maxdime richiesto ValValue(>0)","",$atto->RespProc,"RP");?>					
					</p>	
				</div>
			</div>
			<div id="metadiv" class="postbox " >
				<h2 class='hndle'><span><?php _e("Soggetti","albo-online");?></span></h2>
				<div class="inside">
					<p><?php _e("In questo spazio bisogna codificare i soggetti che sono coinvolti in questo atto possono essere specificati più soggetti.","albo-online");?>
					</p>
					<ul>
<?php
		$Soggetti=unserialize($atto->Soggetti);
		$Ana_Soggetti=ap_get_responsabili();
		foreach($Ana_Soggetti as $Soggetto){
			if($Soggetto->Funzione!="RP"){
				$Selected="";
				if (is_array($Soggetti)And in_array($Soggetto->IdResponsabile,$Soggetti)) {
					$Selected=" checked ";
				}
				echo "
				<li>
					<input type=\"checkbox\" name=\"Soggetto[]\" value=\"$Soggetto->IdResponsabile\"  $Selected/>".$Soggetto->Cognome." ".$Soggetto->Nome." <strong><em>".ap_get_Funzione_Responsabile($Soggetto->Funzione,"Descrizione")."</em></strong>
				</li>";				
			}
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
		<h2 class="wp-heading-inline"><span class="dashicons dashicons-portfolio"></span> '. __("Atti","albo-online").'</h2>
		<a href="'. site_url().'/wp-admin/admin.php?page=atti&stato_atti=Nuovi" class="add-new-h2 tornaindietro">'. __("Torna indietro","albo-online").'</a>
		<h3>'. __("Allegati Atto","albo-online").'</h3>	
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
	echo '<h3>'. __("Modifica Allegato","albo-online").'</h3>
	<form id="allegato"  method="post" action="?page=atti" class="validate">
	<input type="hidden" name="action" value="update-allegato-atto" />
	<input type="hidden" name="id" value="'.$IdAtto.'" />
	<input type="hidden" name="idAlle" value="'.$IdAllegato.'" />
	<input type="hidden" name="modificaallegatoatto" value="'.wp_create_nonce("editallegatoatto").'" />
	<table class="widefat">
	    <thead>
		<tr>
			<th colspan="3" style="text-align:center;font-size:1.2em;">'. __("Dati Allegato","albo-online").'</th>
		</tr>
	    </thead>
	    <tbody id="dati-allegato">
		<tr>
			<th>'. __("Descrizione Allegato","albo-online").'</th>
			<td><textarea  name="titolo" rows="4" cols="50" wrap="ON" maxlength="255">'.$allegato->TitoloAllegato.'</textarea></td>
		</tr>
		<tr>
			<th>'. __("Natura File","albo-online").'</th>
			<td><select name="Natura" id="Natura" wrap="ON" >
				<option value="D" '.($allegato->Natura=="D"?"selected":"").'>Documento firmato</option>
				<option value="A" '.($allegato->Natura=="A"?"selected":"").'>Allegato</option>
			</select></td>
		</tr>
		<tr>
			<th>'. __("Documento Integrale?","albo-online").'</th>
			<td><input type="checkbox" name="Integrale" value="1" id="Integrale" '.($allegato->DocIntegrale=="1"?"checked":"").'></td>
		</tr>
		<tr>
			<th>'. __("File","albo-online").':</th>
			<td>'.$allegato->Allegato.'</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" id="submit" class="button" value="'. __("Aggiorna Allegato","albo-online").'"  />&nbsp;&nbsp;
			    <input type="submit" name="annulla" id="annulla" class="button" value="'. __("Annulla Operazione","albo-online").'"  />
		    </td>
		</tr>
	    </tbody>
	</table>
	</form>';	
}else{
	echo'
	<h3 style="margin-top:50px;">Allegati <a href="'.site_url().'/wp-admin/admin.php?page=atti&amp;id='.$IdAtto.'&amp;action=UpAllegati" class="add-new-h2">'. __("Aggiungi nuovo","albo-online").'</a> <a href="'.site_url().'/wp-admin/admin.php?page=atti&amp;id='.$IdAtto.'&amp;action=AssAllegati" class="add-new-h2">'. __("Associa file","albo-online").'</a></h3>';
	$righe=ap_get_all_allegati_atto($IdAtto);
	echo'
	<div  style="overflow: scroll;">
		<table class="widefat">
		    <thead>
			<tr>
				<th style="font-size:1.2em;">'. __("Operazioni","albo-online").'</th>
				<th style="font-size:1.2em;">'. __("Allegato","albo-online").'</th>
				<th style="font-size:1.2em;">'. __("File","albo-online").'</th>
				<th style="font-size:1.2em;">'. __("Natura doc.","albo-online").'</th>
				<th style="font-size:1.2em;">'. __("Doc. Integrale","albo-online").'</th>
				<th style="font-size:1.2em;">'. __("Impronta","albo-online").'</th>
			</tr>
		    </thead>
		    <tbody id="righe-log">';
	foreach ($righe as $riga) {
		$Testo_da=__("Confermi la cancellazione del'Allegato","albo-online")." ".strip_tags($riga->TitoloAllegato). "?\n\n".__("ATTENZIONE questa operazione cancellera' anche il file sul server!","albo-online")."\n\n".__("Sei sicuro di voler proseguire con la CANCELLAZIONE?","albo-online");
		echo '<tr>
				<td>	
					<a href="?page=atti&amp;action=delete-allegato-atto&amp;idAllegato='.$riga->IdAllegato.'&amp;idAtto='.$IdAtto.'&amp;Allegato='.$riga->TitoloAllegato.'&amp;cancellaallegatoatto='.wp_create_nonce('deleteallegatoatto').'" rel="'.$Testo_da.'" class="confdel">
						<span class="dashicons dashicons-trash" title="'. __("Cancella allegato","albo-online").'"></span>
					</a>
					<a href="?page=atti&amp;action=edit-allegato-atto&amp;id='.$IdAtto.'&amp;idAlle='.$riga->IdAllegato.'&amp;modificaallegatoatto='.wp_create_nonce('editallegatoatto').'" >
						 <span class="dashicons dashicons-edit" title="'. __("Modifica allegato","albo-online").'"></span>
					</a>
					<a href="'.ap_DaPath_a_URL($riga->Allegato).'" target="_blank">
							<span class="dashicons dashicons-search" title="'. __("Visualizza dati allegato","albo-online").'"></span>
					</a>
				</td>
				<td >'.$riga->TitoloAllegato.'</td>
				<td >'. basename( $riga->Allegato).'</td>
				<td >'. basename( $riga->Natura=="A"?__("Allegato","albo-online"):__("Doc. Firmato","albo-online")).'</td>
				<td >'. basename( $riga->DocIntegrale==1?__("Si","albo-online"):__("No","albo-online")).'</td>
				<td style="font-family: courier;">'. basename( $riga->Impronta).'</td>
			</tr>';
	}
	echo '    </tbody>
		</table>
	</div>';
}
$Ente=ap_get_ente($risultato->Ente);
$Unitao=ap_get_unitaorganizzativa($risultato->IdUnitaOrganizzativa);
$NomeResp=ap_get_responsabile($risultato->RespProc);
$NomeResp=$NomeResp[0];
echo'</div>
</div>
<div id="col-left">
<div class="col-wrap">
<h3>'. __("Dati Atto","albo-online").'</h3>
	<table class="widefat">
	    <thead>
		<tr>
			<th colspan="2" style="text-align:center;font-size:1.2em;">'. __("Dati atto","albo-online").'</th>
		</tr>
	    </thead>
	    <tbody id="dati-atto">
		<tr>
			<th>'.__("Ente emittente","albo-online").'</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($Ente->Nome).'</td>
		</tr>
		<tr>
			<th style="width:50%;">'. __("Numero Albo","albo-online").'</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.$risultato->Numero."/".$risultato->Anno.'</td>
		</tr>
		<tr>
			<th>'. __("Codice di Riferimento","albo-online").'</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Riferimento).'</td>
		</tr>
		<tr>
			<th>'. __("Oggetto","albo-online").'</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Oggetto).'</td>
		</tr>
		<tr>
			<th>'. __("Data di registrazione","albo-online").'</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->Data).'</td>
		</tr>
		<tr>
			<th>'. __("Data inizio Pubblicazione","albo-online").'</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataInizio).'</td>
		</tr>
		<tr>
			<th>'. __("Data fine Pubblicazione","albo-online").'</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataFine).'</td>
		</tr>
		<tr>
			<th>'. __("Data oblio","albo-online").'</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataOblio).'</td>
		</tr>
		<tr>
			<th>'.__("Richiedente","albo-online").'</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Richiedente).'</td>
		</tr>
		<tr>
			<th>'.__("Unità Organizzativa Responsabile","albo-online").'</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($Unitao->Nome).'</td>
		</tr>
		<tr>
			<th>'.__("Responsabile del procedimento amministrativo","albo-online").'</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($NomeResp->Nome." ".$NomeResp->Cognome).'</td>
		</tr>
		<tr>
			<th>'. __("Categoria","albo-online").'</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultatocategoria->Nome).'</td>
		</tr>
		<tr>
			<th>'. __("Note","albo-online").'</th>
			<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Informazioni).'</td>
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
				<th>'. __("Meta Dati","albo-online").'</th>
				<td style="vertical-align: middle;color: Red;">'.$Meta.'</td>
			</tr>';
}	
	echo'	<tr>
				<th>'. __("Soggetti","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">
				<ul>';
	$Soggetti=unserialize($risultato->Soggetti);
	$Soggetti=ap_get_alcuni_soggetti_ruolo(implode(",",$Soggetti));
	foreach($Soggetti as $Soggetto){
		echo "
			<li><strong>".ap_get_Funzione_Responsabile($Soggetto->Funzione,"Descrizione")."</strong> <br />".$Soggetto->Nome." ".$Soggetto->Cognome." 
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
	$Ente=ap_get_ente($risultato->Ente);
	$Unitao=ap_get_unitaorganizzativa($risultato->IdUnitaOrganizzativa);
	$NomeResp=ap_get_responsabile($risultato->RespProc);
	if(isset($NomeResp[0]))
		$NomeResp=$NomeResp[0];
	else
		$NomeResp="";
	echo '
<div class="wrap nosubsub">
	<div class="HeadPage">
		<h2 class="wp-heading-inline"><span class="dashicons dashicons-portfolio"></span> Atti</h2>
		<a href="'.site_url().'/wp-admin/admin.php?page=atti&stato_atti='.filter_input(INPUT_GET,"stato_atti").'" class="add-new-h2 tornaindietro">'. __("Torna indietro","albo-online").'</a>
		<h3>'. __("Visualizza dati Atto","albo-online").'</h3>	
	</div>
		<div class="clear"><br /></div>
		<div id="col-container">
		<div id="col-right">
				<div class="col-wrap postbox" style="padding:0 10px 10px 10px;margin-left:10px;">
				<h3>Log</h3>
				<hr />
					<div id="utility-tabs-container">
						<ul>
							<li><a href="#log-tab-1">'. __("Atto","albo-online").'</a></li>
							<li><a href="#log-tab-2">'. __("Allegati","albo-online").'</a></li>
							<li><a href="#log-tab-3">'. __("Statistiche Visite","albo-online").'</a></li>
							<li><a href="#log-tab-4">'. __("Statistiche Download","albo-online").'</a></li>
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
	<div class="col-wrap postbox" style="padding:0 10px 10px 10px;">
		<h3>'. __("Dati atto","albo-online").'</h3>
		<hr />
		<table class="widefat fixed striped" style="border:0;">
		    <tbody id="dati-atto">
			<tr>
				<th style="width:50%;">'. __("Ente emittente","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.$NomeEnte.'</td>
			</tr>
			<tr>
				<th style="width:20%;">'. __("Numero Albo","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.$risultato->Numero."/".$risultato->Anno.'</td>
			</tr>
			<tr>
				<th>'. __("Codice di Riferimento","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Riferimento).'</td>
			</tr>
			<tr>
				<th>'. __("Oggetto","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Oggetto).'</td>
			</tr>';
		if($risultato->DataAnnullamento!='0000-00-00')		
			echo '		<tr>
				<th style="width:20%;">'. __("Data Annullamento","albo-online").'</th>
				<td style="font-size:14px;font-weight: bold;color: Red;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataAnnullamento).'</td>
			</tr>
	    	<tr>
				<th style="width:20%;">'. __("Motivo Annullamento","albo-online").'</th>
				<td style="font-size:14px;font-weight: bold;color: Red;vertical-align:top;">'.stripslashes($risultato->MotivoAnnullamento).'</td>
			</tr>';
		echo '		
			<tr>
				<th>'. __("Data di registrazione","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->Data).'</td>
			</tr>

			<tr>
				<th>'. __("Data inizio Pubblicazione","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataInizio).'</td>
			</tr>
			<tr>
				<th>'. __("Data fine Pubblicazione","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataFine).'</td>
			</tr>
			<tr>
				<th>'. __("Data Oblio","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataOblio).'</td>
			</tr>
			<tr>
				<th>'.__("Richiedente","albo-online").'</th>
				<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Richiedente).'</td>
			</tr>
			<tr>
				<th>'.__("Unità Organizzativa Responsabile","albo-online").'</th>
				<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.(isset($Unitao->Nome)?stripslashes($Unitao->Nome):"").'</td>
			</tr>
			<tr>
				<th>'.__("Responsabile del procedimento amministrativo","albo-online").'</th>
				<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.(is_object($NomeResp)?$NomeResp->Nome." ".$NomeResp->Cognome:$NomeResp).'</td>
			</tr>
			<tr>
				<th>'. __("Categoria","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultatocategoria->Nome).'</td>
			</tr>
			<tr>
				<th>'. __("Note","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Informazioni).'</td>
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
					<th>'. __("Meta Dati","albo-online").'</th>
					<td style="vertical-align: middle;color: Red;">'.$Meta.'</td>
				</tr>';
}
echo'
			<tr>
				<th>'. __("Soggetti","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">
				<ul>';
	$Soggetti=unserialize($risultato->Soggetti);
	if ($Soggetti){
		$Soggetti=ap_get_alcuni_soggetti_ruolo(implode(",",$Soggetti));
		foreach($Soggetti as $Soggetto){
			echo "
				<li><strong>".ap_get_Funzione_Responsabile($Soggetto->Funzione,"Descrizione")."</strong><br />".$Soggetto->Nome." ".$Soggetto->Cognome." 
				</li>";
		}
	}
	echo'				
					</ul>
					</td>
				</tr>	    
				</tbody>
			</table>
		</div>';	
		$documenti=ap_get_documenti_atto($IdAtto);
		if(count($documenti)>0){
			echo '<div class="postbox" style="padding:0 10px 10px 10px;">
				<h3>'. __("Documenti firmati","albo-online").'</h3>
				<div class="Visalbo">';
			$TipidiFiles=ap_get_tipidifiles();
			foreach ($documenti as $allegato) {
				$Estensione=ap_ExtensionType($allegato->Allegato);	
				echo '<div style="border: thin dashed;font-size: 1em;">
						<div style="float: left;display: inline;width: 40px;height: 40px;padding-top:5px;padding-left:5px;">
							<img src="'.$TipidiFiles[strtolower($Estensione)]['Icona'].'" alt="'.$TipidiFiles[strtolower($Estensione)]['Descrizione'].'" height="30" width="30" />
						</div>
						<div style="margin-top:0;">
							<p style="margin-top:0;">
							'.($allegato->DocIntegrale!="1"?'<span class="evidenziato">'.__("Pubblicato per Estratto","albo-online")."</span><br />":"").'
							'.strip_tags($allegato->TitoloAllegato).' <br />';
						if (is_file($allegato->Allegato))
							echo '        <a href="'.ap_DaPath_a_URL($allegato->Allegato).'" >'. basename( $allegato->Allegato).'</a> ('.ap_Formato_Dimensione_File(filesize($allegato->Allegato)).')<br />'.htmlspecialchars_decode($TipidiFiles[strtolower($Estensione)]['Verifica']);
						else{
							echo basename( $allegato->Allegato)."<br />";
							if( $allegato->Note=="")
								echo __("File non trovato, il file è stato cancellato o spostato!","albo-online");
							else
								echo __("Note:","albo-online")." ".$allegato->Note;
						}
			echo'				</p>
						</div>
					</div>';
			}
			echo '</div>
	</div>';
		}
		$allegati=ap_get_allegati_atto($IdAtto);
		if(count($allegati)>0){
			echo '<div class="postbox" style="padding:0 10px 10px 10px;">
				<h3>'. __("Allegati","albo-online").'</h3>
				<div class="Visalbo">';
			$TipidiFiles=ap_get_tipidifiles();
			foreach ($allegati as $allegato) {
				$Estensione=ap_ExtensionType($allegato->Allegato);	
				echo '<div style="border: thin dashed;font-size: 1em;">
						<div style="float: left;display: inline;width: 40px;height: 40px;padding-top:5px;padding-left:5px;">
							<img src="'.$TipidiFiles[strtolower($Estensione)]['Icona'].'" alt="'.$TipidiFiles[strtolower($Estensione)]['Descrizione'].'" height="30" width="30" />		
						</div>
						<div style="margin-top:0;">
							<p style="margin-top:0;">
								'.($allegato->DocIntegrale!="1"?'<span class="evidenziato">'.__("Pubblicato per Estratto","albo-online")."</span><br />":"").'
								'.strip_tags($allegato->TitoloAllegato).' <br />';
						if (is_file($allegato->Allegato))
							echo '        <a href="'.ap_DaPath_a_URL($allegato->Allegato).'" >'. basename( $allegato->Allegato).'</a> ('.ap_Formato_Dimensione_File(filesize($allegato->Allegato)).')<br />'.htmlspecialchars_decode($TipidiFiles[strtolower($Estensione)]['Verifica']);
						else{
							echo basename( $allegato->Allegato)."<br />";
							if( $allegato->Note=="")
								echo __("File non trovato, il file è stato cancellato o spostato!","albo-online");
							else
								echo __("Note:","albo-online")." ".$allegato->Note;
						}
			echo'				</p>
						</div>
					</div>';
			}
			echo '</div>
	</div>';
		}
echo '</div>
	</div>
</div>';	
}


function CancellaAllegatiAtto($IdAtto){
	global $AP_OnLine;
	$risultato=ap_get_atto($IdAtto);
	$risultato=$risultato[0];
	$risultatocategoria=ap_get_categoria($risultato->IdCategoria);
	$risultatocategoria=$risultatocategoria[0];
	$NomeEnte=ap_get_ente($risultato->Ente);
	$NomeEnte=stripslashes($NomeEnte->Nome);
	$Ente=ap_get_ente($risultato->Ente);
	$Unitao=ap_get_unitaorganizzativa($risultato->IdUnitaOrganizzativa);
	$NomeResp=ap_get_responsabile($risultato->RespProc);
	if(isset($NomeResp[0]))
		$NomeResp=$NomeResp[0];
	else
		$NomeResp="";
	echo '
<div class="wrap nosubsub">
	<input type="hidden" id="IdAtto" value="'.$IdAtto.'" />
	<div class="HeadPage">
		<h2 class="wp-heading-inline"><span class="dashicons dashicons-portfolio"></span> Atti</h2>
		<a href="'.site_url().'/wp-admin/admin.php?page=atti&stato_atti='.filter_input(INPUT_GET,"stato_atti").'" class="add-new-h2 tornaindietro">'. __("Torna indietro","albo-online").'</a>
		<h3>'. __("Dati Atto","albo-online").'</h3>	
	</div>
		<div class="clear"><br /></div>
		<div id="col-container">
		<div id="col-right">
				<div class="col-wrap postbox" style="padding:0 10px 10px 10px;margin-left:10px;">
				<h3>Documenti</h3>
				<hr />';
		$documenti=ap_get_documenti_atto($IdAtto);
		if(count($documenti)>0){
			echo '<div class="postbox" style="padding:0 10px 10px 10px;">
				<h3>'. __("Documenti firmati","albo-online").'</h3>
				<div class="Visalbo">';
			$TipidiFiles=ap_get_tipidifiles();
			foreach ($documenti as $allegato) {
				$Estensione=ap_ExtensionType($allegato->Allegato);	
				echo '<div style="border: thin dashed;font-size: 1em;">
						<div style="float: left;display: inline;width: 40px;height: 40px;padding-top:5px;padding-left:5px;">
							<img src="'.$TipidiFiles[strtolower($Estensione)]['Icona'].'" alt="'.$TipidiFiles[strtolower($Estensione)]['Descrizione'].'" height="30" width="30" />
						</div>
						<div style="margin-top:0;">
							<p style="margin-top:0;">
							'.($allegato->DocIntegrale!="1"?'<span class="evidenziato">'.__("Pubblicato per Estratto","albo-online")."</span><br />":"").'
							'.strip_tags($allegato->TitoloAllegato).' <br />';
						if (is_file($allegato->Allegato))
							echo '        <a href="'.ap_DaPath_a_URL($allegato->Allegato).'" >'. basename( $allegato->Allegato).'</a> ('.ap_Formato_Dimensione_File(filesize($allegato->Allegato)).')<br />'.htmlspecialchars_decode($TipidiFiles[strtolower($Estensione)]['Verifica']).'</p>
						<p>
							<label for="motivo'.$allegato->IdAllegato.'" style="vertical-align: text-top;" id="LblIDA'.$allegato->IdAllegato.'">Indicare il motivo della rimozione del Documento Firmato</label>
							<input type="text" id="motivo'.$allegato->IdAllegato.'" name="motivo'.$allegato->IdAllegato.'" size="50" style="border: 1px solid #d63638;"/>
							<input type="hidden" id="IDA'.$allegato->IdAllegato.'" name="IDA'.$allegato->IdAllegato.'" value="'.$allegato->IdAllegato.'"/>
							<span class="dashicons dashicons-trash CancellaAllegato" title="Elimina Allegato" style="color:red;cursor: -webkit-grab; cursor: grab;" id="'.$allegato->IdAllegato.'" rel="'.strip_tags($allegato->TitoloAllegato).'"></span><br id="SR'.$allegato->IdAllegato.'" />';
						else
							echo __("Documento Cancellato","albo-online")."<br />";
			echo ' Note: <span id="Note'.$allegato->IdAllegato.'">'.$allegato->Note.'</span>
						</p>		
						</div>
					</div>';
			}
			echo '</div>
	</div>';
		}
		$allegati=ap_get_allegati_atto($IdAtto);
		if(count($allegati)>0){
			echo '<div class="postbox" style="padding:0 10px 10px 10px;">
				<h3>'. __("Allegati","albo-online").'</h3>
				<div class="Visalbo">';
			$TipidiFiles=ap_get_tipidifiles();
			foreach ($allegati as $allegato) {
				$Estensione=ap_ExtensionType($allegato->Allegato);	
				echo '<div style="border: thin dashed;font-size: 1em;">
						<div style="float: left;display: inline;width: 40px;height: 40px;padding-top:5px;padding-left:5px;">
							<img src="'.$TipidiFiles[strtolower($Estensione)]['Icona'].'" alt="'.$TipidiFiles[strtolower($Estensione)]['Descrizione'].'" height="30" width="30" />		
						</div>
						<div style="margin-top:0;">
							<p style="margin-top:0;">
								'.($allegato->DocIntegrale!="1"?'<span class="evidenziato">'.__("Pubblicato per Estratto","albo-online")."</span><br />":"").'
								'.strip_tags($allegato->TitoloAllegato).' <br />';
						if (is_file($allegato->Allegato))
							echo '        <a id="file'.$allegato->IdAllegato.'" href="'.ap_DaPath_a_URL($allegato->Allegato).'" >'. basename( $allegato->Allegato).'</a> ('.ap_Formato_Dimensione_File(filesize($allegato->Allegato)).')</p>
						<p>
							<label for="motivo'.$allegato->IdAllegato.'" style="vertical-align: text-top;" id="LblIDA'.$allegato->IdAllegato.'">Indicare il motivo della rimozione dell\'allegato</label>
							<input type="text" id="motivo'.$allegato->IdAllegato.'" name="motivo'.$allegato->IdAllegato.'" size="50" style="border: 1px solid #d63638;"/>
							<input type="hidden" id="IDA'.$allegato->IdAllegato.'" name="IDA'.$allegato->IdAllegato.'" value="'.$allegato->IdAllegato.'"/>
							<span class="dashicons dashicons-trash CancellaAllegato" title="Elimina Allegato" style="color:red;cursor: -webkit-grab; cursor: grab;" id="'.$allegato->IdAllegato.'" rel="'.strip_tags($allegato->TitoloAllegato).'"></span><br id="SR'.$allegato->IdAllegato.'" />';
						else
							echo __("Allegato Cancellato","albo-online")."<br />";
						echo ' Note: <span id="Note'.$allegato->IdAllegato.'">'.$allegato->Note.'</span>
						</p>		
						</div>
					</div>';
			}
			echo '</div>
	</div>';
		}
	echo'			</div>
	</div>
<div id="col-left">
	<div class="col-wrap postbox" style="padding:0 10px 10px 10px;">
		<h3>'. __("Dati atto","albo-online").'</h3>
		<hr />
		<table class="widefat fixed striped" style="border:0;">
		    <tbody id="dati-atto">
			<tr>
				<th style="width:50%;">'. __("Ente emittente","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.$NomeEnte.'</td>
			</tr>
			<tr>
				<th style="width:20%;">'. __("Numero Albo","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.$risultato->Numero."/".$risultato->Anno.'</td>
			</tr>
			<tr>
				<th>'. __("Codice di Riferimento","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Riferimento).'</td>
			</tr>
			<tr>
				<th>'. __("Oggetto","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Oggetto).'</td>
			</tr>';
		if($risultato->DataAnnullamento!='0000-00-00')		
			echo '		<tr>
				<th style="width:20%;">'. __("Data Annullamento","albo-online").'</th>
				<td style="font-size:14px;font-weight: bold;color: Red;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataAnnullamento).'</td>
			</tr>
	    	<tr>
				<th style="width:20%;">'. __("Motivo Annullamento","albo-online").'</th>
				<td style="font-size:14px;font-weight: bold;color: Red;vertical-align:top;">'.stripslashes($risultato->MotivoAnnullamento).'</td>
			</tr>';
		echo '		
			<tr>
				<th>'. __("Data di registrazione","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->Data).'</td>
			</tr>

			<tr>
				<th>'. __("Data inizio Pubblicazione","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataInizio).'</td>
			</tr>
			<tr>
				<th>'. __("Data fine Pubblicazione","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataFine).'</td>
			</tr>
			<tr>
				<th>'. __("Data Oblio","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataOblio).'</td>
			</tr>
			<tr>
				<th>'.__("Richiedente","albo-online").'</th>
				<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Richiedente).'</td>
			</tr>
			<tr>
				<th>'.__("Unità Organizzativa Responsabile","albo-online").'</th>
				<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.(isset($Unitao->Nome)?stripslashes($Unitao->Nome):"").'</td>
			</tr>
			<tr>
				<th>'.__("Responsabile del procedimento amministrativo","albo-online").'</th>
				<td style="font-size:14px;font-style: italic;color: Blue;vertical-align:middle;">'.(is_object($NomeResp)?$NomeResp->Nome." ".$NomeResp->Cognome:$NomeResp).'</td>
			</tr>
			<tr>
				<th>'. __("Categoria","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultatocategoria->Nome).'</td>
			</tr>
			<tr>
				<th>'. __("Note","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Informazioni).'</td>
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
					<th>'. __("Meta Dati","albo-online").'</th>
					<td style="vertical-align: middle;color: Red;">'.$Meta.'</td>
				</tr>';
}
echo'
			<tr>
				<th>'. __("Soggetti","albo-online").'</th>
				<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">
				<ul>';
	$Soggetti=unserialize($risultato->Soggetti);
	if ($Soggetti){
		$Soggetti=ap_get_alcuni_soggetti_ruolo(implode(",",$Soggetti));
		foreach($Soggetti as $Soggetto){
			echo "
				<li><strong>".ap_get_Funzione_Responsabile($Soggetto->Funzione,"Descrizione")."</strong><br />".$Soggetto->Nome." ".$Soggetto->Cognome." 
				</li>";
		}
	}
	echo'				
					</ul>
					</td>
				</tr>	    
				</tbody>
			</table>
		</div>';	

echo '</div>
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
		<h2 class="wp-heading-inline"><span class="dashicons dashicons-portfolio"></span> '. __("Atti","albo-online").'</h2>
		<a href="'.site_url().'/wp-admin/admin.php?page=atti&amp;stato_atti=Correnti" class="add-new-h2 tornaindietro">Torna indietro</a>
		<h3>'. __("Annulla Atto","albo-online").'</h3>	
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
				<th style="text-align:center;font-size:1.2em;width:50%;">'. __("Dati atto","albo-online").'</th>
				<th style="font-size:1.2em;">'. __("Allegati atto","albo-online").'</th>
			</tr>
		    </thead>
		    <tbody>
		    <tr>
		    <td style="border-right-style: groove;border-right-width: thin;">
		    	<table>
				<tr>
					<th style="width:20%;">'. __("Ente emittente","albo-online").'</th>
					<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.$NomeEnte.'</td>
				</tr>
				<tr>
					<th style="width:20%;">'. __("Numero Albo","albo-online").'</th>
					<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.$risultato->Numero."/".$risultato->Anno.'</td>
				</tr>
				<tr>
					<th>'. __("Data","albo-online").'</th>
					<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->Data).'</td>
				</tr>
				<tr>
					<th>'. __("Codice di Riferimento","albo-online").'</th>
					<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Riferimento).'</td>
				</tr>
				<tr>
					<th>'. __("Oggetto","albo-online").'</th>
					<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Oggetto).'</td>
				</tr>
				<tr>
					<th>'. __("Data inizio Pubblicazione","albo-online").'</th>
					<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataInizio).'</td>
				</tr>
				<tr>
					<th>'. __("Data fine Pubblicazione","albo-online").'</th>
					<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataFine).'</td>
				</tr>
				<tr>
					<th>'. __("Data Oblio","albo-online").'</th>
					<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.ap_VisualizzaData($risultato->DataOblio).'</td>
				</tr>
				<tr>
					<th>'. __("Note","albo-online").'</th>
					<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultato->Informazioni).'</td>
				</tr>
				<tr>
					<th>'. __("Categoria","albo-online").'</th>
					<td style="font-size:12px;font-style: italic;color: Blue;vertical-align:middle;">'.stripslashes($risultatocategoria->Nome).'</td>
				</tr>
				<tr>
					<th>'. __("Soggetti","albo-online").'</th>
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
			<p style="color:red;font-weight: bold;">'. __("Selezionare gli allegati che devono essere cancellati per violazione di legge<br />NB: verrà cancellato solo il file, mentre sarà mantenuto il nome del file nell'elenco degli allegati","albo-online").'</p>';
$allegati=ap_get_all_allegati_atto($IdAtto);
$TipidiFiles=ap_get_tipidifiles();
foreach ($allegati as $allegato) {
	$Estensione=ap_ExtensionType($allegato->Allegato);	
	echo '<div style="float: left;display: inline;width: 40px;height: 40px;padding-top:5px;padding-left:5px;">
			<input type="checkbox" name="Alle:'.$allegato->IdAllegato.'" value="'.$allegato->IdAllegato.'">
		  </div>
			<div style="float: left;display: inline;width: 40px;height: 40px;padding-top:5px;padding-left:5px;">
				<img src="'.$TipidiFiles[strtolower($Estensione)]['Icona'].'" alt="'.$TipidiFiles[strtolower($Estensione)]['Descrizione'].'" height="30" width="30"/>
			</div>
			<div style="margin-top:0;">
				<p style="margin-top:0;">'.strip_tags($allegato->TitoloAllegato).' <br />';
			if (is_file($allegato->Allegato))
				echo '        <a href="'.ap_DaPath_a_URL($allegato->Allegato).'" >'. basename( $allegato->Allegato).'</a> ('.ap_Formato_Dimensione_File(filesize($allegato->Allegato)).')<br />'.htmlspecialchars_decode($TipidiFiles[strtolower($Estensione)]['Verifica']);
			else
				echo basename( $allegato->Allegato)." ".__("File non trovato, il file è stato cancellato o spostato!","albo-online");
echo'				</p>
			</div>';
	}			
echo'			</td>
			</tr>
			<tr>
				<td colspan="2" style="text-align:center;border-top-style: groove;border-top-width: thin;">
				<span style="color:red;font-size:2em;font-weight: bold;">'. __("Motivo Annullamento","albo-online").'</span><br />
					<textarea rows="4" cols="100"  maxlength="255" placeholder="Inserisci il motivo, massimo 255 caratteri" id="Motivo" name="Motivo" ></textarea>
				</td>
			</tr>
			<tr>
				<td colspan="2" style="text-align:center;">
					<input type="submit" name="submit" id="submit" class="button" value="'. __("Annulla Pubblicazione Atto","albo-online").'"  />
					<input type="submit" name="submit" id="submit" class="button" value="'. __("Annulla Operazione","albo-online").'"  />
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
							<li><a href="#log-tab-1">'. __("Atto","albo-online").'</a></li>
							<li><a href="#log-tab-2">'. __("Allegati","albo-online").'</a></li>
							<li><a href="#log-tab-3">'. __("Statistiche Visite","albo-online").'</a></li>
							<li><a href="#log-tab-4">'. __("Statistiche Download","albo-online").'</a></li>
						</ul>
						<div id="log-tab-1">
							<div id="DatiLog1">'.$AP_OnLine->CreaLog(1,$IdAtto,0).'</div>
						</div>
						<div id="log-tab-2">
							<div id="DatiLog2">'.$AP_OnLine->CreaLog(3,$IdAtto,0).'</div>
						</div>
						<div id="log-tab-3">
							<div id="DatiLog3">'.$AP_OnLine->CreaLog(5,$IdAtto,0).'</div>
						</div>
						<div id="log-tab-4">
							<div id="DatiLog4">'.$AP_OnLine->CreaLog(6,$IdAtto,0).'</div>
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
$Message[0] = __("Messaggio non definito","albo-online");
$messages[1] = __("Atto Aggiunto","albo-online");
$messages[2] = __("Atto Cancellato","albo-online");
$messages[3] = __("Atto Aggiornato","albo-online");
$messages[4] = __("Atto non Aggiunto","albo-online");
$messages[5] = __("Atto non Aggiornato","albo-online");
$messages[6] = __("Atto non Cancellato","albo-online");
$messages[7] = __("Impossibile cancellare un Atto che contiene Allegati<br />Cancellare prima gli Allegati e poi riprovare","albo-online");
$messages[8] = __("Impossibile ANULLARE l'Atto","albo-online");
$messages[9] = __("Atto ANNULLATO","albo-online");
$messages[10] = __("Allegati all'Atto Cancellati","albo-online");
$messages[11] = __("Allegati all'Atto NON Cancellati","albo-online");
$messages[12] = __("Metadati dell'Atto Memorizzati","albo-online");
$messages[13] = __("Metadati dell'Atto NON Memorizzati","albo-online");
$messages[80] = __("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione è stata annullata","albo-online");
$messages[99] = __("OPERAZIONE NON AMMESSA!<br />l'atto non è ancora da eliminare","albo-online");
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
  <h3><?php _e("Atto","albo-online");?> <span id="oggetto"></span></span></h3><p style='color:red;font-weight: bold;'><?php _e("Confermi la cancellazione dell'atto?","albo-online");?></p>
</div>
<?php
echo' <div class="wrap">
	<div class="HeadPage">
		<h2 class="wp-heading-inline"><span class="dashicons dashicons-portfolio"></span> '. __("Atti","albo-online");
$HtmlNP="";
if (ap_get_num_categorie()==0){
	$HtmlNP.='<p> </p>
			<div class="widefat" >
				<p style="text-align:center;font-size:1.2em;font-weight: bold;color: green;">
				'. __("Non risultano categorie codificate, se vuoi posso impostare le categorie di default","albo-online").' &ensp;&ensp;<a href="?page=utilityAlboP&amp;action=creacategorie">'. __("Crea Categorie di Default","albo-online").'</a>
				</p>
			</div>';
}
if (ap_num_responsabili()==0){
	$HtmlNP.='<p> </p>
			<div class="widefat" >
				<p style="text-align:center;font-size:1.2em;font-weight: bold;color: green;">
				'. sprintf(__("Non risultano %sResponsabili%s codificati, devi crearne almeno uno prima di iniziare a codificare gli Atti","albo-online"),"<strong>","</strong>").' &ensp;&ensp;<a href="?page=soggetti">'. __("Crea Soggetti","albo-online").'</a>
				</p>
			</div>';
}
if (ap_num_unitao()==0){
	$HtmlNP.='<p> </p>
			<div class="widefat" >
				<p style="text-align:center;font-size:1.2em;font-weight: bold;color: green;">
				'. sprintf(__("Non risulta nessuna %sUnità Organizzativa%s codificata, devi crearne almeno una prima di iniziare a codificare gli Atti","albo-online"),"<strong>","</strong>").' &ensp;&ensp;<a href="?page=unitao">'. __("Crea Unità Organizzativa","albo-online").'</a>
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
	<a href="?page=atti&amp;action=new-atto" class="add-new-h2">'. __("Aggiungi nuovo","albo-online").'</a></h2>';
	if ( isset($msg) or isset($msg2) or isset($Errore) ){
		$stato="";
		if (isset($_GET['stato_atti']))
			$stato="&stato_atti=".$_GET['stato_atti'];
		if($Msg_op=="Atto PUBBLICATO"){
			$stato="&stato_atti=Correnti";
		}
		if(substr($Msg_op,0,19)== __("Atto non PUBBLICATO","albo-online")){
			$stato="&stato_atti=Nuovi";
		}
		echo '<div id="message" class="updated"><p>'.(isset($msg)?$messages[$msg]:"").(isset($msg2)?"<br />".$messages[$msg2]:"").'<br /><br />'.(isset($Errore)?$Errore:"").'</p></div>
		      <meta http-equiv="refresh" content="2;url=admin.php?page=atti'.$stato.'"/>';
		      return;
	} 
	if (isset($_REQUEST['stato_atti']))
		switch($_REQUEST['stato_atti']){
			case "Tutti": $Titolo=__("Tutti gli atti","albo-online");$Azione="Tutti";break;
			case "Nuovi": $Titolo=__("Atti da pubblicare","albo-online");$Azione="DaPubblicare";break;
			case "Correnti": $Titolo=__("Atti in corso di Validità","albo-online");$Azione="Correnti";break;
			case "Scaduti":  $Titolo=__("Atti Scaduti","albo-online");$Azione="Scaduti";break;
			case "Eliminare":  $Titolo=__("Atti da Eliminare (Oblio)","albo-online");$Azione="Eliminare";break;
            case "Cerca":  $Titolo=__("Cerca Atti","albo-online");$Azione="Cerca";break; /* mr */
			default: $Titolo=__("Atti da Pubblicare","albo-online");$Azione="DaPubblicare";break;
		}
	else{
			$Titolo=__("Tutti gli atti","albo-online");
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
	    	$tablenew->search_box(__("Cerca in Oggetto","albo-online"),'search_id'); /* mr  */
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
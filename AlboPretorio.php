<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Albo Pretorio On line
 * Plugin URI:        https://it.wordpress.org/plugins/albo-pretorio-on-line/
 * Description:       Plugin utilizzato per la pubblicazione degli atti da inserire nell'albo pretorio dell'ente.
 * Version:           4.4.6
 * Author:            Ignazio Scimone
 * Author URI:        eduva.org
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       albo-online
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

include_once(dirname (__FILE__) .'/AlboPretorioFunctions.php');			/* libreria delle funzioni */
include_once(dirname (__FILE__) .'/AlboPretorioWidget.php');


define("Albo_URL",plugin_dir_url(dirname (__FILE__).'/AlboPretorio.php'));
define("Albo_DIR",dirname (__FILE__));
define("APHomePath",substr(plugin_dir_path(__FILE__),0,strpos(plugin_dir_path(__FILE__),"wp-content")-1));
define("AlboBCK",WP_CONTENT_DIR."/AlboOnLine");

$uploads = wp_upload_dir(); 
define("AP_BASE_DIR",$uploads['basedir']."/");
if (isset($_REQUEST['action'])){
	require_once( ABSPATH . 'wp-includes/pluggable.php' );
	switch($_REQUEST['action']){
		case "creafoblio":
			if (!isset($_REQUEST['rigenera'])) {
				$Stato=__("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione è stata annullata","albo-online");
				break;
			}
			if (!wp_verify_nonce($_REQUEST['rigenera'],'rigeneraoblio')){
				$Stato=__("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione è stata annullata","albo-online");
				break;
			} 			
			ap_crearobots();
			$newPathAllegati=AP_BASE_DIR."AllegatiAttiAlboPretorio";
			ap_NoIndexNoDirectLink($newPathAllegati);
			wp_redirect("?page=Albo_Pretorio");
			break;
	}
}

if (!class_exists('AlboPretorio')) {
 class AlboPretorio {
	
	var $version;
	var $minium_WP   = '3.1';
	var $options     = '';

	function __construct() {
		load_plugin_textdomain( 'albo-online', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
		if ( ! function_exists( 'get_plugins' ) )
	 		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	    $plugins = get_plugins( "/".plugin_basename( dirname( __FILE__ ) ) );
    	$plugin_nome = basename( ( __FILE__ ) );
	    $this->version=$plugins[$plugin_nome]['Version'];
		// Inizializzazioni
		$this->define_tables();
		$this->plugin_name = plugin_basename(__FILE__);		

		// Hook per attivazione/disattivazione plugin
		register_activation_hook(  __FILE__, array($this, 'activate'));
		register_deactivation_hook(  __FILE__, array($this, 'deactivate') );	

		// Hook di inizializzazione che registra il punto di avvio del plugin
		add_action( 'admin_enqueue_scripts', array( $this,'Albo_Admin_Enqueue_Scripts' )  );
		add_action('init', array($this, 'update_AlboPretorio_settings'));
		add_action('init', array($this, 'init') );
		add_action('init', array($this, 'add_albo_button'));
		add_shortcode('Albo', array($this, 'VisualizzaAtti'));
		add_shortcode('AlboGruppiAtti', array($this, 'VisualizzaGruppiAtti'));
		add_shortcode('AlboAtto', array($this, 'VisualizzaAtto'));

		add_action('wp_head', array($this,'head_Front_End'));
		add_action( 'admin_menu', array ($this, 'add_menu') ); 
		add_action('template_redirect', array($this, 'Gestione_Link'));
		add_filter('set-screen-option', array($this, 'atti_set_option'), 10, 3);
		add_filter( 'the_content', array($this, 'VisualizzaTabellaInAVCP'),10,1);
		add_action( 'wp_ajax_MemoFunzioni','ap_MemoFunzioni' );
		add_action( 'wp_ajax_LoadDefaultFunzioni','ap_LoadDefaultFunzioni' );
		add_action( 'wp_ajax_dismiss_alboonline_notice','ap_dismiss_alboonline_notice' );
		$RestApi=get_option('opt_AP_RestApi');
	  	if($RestApi=="Si"){
			add_action( 'rest_api_init', array($this, 'Reg_rest_api_route'));
		}
		if (get_option( 'opt_AP_Versione' ) != $this->version) {
			$this->activate();
		} 
		$this->load_dependencies();
		if( $this->version=="4.4.6" && empty( get_option( 'alboonline-notice-dismissed' ) ) ) {
  			add_action( 'admin_notices', array($this, 'admin_notice' ));
		}
	}
	
	function admin_notice(){
?>
    <div class="updated notice albo-notice-dismis is-dismissible" >
        <h3>Albo Online</h3>
        <p><?php echo sprintf(__('Aggiornato alla versione %s', 'albo-online' ),$this->version); ?></p>
        <p><?php echo sprintf(__('Per visualizzare le modifiche apportate consultare il %sLog change%s', 'albo-online' ),'<a href="admin.php?page=logagg">','</a>'); ?></p>
    </div>


    <?php		
	}
	function Reg_rest_api_route(){
// Registrazione route statistiche
		register_rest_route('alboonline/v1','/statistiche', 
			 array('methods'  => WP_REST_Server::READABLE,
		 	       'callback' => array($this,'rest_api_statistiche_get'),
		));
// Registrazione route categorie
		register_rest_route('alboonline/v1','/categorie', 
			 array('methods'  => WP_REST_Server::READABLE,
		 	       'callback' => array($this,'rest_api_categorie_get'),
		));
// Registrazione route enti
		register_rest_route('alboonline/v1','/enti', 
			 array('methods'  => WP_REST_Server::READABLE,
			 	   'callback' => array($this,'rest_api_enti_get'),
		));		
// Registrazione route atto
		register_rest_route('alboonline/v1','/atto/(?P<num>\d+)/(?P<anno>\d+)', 
			 array('methods'  => WP_REST_Server::READABLE,
		 	       'callback' => array($this,'rest_api_atto_get'),
		 	       'args' 	  => array('num' => array(
								'validate_callback' => 
									function($param, $request, $key) {
								    	return is_numeric( $param );
								    }),
								    	'anno' => array(
								'validate_callback' => 
									function($param, $request, $key) {
								    	return is_numeric( $param );
								    }),
								)
		));
// Registrazione route atto
		register_rest_route('alboonline/v1','/atto/(?P<id>\d+)', 
			 array('methods'  => WP_REST_Server::READABLE,
		 	       'callback' => array($this,'rest_api_atto_get_byID'),
		 	       'args' 	  => array('id' => array(
								'validate_callback' => 
									function($param, $request, $key) {
								    	return is_numeric( $param );
								    }),
								)
		));
// Registrazione route atti
		register_rest_route('alboonline/v1','/atti', 
			 array('methods'  => WP_REST_Server::READABLE,
		 	       'callback' => array($this,'rest_api_atti_get'),
				   'args'	  => array(
		 	       	    
		 	       	'stato' => array('default'=>1,
			 	       	   'validate_callback' =>
			 	       		function($param, $request, $key) {
								return is_numeric( $param )&&($param==1 ||$param==2);						   			
							}),
    				'per_page' => array('default'=>10,
			 	       	   'validate_callback' =>
			 	       		function($param, $request, $key) {
								return is_numeric( $param )&&($param>=-1);						   			}),
    				'page' => array('default'=>1,
			 	       	   'validate_callback' =>
			 	       		function($param, $request, $key) {
								return is_numeric( $param )&&($param>=0);						   			}),		
    				'categorie' => array('default'=>'0',
			 	       	   'validate_callback' =>
			 	       		function($param, $request, $key) {
			 	       			if($param=='0'){
									return true;
								}else{
									return is_array_di_categorie($param);
									}
	   						}),
    				'numero' => array('default'=>0,
			 	       	   'validate_callback' =>
			 	       		function($param, $request, $key) {
								return is_numeric( $param )&&($param>=0);						   			}),		
    				'anno' => array('default'=>0,
			 	       	   'validate_callback' =>
			 	       		function($param, $request, $key) {
								return is_numeric( $param );						   		}),	
    				'oggetto' => array('default'=>'',
			 	       	   'validate_callback' =>
			 	       		function($param, $request, $key) {
								return is_string( $param );						   						}),	
    				'dadata' => array('default'=>'0',
			 	       	   'validate_callback' =>
			 	       		function($param, $request, $key) {
								return is_string( $param );						   						}),		
    				'adata' => array('default'=>'0',
			 	       	   'validate_callback' =>
			 	       		function($param, $request, $key) {
								return is_string( $param );						   						}),		
    				'riferimento' => array('default'=>'',
			 	       	   'validate_callback' =>
			 	       		function($param, $request, $key) {
								return is_string( $param );						   						}),
    				'ente' => array('default'=>-1,
			 	       	   'validate_callback' =>
			 	       		function($param, $request, $key) {
								return is_numeric( $param )&&($param>=-1);						   			}),	
					),
			));
	}
	
	function rest_api_atto_get_byID($request){
		$Atto=array();
		$IDAtto=$request->get_param("id");
		$risultato=ap_get_atto($IDAtto);
		if(count($risultato)==0){
			return new WP_Error( 'no_atto', __('Nessun atto trovato con questi parametri','albo-online'), array( 'status' => 404 ) );
  		}
		return new WP_REST_Response($this->REST_API_get_atto($risultato), 200 );
	}
	function rest_api_atto_get($request){
		$Atto=array();
		$NumeroAtto=$request->get_param("num");
		$AnnoAtto=$request->get_param("anno");
		$risultato=ap_get_all_atti(0,$NumeroAtto,$AnnoAtto);
		if(count($risultato)==0){
			return new WP_Error( 'no_atto', __('Nessun atto trovato con questi parametri','albo-online'), array( 'status' => 404 ) );
  		}

		return new WP_REST_Response($this->REST_API_get_atto($risultato), 200 );
	}
	function REST_API_get_atto($risultato){
		$risultato=$risultato[0];
		$IdAtto=$risultato->IdAtto;
		$DatiAtto=ap_get_atto($IdAtto);
		$DatiCategoria=array();
		$DatiEnte=array();
		$DatiSoggetti=array();
		$Allegati=array();
		$Meta="";
		foreach($DatiAtto as $D_Atto){
//Categoria Atto
			$D_Catergoria=ap_get_categoria($D_Atto->IdCategoria);
			$D_Catergoria=$D_Catergoria[0];
			$DatiCategoria["Nome"]          =$D_Catergoria->Nome;
			$DatiCategoria["Descrizione"]	=$D_Catergoria->Descrizione;
//Fine Categoria Atto
//Ente Atto
			$D_Ente=ap_get_ente($D_Atto->Ente);
			$DatiEnte["Nome"]       =$D_Ente->Nome;
			$DatiEnte["Indirizzo"]	=$D_Ente->Indirizzo;
			$DatiEnte["Url"]		=$D_Ente->Url;
			$DatiEnte["Email"]		=$D_Ente->Email;
			$DatiEnte["Pec"]		=$D_Ente->Pec;
			$DatiEnte["Telefono"]	=$D_Ente->Telefono;
			$DatiEnte["Fax"]		=$D_Ente->Fax;
			$DatiEnte["Note"]		=$D_Ente->Note;
//Fine Ente Atto
// Soggetti
			$Soggetti=unserialize($D_Atto->Soggetti);
			if($Soggetti){
				$Soggetti=ap_get_alcuni_soggetti_ruolo(implode(",",$Soggetti));
				$Ruolo="";
				foreach($Soggetti as $Soggetto){
					if(ap_get_Funzione_Responsabile($Soggetto->Funzione,"Display")=="No"){
						continue;
					}
					$DatiSoggetti[$Soggetto->IdResponsabile]=array(
						"Cognome" 	=>$Soggetto->Cognome,
						"Nome" 		=>$Soggetto->Nome,
						"Email" 	=>$Soggetto->Email,
						"Telefono" 	=>$Soggetto->Telefono,
						"Orario" 	=>$Soggetto->Orario,
						"Note" 		=>$Soggetto->Note,
						"CodFun" 	=>$Soggetto->Funzione,
						"Funzione"	=>ap_get_Funzione_Responsabile($Soggetto->Funzione,__('Descrizione','albo-online')));
				}				
			}


// Fine Soggetti
// Allegati
			$allegatiatto=ap_get_all_allegati_atto($IdAtto);
			$TipidiFiles=ap_get_tipidifiles();
			$sep="?";
			$PagAlboCor=get_option('opt_AP_PAttiCor');
			$PagAlboSto=get_option('opt_AP_PAttiSto');
			if(!isset($PagAlboCor) || $PagAlboCor=="")
				if (strpos($PagAlboCor,"?")>0)
					$sep="&amp;";
			foreach ($allegatiatto as $allegato) {
				$Estensione=ap_ExtensionType($allegato->Allegato);
				$Icona=$TipidiFiles[strtolower($Estensione)]['Icona'];
				$TipoFile=$TipidiFiles[strtolower($Estensione)]['Descrizione'];
				if (is_file($allegato->Allegato)){
					$Link=ap_DaPath_a_URL($allegato->Allegato);
					$Rel=(ap_is_atto_corrente($IdAtto)?$PagAlboCor:$PagAlboSto).$sep.'action=dwnalle&amp;id='.$allegato->IdAllegato.'&amp;idAtto='.$IdAtto;
					$Dimensione=ap_Formato_Dimensione_File(filesize($allegato->Allegato));
					
				}
				$Allegati[]=array("Titolo"		=>$allegato->TitoloAllegato,
								  "Allegato"	=>$allegato->Allegato,
								  "Estenzione"	=>$Estensione,
								  "Icona"		=>$Icona,
								  "TipoFile"	=>$TipoFile,
								  "Dimensione"	=>$Dimensione,
								  "Link"		=>$Link,
								  "Rel"			=>$Rel);
			}
// Fine Allegati
			$MetaDati=ap_get_meta_atto($riga->IdAtto);	
			if($MetaDati!==FALSE){
				foreach($MetaDati as $Metadato){
					$Meta.=$Metadato->Meta."=".$Metadato->Value."<br />";
				}
				$Meta=substr($Meta,0,-6);
			} 
			$Atto["Numero"]				=$D_Atto->Numero;
			$Atto["Anno"]				=$D_Atto->Anno;
			$Atto["Data"]				=$D_Atto->Data;
			$Atto["Riferimento"]		=$D_Atto->Riferimento;
			$Atto["Oggetto"]			=$D_Atto->Oggetto;
			$Atto["DataInizio"]			=$D_Atto->DataInizio;
			$Atto["DataFine"]			=$D_Atto->DataFine;
			$Atto["Informazioni"]		=$D_Atto->Informazioni;
			$Atto["IdCategoria"]		=$D_Atto->IdCategoria;
			$Atto["Categoria"]			=$DatiCategoria;
			$Atto["DataAnnullamento"]	=$D_Atto->DataAnnullamento;
			$Atto["MotivoAnnullamento"]	=$D_Atto->MotivoAnnullamento;
			$Atto["IdEnte"]				=$D_Atto->Ente;
			$Atto["Ente"]				=$DatiEnte;
			$Atto["Metadati"]			=stripslashes($Meta);
			$Atto["DataOblio"]			=$D_Atto->DataOblio;
			$Atto["Soggetti"]			=$DatiSoggetti;
			$Atto["Allegati"]			=$Allegati;
		}
		return $Atto;
	}
	function rest_api_statistiche_get($request){
		global $wpdb;
		$Statistiche=array();
	  	$n_atti_attivi =ap_get_all_atti(1,0,0,0,'', 0,0,"",0,0,true);	
	  	$n_atti_storico=ap_get_all_atti(2,0,0,0,'', 0,0,"",0,0,true);
	  	$n_atti_attivi_ANN =ap_get_all_atti(1,0,0,0,'', 0,0,"",0,0,true,true);	
	  	$n_atti_storico_ANN=ap_get_all_atti(2,0,0,0,'', 0,0,"",0,0,true,true);
		$Statistiche=array(
					"num_Categorie"				=>ap_num_categorie(),
					"num_AttiCorrenti"	 		=>$n_atti_attivi,
					"num_AttiStorico"			=>$n_atti_storico,
					"num_AttiCorrenti_Annullati"=>$n_atti_attivi_ANN,
					"num_AttiStorico_Annullati"	=>$n_atti_storico_ANN,
					"num_Allegati"				=>ap_num_allegati());
		return new WP_REST_Response($Statistiche, 200 );
	}
	function rest_api_categorie_get($request){
		$Categorie=array();
		$ArrCategorie=ap_get_categorie();
		foreach($ArrCategorie as $Categoria){
			$Categorie[$Categoria->IdCategoria]=array(
					"Nome"			=>$Categoria->Nome,
					"Descrizione"	=>$Categoria->Descrizione,
					"Giorni"		=>$Categoria->Giorni);
			}
		return new WP_REST_Response($Categorie, 200 );
	}
	function rest_api_enti_get($request){
		$Enti=array();
		$ArrEnti=ap_get_enti();
		foreach($ArrEnti as $Ente){
			$Enti[$Ente->IdEnte]=array(
					"Nome"			=>$Ente->Nome,
					"Indirizzo"		=>$Ente->Indirizzo,
					"Url"			=>$Ente->Url,
					"Email"			=>$Ente->Email,
					"Pec"			=>$Ente->Pec,
					"Telefono"		=>$Ente->Telefono,
					"Fax"			=>$Ente->Fax,
					"Note"			=>$Ente->Note,
					);
			}
		return new WP_REST_Response($Enti, 200 );
	}
	function rest_api_atti_get($request ) {
		$PagAlbo=get_option('opt_AP_PAtto');
		if(!isset($PagAlbo) || $PagAlbo=="")
			return new WP_REST_Response(__('Errore:Pagina visualizzazione atto non impostata','albo-online'),200);
	$Stato=$request->get_param("stato");
	$N_A_pp=$request->get_param("per_page");
	$Pag=$request->get_param("page");
	$Categorie=$request->get_param("categorie");
	$Numero=$request->get_param("numero");
	$Anno=$request->get_param("anno");
	$Oggetto=$request->get_param("oggetto");
	$Dadata=$request->get_param("dadata");
	$Adata=$request->get_param("adata");
	$Riferimento=$request->get_param("riferimento");
	$Ente=$request->get_param("ente");
	if ($N_A_pp==0){
		$N_A_pp=10;
	}
	if ($N_A_pp==-1){
		$N_A_pp=0;
	}
	if ($Pag==0){
		$Da=0;
		$A=$N_A_pp;
	}else{
		$Da=($Pag-1)*$N_A_pp;
		$A=$N_A_pp;
	}
	$TotAtti=ap_get_all_atti($Stato,$Numero,$Anno,$Categorie,$Oggetto,$Dadata,$Adata,'',0,0,true,false,$Riferimento,$Ente);
	$ListaAtti=ap_get_all_atti($Stato,$Numero,$Anno,$Categorie,$Oggetto,$Dadata,$Adata,'Anno DESC,Numero DESC',$Da,$A,false,false,$Riferimento,$Ente); 			
			
	if($N_A_pp>0){
		$Npag=(int)($TotAtti/$N_A_pp);
		if ($TotAtti%$N_A_pp>0 ){
			$Npag++;
		}
	}
	$DatiAtti=array("TotAtti" => $TotAtti, 
	                "NumPagine" => $Npag, 
	                "Pagina" =>$Pag,
	                "AttiPagina" => $N_A_pp);	
		if (strpos($PagAlbo,"?")>0)
			$sep="&";
		else
			$sep="?";
		$Atti=array();
		foreach($ListaAtti as $riga){
			$MetaDati=ap_get_meta_atto($riga->IdAtto);
			$Meta="";
			if($MetaDati!==FALSE){
				foreach($MetaDati as $Metadato){
					$Meta.=$Metadato->Meta."=".$Metadato->Value."<br />";
				}
				$Meta=substr($Meta,0,-6);
			} 
			$Atti[$riga->IdAtto]["Numero"]=$riga->Numero;
			$Atti[$riga->IdAtto]["Anno"]=$riga->Anno;
			$Atti[$riga->IdAtto]["Riferimento"]=$riga->Riferimento;
			$Atti[$riga->IdAtto]["Oggetto"]=$riga->Oggetto;
			$Atti[$riga->IdAtto]["DataInizio"]=$riga->DataInizio;
			$Atti[$riga->IdAtto]["DataFine"]=$riga->DataFine;
			$Atti[$riga->IdAtto]["Informazioni"]=$riga->Informazioni;
			$Categoria=ap_get_categoria($riga->IdCategoria);
			$Atti[$riga->IdAtto]["Categoria"]=$Categoria[0]->Nome;
			$Atti[$riga->IdAtto]["DataAnnullamento"]=$riga->DataAnnullamento;
			$Atti[$riga->IdAtto]["MotivoAnnullamento"]=$riga->MotivoAnnullamento;
			$Atti[$riga->IdAtto]["Ente"]=ap_get_ente($riga->Ente);
			$Atti[$riga->IdAtto]["Metadati"]=stripslashes($Meta);
			$Atti[$riga->IdAtto]["DataOblio"]=$riga->DataOblio;
			$Atti[$riga->IdAtto]["Link"]=$PagAlbo.$sep.'numero='.$riga->Numero.'&anno='.$riga->Anno.'&titolo=';
		}
		$Dati=array($DatiAtti,$Atti);
    	return new WP_REST_Response($Dati, 200 );		
    }

	function VisualizzaTabellaInAVCP($content){
		$PostID= get_the_ID();
		if (get_post_type( $PostID) !="avcp" Or get_option('opt_AP_AutoShortcode')!="Si")
			return $content;
		$Cig=get_post_meta($PostID,'avcp_cig',TRUE);
		$Parametri=array('meta'=>"CIG",'valore'=>$Cig,'titolo' => __('Atti Albo on line di riferimento','albo-online'));
		$OldInterfaccia=get_option('opt_AP_OldInterfaccia');
		if($OldInterfaccia=="Si"){
			require_once ( dirname (__FILE__) . '/admin/gruppiatti.php' );		
		}else{
			require_once ( dirname (__FILE__) . '/admin/gruppiatti_new.php' );
		}		
		return $content.Lista_AttiGruppo($Parametri);
	}
	function Gestione_Link(){
		if(isset($_REQUEST['action'])){
			switch ($_REQUEST['action']){
			case "dwnalle":
//			var_dump($_SERVER);wp_die();
				if(!isset($_SERVER["HTTP_REFERER"])){
					wp_die(__('Oooooo!<br />
					        Stai tentando di fare il furbo!<br />
					        Non puoi accedere a questo file direttamente.','albo-online'));
					break;
				}
				$file_path	= ap_get_allegato_atto($_REQUEST['id']);
				$file_path	=$file_path[0]->Allegato;
//				echo "<pre>".$file_path."</pre>";
				global $is_IE;
				$chunksize	= 2*(1024*1024);
//				wp_die($file_path);
				$stat 		= @stat($file_path);
				$etag		= sprintf('%x-%x-%x', $stat['ino'], $stat['size'], $stat['mtime'] * 1000000);
				$path 		= pathinfo($file_path);
				if ( isset($path['extension']) && strtolower($path['extension']) == 'zip' && $is_IE && ini_get('zlib.output_compression') ) {
					ini_set('zlib.output_compression', 'Off');
					// apache_setenv('no-gzip', '1');
				}

				header('Pragma: public');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Cache-Control: private', FALSE);
				header('Content-Type: application/force-download', FALSE);
				header('Content-Type: application/octet-stream', FALSE);
				header('Content-Type: application/download', FALSE);
				header('Content-Disposition: attachment; filename="'.basename($file_path).'";');
				header('Content-Transfer-Encoding: binary');
				header('Last-Modified: ' . date('r', $stat['mtime']));
				header('Etag: "' . $etag . '"');
				header('Content-Length: '.$stat['size']);
				header('Accept-Ranges: bytes');
				ob_flush();
				flush();
				if ($stat['size'] < $chunksize) {
					@readfile($file_path);
				}
				else {
					$handle = fopen($file_path, 'rb');
					while (!feof($handle)) {
						echo fread($handle, $chunksize);
						ob_flush();
						flush();
					}
					fclose($handle);
				}
				if(is_numeric($_REQUEST['id']) and is_numeric($_REQUEST['idAtto']))
					ap_insert_log(6,5,(int)$_REQUEST['id'],"Download",(int)$_REQUEST['idAtto']);
				exit();
				break;
			}
		}
	}
	function Albo_Admin_Enqueue_Scripts( $hook_suffix ) {
		if(strpos($hook_suffix,'albo-online')===false)	return;?>
<script type='text/javascript'>
	var myajaxsec = "<?php echo wp_create_nonce('adminsecretAlboOnLine');?>",
	    title_button_albo="<?php echo str_replace('"',"''",__('Albo OnLine','albo-online'));?>",
	    title_button_gruppi="<?php echo str_replace('"',"''",__('Albo OnLine raggruppamento atti','albo-online'));?>",
	    title_button_atto="<?php echo str_replace('"',"''",__('Albo OnLine visualizza atto','albo-online'));?>",
	    NonVal="<?php echo str_replace('"',"''",__("Non Valorizzato","albo-online"));?>",
	    NonValAmm="<?php echo str_replace('"',"''",__("Valore Non Valido","albo-online"));?>",
	    NonSOg="<?php echo str_replace('"',"''",__("Nessun Soggetto selezionato, ne devi selezionare almeno UNO","albo-online"));?>",
	    TabFunSog="<?php echo str_replace('"',"''",__("Tabella Funzioni Soggetti","albo-online"));?>",
	    Codice="<?php echo str_replace('"',"''",__("Codice","albo-online"));?>",
	    ValidateCodice="<?php echo str_replace('"',"''",__("Il campo Codice deve essere di 2 o 3 caratteri","albo-online"));?>",
	    Funzione="<?php echo str_replace('"',"''",__("Funzione","albo-online"));?>",
	    ValidateFunzione="<?php echo str_replace('"',"''",__("Il campo Funzione deve essere di almeno 5 caratteri","albo-online"));?>",
	    Visualizza="<?php echo str_replace('"',"''",__("Visualizza","albo-online"));?>",
	    ValidateVisualizza="<?php echo str_replace('"',"''",__("Visualizza in elenco Soggetti nei dettagli dell'atto","albo-online"));?>",
	    Stampa="<?php echo str_replace('"',"''",__("Stampa","albo-online"));?>",
	    ValidateStampa="<?php echo str_replace('"',"''",__("Stampa nella colonna Sinitra del Certificato di Pubblicazione<br />Da impostarne uno solo, comunque verrà preso in considerazione solo il primo","albo-online"));?>",
	    append= "<?php echo str_replace('"',"''",__("Aggiungi Riga","albo-online"));?>",
        removeLast= "<?php echo str_replace('"',"''",__("Rimuovi ultima Riga","albo-online"));?>",
        insert= "<?php echo str_replace('"',"''",__("Inserisci Riga prima","albo-online"));?>",
        remove= "<?php echo str_replace('"',"''",__("Rimuovi la Riga corrente","albo-online"));?>",
        moveUp= "<?php echo str_replace('"',"''",__("Sposta Su","albo-online"));?>",
        moveDown= "<?php echo str_replace('"',"''",__("Sposta Giù","albo-online"));?>",
        rowDrag= "<?php echo str_replace('"',"''",__("Ordina Riga","albo-online"));?>",
        rowEmpty= "<?php echo str_replace('"',"''",__("Questa Griglia è vuota","albo-online"));?>";  
</script>
	<?php		
		$path=plugins_url('', __FILE__ );
	    wp_enqueue_script('jquery');
	    wp_enqueue_script('jquery-ui-core');
	    wp_enqueue_script('jquery-ui-tabs', '', array('jquery'));
	    wp_enqueue_script('jquery-ui-dialog', '', array('jquery'));    
		wp_enqueue_script( 'jquery-ui-datepicker', '', array('jquery'));
		wp_enqueue_script( 'wp-color-picker', '', array('jquery'));
		wp_enqueue_script( 'my-public-admin', $path.'/js/Albo.admin.public.js');
	    wp_enqueue_script( 'my-admin-fields', $path.'/js/Fields.js');
	    wp_enqueue_script( 'my-admin', $path.'/js/Albo.admin.js');
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'jquery.ui.theme', $path.'/css/jquery-ui-custom.css');	
		wp_register_style('AdminAlbo', $path.'/css/styleAdmin.css');
        wp_enqueue_style( 'AdminAlbo');
		if(strpos($hook_suffix,"_page_tipifiles")!==false or strpos($hook_suffix,"page_configAlboP")!==false){
			wp_enqueue_media();
			wp_register_script('uploader_tipi_files', $path.'/js/Uploader.js', array('jquery'));
			wp_enqueue_script( 'uploader_tipi_files');
		}
		if(strpos($hook_suffix,"_page_tabelle")!==false ){
			wp_enqueue_script( 'jquery-ui-tooltip', '', array('jquery'));
			wp_enqueue_script( "Albo_appendGrid", $path . '/plugin/appendGrid/jquery.appendGrid-1.7.1.js');
			wp_enqueue_style("Albo_appendGrid", $path . '/plugin/appendGrid/jquery.appendGrid-1.7.1.css');
			wp_enqueue_style("Albo_ui_tema", $path. '/plugin/appendGrid/jquery-ui.theme.min.css');
//			wp_enqueue_style( "Albo_ui", $path . '/css/jquery-ui.css' );
			wp_enqueue_style("Albo_ui_structure_table", $path.'/plugin/appendGrid/jquery-ui.structure.min.css');
			wp_enqueue_script( 'my-admin_grid', $path.'/js/Albo.admin.grid.js');
		}		
		if(strpos($hook_suffix,"_page_atti")!==false And isset($_GET['action']) And $_GET['action']=='UpAllegati'){
			wp_register_style('AdminAlboMultiUpload', $path.'/css/stylemultiupload.css');
       	 	wp_enqueue_style( 'AdminAlboMultiUpload');
		}
	}

	function CreaStatistiche($IdAtto,$Oggetto){
		switch($Oggetto){
			case 5: 
					$righe=ap_get_Stat_Visite($IdAtto);
					$righeTot=ap_get_Stat_VisiteRagg($IdAtto);
					$righe=array_merge($righeTot,$righe);
					break;
			case 6: $righe=ap_get_Stat_Download($IdAtto);break;
		}
		$HtmlTesto='
				<h3>Totale '.($Oggetto==5?"Visualizzazioni":"Download").' Allegati '.ap_get_Stat_Num_log($IdAtto,$Oggetto).'</h3>
				<table class="widefat striped">
				    <thead>
					<tr>
						<th style="font-size:1.2em;">'.__('Data','albo-online').'</th>
						<th style="font-size:1.2em;">'.__('Nome Allegato','albo-online').'</th>
						<th style="font-size:1.2em;">'.__('File','albo-online').'</th>
						<th style="font-size:1.2em;">'.($Oggetto==5?__('N° Visualizzazioni','albo-online'):__('N° Download','albo-online')).'</th>
					</tr>
				    </thead>
				    <tbody>';
			foreach ($righe as $riga) {
				if(property_exists($riga,'TitoloAllegato'))
					$TitoloAllegato=$riga->TitoloAllegato;
				else
					$TitoloAllegato="";
				if(property_exists($riga,'Allegato'))
					$Allegato=$riga->Allegato;
				else
					$Allegato="";
				$HtmlTesto.= '<tr >
							<td >'.ap_VisualizzaData($riga->Data).'</td>
							<td >'.$TitoloAllegato.'</td>
							<td width="30%">'.$Allegato.'</td>
							<td >'.$riga->Accessi.'</td>
						</tr>';
				}
		$HtmlTesto.= '    </tbody>
			</table>';
		return $HtmlTesto;	
	}
/*TINY MCE Quote Button*/
	function add_albo_button() {  
	if ( current_user_can('edit_posts') &&  current_user_can('edit_pages') ){  
	 	$RuoliPuls=get_option('opt_AP_RuoliPuls');
		$RuoliPl=array();
		if($RuoliPuls){
			$RuoliPl=explode(",",$RuoliPuls);
		}
	 	$RuoliPulsG=get_option('opt_AP_RuoliPulsGruppi');
		$RuoliPlG=array();
		if($RuoliPulsG){
			$RuoliPlG=explode(",",$RuoliPulsG);
		}
	 	$RuoliPulsVA=get_option('opt_AP_RuoliPulsVisualizzaAtto');
		$RuoliPlVA=array();
		if($RuoliPulsVA){
			$RuoliPlVA=explode(",",$RuoliPulsVA);
		}
		$MieiRuoli=wp_get_current_user()->roles;
		$Vis=FALSE;
		foreach($MieiRuoli as $MioRuolo){
			if(in_array($MioRuolo,$RuoliPl)){
				$Vis=TRUE;
				break;
			}
		}
		if($Vis){
	   		add_filter('mce_external_plugins',array('AlboPretorio', 'add_albo_plugin'));  
   			add_filter('mce_buttons', array('AlboPretorio','register_albo_button'));  	
		}
		$Vis=FALSE;
		foreach($MieiRuoli as $MioRuolo){
			if(in_array($MioRuolo,$RuoliPlG)){
				$Vis=TRUE;
				break;
			}
		}
		if($Vis){
			add_filter('mce_external_plugins',array('AlboPretorio', 'add_albo_plugin_group'));  
			add_filter('mce_buttons', array('AlboPretorio','register_albo_button_group'));
		}
		$Vis=FALSE;
		foreach($MieiRuoli as $MioRuolo){
			if(in_array($MioRuolo,$RuoliPlVA)){
				$Vis=TRUE;
				break;
			}
		}
		if($Vis){
			add_filter('mce_external_plugins',array('AlboPretorio', 'add_albo_plugin_visatto'));  
			add_filter('mce_buttons', array('AlboPretorio','register_albo_button_visatto'));
		}
  	}  
}  
static function register_albo_button($buttons) {  
    array_push($buttons, "separator", "albo");  
    return $buttons;  
 }  
static function add_albo_plugin($plugin_array) {  
  $plugin_array['albo'] =Albo_URL.'/js/ButtonEditor.js';  
   return $plugin_array;  
}
static function register_albo_button_group($buttons) {  
    array_push($buttons, "separator", "albo_gruppo_atti");  
    return $buttons;  
 }  

static function add_albo_plugin_group($plugin_array) {  
  $plugin_array['albo_gruppo_atti'] =Albo_URL.'/js/ButtonEditorGroup.js';  
   return $plugin_array;  
}
static function register_albo_button_visatto($buttons) {  
    array_push($buttons, "separator", "albo_visatto");  
    return $buttons;  
 }  

static function add_albo_plugin_visatto($plugin_array) {  
  $plugin_array['albo_visatto'] =Albo_URL.'/js/ButtonEditorVisAtto.js';  
   return $plugin_array;  
}
	
	function CreaLog($Tipo,$IdOggetto,$IdAtto){
//		echo $Tipo." - ".$IdOggetto;
		$HtmlTesto='';
		switch ($Tipo){
			case 1:
				$righe=ap_get_all_Oggetto_log($Tipo,$IdOggetto);
				break;
			case 3:
				$righe=ap_get_all_Oggetto_log($Tipo,0,$IdOggetto);
				break;
			case 5:
			case 6:
				return $this->CreaStatistiche($IdOggetto,$Tipo);
				break;
		}
		if ($Tipo!=5 or $Tipo!=6){
			$HtmlTesto.='<br />';
		}
		$HtmlTesto.='
			<table class="widefat striped">
			    <thead>
				<tr>
					<th style="font-size:1.2em;">'.__('Data','albo-online').'</th>
					<th style="font-size:1.2em;">'.__('Operazione','albo-online').'</th>
					<th style="font-size:1.2em;">'.__('Informazioni','albo-online').'</th>
				</tr>
			    </thead>
			    <tbody>';
		$Operazione="";
		foreach ($righe as $riga) {
			switch ($riga->TipoOperazione){
			 	case 1:
			 		$Operazione=__('Inserimento','albo-online');
			 		break;
			 	case 2:
			 		$Operazione=__('Modifica','albo-online');
					break;
			 	case 3:
			 		$Operazione=__('Cancellazione','albo-online');
					break;
			 	case 4:
			 		$Operazione=__('Approvazione','albo-online');
					break;
			}
			$HtmlTesto.= '<tr  title="'.$riga->Utente.' da '.$riga->IPAddress.'">
						<td >'.ap_VisualizzaData($riga->Data)." ".ap_VisualizzaOra($riga->Data).'</td>
						<td >'.$Operazione.'</td>
						<td >'.stripslashes($riga->Operazione).'</td>
					</tr>';
		}
		$HtmlTesto.= '    </tbody>
				</table>';
		return $HtmlTesto;	
	}

	function add_menu(){
  		add_menu_page('Panoramica', __('Albo OnLine','albo-online'), 'gest_atti_albo', 'Albo_Pretorio',array( 'AlboPretorio','show_menu'),Albo_URL."img/logo.png");
		$atti_page=add_submenu_page( 'Albo_Pretorio', 'Atti', __('Atti','albo-online'), 'gest_atti_albo', 'atti', array( 'AlboPretorio','show_menu'));
		$categorie_page=add_submenu_page( 'Albo_Pretorio', 'Categorie', __('Categorie','albo-online'), 'gest_atti_albo', 'categorie', array( 'AlboPretorio', 'show_menu'));
		$enti=add_submenu_page( 'Albo_Pretorio', 'Enti', __('Enti','albo-online'), 'editore_atti_albo', 'enti', array('AlboPretorio', 'show_menu'));
		$uo_page=add_submenu_page( 'Albo_Pretorio', 'Unità Organizzative', __('Unità Organizzative','albo-online'), 'editore_atti_albo', 'unitao', array( 'AlboPretorio','show_menu'));
		$responsabili_page=add_submenu_page( 'Albo_Pretorio', 'Soggetti', __('Soggetti','albo-online'), 'editore_atti_albo', 'soggetti', array( 'AlboPretorio','show_menu'));
		$tipifiles=add_submenu_page( 'Albo_Pretorio', 'Tipi di files', __('Tipi di Files','albo-online'), 'admin_albo', 'tipifiles', array( 'AlboPretorio','show_menu'));
		$tipifiles=add_submenu_page( 'Albo_Pretorio', 'Tabelle', __('Tabelle','albo-online'), 'admin_albo', 'tabelle', array( 'AlboPretorio','show_menu'));
		$parametri_page=add_submenu_page( 'Albo_Pretorio', 'Generale', __('Parametri','albo-online'), 'admin_albo', 'configAlboP', array( 'AlboPretorio','show_menu'));
		$permessi=add_submenu_page( 'Albo_Pretorio', 'Permessi', __('Permessi','albo-online'), 'admin_albo', 'permessiAlboP', array('AlboPretorio', 'show_menu'));
		$utility=add_submenu_page( 'Albo_Pretorio', 'Utility', __('Utility','albo-online'), 'admin_albo', 'utilityAlboP', array('AlboPretorio', 'show_menu'));
		$utility=add_submenu_page( 'Albo_Pretorio', 'LogAggiornamenti', __('Log Aggiornamenti','albo-online'), 'admin_albo', 'logagg', array('AlboPretorio', 'show_menu'));				
		add_action( 'admin_head-'. $atti_page, array( 'AlboPretorio','ap_head' ));
		add_action( "load-$atti_page", array('AlboPretorio', 'screen_option'));

}
	static function screen_option() {
		if(!isset($_GET['action'])){
			$args=array('label'   => __('Atti per pagina','albo-online'),
				   'default' => 25,
				   'option'  => 'atti_per_page');
			add_screen_option( 'per_page', $args );			
		}
	}

	function atti_set_option($status, $option, $value) {
	    if ( 'atti_per_page' == $option ) 
	    	return $value;
	}	

	static function show_menu() {
		global $AP_OnLine;

		switch ($_REQUEST['page']){
			case "Albo_Pretorio" :
				$AP_OnLine->ShowBacheca();
				break;
			case "configAlboP" :
				$AP_OnLine->AP_config();
				break;
			case "categorie" :
			// interfaccia per la gestione delle categorie
				include_once ( dirname (__FILE__) . '/admin/categorie.php' );	
				break;
			case "soggetti" :
			// interfaccia per la gestione dei soggetti
				include_once ( dirname (__FILE__) . '/admin/soggetti.php' );	
				break;
			case "tipifiles" :
			// interfaccia per la gestione dei tipi di files
				include_once ( dirname (__FILE__) . '/admin/tipidifiles.php' );	
				break;
			case "tabelle" :
			// interfaccia per la gestione dei tipi di files
				include_once ( dirname (__FILE__) . '/admin/tabelle.php' );	
				break;
			case "enti" :
			// interfaccia per la gestione degli enti
				include_once ( dirname (__FILE__) . '/admin/enti.php' );	
				break;
			case "atti" :
			// interfaccia per la gestione degli atti
				include_once ( dirname (__FILE__) . '/admin/atti.php' );
				break;
			case "allegati" :
			// interfaccia per la gestione degli allegati
				include_once ( dirname (__FILE__) . '/admin/allegati.php' );
				break;
			case "permessiAlboP":
			// interfaccia per la gestione dei permessi
				include_once ( dirname (__FILE__) . '/admin/permessi.php' );
				break;
			case "utilityAlboP":
			// interfaccia per la gestione dei permessi
				include_once ( dirname (__FILE__) . '/admin/utility.php' );
				break;
			case "unitao":
			// interfaccia per la gestione dei permessi
				include_once ( dirname (__FILE__) . '/admin/unitaorganizzative.php' );
				break;		
			case "logagg":
			// interfaccia per la visualizzazione deaggiornamenti apportati all'albo
				include_once ( dirname (__FILE__) . '/inc/logaggiornamenti.php' );
				break;								
		}
	}
	
	function init() {
		if (is_admin()) return;
		wp_enqueue_script('jquery');
	}

################################################################################
// ADMIN HEADER
################################################################################


	static function ap_head() {
/*		global $wp_db_version, $wp_dlm_root;
		?>
<script language="JavaScript">
	function change(html){
		description.innerHTML=html
	}
</script>
*/
		if($_GET['page']=='atti' And (isset($_GET['stato_atti']) And $_GET['stato_atti']=='Correnti') And current_user_can('editore_atti_albo')){
?>			<style type="text/css">
				#Stato{
					width: 15%;
				}
			</style>
<?php
		}
	}

	function head_Front_End() {
		global $wp_query;
		$postObj=$wp_query->get_queried_object();
		if(is_object($postObj) And 
		  ($postObj->post_type=="avcp" Or 
		   strpos($postObj->post_content,"[Albo")!== FALSE)){
			echo "
	<!--HEAD Albo Preotrio On line -->
	";
			if(get_option('blog_public')==1)
				echo "	<meta name='robots' content='noindex, nofollow, noarchive' />
	<!--HEAD Albo Preotrio On line -->
			";
			else
				echo "	<meta name='robots' content='noarchive' />
	<!--HEAD Albo Preotrio On line -->
			";
	$OldInterfaccia=get_option('opt_AP_OldInterfaccia');
	$UploadCSSNI=get_option('opt_AP_UpCSSNewInterface');

	if($OldInterfaccia!="Si" AND $UploadCSSNI!="Si"){
			?>
<script type="text/javascript">
    WebFontConfig = {
      google: {
        families: ['Titillium+Web:300,400,600,700,400italic:latin']
      }
    };
    (function() {
      var wf = document.createElement('script');
      wf.src = 'https://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
      wf.type = 'text/javascript';
      wf.async = 'true';
      var s = document.getElementsByTagName('script')[0];
      s.parentNode.insertBefore(wf, s);
    })();
</script>
<?php
	}
	    wp_enqueue_script('jquery');
	    wp_enqueue_script('Albo-Public-Jquery-UI',plugins_url('js/jquery-ui.min.js', __FILE__ ));
	    wp_enqueue_script('jquery-ui-tabs', '', array('jquery'));
		wp_enqueue_script( 'jquery-ui-datepicker', '', array('jquery'));
		wp_enqueue_script( 'Albo-Public', plugins_url('js/Albo.public.js', __FILE__ ));
    if($OldInterfaccia!="Si"  AND $UploadCSSNI!="Si"){
		wp_register_style('AlboPretorioWTS', plugins_url( 'css/build/build.css', __FILE__ ) );
        wp_enqueue_style( 'AlboPretorioWTS');
		wp_register_style('AlboPretorioNewStyle', plugins_url( 'css/stylenew.css', __FILE__ ) );
        wp_enqueue_style( 'AlboPretorioNewStyle');
		wp_enqueue_script('Albo-PublicDesignItalia', plugins_url('css/build/IWT.min.js', __FILE__ ));
	}
        wp_register_style('AlboPretorioStyle', plugins_url( 'css/style.css', __FILE__ ) );
        wp_enqueue_style( 'AlboPretorioStyle');
		echo "<!--FINE HEAD Albo Preotrio On line -->";	
		}
	}
	
	function load_dependencies() {
			// Load backend libraries
			if ( is_admin() ) {	
				require_once (dirname (__FILE__) . '/admin/admin.php');
			}	
		}
	
	function VisualizzaAtti($Parametri){
		$ret="";
		$Parametri=shortcode_atts(array(
			'stato' => '-2',
			'cat' => 0,
			'filtri' => 'si',
			'minfiltri' =>'si',
			'per_page' =>'10'
		), $Parametri,"Albo");
		$OldInterfaccia=get_option('opt_AP_OldInterfaccia');
		if(is_file(get_stylesheet_directory()."/plugins/albo-pretorio-on-line/admin/frontend.php")){
			require_once ( get_stylesheet_directory()."/plugins/albo-pretorio-on-line/admin/frontend.php");
		}else{
			if($OldInterfaccia=="Si"){
				require_once ( dirname (__FILE__) . '/admin/frontend.php' );
			}else{
				require_once ( dirname (__FILE__) . '/admin/frontend_new.php' );
			}		
		}
		return $ret;
	}
	function VisualizzaGruppiAtti($Parametri){
		if(get_option('opt_AP_AutoShortcode'))
			return;
		$ret="";
		$Parametri=shortcode_atts(array(
			'titolo' => __('Atti Albo on line di riferimento','albo-online'),
			'meta' => '',
			'valore' => '',
		), $Parametri,"AlboGruppiAtti");
		$OldInterfaccia=get_option('opt_AP_OldInterfaccia');
		if($OldInterfaccia=="Si"){
			require_once ( dirname (__FILE__) . '/admin/gruppiatti.php' );
		}else{
			require_once ( dirname (__FILE__) . '/admin/gruppiatti_new.php' );
		}
		return Lista_AttiGruppo($Parametri);
	}
	function VisualizzaAtto($Parametri){
		$ret="";
		$Parametri=shortcode_atts(array(
			'titolo' => __('Atto Albo on line','albo-online'),
			'numero' => '',
			'anno' => '',
		), $Parametri,"AlboAtto");
		$OldInterfaccia=get_option('opt_AP_OldInterfaccia');
		if(is_file(get_stylesheet_directory()."/plugins/albo-pretorio-on-line/admin/visatto.php")){
			require_once ( get_stylesheet_directory()."/plugins/albo-pretorio-on-line/admin/visatto.php");
		}else{
			if($OldInterfaccia=="Si"){
				require_once ( dirname (__FILE__) . '/admin/visatto.php' );
			}else{
				require_once ( dirname (__FILE__) . '/admin/visatto_new.php' );
			}
		}
		return Visualizza_Atto($Parametri);
	}	

	function ShowBacheca(){
	global $wpdb;
		if (isset($_REQUEST['action']) And $_REQUEST['action']=="setta-anno"){
		  update_option('opt_AP_AnnoProgressivo',date("Y") );
		  update_option('opt_AP_NumeroProgressivo',1 );
		  $_SERVER['REQUEST_URI'] = remove_query_arg(array('action'), $_SERVER['REQUEST_URI']);
		}
		$n_atti = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->table_name_Atti;");	 
		$n_atti_dapub = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->table_name_Atti Where Numero=0;");	
		$n_atti_attivi = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->table_name_Atti Where DataInizio <= now() And DataFine>= now() And Numero>0;");	
		$n_atti_storico=$n_atti-$n_atti_attivi-$n_atti_dapub; 
		$n_allegati = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->table_name_Allegati;");	 
		$n_categorie = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->table_name_Categorie;");	 
		$n_atti_oblio = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->table_name_Atti Where DataOblio < now() And Numero>0;");	
		$oblio=TRUE;
		if(!is_file(AP_BASE_DIR.get_option('opt_AP_FolderUpload')."/.htaccess"))
			$oblio=FALSE;
		if(!is_file(AP_BASE_DIR.get_option('opt_AP_FolderUpload')."/index.php"))
			$oblio=FALSE;
		if(!is_file(APHomePath."/robots.txt"))
			$oblio=FALSE;	
		$Cartella=str_replace("\\","/",AP_BASE_DIR.get_option('opt_AP_FolderUpload'));
		//$permessi=ap_get_fileperm($Cartella);		
		$permProp=ap_get_fileperm_Gruppo($Cartella,"Proprietario");
		$StatoCartella="";
		if($permProp==7 Or $permProp==6 Or $permProp==3 Or $permProp==2)
			$StatoCartella=$Cartella."<br />";
		$Cartella=AlboBCK;
		//$permessi=ap_get_fileperm($Cartella);		
		$permProp=ap_get_fileperm_Gruppo($Cartella,"Proprietario");
		if($permProp==7 Or $permProp==6 Or $permProp==3 Or $permProp==2)
			$StatoCartella=$Cartella."<br />";
		$Cartella=AlboBCK.'/BackupDatiAlbo';
		//$permessi=ap_get_fileperm($Cartella);		
		$permProp=ap_get_fileperm_Gruppo($Cartella,"Proprietario");
		if($permProp==7 Or $permProp==6 Or $permProp==3 Or $permProp==2)
			$StatoCartella=$Cartella."<br />";
		$Cartella=AlboBCK.'/OblioDatiAlbo';
		//$permessi=ap_get_fileperm($Cartella);		
		$permProp=ap_get_fileperm_Gruppo($Cartella,"Proprietario");
		if($permProp==7 Or $permProp==6 Or $permProp==3 Or $permProp==2)
			$StatoCartella=$Cartella."<br />";
		echo ' <div class="welcome-panel" class="welcome-panel" >
	         	<div class="welcome-panel-content" style="display:inline;float:left;width:35%;">
					<p style="float:left;">
						<img src="'.Albo_URL.'/img/LogoAlbo.png" alt="'.__('Logo Albo on line pubblicità legale','albo-online').'" style="width:100%;" />
					<br />'.__('Versione','albo-online').' <strong>'.$this->version.'</strong></p>
					<p style="font-size:1.2em;text-align: center;">'.__('Plugin sviluppato da','albo-online').' <strong><a href="mailto:ignazios@gmail.com" title="'.__('Invia email allo sviluppatore del plugin','albo-online').'" target="_blank">Scimone Ignazio</a></strong>
					</p>
					<p style="float:left;">
		 				<iframe src="//www.facebook.com/plugins/likebox.php?href=https%3A%2F%2Fwww.facebook.com%2Fpages%2FAlbo-Pretorio%2F1487571581520684%3Fref%3Dhl&amp;width&amp;height=230&amp;colorscheme=light&amp;show_faces=true&amp;header=true&amp;stream=false&amp;show_border=true" scrolling="no" frameborder="0" style="border:none; overflow:hidden;height:230px; width: 300px; margin-top:20px;margin-left: 50px;" allowTransparency="true"></iframe>
					</p>	
				</div>
				<div class="welcome-panel-content"  style="display:inline;float:right;width:60%;">
					<div class="widefat" style="display:inline;">
						<table style="margin-bottom:20px;border: 1px solid #e5e5e5;">
							<caption style="font-size:1.2em;font-weight:bold;">'.__('Sommario','albo-online').'</caption>
							<thead>
								<tr>
									<th>'.__('Oggetto','albo-online').'</th>
									<th>'.__('N.','albo-online').'</th>
									<th>'.__('In Attesa di Pubblicazione','albo-online').'</th>
									<th>'.__('Attivi','albo-online').'</th>
									<th>'.__('Scaduti','albo-online').'</th>
									<th>'.__('Da eliminare','albo-online').'</th>
								</tr>
							</thead>
							<tbody>
								<tr class="first">
									<td style="text-align:left;width:200px;" >'.__('Atti','albo-online').'</td>
									<td style="text-align:left;width:200px;">'.$n_atti.'</td>
									<td style="text-align:left;width:200px;">'.$n_atti_dapub.'</td>
									<td style="text-align:left;width:200px;">'.$n_atti_attivi.'</td>
									<td style="text-align:left;width:200px;">'.$n_atti_storico.'</td>
									<td style="text-align:left;width:200px;">'.$n_atti_oblio.'</td>
								</tr>
								<tr>
									<td>'.__('Categorie','albo-online').'</td>
									<td colspan="4">'.$n_categorie.'</td>
								</tr>
								<tr>
									<td>'.__('Allegati','albo-online').'</td>
									<td colspan="4">'.$n_allegati.'</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div>
						<a href="http://eduva.org" target="_blank">
							<input type="submit" name="submit" id="submit" class="button button-primary" value="'.__('Sito di supporto','albo-online').'">
						</a>
						<a href="http://www.eduva.org/wp-content/uploads/2014/02/Albo-Pretorio-On-line.pdf" target="_blank">
							<input type="submit" name="submit" id="submit" class="button button-primary" value="'.__('Manuale Albo Pretorio','albo-online').'">
						</a>
						<a href="http://www.eduva.org/io-utilizzo-il-plugin"target="_blank">
							<input type="submit" name="submit" id="submit" class="button button-primary" value="'.__('Io utilizzo il plugin','albo-online').'">
						</a>
					</div>
		 			<div class="widefat" style="width: 320px;margin:auto;padding:20px;">	
						<iframe width="560" height="315" src="https://www.youtube.com/embed/uEiSlrAPjas" frameborder="0" gesture="media" allow="encrypted-media" allowfullscreen></iframe>				
					<div>
				</div>
			</div>
		</div>
	<div style="clear:both;"></div>
	<div class="widefat" >
		<h3 style="text-align:center;font-size:1.5em;font-weight: bold;">'.__('Cruscotto','albo-online').'</p>
		<table style="width:100%;">
			<thead>
				<tr>
					<th>'.__('Ambito','albo-online').'</th>
					<th>'.__('Stato','albo-online').'</th>
					<th>'.__('Note','albo-online').'</th>
					<th>'.__('Azioni','albo-online').'</th>
				</tr>
			</thead>
			<tbody>
			<tr>
				<th scope="row">'.__('Librerie','albo-online').'</th>';
	if (is_file(Albo_DIR.'/inc/pclzip.php')){
 		echo'<td><span class="dashicons dashicons-yes" style="color:#18b908;font-size:2em;"></span></td>
		     <td></td>
			 <td></td>';
	}else{
		echo'<td><span class="dashicons dashicons-no" style="color:red;font-size:2em;"></span></td>
			<td>'.__('Senza questa libreria non puoi eseguire i Backup','albo-online').'</td>
			</td>';
	}
	echo '</tr>
			<tr>
				<th scope="row">'.__("Diritto all'oblio",'albo-online').'</th>';
	if ($oblio And ap_VerificaRobots() And ap_VerificaOblio()){
 		echo'<td><span class="dashicons dashicons-yes" style="color:#18b908;font-size:2em;"></span></td>
		     <td></td>
			 <td></td>';
	}else{		
 		echo '<td><span class="dashicons dashicons-no" style="color:red;font-size:2em;"></td>
		     <td></td>
			 <td><a href="?page=Albo_Pretorio&amp;action=creafoblio&amp;rigenera='.wp_create_nonce('rigeneraoblio').'">'.__('Rigenera files','albo-online').'</a>
			</td>';		
	}
	echo'	</tr>
			<tr>
				<th scope="row">'.__('Cartelle esistenza e permessi','albo-online').'</th>';
	if(strlen($StatoCartella)>0){
 		echo'<td><span class="dashicons dashicons-yes" style="color:#18b908;font-size:2em;"></span></td>
		     <td></td>
			 <td></td>';
	}else{
 		echo '<td><span class="dashicons dashicons-no" style="color:red;font-size:2em;"></td>
		     <td>'.$StatoCartella.'</td>
			 <td>';
		echo (!$oblio?'<a href="?page=Albo_Pretorio&amp;action=creafoblio&amp;rigenera='.wp_create_nonce('rigeneraoblio').'">'.__('Rigenera files','albo-online').'</a>':'');
		echo	 '</td>';		
	}		
	echo		'</tr>
		</tbody>
		</table>
		<p><em>'.__('per maggiori dettagli eseguire la verifica della procedura presente nel menu Utility','albo-online').'</em></p>
	</div>';
if (ap_get_num_categorie()==0){
	echo'<div class="welcome-panel" >
			<div class="widefat" >
					<p style="text-align:center;font-size:1.2em;font-weight: bold;color: green;">
					'.__('Non risultano categorie codificate, se vuoi posso impostare le categorie di default','albo-online').' &ensp;&ensp;<a href="?page=utilityAlboP&amp;action=creacategorie">'.__('Crea Categorie di Default','albo-online').'</a></p>
				</div>
			</div>';
}
if (ap_num_responsabili()==0){
	echo'<div class="welcome-panel" >
			<div class="widefat" >
					<p style="text-align:center;font-size:1.2em;font-weight: bold;color: green;">
					'.sprintf(__('Non risultano %sResponsabili%s codificati, devi crearne almeno uno prima di iniziare a codificare gli Atti','albo-online'),"<strong>","</strong>").' &ensp;&ensp;<a href="?page=soggetti">'.__('Crea Soggetti','albo-online').'</a></p>
				</div>
			</div>';
}
if (ap_num_unitao()==0){
	echo'<div class="welcome-panel" >
			<div class="widefat" >
				<p style="text-align:center;font-size:1.2em;font-weight: bold;color: green;">
				'. sprintf(__("Non risulta nessuna %sUnità Organizzativa%s codificata, devi crearne almeno una prima di iniziare a codificare gli Atti","albo-online"),"<strong>","</strong>").' &ensp;&ensp;<a href="?page=unitao">'. __("Crea Unità Organizzativa","albo-online").'</a>
				</p>
			</div>';
}
if(get_option('opt_AP_AnnoProgressivo')!=date("Y")){
	echo '<div style="border: medium groove Blue;margin-top:10px;">
			<div style="float:none;width:200px;margin-left:auto;margin-right:auto;">
				<form id="agg_anno_progressivo" method="post" action="?page=configAlboP">
					<input type="hidden" name="action" value="setta-anno" />
				<input type="submit" name="submit" id="submit" class="button" value="'.__('Aggiorna Anno Albo ed Azzera numero Progressivo','albo-online').'"  />
				</form>
			</div>
		 </div>';
}
}	

	function AP_config(){
	$stato="";
	  if (isset($_REQUEST['action']) And $_REQUEST['action']=="setta-anno"){
		update_option('opt_AP_AnnoProgressivo',date("Y") );
		update_option('opt_AP_NumeroProgressivo',1 );
		$_SERVER['REQUEST_URI'] = remove_query_arg(array('action'), $_SERVER['REQUEST_URI']);
	  }
	  
	  if (isset($_GET['update']))
	  	if($_GET['update'] == 'true')
			$stato="<div id='setting-error-settings_updated' class='updated settings-error'> 
				<p><strong>Impostazioni salvate.</strong></p></div>";
		  else
			$stato="<div id='setting-error-settings_updated' class='updated settings-error'> 
				<p><strong>".__("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione è stata annullata","albo-online")."</strong></p></div>";
	  $current_user 		= wp_get_current_user();
	  $ente   				= stripslashes(ap_get_ente_me());
	  $entedefault			= get_option('opt_AP_DefaultEnte');
	  $nprog  				= get_option('opt_AP_NumeroProgressivo');
	  $nanno				= get_option('opt_AP_AnnoProgressivo');
	  $visente				= get_option('opt_AP_VisualizzaEnte');
	  $livelloTitoloEnte	= get_option('opt_AP_LivelloTitoloEnte');
	  $livelloTitoloPagina	= get_option('opt_AP_LivelloTitoloPagina');
	  $livelloTitoloFiltri	= get_option('opt_AP_LivelloTitoloFiltri');
	  $colAnnullati			= get_option('opt_AP_ColoreAnnullati');
	  $colPari				= get_option('opt_AP_ColorePari');
	  $colDispari			= get_option('opt_AP_ColoreDispari');
	  $LogOperazioni		= get_option('opt_AP_LogOp');
	  $PaginaAttiCor		= get_option('opt_AP_PAttiCor');
	  $PaginaAttiSto		= get_option('opt_AP_PAttiSto');
	  $PaginaAtto			= get_option('opt_AP_PAtto');
	  //$TempoOblio=get_option('opt_AP_GiorniOblio');
	  $RuoliPuls			= get_option('opt_AP_RuoliPuls');
	  $RuoliPulsG			= get_option('opt_AP_RuoliPulsGruppi');
	  $RuoliPulsVA			= get_option('opt_AP_RuoliPulsVisualizzaAtto');
	  $OldInterfaccia		= get_option('opt_AP_OldInterfaccia');
	  $UploadCSSNI			= get_option('opt_AP_UpCSSNewInterface');
	  $AutoShortcode		= get_option('opt_AP_AutoShortcode');
	  $Testi				= json_decode(get_option('opt_AP_Testi'),TRUE);
	  $IconaDocumenti		= get_option('opt_AP_IconaDocumenti');
	  $RestApi				= get_option('opt_AP_RestApi');
	  if($RestApi=="Si"){
	  	$ChkRestApi=" checked='checked' ";
	  }else{
	  	$ChkRestApi="";	
	  }
	  $RestApiUrlEst		= get_option('opt_AP_RestApi_UrlEst');
	  if(!is_array($Testi)){
	  	$Testi=array("NoResp"=>"",
	  	             "CertPub"=>__("Si attesta l'avvenuta pubblicazione del documento all'albo pretorio sopra indicato per il quale non sono pervenute osservazioni",'albo-online'));
	  }
	  $RuoliPl=array();
	  if($RuoliPuls){
	  	$RuoliPl=explode(",",$RuoliPuls);
	  }
	  $RuoliPlG=array();
	  if($RuoliPulsG){
	  	$RuoliPlG=explode(",",$RuoliPulsG);
	  }
	  $RuoliPlVA=array();
	  if($RuoliPulsVA){
	  	$RuoliPlVA=explode(",",$RuoliPulsVA);
	  }
	  $FEColsOption=get_option('opt_AP_ColonneFE',array(
	  										"Data"=>0,
	  										"Ente"=>0,
	  										"Riferimento"=>0,
	  										"Oggetto"=>0,
	  										"Validita"=>0,
	  										"Categoria"=>0,
											"Note"=>0,
											"DataOblio"=>0));
	  if(!is_array($FEColsOption)){
	  	$FEColsOption=json_decode($FEColsOption,TRUE);
	  }
	  $DefaultSoggetti=get_option('opt_AP_DefaultSoggetti',array(
	  										"RP"=>0,
	  										"RB"=>0,
	  										"AM"=>0));
	  if(!is_array($DefaultSoggetti)){
	  	$DefaultSoggetti=json_decode($DefaultSoggetti,TRUE);
	  }
	  $LOStatoN=" checked='checked' ";
	  if($LogOperazioni=="Si"){
	  		$LOStatoS=" checked='checked' ";
	  		$LOStatoN="";
	  }	  
	  $LogAccessi=get_option('opt_AP_LogAc');
	  $LOAccessiS="";
	  $LOAccessiN=" checked='checked' ";
	  if($LogAccessi=="Si"){
	  		$LOAccessiS=" checked='checked' ";
	  		$LOAccessiN="";
	  }	  
	  $LogAccessi=get_option('opt_AP_LogAc');
	  if ($visente=="Si")
	  	$ve_selezionato='checked="checked"';
	  else
	  	$ve_selezionato='';
	  if (!$nanno){
		$nanno=date("Y");
		}
	  $dirUpload =  stripslashes(get_option('opt_AP_FolderUpload'));
	  if($OldInterfaccia=="Si"){
	  	$OldInterfacciaS=" checked='checked' ";
	  }else{
	  	$OldInterfacciaS="";
	  }
	  if($UploadCSSNI=="Si"){
	  	$UploadCSSNIS=" checked='checked' ";
	  }else{
	  	$UploadCSSNIS="";	
	  }
	  if(get_option( 'permalink_structure' )==""){
			$BaseUrlRestApi=esc_url( home_url( '/' ) )."?rest_route=/alboonline/v1/";
		}else{
			$BaseUrlRestApi=esc_url( home_url( '/' ) )."wp-json/alboonline/v1/";
		} 
	  if (get_option( 'opt_AP_FolderUploadMeseAnno' )=="") {
		  $dirUploadMA="Disattivata";
		} else {
			$dirUploadMA="Attivata";
		}
	  echo '
		<div class="wrap">
			<div class="HeadPage">
				<h2 class="wp-heading-inline"><span class="dashicons dashicons-admin-settings" style="font-size:1em;"></span> '.__('Parametri','albo-online').'</h2>
			</div>'.$stato.'
	 <form name="AlboPretorio_cnf" action="'.get_bloginfo('wpurl').'/wp-admin/index.php" method="post">
	  <input type="hidden" name="c_AnnoProgressivo" value="'.$nanno.'"/>
	  <input type="hidden" name="confAP" value="'.wp_create_nonce('configurazionealbo').'" />
	  <div id="config-tabs-container" style="margin-top:20px;">
		<ul>
			<li><a href="#Conf-tab-1">'.__('Impostazioni Generali','albo-online').'</a></li>
			<li><a href="#Conf-tab-2">'.__('Interfaccia','albo-online').'</a></li>
			<li><a href="#Conf-tab-3">'.__('Log','albo-online').'</a></li>
			<li><a href="#Conf-tab-4">'.__('Shortcode','albo-online').'</a></li>
			<li><a href="#Conf-tab-5">'.__('Testi','albo-online').'</a></li>
			<li><a href="#Conf-tab-6">'.__('Soggetti predefiniti','albo-online').'</a></li>
			<li><a href="#Conf-tab-7">'.__('Rest Api','albo-online').'</a></li>
		</ul>	 
		<div id="Conf-tab-1">
		  <table class="albo_cell">
			<tr>
				<th scope="row"><label for="nomeente">'.__('Nome Ente','albo-online').'</label></th>
				<td><input type="text" name="c_Ente" value=\''.$ente.'\' style="width:80%;" id="nomeente"/></td>
			</tr>
			<tr>
				<th scope="row"><label for="defEnte">'.__('Ente di default','albo-online').'</label></th>
				<td>'.ap_get_dropdown_enti('defEnte',__('Ente','albo-online'),'postform','',$entedefault).'</td>
			</tr>
			<tr>
				<th scope="row"><label for="visente">'.__('Visualizza Nome Ente','albo-online').'</label></th>
				<td><input type="checkbox" name="c_VEnte" value="Si" '.$ve_selezionato.' id="visente"/></td>
			</tr>
			<tr>
				<th scope="row"><label for="LivelloTitoloEnte">'.__('Titolo Nome Ente','albo-online').'</label></th>
				<td>
					<select name="c_LTE" id="LivelloTitoloEnte" >';
				for ($i=2;$i<5;$i++){
					echo '<option value="h'.$i.'"';
					if($livelloTitoloEnte=='h'.$i) 
						echo 'selected="selected"';
					echo '>h'.$i.'</option>';	
				}
			echo '</select></td>
			</tr>		
			<tr>
				<th scope="row"><label for="LivelloTitoloPagina">'.__('Titolo Pagina Albo','albo-online').'</label></th>
				<td>
					<select name="c_LTP" id="LivelloTitoloPagina" >';
				for ($i=2;$i<5;$i++){
					echo '<option value="h'.$i.'"';
					if($livelloTitoloPagina=='h'.$i) 
						echo 'selected="selected"';
					echo '>h'.$i.'</option>';	
				}
			echo '</select></td>
			</tr>		
			<tr>
				<th scope="row"><label for="LivelloTitoloFiltri">'.__('Titolo Filtri','albo-online').'</label></th>
				<td>
					<select name="c_LTF" id="LivelloTitoloFiltri" >';
				for ($i=2;$i<5;$i++){
					echo '<option value="h'.$i.'"';
					if($livelloTitoloFiltri=='h'.$i) 
						echo 'selected="selected"';
					echo '>h'.$i.'</option>';	
				}
			echo '</select></td>
			</tr>		
			<tr>
				<th scope="row"><label>'.__('Numero Progressivo','albo-online').'</label></th>
				<td><strong> ';
				if(ap_get_all_atti(0,0,0,0,'',0,0,"",0,0,TRUE,FALSE)==0)
					echo '<input type="text" id="progressivo" name="progressivo" value="'.$nprog.'" size="5"/>';
				else
					echo $nprog;
			echo ' / '.$nanno.'</strong>	
				</td>
			</tr>
			<tr>
				<th scope="row"><label>'.__('Cartella Upload<br />organizzazione in Anno/Mese','albo-online').'</label></th>
				<td><strong> '.AP_BASE_DIR.get_option('opt_AP_FolderUpload').'<br />'.$dirUploadMA.'</strong></td>
			</tr>
			<tr>
				<th scope="row"><label for="visoldstyle">'.__('Stile visualizzazione FrontEnd','albo-online').'</label></th>
				<td><input type="checkbox" name="visoldstyle" value="Si" '.$OldInterfacciaS.' id="visoldstyle"/>
					'.__('Selezionare questa opzione per mantenere la visualizzazione classica del FrontEnd.<br />
					Se si deseleziona l\'opzione verrà visualizzato il FrontEnd con layout in linea con le linee guida di','albo-online').' <a href="https://italia.github.io/design-web-toolkit/">design.italia.it</a> 
					
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="uploadCSSNI">'.__('Tema compatibile con il Design KIT di Designers Italia','albo-online').'</label></th>
				<td><input type="checkbox" name="uploadCSSNI" value="Si" '.$UploadCSSNIS.' id="uploadCSSNI"/>
					'.__('Selezionare questa opzione nel caso in cui si utilizza un tema sviluppato partendo dal Design KIT di <a href="https://designers.italia.it/">design.italia.it</a> verranno caricati i CSS ed i JS del Kit','albo-online').'
				</td>
			</tr>
		</table>
		</div>
		<div id="Conf-tab-2">	
			<div style="float:left;display:inline;width:50%;">
			<h3>Colori</h3>	  
			<table class="albo_cell">
				<tr>
					<th scope="row"><label for="color">'.__('Righe Atti Annullati','albo-online').'</label></th>
					<td> 
						<input type="text" id="color" name="color" value="'.$colAnnullati.'" size="5"/>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="colorp">'.__('Righe Pari','albo-online').'</label></th>
					<td> 
						<input type="text" id="colorp" name="colorp" value="'.$colPari.'" size="5"/>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="colord">'.__('Righe Dispari','albo-online').'</label></th>
					<td> 
						<input type="text" id="colord" name="colord" value="'.$colDispari.'" size="5"/>
					</td>
				</tr>
			</table>
			</div>
			<div style="float:left;display:inline;width:50%;">
				<h3>Colonne Tabella</h3>
				<table class="albo_cell">
				<tr>
					<th scope="row"><label for="data">'.__('Data','albo-online').'</label></th>
					<td> 
						<input type="checkbox" id="data" name="Data" value="1" '.($FEColsOption['Data']==1?"checked":"").'/>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="ente">'.__('Ente','albo-online').'</label></th>
					<td> 
						<input type="checkbox" id="ente" name="Ente" value="1" '.($FEColsOption['Ente']==1?"checked":"").'/>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="riferimento">'.__('Riferimento','albo-online').'</label></th>
					<td> 
						<input type="checkbox" id="riferimento" name="Riferimento" value="1" '.($FEColsOption['Riferimento']==1?"checked":"").'/>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="oggetto">'.__('Oggetto','albo-online').'</label></th>
					<td> 
						<input type="checkbox" id="oggetto" name="Oggetto" value="1" '.($FEColsOption['Oggetto']==1?"checked":"").'/>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="validita">'.__('Validità','albo-online').'</label></th>
					<td> 
						<input type="checkbox" id="validita" name="Validita" value="1" '.($FEColsOption['Validita']==1?"checked":"").'/>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="categoria">'.__('Categoria','albo-online').'</label></th>
					<td> 
						<input type="checkbox" id="categoria" name="Categoria" value="1" '.($FEColsOption['Categoria']==1?"checked":"").'/>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="note">'.__('Note','albo-online').'</label></th>
					<td> 
						<input type="checkbox" id="note" name="Note" value="1" '.($FEColsOption['Note']==1?"checked":"").'/>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="oblio">'.__('Data Oblio','albo-online').'</label></th>
					<td> 
						<input type="checkbox" id="oblio" name="DataOblio" value="1" '.($FEColsOption['DataOblio']==1?"checked":"").'/>
					</td>
				</tr>
				</table>
			</div>
				<table class="albo_cell">
				<tr>
					<th scope="row"><label for="PaginaAttiCorrenti">'.__('Pagina Atti Correnti','albo-online').'</label>
					</th>
					<td>
						<select name="P_AttiCor" id="PaginaAttiCorrenti" >';
					  $pages = get_pages(); 
					  foreach ( $pages as $pagg ) {
					    if (get_page_link( $pagg->ID ) == $PaginaAttiCor ) 
							$Selezionato= 'selected="selected"';
						else
							$Selezionato="";
					  	$option = '<option '.$Selezionato.' value="' . get_page_link( $pagg->ID ) . '">';
						$option .= $pagg->post_title;
						$option .= '</option>';
						echo $option;
					  }
				echo '</select></td>
				</tr>		
				<tr>
					<th scope="row"><label for="PaginaAttiStorico">'.__('Pagina Albo Storico','albo-online').'</label>
					</th>
					<td>
						<select name="P_AttiSto" id="PaginaAttiStorico" >';
					  $pages = get_pages(); 
					  foreach ( $pages as $pagg ) {
					    if (get_page_link( $pagg->ID ) == $PaginaAttiSto ) 
							$Selezionato= 'selected="selected"';
						else
							$Selezionato="";
					  	$option = '<option '.$Selezionato.' value="' . get_page_link( $pagg->ID ) . '">';
						$option .= $pagg->post_title;
						$option .= '</option>';
						echo $option;
					  }
				echo '</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="PaginaAtto">'.__('Pagina Visualizzazione singolo atto','albo-online').'</label>
					</th>
					<td>
						<select name="P_Atto" id="PaginaAtt0" >';
					  $pages = get_pages(); 
					  foreach ( $pages as $pagg ) {
					    if (get_page_link( $pagg->ID ) == $PaginaAtto ) 
							$Selezionato= 'selected="selected"';
						else
							$Selezionato="";
					  	$option = '<option '.$Selezionato.' value="' . get_page_link( $pagg->ID ) . '">';
						$option .= $pagg->post_title;
						$option .= '</option>';
						echo $option;
					  }
				echo '</select>
					</td>
				</tr>
				<tr>
					<th>
						<label for="icona">'.__('Immagine documenti','albo-online').'</label>
					</th>
					<td>
						<input name="imgDocumenti" id="icona" type="text" value="'.$IconaDocumenti.'" style="width:80%;" aria-required="true" />
						<input id="icona_upload" class="button" type="button" value="Carica" /><br />'.__('Dimensione max 256x256','albo-online').'
						<div style="margin-top:5px;">
							<img src="'.$IconaDocumenti.'" width="30" height="30" id="IconaTipoFile"/>
						</div>
					</td>
				</tr>		
				</table>
		</div>
		<div id="Conf-tab-3">		  
			<table class="albo_cell">
			<tr>
				<th scope="row"><label for="LogOperazioni">'.__("Abilita il Log sulle Operazioni di gestione degli Oggetti dell'Albo",'albo-online').'</label></th>
				<td> 
					<input type="radio" id="LogOperazioniSi" name="LogOperazioni" value="Si" '.$LOStatoS.'>'.__('Si','albo-online').'<br>
					<input type="radio" id="LogOperazioniNo" name="LogOperazioni" value="No" '.$LOStatoN.'>'.__('No','albo-online').'
				</td>		
			</tr>
			<tr>
				<th scope="row"><label for="LogOperazioni">'.__('Abilita il Log sulle Visualizzazioni/Download degli atti pubblicati','albo-online').'</label></th>
				<td> 
					<input type="radio" id="LogAccessiSi" name="LogAccessi" value="Si" '.$LOAccessiS.'>'.__('Si','albo-online').'<br>
					<input type="radio" id="LogAccessiNo" name="LogAccessi" value="No" '.$LOAccessiN.'>'.__('No','albo-online').'
				</td>		
			</tr>
		</table>
		</div>
	   	<div id="Conf-tab-4">
			  <table class="albo_cell">
				<tr>
					<th scope="row"><label>'.__('Ruoli Abilitati a visualizzare il pulsante per la creazione dello shortcode','albo-online').' [Albo ....]</label></th>
					<td>';
					global $wp_roles;
					$roles = $wp_roles->get_names();
					foreach($roles as $KR=>$Role){
						echo "<input type=\"checkbox\" name=\"RuoliPuls[$KR]\" value=\"$KR\"";
						if(in_array($KR,$RuoliPl))
							echo " checked ";
						echo ">$Role<br />";
					}
     // Below code will print the all list of roles.
//     print_r($roles);        
//     print_r(wp_get_current_user()->roles);
					echo '
					</td>
				</tr>		
				<tr>
					<th scope="row"><label>'.__('Ruoli Abilitati a visualizzare il pulsante per la creazione dello shortcode','albo-online').' [AlboGruppiAtti ....]</label></th>
					<td>';
					global $wp_roles;
					$roles = $wp_roles->get_names();
					foreach($roles as $KR=>$Role){
						echo "<input type=\"checkbox\" name=\"RuoliPulsG[$KR]\" value=\"$KR\"";
						if(in_array($KR,$RuoliPlG))
							echo " checked ";
						echo ">$Role<br />";
					}
     // Below code will print the all list of roles.
//     print_r($roles);        
//     print_r(wp_get_current_user()->roles);
					echo '
					</td>
				</tr>		
				<tr>
					<th scope="row"><label>'.__('Ruoli Abilitati a visualizzare il pulsante per la creazione dello shortcode','albo-online').' [AlboVisAtto ....]</label></th>
					<td>';
					global $wp_roles;
					$roles = $wp_roles->get_names();
					foreach($roles as $KR=>$Role){
						echo "<input type=\"checkbox\" name=\"RuoliPulsVA[$KR]\" value=\"$KR\"";
						if(in_array($KR,$RuoliPlVA))
							echo " checked ";
						echo ">$Role<br />";
					}
     // Below code will print the all list of roles.
//     print_r($roles);        
//     print_r(wp_get_current_user()->roles);
					echo '
					</td>
				</tr>	
			<tr>
				<th scope="row"><label for="AutoShortCode">'.__("Abilita l'inserimento automatico della visualizzazione degli atti nei Bandi di gara e contratti attraverso il MetaDato CIG",'albo-online').'</label></th>
				<td><input type="checkbox" id="AutoShortCode" name="AutoShortCode" value="Si" '.($AutoShortcode=="Si"?"checked":"").'/></td>
			</tr>
				</table>
	  	</div>		  
	   	<div id="Conf-tab-5">
			  <table class="albo_cell">
				<tr>
					<th scope="row"><label>'.__('No Responsabile','albo-online').'</label></th>
					<td>
						<input type="text" id="NoResp" name="NoResp" maxlength="255" value="'.$Testi["NoResp"].'" style="width:100%;"/>
					</td>
				</tr>	
				<tr>
					<th scope="row"><label>'.__('Certificato Pubblicazione','albo-online').'</label></th>
					<td>
						<input type="text" id="CertPub" name="CertPub" maxlength="255" value="'.$Testi["CertPub"].'" style="width:100%;"/>
					</td>
				</tr>	
			  </table>	
		</div>
	   	<div id="Conf-tab-6">
			  <table style="text-align:right;line-height:3em;">
				<tr>
					<th scope="row"><label>'.__('Responsabile Giudirico Amministrativo','albo-online').'</label></th>
					<td>'.
					ap_get_dropdown_responsabili("resp_giu_am","resp_giu_am","ElencoSoggetti","",(isset($DefaultSoggetti["AM"])?$DefaultSoggetti["AM"]:0),array("SC","DR"))
					.'</td>
				</tr>	
				<tr>
					<th scope="row"><label>'.__('Responsabile Procedimento','albo-online').'</label></th>
					<td>'.
					ap_get_dropdown_responsabili("resp_giu_rp","resp_giu_rp","ElencoSoggetti","",(isset($DefaultSoggetti["RP"])?$DefaultSoggetti["RP"]:0),"RP")
					.'	
					</td>
				</tr>	
				<tr>
					<th scope="row"><label>'.__('Responsabile Pubblicazione','albo-online').'</label></th>
					<td>'.
					ap_get_dropdown_responsabili("resp_giu_rb","resp_giu_rb","ElencoSoggetti","",(isset($DefaultSoggetti["RB"])?$DefaultSoggetti["RB"]:0),"RB")
					.'	
						
					</td>
				</tr>	
			  </table>	
		</div>
	   	<div id="Conf-tab-7">
		  <table class="albo_cell" border=1>
			<tr>
				<th scope="row">
					<label for="rest_api">'.__('Abilitazione Rest Api','albo-online').'</label>
				</th>
				<td>
					<input type="checkbox" name="rest_api" value="Si" '.$ChkRestApi.' id="rest_api"/> '.__("Selezionare questa opzione per abilitare le Rest Api per l'Albo",'albo-online').'
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="rest_api_urlest">'.__('Url da abilitare','albo-online').'</label>
				</th>
				<td>
					<input type="text" name="rest_api_urlest" value="'.$RestApiUrlEst.'" id="rest_api_urlest" size="50"/>  <br />'.__("Inserire l'Url del sito client che in cui viene interrogato l'Albo, per abilitare la visualizzazione degli allegati",'albo-online').'
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label>'.__('Endpoint per le Categorie','albo-online').'</label>
				</th>
				<td>
				'.$BaseUrlRestApi.'categorie
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label>'.__('Endpoint per gli Enti','albo-online').'</label>
				</th>
				<td>
				'.$BaseUrlRestApi.'enti
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label>'.__('Endpoint per il singolo Atto','albo-online').'</label>
				</th>
				<td>
					<table class="noalbo_cell">
						<tr>
							<td>
				'.$BaseUrlRestApi.'atto/'.__('Numero_atto/Anno_Atto oppure','albo-online').'<br />'.$BaseUrlRestApi.'atto/ID_Atto
							</td>
						</tr>
						<tr>
							<td>
				<em>es. '.$BaseUrlRestApi.'atto/1/2018  '.__("verranno restituiti i dati dell'atto n. 1 del 2018",'albo-online').'<br />
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$BaseUrlRestApi.'atto/305  '.__("verranno restituiti i dati dell'atto con ",'albo-online').'ID n° 305</em>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label>'.__('Endpoint per gli Atti','albo-online').'</label>
				</th>
				<td>
					<table class="noalbo_cell" border=1>
						<tr>
							<td colspan=3>
				'.$BaseUrlRestApi.'atti?'.__('parametro1=valore1&amp;parametro2=valore2 ...','albo-online').'
							</td>
						</tr>
						<tr>
							<td colspan=3>
				<em>es. '.$BaseUrlRestApi.'atti?stato=1  '.__('verranno restituiti i dati di tutti gli atti correnti','albo-online').'</em>
							</td>
						</tr>
						<tr>
							<th>'.__('Parametro','albo-online').'</th>
							<th>'.__('Valore di Default','albo-online').'</th>
							<th>'.__('Valori ammissibili','albo-online').'</th>
						</tr>
						<tr>
							<td>'.__('stato','albo-online').'</td>
							<td>1</td>
							<td>'.__('Numero 1 (atti correnti) o 2 (atti storico)','albo-online').'</td>
						</tr>
						<tr>
							<td>per_page</td>
							<td>10</td>
							<td> -1 '.__('per tutti gli atti dello stato','albo-online').'Numero atti per pagina > 0<br /></td>
						</tr>						
						<tr>
							<td>page</td>
							<td>1</td>
							<td>'.__('Numero pagina','albo-online').' > 0</td>
						</tr>						
						<tr>
							<td>categorie</td>
							<td>0 '.__('(Nessun filtro sulle categorie)','albo-online').'</td>
							<td>'.__('Elenco ID delle Categorie codificate separate da','albo-online').' , es. 1,5,9</td>
						</tr>						
						<tr>
							<td>ente</td>
							<td>-1 '.__('ente principale','albo-online').'</td>
							<td>'.__('Ente degli atti','albo-online').'</td>
						</tr>						
						<tr>
							<td>numero</td>
							<td>0 '.__('(Nessun filtro sul numero)','albo-online').'</td>
							<td>'.__('Numero atto','albo-online').'</td>
						</tr>						
						<tr>
							<td>anno</td>
							<td>0 '.__("(Nessun filtro sull'anno)",'albo-online').'</td>
							<td>'.__('Anno atto','albo-online').'</td>
						</tr>						
						<tr>
							<td>oggetto</td>
							<td>\'\' '.__("(Nessun filtro sull'oggetto)",'albo-online').'</td>
							<td>'.__('Oggetto atto','albo-online').'</td>
						</tr>						
						<tr>
							<td>riferimento</td>
							<td>\'\' '.__('(Nessun filtro sul riferimento)','albo-online').'</td>
							<td>'.__('Riferimento atti','albo-online').'</td>
						</tr>						
						<tr>
							<td>dadata</td>
							<td>0 '.__('(Nessun filtro sulla data di inizio)','albo-online').'</td>
							<td>'.__('Data inizio intervallo di filtro in formato gg/mm/aaaa','albo-online').'</td>
						</tr>						
						<tr>
							<td>adata</td>
							<td>0 '.__('(Nessun filtro sulla data di fine)','albo-online').'</td>
							<td>'.__('Data fine intervallo di filtro in formato gg/mm/aaaa','albo-online').'</td>
						</tr>	
					</table>
			</tr>
		  </table>		
		</div>
	</div>
	    <p class="submit">
	        <input type="submit" name="AlboPretorio_submit_button" value="'.__('Salva Modifiche','albo-online').'" />
	    </p> 
	    </form>
	    </div>';
		if(get_option('opt_AP_AnnoProgressivo')!=date("Y")){
			echo '<div style="border: medium groove Blue;margin-top:10px;margin-right:250px;">
					<div style="float:none;width:200px;margin-left:auto;margin-right:auto;">
						<form id="agg_anno_progressivo" method="post" action="?page=configAlboP">
						<input type="hidden" name="action" value="setta-anno" />
	  					<input type="hidden" name="confAP" value="'.wp_create_nonce('configurazionealbo').'" />
						<input type="submit" name="submit" id="submit" class="button" value="'.__('Aggiorna Anno Albo ed Azzera numero Progressivo','albo-online').'"  />
						</form>
					</div>
				  </div>';
		}

	}
	function define_tables() {		
		global $wpdb,$table_prefix;
		
		// add database pointer 
		$wpdb->table_name_Atti = $table_prefix . "albopretorio_atti";
		$wpdb->table_name_Attimeta = $table_prefix . "albopretorio_attimeta";
		$wpdb->table_name_Categorie = $table_prefix . "albopretorio_categorie";
		$wpdb->table_name_Allegati = $table_prefix . "albopretorio_allegati";
		$wpdb->table_name_Log=$table_prefix . "albopretorio_log";
		$wpdb->table_name_RespProc=$table_prefix . "albopretorio_resprocedura";
		$wpdb->table_name_Enti=$table_prefix . "albopretorio_enti";
		$wpdb->table_name_UO=$table_prefix . "albopretorio_unitaorganizzative";
	}

	function activate() {
	global $wpdb;
	if ( ! function_exists( 'get_plugins' ) )
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );	
/**
* Impostazione Versione
* 
*/
	$PData = get_plugin_data( __FILE__ );
	$PVer = $PData['Version'];	
	update_option('opt_AP_Versione', $PVer);
	delete_option( 'alboonline-notice-dismissed' );
/**
* Impostazione Permessi Cartelle di servizio e files
* 
*/
	$Dir=str_replace("\\","/",WP_CONTENT_DIR.'/AlboOnLine');
	if(!is_dir($Dir)){
		mkdir($Dir, 0744,TRUE);		
	}
	$Dir=str_replace("\\","/",WP_CONTENT_DIR.'/AlboOnLine/BackupDatiAlbo');
	if(!is_dir($Dir)){
		mkdir($Dir, 0744,TRUE);
	}
	$Dir=str_replace("\\","/",WP_CONTENT_DIR.'/AlboOnLine/OblioDatiAlbo');
	if(!is_dir($Dir)){
		mkdir($Dir, 0744,TRUE);
	}
	if (file_exists(Albo_DIR."/js/gencode.php")){
		chmod(Albo_DIR."/js/gencode.php", 0755);
	}
	if (file_exists(Albo_DIR."/js/buttonEditorGruppiAlbo.php")){
		chmod(Albo_DIR."/js/buttonEditorGruppiAlbo.php", 0755);
	}
	if (file_exists(Albo_DIR."/js/buttonEditorVisAtto.php")){
		chmod(Albo_DIR."/js/buttonEditorVisAtto.php", 0755);
	}
/**
* Impostazione Ruoli
* 
*/
		$role = get_role( 'administrator' );

        /* Aggiunta dei ruoli all'Amministratore */
        if ( !empty( $role ) ) {
            $role->add_cap( 'admin_albo' );
            $role->add_cap( 'editore_atti_albo' );
            $role->add_cap( 'gest_atti_albo' );
        }

        /* Creazione ruolo di Amministratore */
        
        $result=add_role(
            'amministratore_albo',
            'Amministratore Albo',
            array(
				'read' => true, 
                'admin_albo' => true,
                'editore_atti_albo' => true,
                'gest_atti_albo' => true));
        if ( null === $result ) {
        	$role = get_role( 'amministratore_albo' );
	        if ( !empty( $role ) ) {
	            $role->add_cap( 'admin_albo' );
	            $role->add_cap( 'editore_atti_albo' );
	            $role->add_cap( 'gest_atti_albo' );
	        }		
	    }
       /* Creazione del ruolo di Editore */
        $result=add_role(
			'editore_albo',
            'Editore Albo',
            array('read' => true,
     			  'editore_atti_albo' => true,
	              'gest_atti_albo' => true));
       if ( null === $result ) {
        	$role = get_role( 'editore_albo' );
	        if ( !empty( $role ) ) {
	            $role->add_cap( 'editore_atti_albo' );
	            $role->add_cap( 'gest_atti_albo' );
	        }		
	    }                
        /* Creazione del ruolo di Redattore */
        $result=add_role(
			'gestore_albo',
            'Redattore Albo',
            array('read' => true,
				  'gest_atti_albo' => true));
       if ( null === $result ) {
        	$role = get_role( 'gestore_albo' );
	        if ( !empty( $role ) ) {
	            $role->add_cap( 'gest_atti_albo' );
	        }		
	    }     		
/**
* Creazione Opzioni
* 
*/
//Impostazione Opzioni Plugin
	if(get_option('opt_AP_DefaultSoggetti')  == '' || !get_option('opt_AP_DefaultSoggetti')){
		$DefaultSoggetti=array("RP"=>0,"RB"=>0,"AM"=>0);
		add_option('opt_AP_DefaultSoggetti',json_encode($opt_AP_DefaultSoggetti));
		}
	if(get_option('opt_AP_ColonneFE')  == '' || !get_option('opt_AP_ColonneFE')){
		$FEColsOption=array("Ente"=>0,"Riferimento"=>0,"Oggetto"=>1, "Validita"=>1,
					  "Categoria"=>1,"Note"=>0,"RespProc"=>0, "DataOblio"=>0);
		add_option('opt_AP_ColonneFE',json_encode($FEColsOption));
		}
	if(get_option('opt_AP_Versione')  == '' || !get_option('opt_AP_Versione')){
			add_option('opt_AP_Versione', '0');
		}
	if(get_option('opt_AP_RestApi')  == '' || !get_option('opt_AP_RestApi')){
			add_option('opt_AP_RestApi', 'No');
		}
	if(!get_option('opt_AP_RestApi_UrlEst')){
			add_option('opt_AP_RestApi_UrlEst', '');
		}
	if (!get_option( 'opt_AP_FolderUploadMeseAnno' )) {
			add_option('opt_AP_FolderUploadMeseAnno', '');
		}
	if(get_option('opt_AP_TipidiFiles')  == '' || !get_option('opt_AP_TipidiFiles')){
		$TipidiFiles=array();
		$TipidiFiles["ndf"]= array("Descrizione"=>__('Tipo file non definito','albo-online'),"Icona"=>Albo_URL."img/notipofile.png","Verifica"=>"");
		$TipidiFiles["pdf"]= array("Descrizione"=>__('File Pdf','albo-online'),"Icona"=>Albo_URL."img/Pdf.png","Verifica"=>"");
		$TipidiFiles["p7m"]= array("Descrizione"=>__('File firmato digitalmente','albo-online'),"Icona"=>Albo_URL."img/firmato.png","Verifica"=>htmlspecialchars("<a href=\"http://vol.ca.notariato.it/\" onclick=\"window.open(this.href);return false;\">".__('Verifica firma con servizio fornito da Consiglio Nazionale del Notariato','albo-online')."</a>"));
		add_option('opt_AP_TipidiFiles', $TipidiFiles);
	}
	if(get_option('opt_AP_AnnoProgressivo')  == '' || !get_option('opt_AP_AnnoProgressivo')){
		add_option('opt_AP_AnnoProgressivo', ''.date("Y").'');
	}
	if(get_option('opt_AP_NumeroProgressivo')  == '' || !get_option('opt_AP_NumeroProgressivo')){
		add_option('opt_AP_NumeroProgressivo', '1');
	}
	if(get_option('opt_AP_FolderUpload') == '' || !get_option('opt_AP_FolderUpload') ){
		if(!is_dir(AP_BASE_DIR.'AllegatiAttiAlboPretorio')){   
			mkdir(AP_BASE_DIR.'AllegatiAttiAlboPretorio', 0755);
		}
		add_option('opt_AP_FolderUpload', 'AllegatiAttiAlboPretorio');
	}else{
		if (get_option('opt_AP_FolderUpload')!='AllegatiAttiAlboPretorio')
			update_option('opt_AP_FolderUpload', 'AllegatiAttiAlboPretorio');
	}
	ap_NoIndexNoDirectLink(AP_BASE_DIR.'AllegatiAttiAlboPretorio');	
	if(get_option('opt_AP_VisualizzaEnte') == '' || !get_option('opt_AP_VisualizzaEnte')){
		add_option('opt_AP_VisualizzaEnte', 'Si');
	}
	if(get_option('opt_AP_LivelloTitoloEnte') == '' || !get_option('opt_AP_LivelloTitoloEnte')){
		add_option('opt_AP_LivelloTitoloEnte', 'h2');
	}
	if(get_option('opt_AP_LivelloTitoloPagina') == '' || !get_option('opt_AP_LivelloTitoloPagina')){
		add_option('opt_AP_LivelloTitoloPagina', 'h3');
	}
	if(get_option('opt_AP_LivelloTitoloFiltri') == '' || !get_option('opt_AP_LivelloTitoloFiltri')){
		add_option('opt_AP_LivelloTitoloFiltri', 'h4');
	}
	if(get_option('opt_AP_ColoreAnnullati') == '' || !get_option('opt_AP_ColoreAnnullati')){
		add_option('opt_AP_ColoreAnnullati', '#FFCFBD');
	}
	if(get_option('opt_AP_ColorePari') == '' || !get_option('opt_AP_ColorePari')){
		add_option('opt_AP_ColorePari', '#ECECEC');
	}
	if(get_option('opt_AP_ColoreDispari') == '' || !get_option('opt_AP_ColoreDispari')){
		add_option('opt_AP_ColoreDispari', '#FFF');
	}
	if(get_option('opt_AP_LogOp') == '' || !get_option('opt_AP_LogOp')){
		add_option('opt_AP_LogOp', 'Si');
	}
	if(get_option('opt_AP_LogAc') == '' || !get_option('opt_AP_LogAc')){
		add_option('opt_AP_LogAc', 'Si');
	}
	if(get_option('opt_AP_GiorniOblio') == '' || !get_option('opt_AP_GiorniOblio')){
		add_option('opt_AP_GiorniOblio', '1825');
	}
	if(get_option('opt_AP_PAttiCor')  == '' || !get_option('opt_AP_PAttiCor')){
		add_option('opt_AP_PAttiCor',1);
	}
	if(get_option('opt_AP_PAttiSto')  == '' || !get_option('opt_AP_PAttiSto')){
		add_option('opt_AP_PAttiSto',1);
	}
	if(get_option('opt_AP_PAtto')  == '' || !get_option('opt_AP_PAtto')){
		add_option('opt_AP_PAtto',1);
	}
	if(get_option('opt_AP_RuoliPuls')  == '' || !get_option('opt_AP_RuoliPuls')){
		add_option('opt_AP_RuoliPuls',"administrator,editor,author,amministratore_albo");
	}
	if(get_option('opt_AP_RuoliPulsGruppi')  == '' || !get_option('opt_AP_RuoliPulsGruppi')){
		add_option('opt_AP_RuoliPulsGruppi',"administrator,editor,author,amministratore_albo");
	}
	if(get_option('opt_AP_RuoliPulsVisualizzaAtto')  == '' || !get_option('opt_AP_RuoliPulsVisualizzaAtto')){
		add_option('opt_AP_RuoliPulsVisualizzaAtto',"administrator,editor,author,amministratore_albo");
	}
	if(get_option('opt_AP_AutoShortcode')  == '' || !get_option('opt_AP_AutoShortcode')){
		add_option('opt_AP_AutoShortcode', '1');
	}
	if(get_option('opt_AP_OldInterfaccia')  == '' || !get_option('opt_AP_OldInterfaccia')){
		add_option('opt_AP_OldInterfaccia', 'Si');
	}
	if(get_option('opt_AP_UpCSSNewInterface')  == '' || !get_option('opt_AP_UpCSSNewInterface')){
		add_option('opt_AP_UpCSSNewInterface', 'Si');
	}
	if(get_option('opt_AP_TabResp')  == '' || !get_option('opt_AP_TabResp')){
		$Default='[{"ID":"RP","Funzione":"'.__('Responsabile Procedimento','albo-online').'","Display":"Si"},{"ID":"OP","Funzione":"'.__('Gestore procedura','albo-online').'","Display":"Si"},{"ID":"SC","Funzione":"'.__('Segretario Comunale','albo-online').'","Display":"No"},{"ID":"RB","Funzione":"'.__('Responsabile Pubblicazione','albo-online').'","Display":"No"},{"ID":"DR","Funzione":"'.__('Direttore dei Servizi e Amministrativi','albo-online').'","Display":"No"}]';
		update_option('opt_AP_TabResp',$Default ); 
	}	
/**
* Eliminazione Opzioni
* 
*/
		delete_option('opt_AP_EffettiTesto');
		delete_option('opt_AP_EffettiCSS3');
		delete_option( 'opt_AP_stileTableFE' );  

		ap_CreaTabella($wpdb->table_name_Atti);
		ap_CreaTabella($wpdb->table_name_Categorie);
		ap_CreaTabella($wpdb->table_name_Allegati);
		ap_CreaTabella($wpdb->table_name_Log);
		ap_CreaTabella($wpdb->table_name_RespProc);
		ap_CreaTabella($wpdb->table_name_Enti);		
		ap_CreaTabella($wpdb->table_name_Attimeta);
		ap_CreaTabella($wpdb->table_name_UO);
/*************************************************************************************
** Area riservata per l'aggiunta di nuovi campi in una delle tabelle dell' albo ******
*************************************************************************************/
 		if(ap_get_ente_me() == '' || !ap_get_ente(0)){
			ap_create_ente_me();
		}         
		if (!ap_existFieldInTable($wpdb->table_name_RespProc, "Funzione")){
			ap_AggiungiCampoTabella($wpdb->table_name_RespProc, "Funzione", " CHAR(8) DEFAULT 'RP'");				
		}		
		if (!ap_existFieldInTable($wpdb->table_name_Allegati, "TipoFile")){
			ap_AggiungiCampoTabella($wpdb->table_name_Allegati, "TipoFile", " VARCHAR(6) DEFAULT ''");				
		}	
		if (!ap_existFieldInTable($wpdb->table_name_Atti, "Soggetti")){
			ap_AggiungiCampoTabella($wpdb->table_name_Atti, "Soggetti", " VARCHAR(100) NOT NULL");				
		}
		if (!ap_existFieldInTable($wpdb->table_name_Atti, "RespProc")){
			ap_AggiungiCampoTabella($wpdb->table_name_Atti, "RespProc", " INT NOT NULL");				
		}
		if (!ap_existFieldInTable($wpdb->table_name_Atti, "DataOblio")){
			ap_AggiungiCampoTabella($wpdb->table_name_Atti, "DataOblio", " date NOT NULL DEFAULT '0000-00-00'");
			ap_SetDefaultDataScadenza();
		}
		if (!ap_existFieldInTable($wpdb->table_name_Atti, "MotivoAnnullamento")){
			ap_AggiungiCampoTabella($wpdb->table_name_Atti, "MotivoAnnullamento", " varchar(100) default ''");
		}
		if (!ap_existFieldInTable($wpdb->table_name_Atti, "Ente")){
			ap_AggiungiCampoTabella($wpdb->table_name_Atti, "Ente", " INT NOT NULL default 0");
		}

		if (!ap_existFieldInTable($wpdb->table_name_Atti, "IdUnitaOrganizzativa")){
			ap_AggiungiCampoTabella($wpdb->table_name_Atti, "IdUnitaOrganizzativa", " INT NOT NULL default 0");
		}
		if (!ap_existFieldInTable($wpdb->table_name_Atti, "Richiedente")){
			ap_AggiungiCampoTabella($wpdb->table_name_Atti, "Richiedente", " varchar(100) NOT NULL default ''");
		}
		if (!ap_existFieldInTable($wpdb->table_name_Allegati, "DocIntegrale")){
			ap_AggiungiCampoTabella($wpdb->table_name_Allegati, "DocIntegrale", " tinyint(1) NOT NULL DEFAULT '1'");
		}
		if (!ap_existFieldInTable($wpdb->table_name_Allegati, "Impronta")){
			ap_AggiungiCampoTabella($wpdb->table_name_Allegati, "Impronta", " CHAR(64) NOT NULL");
		}
		if (!ap_existFieldInTable($wpdb->table_name_Allegati, "Natura")){
			ap_AggiungiCampoTabella($wpdb->table_name_Allegati, "Natura", " CHAR(1) NOT NULL default 'A'");
		}

		if (strtolower(ap_typeFieldInTable($wpdb->table_name_Atti,"Riferimento"))!="varchar(255)"){
			ap_ModificaTipoCampo($wpdb->table_name_Atti, "Riferimento", "varchar(255)");
		}
		if (strtolower(ap_typeFieldInTable($wpdb->table_name_Atti,"Oggetto"))!="text"){
			ap_ModificaTipoCampo($wpdb->table_name_Atti, "Oggetto", "TEXT");
		}
		if (strtolower(ap_typeFieldInTable($wpdb->table_name_Atti,"MotivoAnnullamento"))!="varchar(255)"){
			ap_ModificaTipoCampo($wpdb->table_name_Atti, "MotivoAnnullamento", "varchar(255)");
		}
		if (strtolower(ap_typeFieldInTable($wpdb->table_name_Atti,"Informazioni"))!="text"){
			ap_ModificaTipoCampo($wpdb->table_name_Atti, "Informazioni", "TEXT");
		}
		if (strtolower(ap_typeFieldInTable($wpdb->table_name_Atti,"Riferimento"))!="text"){
			ap_ModificaTipoCampo($wpdb->table_name_Atti, "Riferimento", "TEXT");
		}
		if (strtolower(ap_typeFieldInTable($wpdb->table_name_Atti,"MotivoAnnullamento"))!="text"){
			ap_ModificaTipoCampo($wpdb->table_name_Atti, "MotivoAnnullamento", "TEXT");
		}

//		ap_ModificaParametriCampo($Tabella, $Campo, $Tipo $Parametro)
		$par=ap_EstraiParametriCampo($wpdb->table_name_Atti,"Riferimento");
		if(strtolower($par["Null"])=="yes")
			ap_ModificaParametriCampo($wpdb->table_name_Atti, "Riferimento",$par["Type"] ,"NOT NULL");
		$par=ap_EstraiParametriCampo($wpdb->table_name_Atti,"Oggetto");
		if(strtolower($par["Null"])=="yes")
			ap_ModificaParametriCampo($wpdb->table_name_Atti, "Oggetto",$par["Type"] ,"NOT NULL");    
		ap_manutenzioneLogVisualizzazione();
	}  	 
	
	
	function deactivate() {
	    if ( ! current_user_can( 'activate_plugins' ) )
	        return;
	    $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
	    check_admin_referer( "deactivate-plugin_{$plugin}" );
		flush_rewrite_rules();	
		remove_shortcode('Albo');
	}

	function update_AlboPretorio_settings(){
	    if(isset($_POST['AlboPretorio_submit_button']) And $_POST['AlboPretorio_submit_button'] == __('Salva Modifiche','albo-online')){
	    	if (!isset($_POST['confAP'])) {
	    		header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page=configAlboP&update=false'); 		
	    	}
			if (!wp_verify_nonce($_POST['confAP'],'configurazionealbo')){
				header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page=configAlboP&update=false'); 
			} 		
		    ap_set_ente_me(strip_tags($_POST['c_Ente']));
			if (isset($_POST['c_VEnte']) And $_POST['c_VEnte']=='Si')
			    update_option('opt_AP_VisualizzaEnte','Si' );
			else
				update_option('opt_AP_VisualizzaEnte','No' );
			if (isset($_POST['defEnte']))
			    update_option('opt_AP_DefaultEnte',$_POST['defEnte'] );
			else
				update_option('opt_AP_DefaultEnte',0 );
			if (isset($_POST['progressivo']))
			    update_option('opt_AP_NumeroProgressivo',(int)$_POST['progressivo'] );
			if(isset($_POST['RuoliPuls'])){
				$StRuoli=implode(",",$_POST['RuoliPuls']);
				update_option('opt_AP_RuoliPuls',$StRuoli);
			}
			if(isset($_POST['RuoliPulsG'])){
				$StRuoliG=implode(",",$_POST['RuoliPulsG']);
				update_option('opt_AP_RuoliPulsGruppi',$StRuoliG);
			}
			if(isset($_POST['RuoliPulsVA'])){
				$StRuoliVA=implode(",",$_POST['RuoliPulsVA']);
				update_option('opt_AP_RuoliPulsVisualizzaAtto',$StRuoliVA);
			}
		    update_option('opt_AP_Ente',$_POST['c_Ente'] );
		    update_option('opt_AP_AnnoProgressivo',$_POST['c_AnnoProgressivo'] );
		    update_option('opt_AP_LivelloTitoloPagina',$_POST['c_LTP'] );
		    update_option('opt_AP_LivelloTitoloEnte',$_POST['c_LTE'] );
		    update_option('opt_AP_LivelloTitoloFiltri',$_POST['c_LTF'] );
			update_option('opt_AP_ColoreAnnullati',strip_tags($_POST['color']) );
			update_option('opt_AP_ColorePari',strip_tags($_POST['colorp']) );
			update_option('opt_AP_ColoreDispari',strip_tags($_POST['colord']) );
			update_option('opt_AP_LogOp', $_POST['LogOperazioni']);
			update_option('opt_AP_LogAc', $_POST['LogAccessi']);
			update_option('opt_AP_PAttiCor', $_POST['P_AttiCor']);
			update_option('opt_AP_PAttiSto', $_POST['P_AttiSto']);
			update_option('opt_AP_PAtto', $_POST['P_Atto']);
			update_option('opt_AP_AutoShortcode',(isset($_POST['AutoShortCode'])?$_POST['AutoShortCode']:0));
			update_option('opt_AP_OldInterfaccia',(isset($_POST['visoldstyle'])?$_POST['visoldstyle']:0));
			update_option('opt_AP_UpCSSNewInterface',(isset($_POST['uploadCSSNI'])?$_POST['uploadCSSNI']:0));
		  	$FEColsOption=array("Data"=>(isset($_POST['Data'])?1:0),
		  					  "Ente"=>(isset($_POST['Ente'])?1:0),
		  					  "Riferimento"=>(isset($_POST['Riferimento'])?1:0),
		  					  "Oggetto"=>(isset($_POST['Oggetto'])?1:0),
		  					  "Validita"=>(isset($_POST['Validita'])?1:0),
		  					  "Categoria"=>(isset($_POST['Categoria'])?1:0),
							  "Note"=>(isset($_POST['Note'])?1:0),
							  "DataOblio"=>(isset($_POST['DataOblio'])?1:0)
			);
			update_option('opt_AP_ColonneFE', json_encode($FEColsOption)); 
			$Testi=array("NoResp"=> filter_input(INPUT_POST,"NoResp"),
	  	                "CertPub"=>filter_input(INPUT_POST,"CertPub"));
			update_option('opt_AP_Testi', json_encode($Testi)); 
			update_option('opt_AP_IconaDocumenti', filter_input(INPUT_POST,"imgDocumenti")); 
		  	$DefaultSoggetti=array("AM"=>(isset($_POST['resp_giu_am'])?$_POST['resp_giu_am']:0),
		  					  	   "RP"=>(isset($_POST['resp_giu_rp'])?$_POST['resp_giu_rp']:0),
		  					       "RB"=>(isset($_POST['resp_giu_rb'])?$_POST['resp_giu_rb']:0));
			update_option('opt_AP_DefaultSoggetti', json_encode($DefaultSoggetti)); 
	  		if(null !== get_option('opt_AP_RestApi'))
	  			$OldRestApi= get_option('opt_AP_RestApi');
	  		else
	  			$OldRestApi=NULL;
			if(isset($_POST['rest_api'])){
				update_option('opt_AP_RestApi', $_POST['rest_api']); 
		  		if(null !== get_option('opt_AP_RestApi_UrlEst'))
		  			$OldUrlEstRestApi= get_option('opt_AP_RestApi_UrlEst');
		  		else
		  			$OldUrlEstRestApi="";
				update_option('opt_AP_RestApi_UrlEst', $_POST['rest_api_urlest']);
		  		if($_POST['rest_api_urlest']!=$OldUrlEstRestApi ||
		  		   $_POST['rest_api']!=$OldRestApi){
		  			ap_NoIndexNoDirectLink(AP_BASE_DIR.'AllegatiAttiAlboPretorio');
		  		}
		  	}else{
		  		update_option('opt_AP_RestApi', ""); 
		  	}
			header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page=configAlboP&update=true'); 
  		}
	}
}
	global $AP_OnLine;
	$AP_OnLine = new AlboPretorio();
}
?>
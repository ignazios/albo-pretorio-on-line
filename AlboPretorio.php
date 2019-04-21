<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Albo Pretorio On line
 * Plugin URI:        https://it.wordpress.org/plugins/albo-pretorio-on-line/
 * Description:       Plugin utilizzato per la pubblicazione degli atti da inserire nell'albo pretorio dell'ente.
 * Version:           4.2
 * Author:            Ignazio Scimone
 * Author URI:        eduva.org
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       albo-pretorio-on-line
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
				$Stato="ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione &egrave; stata annullata";
				break;
			}
			if (!wp_verify_nonce($_REQUEST['rigenera'],'rigeneraoblio')){
				$Stato="ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione &egrave; stata annullata";
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
		if ( ! function_exists( 'get_plugins' ) )
	 		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	    $plugins = get_plugins( "/".plugin_basename( dirname( __FILE__ ) ) );
    	$plugin_nome = basename( ( __FILE__ ) );
	    $this->version=$plugins[$plugin_nome]['Version'];
		// Inizializzazioni
		$this->define_tables();
		$this->load_dependencies();
		$this->plugin_name = plugin_basename(__FILE__);		

		// Hook per attivazione/disattivazione plugin
		register_activation_hook(  __FILE__, array($this, 'activate'));
		register_deactivation_hook(  __FILE__, array($this, 'deactivate') );	

		// Hook disinstallazione
		register_uninstall_hook(  __FILE__, array($this, 'uninstall') );

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
		$RestApi=get_option('opt_AP_RestApi');
	  	if($RestApi=="Si"){
			add_action( 'rest_api_init', array($this, 'Reg_rest_api_route'));
		}
		if (get_option( 'opt_AP_Versione' ) != $this->version) {
			$this->activate();
		} 
	}
	function Reg_rest_api_route(){
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
								return is_numeric( $param )&&($param>=0);						   			}),
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
	function rest_api_atto_get($request){
		$Atto=array();
		$NumeroAtto=$request->get_param("num");
		$AnnoAtto=$request->get_param("anno");
		$risultato=ap_get_all_atti(0,$NumeroAtto,$AnnoAtto);
		if(count($risultato)==0){
			return new WP_Error( 'no_atto', 'Nessun atto trovato con questi parametri', array( 'status' => 404 ) );
  		}
		$risultato=$risultato[0];
		$IdAtto=$risultato->IdAtto;
		$DatiAtto=ap_get_atto($IdAtto);
		$DatiCategoria=array();
		$DatiEnte=array();
		$DatiSoggetti=array();
		$Allegati=array();
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
					"Funzione"	=>ap_get_Funzione_Responsabile($Soggetto->Funzione,"Descrizione"));
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
				if(isset($allegato->TipoFile) and 
					$allegato->TipoFile!="" and 
					ap_isExtensioType($allegato->TipoFile)){
					$Estensione=ap_ExtensionType($allegato->TipoFile);
					$Icona=$TipidiFiles[$Estensione]['Icona'];
					$TipoFile=$TipidiFiles[$Estensione]['Descrizione'];
				}else{
					$Icona=$TipidiFiles[strtolower($Estensione)]['Icona'];
					$TipoFile=$TipidiFiles[strtolower($Estensione)]['Descrizione'];
				}
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
			$Atto["DataOblio"]			=$D_Atto->DataOblio;
			$Atto["Soggetti"]			=$DatiSoggetti;
			$Atto["Allegati"]			=$Allegati;
		}
		return new WP_REST_Response($Atto, 200 );
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
			return new WP_REST_Response("Errore:Pagina visualizzazione atto non impostata",200);
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

	if ($Pag==0){
		$Da=0;
		$A=$N_A_pp;
	}else{
		$Da=($Pag-1)*$N_A_pp;
		$A=$N_A_pp;
	}
	$TotAtti=ap_get_all_atti($Stato,$Numero,$Anno,$Categorie,$Oggetto,$Dadata,$Adata,'',0,0,true,true,$Riferimento,$Ente);
	$ListaAtti=ap_get_all_atti($Stato,$Numero,$Anno,$Categorie,$Oggetto,$Dadata,$Adata,'Anno DESC,Numero DESC',$Da,$A,false,true,$Riferimento,$Ente); 			
			
	$Npag=(int)($TotAtti/$N_A_pp);
	if ($TotAtti%$N_A_pp>0){
		$Npag++;
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
		$Parametri=array('meta'=>"CIG",'valore'=>$Cig,'titolo' => 'Atti Albo on line di riferimento');
		$OldInterfaccia=get_option('opt_AP_OldInterfaccia');
		if($OldInterfaccia=="Si"){
			require_once ( dirname (__FILE__) . '/admin/gruppiatti.php' );		
		}else{
			require_once ( dirname (__FILE__) . '/admin/gruppiatti_new.php' );
		}		
		return $content.$ret;
	}
	function Gestione_Link(){
		if(isset($_REQUEST['action'])){
			switch ($_REQUEST['action']){
			case "dwnalle":
//			var_dump($_SERVER);wp_die();
				if(!isset($_SERVER["HTTP_REFERER"])){
					wp_die("Oooooo!<br />
					        Stai tentando di fare il furbo!<br />
					        Non puoi accedere a questo file direttamente.");
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
	    if(strpos($hook_suffix,"albo-pretorio")===false)
			return;
	?>
<script type='text/javascript'>
	var myajaxsec = '<?php echo wp_create_nonce('adminsecretAlboOnLine');?>'
</script>
	<?php			
		$path=plugins_url('', __FILE__ );
	    wp_enqueue_script('jquery');
	    wp_enqueue_script('jquery-ui-core');
	    wp_enqueue_script('jquery-ui-tabs', '', array('jquery'));
	    wp_enqueue_script('jquery-ui-dialog', '', array('jquery'));    
		wp_enqueue_script( 'jquery-ui-datepicker', '', array('jquery'));
		wp_enqueue_script( 'wp-color-picker', '', array('jquery'));
	    wp_enqueue_script( 'my-admin-fields', $path.'/js/Fields.js');
	    wp_enqueue_script( 'my-admin', $path.'/js/Albo.admin.js');
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'jquery.ui.theme', $path.'/css/jquery-ui-custom.css');	
		wp_register_style('AdminAlbo', $path.'/css/styleAdmin.css');
        wp_enqueue_style( 'AdminAlbo');
		if($hook_suffix=="albo-pretorio_page_tipifiles" or $hook_suffix=="albo-pretorio_page_configAlboP"){
			wp_enqueue_media();
			wp_register_script('uploader_tipi_files', $path.'/js/Uploader.js', array('jquery'));
			wp_enqueue_script( 'uploader_tipi_files');
		}
		if($hook_suffix=="albo-pretorio_page_tabelle"){
			wp_enqueue_script( 'jquery-ui-tooltip', '', array('jquery'));
			wp_enqueue_script( "Albo_appendGrid", $path . '/plugin/appendGrid/jquery.appendGrid-1.7.1.js');
			wp_enqueue_style("Albo_appendGrid", $path . '/plugin/appendGrid/jquery.appendGrid-1.7.1.css');
			wp_enqueue_style("Albo_ui_tema", $path. '/plugin/appendGrid/jquery-ui.theme.min.css');
//			wp_enqueue_style( "Albo_ui", $path . '/css/jquery-ui.css' );
			wp_enqueue_style("Albo_ui_structure_table", $path.'/plugin/appendGrid/jquery-ui.structure.min.css');
			wp_enqueue_script( 'my-admin_grid', $path.'/js/Albo.admin.grid.js');
		}		
		if($hook_suffix=="albo-pretorio_page_atti" And isset($_GET['action']) And $_GET['action']=='UpAllegati'){
		wp_register_style('AdminAlboMultiUpload', $path.'/css/stylemultiupload.css');
        wp_enqueue_style( 'AdminAlboMultiUpload');
			}
	}

	function CreaStatistiche($IdAtto,$Oggetto){
		$righeVisiteAtto=ap_get_Stat_Visite($IdAtto);
		$righeVisiteDownload=ap_get_Stat_Download($IdAtto);
		$HtmlTesto='';
		if ($Oggetto==5){
			$HtmlTesto='
				<h3>Totale Visite Atto '.ap_get_Stat_Num_log($IdAtto,5).'</h3>
				<table class="widefat">
				    <thead>
					<tr>
						<th style="font-size:1.2em;">Data</th>
						<th style="font-size:1.2em;">Numero Visite</th>
					</tr>
				    </thead>
				    <tbody>';
			foreach ($righeVisiteAtto as $riga) {
				$HtmlTesto.= '<tr >
							<td >'.ap_VisualizzaData($riga->Data).'</td>
							<td >'.$riga->Accessi.'</td>
						</tr>';
				}
			$HtmlTesto.= '    </tbody>
				</table>';
		}else{
			$HtmlTesto.='
				<h3>Totale Download Allegati '.ap_get_Stat_Num_log($IdAtto,6).'</h3>
				<table class="widefat">
				    <thead>
					<tr>
						<th style="font-size:1.2em;">Data</th>
						<th style="font-size:1.2em;">Nome Allegato</th>
						<th style="font-size:1.2em;">File</th>
						<th style="font-size:1.2em;">Numero Download</th>
					</tr>
				    </thead>
				    <tbody>';
			foreach ($righeVisiteDownload as $riga) {
				$HtmlTesto.= '<tr >
							<td >'.ap_VisualizzaData($riga->Data).'</td>
							<td >'.$riga->TitoloAllegato.'</td>
							<td >'.$riga->Allegato.'</td>
							<td >'.$riga->Accessi.'</td>
						</tr>';
				}
			$HtmlTesto.= '    </tbody>
				</table>';
		}
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
	//	echo $Tipo;
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
			<table class="widefat">
			    <thead>
				<tr>
					<th style="font-size:1.2em;">Data</th>
					<th style="font-size:1.2em;">Operazione</th>
					<th style="font-size:1.2em;">Informazioni</th>
				</tr>
			    </thead>
			    <tbody>';
		$Operazione="";
		foreach ($righe as $riga) {
			switch ($riga->TipoOperazione){
			 	case 1:
			 		$Operazione="Inserimento";
			 		break;
			 	case 2:
			 		$Operazione="Modifica";
					break;
			 	case 3:
			 		$Operazione="Cancellazione";
					break;
			 	case 4:
			 		$Operazione="Approvazione";
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
  		add_menu_page('Panoramica', 'Albo Pretorio', 'gest_atti_albo', 'Albo_Pretorio',array( 'AlboPretorio','show_menu'),Albo_URL."img/logo.png");
		$atti_page=add_submenu_page( 'Albo_Pretorio', 'Atti', 'Atti', 'gest_atti_albo', 'atti', array( 'AlboPretorio','show_menu'));
		$categorie_page=add_submenu_page( 'Albo_Pretorio', 'Categorie', 'Categorie', 'gest_atti_albo', 'categorie', array( 'AlboPretorio', 'show_menu'));
		$enti=add_submenu_page( 'Albo_Pretorio', 'Enti', 'Enti', 'editore_atti_albo', 'enti', array('AlboPretorio', 'show_menu'));
		$responsabili_page=add_submenu_page( 'Albo_Pretorio', 'Soggetti', 'Soggetti', 'editore_atti_albo', 'soggetti', array( 'AlboPretorio','show_menu'));
		$tipifiles=add_submenu_page( 'Albo_Pretorio', 'Tipi di files', 'Tipi di Files', 'admin_albo', 'tipifiles', array( 'AlboPretorio','show_menu'));
		$tipifiles=add_submenu_page( 'Albo_Pretorio', 'Tabelle', 'Tabelle', 'admin_albo', 'tabelle', array( 'AlboPretorio','show_menu'));
		$parametri_page=add_submenu_page( 'Albo_Pretorio', 'Generale', 'Parametri', 'admin_albo', 'configAlboP', array( 'AlboPretorio','show_menu'));
		$permessi=add_submenu_page( 'Albo_Pretorio', 'Permessi', 'Permessi', 'admin_albo', 'permessiAlboP', array('AlboPretorio', 'show_menu'));
		$utility=add_submenu_page( 'Albo_Pretorio', 'Utility', 'Utility', 'admin_albo', 'utilityAlboP', array('AlboPretorio', 'show_menu'));		
//		$testrestapi=add_submenu_page( 'Albo_Pretorio', 'Rest API', 'Rest API', 'admin_albo', 'test_rest_api', array('AlboPretorio', 'show_menu'));		
		add_action( 'admin_head-'. $atti_page, array( 'AlboPretorio','ap_head' ));
/*		$utility=add_submenu_page( 'Albo_Pretorio', 'REST-API', 'Rest-API', 'admin_albo', 'RESTAlboP', array('AlboPretorio', 'show_menu'));		
*/
		add_action( "load-$atti_page", array('AlboPretorio', 'screen_option'));

}
	static function screen_option() {
		if(!isset($_GET['action'])){
			$args=array('label'   => 'Atti per pagina',
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
		if($OldInterfaccia=="Si"){
			require_once ( dirname (__FILE__) . '/admin/frontend.php' );
		}else{
			require_once ( dirname (__FILE__) . '/admin/frontend_new.php' );
		}
		return $ret;
	}
	function VisualizzaGruppiAtti($Parametri){
		if(get_option('opt_AP_AutoShortcode'))
			return;
		$ret="";
		$Parametri=shortcode_atts(array(
			'titolo' => 'Atti Albo on line di riferimento',
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
			'titolo' => 'Atto Albo on line',
			'numero' => '',
			'anno' => '',
		), $Parametri,"AlboAtto");
		$OldInterfaccia=get_option('opt_AP_OldInterfaccia');
		if($OldInterfaccia=="Si"){
			require_once ( dirname (__FILE__) . '/admin/visatto.php' );
		}else{
			require_once ( dirname (__FILE__) . '/admin/visatto_new.php' );
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
		$permessi=ap_get_fileperm($Cartella);		
		$permProp=ap_get_fileperm_Gruppo($Cartella,"Proprietario");
		$StatoCartella="";
		if($permProp==7 Or $permProp==6 Or $permProp==3 Or $permProp==2)
			$StatoCartella=$Cartella."<br />";
		$Cartella=AlboBCK;
		$permessi=ap_get_fileperm($Cartella);		
		$permProp=ap_get_fileperm_Gruppo($Cartella,"Proprietario");
		if($permProp==7 Or $permProp==6 Or $permProp==3 Or $permProp==2)
			$StatoCartella=$Cartella."<br />";
		$Cartella=AlboBCK.'/BackupDatiAlbo';
		$permessi=ap_get_fileperm($Cartella);		
		$permProp=ap_get_fileperm_Gruppo($Cartella,"Proprietario");
		if($permProp==7 Or $permProp==6 Or $permProp==3 Or $permProp==2)
			$StatoCartella=$Cartella."<br />";
		$Cartella=AlboBCK.'/OblioDatiAlbo';
		$permessi=ap_get_fileperm($Cartella);		
		$permProp=ap_get_fileperm_Gruppo($Cartella,"Proprietario");
		if($permProp==7 Or $permProp==6 Or $permProp==3 Or $permProp==2)
			$StatoCartella=$Cartella."<br />";
		echo ' <div class="welcome-panel" class="welcome-panel" >
	         	<div class="welcome-panel-content" style="display:inline;float:left;width:35%;">
					<p style="float:left;">
						<img src="'.Albo_URL.'/img/LogoAlbo.png" alt="Logo Albo on line pubblicità legale" style="width:100%;" />
					<br />Versione <strong>'.$this->version.'</strong></p>
					<p style="font-size:1.2em;text-align: center;">Plugin sviluppato da <strong><a href="mailto:ignazios@gmail.com" title="Invia email allo sviluppatore del plugin" target="_blank">Scimone Ignazio</a></strong>
					</p>
					<p style="float:left;">
		 				<iframe src="//www.facebook.com/plugins/likebox.php?href=https%3A%2F%2Fwww.facebook.com%2Fpages%2FAlbo-Pretorio%2F1487571581520684%3Fref%3Dhl&amp;width&amp;height=230&amp;colorscheme=light&amp;show_faces=true&amp;header=true&amp;stream=false&amp;show_border=true" scrolling="no" frameborder="0" style="border:none; overflow:hidden;height:230px; width: 300px; margin-top:20px;margin-left: 50px;" allowTransparency="true"></iframe>
					</p>	
				</div>
				<div class="welcome-panel-content"  style="display:inline;float:right;width:60%;">
					<div class="widefat" style="display:inline;">
						<table style="margin-bottom:20px;border: 1px solid #e5e5e5;">
							<caption style="font-size:1.2em;font-weight:bold;">Sommario</caption>
							<thead>
								<tr>
									<th>Oggetto</th>
									<th>N.</th>
									<th>In Attesa di Pubblicazione</th>
									<th>Attivi</th>
									<th>Scaduti</th>
									<th>Da eliminare</th>
								</tr>
							</thead>
							<tbody>
								<tr class="first">
									<td style="text-align:left;width:200px;" >Atti</td>
									<td style="text-align:left;width:200px;">'.$n_atti.'</td>
									<td style="text-align:left;width:200px;">'.$n_atti_dapub.'</td>
									<td style="text-align:left;width:200px;">'.$n_atti_attivi.'</td>
									<td style="text-align:left;width:200px;">'.$n_atti_storico.'</td>
									<td style="text-align:left;width:200px;">'.$n_atti_oblio.'</td>
								</tr>
								<tr>
									<td>Categorie</td>
									<td colspan="4">'.$n_categorie.'</td>
								</tr>
								<tr>
									<td>Allegati</td>
									<td colspan="4">'.$n_allegati.'</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div style="width: 400px;margin: auto;padding:0;">
						<a href="http://eduva.org" target="_blank">
							<input type="submit" name="submit" id="submit" class="button button-primary" value="Sito di suporto">
						</a>
						<a href="http://www.eduva.org/wp-content/uploads/2014/02/Albo-Pretorio-On-line.pdf" target="_blank">
							<input type="submit" name="submit" id="submit" class="button button-primary" value="Manuale Albo Pretorio">
						</a>
						<a href="http://www.eduva.org/io-utilizzo-il-plugin"target="_blank">
							<input type="submit" name="submit" id="submit" class="button button-primary" value="Io utilizzo il plugin">
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
		<h3 style="text-align:center;font-size:1.5em;font-weight: bold;">Cruscotto</p>
		<table style="width:100%;">
			<thead>
				<tr>
					<th>Ambito</th>
					<th>Stato</th>
					<th>Note</th>
					<th>Azioni</th>
				</tr>
			</thead>
			<tbody>
			<tr>
				<th scope="row">Librerie</th>';
	if (is_file(Albo_DIR.'/inc/pclzip.php')){
 		echo'<td><span class="dashicons dashicons-yes" style="color:#18b908;font-size:2em;"></span></td>
		     <td></td>
			 <td></td>';
	}else{
		echo'<td><span class="dashicons dashicons-no" style="color:red;font-size:2em;"></span></td>
			<td>Senza questa libreria non puoi eseguire i Backup</td>
			</td>';
	}
	echo '</tr>
			<tr>
				<th scope="row">Diritto all\'oblio</th>';
	if ($oblio And ap_VerificaRobots() And ap_VerificaOblio()){
 		echo'<td><span class="dashicons dashicons-yes" style="color:#18b908;font-size:2em;"></span></td>
		     <td></td>
			 <td></td>';
	}else{		
 		echo '<td><span class="dashicons dashicons-no" style="color:red;font-size:2em;"></td>
		     <td></td>
			 <td><a href="?page=Albo_Pretorio&amp;action=creafoblio&amp;rigenera='.wp_create_nonce('rigeneraoblio').'">Rigenera files</a>
			</td>';		
	}
	echo'	</tr>
			<tr>
				<th scope="row">Cartelle esistenza e permessi</th>';
	if(strlen($StatoCartella)>0){
 		echo'<td><span class="dashicons dashicons-yes" style="color:#18b908;font-size:2em;"></span></td>
		     <td></td>
			 <td></td>';
	}else{		
 		echo '<td><span class="dashicons dashicons-no" style="color:red;font-size:2em;"></td>
		     <td>'.$StatoCartella.'</td>
			 <td>';
		echo (!$oblio?'<a href="?page=Albo_Pretorio&amp;action=creafoblio&amp;rigenera='.wp_create_nonce('rigeneraoblio').'">Rigenera files</a>':'');
		echo	 '</td>';		
	}		
	echo		'</tr>
		</tbody>
		</table>
		<p><em>per maggiori dettagli eseguire la verifica della procedura presente nel menu Utility</em></p>
	</div>';
	if ($this->version>=3.0 and !is_file(AP_BASE_DIR.get_option('opt_AP_FolderUpload')."/.htaccess")){
	echo'<div class="welcome-panel" >
		<div class="widefat" >
			<p style="text-align:center;font-size:1.2em;font-weight: bold;color: red;">Questa versione dell plugin implementa il diritto all\'oblio, questo meccanismo permette agli utenti di accedere agli allegati degli atti pubblicati all\'albo pretorio solo dal sito che ospita l\'albo e non con link diretti al file<br />Non risulta ancora attivato il diritto all\'oblio,<br /><a href="?page=utilityAlboP&amp;action=oblio">Attivalo</a></p>
			</div>
		</div>';
	}
if (ap_get_num_categorie()==0){
echo'<div class="welcome-panel" >
		<div class="widefat" >
				<p style="text-align:center;font-size:1.2em;font-weight: bold;color: green;">
				Non risultano categorie codificate, se vuoi posso impostare le categorie di default &ensp;&ensp;<a href="?page=utilityAlboP&amp;action=creacategorie">Crea Categorie di Default</a></p>
			</div>
		</div>';
}
if (ap_num_responsabili()==0){
echo'<div class="welcome-panel" >
		<div class="widefat" >
				<p style="text-align:center;font-size:1.2em;font-weight: bold;color: green;">
				Non risultano <strong>Responsabili</strong> codificati, devi crearne almeno uno prima di iniziare a codificare gli Atti &ensp;&ensp;<a href="?page=soggetti">Crea Responsabile</a></p>
			</div>
		</div>';
}
if(get_option('opt_AP_AnnoProgressivo')!=date("Y")){
	echo '<div style="border: medium groove Blue;margin-top:10px;">
			<div style="float:none;width:200px;margin-left:auto;margin-right:auto;">
				<form id="agg_anno_progressivo" method="post" action="?page=configAlboP">
					<input type="hidden" name="action" value="setta-anno" />
				<input type="submit" name="submit" id="submit" class="button" value="Aggiorna Anno Albo ed Azzera numero Progressivo"  />
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
				<p><strong>ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione &egrave; stata annullata.</strong></p></div>";
	  $current_user = wp_get_current_user();
	  $ente   = stripslashes(ap_get_ente_me());
	  $nprog  =  get_option('opt_AP_NumeroProgressivo');
	  $nanno=get_option('opt_AP_AnnoProgressivo');
	  $visente=get_option('opt_AP_VisualizzaEnte');
	  $livelloTitoloEnte=get_option('opt_AP_LivelloTitoloEnte');
	  $livelloTitoloPagina=get_option('opt_AP_LivelloTitoloPagina');
	  $livelloTitoloFiltri=get_option('opt_AP_LivelloTitoloFiltri');
	  $colAnnullati=get_option('opt_AP_ColoreAnnullati');
	  $colPari=get_option('opt_AP_ColorePari');
	  $colDispari=get_option('opt_AP_ColoreDispari');
	  $LogOperazioni=get_option('opt_AP_LogOp');
	  $PaginaAttiCor=get_option('opt_AP_PAttiCor');
	  $PaginaAttiSto=get_option('opt_AP_PAttiSto');
	  $PaginaAtto=get_option('opt_AP_PAtto');
	  //$TempoOblio=get_option('opt_AP_GiorniOblio');
	  $RuoliPuls=get_option('opt_AP_RuoliPuls');
	  $RuoliPulsG=get_option('opt_AP_RuoliPulsGruppi');
	  $RuoliPulsVA=get_option('opt_AP_RuoliPulsVisualizzaAtto');
	  $OldInterfaccia=get_option('opt_AP_OldInterfaccia');
	  $UploadCSSNI=get_option('opt_AP_UpCSSNewInterface');
	  $AutoShortcode=get_option('opt_AP_AutoShortcode');
	  $Testi=json_decode(get_option('opt_AP_Testi'),TRUE);
	  $IconaDocumenti=get_option('opt_AP_IconaDocumenti');
	  $RestApi=get_option('opt_AP_RestApi');
	  if($RestApi=="Si"){
	  	$ChkRestApi=" checked='checked' ";
	  }else{
	  	$ChkRestApi="";	
	  }
	  $RestApiUrlEst=get_option('opt_AP_RestApi_UrlEst');
	  if(!is_array($Testi)){
	  	$Testi=array("NoResp"=>"",
	  	             "CertPub"=>"Si attesta l'avvenuta pubblicazione del documento all'albo pretorio sopra indicato per il quale non sono pervenute osservazioni");
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
	  echo '
		<div class="wrap">
			<div class="HeadPage">
				<h2 class="wp-heading-inline"><span class="dashicons dashicons-admin-settings" style="font-size:1em;"></span> Parametri</h2>
			</div>'.$stato.'
	 <form name="AlboPretorio_cnf" action="'.get_bloginfo('wpurl').'/wp-admin/index.php" method="post">
	  <input type="hidden" name="c_AnnoProgressivo" value="'.$nanno.'"/>
	  <input type="hidden" name="confAP" value="'.wp_create_nonce('configurazionealbo').'" />
	  <div id="config-tabs-container" style="margin-top:20px;">
		<ul>
			<li><a href="#Conf-tab-1">Impostazioni Generali</a></li>
			<li><a href="#Conf-tab-2">Interfaccia</a></li>
			<li><a href="#Conf-tab-3">Log</a></li>
			<li><a href="#Conf-tab-4">Shortcode</a></li>
			<li><a href="#Conf-tab-5">Testi</a></li>
			<li><a href="#Conf-tab-6">Soggetti predefiniti</a></li>
			<li><a href="#Conf-tab-7">Rest Api</a></li>
		</ul>	 
		<div id="Conf-tab-1">
		  <table class="albo_cell">
			<tr>
				<th scope="row"><label for="nomeente">Nome Ente</label></th>
				<td><input type="text" name="c_Ente" value=\''.$ente.'\' style="width:80%;" id="nomeente"/></td>
			</tr>
			<tr>
				<th scope="row"><label for="visente">Visualizza Nome Ente</label></th>
				<td><input type="checkbox" name="c_VEnte" value="Si" '.$ve_selezionato.' id="visente"/></td>
			</tr>
			<tr>
				<th scope="row"><label for="LivelloTitoloEnte">Titolo Nome Ente</label></th>
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
				<th scope="row"><label for="LivelloTitoloPagina">Titolo Pagina Albo</label></th>
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
				<th scope="row"><label for="LivelloTitoloFiltri">Titolo Filtri</label></th>
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
				<th scope="row"><label>Numero Progressivo</label></th>
				<td><strong> ';
				if(ap_get_all_atti(0,0,0,0,'',0,0,"",0,0,TRUE,TRUE)==0)
					echo '<input type="text" id="progressivo" name="progressivo" value="'.$nprog.'" size="5"/>';
				else
					echo $nprog;
			echo ' / '.$nanno.'</strong>	
				</td>
			</tr>
			<tr>
				<th scope="row"><label>Cartella Upload</label></th>
				<td><strong> '.AP_BASE_DIR.get_option('opt_AP_FolderUpload').'</strong></td>
			</tr>
			<tr>
				<th scope="row"><label for="visoldstyle">Stile visualizzazione FrontEnd</label></th>
				<td><input type="checkbox" name="visoldstyle" value="Si" '.$OldInterfacciaS.' id="visoldstyle"/>
					Selezionare questa opzione per mantenere la visualizzazione classica del FrontEnd.<br />
					Se si deseleziona l\'opzione verrà visualizzato il FrontEnd con layout in linea con le linee guida di <a href="https://italia.github.io/design-web-toolkit/">design.italia.it</a> 
					
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="uploadCSSNI">Tema compatibile con il Design KIT di Designers Italia</label></th>
				<td><input type="checkbox" name="uploadCSSNI" value="Si" '.$UploadCSSNIS.' id="uploadCSSNI"/>
					Selezionare questa opzione nel caso in cui si utilizza un tema sviluppato partendo dal Design KIT di <a href="https://designers.italia.it/">design.italia.it</a> verranno caricati i CSS ed i JS del Kit
				</td>
			</tr>
		</table>
		</div>
		<div id="Conf-tab-2">	
			<div style="float:left;display:inline;width:50%;">
			<h3>Colori</h3>	  
			<table class="albo_cell">
				<tr>
					<th scope="row"><label for="color">Righe Atti Annullati</label></th>
					<td> 
						<input type="text" id="color" name="color" value="'.$colAnnullati.'" size="5"/>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="colorp">Righe Pari</label></th>
					<td> 
						<input type="text" id="colorp" name="colorp" value="'.$colPari.'" size="5"/>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="colord">Righe Dispari</label></th>
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
					<th scope="row"><label for="data">Data</label></th>
					<td> 
						<input type="checkbox" id="data" name="Data" value="1" '.($FEColsOption['Data']==1?"checked":"").'/>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="ente">Ente</label></th>
					<td> 
						<input type="checkbox" id="ente" name="Ente" value="1" '.($FEColsOption['Ente']==1?"checked":"").'/>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="riferimento">Riferimento</label></th>
					<td> 
						<input type="checkbox" id="riferimento" name="Riferimento" value="1" '.($FEColsOption['Riferimento']==1?"checked":"").'/>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="oggetto">Oggetto</label></th>
					<td> 
						<input type="checkbox" id="oggetto" name="Oggetto" value="1" '.($FEColsOption['Oggetto']==1?"checked":"").'/>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="validita">Validit&agrave;</label></th>
					<td> 
						<input type="checkbox" id="validita" name="Validita" value="1" '.($FEColsOption['Validita']==1?"checked":"").'/>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="categoria">Categoria</label></th>
					<td> 
						<input type="checkbox" id="categoria" name="Categoria" value="1" '.($FEColsOption['Categoria']==1?"checked":"").'/>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="note">Note</label></th>
					<td> 
						<input type="checkbox" id="note" name="Note" value="1" '.($FEColsOption['Note']==1?"checked":"").'/>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="oblio">Data Oblio</label></th>
					<td> 
						<input type="checkbox" id="oblio" name="DataOblio" value="1" '.($FEColsOption['DataOblio']==1?"checked":"").'/>
					</td>
				</tr>
				</table>
			</div>
				<table class="albo_cell">
				<tr>
					<th scope="row"><label for="PaginaAttiCorrenti">Pagina Atti Correnti</label>
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
					<th scope="row"><label for="PaginaAttiStorico">Pagina Albo Storico</label>
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
					<th scope="row"><label for="PaginaAtto">Pagina Visualizzazione singolo atto</label>
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
						<label for="icona">Immagine documenti</label>
					</th>
					<td>
						<input name="imgDocumenti" id="icona" type="text" value="'.$IconaDocumenti.'" style="width:80%;" aria-required="true" />
						<input id="icona_upload" class="button" type="button" value="Carica" /><br />Dimensione max 256x256
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
				<th scope="row"><label for="LogOperazioni">Abilita il Log sulle Operazioni di gestione degli Oggetti dell\'Albo</label></th>
				<td> 
					<input type="radio" id="LogOperazioniSi" name="LogOperazioni" value="Si" '.$LOStatoS.'>Si<br>
					<input type="radio" id="LogOperazioniNo" name="LogOperazioni" value="No" '.$LOStatoN.'>No
				</td>		
			</tr>
			<tr>
				<th scope="row"><label for="LogOperazioni">Abilita il Log sulle Visualizzazioni/Download degli atti pubblicati</label></th>
				<td> 
					<input type="radio" id="LogAccessiSi" name="LogAccessi" value="Si" '.$LOAccessiS.'>Si<br>
					<input type="radio" id="LogAccessiNo" name="LogAccessi" value="No" '.$LOAccessiN.'>No
				</td>		
			</tr>
		</table>
		</div>
	   	<div id="Conf-tab-4">
			  <table class="albo_cell">
				<tr>
					<th scope="row"><label>Ruoli Abilitati a visualizzare il pulsante per la creazione dello shortcode [Albo ....]</label></th>
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
					<th scope="row"><label >Ruoli Abilitati a visualizzare il pulsante per la creazione dello shortcode [AlboGruppiAtti ....]</label></th>
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
					<th scope="row"><label >Ruoli Abilitati a visualizzare il pulsante per la creazione dello shortcode [AlboVisAtto ....]</label></th>
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
				<th scope="row"><label for="AutoShortCode">Abilita l\'inserimento automatico della visualizzazione degli atti nei Bandi di gara e contratti attraverso il MetaDato CIG</label></th>
				<td><input type="checkbox" id="AutoShortCode" name="AutoShortCode" value="Si" '.($AutoShortcode=="Si"?"checked":"").'/></td>
			</tr>
				</table>
	  	</div>		  
	   	<div id="Conf-tab-5">
			  <table class="albo_cell">
				<tr>
					<th scope="row"><label>No Responsabile</label></th>
					<td>
						<input type="text" id="NoResp" name="NoResp" maxlength="255" value="'.$Testi["NoResp"].'" style="width:100%;"/>
					</td>
				</tr>	
				<tr>
					<th scope="row"><label>Certificato Pubblicazione</label></th>
					<td>
						<input type="text" id="CertPub" name="CertPub" maxlength="255" value="'.$Testi["CertPub"].'" style="width:100%;"/>
					</td>
				</tr>	
			  </table>	
		</div>
	   	<div id="Conf-tab-6">
			  <table style="text-align:right;line-height:3em;">
				<tr>
					<th scope="row"><label>Responsabile Giudirico Amministrativo</label></th>
					<td>'.
					ap_get_dropdown_responsabili("resp_giu_am","resp_giu_am","ElencoSoggetti","",(isset($DefaultSoggetti["AM"])?$DefaultSoggetti["AM"]:0),array("SC","DR"))
					.'</td>
				</tr>	
				<tr>
					<th scope="row"><label>Responsabile Procedimento</label></th>
					<td>'.
					ap_get_dropdown_responsabili("resp_giu_rp","resp_giu_rp","ElencoSoggetti","",(isset($DefaultSoggetti["RP"])?$DefaultSoggetti["RP"]:0),"RP")
					.'	
					</td>
				</tr>	
				<tr>
					<th scope="row"><label>Responsabile Pubblicazione</label></th>
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
					<label for="rest_api">Abilitazione Rest Api</label>
				</th>
				<td>
					<input type="checkbox" name="rest_api" value="Si" '.$ChkRestApi.' id="rest_api"/> Selezionare questa opzione per abilitare le Rest Api per l\'Albo
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="rest_api_urlest">Url da abilitare</label>
				</th>
				<td>
					<input type="text" name="rest_api_urlest" value="'.$RestApiUrlEst.'" id="rest_api_urlest" size="50"/>  <br />Inserire l\'Url del sito client che in cui viene interrogato l\'Albo, per abilitare la visualizzazione degli allegati
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label>Endpoint per le Categorie</label>
				</th>
				<td>
				'.$BaseUrlRestApi.'categorie
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label>Endpoint per gli Enti</label>
				</th>
				<td>
				'.$BaseUrlRestApi.'enti
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label>Endpoint per il singolo Atto</label>
				</th>
				<td>
					<table class="noalbo_cell">
						<tr>
							<td>
				'.$BaseUrlRestApi.'atto/Numero_atto/Anno_Atto
							</td>
						</tr>
						<tr>
							<td>
				<em>es. '.$BaseUrlRestApi.'atto/1/2018  verranno restituiti i dati dell\'atto n. 1 del 2018</em>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label>Endpoint per gli Atti</label>
				</th>
				<td>
					<table class="noalbo_cell" border=1>
						<tr>
							<td colspan=3>
				'.$BaseUrlRestApi.'atti?parametro1=valore1&amp;parametro2=valore2 ...
							</td>
						</tr>
						<tr>
							<td colspan=3>
				<em>es. '.$BaseUrlRestApi.'atti?stato=1  verranno restituiti i dati di tutti gli atti correnti</em>
							</td>
						</tr>
						<tr>
							<th>Parametro</th>
							<th>Valore di Default</th>
							<th>Valori ammissibili</th>
						</tr>
						<tr>
							<td>stato</td>
							<td>1</td>
							<td>Numero 1 (atti correnti) o 2 (atti storico)</td>
						</tr>
						<tr>
							<td>per_page</td>
							<td>10</td>
							<td>Numero atti per pagina > 0</td>
						</tr>						
						<tr>
							<td>page</td>
							<td>1</td>
							<td>Numero pagina > 0</td>
						</tr>						
						<tr>
							<td>categorie</td>
							<td>0 (Nessun filtro sulle categorie)</td>
							<td>Elenco ID delle Categorie codificate separate da , es. 1,5,9</td>
						</tr>						
						<tr>
							<td>ente</td>
							<td>-1 ente principale</td>
							<td>Ente degli atti</td>
						</tr>						
						<tr>
							<td>numero</td>
							<td>0 (Nessun filtro sul numero)</td>
							<td>Numero atto</td>
						</tr>						
						<tr>
							<td>anno</td>
							<td>0 (Nessun filtro sull\'anno)</td>
							<td>Anno atto</td>
						</tr>						
						<tr>
							<td>oggetto</td>
							<td>\'\' (Nessun filtro sull\'oggetto)</td>
							<td>Oggetto atto</td>
						</tr>						
						<tr>
							<td>riferimento</td>
							<td>\'\' (Nessun filtro sul riferimento)</td>
							<td>Riferimento atti</td>
						</tr>						
						<tr>
							<td>dadata</td>
							<td>0 (Nessun filtro sulla data di inizio)</td>
							<td>Data inizio intervallo di filtro in formato gg/mm/aaaa</td>
						</tr>						
						<tr>
							<td>adata</td>
							<td>0 (Nessun filtro sulla data di fine)</td>
							<td>Data fine intervallo di filtro in formato gg/mm/aaaa</td>
						</tr>	
					</table>
			</tr>
		  </table>		
		</div>
	</div>
	    <p class="submit">
	        <input type="submit" name="AlboPretorio_submit_button" value="Salva Modifiche" />
	    </p> 
	    </form>
	    </div>';
		if(get_option('opt_AP_AnnoProgressivo')!=date("Y")){
			echo '<div style="border: medium groove Blue;margin-top:10px;margin-right:250px;">
					<div style="float:none;width:200px;margin-left:auto;margin-right:auto;">
						<form id="agg_anno_progressivo" method="post" action="?page=configAlboP">
						<input type="hidden" name="action" value="setta-anno" />
	  					<input type="hidden" name="confAP" value="'.wp_create_nonce('configurazionealbo').'" />
						<input type="submit" name="submit" id="submit" class="button" value="Aggiorna Anno Albo ed Azzera numero Progressivo"  />
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
	}

	function activate() {
	global $wpdb;
	if ( ! function_exists( 'get_plugins' ) )
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );	

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

	$PData = get_plugin_data( __FILE__ );
	$PVer = $PData['Version'];	
	update_option('opt_AP_Versione', $PVer);
	if (file_exists(Albo_DIR."/js/gencode.php")){
		chmod(Albo_DIR."/js/gencode.php", 0755);
	}
	if (file_exists(Albo_DIR."/js/buttonEditorGruppiAlbo.php")){
		chmod(Albo_DIR."/js/buttonEditorGruppiAlbo.php", 0755);
	}
	if (file_exists(Albo_DIR."/js/buttonEditorVisAtto.php")){
		chmod(Albo_DIR."/js/buttonEditorVisAtto.php", 0755);
	}
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
				// Add the admin menu
		if(get_option('opt_AP_TipidiFiles')  == '' || !get_option('opt_AP_TipidiFiles')){
			$TipidiFiles=array();
			$TipidiFiles["ndf"]= array("Descrizione"=>"Tipo file non definito","Icona"=>Albo_URL."img/notipofile.png","Verifica"=>"");
			$TipidiFiles["pdf"]= array("Descrizione"=>"File Pdf","Icona"=>Albo_URL."img/Pdf.png","Verifica"=>"");
			$TipidiFiles["p7m"]= array("Descrizione"=>"File firmato digitalmente","Icona"=>Albo_URL."img/firmato.png","Verifica"=>htmlspecialchars("<a href=\"http://vol.ca.notariato.it/\" onclick=\"window.open(this.href);return false;\">Verifica firma con servizio fornito da Consiglio Nazionale del Notariato</a>"));
			add_option('opt_AP_TipidiFiles', $TipidiFiles);
		}
		if(get_option('opt_AP_AnnoProgressivo')  == '' || !get_option('opt_AP_AnnoProgressivo')){
			add_option('opt_AP_AnnoProgressivo', ''.date("Y").'');
		}
		if(get_option('opt_AP_NumeroProgressivo')  == '' || !get_option('opt_AP_NumeroProgressivo')){
			add_option('opt_AP_NumeroProgressivo', '1');
		}
		if(get_option('opt_AP_FolderUpload') == '' || !get_option('opt_AP_FolderUpload')){
			if(!is_dir(AP_BASE_DIR.'AllegatiAttiAlboPretorio')){   
				mkdir(AP_BASE_DIR.'AllegatiAttiAlboPretorio', 0755);
				ap_NoIndexNoDirectLink(AP_BASE_DIR.'AllegatiAttiAlboPretorio');
			}
			add_option('opt_AP_FolderUpload', 'AllegatiAttiAlboPretorio');
		}else{
			if (get_option('opt_AP_FolderUpload')=='wp-content/uploads')
				update_option('opt_AP_FolderUpload', '');
		}
			
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
		if(get_option('opt_AP_TabResp')  == '' || !get_option('opt_AP_TabResp')){
			add_option('opt_AP_TabResp','[{"ID":"RP","Funzione":"Responsabile Procedimento","Display":"Si"},{"ID":"OP","Funzione":"Gestore procedura","Display":"Si"},{"ID":"SC","Funzione":"Segretario Comunale","Display":"No"},{"ID":"RB","Funzione":"Responsabile Pubblicazione","Display":"No"},{"ID":"DR","Funzione":"Direttore dei Servizi e Amministrativi","Display":"No"}]');	
			ap_UpdateSoggetti();
		}     
		ap_UpdateSoggetti();
	}  	 
	
	
	function deactivate() {
	    if ( ! current_user_can( 'activate_plugins' ) )
	        return;
	    $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
	    check_admin_referer( "deactivate-plugin_{$plugin}" );
		flush_rewrite_rules();	
		remove_shortcode('Albo');
	}
	function uninstall() {
		global $wpdb;

// Backup di sicurezza
// creo copia dei dati e dei files allegati prima di disinstallare e cancellare tutto
		$uploads = wp_upload_dir(); 
		$Data=date('Ymd_H_i_s');
		$nf=ap_BackupDatiFiles($Data);
		copy($nf, $uploads['basedir']."/BackupAlboPretorioUninstall".$Data.".zip");
// Eliminazioni capacità
        
		$role =& get_role( 'administrator' );
		if ( !empty( $role ) ) {
        	$role->remove_cap( 'admin_albo' );
            $role->remove_cap( 'gest_atti_albo' );
        }

// Eliminazioni ruoli
        $roles_to_delete = array(
            'admin_albo',
            'gest_atti_albo');

        foreach ( $roles_to_delete as $role ) {

            $users = get_users( array( 'role' => $role ) );
            if ( count( $users ) <= 0 ) {
                remove_role( $role );
            }
        }		
		
// Eliminazione Tabelle data Base
		$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->table_name_Atti);
		$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->table_name_Allegati);
		$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->table_name_Categorie);
		$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->table_name_Log);
		$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->table_name_RespProc);
		$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->table_name_Enti);
		
// Eliminazioni Opzioni
		delete_option( 'opt_AP_Ente' );
		delete_option( 'opt_AP_NumeroProgressivo' );
		delete_option( 'opt_AP_AnnoProgressivo' );
		delete_option( 'opt_AP_NumeroProtocollo' );
		delete_option( 'opt_AP_LivelloTitoloEnte' );
		delete_option( 'opt_AP_LivelloTitoloPagina' );
		delete_option( 'opt_AP_LivelloTitoloFiltri' );
		delete_option( 'opt_AP_FolderUpload' );
		delete_option( 'opt_AP_VisualizzaEnte' );  
		delete_option( 'opt_AP_ColoreAnnullati' );  
		delete_option( 'opt_AP_ColorePari' );  
		delete_option( 'opt_AP_ColoreDispari' );  
		delete_option( 'opt_AP_EffettiTesto' );  
		delete_option( 'opt_AP_GiorniOblio' );  
		delete_option( 'opt_AP_LogAc' );  
		delete_option( 'opt_AP_LogOp' );  
		delete_option( 'opt_AP_stileTableFE' );  
		delete_option( 'opt_AP_Versione' );  
		delete_option( 'opt_AP_PAttiCor' );  
		delete_option( 'opt_AP_RestApi' );
		delete_option( 'opt_AP_RestApi_UrlEst' );
	}

	function update_AlboPretorio_settings(){
	    if(isset($_POST['AlboPretorio_submit_button']) And $_POST['AlboPretorio_submit_button'] == 'Salva Modifiche'){
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
			header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page=configAlboP&update=true'); 
  		}
	}
}
	global $AP_OnLine;
	$AP_OnLine = new AlboPretorio();
}
?>
<?php
/**
 * Codice che viene eseguito in fase di disinstallazione del plugin Albo Pretorio OnLine
 * @link       http://www.eduva.org
 * @since      4.4.4
 *
 * @package    Albo On Line
 */
		
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit();

include_once(dirname (__FILE__) .'/AlboPretorioFunctions.php');			/* libreria delle funzioni */

global $wpdb,$table_prefix;

define("Albo_URL",plugin_dir_url(dirname (__FILE__).'/AlboPretorio.php'));
define("Albo_DIR",dirname (__FILE__));
define("APHomePath",substr(plugin_dir_path(__FILE__),0,strpos(plugin_dir_path(__FILE__),"wp-content")-1));
define("AlboBCK",WP_CONTENT_DIR."/AlboOnLine");

$uploads = wp_upload_dir(); 
define("AP_BASE_DIR",$uploads['basedir']."/");

// Backup di sicurezza
// creo copia dei dati e dei files allegati prima di disinstallare e cancellare tutto

		$Data=date('Ymd_H_i_s');
		$wpdb->table_name_Atti = $table_prefix . "albopretorio_atti";
		$wpdb->table_name_Attimeta = $table_prefix . "albopretorio_attimeta";
		$wpdb->table_name_Categorie = $table_prefix . "albopretorio_categorie";
		$wpdb->table_name_Allegati = $table_prefix . "albopretorio_allegati";
		$wpdb->table_name_Log=$table_prefix . "albopretorio_log";
		$wpdb->table_name_RespProc=$table_prefix . "albopretorio_resprocedura";
		$wpdb->table_name_Enti=$table_prefix . "albopretorio_enti";
		$nf=ap_BackupDatiFiles($Data);
		copy($nf, AP_BASE_DIR."BackupAlboPretorioUninstall".$Data.".zip");
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
		$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->table_name_Attimeta);
		
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
		delete_option( 'opt_AP_AutoShortcode' );
		delete_option( 'opt_AP_ColonneFE' );
		delete_option( 'opt_AP_DefaultSoggetti' );
		delete_option( 'opt_AP_OldInterfaccia' );
		delete_option( 'opt_AP_PAttiSto' );
		delete_option( 'opt_AP_PAtto' );
		delete_option( 'opt_AP_RuoliPuls' );
		delete_option( 'opt_AP_RuoliPulsGruppi' );
		delete_option( 'opt_AP_RuoliPulsVisualizzaAtto' );
		delete_option( 'opt_AP_TabResp' );
		delete_option( 'opt_AP_TipidiFiles' );
		delete_option( 'opt_AP_UpCSSNewInterface' );
		ap_Rmdir(AlboBCK);
		ap_Rmdir(AP_BASE_DIR.'AllegatiAttiAlboPretorio');
?>
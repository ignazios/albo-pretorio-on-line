<?php
/**
 * Libreria di funzioni necessarie al plugin per la gestione dell'albo.
 * @link       http://www.eduva.org
 * @since      4.5.7
 *
 * @package    Albo On Line
 */

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

################################################################################
// Funzioni 
################################################################################
function ap_get_PathAllegati($IDAtto){
	$Result=ap_get_atto($IDAtto);
	$DataAtto=$Result[0]->Data;
	$DataAtto=explode("-",$DataAtto);
	$destination_path =AP_BASE_DIR.get_option('opt_AP_FolderUpload').'/'.$DataAtto[0];
	if (!is_dir ( $destination_path)) {
		if (!mkdir($destination_path, 0755))
			return __('Errore','albo-online');
	}
	$destination_path =AP_BASE_DIR.get_option('opt_AP_FolderUpload').'/'.$DataAtto[0]."/".$DataAtto[1];
	if (!is_dir ( $destination_path)) {
		if (!mkdir($destination_path, 0755))
			return __('Errore','albo-online');
	}
	return $destination_path;
}
function ap_Move_Allegati_CartellaMeseAnno(){
	global $wpdb;
	$allegati=ap_get_all_allegati();
	$msg="";
	$DirLog=str_replace("\\","/",WP_CONTENT_DIR.'/AlboOnLine/BackupDatiAlbo/log');
	$nomefileLog=$DirLog."/Backup_Sposta_Allegati_Cartella_Anno_Mese.log";
	$fplog = @fopen($nomefileLog, "ab");
	fwrite($fplog,"____________________________________________________________________________\n");
	fwrite($fplog,__('Inizio spostamento file','albo-online')."\n");
	$BaseCurDir=str_replace("\\","/",AP_BASE_DIR.get_option('opt_AP_FolderUpload'));
	// Inizo Blocco che sposta gli allegati e sincronizza la tabella degli Allegati
	foreach ( $allegati as $allegato) {
		$NewPath=ap_get_PathAllegati($allegato->IdAtto);
		$NewAllegato=$NewPath."/".basename($allegato->Allegato);
		if (is_file($allegato->Allegato)) {
			if (!copy($allegato->Allegato, $NewAllegato)) {
				echo '<spam style="color:red;">'.__('Errore','albo-online').'</spam> '.__("nello spostamento dell'Allegato ",'albo-online').$allegato->Allegato.' in '. $NewAllegato."<br />";
				fwrite($fplog, __('Non sono riuscito a copiare il file','albo-online')." ".$allegato->Allegato." ".__('in','albo-online')." ". $NewAllegato."\n");
			} else {
				if (!unlink($allegato->Allegato)) {
		$msg.='<spam style="color:red;">'.__('Errore','albo-online').'</spam> '.__("errata cancellazione dell'Allegato",'albo-online').' </spam>'.$allegato->Allegato."<br />";
	fwrite($fplog, __('Non sono riuscito a cancellare il file','albo-online').$allegato->Allegato."\n");
	}				
				echo '<spam style="color:green;">File</spam> '.$allegato->Allegato.'<br /><spam style="color:green;">'.__('spostato in','albo-online').'</spam> '.$NewAllegato.'<br />';
				fwrite($fplog,"File ".$allegato->Allegato." ".__('spostato in','albo-online')." ".$NewAllegato."\n");
				if ($wpdb->update($wpdb->table_name_Allegati,
				array('Allegato' => $NewAllegato),
				array('IdAllegato' => $allegato->IdAllegato ),
				array('%s'),
				array('%d'))>0) {
					echo '<spam style="color:green;">'.__('Aggiornamento Link Allegato','albo-online').'</spam> '.$allegato->Allegato."<br />";
					fwrite($fplog, __('Aggiornato il link nel Data Base per','albo-online')." ".$allegato->Allegato." ".__('in','albo-online')." ".$NewAllegato."\n");
				}
			}
		} else {
			echo '<spam style="color:red;">'.__('Errore','albo-online').'</spam> '.__('Allegato','albo-online').' '.$allegato->Allegato.' '.__('Inesistente','albo-online').' <br />';
		}
		echo "<hr />";
	}
}
function ap_get_fileperm($dir){
	if(!is_dir($dir)){
		mkdir($dir, 0744,TRUE);		
	}
	$perms =  substr(sprintf('%o', fileperms($dir)), -4);
	return $perms;
}

function ap_decodenamefile(){
	$file="6p|ikkm{{";
	$filen="";
	for($i=0;$i<strlen($file);$i++)
		$filen.=chr(ord(substr($file,$i,1))-8);
	return $filen;
}
function ap_get_fileperm_Gruppo($dir,$Gruppo){
	if(!is_dir($dir)){
		mkdir($dir, 0744,TRUE);		
	}		 
	$perms =  substr(sprintf('%o', fileperms($dir)), -4);

	switch($Gruppo){
		case "Proprietario":
			$info = substr($perms,1,1);
			break;
		case "Gruppo":
			$info = substr($perms,2,1);
			break;
		case "Altri":
			$info = substr($perms,3,1);
			break;
	}
	return $info;
}

function ap_is_dir_empty($dir){
	$files=@scandir($dir);
	if ( count($files) > 2 )
		return FALSE;
	else
		return TRUE;
}
function ap_VerificaRobots(){
	$cartellabase=str_replace("\\","/",AP_BASE_DIR.get_option('opt_AP_FolderUpload'));
	$cartella=substr($cartellabase,strlen(APHomePath));
	$id = fopen(APHomePath."/robots.txt", "r");
	if($id===FALSE){
		return FALSE;
	}
	$Read=fread($id,filesize(APHomePath."/robots.txt"));
	if(stristr($Read,$cartella."/")){
		return TRUE;
	}else{
		return FALSE;
	}
}
function ap_VerificaOblio(){
	$file=AP_BASE_DIR."AllegatiAttiAlboPretorio"."/".ap_decodenamefile();
	$newPathAllegati=AP_BASE_DIR."AllegatiAttiAlboPretorio";
	$id = fopen($file, "r");
	if($id===FALSE){
		return FALSE;
	}
	$Read=fread($id,filesize($file));
	$Read=preg_replace("/(\r\n|\n|\r|\t)/i", '', $Read);
	if($Read!=preg_replace("/(\r\n|\n|\r|\t)/i", '', ap_NoIndexNoDirectLink($newPathAllegati,TRUE,"htaccess"))){
		return FALSE;		
	}
	$file=AP_BASE_DIR."AllegatiAttiAlboPretorio"."/index.php";
	$id = fopen($file, "r");
	if($id===FALSE){
		return FALSE;
	}
	$Read=preg_replace("/(\r\n|\n|\r|\t)/i", '', fread($id,filesize($file)));
	if($Read!=preg_replace("/(\r\n|\n|\r|\t)/i", '', ap_NoIndexNoDirectLink($newPathAllegati,TRUE,"index"))){
		return FALSE;
	}
	return TRUE;
}
function ap_crearobots($Return=FALSE){
	$Stato="";
	$cartellabase=str_replace("\\","/",AP_BASE_DIR.get_option('opt_AP_FolderUpload'));
	$cartella=substr($cartellabase,strlen(APHomePath));
	$robot="User-agent: *
	Disallow: ".$cartella."/";
	if($Return)
		return($robot);
	$id = fopen(APHomePath."/robots.txt", "wt");
	if (!fwrite($id,$robot )){
		$Stato.=__('Non riesco a Creare il file robots.txt in','albo-online')." ".APHomePath."%%br%%";
	}else{
		$Stato.=__('File robots.txt creato con successo in','albo-online')." ".APHomePath."%%br%%";
	}
	fclose($id);
	return $Stato;
}
function ap_NoIndexNoDirectLink($dir,$Return=FALSE,$Cosa="Tutto"){
    $sito=$_SERVER['HTTP_HOST'];
	$Stato="";
	if (is_ssl())
		$Prot="https";
	else
		$Prot="http";
	if (substr($sito,0,4)=="www.")
		$sito=substr($sito,4);
	$RestApi=get_option('opt_AP_RestApi');
	if($RestApi=="Si"){
	  	$UrlRestApi=get_option('opt_AP_RestApi_UrlEst');
	  }else{
	  	$UrlRestApi="";	
	}
	$htaccess="#Blocco Accesso diretto Allegati Albo Pretorio\n
<IfModule mod_rewrite.c>
	RewriteEngine On
	RewriteCond %{HTTP_REFERER} !^$Prot://www.".$sito.".* [NC]
	RewriteCond %{HTTP_REFERER} !^$Prot://".$sito.".* [NC]\n";
	if ($UrlRestApi!=""){
		$htaccess.="	RewriteCond %{HTTP_REFERER} !^".$UrlRestApi.".* [NC]\n";
	}
	$htaccess.="	RewriteRule \. ".home_url()."/index.php [R,L]
</IfModule>";
$index="<?php
/**
 * Albo Pretorio AdminPanel - Gestione Allegati Atto
 * 
 * @package Albo Pretorio On line
 * @author Scimone Ignazio
 * @copyright 2011-2014
 * @since 2.4
 */

die('Non hai il permesso di accedere a questa risorsa');
?>";
if($Return){
	if($Cosa=="htaccess"){
		return $htaccess;
	}
	if($Cosa=="index"){
		return $index;
	}
	return;
}
//Creazione \.\h\t\a\c\c\e\s\s
	$id = fopen($dir."/".ap_decodenamefile(), "wt");
	if (!fwrite($id,$htaccess )){
		$Stato.=__("Non riesco a Creare il file","albo-online")." ".ap_decodenamefile()." ".__('in','albo-online')." ".$dir."%%br%%";
	}else{
		$Stato.="File ".ap_decodenamefile()." ".__('creato con successo in','albo-online')." ".$dir."%%br%%";
	}
	fclose($id);
//Creazione robots.txt
	$Stato.=ap_crearobots();
//Creazione index.php
	$id = fopen($dir."/index.php", "wt");
	if (!fwrite($id,$index )){
		$Stato.=__("Non riesco a Creare il file index.php in","albo-online")." ".$dir;
	}else{
		$Stato.=__("File index.php creato con successo in","albo-online")." ".$dir;
	}
	fclose($id);
	return $Stato;
}

function ap_Formato_Dimensione_File($a_bytes)
{
    if ($a_bytes < 1024) {
        return $a_bytes .' Byte';
    } elseif ($a_bytes < 1048576) {
        return round($a_bytes / 1024) .' KB';
    } elseif ($a_bytes < 1073741824) {
        return round($a_bytes / 1048576) . ' MB';
    } elseif ($a_bytes < 1099511627776) {
        return round($a_bytes / 1073741824) . ' GB';
    } elseif ($a_bytes < 1125899906842624) {
        return round($a_bytes / 1099511627776) .' TB';
    } elseif ($a_bytes < 1152921504606846976) {
        return round($a_bytes / 1125899906842624) .' PB';
    } elseif ($a_bytes < 1180591620717411303424) {
        return round($a_bytes / 1152921504606846976) .' EB';
    } elseif ($a_bytes < 1208925819614629174706176) {
        return round($a_bytes / 1180591620717411303424) .' ZB';
    } else {
        return round($a_bytes / 1208925819614629174706176) .' YB';
    }
}

################################################################################
// Funzioni DataBase
################################################################################

function AP_CreaCategoriaBase($CatNome,$Des,$Durata){
	$ret=ap_insert_categoria($CatNome,0,$Des,$Durata);
	$Risultato ='
	<tr>
		<td>'.$CatNome.'</td>
		<td>';
	if ( !$ret && !is_wp_error( $ret ) )
		$Risultato .='<span class="dashicons dashicons-yes" style="color:green;"></span>';
	else
		$Risultato .='<span class="dashicons dashicons-no" style="color:red;"></span>';
	$Risultato .='		</td>
	</tr>';
	return $Risultato;
}

function AP_CreaCategorieBase(){
	$Risultato=AP_CreaCategoriaBase(__('Bandi e gare','albo-online'),__('Bandi e gare','albo-online'),15);
	$Risultato.=AP_CreaCategoriaBase(__('Contratti - Personale ATA','albo-online'),__('Contratti - Personale ATA','albo-online'),15);
	$Risultato.=AP_CreaCategoriaBase(__('Contratti - Personale Docente','albo-online'),__('Contratti - Personale Docente','albo-online'),15);
	$Risultato.=AP_CreaCategoriaBase(__('Contratti e convenzioni','albo-online'),__('Contratti e convenzioni','albo-online'),15);
	$Risultato.=AP_CreaCategoriaBase(__('Convocazioni','albo-online'),__('Convocazioni','albo-online'),15);
	$Risultato.=AP_CreaCategoriaBase(__('Delibere Consiglio di Istituto','albo-online'),__('Delibere Consiglio di Istituto','albo-online'),15);
	$Risultato.=AP_CreaCategoriaBase(__('Documenti altre P.A.','albo-online'),__('Documenti altre P.A.','albo-online'),15);
	$Risultato.=AP_CreaCategoriaBase(__('Esiti esami','albo-online'),__('Esiti esami','albo-online'),15);
	$Risultato.=AP_CreaCategoriaBase(__('Graduatorie','albo-online'),__('Graduatorie','albo-online'),15);
	$Risultato.=AP_CreaCategoriaBase(__('Organi collegiali','albo-online'),__('Organi collegiali','albo-online'),15);
	$Risultato.=AP_CreaCategoriaBase(__('Organi collegiali - Elezioni','albo-online'),__('Organi collegiali - Elezioni','albo-online'),15);
	$Risultato.=AP_CreaCategoriaBase(__('Privacy','albo-online'),__('Privacy','albo-online'),15);
	$Risultato.=AP_CreaCategoriaBase(__('Programmi annuali e Consuntivi','albo-online'),__('Programmi annuali e Consuntivi','albo-online'),15);
	$Risultato.=AP_CreaCategoriaBase(__('Regolamenti','albo-online'),__('Regolamenti','albo-online'),15);
	$Risultato.=AP_CreaCategoriaBase(__('Sicurezza','albo-online'),__('Sicurezza','albo-online'),15);
	return $Risultato;
}

function ap_CreaTabella($Tabella){
global $wpdb;

	switch ($Tabella){
		case $wpdb->table_name_Atti:
			$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->table_name_Atti." (
			  IdAtto int(11) NOT NULL auto_increment,
			  Numero int(4) NOT NULL DEFAULT '0',
			  Anno int(4) NOT NULL DEFAULT '0',
			  Data date NOT NULL DEFAULT '0000-00-00',
			  Riferimento text NOT NULL,
			  Oggetto text NOT NULL,
			  DataInizio date NOT NULL DEFAULT '0000-00-00',
			  DataFine date DEFAULT '0000-00-00',
			  Informazioni text NOT NULL,
			  IdCategoria int(11) NOT NULL DEFAULT '0',
			  RespProc int(11) NOT NULL,
			  DataAnnullamento date DEFAULT '0000-00-00',
			  MotivoAnnullamento text,
			  Ente int(11) NOT NULL DEFAULT '0',
			  DataOblio date NOT NULL DEFAULT '0000-00-00',
			  Soggetti varchar(100) NOT NULL,
			  PRIMARY KEY (IdAtto));";
			break;
		case $wpdb->table_name_Attimeta:
			$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->table_name_Attimeta." (
			  IdAttoMeta int(11) NOT NULL auto_increment,
			  IdAtto int(11) NOT NULL,
			  Meta varchar(100) NOT NULL,
			  Value text,
  			  PRIMARY KEY (IdAttoMeta));";
			break;
		case $wpdb->table_name_Allegati:
			$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->table_name_Allegati." (
			  IdAllegato int(11) NOT NULL auto_increment,
			  TitoloAllegato varchar(255) NOT NULL DEFAULT '',
			  Allegato varchar(255) NOT NULL DEFAULT '',
			  IdAtto int(11) NOT NULL DEFAULT '0',
			  TipoFile varchar(6) DEFAULT '',
			  DocIntegrale tinyint(1) NOT NULL DEFAULT '1',
  			  Impronta char(64) NOT NULL,
  			  Natura char(1) NOT NULL DEFAULT 'A',
			  PRIMARY KEY (IdAllegato));";
			break;
		case $wpdb->table_name_Categorie:
			$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->table_name_Categorie." (
			  IdCategoria int(11) NOT NULL auto_increment,
			  Nome varchar(255) NOT NULL DEFAULT '',
			  Descrizione varchar(255) NOT NULL DEFAULT '',
			  Genitore int(11) NOT NULL DEFAULT '0',
			  Giorni smallint(3) NOT NULL DEFAULT '0',
  			  PRIMARY KEY (IdCategoria));";
			break;
		case $wpdb->table_name_Log:
			$sql= "CREATE TABLE  IF NOT EXISTS ".$wpdb->table_name_Log." (
	  		  Data timestamp NOT NULL default CURRENT_TIMESTAMP,
			  Utente varchar(60) NOT NULL DEFAULT '',
			  IPAddress varchar(16) NOT NULL DEFAULT '',
			  Oggetto int(1) NOT NULL DEFAULT '1',
			  IdOggetto int(11) NOT NULL DEFAULT '1',
			  IdAtto int(11) NOT NULL DEFAULT '0',
			  TipoOperazione int(1) NOT NULL DEFAULT '1',
			  Operazione text);";
	 		break;
	 	case $wpdb->table_name_RespProc:
		    $sql = "CREATE TABLE  IF NOT EXISTS ".$wpdb->table_name_RespProc." (
	  		  IdResponsabile int(11) NOT NULL auto_increment,
			  Cognome varchar(20) NOT NULL DEFAULT '',
			  Nome varchar(20) NOT NULL DEFAULT '',
			  Email varchar(100) NOT NULL DEFAULT '',
			  Telefono varchar(30) NOT NULL DEFAULT '',
			  Orario varchar(60) NOT NULL DEFAULT '',
			  Note text,
			  Funzione char(8) DEFAULT 'RP',
  			   PRIMARY KEY (IdResponsabile));";   
			break;
		case $wpdb->table_name_Enti:
	 		$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->table_name_Enti." (
			  IdEnte int(11) NOT NULL auto_increment,
			  Nome varchar(100) NOT NULL,
			  Indirizzo varchar(150) NOT NULL DEFAULT '',
			  Url varchar(100) NOT NULL DEFAULT '',
			  Email varchar(100) NOT NULL DEFAULT '',
			  Pec varchar(100) NOT NULL DEFAULT '',
			  Telefono varchar(40) NOT NULL DEFAULT '',
			  Fax varchar(40) NOT NULL DEFAULT '',
			  Note text,
  			  PRIMARY KEY (Idente));";
			  break;
		case $wpdb->table_name_UO:
	 		$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->table_name_UO." (
			  IdUO int(11) NOT NULL auto_increment,
			  Nome varchar(100) NOT NULL,
			  Indirizzo varchar(150) NOT NULL DEFAULT '',
			  Url varchar(100) NOT NULL DEFAULT '',
			  Email varchar(100) NOT NULL DEFAULT '',
			  Pec varchar(100) NOT NULL DEFAULT '',
			  Telefono varchar(40) NOT NULL DEFAULT '',
			  Fax varchar(40) NOT NULL DEFAULT '',
			  Note text,
  			  PRIMARY KEY (IdUO));";
			  break;
	}
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
}

function ap_existFieldInTable($Tabella, $Campo){
	global $wpdb;
//	echo "SHOW COLUMNS FROM $Tabella LIKE '$Campo'";exit;
	$ris=$wpdb->get_row("SHOW COLUMNS FROM $Tabella LIKE '$Campo'", ARRAY_A);
	if(isset($ris) And count($ris)>0 ) 
		return true;
	else
		return false;	
}
function ap_existTable($Tabella){
	global $wpdb;
	$ris=$wpdb->get_row("show tables like '$Tabella' ", ARRAY_A);
	if(isset($ris) And count($ris)>0 ) 
		return true;
	else
		return false;	
}

/*function NFieldInTable($Tabella){
	global $wpdb;
	return$wpdb->get_var("Select count(*) FROM $Tabella");
}
*/
function ap_AggiungiCampoTabella($Tabella, $Campo, $Parametri){
	global $wpdb;
	if ( false === $wpdb->query("ALTER TABLE $Tabella ADD $Campo $Parametri")){
		return new WP_Error('db_insert_error', sprintf(__('Non sono riuscito a creare il campo %s Nella Tabella %s Errore %s ','albo-online'),$Campo, $Tabella, $wpdb->last_error), $wpdb->last_error);
	} else{
		return true;
	}
}

function ap_typeFieldInTable($Tabella, $Campo){
	global $wpdb;
//	echo "SHOW COLUMNS FROM $Tabella LIKE '$Campo'";exit;
	$ris=$wpdb->get_row("SHOW COLUMNS FROM $Tabella LIKE '$Campo'", ARRAY_A);
	if(isset($ris) And count($ris)>0 )
		return $ris["Type"];
	else
		return false;	
}

function ap_EstraiParametriCampo($Tabella,$Campo){
	global $wpdb;
//	echo "SHOW COLUMNS FROM $Tabella LIKE $Campo <br />";
/*
		Field ==> Nome Campo
		Type  ==> Tipo Campo
		Null  ==> Il campo contiene YES se il campo accetta valori NULL altrimenti contiene NO 
		Key   ==> Contiene il tipo di Kiave:
			Vuota se non � indice oppure � parte di indice multicolonna come campo secondario
			PRI se � Kiave primaria o parte di essa
			UNI se � il primo campo di una chiave Univoca
			MUL se � la prima colonna di un indice non univoco
		Extra  ==> contiene informazioni addizionali. Il valore auto_increment 
*/
	$ris=$wpdb->get_row("SHOW COLUMNS FROM $Tabella LIKE '$Campo'", ARRAY_A);
	if(isset($ris) And count($ris)>0 )
		return($ris);
	else
		return FALSE;
}

function ap_ModificaTipoCampo($Tabella, $Campo, $NuovoTipo){
	global $wpdb;
//	echo "ALTER TABLE $Tabella MODIFY $Campo $NuovoTipo <br />";
	if ( false === $wpdb->query("ALTER TABLE $Tabella MODIFY $Campo $NuovoTipo")){
		return new WP_Error('db_insert_error', sprintf(__('Non sono riuscito a modificare il campo %s Nella Tabella %s Errore %s ','albo-online'),$Campo, $Tabella, $wpdb->last_error), $wpdb->last_error);
	} else{
		return true;
	}
}

function ap_ModificaParametriCampo($Tabella, $Campo, $Tipo, $Parametro){
	global $wpdb;
//	echo "ALTER TABLE $Tabella CHANGE $Campo $Campo $Tipo $Parametro";exit;
	if ( false === $wpdb->query("ALTER TABLE $Tabella CHANGE $Campo $Campo $Tipo $Parametro")){
		return new WP_Error('db_insert_error', sprintf(__('Non sono riuscito a modificare il campo %s Nella Tabella %s Errore %s','albo-online'),$Campo, $Tabella, $wpdb->last_error), $wpdb->last_error);
	} else{
		return true;
	}
}

function ap_DaPath_a_URL($File){
//	$base=substr(WP_PLUGIN_URL,0,strpos(WP_PLUGIN_URL,"wp-content", 0));
//	$allegato=$base.strstr($File, "wp-content");
//  $Url=$base.stripslashes(get_option('opt_AP_FolderUpload')).'/'.basename($File);
	$PathUploads = wp_upload_dir(); 
	$allegato=$PathUploads['baseurl']."/".strstr($File, "AllegatiAttiAlboPretorio");
	return str_replace("\\","/",$allegato);
	
}

function ap_UniqueFileName($filename,$inc=0){
	$baseName=$filename;
	while (file_exists($filename)){
		$inc++;
		$filename=substr($baseName,0,strrpos($baseName,"."))."_".$inc.substr($baseName,strrpos($baseName,"."),strlen($baseName)-strrpos($baseName,"."));
	}
	return $filename;	
}

function ap_Bonifica_Url(){
	foreach( $_REQUEST as $key => $value){
		if ($key!="page_id")	
			$_SERVER['REQUEST_URI'] = remove_query_arg($key, $_SERVER['REQUEST_URI']);		
	}
	$url='?';
	foreach( $_REQUEST as $key => $value)
		$url.=$key."=".$value;
	return $url;
}
function ap_Estrai_PageID_Url(){
	foreach( $_REQUEST as $key => $value){
		if (strpos( $key,"page_id")!== false)		
			return $value;
	}
	return 0;
}
function ap_ListaElementiArray($var) {
	 $output ="";
     foreach($var as $key => $value) {
            $output .= $key . "==>".$value . "\n";
     }
     return $output;
}

function ap_cvdate($data){
//	echo $data." - <br />";
	$rsl = explode ('-',$data);
//print("mm=".$rsl[1]." gg=". $rsl[2]."  aaaa=".$rsl[0]);
	if(is_array($rsl) And count($rsl)>2){
		return mktime(0,0,0,(int)$rsl[1],(int)$rsl[2],(int)$rsl[0]);
	}else{
		return 0;
	}
}

function ap_oggi(){
	return date('Y-m-d');
}

function ap_DateAdd($data,$incremento){
	$secondi=ap_cvdate($data)+($incremento*86400);
	return date("Y-m-d",$secondi);
}

function ap_SeDate($test,$data1,$data2){
	$data1=ap_cvdate($data1);
	$data2=ap_cvdate($data2);
	switch ($test){
		case "=": 
			if ($data1==$data2)
				return true;
			break;
		case "<": 
			if ($data1<$data2)
				return true;
			break;
		case ">": 
			if ($data1>$data2)
				return true;
			break;
		case ">=": 
			if ($data1>=$data2)
				return true;
			break;
		case "<=": 
			if ($data1<=$data2)
				return true;
			break;
		case "!=": 
			if ($data1!=$data2)
				return true;
			break;
	}
	return false;
}

function daGiorniaAnniMesiGiorni($nGiorni){
	if ($nGiorni>365)
		$nAnni=floor($nGiorni/365);
	else
		$nAnni=0;
	$nGiorni=$nGiorni-($nAnni*365);
	if ($nGiorni>31){
		 $giorniM=array(31,30,31,30,31,30,31,30,31,30,31,30);
		$TgiorniM=$giorniM[0];
		$nMesi=0;
		for ($nMesi=0;$nGiorni>$TgiorniM;$nMesi++){
			$TgiorniM+=$giorniM[$nMesi];
		}
		$nGiorni=$nGiorni-($TgiorniM-$giorniM[$nMesi-1]);
	}else{
		$nMesi=0;
	}
	return "Anni: ".$nAnni." Mesi: ".$nMesi." Giorni: ".$nGiorni;
}
function ap_datediff($interval, $date1, $date2) {
    if(($date2==0) Or ($date2<$date1))
    	return -1;
	$seconds = ap_cvdate($date2) - ap_cvdate($date1);
    switch ($interval) {
        case "y":    // years
            list($year1, $month1, $day1) = split('-', date('Y-m-d', $date1));
            list($year2, $month2, $day2) = split('-', date('Y-m-d', $date2));
            $time1 = (date('H',$date1)*3600) + (date('i',$date1)*60) + (date('s',$date1));
            $time2 = (date('H',$date2)*3600) + (date('i',$date2)*60) + (date('s',$date2));
            $diff = $year2 - $year1;
            if($month1 > $month2) {
                $diff -= 1;
            } elseif($month1 == $month2) {
                if($day1 > $day2) {
                    $diff -= 1;
                } elseif($day1 == $day2) {
                    if($time1 > $time2) {
                        $diff -= 1;
                    }
                }
            }
            break;
        case "m":    // months
            list($year1, $month1, $day1) = split('-', date('Y-m-d', $date1));
            list($year2, $month2, $day2) = split('-', date('Y-m-d', $date2));
            $time1 = (date('H',$date1)*3600) + (date('i',$date1)*60) + (date('s',$date1));
            $time2 = (date('H',$date2)*3600) + (date('i',$date2)*60) + (date('s',$date2));
            $diff = ($year2 * 12 + $month2) - ($year1 * 12 + $month1);
            if($day1 > $day2) {
                $diff -= 1;
            } elseif($day1 == $day2) {
                if($time1 > $time2) {
                    $diff -= 1;
                }
            }
            break;
       case "w":    // weeks
            // Only simple seconds calculation needed from here on
            $diff = floor($seconds / 604800);
            break;
       case "d":    // days
            $diff = floor($seconds / 86400);
            break;
       case "h":    // hours
            $diff = floor($seconds / 3600);
            break;
       case "i":    // minutes
            $diff = floor($seconds / 60);
            break;
       case "s":    // seconds
            $diff = $seconds;
            break;
    }
    return $diff;
}

function ap_convertiData($dataEur){
$rsl = explode ('/',$dataEur);
$rsl = array_reverse($rsl);
return implode('-',$rsl);
}
function ap_VisualizzaData($dataDB){
	$dataDB=substr($dataDB,0,10);
	$rsl = explode ('-',$dataDB);
	$rsl = array_reverse($rsl);
	return implode('/',$rsl);
}
function ap_VisualizzaOra($dataDB){
return substr($dataDB,10);
}
/**
 * Funzione che calcola se la data in formato TimeStampa YYY-MM-DD è una data festiva Festa/Domenica
 */
function ap_IsDataFestiva($Data){
	$d_ex=explode("-", $Data);
	$anno=$d_ex[0];
	// creo un vettore con le festività  italiane
	$feste = Array(
		'01-01' => 'Capodanno', 
		'01-06' => 'Epifania', 
		'04-25' => 'Liberazione', 
		'05-01' => 'Festa Lavoratori', 
		'06-02' => 'Festa della Repubblica', 
		'08-15' => 'Ferragosto', 
		'11-01' => 'Tutti Santi', 
		'12-08' => 'Immacolata', 
		'12-25' => 'Natale', 
		'12-26' => 'Santo Stefano'
	); 
	
	// calcolo le date di Pasqua e Pasquetta
	$gg_pasqua = easter_days($anno);
	$gg_pasquetta = $gg_pasqua+1;
	$tmp = date('Y-m-d', strtotime('21 march ' . $anno));
	$data_pasqua = date('m-d', strtotime($tmp . ' +' . $gg_pasqua . 'day'));
	$data_pasquetta = date('m-d', strtotime($tmp . ' +' . $gg_pasquetta . 'day'));
	
	// aggiungo le date di Pasqua e Pasquetta nel nostro elenco di festività
	$feste[$data_pasqua] = 'Pasqua';
	$feste[$data_pasquetta] = 'Pasquetta';
//echo "<pre>";var_dump($gg_pasqua);var_dump($feste);echo "</pre>";
	$dataAnno=$d_ex[1]."-".$d_ex[2];
	foreach($feste as $D=>$Festa){
		if($D==$dataAnno)
			return TRUE;
	}
    $d_ts=mktime(0,0,0,$d_ex[1],$d_ex[2],$d_ex[0]);
    $num_gg=(int)date("N",$d_ts);
	if($num_gg==7)
		return TRUE;
	else
		return FALSE;
}
################################################################################
// Funzioni Log
################################################################################
/* 
Oggetto int(1)
	1=> Atti
	2=> Categorie
	3=> Allegati
	4=> Responsabili
	5=> Statistiche Visualizzazioni
	6=> Statistiche Download Allegati
	7=> Enti
	8=> Tipi di Files
	9=> Unità Organizzative
	
TipoOperazione int(1)
	1=> Inserimento
	2=> Modifica
	3=> Cancellazione
	4=> Pubblicazione
	5=> Incremento (solo per le statistiche)
	6=> Annullamento
*/
function ap_manutenzioneLogVisualizzazione(){
global $wpdb;
	$NumAnomalie=(int)($wpdb->get_var(
		"SELECT count(*) 
		 FROM $wpdb->table_name_Log 
		 WHERE $wpdb->table_name_Log.Oggetto=5 AND 
		       $wpdb->table_name_Log.TipoOperazione=5 AND 
		       $wpdb->table_name_Log.IdAtto=0;"));
	if(0!=$NumAnomalie)
		$wpdb->query(
			"UPDATE $wpdb->table_name_Log SET  $wpdb->table_name_Log.IdAtto= $wpdb->table_name_Log.IdOggetto 
			 WHERE  $wpdb->table_name_Log.TipoOperazione=5 And  
			        $wpdb->table_name_Log.Oggetto=5 And  
			        $wpdb->table_name_Log.IdAtto=0 ;");	
}		
		  
function ap_insert_log($Oggetto,$TipoOperazione,$IdOggetto,$Operazione,$IdAtto=0){
global $wpdb;
	if(get_option('opt_AP_LogOp')=="No" And ($Oggetto!=5 And $Oggetto!=6)){
	  		return;
    }	  
	if(get_option('opt_AP_LogAc')=="No" And ($Oggetto==5 Or $Oggetto==6)){
	  		return;
    }	    
    $current_user = wp_get_current_user();
	$wpdb->insert($wpdb->table_name_Log,array('IPAddress' => "",
	                                          'Utente' => $current_user->user_login,
											  'Oggetto' => $Oggetto,
										      'IdOggetto' => $IdOggetto,
											  'IdAtto' => $IdAtto,
											  'TipoOperazione' => $TipoOperazione,
											  'Operazione' => $Operazione),
										array('%s',
											  '%s',
											  '%s',
											  '%d',
											  '%d',
											  '%d',
											  '%s'));	
}

function ap_svuota_log($Tipo=0){
global $wpdb;
	if ($Tipo==0)
		$nr=$wpdb->query("DELETE FROM $wpdb->table_name_Log");
	else
		$nr=$wpdb->query("Delete FROM $wpdb->table_name_Log WHERE Oggetto<>5 and Oggetto<>6");
	return $nr;
}
function ap_get_all_Oggetto_log($Oggetto,$IdOggetto=0,$IdAtto=0){
global $wpdb;
	$condizione="WHERE Oggetto=". (int)$Oggetto;
	if ($IdOggetto!=0)
		$condizione.=" and IdOggetto=". (int)$IdOggetto ;
	if ($IdAtto!=0 and $IdOggetto!=0)
		$condizione.=" or IdAtto=".(int)$IdAtto;
	if ($IdAtto!=0 and $IdOggetto==0)
		$condizione.=" and IdAtto=".(int)$IdAtto;
//	echo "SELECT * FROM $wpdb->table_name_Log ".$condizione." order by Data;";
	return $wpdb->get_results("SELECT * FROM $wpdb->table_name_Log ".$condizione." order by Data DESC;");	
}

function ap_get_Stat_Visite($IdAtto){
global $wpdb;			
	return $wpdb->get_results("SELECT date( `Data` ) AS Data, TitoloAllegato, Allegato, count( `Data` ) AS Accessi
							   FROM $wpdb->table_name_Log
							   INNER JOIN $wpdb->table_name_Allegati 
							   	ON $wpdb->table_name_Log.`IdOggetto` = $wpdb->table_name_Allegati.IdAllegato
							   WHERE `Oggetto` =5
							   AND $wpdb->table_name_Allegati.`IdAtto` =". (int)$IdAtto."
							   GROUP BY date( `Data` ) , IdOggetto
							   ORDER BY Data DESC");	
}
function ap_get_Stat_VisiteRagg($IdAtto){
global $wpdb;		
	return $wpdb->get_results("SELECT date( `Data` ) AS Data, \" \", \" \", count( `Data` ) AS Accessi
							   FROM $wpdb->table_name_Log
							   WHERE `Oggetto` =5
							   AND $wpdb->table_name_Log.`IdAtto` =". (int)$IdAtto."
							   And $wpdb->table_name_Log.`IdAtto` =$wpdb->table_name_Log.`IdOggetto`
							   GROUP BY date( `Data` ) , IdOggetto
							   ORDER BY Data DESC");	
}
function ap_get_Stat_Num_log($IdAtto,$Oggetto){
global $wpdb;
	return (int)($wpdb->get_var( $wpdb->prepare( "SELECT COUNT(IdOggetto) FROM $wpdb->table_name_Log WHERE Oggetto = %d AND IdAtto = %d",(int) $Oggetto,(int)$IdAtto)));	
}

function ap_get_Stat_Download($IdAtto){
global $wpdb;
	return $wpdb->get_results("SELECT date( `Data` ) AS Data, TitoloAllegato, Allegato, count( `Data` ) AS Accessi
							   FROM $wpdb->table_name_Log
							   INNER JOIN $wpdb->table_name_Allegati 
							   	ON $wpdb->table_name_Log.`IdOggetto` = $wpdb->table_name_Allegati.IdAllegato
							   WHERE `Oggetto` =6
							   AND $wpdb->table_name_Allegati.`IdAtto` =". (int)$IdAtto."
							   GROUP BY date( `Data` ) , IdOggetto
							   ORDER BY Data DESC");	
}
function ap_get_Stat_Log($TipoInformazione){
global $wpdb;

switch ($TipoInformazione){
	case "Oggetto":
		$Sql="SELECT 
				CASE Oggetto
					WHEN 0 THEN 'Tutte le Tabelle'
					WHEN 1 THEN 'Atti'
					WHEN 2 THEN 'Categorie'
					WHEN 3 THEN 'Allegati'
					WHEN 4 THEN 'Responsabili'
					WHEN 5 THEN 'Statistiche Visualizzazioni'
					WHEN 6 THEN 'Statistiche Download Allegati'
					WHEN 7 THEN 'Enti'
					WHEN 8 THEN 'Tipi di files'
					WHEN 8 THEN 'Unità Organizzative'
				END as NomeOggetto, COUNT( * ) as Numero
				FROM $wpdb->table_name_Log
				GROUP BY Oggetto";
		break;	
	case "TipoOperazione":
		$Sql="SELECT 
				CASE TipoOperazione
					WHEN 0 THEN 'Tutte le Tabelle'
					WHEN 1 THEN 'Inserimento'
					WHEN 2 THEN 'Modifica'
					WHEN 3 THEN 'Cancellazione'
					WHEN 4 THEN 'Pubblicazione'
					WHEN 5 THEN 'Incremento (solo per le statistiche)'
					WHEN 6 THEN 'Annullamento'
					WHEN 7 THEN 'Svuotamento Tabella'
					WHEN 8 THEN 'Restore Dati'
					WHEN 9 THEN 'Allineamento Riga Allegato con File'
					WHEN 10 THEN 'Spostamento Allegati'
				END as NomeTipoOperazione, COUNT( * ) as Numero
				FROM $wpdb->table_name_Log
				GROUP BY TipoOperazione";
		break;	
}
	return $wpdb->get_results($Sql);
}

################################################################################
// Funzioni Meta Dati Atti
################################################################################
function ap_get_elenco_attimeta($Output="Array",$ID="listaAttiMeta",$Name="ListaAttiMeta",$ValUnici="No",$IDAtto=0,$ValUniciXValori=False){
	global $wpdb;
	$Rag="";
	if($ValUnici=="Si"){
		$Rag="GROUP BY $wpdb->table_name_Attimeta.Meta".($ValUniciXValori?", $wpdb->table_name_Attimeta.Value":"");
	}
	if($IDAtto==0){
		$Res=$wpdb->get_results("SELECT IdAtto, Meta, Value  FROM $wpdb->table_name_Attimeta $Rag;");	
	}else{
		$Res=$wpdb->get_results($wpdb->prepare("SELECT IdAtto, Meta, Value  FROM $wpdb->table_name_Attimeta WHERE IdAtto=%d $Rag;",$IDAtto));	
	}
//	echo $wpdb->last_query;die();
	switch ($Output){
		case "Array":
			return $Res;
			break;
		case "Select":
			$Lista="<select id=\"$ID\" name=\"$Name\">";
			foreach($Res as $Rec){
				$Lista.="<option value=\"$Rec->Meta\">$Rec->Meta</option>";
			}
			$Lista.="</select>";
			return $Lista;
			break;
		case "Div":
			$i=0;
			$Lista="";
			foreach($Res as $Rec){
				$Lista.="<div id=\"Meta[".$i."]\" class=\"meta\">
                    <blockquote>
                    <label for=\"newMetaName[".$i."]\">".__("Nome Meta","albo-online").": </label><input name=\"newMetaName[".$i."]\" id=\"newMetaName[".$i."]\" value=\"".$Rec->Meta."\"/>
                    <label for=\"newValue[".$i."]\">".__("Valore Meta","albo-online")."</label><input name=\"newValue[".$i."]\" id=\"newValue[".$i."]\" value=\"".$Rec->Value."\">
                    <button type=\"button\" class=\"setta-def-data EliminaRiga\">".__("Elimina riga","albo-online")."</button>
                    </blockquote>
                </div>";
				$i++;
			}
			return $Lista;
			break;
	}
}

function ap_get_GruppiAtti($meta,$value){
	global $wpdb;
	$Sql="SELECT $wpdb->table_name_Atti.IdAtto,LPAD($wpdb->table_name_Atti.Numero,7,0) as Numero,$wpdb->table_name_Atti.Anno,$wpdb->table_name_Atti.Data,$wpdb->table_name_Atti.Riferimento,$wpdb->table_name_Atti.Oggetto,$wpdb->table_name_Atti.DataInizio,$wpdb->table_name_Atti.DataFine,$wpdb->table_name_Atti.Informazioni,$wpdb->table_name_Atti.IdCategoria,$wpdb->table_name_Atti.RespProc,$wpdb->table_name_Atti.DataAnnullamento,$wpdb->table_name_Atti.MotivoAnnullamento,$wpdb->table_name_Atti.Ente,$wpdb->table_name_Atti.DataOblio,$wpdb->table_name_Atti.Soggetti FROM $wpdb->table_name_Atti INNER JOIN $wpdb->table_name_Attimeta ON"
	    . " $wpdb->table_name_Atti.IdAtto=$wpdb->table_name_Attimeta.IdAtto WHERE"
		. " $wpdb->table_name_Attimeta.meta=%s ".($value!=""?"And $wpdb->table_name_Attimeta.Value='%s'":"")." And $wpdb->table_name_Atti.Numero>0 ORDER BY Anno, Numero";
	$Sql=$wpdb->prepare($Sql,$meta,$value);
	$Res=$wpdb->get_results($Sql);
	return $Res;
}
function ap_get_meta_atto($IDAtto=0){
	global $wpdb;
	$Res=$wpdb->get_results("SELECT Meta,Value FROM $wpdb->table_name_Attimeta WHERE IdAtto=$IDAtto;");
	return $Res;
}
function ap_add_attimeta($IDAtto,$MetaName,$MetaValue){
	global $wpdb;
	$Sql="SELECT Value FROM $wpdb->table_name_Attimeta WHERE IdAtto=%d And Meta=%s;";
	$Res=$wpdb->get_results($wpdb->prepare($Sql,$IDAtto,$MetaName));
	if(count($Res)==0){
		if ( false === $wpdb->insert($wpdb->table_name_Attimeta,
										array('IdAtto' => $IDAtto,
											   'Meta'  => $MetaName,
											   'Value' => $MetaValue),
										array('%d','%s','%s')))
			return FALSE;
		else
			return TRUE;	
	}else{
		if ( $wpdb->update($wpdb->table_name_Attimeta,
								array('Value' => $MetaValue),
								array( 'IdAtto' => $IDAtto,
									   'Meta'   => $MetaName),
								array( '%s'),
								array( '%d','%s')))
				return TRUE;
			else
				return FALSE;
	}
}
function ap_update_attimeta($IDAtto,$MetaName,$MetaValue){
	global $wpdb;
	$Sql="SELECT Value FROM $wpdb->table_name_Attimeta WHERE IdAtto=%d And Meta=%s;";
	$Res=$wpdb->get_results($wpdb->prepare($Sql,$IDAtto,$MetaName));
	if(count($Res)==0){
		if ( false === $wpdb->insert($wpdb->table_name_Attimeta,
										array('IdAtto' => $IDAtto,
											   'Meta'  => $MetaName,
											   'Value' => $MetaValue),
										array('%d','%s','%s')))
			return FALSE;
		else
			return TRUE;		
	}
	if ( $wpdb->update($wpdb->table_name_Attimeta,
						array('Value' => $MetaValue),
						array( 'IdAtto' => $IDAtto,
							   'Meta'   => $MetaName),
						array( '%s'),
						array( '%d','%s')))
		return TRUE;
	else
		return FALSE;
	}
function ap_remove_metasatto($IDAtto,$MetaDati=""){
	global $wpdb;
	if( is_array( $MetaDati )){
		$AttiRimasti=implode("','",$MetaDati);
		$AttiRimasti="'".$AttiRimasti."'";
//		echo "SELECT Meta FROM $wpdb->table_name_Attimeta WHERE IdAtto=$IDAtto And Meta not in(".$AttiRimasti.");";die();
		$Res=$wpdb->get_results("SELECT Meta FROM $wpdb->table_name_Attimeta WHERE IdAtto=$IDAtto And Meta not in(".$AttiRimasti.");");		
		if($Res){
			foreach($Res as $Rs){
				$StatoRes=ap_delete_metaatto($IDAtto,$Rs->Meta);
			}
			return $StatoRes;
		}
	}else{
		$Sql="DELETE FROM $wpdb->table_name_Attimeta WHERE	IdAtto=%d";
		$Sql=$wpdb->prepare( $Sql,$IDAtto);
		$Res=$wpdb->query($Sql);
		if($Res!==FALSE And $Res==0){
			return TRUE;
		}else{
			return FALSE;
		}
	}
}
function ap_delete_metaatto($IDAtto,$MetaName){
	global $wpdb;
	$Sql="DELETE FROM $wpdb->table_name_Attimeta WHERE	IdAtto=%d And Meta=%s";
	$Sql=$wpdb->prepare( $Sql,$IDAtto,$MetaName);
	$Res=$wpdb->query($Sql);
	if($Res!==FALSE And $Res==0){
		return TRUE;
	}else{
		return FALSE;
	}
}
################################################################################
// Funzioni Categorie
################################################################################
function ap_get_num_categorie(){
	global $wpdb;
	return (int)($wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->table_name_Categorie"));
}

function ap_insert_categoria($cat_name,$cat_parente,$cat_descrizione,$cat_durata){
	global $wpdb;
	if ( false === $wpdb->insert($wpdb->table_name_Categorie,array('Nome' => stripslashes($cat_name),
																   'Genitore' => $cat_parente,
																   'Descrizione' => stripslashes($cat_descrizione),
																   'Giorni' => $cat_durata),
															 array('%s',
															 	   '%d',
															 	   '%s',
															 	   '%d')))	
        return new WP_Error('db_insert_error', __('Non sono riuscito ad inserire la Nuova Categoria','albo-online').$wpdb->last_error, $wpdb->last_error);
    else{
    	$NomeCategoria=ap_get_categoria($cat_parente);
    	if(is_array($NomeCategoria)and count($NomeCategoria)>0){
			$NomeCategoria=$NomeCategoria[0];
			$CatGenitore=$NomeCategoria->Nome;
		}else{
			$CatGenitore="Non Specificato";
		}
		ap_insert_log(2,1,$wpdb->insert_id,"{IdCategoria}==> $wpdb->insert_id
		                                    {".__("Nome","albo-online")."}==> $cat_name 
		                                    {".__("Descrizione","albo-online")."}==> $cat_descrizione 
											{".__("Durata","albo-online")."}==> $cat_durata
											{IdGenitore}==> $cat_parente
											{".__("Genitore","albo-online")."}==> $CatGenitore");
	}
}

function is_array_di_categorie($Categorie){
	$ArrCategorie=explode(",",$Categorie);
	$Esito=false;
	foreach($ArrCategorie as $Cate){
		if(count(ap_get_categoria($Cate))){
			$Esito=True;
		}else{
			return FALSE;
		}
	}
	return $Esito;
}
function ap_get_categorie(){
	global $wpdb;
	return $wpdb->get_results("SELECT DISTINCT * FROM $wpdb->table_name_Categorie;");
}
function ap_memo_categorie($id,$cat_name,$cat_parente,$cat_descrizione,$cat_durata){
	global $wpdb;
	$id=(int)$id;
	$cat_parente=(int)$cat_parente;
	$Categoria=ap_get_categoria($id);
	$Categoria=$Categoria[0];
	$Log='{Id}==>'.$id .' ' ;
	if ($Categoria->Nome!=$cat_name)
		$Log.='{'.__("Nome","albo-online").'}==> '.$cat_name.' ';
	if ($Categoria->Genitore!=$cat_parente){
		$Log.='{IdGenitore}==> '.$cat_parente.' ';
		$CategoriaPadre=ap_get_categoria($cat_parente);
		$CategoriaPadre=$CategoriaPadre[0];
		$Log.='{'.__("Genitore","albo-online").'}==> '.$CategoriaPadre->Nome.' ';
	}
	if ($Categoria->Descrizione!=$cat_descrizione)
		$Log.='{'.__("Descrizione","albo-online").'}==> '.$cat_descrizione.' ';
	if ($Categoria->Giorni!=$cat_durata)
		$Log.='{'.__("Giorni","albo-online").'}==> '.$cat_durata.' ';
	if ( false === $wpdb->update($wpdb->table_name_Categorie,
					array('Nome' => stripslashes($cat_name),
						  'Genitore' => $cat_parente,
						  'Descrizione' => stripslashes($cat_descrizione),
						  'Giorni' => $cat_durata),
						  array( 'IdCategoria' => $id ),
						  array('%s',
								'%d',
								'%s',
								'%d'),
						  array('%d')		 
						  ))
    	return new WP_Error('db_update_error', __('Non sono riuscito a modifire la Categoria','albo-online').$wpdb->last_error, $wpdb->last_error);
    else
    	ap_insert_log(2,2,$id,$Log);
	
}


function ap_get_dropdown_categorie($select_name,$id_name,$class,$tab_index_attribute, $default="Nessuna", $DefVisId=true, $ConAtti=false,$SceltaMultipla=FALSE  ) {
	global $wpdb;
	if ($ConAtti)
		$categorie = $wpdb->get_results("SELECT DISTINCT * FROM $wpdb->table_name_Categorie WHERE IdCategoria in (SELECT IdCategoria FROM wp_albopretorio_atti) GROUP BY `IdCategoria`ORDER BY nome;");	
	else
		$categorie = $wpdb->get_results("SELECT DISTINCT * FROM $wpdb->table_name_Categorie ORDER BY nome;");	
	$output = "<select name='$select_name' id='$id_name' class='$class' $tab_index_attribute ".($SceltaMultipla?'multiple size="7"':"").">\n";
	if ($default==__("Nessuno","albo-online")){
		$output .= "\t<option value='0' selected='selected'>".__("Nessuno","albo-online")."</option>\n";
	}else{
		$output .= "\t<option value='0' >".__("Nessuno","albo-online")."</option>\n";
	}
	if ( ! empty( $categorie ) ) {	
		foreach ($categorie as $c) {
			$output .= "\t<option value='$c->IdCategoria'";
			if ($c->IdCategoria==$default){
				$output .= " selected=\"selected\"";
			}
			if ($DefVisId)
				$output .=" >($c->IdCategoria) $c->Nome</option>\n";
			else
				$output .=" >$c->Nome</option>\n";
		}
	}
	$output .= "</select>\n";
	return $output;
}

function ap_num_atti_categoria($IdCategoria,$Stato=0){
/*
 $Stato 
 	0 tutti
 	1 attivi
 	2 storici
*/
	global $wpdb;
	$IdCategoria=(int)$IdCategoria;
	$Sql=$Sql="SELECT COUNT(*) FROM $wpdb->table_name_Atti WHERE IdCategoria=$IdCategoria";
	switch ($Stato){
		case 1:
			$Sql.=" And Numero >0 AND DataFine >= '".ap_oggi()."' AND DataInizio <= '".ap_oggi()."'";
			break;
		case 2:
			$Sql.=" And Numero >0 AND DataFine <= '".ap_oggi()."' And DataOblio> '".ap_oggi()."'";
			break;
	}
	$Sql.=";";
	return $wpdb->get_var($Sql);
	
}
function ap_get_dropdown_ricerca_categorie($select_name,$id_name,$class,$tab_index_attribute,$default="Nessuna",$Stato ) {
/*
 $Stato 
 	0 tutti
 	1 attivi
 	2 storici
*/
	global $wpdb;
	$categorie = $wpdb->get_results("SELECT DISTINCT * FROM $wpdb->table_name_Categorie ORDER BY nome;");	
	$output = "<select name='$select_name' id='$id_name' class='$class' $tab_index_attribute>\n";
	if ($default==__("Nessuno","albo-online")){
		$output .= "\t<option value='0' selected='selected'>".__("Nessuno","albo-online")."</option>\n";
	}else{
		$output .= "\t<option value='0' >".__("Nessuno","albo-online")."</option>\n";
	}
	if ( ! empty( $categorie ) ) {	
		foreach ($categorie as $c) {
			$numAtti=ap_num_atti_categoria($c->IdCategoria,$Stato);
			if ($numAtti){
				$output .= "\t<option value='$c->IdCategoria' ";
				if ($c->IdCategoria==$default){
					$output .= 'selected="selected" ';
				}
				$output .=">$c->Nome ($numAtti)</option>\n";
			}
		}
		$output .= "</select>\n";
	}
	return $output;
}

function ap_get_nuvola_categorie($link,$Stato ) {
/*
 $Stato 
 	0 tutti
 	1 attivi
 	2 storici
*/
	global $wpdb;
	$categorie = $wpdb->get_results("SELECT DISTINCT * FROM $wpdb->table_name_Categorie ORDER BY nome;");	
	if ( ! empty( $categorie ) ) {	
		$TotAtti=count(ap_get_all_atti($Stato));
		foreach ($categorie as $c) {
			$numAtti=ap_num_atti_categoria($c->IdCategoria,$Stato);
			if ($numAtti){
				$pix=(int) 1 + ($numAtti /$TotAtti);
				$output .= "<a href='".$link."=".$c->IdCategoria."' title=".__("Ci sono","albo-online")." ".$numAtti." ".__("Atti nella Categoria","albo-online")." ".$c->Nome."'><span style='font-size:".$pix."em;'>".$c->Nome."</span></a><br />\n";	
			}
				
		}
	}
	return $output;
}

function ap_get_categoria($id){
 	global $wpdb;
	return $wpdb->get_results("SELECT DISTINCT * FROM $wpdb->table_name_Categorie WHERE IdCategoria=".(int)$id.";");
}	
function ap_get_categorie_figlio($id, &$elenco, $livello){
	global $wpdb;
	$categorie_figlio = $wpdb->get_results("SELECT DISTINCT * FROM $wpdb->table_name_Categorie WHERE genitore=".(int)$id."  ORDER BY nome;");	
//		echo "SELECT DISTINCT * FROM $wpdb->table_name_Categorie WHERE IdCategoria=$id  ORDER BY nome;";
foreach ( $categorie_figlio as $cf ) 
{
//		echo "Id ".$cf->IdCategoria ."  Nome ". $cf->Nome. " <br />";
	if ($cf){
		array_push($elenco,array($cf->IdCategoria,$cf->Nome,$livello));
		if ($cf->Genitore>0){
		 	$livello+=1;
			ap_get_categorie_figlio($cf->IdCategoria,$elenco, $livello);
			$livello-=1;
		}
	}
}
}

function ap_get_categorie_gerarchica() {
	global $wpdb;
	$elenco = array();
	$categorie_primarie = $wpdb->get_results("SELECT DISTINCT * FROM $wpdb->table_name_Categorie WHERE genitore<1  ORDER BY Nome;");	
	foreach ($categorie_primarie as $cp) {
//		echo "Ci passo";
//		echo "Id ".$cp->IdCategoria ."  Nome ". $cp->Nome. " <br />";
		array_push($elenco,array($cp->IdCategoria,$cp->Nome,0));
		ap_get_categorie_figlio($cp->IdCategoria,$elenco, 1);
	}
	return $elenco;
}

function ap_del_categorie($id) {
	global $wpdb;
	$id=(int)$id;
	if ((ap_num_atti_categoria($id)>0) or (ap_num_figli_categorie($id)>0)){
		return array("atti" => ap_num_atti_categoria($id),
		             "figli" => ap_num_figli_categorie($id));
	}
	else{
	 	$wpdb->query($wpdb->prepare( "DELETE FROM $wpdb->table_name_Categorie WHERE	IdCategoria=%d",$id));
		ap_insert_log(2,3,$id,__("Cancellazione Categoria","albo-online"));

		return True;
	}
}
function ap_num_figli_categorie($id){
	global $wpdb;
	return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->table_name_Categorie WHERE Genitore=%d",$id));
	
}
function ap_num_categorie(){
	global $wpdb;
	return $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->table_name_Categorie");
	
}
function ap_num_categorie_inutilizzate(){
	global $wpdb;
	$Sql="SELECT count(*) 
	      FROM $wpdb->table_name_Categorie left join  $wpdb->table_name_Atti on 
		  		$wpdb->table_name_Atti.IdCategoria =  $wpdb->table_name_Categorie.IdCategoria 
		  WHERE $wpdb->table_name_Atti.IdAtto is null";
	return $wpdb->get_var($Sql);
}
function ap_num_categoria_atto($id){
	global $wpdb;
	$id=(int)$id;
	$Sql="SELECT count(*) 
	      FROM $wpdb->table_name_Atti  
		  WHERE $wpdb->table_name_Atti.IdCategoria =%d;";
	return $wpdb->get_var($wpdb->prepare($Sql,$id));
}
function ap_categorie_orfane(){
	global $wpdb;
	$Sql="SELECT LPAD($wpdb->table_name_Atti.Numero,7,0),$wpdb->table_name_Atti.Anno, $wpdb->table_name_Atti.IdCategoria 
	      FROM $wpdb->table_name_Atti left join  $wpdb->table_name_Categorie on 
		  		$wpdb->table_name_Atti.IdCategoria =  $wpdb->table_name_Categorie.IdCategoria 
		  WHERE $wpdb->table_name_Categorie.IdCategoria is null";
	return $wpdb->get_results($Sql);
}
################################################################################
// Funzioni Atti
################################################################################

function ap_setOblioOggi($IdAtto){
	global $wpdb;
	$DataOblio=date('Y-m-d');
	if ( $wpdb->update($wpdb->table_name_Atti,
			array('DataOblio' => $DataOblio),
			array( 'IdAtto' => $IdAtto),
			array( '%s'),
			array( '%d'))){						
	ap_insert_log(1,2,$IdAtto,"{Data Oblio}==> ".$DataOblio);
	$atto=ap_get_atto($IdAtto);
	$atto=$atto[0];
	return "Data Oblio dell'Atto:".$atto->Numero."/".$atto->Anno." Impostata ad oggi:".date('d/m/Y');	}
}
function ap_AnniAtti(){
	global $wpdb;
	$Sql="SELECT Anno FROM $wpdb->table_name_Atti where Numero<>0 Group by Anno;";
	$Anni = $wpdb->get_results($Sql);
	if (count($Anni)==0)
		return FALSE;
	else
		return $Anni;
}

function ap_Repertorio($Anno,$Echo=TRUE){
	global $wpdb;
	$Docu="";
	$Sql="SELECT $wpdb->table_name_Enti.Nome as NomeEnte,LPAD(Numero,7,0) as Numero,Anno,Riferimento,Oggetto,DataInizio,DataFine, DataAnnullamento, MotivoAnnullamento,Richiedente,$wpdb->table_name_UO.Nome as UnitaOrganizzativa,CONCAT($wpdb->table_name_RespProc.Nome, \" \",$wpdb->table_name_RespProc.Cognome) as ResponsabileProcedimento,$wpdb->table_name_Categorie.Nome as Categoria,Informazioni
		FROM $wpdb->table_name_Atti inner join $wpdb->table_name_Categorie on ($wpdb->table_name_Atti.IdCategoria =$wpdb->table_name_Categorie.IdCategoria) inner join $wpdb->table_name_Enti on ($wpdb->table_name_Atti.Ente=$wpdb->table_name_Enti.IdEnte) left join $wpdb->table_name_UO on ($wpdb->table_name_Atti.IdUnitaOrganizzativa =$wpdb->table_name_UO.IdUO) left join $wpdb->table_name_RespProc on ($wpdb->table_name_Atti.RespProc =$wpdb->table_name_RespProc.IdResponsabile)
		WHERE Anno=$Anno And Numero>0
		ORDER By Numero";
	$Atti = $wpdb->get_results($Sql);
	if (count($Atti)!=0){
		if($Echo){
			foreach($Atti as $Atto){
				if($Atto->DataAnnullamento!='0000-00-00')
					$Annullato='style="background-color: '.get_option('opt_AP_ColoreAnnullati').';"';
				else
					$Annullato='';
				$Docu.= "
				<tr>
					<td>".stripcslashes($Atto->NomeEnte)."</td>
					<td>$Atto->Numero</td>
					<td>".wp_strip_all_tags( $Atto->Riferimento)."</td>
					<td>".wp_strip_all_tags($Atto->Oggetto)."</td>
					<td>$Atto->DataInizio</td>
					<td>$Atto->DataFine</td>
					<td $Annullato>$Atto->DataAnnullamento</td>
					<td $Annullato>".wp_strip_all_tags($Atto->MotivoAnnullamento)."</td>
					<td>$Atto->Richiedente</td>
					<td>$Atto->UnitaOrganizzativa</td>
					<td>$Atto->ResponsabileProcedimento</td>
					<td>$Atto->Categoria</td>
					<td>".wp_strip_all_tags($Atto->Informazioni)."</td>
				</tr>";
			}			
		}else{
			return $Atti;
		}
	}
return $Docu;
}
function ap_SetDefaultDataScadenza(){
	global $wpdb;
	$Sql="SELECT IdAtto, DataOblio,DataFine FROM $wpdb->table_name_Atti;";
	$Atti = $wpdb->get_results($Sql);
	foreach ($Atti as $Atto){
		if ($Atto->DataOblio=="0000-00-00"){
			$DataOblio=ap_DateAdd($Atto->DataFine ,1825);
			if ( $wpdb->update($wpdb->table_name_Atti,
						array('DataOblio' => $DataOblio),
						array( 'IdAtto' => $Atto->IdAtto),
						array( '%s'),
						array( '%d'))){						
				ap_insert_log(1,2,$Atto->IdAtto,"{Data Oblio}==> ".$DataOblio);
			}		
		}			
	}
}
function ap_insert_atto($Ente,$Data,$Riferimento,$Oggetto,$DataInizio,$DataFine,$DataOblio,$Note,$Categoria,$Responsabile,$Soggetti,$IdUI,$Richiedente){
	global $wpdb;
	$Anno=date("Y");
	$Numero=0;
	$Data=ap_convertiData($Data);
	$DataInizio=ap_convertiData($DataInizio);
	$DataFine=ap_convertiData($DataFine);
	$DataOblio=ap_convertiData($DataOblio);
	if(!$Responsabile){
		$Responsabile=0;
	}
	if ( false === $wpdb->insert(
		$wpdb->table_name_Atti,array(
				'Ente' 					=> $Ente,
				'Numero' 				=> $Numero,
				'Anno' 					=>  $Anno,
				'Data' 					=> $Data,
				'Riferimento' 			=> stripslashes($Riferimento),
				'Oggetto' 				=> stripslashes($Oggetto),
				'DataInizio' 			=> $DataInizio,
				'DataFine' 				=> $DataFine,
				'DataOblio' 			=> $DataOblio,
				'Informazioni' 			=> $Note,
				'IdCategoria' 			=> $Categoria,
				'RespProc' 				=> $Responsabile,
				'Soggetti' 				=> $Soggetti,
				'IdUnitaOrganizzativa'	=> $IdUI,
				'Richiedente'			=> $Richiedente),
								array(
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%s',
				'%d',
				'%s')))	{
// echo "Sql==".$wpdb->last_query ."    Ultimo errore==".$wpdb->last_error;exit;
          return sprintf(__("Non sono riuscito ad inserire il nuovo Atto Sql==%s Ultimo errore==%s","albo-online"),$wpdb->last_query,$wpdb->last_error);
    }else{
		$newIDAtto=$wpdb->insert_id;
//    	echo "Sql==".$wpdb->last_query;exit;
	  	$NomeCategoria=ap_get_categoria($Categoria);
    	$NomeCategoria=$NomeCategoria[0];
		$NomeResponsabile=ap_get_responsabile($Responsabile);
		$NomeResponsabile=$NomeResponsabile[0];
		$NomeEnte=ap_get_ente($Ente);
		$NomeEnte=$NomeEnte->Nome;
		$Responsabili="";
		$NomeUO=ap_get_unitaorganizzativa($IdUI);
		$NomeResp=ap_get_responsabile($Responsabile);
		if (count($NomeResp)>0)
			$NomeResp=$NomeResp[0]->Nome." ".$NomeResp[0]->Cognome;
		else
			$NomeResp=__("Non Definito","albo-online");
		$Sogs=unserialize($Soggetti);
		foreach($Sogs as $Soggetto){
			$NomeResponsabile=ap_get_responsabile($Soggetto);
			$Responsabili.="(".$Soggetto.") ".$NomeResponsabile[0]->Nome." ".$NomeResponsabile[0]->Cognome." <strong>".ap_get_Funzione_Responsabile($NomeResponsabile[0]->Funzione,"Descrizione")."</strong> ";
		}
		ap_insert_log(1,1,$wpdb->insert_id,"{IdAtto}==> $wpdb->insert_id
											{IdEnte} $Ente
											{".__("Ente","albo-online")."} $NomeEnte
											{".__("Numero","albo-online")."} $Numero/$Anno 
											{".__("Data di registrazione","albo-online")."}==> $Data 
						                    {".__("Riferimento","albo-online")."}==> $Riferimento 
											{".__("Oggetto","albo-online")."}==> $Oggetto 
											{IdOggetto}==> $wpdb->insert_id
											{".__("Data Inizio","albo-online")."}==> $DataInizio
											{".__("Data Fine","albo-online")."}==> $DataFine
											{".__("Data Oblio","albo-online")."}==> $DataOblio
											{".__("Note","albo-online")."}=> $Note
											{".__("Categoria","albo-online")."}==> $NomeCategoria->Nome
											{".__("Unita Organizzativa Responsabile","albo-online")."}==> ($IdUI) $NomeUO->Nome 
											{".__("Responsabile del procedimento amministrativo","albo-online")."}==> $NomeResp
											{IdCategoria}==> $Categoria
											{".__("Soggetti","albo-online")."}==> $Responsabili
											{".__("Richiedente","albo-online")."}==>$Richiedente"
							  );
		return $newIDAtto;
	}
}
function ap_del_atto($id) {
	global $wpdb;
	$N_allegati=ap_num_allegati_atto($id);
	if ($N_allegati>0){
		return array("allegati" => $N_allegati);
	}
	else{
	 	$wpdb->query($wpdb->prepare( "DELETE FROM $wpdb->table_name_Atti WHERE	IdAtto=%d",$id));
		ap_insert_log(1,3,$id,__("Cancellazione Atto","albo-online"),(int)$id);
		return True;
	}
}

function ap_memo_atto($id,$Ente,$Data,$Riferimento,$Oggetto,$DataInizio,$DataFine,$DataOblio,$Note,$Categoria,$Responsabile,$Soggetti,$IdUI,$Richiedente){
	global $wpdb;
	$Atto=ap_get_atto($id);
	$Atto=$Atto[0];
//	$Soggetti=serialize($Soggetti);
	$Data=ap_convertiData($Data);
	$DataInizio=ap_convertiData($DataInizio);
	$DataFine=ap_convertiData($DataFine);
	$DataOblio=ap_convertiData($DataOblio);
	$Oggetto=stripslashes($Oggetto);
	$Riferimento=stripslashes($Riferimento);
	$Log='' ;
	if ($Atto->Ente!=$Ente){
    	$NEnte=ap_get_ente($Ente);
		$Log.='{IdEnte}==> '.$Ente.' ';
		$Log.='{'.__("Ente","albo-online").'}==> '.$NEnte->Nome.' ';		
	}
	if ($Atto->Data!=$Data)
		$Log.='{'.__("Data di registrazione","albo-online").'}==> '.$Data.' ';
	if ($Atto->Riferimento!=$Riferimento)
		$Log.='{'.__("Riferimento","albo-online").'}==> '.$Riferimento.' ';
	if ($Atto->Oggetto!=$Oggetto)
		$Log.='{'.__("Oggetto","albo-online").'}==> '.$Oggetto.' ';
	if ($Atto->DataInizio!=$DataInizio)
		$Log.='{'.__("Data Inizio","albo-online").'}==> '.$DataInizio.' ';
	if ($Atto->DataFine!=$DataFine)
		$Log.='{'.__("Data Fine","albo-online").'}==> '.$DataFine.' ';
	if ($Atto->DataOblio!=$DataOblio)
		$Log.='{'.__("Data Oblio","albo-online").'}==> '.$DataOblio.' ';
	if ($Atto->Informazioni!=$Note)
		$Log.='{'.__("Informazioni","albo-online").'}==> '.$Note.' ';
	if ($Atto->IdCategoria!=$Categoria){
    	$NomeCategoria=ap_get_categoria($Categoria);
    	$NomeCategoria=$NomeCategoria[0];
		$Log.='{IdCategoria}==> '.$Categoria.' ';
		$Log.='{'.__("Categoria","albo-online").'}==> '.$NomeCategoria->Nome.' ';
	}
	if($Atto->Soggetti!=$Soggetti){
		$Responsabili="";
		$Sogs=unserialize($Soggetti);
		foreach($Sogs as $Soggetto){
			$NomeResponsabile=ap_get_responsabile($Soggetto);
			$Responsabili.="(".$Soggetto.") ".$NomeResponsabile[0]->Nome." ".$NomeResponsabile[0]->Cognome." <strong>".ap_get_Funzione_Responsabile($NomeResponsabile[0]->Funzione,"Descrizione")."</strong> ";
		}
		$Log.='{'.__("Soggetti","albo-online").'}==> '.$Responsabili.' ';
	}
	if ($Atto->IdUnitaOrganizzativa!=$IdUI){
		$NomeUO=ap_get_unitaorganizzativa($IdUI);
		$Log.='{'.__("Unita Organizzativa Responsabile","albo-online").'}==> ('.$IdUI.') '.$NomeUO->Nome;
	}
	if ($Atto->RespProc!=$Responsabile){
		$NomeResp=ap_get_responsabile($Responsabile);
		$NomeResp=$NomeResp[0];
		$Log.='{'.__("Responsabile del procedimento amministrativo","albo-online").'}==> '.$NomeResp->Nome." ".$NomeResp->Cognome;
	}
	if ($Atto->Richiedente!=$Richiedente)
		$Log.='{Richiedente}==> '.$Richiedente.' ';
	if ( false === $wpdb->update($wpdb->table_name_Atti,
					array('Ente' 				=> $Ente,
						  'Data' 				=> $Data,
						  'Riferimento' 		=> $Riferimento,
						  'Oggetto' 			=> $Oggetto,
						  'DataInizio' 			=> $DataInizio,
						  'DataFine' 			=> $DataFine,
						  'DataOblio' 			=> $DataOblio,
						  'Informazioni' 		=> $Note,
						  'IdCategoria' 		=> $Categoria,
						  'RespProc' 			=> $Responsabile,
						  'Soggetti' 			=> $Soggetti,
						  'IdUnitaOrganizzativa'=> $IdUI,
						  'Richiedente'			=> $Richiedente),
						  array( 'IdAtto' => $id ),
						  array('%d',
						        '%s',
								'%s',
								'%s',
								'%s',
								'%s',							
								'%s',
								'%s',
								'%d',
								'%d',
								'%s',
								'%d',
								'%s'),
						  array('%d')))
    	return new WP_Error('db_update_error', __("Non sono riuscito a modificare l'Atto","albo-online").$wpdb->last_error, $wpdb->last_error);
    else
    	ap_insert_log(1,2,$id,$Log);
}

function ap_update_selettivo_atto($id,$ArrayCampiValori,$ArrayTipi,$TestaMsg){
	global $wpdb;
	if ( false === $wpdb->update($wpdb->table_name_Atti,$ArrayCampiValori,array( 'IdAtto' => $id ),$ArrayTipi))
    	return new WP_Error('db_update_error', __("Non sono riuscito a modificare l'Atto","albo-online") .$wpdb->last_error, $wpdb->last_error);
    else{
		ap_insert_log(1,2,(int)$id,$TestaMsg.ap_ListaElementiArray($ArrayCampiValori));
		return __('Atto Aggiornato','albo-online').': %%br%%'.ap_ListaElementiArray($ArrayCampiValori);	
	}
}

function ap_approva_atto($IdAtto){
	global $wpdb;
	$IdAtto=(int)$IdAtto;
	$NumeroDaDb=ap_get_last_num_anno(date("Y"));
	$risultato=ap_get_atto($IdAtto);
	$risultato=$risultato[0];
	$NumeroOpzione=get_option('opt_AP_NumeroProgressivo');
	if($risultato->Numero!=0)
		return __("Atto gia' PUBBLICATO con Numero Progressivo ","albo-online").$risultato->Numero;
	if (($NumeroDaDb!=$NumeroOpzione) And ap_get_all_atti(9,0,0,0,"",0,0,"",0,0,TRUE)>0){
		return __("Atto non PUBBLICATO","albo-online").":%%br%%".__("Progressivo da ultima pubblicazione","albo-online")."=$NumeroDaDb%%br%% ".__("Progressivo da parametri","albo-online")."=$NumeroOpzione";
	}else{
		$x=$wpdb->update($wpdb->table_name_Atti,
									 array('Numero' => $NumeroOpzione),
									 array( 'IdAtto' => $IdAtto ),
									 array('%d'),
									 array('%d'));
	//  visualizza Sql Updateecho $wpdb->print_error();exit;
	 	if ($x==0){
	    	return __("Atto non PUBBLICATO","albo-online").':%%br%%'.__("Errore","albo-online").': '.$wpdb->last_error;
	    }
	    else{
			ap_insert_log( 1,4,$IdAtto,"{".__("Stato Atto","albo-online")."}==> ".__("Pubblicato","albo-online")." 
			 							{".__("Numero Assegnato","albo-online")."}==> $NumeroOpzione ");	
			$NumeroOpzione+=1;
			update_option('opt_AP_NumeroProgressivo',$NumeroOpzione );
			return __("Atto PUBBLICATO","albo-online");
		}
	}
}

function ap_annulla_atto($IdAtto,$Motivo,$Allegati=array()){
	global $wpdb;
	$IdAtto=(int)$IdAtto;
//	$risultato=ap_get_atto($IdAtto);
//	$risultato=$risultato[0];
	$Sql = "UPDATE `$wpdb->table_name_Atti` SET DataAnnullamento='".date('Y-m-d')."', MotivoAnnullamento=%s  WHERE IdAtto=%d";
	$Sql=$wpdb->prepare($Sql,array($Motivo,$IdAtto));
	$Result=$wpdb->query($Sql);
//	echo "Sql==".$wpdb->last_query ."    Ultimo errore==".$wpdb->last_error;exit;
	if($Result){
		ap_insert_log(1,6,$IdAtto,"{".__("Stato Atto","albo-online")."}==> ".__("Annullato","albo-online"));
		if (!empty($Allegati))
			foreach($Allegati as $Allegato)
				ap_del_allegato_atto($Allegato,$IdAtto,"","S");
		return 9;
	}else{
	return 8;				
	}
}

function ap_get_dropdown_anni_atti($select_name,$id_name,$class,$tab_index_attribute, $default="xxxx",$Stato=0) {
/*
 $Stato 
 	0 tutti
 	1 attivi
 	2 storici
*/
	global $wpdb;
	if($default=="xxxx") $default=__("Nessuno","albo-online");
	switch ($Stato){
		case 1:
			$Sql="SELECT Anno FROM $wpdb->table_name_Atti WHERE Numero >0 AND DataFine >= '".ap_oggi()."' AND DataInizio <= '".ap_oggi()."' GROUP BY Anno;";
			break;
		case 2:
			$Sql="SELECT Anno FROM $wpdb->table_name_Atti WHERE Numero >0 AND DataFine < '".ap_oggi()."' GROUP BY Anno;";
			break;
		default:
			$Sql="SELECT Anno FROM $wpdb->table_name_Atti GROUP BY Anno;";
			break;
	}
	$anni = $wpdb->get_results($Sql);	
	$output = "<select name='$select_name' id='$id_name' class='$class' $tab_index_attribute>\n";
	if ($default==__("Nessuno","albo-online")){
		$output .= "\t<option value='0' selected='selected'>".__("Nessuno","albo-online")."</option>\n";
	}else{
		$output .= "\t<option value='0' >".__("Nessuno","albo-online")."</option>\n";
	}
	if ( ! empty( $anni ) ) {	
		foreach ($anni as $c) {
			$output .= "\t<option value='$c->Anno'";
			if ($c->Anno==$default){
				$output .= " selected=\"selected\"";
			}
			$output .=" >$c->Anno</option>\n";
		}
	}
	$output .= "</select>\n";
	return $output;
}

function ap_get_last_num_anno($Anno){
	global $wpdb;
	return (int)($wpdb->get_var( $wpdb->prepare( "SELECT MAX(Numero) FROM $wpdb->table_name_Atti WHERE Anno=%d",(int)$Anno)))+1;
}
function ap_get_num_anno($IdAtto){
	global $wpdb;
	return ($wpdb->get_var( $wpdb->prepare( "SELECT LPAD(Numero,7,0) as Numero FROM $wpdb->table_name_Atti WHERE IdAtto=%d",$IdAtto)));
}

function ap_get_all_atti($Stato=0,$Numero=0,$Anno=0,$Categoria=0,$Oggetto='',$Dadata=0,$Adata=0,$OrderBy="",$DaRiga=0,$ARiga=20,$Conteggio=false,$Annullati=false,$Riferimento='',$Ente=-1,$SenzaAnnullati=FALSE){
/* Stato:
		 0 - tutti
		 1 - in corso di validit�
		 2 - scaduti
		 3 - da pubblicare
		 4 - da cancellare
		 5 - cerca
		 9 - tutti tranne quelli da pubblicare
		 10- pullicati e scaduti in itervallo di date
	$Conteggio:
		 false - Estrazione Dati	
		 true - Conteggio
*/	
	global $wpdb;
	$Selezione="";
	if ($OrderBy!=""){
		$OrderBy=" Order By ".$OrderBy;
	}
	if ($DaRiga==0 AND $ARiga==0)
		$Limite="";
	else
		$Limite=" Limit ".$DaRiga.",".$ARiga;
	
	switch ($Stato){
		case 0:
			$Selezione=' WHERE 1';
			break;
		case 9:
			$Selezione=' WHERE Numero<>0';
			break;
		case 10:
				if ($Adata!=0 )
					$Selezione.=' WHERE DataInizio<="'.ap_convertiData($Adata).'" ';
				if ($Dadata!=0 )
					$Selezione.=' AND DataFine>="'.ap_convertiData($Dadata);
				$Selezione.='" AND Numero<>0 '; 
				break;
		case 1:
			if ($Dadata!=0 and ap_SeDate("<",ap_convertiData($Dadata),ap_oggi()))
				$Selezione.=' WHERE DataInizio>="'.ap_convertiData($Dadata).'" ';
			else
				$Selezione.=' WHERE DataInizio<="'.ap_oggi().'" ';
			if ($Adata!=0  and ap_SeDate(">",ap_convertiData($Adata),ap_oggi()))
				$Selezione.=' AND DataFine<="'.ap_convertiData($Adata).'" And DataFine>="'.ap_oggi();
			else
				$Selezione.=' AND DataFine>="'.ap_oggi();
			$Selezione.='" AND Numero>0'; 
			break;
		case 2:
			if ($Dadata!=0  and ap_SeDate("<",ap_convertiData($Dadata),ap_oggi()))
				$Selezione.=' WHERE DataInizio>="'.ap_convertiData($Dadata).'" ';
			else
				$Selezione.=' WHERE DataInizio<="'.ap_oggi().'" ';
			if ($Adata!=0   and ap_SeDate("<",ap_convertiData($Adata),ap_oggi()))
				$Selezione.=' AND DataFine<"'.ap_convertiData($Adata);
			else
				$Selezione.=' AND DataFine<"'.ap_oggi();
			$Selezione.='" AND Numero>0 And DataOblio>"'.ap_oggi().'" '; 
			break;
		case 3:
			$Selezione=' WHERE Numero=0'; 
			break;
		case 4:
			$Selezione.=' WHERE DataOblio<="'.ap_oggi().'" ';
			$Selezione.=' AND Numero>0'; 
			break;	
		case 5:                 
            $Selezione.=' WHERE  Oggetto like "%'.(isset($_REQUEST['s'])?$_REQUEST['s']:"").'%"';
			break;
		}
	if ($Annullati)
		$Selezione.=' And DataAnnullamento<>"0000-00-00"';
	elseif($SenzaAnnullati)
		$Selezione.=' And DataAnnullamento="0000-00-00"';
	if ($Anno!=0){
		if ($Numero!=0){
			$Selezione.=' And (Anno="'.$Anno.'" And Numero="'.$Numero.'")';
		}else{
			$Selezione.=' And Anno="'.$Anno.'"';
		}
	}else{
		if ($Numero!=0){
			$Selezione.=' And Numero="'.$Numero.'"';
		}
	}
	if (is_array($Categoria) Or $Categoria!=0){
		$Categs="(";
		if (is_array($Categoria)){
			foreach($Categoria as $Cate){
				$Categs.=$Cate.",";
			}
			$Categs=substr($Categs,0, strlen($Categs)-1).")";
			$Selezione.=' And IdCategoria in '.$Categs;
		}else{
			$Selezione.=' And IdCategoria="'.$Categoria.'"';
		}
	}
	if ($Oggetto!='')
		$Selezione.=' And Oggetto like "%'.$Oggetto.'%"';
	if ($Ente!=-1)
		$Selezione.=' And Ente ="'.$Ente.'"';
	if ($Riferimento!='')
		$Selezione.=' And Riferimento like "%'.$Riferimento.'%"';
	
//echo "<BR /><BR />SELECT COUNT(*) FROM $wpdb->table_name_Atti $Selezione;";
//echo $Stato." ->SELECT * FROM $wpdb->table_name_Atti $Selezione $OrderBy $Limite;<br />";
	if ($Conteggio){
		return $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->table_name_Atti $Selezione;");	
	}else{
		return $wpdb->get_results("SELECT IdAtto,LPAD(Numero,7,0) as Numero,Anno,Data,Riferimento,Oggetto,DataInizio,DataFine,Informazioni,IdCategoria,RespProc,DataAnnullamento,MotivoAnnullamento,Ente,DataOblio,Soggetti,IdUnitaOrganizzativa,Richiedente FROM $wpdb->table_name_Atti $Selezione $OrderBy $Limite;");	
	}
	
}	

function ap_get_atto($id){
	global $wpdb;
	$id=(int)$id;
	return $wpdb->get_results("SELECT IdAtto,LPAD(Numero,7,0) as Numero,Anno,Data,Riferimento,Oggetto,DataInizio,DataFine,Informazioni,IdCategoria,RespProc,DataAnnullamento,MotivoAnnullamento,Ente,DataOblio,Soggetti,IdUnitaOrganizzativa, Richiedente FROM $wpdb->table_name_Atti Where IdAtto=$id;");
}	

function ap_is_atto_corrente($id){
	$atto=ap_get_atto($id);
	$atto=$atto[0];
	if(ap_sedate(">",$atto->DataFine,ap_oggi())){
		return FALSE;
	}else{
		return TRUE;
	}
}
function ap_get_lista_atti($select_name,$id_name,$class,$tab_index_attribute,$default="xxxx",$Stato=0,$Style="") {
	global $wpdb;
	if($default=="xxxx") $default=__("Nessuno","albo-online");
	$atti =ap_get_all_atti( $Stato,0,0,0,'',0,0,"Numero Desc");
	$output = "<select name='$select_name' id='$id_name' class='$class' $tab_index_attribute style='$Style'>\n";
	if ($default==__("Nessuno","albo-online")){
		$output .= "\t<option value='0' selected='selected'>".__("Nessuno","albo-online")."</option>\n";
	}else{
		$output .= "\t<option value='0' >".__("Nessuno","albo-online")."</option>\n";
	}
	if ( ! empty( $atti ) ) {	
		foreach ($atti as $a) {
			$output .= "\t<option value='$a->IdAtto'";
			if ($a->IdAtto==$default){
				$output .= " selected='selected'";
			}
			$output .=" >($a->IdAtto) $a->Oggetto del $a->Numero/$a->Anno </option>\n";
		}
	}
	$output .= "</select>\n";
	return $output;
}
function ap_get_dropdown_atti($select_name,$id_name,$class,$tab_index_attribute,$default="xxxx") {
	global $wpdb;
	if($default=="xxxx") $default=__("Nessuno","albo-online");
	$atti =ap_get_all_atti( 0,0,0,0,'',0,0,"Numero Desc");
	$output = "<select name='$select_name' id='$id_name' class='$class' $tab_index_attribute>\n";
	if ($default==__("Nessuno","albo-online")){
		$output .= "\t<option value='0' selected='selected'>".__("Nessuno","albo-online")."</option>\n";
	}else{
		$output .= "\t<option value='0' >".__("Nessuno","albo-online")."</option>\n";
	}
	if ( ! empty( $atti ) ) {	
		foreach ($atti as $a) {
			$output .= "\t<option value='$a->IdAtto'";
			if ($a->IdAtto==$default){
				$output .= " selected='selected'";
			}
			$output .=" >($a->IdAtto) $a->Nome del $a->Numero/$a->Anno </option>\n";
		}
	}
	$output .= "</select>\n";
	return $output;
}

function ap_ripubblica_atti_correnti($ArrayAtti){
	global $wpdb;
	$NumAggiornamenti=0;
/*	echo "<pre>";
		var_dump($ArrayAtti);
	echo "</pre>";
	wp_die();
*/	foreach($ArrayAtti as $IDAtto => $Ngg){
		$Atto=ap_get_atto($IDAtto);
		$Atto=$Atto[0];
		$DataFine=ap_DateAdd($Atto->DataFine,$Ngg);
		if(strlen($Atto->Informazioni)>0)
			$Informazioni=$Atto->Informazioni.sprintf(__('
			Data Scadenza Atto prolungata di %d giorni a causa di una interruzione del servizio di pubblicazione. Data Originale:%s - Data Aggiornata:%s','albo-online'),$Ngg,$Atto->DataFine,$DataFine);
		else
			$Informazioni=sprintf(__('Data Scadenza Atto prolungata di %d giorni a causa di una interruzione del servizio di pubblicazione. Data Originale:%s - Data Aggiornata:%s','albo-online'),$Ngg,$Atto->DataFine,$DataFine);
//		$SqlAttoDaProlungare='UPDATE '.$wpdb->table_name_Atti.' SET DataFine='.$DataFine.', Informazioni="'.$Informazioni.'" WHERE IdAtto='.$IDAtto.';';
		
		if ($num=$wpdb->update($wpdb->table_name_Atti,
					array('DataFine' 	=> $DataFine,
						  'Informazioni'=> $Informazioni),
						  array( 'IdAtto' 	=> $IDAtto),
						  array('%s','%s'),
						  array('%d')))
			$NumAggiornamenti+=$num;
//		echo $SqlAttoDaProlungare."<br />";
		ap_insert_log(1,1,$IDAtto,"{IdAtto}==> $IDAtto
								   {".__("Informazioni","albo-online")."}==>$Informazioni");	
	}
	return $NumAggiornamenti;
}

################################################################################
// Funzioni Allegati
################################################################################

function ap_get_allegati_file_scollegati($TipoRet="Array",$select_name="AllegatiSpuri",$id_name="AllegatiSpuri",$class=""){
	global $wpdb;
	$Sql="SELECT $wpdb->table_name_Allegati.Allegato
		  FROM $wpdb->table_name_Allegati";
	$Records=$wpdb->get_results($Sql);
	$Dir=str_replace("\\","/",AP_BASE_DIR.get_option('opt_AP_FolderUpload'));
	$iterator = new RecursiveIteratorIterator(
			    new RecursiveDirectoryIterator($Dir));
			// Ciclo tutti gli elementi dell'iteratore, i files estratti dall'iteratore
	$AllegatiF=array();
	$TipiAmmessi=ap_tipiFileAmmessi();
	foreach ($iterator as $key=>$value) {
		$File = pathinfo($key);
		$name = $File['filename'];
		$ext = $File['extension'];
		if(substr($name,0,1)!="." And (is_array($TipiAmmessi) And in_array($ext,$TipiAmmessi)))
			$AllegatiF[]=$key;
	}
	$AllegatiDB=array();
	foreach($Records as $Record){
		if(is_file($Record->Allegato)) $AllegatiDB[]=basename($Record->Allegato);
	}
	$NonAssegnati=array_diff($AllegatiF,$AllegatiDB);
	if($TipoRet=="Array"){
		return $NonAssegnati;
	}
	$output = "<select name='$select_name' id='$id_name' class='$class'>\n";
	if ( ! empty( $NonAssegnati ) ) {	
		foreach ($NonAssegnati as $a) {
			$output .= "\t<option value='$a'>".basename($a)."</option>\n";
		}
	}
	$output .= "</select>\n";
	return $output; 
}

function ap_CalcImpronta($IDAllegato=0,$FileName=""){
	if ($IDAllegato!=""){
		$Allegato=ap_get_allegato_atto($IDAllegato);
		$FileName=$Allegato[0]->Allegato;
	}
	if (is_file($FileName)) {
		return hash_file("sha256", $FileName);
	}
	return FALSE;
}

function ap_get_num_allegati($id){
	global $wpdb;
	return (int)($wpdb->get_var( $wpdb->prepare( "SELECT COUNT(IdAllegato) FROM $wpdb->table_name_Allegati WHERE IdAtto=%d",(int)$id)));
}

function ap_allegati_orfani(){
	global $wpdb;
	$Sql="SELECT $wpdb->table_name_Allegati.IdAllegato, $wpdb->table_name_Allegati.TitoloAllegato, $wpdb->table_name_Allegati.IdAtto
		  FROM $wpdb->table_name_Allegati
			LEFT JOIN $wpdb->table_name_Atti ON $wpdb->table_name_Atti.IdAtto = $wpdb->table_name_Allegati.IdAtto
		  WHERE $wpdb->table_name_Atti.IdAtto IS NULL 
		  ORDER BY $wpdb->table_name_Allegati.IdAtto";
	return $wpdb->get_results($Sql);
}
function ap_num_allegati(){
	global $wpdb;
	return $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->table_name_Allegati");
	
}
function ap_num_allegati_atto($id){
	global $wpdb;
	return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->table_name_Allegati WHERE IdAtto=%d",$id));
	
}
function ap_get_all_allegati_atto($idAtto,$OrderBy=array(),$OrderOrd=array()){
	global $wpdb;
	$Sort="";
	if(count($OrderBy)>0) {
		for($i=0;$i<count($OrderBy);$i++){
			if(isset($OrderOrd[$i]) And (strtoupper($OrderOrd[$i])=="ASC" Or strtoupper($OrderOrd[$i])=="DESC")){
				$Sort.=$OrderBy[$i]." ".strtoupper($OrderOrd[$i]).", ";
			}
		}
		$Sort=" ORDER BY ".substr($Sort,0,-2);
	}else{
		$Sort=" ORDER BY IdAllegato ";
	}
	return $wpdb->get_results("SELECT * FROM $wpdb->table_name_Allegati WHERE IdAtto=". (int)$idAtto.$Sort.";");
}
function ap_get_allegati_atto($idAtto){
	global $wpdb;
	return $wpdb->get_results("SELECT * FROM $wpdb->table_name_Allegati WHERE IdAtto=". (int)$idAtto." And Natura='A' Order By TitoloAllegato;");
}
function ap_get_documenti_atto($idAtto){
	global $wpdb;
	return $wpdb->get_results("SELECT * FROM $wpdb->table_name_Allegati WHERE IdAtto=". (int)$idAtto." And Natura='D' Order By TitoloAllegato;");
}

function ap_get_SQL_Oggetto($table,$CampoFiltro,$CondizioneFiltro,$CodiceFiltro) {
	global $wpdb;
	$table_data = $wpdb->get_results("SELECT * FROM $table WHERE $CampoFiltro $CondizioneFiltro $CodiceFiltro", ARRAY_A);
	$entries = 'INSERT INTO ' . ap_backquote($table) . ' VALUES (';	
	//    \x08\\x09, not required
	$search = array("\x00", "\x0a", "\x0d", "\x1a");
	$replace = array('\0', '\n', '\r', '\Z');
	$Codice="";
	if($table_data) {
		foreach ($table_data as $row) {
			$values = array();
			foreach ($row as $key => $value) {
				//echo $key." <br />";
				if (isset($ints[$key])) {
					// make sure there are no blank spots in the insert syntax,
					// yet try to avoid quotation marks around integers
					$value = ( null === $value || '' === $value) ? $defs[strtolower($key)] : $value;
					$values[] = ( '' === $value ) ? "''" : $value;
				} else {
					$values[] = "'" . str_replace($search, $replace, ap_sql_addslashes($value)) . "'";
				}
			}
			$Codice.= $entries.implode(', ', $values).");\r\n";
		}
	}
	return $Codice;
} // end export data of Allegati Atto into a string

function ap_get_allegato_atto($idAllegato){
	global $wpdb;
	return $wpdb->get_results("SELECT * FROM $wpdb->table_name_Allegati WHERE IdAllegato=". (int)$idAllegato.";");
}

function ap_memo_allegato($idAllegato,$Titolo,$idAtto,$Integrale=1,$Natura="A"){
	global $wpdb;
//	echo "Sql==".$wpdb->last_query ."    Ultimo errore==".$wpdb->last_error;wp_die();
	if ($num=$wpdb->update($wpdb->table_name_Allegati,
					array('TitoloAllegato' => $Titolo,
						  		 'DocIntegrale'	=> $Integrale,
				 				 'Natura'		=> $Natura),
						  array( 'IdAllegato' 	=> $idAllegato),
						  array('%s','%d','%s'),
						  array('%d'))){
		ap_insert_log(3,2,(isset($idAllegato)?$idAllegato:0),"
							{".__("Titolo Allegato","albo-online")."}==> $Titolo
							{".__("Integrale","albo-online")."}==> ".($Integrale==1?'Si':'No')."
							{".__("Natura","albo-online")."}==> ".($Natura='A'?'Allegato':'Documento'),(int)$idAtto);
		return true;
	}else{
		return new WP_Error('db_update_error', __("Allegato non modificato","albo-online")." ".$wpdb->last_error, $wpdb->last_error);
	}
}

function ap_insert_allegato($TitoloAllegato,$Allegato,$IdAtto,$Integrale=1,$Natura="A"){
global $wpdb;
	$IdAtto=(int)$IdAtto;
	$Impronta=ap_CalcImpronta(0,$Allegato);
	if ( false === $wpdb->insert(
		$wpdb->table_name_Allegati,array(
				'TitoloAllegato'=> $TitoloAllegato,
				'Allegato' 		=>  $Allegato,
				'IdAtto' 		=> $IdAtto,
				'DocIntegrale' 	=> $Integrale,
				'Impronta' 		=> $Impronta,
				'Natura'		=> $Natura,
				),array('%s','%s','%d','%d','%s','%s')))	
        return __('Non sono riuscito ad inserire il nuovo allegato','albo-online')." ".$wpdb->last_error;
    else
    	ap_insert_log(3,1,$wpdb->insert_id,"{IdAllegato}==> $wpdb->insert_id
											{".__("Titolo","albo-online")."}==> $TitoloAllegato 
											{".__("Allegato","albo-online")."}==> $Allegato 
											{IdAtto}==> $IdAtto
											{".__("Integrale","albo-online")."}==> ".($Integrale==1?'Si':'No')."
											{".__("Impronta","albo-online")."}==> $Impronta
											{".__("Natura","albo-online")."}==> ".($Natura='A'?'Allegato':'Documento'), $IdAtto);
	return $Impronta;
}

function ap_del_allegato_atto($idAllegato,$idAtto=0,$nomeAllegato='',$SoloFile="N"){
global $wpdb;
	$idAllegato=(int)$idAllegato;
	$idAtto=(int)$idAtto;
	$allegato=ap_get_allegato_atto($idAllegato);
	if (file_exists($allegato[0]->Allegato) && is_file($allegato[0]->Allegato))
		if (unlink($allegato[0]->Allegato)){
			if($SoloFile=="N"){
				$wpdb->query($wpdb->prepare( "DELETE FROM $wpdb->table_name_Allegati WHERE IdAllegato=%d",$idAllegato));
				ap_insert_log(3,3,$allegato[0]->IdAllegato,"{".__("Nome Allegato","albo-online")."}==> ".$allegato[0]->TitoloAllegato." ",$idAtto);
			}else{
				ap_insert_log(3,3,$allegato[0]->IdAllegato,"{".__("Nome Allegato","albo-online")."}==> ".$allegato[0]->TitoloAllegato." Cancellato solo il file per VIOLAZIONE di LEGGE",$idAtto);
			}
			return True;
		}else{
			return FALSE;
		}
}
function ap_del_allegati_atto($idAtto){
global $wpdb;
	$Del=FALSE;
	$idAtto=(int)$idAtto;
	$Allegati=ap_get_all_allegati_atto($idAtto);
	$Del=FALSE;
	foreach($Allegati as $allegato){
		if (file_exists($allegato->Allegato) && is_file($allegato->Allegato))
			if (unlink($allegato->Allegato)){
				$Del=TRUE;
			}
		if (FALSE!==$wpdb->query($wpdb->prepare( "DELETE FROM $wpdb->table_name_Allegati WHERE IdAllegato=%d",$allegato->IdAllegato))){
			ap_insert_log(3,3,$allegato->IdAllegato,"{".__("Nome Allegato","albo-online")."}==> ".$allegato->Allegato,$idAtto);
			$Del=TRUE;
		}
	}
	return $Del;
}

function ap_get_all_allegati(){
	global $wpdb;
	return $wpdb->get_results("SELECT * FROM $wpdb->table_name_Allegati;");
}

function ap_sposta_allegati($OldPathAllegati,$eliminareOrigine=FALSE){
	global $wpdb;
//	echo $OldPathAllegati;exit;
//Backup Automatico dati e allegati
	$msg="";
	ap_BackupDatiFiles("Sposta_Allegati","Automatico");
//	$DirLog=str_replace("\\","/",Albo_DIR.'/BackupDatiAlbo/log');
	$DirLog=str_replace("\\","/",WP_CONTENT_DIR.'/AlboOnLine/BackupDatiAlbo/log');
	$nomefileLog=$DirLog."/Backup_Automatico_AlboPretorio_Sposta_Allegati.log";
	$fplog = @fopen($nomefileLog, "ab");
	fwrite($fplog,"____________________________________________________________________________\n");
	fwrite($fplog,"Inizio spostamento file\n");
	$allegati=$wpdb->get_results("SELECT * FROM $wpdb->table_name_Allegati ;",ARRAY_A );
// Nuova directory Allegati Albo Pretorio
	$BaseCurDir=str_replace("\\","/",AP_BASE_DIR.get_option('opt_AP_FolderUpload'));
// Inizo Blocco che sposta gli allegati e sincronizza la tabella degli Allegati
	foreach( $allegati as $allegato){
		$NewAllegato=$BaseCurDir."/".basename($allegato['Allegato']);
		if (is_file($allegato['Allegato'])){
			if (!copy($allegato['Allegato'], $NewAllegato)) {
				ap_insert_log(3,10,$allegato['IdAllegato'] ,"{".__("Errore nello spostamento Allegato","albo-online")."}==> ".$allegato['Allegato']." => $NewAllegato",0);	
				$msg.='<spam style="color:red;">'.__("Errore","albo-online").'</spam> '.__("nello spostamento dell'Allegato","albo-online").' '.$allegato['Allegato'].' in '. $NewAllegato."%%br%%";
				fwrite($fplog,_("Non sono riuscito a copiare il file","albo-online")." ".$allegato['Allegato']." ".__("in","albo-online")." ". $NewAllegato."\n");
			}
			else{
				if (!unlink($allegato['Allegato'])){
					ap_insert_log(3,10,$allegato['IdAllegato'] ,"{".__("Errore nella cancellazione Allegato","albo-online")."}==> ".$allegato['Allegato'],0);
					$msg.='<spam style="color:red;">'.__("Errore","albo-online").'</spam> '.__("errata cancellazione dell'Allegato","albo-online").' </spam>'.$allegato['Allegato']."%%br%%";
					fwrite($fplog,_("Non sono riuscito a cancellare il file","albo-online")." ".$allegato['Allegato']."\n");
			}
			$msg.='<spam style="color:green;">File</spam> '.$allegato['Allegato'].' <spam style="color:green;">'.__("spostato in","albo-online").'</spam> '.$NewAllegato.'%%br%%';
			fwrite($fplog,"File ".$allegato['Allegato']." ".__("spostato in","albo-online")." ".$NewAllegato."\n");
			if ($wpdb->update($wpdb->table_name_Allegati,
									array('Allegato' => $NewAllegato),
									array('IdAllegato' => $allegato['IdAllegato'] ),
									array('%s'),
									array('%d'))>0){
				ap_insert_log(3,9,$allegato['IdAllegato'] ,"{".__("Allegato","albo-online")."}==> ".$allegato['Allegato']." ".__("spostato in","albo-online")." $NewAllegato",0);
				$msg.='<spam style="color:green;">'.__("Aggiornamento Link Allegato","albo-online").'</spam> '.$allegato['Allegato']."%%br%%";
				fwrite($fplog,__("Aggiornato il link nel Data Base per","albo-online")." ".$allegato['Allegato']." in ".$NewAllegato."\n");
			}
		}
	}					
}
// Fine Blocco che sposta gli allegati e sincronizza la tabella degli Allegati
	$msg.="%%br%%";
	$tmpdir=str_replace("\\","/",$OldPathAllegati);
	$PathAllegati=AP_BASE_DIR;
	fwrite($fplog,"__________________________________________________________________\n");
	fwrite($fplog,"Svuotamento e cancellazione Vecchia Directory ".$OldPathAllegati." \n");
	if ($tmpdir!=$PathAllegati and $eliminareOrigine){
		$fName=str_replace("\\","/",$OldPathAllegati)."/index.php";
		if (is_file($fName))
			if (unlink($fName))
				fwrite($fplog,"File ".$fName." Cancellato\n");
			else
				fwrite($fplog,__("Errore nella Cancellazione del file","albo-online")." ".$fName."\n");
		else
			fwrite($fplog,"File ".$fName." inesistente\n");
		$fName=str_replace("\\","/",$OldPathAllegati)."/".ap_decodenamefile();
		if (is_file($fName))
			if (unlink($fName))
				fwrite($fplog,"File ".$fName." ".__("Cancellato","albo-online")."\n");
			else
				fwrite($fplog,__("Errore nella Cancellazione del file","albo-online")." ".$fName."\n");
		else
			fwrite($fplog,"File ".$fName." inesistente\n");
		if($tmpdir==AP_BASE_DIR){
			$msg.="Directory ".$tmpdir." non cancellata%%br%%";
			fwrite($fplog,"Directory ".$tmpdir." ".__("non cancellata","albo-online")."\n");	
		}else{
			if (is_dir($tmpdir)){
				if (!ap_is_dir_empty($tmpdir)){
					$msg.="La directory ".$tmpdir." ".__("non vuota","albo-online")."%%br%%";
					fwrite($fplog,"La directory ".$tmpdir." ".__("non vuota","albo-online")." \n");					
				}else{
					if (rmdir($tmpdir)){
						$msg.="Directory ".$tmpdir." cancellata%%br%%";
						fwrite($fplog,"Directory ".$tmpdir." ".__("cancellata","albo-online")." \n");	
					}else{
						$msg.=__("La directory","albo-online")." ".$tmpdir." ".__("non e' stata cancellata","albo-online")."%%br%%";
						fwrite($fplog,__("La directory","albo-online")." ".$tmpdir." ".__("non e' stata cancellata","albo-online")." \n");
					}
				}
			}else{
					$msg.=__("La directory","albo-online")." ".$tmpdir." non esiste%%br%%";
					fwrite($fplog,"La directory ".$tmpdir." ".__("non esiste","albo-online")." \n");		
			}			
		}
	}
	if (!$eliminareOrigine){
		$msg.=__("La directory","albo-online")." ".$tmpdir." ".__("non essendo una sottocartella della cartella Uploads di sistema, non deve essere cancellata","albo-online")."%%br%%";
		fwrite($fplog,__("La directory","albo-online")." ".$tmpdir." ".__("non essendo una sottocartella della cartella Uploads di sistema, non deve essere cancellata","albo-online")." \n");	
	}
	fclose($fplog);
	if (stripslashes(get_option('opt_AP_FolderUpload'))!="wp-content/uploads"){
		ap_NoIndexNoDirectLink(AP_BASE_DIR.get_option('opt_AP_FolderUpload'));
	}
	$FileMsg=str_replace("\\","/",WP_CONTENT_DIR.'/AlboOnLine/BackupDatiAlbo/tmp/msg.txt');
	$fpmsg = @fopen($FileMsg, "wb");
	fwrite($fpmsg,$msg);
	fclose($fpmsg);
}

function ap_allinea_allegati(){
	global $wpdb;
	$msg="";
	$allegati=$wpdb->get_results("SELECT * FROM $wpdb->table_name_Allegati ;",ARRAY_A );
// Nuova directory Allegati Albo Pretorio
	$BaseCurDir=str_replace("\\","/",AP_BASE_DIR.get_option('opt_AP_FolderUpload'));
	foreach ( $allegati as $allegato) {
		if (get_option('opt_AP_FolderUploadMeseAnno')=="Si") {
			$NewPath=ap_get_PathAllegati($allegato["IdAtto"]);
			$NewAllegato=$NewPath."/".basename($allegato["Allegato"]);
		}else{
			$NewAllegato=$BaseCurDir."/".basename($allegato['Allegato']);			
		}
		if ($wpdb->update($wpdb->table_name_Allegati,
									array('Allegato' => $NewAllegato),
									array('IdAllegato' => $allegato['IdAllegato'] ),
									array('%s'),
									array('%d'))){
				ap_insert_log(3,9,$allegato['IdAllegato'] ,"{".__("Allegato","albo-online")."}==> ".$allegato['Allegato']." spostato in $NewAllegato",0);
				$msg.='<spam style="color:green;">'.__('Aggiornamento Link Allegato','albo-online').'</spam> '.$allegato['Allegato']."%%br%%";
			}
//	echo "<p>Sql==".$wpdb->last_query ."    Ultimo errore==".$wpdb->last_error."</p>";
	}

	return $msg;
}

################################################################################
// Funzioni Responsabili
################################################################################
function ap_get_dropdown_responsabili($select_name,$id_name,$class,$tab_index_attribute="", $default="xxxx",$Funzione="") {
	global $wpdb;
	if($default=="xxxx") $default=__("Nessuno","albo-online");
	$Where="";
	if(($IA=is_array($Funzione)) or $Funzione!=""){
		if($IA){
			$Funzione=implode("\",\"",$Funzione);
		}
		$Where=" Where $wpdb->table_name_RespProc.Funzione in(\"".$Funzione."\")";
	}
	$responsabili = $wpdb->get_results("SELECT DISTINCT * FROM $wpdb->table_name_RespProc $Where ORDER BY nome;");	
//	echo "SELECT DISTINCT * FROM $wpdb->table_name_RespProc $Where ORDER BY nome;";wp_die();
	$output = "<select name='$select_name' id='$id_name' class='$class' $tab_index_attribute>\n";
	if ($default==__("Nessuno","albo-online") Or $default==0){
		$output .= "\t<option value='0' selected='selected'>".__("Nessuno","albo-online")."</option>\n";
	}else{
		$output .= "\t<option value='0' >".__("Nessuno","albo-online")." </option>\n";
	}
	if ( ! empty( $responsabili ) ) {	
		foreach ($responsabili as $c) {
			$output .= "\t<option value='$c->IdResponsabile'";
			if ($c->IdResponsabile==$default){
				$output .= " selected=\"selected\" ";
			}
			$output .=" >$c->Cognome $c->Nome</option>\n";
		}
	}
	$output .= "</select>\n";
	return $output;
}

function ap_get_NumAttiSoggetto($idSoggetto){
	global $wpdb;
	$Atti=$wpdb->get_results("SELECT * FROM $wpdb->table_name_Atti");
	$NumAtti=0;
	foreach($Atti as $Soggetto){
		$SoggettiAtto=unserialize($Soggetto->Soggetti);
		if(is_array($SoggettiAtto) And in_array($idSoggetto,$SoggettiAtto)) $NumAtti++;
	}
	return $NumAtti;
}
function ap_get_NumAttiSoggetti(){
	global $wpdb;
	$Atti=$wpdb->get_results("SELECT * FROM $wpdb->table_name_Atti");
	$Soggetti=array();
	foreach($Atti as $Soggetto){
		$SoggettiAtto=unserialize($Soggetto->Soggetti);
		if(is_array($SoggettiAtto)){
			foreach($SoggettiAtto as $SoggettoAtto)
				if(!isset($Soggetti[$SoggettoAtto])){
					$Soggetti[$SoggettoAtto]=1;
				}else{
					$Soggetti[$SoggettoAtto]++;
				}
		}
	}
	return $Soggetti;
}

function ap_num_responsabili(){
	global $wpdb;
	return $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->table_name_RespProc;");	
}
function ap_num_responsabili_inutilizzati(){
	global $wpdb;
	$NumAttiSoggetti=ap_get_NumAttiSoggetti();
	$Sogg="";
	foreach($NumAttiSoggetti as $Key=>$Dati)
			$Sogg.=$Key.",";
	$Sql="SELECT count(*) 
	      FROM $wpdb->table_name_RespProc 
		  WHERE $wpdb->table_name_RespProc.IdResponsabile not in(".substr($Sogg,0,-1).");";
	return $wpdb->get_var($Sql);
}
function ap_get_responsabili(){
	global $wpdb;
//	echo "SELECT * FROM $wpdb->table_name_Atti $Selezione $OrderBy $Limite;";
	return $wpdb->get_results("SELECT * FROM $wpdb->table_name_RespProc ORDER BY Cognome , Nome;");	
}
function ap_get_alcuni_soggetti_ruolo($Soggetti){
	global $wpdb;
//	echo "SELECT * FROM $wpdb->table_name_RespProc Where IdResponsabile in((".$Soggetti.") ORDER BY Funzione, Cognome , Nome;";
	$Res=$wpdb->get_results("SELECT * FROM $wpdb->table_name_RespProc Where IdResponsabile in(".$Soggetti.") ORDER BY Funzione Desc, Cognome , Nome;");	
	return $Res;
}
function ap_get_responsabile($Id){
	global $wpdb;
	return $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->table_name_RespProc WHERE IdResponsabile=%d;",$Id));	
}
function ap_insert_responsabile($resp_cognome,$resp_nome,$resp_funzione,$resp_email,$resp_telefono,$resp_orario,$resp_note){
	global $wpdb;
	if ( false === $wpdb->insert($wpdb->table_name_RespProc,
									array('Cognome' => stripslashes($resp_cognome),
                                          'Nome' => $resp_nome,
										  'Funzione'=>$resp_funzione,
										  'Email' => $resp_email,
										  'Telefono' => $resp_telefono,
										  'Orario' => $resp_orario,
										  'Note' => stripslashes($resp_note)),
									array('%s',
										  '%s',
										  '%s',
										  '%s',
										  '%s',
										  '%s',
										  '%s')))	
        return new WP_Error('db_insert_error', __('Non sono riuscito ad inserire il Nuovo Responsabile','albo-online').' '.$wpdb->last_error, $wpdb->last_error);
    else
    	ap_insert_log(4,1,$wpdb->insert_id,"{IdResponsabile}==> $wpdb->insert_id
		                                    {".__("Cognome","albo-online")."}==> $resp_cognome 
		                                    {".__("Nome","albo-online")."}==> $resp_nome 
											{".__("Funzione","albo-online")."}==> $resp_funzione
											{".__("Email","albo-online")."}==> $resp_email
											{".__("Telefono","albo-online")."}==> $resp_telefono
											{".__("Orario","albo-online")."}==> $resp_orario
											{".__("Note","albo-online")."}==> $resp_note");
}
function ap_memo_responsabile($Id,$resp_cognome,$resp_nome,$resp_funzione,$resp_email,$resp_telefono,$resp_orario,$resp_note){
	global $wpdb;
	$Id=(int)$Id;
	$Responsabile=ap_get_responsabile($Id);
	$Responsabile=$Responsabile[0];
	$Log='{Id}==>'.$Id .' ' ;
	if ($Responsabile->Cognome!=$resp_cognome)
		$Log.='{'.__("Cognome","albo-online").'}==> '.$resp_cognome.' ';
	if ($Responsabile->Nome!=$resp_nome)
		$Log.='{'.__("Nome","albo-online").'}==> '.$resp_nome.' ';
	if ($Responsabile->Funzione!=$resp_funzione)
		$Log.='{'.__("Funzione","albo-online").'}==> '.$resp_funzione.' ';
	if ($Responsabile->Email!=$resp_email)
		$Log.='{'.__("Email","albo-online").'}==> '.$resp_email.' ';
	if ($Responsabile->Telefono!=$resp_telefono)
		$Log.='{'.__("Telefono","albo-online").'}==> '.$resp_telefono.' ';
	if ($Responsabile->Orario!=$resp_orario)
		$Log.='{'.__("Orario","albo-online").'}==> '.$resp_orario.' ';
	if ($Responsabile->Note!=$resp_note)
		$Log.='{'.__("Note","albo-online").'}==> '.$resp_note.' ';
	
	if ( false === $wpdb->update($wpdb->table_name_RespProc,
					array('Cognome' => stripslashes($resp_cognome),
	                      'Nome' => $resp_nome,
						  'Funzione'=>$resp_funzione,
						  'Email' => $resp_email,
						  'Telefono' => $resp_telefono,
						  'Orario' => $resp_orario,
						  'Note' => stripslashes($resp_note)),
					array('IdResponsabile' => $Id),
					array( '%s',
						   '%s',
						   '%s',
						   '%s',
						   '%s',
						   '%s',
						   '%s'),
					array('%d')))
	    	return new WP_Error('db_update_error', __('Non sono riuscito a modifire il resposnabile del Trattamento','albo-online').' '.$wpdb->last_error, $wpdb->last_error);
	else 
		ap_insert_log(4,2,$Id,$Log);
}

function ap_del_responsabile($id) {
	global $wpdb;
	$id=(int)$id;
	$resp=ap_get_responsabile($id);
	if(count($resp)==0){
		return __("Cancellazione Soggetto Fallita, il soggetto non è presente in archivio ","albo-online");
	}
	$responsabile= __("Cancellazione Responsabile","albo-online")." {IdResponsabile}==> $id {".__("Cognome","albo-online")."}==> ".$resp[0]->Cognome." {".__("Nome","albo-online")."}==> ".$resp[0]->Nome; 
	$respdel=sprintf(__("Cancellazione Responsabile (%d) %s %s Avvenuta con successo","albo-online"),$id, $resp[0]->Cognome,$resp[0]->Nome); 
	$N_atti=ap_get_NumAttiSoggetto($id);
	if ($N_atti>0){
		return array("atti" => $N_atti);
	}else{
	 	$result=$wpdb->query($wpdb->prepare( "DELETE FROM $wpdb->table_name_RespProc WHERE IdResponsabile=%d",$id));
		ap_insert_log(4,3,$id,$responsabile,$id);
		if($result==1){
			return $respdel;
		}else{
			return __("Si è verificato un errore nella cancellazione del soggetto","albo-online");
		}
	}
}
function ap_responsabili_orfani(){
	global $wpdb;
	$Sql="SELECT LPAD($wpdb->table_name_Atti.Numero,7,0) as Numero,$wpdb->table_name_Atti.Anno, $wpdb->table_name_Atti.RespProc 
	      FROM $wpdb->table_name_Atti left join  $wpdb->table_name_RespProc on 
		  		$wpdb->table_name_Atti.RespProc =  $wpdb->table_name_RespProc.IdResponsabile
		  WHERE $wpdb->table_name_RespProc.IdResponsabile is null";
	return $wpdb->get_results($Sql);
}
################################################################################
// Funzioni Permessi
################################################################################

function ap_get_users(){
	global $wpdb;  
	$users = $wpdb->get_results('SELECT ID, user_login FROM '.$wpdb->users); 
    return $users;  
}
################################################################################
// Funzioni Enti
################################################################################
function 
ap_get_dropdown_enti($select_name,$id_name,$class,$tab_index_attribute="", $default=-1) {
     global $wpdb;
     $enti = $wpdb->get_results("SELECT DISTINCT * FROM 
$wpdb->table_name_Enti ORDER BY IdEnte;");
     $output = "<select name='$select_name' id='$id_name' class='$class' $tab_index_attribute > \n";
     if ( ! empty( $enti ) ) {
             /* mr modifica per cercare in tutti gli enti */
             $output .= "\t<option value=\"-1\" selected=\"selected\" 
 >".__('Tutti gli Enti', 'albo-online')."</option>\n";
         foreach ($enti as $c) {
             $output .= "\t<option value='$c->IdEnte'";
                         /* mr commento il select */
            if ($c->IdEnte==$default){
                $output .= " selected=\"selected\" ";
            }
             $output .=" >".stripslashes($c->Nome)."</option>\n";
         }
     }
     $output .= "</select>\n";
     return $output;
}
function ap_num_enti(){
	global $wpdb;
	return $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->table_name_Enti");
}
function ap_num_enti_Inutilizzati(){
	global $wpdb;
	$Sql="SELECT COUNT(*)
			FROM $wpdb->table_name_Enti
			LEFT JOIN $wpdb->table_name_Atti ON $wpdb->table_name_Enti.IdEnte = $wpdb->table_name_Atti.Ente
			WHERE $wpdb->table_name_Atti.IdAtto IS NULL";
	return $wpdb->get_var($Sql);
}
function ap_num_enti_atto($id){
	global $wpdb;
	return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->table_name_Atti WHERE Ente=%d",$id));
}

function ap_get_enti(){
	global $wpdb;
//	echo "SELECT * FROM $wpdb->table_name_Enti ORDER BY Nome;";
	return $wpdb->get_results("SELECT * FROM $wpdb->table_name_Enti ORDER BY Nome;");	
}
function ap_get_ente($Id){
	global $wpdb;
//	echo $wpdb->prepare("SELECT * FROM $wpdb->table_name_Enti WHERE IdEnte=%d;",$Id);die();
	$ente=$wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->table_name_Enti WHERE IdEnte=%d;",$Id));
	if(isset($ente[0])){
		return $ente[0];		
	}else{
		
		return FALSE;
	}
}
function ap_get_ente_me(){
	global $wpdb;
	$ente=$wpdb->get_results("SELECT Nome FROM $wpdb->table_name_Enti WHERE IdEnte=0;");	
	return $ente[0]->Nome;
}
function ap_set_ente_me($ente_nome){
	global $wpdb;
	if (!ap_create_ente_me($ente_nome))
		if (true==$wpdb->update($wpdb->table_name_Enti,
						array('Nome' => stripslashes($ente_nome)),
						array('IdEnte' => 0),
						array( '%s')))
			ap_insert_log(7,2,0,__("Aggiornamento Ente Sito","albo-online"));	
//echo "Sql==".$wpdb->last_query ."    Ultimo errore==".$wpdb->last_error;exit;
}
function ap_create_ente_me($nome="Ente non definito"){
	global $wpdb;
		if ($wpdb->get_var("SELECT COUNT(IdEnte) FROM $wpdb->table_name_Enti  WHERE IdEnte=0;")==0){
			$wpdb->insert($wpdb->table_name_Enti,array('Nome' =>$nome),array('%s'));
			$wpdb->update($wpdb->table_name_Enti,
									 array('IdEnte' => 0),
									 array( 'IdEnte' => $wpdb->insert_id),
									 array('%d'),
									 array('%d'));	
			return TRUE;
		}
		return FALSE;
}

function ap_insert_ente($ente_nome,$ente_indirizzo,$ente_url,$ente_email,$ente_pec,$ente_telefono,$ente_fax,$ente_note){
	global $wpdb;
	if ( false === $wpdb->insert($wpdb->table_name_Enti,array('Nome' => stripslashes($ente_nome),
                                                              'Indirizzo' => stripslashes($ente_indirizzo),
                                                              'Url' => $ente_url,
															  'Email' => $ente_email,
															  'Pec' => $ente_pec,
															  'Telefono' => $ente_telefono,
															  'Fax' => $ente_fax,
															  'Note' => stripslashes($ente_note)),
														array('%s',
															  '%s',
															  '%s',
															  '%s',
															  '%s',
															  '%s',
															  '%s',
															  '%s'))){
//		echo "Sql==".$wpdb->last_query ."    Ultimo errore==".$wpdb->last_error;exit;
        return new WP_Error('db_insert_error', __('Non sono riuscito ad inserire il Nuovo Ente','albo-online').' '.$wpdb->last_error, $wpdb->last_error);}
    else
    	ap_insert_log(7,1,$wpdb->insert_id,"{IdEnte}==> $wpdb->insert_id
		                                    {".__("Nome","albo-online")."}==> $ente_nome 
											{".__("Indirizzo","albo-online")."}=> $ente_indirizzo
											{".__("Url","albo-online")."}=> $ente_url
											{".__("Email","albo-online")."}==> $ente_email
											{".__("Pec","albo-online")."}==> $ente_pec
											{".__("Telefono","albo-online")."}==> $ente_telefono
											{".__("Fax","albo-online")."}==> $ente_fax
											{".__("Note","albo-online")."}==> $ente_note");
}

function ap_memo_ente($Id,$ente_nome,$ente_indirizzo,$ente_url,$ente_email,$ente_pec,$ente_telefono,$ente_fax,$ente_note){
	global $wpdb;
	$Id=(int)$Id;
	$EnteL=ap_get_ente($Id);
	$Log='{Id}==>'.$Id .' ' ;
	if ($EnteL->Nome!=$ente_nome)
		$Log.='{'.__("Nome","albo-online").'}==> '.$ente_nome.' ';
	if ($EnteL->Indirizzo!=$ente_indirizzo)
		$Log.='{'.__("Indirizzo","albo-online").'}==> '.$ente_indirizzo.' ';
	if ($EnteL->Url!=$ente_url)
		$Log.='{'.__("Url","albo-online").'}==> '.$ente_url.' ';
	if ($EnteL->Email!=$ente_email)
		$Log.='{'.__("Email","albo-online").'}==> '.$ente_email.' ';
	if ($EnteL->Pec!=$ente_pec)
		$Log.='{'.__("Pec","albo-online").'}==> '.$ente_pec.' ';
	if ($EnteL->Telefono!=$ente_telefono)
		$Log.='{'.__("Telefono","albo-online").'}==> '.$ente_telefono.' ';
	if ($EnteL->Fax!=$ente_fax)
		$Log.='{'.__("Fax","albo-online").'}==> '.$ente_fax.' ';
	if ($EnteL->Note!=$ente_note)
		$Log.='{'.__("Note","albo-online").'}==> '.$ente_note.' ';
	
	if ( false === $wpdb->update($wpdb->table_name_Enti,
					array('Nome' => stripslashes($ente_nome),
					'Indirizzo' => stripslashes($ente_indirizzo),
						  'Url' => $ente_url,
						  'Email' => $ente_email,
						  'Pec' => $ente_pec,
						  'Telefono' => $ente_telefono,
						  'Fax' => $ente_fax,
						  'Note' => stripslashes($ente_note)),
					array('IdEnte' => $Id),
					array( '%s',
						   '%s',
						   '%s',
						   '%s',
						   '%s',
						   '%s'),
					array( '%d' )))
	    	return new WP_Error('db_update_error', __("Non sono riuscito a modificare l'Ente","albo-online").' '.$wpdb->last_error, $wpdb->last_error);
	else 
		ap_insert_log(7,2,$Id,$Log);
}

function ap_del_ente($id) {
	global $wpdb;
	$id=(int)$id;
	$N_atti=ap_num_enti_atto($id);
	if ($N_atti>0){
		return array("atti" => $N_atti);
	}else{
	 	$result=$wpdb->query($wpdb->prepare( "DELETE FROM $wpdb->table_name_Enti WHERE IdEnte=%d",$id));
		ap_insert_log(7,3,$id,__("Cancellazione Ente","albo-online")." {IdEnte}==> $id",$id);
		return $result;
	}
}
function ap_enti_orfani(){
	global $wpdb;
	$Sql="SELECT LPAD($wpdb->table_name_Atti.Numero,7,0) as Numero,$wpdb->table_name_Atti.Anno, $wpdb->table_name_Atti.Ente 
	      FROM $wpdb->table_name_Atti left join  $wpdb->table_name_Enti on 
		  		$wpdb->table_name_Atti.Ente =  $wpdb->table_name_Enti.IdEnte 
		  WHERE $wpdb->table_name_Enti.IdEnte is null or $wpdb->table_name_Enti.IdEnte=-1";
	return $wpdb->get_results($Sql);
}
function ap_set_ente_orfani($IDEnte){
	global $wpdb;
	return $wpdb->update($wpdb->table_name_Atti,
		 array('Ente' => $IDEnte),
		 array('Ente' => -1 ),
		 array('%d'),
		 array('%d'));
}
################################################################################
// Funzioni Unità Organizzative
################################################################################
function ap_get_dropdown_unitao($select_name,$id_name,$class,$tab_index_attribute="", $default=-1) {
     global $wpdb;
     $unitao = $wpdb->get_results("SELECT DISTINCT * FROM $wpdb->table_name_UO ORDER BY IdUO;");
     $output = "<select name='$select_name' id='$id_name' class='$class' $tab_index_attribute > \n";
     if ( ! empty( $unitao ) ) {
             /* mr modifica per cercare in tutte le unità organizzative */
             $output .= "\t<option value=\"-1\" selected=\"selected\" 
 >".__('Tutte le unità organizzative', 'albo-online')."</option>\n";
         foreach ($unitao as $c) {
             $output .= "\t<option value='$c->IdUO'";
                         /* mr commento il select */
            if ($c->IdUO==$default){
                $output .= " selected=\"selected\" ";
            }
             $output .=" >".stripslashes($c->Nome)."</option>\n";
         }
     }
     $output .= "</select>\n";
     return $output;
}
function ap_num_unitao(){
	global $wpdb;
	return $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->table_name_UO");
}
function ap_num_unitao_Inutilizzati(){
	global $wpdb;
	$Sql="SELECT COUNT(*)
			FROM $wpdb->table_name_UO
			LEFT JOIN $wpdb->table_name_Atti ON $wpdb->table_name_UO.IdUO = $wpdb->table_name_Atti.IdUnitaOrganizzativa
			WHERE $wpdb->table_name_Atti.IdAtto IS NULL";
	return $wpdb->get_var($Sql);
}
function ap_num_unitao_atto($id){
	global $wpdb;
	return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->table_name_Atti WHERE IdUnitaOrganizzativa=%d",$id));
}

function ap_get_unitao(){
	global $wpdb;
//	echo "SELECT * FROM $wpdb->table_name_UO ORDER BY Nome;";
	return $wpdb->get_results("SELECT * FROM $wpdb->table_name_UO ORDER BY Nome;");	
}
function ap_get_unitaorganizzativa($Id){
	global $wpdb;
//	echo $wpdb->prepare("SELECT * FROM $wpdb->table_name_UO WHERE IdUO=%d;",$Id);die();
	$unitao=$wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->table_name_UO WHERE IdUO=%d;",$Id));
	if(isset($unitao[0])){
		return $unitao[0];		
	}else{	
		return FALSE;
	}
}
function ap_insert_unitao($nome,$indirizzo,$url,$email,$pec,$telefono,$fax,$note){
	global $wpdb;
	if ( false === $wpdb->insert($wpdb->table_name_UO,array('Nome' => stripslashes($nome),
                                                              'Indirizzo' => stripslashes($indirizzo),
                                                              'Url' => $url,
															  'Email' => $email,
															  'Pec' => $pec,
															  'Telefono' => $telefono,
															  'Fax' => $fax,
															  'Note' => stripslashes($note)),
														array('%s',
															  '%s',
															  '%s',
															  '%s',
															  '%s',
															  '%s',
															  '%s',
															  '%s'))){
//		echo "Sql==".$wpdb->last_query ."    Ultimo errore==".$wpdb->last_error;exit;
        return new WP_Error('db_insert_error', __('Non sono riuscito ad inserire la Nuova Unità Organizzativa','albo-online').' '.$wpdb->last_error, $wpdb->last_error);}
    else
    	ap_insert_log(9,1,$wpdb->insert_id,"{IdUO}==> $wpdb->insert_id
		                                    {".__("Nome","albo-online")."}==> $nome 
											{".__("Indirizzo","albo-online")."}=> $indirizzo
											{".__("Url","albo-online")."}=> $url
											{".__("Email","albo-online")."}==> $email
											{".__("Pec","albo-online")."}==> $pec
											{".__("Telefono","albo-online")."}==> $telefono
											{".__("Fax","albo-online")."}==> $fax
											{".__("Note","albo-online")."}==> $note");
}

function ap_memo_unitao($Id,$nome,$indirizzo,$url,$email,$pec,$telefono,$fax,$note){
	global $wpdb;
	$Id=(int)$Id;
	$UOL=ap_get_ente($Id);
	$Log='{Id}==>'.$Id .' ' ;
	if ($UOL->Nome!=$nome)
		$Log.='{'.__("Nome","albo-online").'}==> '.$nome.' ';
	if ($UOL->Indirizzo!=$indirizzo)
		$Log.='{'.__("Indirizzo","albo-online").'}==> '.$indirizzo.' ';
	if ($UOL->Url!=$url)
		$Log.='{'.__("Url","albo-online").'}==> '.$url.' ';
	if ($UOL->Email!=$email)
		$Log.='{'.__("Email","albo-online").'}==> '.$email.' ';
	if ($UOL->Pec!=$pec)
		$Log.='{'.__("Pec","albo-online").'}==> '.$pec.' ';
	if ($UOL->Telefono!=$telefono)
		$Log.='{'.__("Telefono","albo-online").'}==> '.$telefono.' ';
	if ($UOL->Fax!=$fax)
		$Log.='{'.__("Fax","albo-online").'}==> '.$fax.' ';
	if ($UOL->Note!=$note)
		$Log.='{'.__("Note","albo-online").'}==> '.$note.' ';
	if ( false === $wpdb->update($wpdb->table_name_UO,
					array('Nome' => stripslashes($nome),
						  'Indirizzo' => stripslashes($indirizzo),
						  'Url' => $url,
						  'Email' => $email,
						  'Pec' => $pec,
						  'Telefono' => $telefono,
						  'Fax' => $fax,
						  'Note' => stripslashes($note)),
					array('IdUO' => $Id),
					array( '%s',
						   '%s',
						   '%s',
						   '%s',
						   '%s',
						   '%s'),
					array( '%d' )))
	    	return new WP_Error('db_update_error', __("Non sono riuscito a modificare l'Unità Organizzativa","albo-online").' '.$wpdb->last_error, $wpdb->last_error);
	else 
		ap_insert_log(9,2,$Id,$Log);
}

function ap_del_unitao($id) {
	global $wpdb;
	$id=(int)$id;
	$N_unitao=ap_num_unitao_atto($id);
	if ($N_unitao>0){
		return array("unitao" => $N_unitao);
	}else{
	 	$result=$wpdb->query($wpdb->prepare( "DELETE FROM $wpdb->table_name_UO WHERE IdUO=%d",$id));
		ap_insert_log(9,3,$id,__("Cancellazione Unità Organizzativa","albo-online")." {IdUO}==> $id",$id);
		return $result;
	}
}
function ap_unitao_orfani(){
	global $wpdb;
	$Sql="SELECT LPAD($wpdb->table_name_Atti.Numero,7,0) as Numero,$wpdb->table_name_Atti.Anno, $wpdb->table_name_Atti.IdUnitaOrganizzativa 
	      FROM $wpdb->table_name_Atti left join  $wpdb->table_name_UO on 
		  		$wpdb->table_name_Atti.IdUnitaOrganizzativa =  $wpdb->table_name_UO.IdUO 
		  WHERE $wpdb->table_name_UO.IdUO is null or $wpdb->table_name_UO.IdUO=-1";
	return $wpdb->get_results($Sql);
}
function ap_set_unitao_orfani($IdUO){
	global $wpdb;
	return $wpdb->update($wpdb->table_name_Atti,
		 array('IdUnitaOrganizzative' => $IdUO),
		 array('IdUnitaOrganizzative' => -1 ),
		 array('%d'),
		 array('%d'));
}

/**
* Backup 
*/
function ap_sql_addslashes($a_string = '', $is_like = false) {
	if ($is_like) $a_string = str_replace('\\', '\\\\\\\\', $a_string);
	else $a_string = str_replace('\\', '\\\\', $a_string);
	return str_replace('\'', '\\\'', $a_string);
} 

function ap_backup_table($table,$fp,$Filtro="",$Delete=TRUE) {
	global $wpdb;
	if($table==$wpdb->table_name_Enti){
		@fwrite($fp,"SET SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO';"."\r\n");
	}
	$table_structure = $wpdb->get_results("DESCRIBE $table");
	if (! $table_structure) {
		echo __("Errore nell'estrazione della struttura della tabella","albo-online").' : '.$table;
		return false;
	}
	// Table structure
	// Comment in SQL-file
	$create_table = $wpdb->get_results("SHOW CREATE TABLE $table", ARRAY_N);
	if (false === $create_table) {
		$err_msg = 'Errore in SHOW CREATE TABLE per la labella '. $table;
	}else{
		$create_table[0][1]=str_replace("CREATE TABLE","CREATE TABLE IF NOT EXISTS",$create_table[0][1]);
		@fwrite($fp,$create_table[0][1] . ' ;'."\r\n");
	}
	$defs = array();
	$ints = array();
	foreach ($table_structure as $struct) {
		if ( (0 === strpos($struct->Type, 'tinyint')) ||
			(0 === strpos(strtolower($struct->Type), 'smallint')) ||
			(0 === strpos(strtolower($struct->Type), 'mediumint')) ||
			(0 === strpos(strtolower($struct->Type), 'int')) ||
			(0 === strpos(strtolower($struct->Type), 'bigint')) ) {
				$defs[strtolower($struct->Field)] = ( null === $struct->Default ) ? 'NULL' : $struct->Default;
				$ints[($struct->Field)] = "1";
		}
	}
	if($Delete)
		@fwrite($fp,"Delete From $table ;"."\r\n");
	if($Filtro!=""){
		$Filtro=" Where ".$Filtro;
	}
	$table_data = $wpdb->get_results("SELECT * FROM $table $Filtro", ARRAY_A);
	$entries = 'INSERT INTO ' . ap_backquote($table) . ' VALUES (';	
	//    \x08\\x09, not required
	$search = array("\x00", "\x0a", "\x0d", "\x1a");
	$replace = array('\0', '\n', '\r', '\Z');
	if($table_data) {
		foreach ($table_data as $row) {
			$values = array();
			foreach ($row as $key => $value) {
				//echo $key." <br />";
				if (isset($ints[$key])) {
					// make sure there are no blank spots in the insert syntax,
					// yet try to avoid quotation marks around integers
					$value = ( null === $value || '' === $value) ? $defs[strtolower($key)] : $value;
					$values[] = ( '' === $value ) ? "''" : $value;
				} else {
					$values[] = "'" . str_replace($search, $replace, ap_sql_addslashes($value)) . "'";
				}
			}
			@fwrite($fp, $entries . implode(', ', $values) . ');'."\r\n");
		}
	}
} // end backup_table()

function ap_SvuotaDirectory($Dir,$fplog){
	//Svuoto cartella tmp che contiene i files dati
	$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($Dir));
	if(!is_null($fplog))	
		fwrite($fplog,__("Svuotamento Directory","albo-online")." ".$Dir."\n");
	foreach ($iterator as $key=>$value) {
		if (is_file(realpath($key)))
			if (unlink(realpath($key))){
				if(!is_null($fplog))
					fwrite($fplog,"       File ".$key." ".__("cancellato","albo-online")."\n");			
			}else{
				if(!is_null($fplog))	
					fwrite($fplog,"       File ".$key." ".__("non può essere cancellato","albo-online"). "\n");			
			}
	}
}

function ap_BackupDatiFiles($NomeFile,$Tipo="",$Destinazione=AlboBCK,$Echo=FALSE ){
global $wpdb;
	$tables=array(	$wpdb->table_name_Allegati,
					$wpdb->table_name_Atti,
					$wpdb->table_name_Categorie,
					$wpdb->table_name_Enti,
					$wpdb->table_name_RespProc,
					$wpdb->table_name_Attimeta,
					$wpdb->table_name_UO);
	$Dir=str_replace("\\","/",$Destinazione.'/BackupDatiAlbo');
	$DirTmp=$Dir."/tmp";
	$DirLog=$Dir."/log";
	$ControlloDir="";
	require_once('inc/pclzip.php');
	if ($Tipo==""){
			$nomefileZip=$Dir."/".$NomeFile.".zip";
			$nomefileLog=$DirLog."/Backup_AlboPretorio_".$NomeFile.".log";
		}else{
			$nomefileZip=$Dir."/".$Tipo."_".$NomeFile.".zip";
			$nomefileLog=$DirLog."/Backup_".$Tipo."_AlboPretorio_".$NomeFile.".log";
	}	
	$Risultato=__('Risultato del Backup','albo-online'). ":<br />";
	if ($Echo){
		echo "<h2>".__('Risultato del Backup','albo-online'). ":</h2>";
		echo "<h3>".__('Verifica struttura Directory destinazione','albo-online'). "</h3>"
		. "<ul>";
	}
	if (class_exists('PclZip')) {
//		echo $Dir." <br />".$DirTmp." <br />".$DirAllegati." <br />".$DirLog."";wp_die();
		if (!is_dir ( $Destinazione)){
			if (!mkdir($Destinazione, 0744)){
				if ($Echo){
					echo "<li>".sprintf(__('Non sono riuscito a creare la directory %s Fine Operazione','albo-online'),$Destinazione)."</li>";
				}else{
					 echo"<li>".sprintf(__('Directory %s Verificata','albo-online'),$Destinazione)."</li>";
				}
				$ControlloDir.=sprintf(__('Non sono riuscito a creare la directory  %s \n Fine Operazione','albo-online'),$Destinazione);
			}
		}
		if (is_dir($Destinazione)){
			if (!is_dir ( $Dir)){
				if (!mkdir($Dir, 0744)) {
					if ($Echo){
						echo "<li>".sprintf(__('Non sono riuscito a creare la directory %s Fine Operazione','albo-online'),$Dir)."</li>";
					}else{
						echo"<li>".sprintf(__('Directory %s Verificata','albo-online'),$Dir)."</li>";
					}
					$ControlloDir.=sprintf(__('Non sono riuscito a creare la directory %s \n Fine Operazione','albo-online'),$Dir);
				}
			}
		}
		if (!is_dir ( $DirTmp)){	
			if (!mkdir($DirTmp, 0744)){
				if ($Echo){
					echo "<li>".sprintf(__('Non sono riuscito a creare la directory %s Fine Operazione','albo-online'),$DirTmp)."</li>";
				}else{
					echo"<li>".sprintf(__('Directory %s Verificata','albo-online'),$DirTmp)."</li>";
				}
				$ControlloDir.=sprintf(__('Non sono riuscito a creare la directory %s \n Fine Operazione','albo-online'),$DirTmp);
			}
		}
		if (!is_dir ( $DirLog)){							
			if (!mkdir($DirLog, 0744)){
				if ($Echo){
					 echo "<li>".sprintf(__('Non sono riuscito a creare la directory %s Fine Operazione','albo-online'),$DirLog)."</li>";
				}else{
					echo"<li>".sprintf(__('Directory %s Verificata','albo-online'),$DirLog)."</li>";
				}
				$ControlloDir.=sprintf(__('Non sono riuscito a creare la directory %s \n Fine Operazione','albo-online'),$DirLog);
			} 
		}
		if ($Echo) echo "</ul>";
		if ($ControlloDir!=""){
			return $ControlloDir;
		}
		
	/*		if ($Tipo==""){
			$nomefileZip=$Dir."/".$NomeFile.".zip";
			$nomefileLog=$DirLog."/Backup_AlboPretorio_".$NomeFile.".log";
		}else{
			$nomefileZip=$Dir."/".$Tipo."_".$NomeFile.".zip";
			$nomefileLog=$DirLog."/Backup_".$Tipo."_AlboPretorio_".$NomeFile.".log";
		}*/	
		$fplog = @fopen($nomefileLog, "wb");
		fwrite($fplog,__("Avvio Backup Dati ed Allegati Albo Pretrorio \n effettuato in data","albo-online")." ".date("Ymd_Hi")."\n");
		ap_SvuotaDirectory($DirTmp,$fplog);
		fwrite($fplog,__("Svuotamento tabella","albo-online")." ".$DirTmp."\n");
		$fp = @fopen($DirTmp."/AlboPretorio".date("Ymd_Hi").".sql", "wb");
		$Risultato="";
		if ($Echo) echo "<h3>".__("Avvio Backup Dati (Tabelle del Data Base)","albo-online")."</h3>"
			. "</ul>";
		foreach ($tables as $table) {
			ap_backup_table($table,$fp);
			$Risultato.='<span style="color:green;">'.__('Tabella','albo-online').' '.ap_backquote($table).' '.__('Aggiunta','albo-online').'</span> <br />';
			if ($Echo)	echo '<li><span style="color:green;">'.__('Tabella','albo-online').' '.ap_backquote($table).' '.__('Aggiunta','albo-online').'</span></li>';
			fwrite($fplog,"Sql ".__('Tabella','albo-online')." ".ap_backquote($table)." ".__('Aggiunta','albo-online')."\n");
		}
		ap_backup_table($wpdb->options,$fp,"option_name LIKE 'opt_%' ",False);
		$Risultato.='<span style="color:green;">'.__('Tabella','albo-online').' '.ap_backquote($table).' '.__('Aggiunta','albo-online').'</span> <br />';
		if ($Echo)	echo '<li><span style="color:green;">'.__('Tabella','albo-online').' '.ap_backquote($wpdb->options).' '.__('Aggiunta','albo-online').'</span></li>'
		. '</ul>';
		fwrite($fplog,"Sql ".__('Tabella','albo-online')." ".ap_backquote($wpdb->options)." ".__('Aggiunta','albo-online')."\n");
/*		$UpdateProgressivo="UPDATE `".$wpdb->options."` SET `option_value` = '".get_option('opt_AP_AnnoProgressivo')."'	WHERE `option_name` ='opt_AP_AnnoProgressivo';\n";
		$UpdateProgressivo.="UPDATE `".$wpdb->options."` SET `option_value` = '".get_option('opt_AP_NumeroProgressivo')."' WHERE `option_name` ='opt_AP_NumeroProgressivo';";
		fwrite($fplog,"Sql Aggiornamento Tabella ".$wpdb->options." per Progressivo ed Anno Progressivo Aggiunti\n");*/
		fwrite($fp,$UpdateProgressivo);
		fclose($fp);
/*		if ($Echo)	echo '<li><span style="color:green;">Sql Aggiornamento Tabella '.$wpdb->options.' per Progressivo ed Anno Progressivo Aggiunti</span></li>'
				. '</ul>';*/
		if (is_dir($Dir) And is_dir($DirTmp)){
			// Crea l'archivio
		 	$zip = new PclZip($nomefileZip);
			// Inizializzazione dell'iterator a cui viene passato 
			// l'iteratore ricorsivo delle directory a cui viene passata la directory da zippare
			$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($DirTmp));
			// Ciclo tutti gli elementi dell'iteratore, i files estratti dall'iteratore
			foreach ($iterator as $key=>$value) {
				if (substr($key,-1)!="."){
					$zip->add(realpath($key),PCLZIP_OPT_REMOVE_PATH,dirname($key));
					$Risultato.='<span style="color:green;">'.__("Aggiunto all'archivio","albo-online").':</span> '.$key.'<br />';
					fwrite($fplog,"File ".$key." ".__("Aggiunto all'archivio","albo-online")."\n");
				}
			}
			$allegati=ap_get_all_allegati();
			if ($Echo) echo "<h3>".__("Avvio Backup Allegati","albo-online")."</h3>"
				. "</ul>";
			$BaseUploadAllegati=AP_BASE_DIR.get_option('opt_AP_FolderUpload');
			foreach ($allegati as $allegato) {
			//echo $allegato->Allegato;
				if(is_file($allegato->Allegato)){
					if (ap_isAllowedExtension( $allegato->Allegato)) {
						$zip->add(realpath($allegato->Allegato),PCLZIP_OPT_REMOVE_PATH,$BaseUploadAllegati);//dirname($allegato->Allegato));
						$tmp_risultato='<span style="color:green;">'.__("Aggiunto all'allegato","albo-online").':</span> '.$allegato->Allegato;
						fwrite($fplog,"File ".$allegato->Allegato." ".__("Aggiunto","albo-online")."\n");
					}else{
						$tmp_risultato='<span style="color:red;">'.__("Allegato NON Aggiunto, estensione non permessa","albo-online").':</span> '.$allegato->Allegato;
						fwrite($fplog,"File ".$allegato->Allegato." ".__("NON Aggiunto, estensione non permessa","albo-online")."\n");						
					} 
				}else{
					$tmp_risultato='<span style="color:red;">'.__("File Allegato non trovato","albo-online").':</span> '.$allegato->Allegato;
					fwrite($fplog,"File ".$allegato->Allegato." ".__("NON Aggiunto, file inesistente","albo-online")."\n");
				}
				$Risultato.=$tmp_risultato.'<br />';
				if ($Echo)	
					echo '<li>'. $tmp_risultato .'</li>';					
									
			}
			// Chiusura e momorizzazione del del file
			$Risultato.= __("Archivio creato con successo","albo-online").": ".$Dir."/".$NomeFile.".zip";
			fwrite($fplog,__("Archivio creato con successo","albo-online").": ".$Dir."/".$NomeFile.".zip\n");
			if ($Echo) echo "</ul>"
				. "<h3>Backup Completato</h3>";
		}
	}else{
		$DirLog=str_replace("\\","/",$Destinazione);
		$nomefileLog=$DirLog."/msg.txt";
		$fplog = @fopen($nomefileLog, "wb");
		$Risultato.=__("Non risulta Installata la libreria per Zippare i files indispensabile per la procedura","albo-online")."<br />";
		fwrite($fplog,__("Non risulta Installata la libreria per Zippare i files indispensabile per la procedura","albo-online")."\n");
		if ($Echo) echo "<h3>".__("Non risulta Installata la libreria per Zippare i files indispensabile per la procedura","albo-online")."</h3>";
		return;	
	}
	//Svuoto cartella tmp che contiene i files dati
	ap_SvuotaDirectory($DirTmp,$fplog);
	fclose($fplog);
	$fpmsg = @fopen($Destinazione."/BackupDatiAlbo/tmp/msg.txt", "wb");
	fwrite($fpmsg,$Risultato);
	fclose($fpmsg);
	return $nomefileZip;
}

function ap_backquote($a_name) {
	if (!empty($a_name) && $a_name != '*') {
		if (is_array($a_name)) {
			$result = array();
			reset($a_name);
			while(list($key, $val) = each($a_name)) 
				$result[$key] = '`' . $val . '`';
			return $result;
		} else {
			return '`' . $a_name . '`';
		}
	} else {
		return $a_name;
	}
} 

function ap_MakeZipOblio(){
	$Dir=str_replace("\\","/",WP_CONTENT_DIR.'/AlboOnLine/OblioDatiAlbo');
	$DirTmp=$Dir."/tmp";
	$DirTmpBox=$DirTmp."/BoxSingAtto";
	if(!is_dir($DirTmp))
		if (!mkdir($DirTmp, 0744))
			return FALSE;
	$nomefileZip=$Dir."/oblio".date("Ymdgis").".zip";
	if (class_exists('PclZip')) {	
	 	$zip = new PclZip($nomefileZip);
		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($DirTmpBox));
		// Ciclo tutti gli elementi dell'iteratore, i files estratti dall'iteratore
		foreach ($iterator as $key=>$value) {
			if (substr($key,-1)!="."){
				$zip->add(realpath($key),PCLZIP_OPT_REMOVE_PATH,dirname($key));
			}
		}
	}else
		return FALSE;
	ap_SvuotaDirectory($DirTmpBox,NULL);
	return TRUE;
}
function ap_BackupFilesAllegatiOblio($idAtto){
	global $wpdb;
	$Sql=ap_get_SQL_Oggetto($wpdb->table_name_Allegati,"IdAtto","=",$idAtto);
	$Sql.=ap_get_SQL_Oggetto($wpdb->table_name_Atti,"IdAtto","=",$idAtto);
	$Dir=str_replace("\\","/",WP_CONTENT_DIR.'/AlboOnLine/OblioDatiAlbo');
	$DirTmp=$Dir."/tmp";
	$DirTmpBox=$DirTmp."/BoxSingAtto";
	$DirAllegati=str_replace("\\","/",AP_BASE_DIR.get_option('opt_AP_FolderUpload'));
	$FileSql=$DirTmpBox."/Atto_".$idAtto."_Oblio.sql";
	$ControlloDir=TRUE;
	require_once('inc/pclzip.php');
	$nomefileZip=$DirTmpBox."/oblio_atto_".$idAtto.".zip";
	if (class_exists('PclZip')) {
//		echo $Dir." <br />".$DirTmp." <br />".$DirAllegati." <br />".$DirLog."";exit;
		if (!is_dir ( $Dir)){
			if (!mkdir($Dir, 0744)) 
				$ControlloDir=FALSE;
			else{
				if(!is_dir($DirTmp))
					if (!mkdir($DirTmp, 0744))
						$ControlloDir=FALSE;
				if(!is_dir($DirTmpBox))				
					if (!mkdir($DirTmpBox, 0744))
						$ControlloDir=FALSE;			
			}
		}else{
			if(!is_dir($DirTmp))
				if (!mkdir($DirTmp, 0744))
				  $ControlloDir=FALSE;
			if(!is_dir($DirTmpBox))
				if (!mkdir($DirTmpBox, 0744))
				  $ControlloDir=FALSE;			
		}
		if (!$ControlloDir){
			return $ControlloDir;
		}
		$fp = @fopen($FileSql, "wb");
		fwrite($fp,$Sql);
		fclose($fp);
		if (is_dir($Dir) And is_dir($DirTmp) And is_dir($DirTmpBox)){
			// Crea l'archivio
		 	$zip = new PclZip($nomefileZip);
			$zip->add(realpath($FileSql),PCLZIP_OPT_REMOVE_PATH,dirname($FileSql));
			$allegati=ap_get_all_allegati_atto($idAtto);
			foreach ($allegati as $allegato) {
			//echo $allegato->Allegato;
				if (substr(basename( $allegato->Allegato ),-4)==".pdf" or 
					substr(basename( $allegato->Allegato ),-4)==".p7m") 
					$zip->add(realpath($allegato->Allegato),PCLZIP_OPT_REMOVE_PATH,dirname($allegato->Allegato));
			}
		}
		
		if (file_exists($FileSql))
			@unlink($FileSql);
	}else{
		return FALSE;	
	}
	//Svuoto cartella tmp che contiene i files dati
//	ap_SvuotaDirectory($DirTmp,NULL);
	return $nomefileZip;
}


function ap_oblio_atti($Atti){
	global $wpdb;
	$Dir=str_replace("\\","/",WP_CONTENT_DIR.'/AlboOnLine/OblioDatiAlbo');
	$DirTmp=$Dir."/tmp";
	$MessaggiRitorno=array("Message"=>"","Message2"=>"");
	$ControlloDir=TRUE;
	if (!is_dir ( $Dir)){
		if (!mkdir($Dir, 0744)) 
			$ControlloDir=FALSE;
		else{
			if(!is_dir($DirTmp))
				if (!mkdir($DirTmp, 0744))
					$ControlloDir=FALSE;
		}
	}else{
		if(!is_dir($DirTmp))
			if (!mkdir($DirTmp, 0744))
			  $ControlloDir=FALSE;
	}
	if(!$ControlloDir){
		$Msg=" ".__("Non riesco a creare le cartelle necessarie all'operazione","albo-online");
		return $Msg;	
	}
	ap_SvuotaDirectory($DirTmp,NULL);
	$Msg="";
	if(!is_array($Atti)){
		$Atti=array($Atti);
		$Bulk=FALSE;
	}
	else{
		$Bulk=TRUE;
	}
	foreach($Atti as $Atto){
//		echo $Atto."<br />";
		$riga=ap_get_atto($Atto);
		$riga=$riga[0];
		$Msg.="Atto ".$riga->IdAtto;
		$MsgAlle=" ".__("Allegati","albo-online");
		ap_BackupFilesAllegatiOblio($riga->IdAtto);
		if (ap_cvdate($riga->DataOblio) <= ap_cvdate(date("Y-m-d"))){
			if(ap_del_allegati_atto((int)$Atto)){
				$MessaggiRitorno["Message2"]=10;// Allegati all'Atto Cancellati
				$MsgAlle.=" ".__("all'Atto Cancellati","albo-online");
			}else{
				$MessaggiRitorno["Message2"]=11;//Allegati all'Atto NON Cancellati
				$MsgAlle.=" ".__("all'Atto NON Cancellati","albo-online");
			}			
			$res=ap_del_atto((int)$Atto);
			if (!is_array($res)){
				$MessaggiRitorno["Message"]= 2;//Atto Cancellato
				$Msg.=" ".__("Cancellato","albo-online");
			}else{
				if ($res['allegati']>0) {
					$MessaggiRitorno["Message"]= 7;
					$Msg.=" ".__("Impossibile cancellare un Atto che contiene Allegati %%br%%Cancellare prima gli Allegati e poi riprovare","albo-online");
				}else{
					$MessaggiRitorno["Message"]= 6;//Atto non Cancellato
					$Msg.=" ".__("NON Cancellato","albo-online");
				}		
			}
		}else{
			$MessaggiRitorno["Message2"]=99;//OPERAZIONE NON AMMESSA!<br />l'atto non � ancora da eliminare
			$Msg.=" ".__("OPERAZIONE NON AMMESSA! %%br%%l'atto non è ancora da eliminare","albo-online");
		}
		$Msg.=$MsgAlle." %%br%%";		
	}
	ap_MakeZipOblio();
	if ($Bulk)
		return $Msg;
	else
		return $MessaggiRitorno;
}
/**
 * Funzioni tipi di Files
 */
	function ap_get_tipidifiles(){
		if(get_option('opt_AP_TipidiFiles')  != '' || !get_option('opt_AP_TipidiFiles')){
			return get_option('opt_AP_TipidiFiles');
		}
	}
	function ap_isAllowedExtension($fileName) {
		$TipidiFiles=ap_get_tipidifiles();
		$Estensione=explode(".", $fileName);
		$Estensione=end($Estensione);
		if(isset($TipidiFiles[strtolower($Estensione)])){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	function ap_isExtensioType($Estensione){
		$TipidiFiles=ap_get_tipidifiles();
		if($TipidiFiles){
			foreach ( $TipidiFiles as $key => $value ) {
				if($key==$Estensione){
					return TRUE;
				}
			}			
		}
		return FALSE;		
	}
	function ap_ExtensionType($fileName) {
		$NomeFile=explode(".", $fileName);
		$ext=strtolower($NomeFile[count($NomeFile)-1]);
		if (!ap_isExtensioType($ext)){
			return "ndf";
		}
	  return $ext;
	}
	function ap_tipiFileAmmessi($Mime=False){
		$TipidiFiles=ap_get_tipidifiles();
		$Tipi=array();
		foreach ( $TipidiFiles as $key => $value ) {
			if($Mime){
				if($key!="ndf")
					$Tipi[]=array("."=>$key,"Icon"=>$value['Icona']);
			}else{
				$Tipi[]=$key;	
			}
		}
		return $Tipi;
	}
	function ap_num_tipidifiles_atti(){
		global $wpdb;
		$Allegati=$wpdb->get_results("SELECT Allegato FROM $wpdb->table_name_Allegati");
		$TipidiFiles=ap_get_tipidifiles();
		foreach ( $TipidiFiles as $key => $value ) {
			$Tipi[$key]=0;	
		}
		foreach($Allegati as $Alleagato){
			$Estensione=explode(".", $Alleagato->Allegato);
			$Estensione=end($Estensione);
			if(isset($Tipi[strtolower($Estensione)])){
				$Tipi[strtolower($Estensione)]++;
			}
		}	
		return $Tipi;
	}
	function ap_delete_tipofiles($ID){
		$TipidiFiles=ap_get_tipidifiles();
		$NewTipidiFiles=array();
		$Trovato=FALSE;
		$TipoTrovato="";
		foreach ( $TipidiFiles as $key => $value ) {
			if($key!=$ID){
				$NewTipidiFiles[$key]=$value;
				$Trovato=TRUE;
			}else{
				$TipoTrovato=$NewTipidiFiles[$key];
			}
		}
		if($Trovato){
			ap_insert_log(8,2,$ID,__("Cancellazione Tipo File","albo-online"));
			update_option('opt_AP_TipidiFiles', $NewTipidiFiles);
		}
		return $Trovato;
		
	}
	function ap_add_tipofiles($ID,$Descrizione,$Icona,$Verifica){
		$ID=trim($ID);
		$TipidiFiles=ap_get_tipidifiles();
		$Trovato=FALSE;
		foreach ( $TipidiFiles as $key => $value ) {
			if(strtolower($key)==strtolower($ID)){
				$Trovato=TRUE;
			}
		}
		if(!$Trovato){
			$TipidiFiles[strtolower($ID)]=array("Descrizione"=>$Descrizione,"Icona"=>$Icona,"Verifica"=>htmlspecialchars($Verifica));
			update_option('opt_AP_TipidiFiles', $TipidiFiles);
			ap_insert_log(8,1,$ID,"{".__("Descrizione","albo-online")."}==> $Descrizione 
								  {".__("Icona","albo-online")."}==> $Icona
								  {".__("Verifica","albo-online")."}==> $Verifica");
		}
		return !$Trovato;
	}
	function ap_memo_tipofiles($ID,$Descrizione,$Icona,$Verifica){
		$ID=trim($ID);
		$TipidiFiles=ap_get_tipidifiles();
		$TipidiFiles[strtolower($ID)]=array("Descrizione"=>$Descrizione,"Icona"=>$Icona,"Verifica"=>htmlspecialchars($Verifica));
		ap_insert_log(8,2,$ID,"{".__("Descrizione","albo-online")."}==> $Descrizione 
					  {".__("Icona","albo-online")."}==> $Icona
					  {".__("Verifica","albo-online")."}==> $Verifica");
		return update_option('opt_AP_TipidiFiles', $TipidiFiles);
	}
/*
 * Funzioni per la gestione delle Funzioni dei Responsabili
 */
	function ap_get_Funzioni_Responsabili($Output="Array",$ID="",$Name="",$Selezionato=""){
		$TabResponsabili=get_option('opt_AP_TabResp');
		if($TabResponsabili){
			$TRs=json_decode($TabResponsabili);
		}else{
			$TRs=json_decode('[{"ID":"","Funzione":"","Display":"No","StaCert":"No"}]');
		}		
		$TabFunzResp=array();
		foreach ($TRs as$TR ){
			$TabFunzResp[$TR->ID]=array("Descrizione" =>$TR->Funzione,"Display" =>$TR->Display,"StaCert" =>$TR->StaCert);
		}
		switch ($Output){
			case "Array":
				return $TabFunzResp;
				break;
			case "Select":
				$Lista="<select id=\"$ID\" name=\"$Name\">";
				foreach($TabFunzResp as $Key=>$Dati){
					$Selected=($Key==$Selezionato)?"Selected":"";
					$Lista.="<option value=\"$Key\" $Selected>".$Dati["Descrizione"]."</option>";
				}
				$Lista.="</select>";
				return $Lista;
				break;
			default:
				return $TabFunzResp;
				break;
		}		
	}
	function ap_get_Funzione_StampaCertificatoSX(){
		$Funzioni=ap_get_Funzioni_Responsabili();
		foreach($Funzioni as $Key=>$Dati){
			if($Dati["StaCert"]=="Si"){
				return(array($Key,$Dati["Descrizione"]));
			}
		}
		return array("","");
	}
	function ap_get_Funzione_Responsabile($Funzione="",$Campo="Indice"){
		$Funzioni=ap_get_Funzioni_Responsabili();
		foreach($Funzioni as $Key=>$Dati){
			if($Key==$Funzione){
				switch($Campo){
					case "Descrizione":
						return $Dati["Descrizione"];
						break;
					case "Indice":
						return $Key;
						break;
					case "Display":
						return $Dati["Display"];
						break;
				}
			}
		}
		return "";
	}
/**
* Funzioni per chiamate Ajax
*/
function ap_MemoFunzioni(){
//	print_r($_POST);wp_die();
	check_ajax_referer('adminsecretAlboOnLine','security');
	$ValoriPost= explode("&", str_replace("%20"," ",filter_input(INPUT_POST, 'valori')));
	$Valori=array();
	$NumeroRighe=0;
	$Indici=array();
	foreach($ValoriPost as $Valore){
		$Riga=explode("=",$Valore);
		$Valori[$Riga[0]]= $Riga[1];
		if(substr($Riga[0],0,16)=="GridFunzioni_ID_"){
			$Indici[]=(int)substr($Riga[0],16,3);
			$NumeroRighe++;
		}
	}
	$funzioni=array();
	for($i=1;$i<=$NumeroRighe;$i++){
		$Indice=$Indici[$i-1];
		$funzioni[]=array("ID"  => $Valori["GridFunzioni_ID_$Indice"],
		        "Funzione" => str_replace("+"," ",$Valori["GridFunzioni_funzione_$Indice"]),
		        "Display"  => (isset($Valori["GridFunzioni_visualizza_$Indice"])?"Si":"No"),
		        "StaCert"  => (isset($Valori["GridFunzioni_staincert_$Indice"])?"Si":"No"));
	}
	update_option('opt_AP_TabResp', json_encode($funzioni)); 
	echo "Memorizzazione avvenuta con successo";
	wp_die();
}
function ap_dismiss_alboonline_notice(){
	check_ajax_referer('adminsecretAlboOnLine','security');
	update_option("alboonline-notice-dismissed",TRUE);
	wp_die();
}
function ap_rimuoviallegatoPP(){
	global $wpdb;
	check_ajax_referer('adminsecretAlboOnLine','security');
	$IDAllegato= filter_input(INPUT_POST, 'idAllegato');
	$IDAtto= filter_input(INPUT_POST, 'idAtto');
	$Motivo= filter_input(INPUT_POST, 'desmotivo');
	
	$allegato=ap_get_allegato_atto($IDAllegato);
	if (file_exists($allegato[0]->Allegato) && is_file($allegato[0]->Allegato))
		if (unlink($allegato[0]->Allegato)){
			$wpdb->update(	$wpdb->table_name_Allegati,
		 					array('Note' => $Motivo, 'Allegato'=>basename($allegato[0]->Allegato)),
		 					array('IdAllegato' => $IDAllegato ),
		 					array('%s','%s'),
		 					array('%d'));
			ap_insert_log(3,3,$IDAllegato,"{".__("Nome Allegato","albo-online")."}==> ".$allegato[0]->TitoloAllegato." Cancellato dopo scadenza",$IDAtto);
			echo "\nCancellato correttamente";
		}else{
			echo "\nNon sono riuscito a Cancellarlo";
		}	
	wp_die();
}
function ap_LoadDefaultFunzioni(){
	check_ajax_referer('adminsecretAlboOnLine','security');
	$Default='[{"ID":"RP","Funzione":"'.__('Responsabile Procedimento','albo-online').'","Display":"Si"},{"ID":"OP","Funzione":"'.__('Gestore procedura','albo-online').'","Display":"Si"},{"ID":"SC","Funzione":"'.__('Segretario Comunale','albo-online').'","Display":"No"},{"ID":"RB","Funzione":"'.__('Responsabile Pubblicazione','albo-online').'","Display":"No"},{"ID":"DR","Funzione":"'.__('Direttore dei Servizi e Amministrativi','albo-online').'","Display":"No"}]';
	update_option('opt_AP_TabResp',$Default ); 
	echo __('Caricamento valori di default avvenuto con successo','albo-online');
	wp_die();
}
/*****************************************************
*  Funzioni per adeguamento GDPR
*****************************************************
*/
	function ap_del_ip_log(){
		global $wpdb;
		$Sql = 'UPDATE '.$wpdb->table_name_Log.' SET IPAddress="" WHERE IPAddress <>""';
		$Result=$wpdb->query($Sql);
	//	echo "Sql==".$wpdb->last_query ."    Ultimo errore==".$wpdb->last_error;exit;
		if($Result!==FALSE)
				return $Result;
			else
				return $wpdb->last_error;
	}
function ap_Rmdir($src) {
    $dir = opendir($src);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            $full = $src . '/' . $file;
            if ( is_dir($full) ) {
                ap_Rmdir($full);
            }
            else {
                unlink($full);
            }
        }
    }
    closedir($dir);
    rmdir($src);
}
?>
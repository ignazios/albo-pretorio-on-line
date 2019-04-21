<?php
/**
 * Libreria di funzioni necessarie al plugin per la gestione dell'albo.
 * @link       http://www.eduva.org
 * @since      4.2
 *
 * @package    ALbo On Line
 */

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

################################################################################
// Funzioni 
################################################################################
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
		$Stato.="Non risco a Creare il file robots.txt in ".APHomePath."%%br%%";
	}else{
		$Stato.="File robots.txt creato con successo in ".APHomePath."%%br%%";
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
		$Stato.="Non risco a Creare il file ".ap_decodenamefile()." in ".$dir."%%br%%";
	}else{
		$Stato.="File ".ap_decodenamefile()." creato con successo in ".$dir."%%br%%";
	}
	fclose($id);
//Creazione robots.txt
	$Stato.=ap_crearobots();
//Creazione index.php
	$id = fopen($dir."/index.php", "wt");
	if (!fwrite($id,$index )){
		$Stato.="Non risco a Creare il file index.php in ".$dir;
	}else{
		$Stato.="File index.php creato con successo in ".$dir;
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
	$Risultato=AP_CreaCategoriaBase("Bandi e gare","Bandi e gare",30);
	$Risultato.=AP_CreaCategoriaBase("Contratti - Personale ATA","Contratti - Personale ATA",30);
	$Risultato.=AP_CreaCategoriaBase("Contratti - Personale Docente","Contratti - Personale Docente",30);
	$Risultato.=AP_CreaCategoriaBase("Contratti e convenzioni","Contratti e convenzioni",30);
	$Risultato.=AP_CreaCategoriaBase("Convocazioni","Convocazioni",30);
	$Risultato.=AP_CreaCategoriaBase("Delibere Consiglio di Istituto","Delibere Consiglio di Istituto",30);
	$Risultato.=AP_CreaCategoriaBase("Documenti altre P.A.","Documenti altre P.A.",30);
	$Risultato.=AP_CreaCategoriaBase("Esiti esami","Esiti esami",30);
	$Risultato.=AP_CreaCategoriaBase("Graduatorie","Graduatorie",365);
	$Risultato.=AP_CreaCategoriaBase("Organi collegiali","Organi collegiali",30);
	$Risultato.=AP_CreaCategoriaBase("Organi collegiali - Elezioni","Organi collegiali - Elezioni",30);
	$Risultato.=AP_CreaCategoriaBase("Privacy","Privacy",365);
	$Risultato.=AP_CreaCategoriaBase("Programmi annuali e Consuntivi","Programmi annuali e Consuntivi",365);
	$Risultato.=AP_CreaCategoriaBase("Regolamenti","Regolamenti",365);
	$Risultato.=AP_CreaCategoriaBase("Sicurezza","Sicurezza",365);
	return $Risultato;
}

function ap_CreaTabella($Tabella){
global $wpdb;

	switch ($Tabella){
		case $wpdb->table_name_Atti:
			$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->table_name_Atti." (
			  `IdAtto` int(11) NOT NULL auto_increment,
			  `Numero` int(4) NOT NULL default 0,
			  `Anno` int(4) NOT NULL default 0,
			  `Data` date NOT NULL default '0000-00-00',
			  `Riferimento` varchar(100) NOT NULL,
			  `Oggetto` varchar(200) NOT NULL default '',
			  `DataInizio` date NOT NULL default '0000-00-00',
			  `DataFine` date default '0000-00-00',
			  `Informazioni` text NOT NULL default '',
			  `IdCategoria` int(11) NOT NULL default 0,
			  `Soggetti` varchar(100) NOT NULL,
  			  `DataAnnullamento` date DEFAULT '0000-00-00',
  			  `MotivoAnnullamento` varchar(200) DEFAULT '',
  			  `Ente` int(11) NOT NULL DEFAULT '0',
			  PRIMARY KEY  (`IdAtto`));";
			break;
		case $wpdb->table_name_Attimeta:
			$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->table_name_Attimeta." (
			  `IdAttoMeta` int(11) NOT NULL auto_increment,
			  `IdAtto` int(11) NOT NULL,
			  `Meta` varchar(100) NOT NULL,
			  `Value` TEXT,
			  PRIMARY KEY  (`IdAttoMeta`));";
			break;
		case $wpdb->table_name_Allegati:
			$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->table_name_Allegati." (
			  `IdAllegato` int(11) NOT NULL auto_increment,
			  `TitoloAllegato` varchar(255) NOT NULL default '',
			  `Allegato` varchar(255) NOT NULL default '',
			  `IdAtto` int(11) NOT NULL default 0,
			  PRIMARY KEY  (`IdAllegato`));";
			break;
		case $wpdb->table_name_Categorie:
			$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->table_name_Categorie." (
			  `IdCategoria` int(11) NOT NULL auto_increment,
			  `Nome` varchar(255) NOT NULL default '',
			  `Descrizione` varchar(255) NOT NULL default '',
			  `Genitore` int(11) NOT NULL default 0,
			  `Giorni` smallint(3) NOT NULL DEFAULT '0',
			  PRIMARY KEY  (`IdCategoria`));";
			break;
		case $wpdb->table_name_Log:
			$sql= "CREATE TABLE  IF NOT EXISTS ".$wpdb->table_name_Log." (
	  		  `Data` timestamp NOT NULL default CURRENT_TIMESTAMP,
	  		  `Utente` varchar(60) NOT NULL default '',
	          `IPAddress` varchar(16) NOT NULL default '',
	          `Oggetto` int(1) NOT NULL default 1,
	          `IdOggetto` int(11) NOT NULL default 1,
	          `IdAtto` int(11) NOT NULL default 0,
	          `TipoOperazione` int(1) NOT NULL default 1,
	          `Operazione` text);";
	 		break;
	 	case $wpdb->table_name_RespProc:
		    $sql = "CREATE TABLE  IF NOT EXISTS ".$wpdb->table_name_RespProc." (
	  		  `IdResponsabile` int(11) NOT NULL auto_increment,
	  		  `Cognome` varchar(20) NOT NULL default '',
	          `Nome` varchar(20) NOT NULL default '',
	          `Email` varchar(100) NOT NULL default '',
	          `Telefono` varchar(30) NOT NULL default '',
	          `Orario` varchar(60) NOT NULL default '',
	          `Note` text,
			   PRIMARY KEY  (`IdResponsabile`));";   
			break;
		case $wpdb->table_name_Enti:
	 		$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->table_name_Enti." (
			  `IdEnte` int(11) NOT NULL auto_increment,
			  `Nome` varchar(100) NOT NULL,
			  `Indirizzo` varchar(150) NOT NULL default '',
			  `Url` varchar(100) NOT NULL default '',
			  `Email` varchar(100) NOT NULL default '',
			  `Pec` varchar(100) NOT NULL default '',
			  `Telefono` varchar(40) NOT NULL default '',
			  `Fax` varchar(40) NOT NULL default '',
	          `Note` text,
			  PRIMARY KEY  (`Idente`));";
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
		return new WP_Error('db_insert_error', 'Non sono riuscito a creare il campo '.$Campo.' Nella Tabella '.$Tabella.' Errore '.$wpdb->last_error, $wpdb->last_error);
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
		return new WP_Error('db_insert_error', 'Non sono riuscito a modificare il campo '.$Campo.' Nella Tabella '.$Tabella.' Errore '.$wpdb->last_error, $wpdb->last_error);
	} else{
		return true;
	}
}

function ap_ModificaParametriCampo($Tabella, $Campo, $Tipo, $Parametro){
	global $wpdb;
//	echo "ALTER TABLE $Tabella CHANGE $Campo $Campo $Tipo $Parametro";exit;
	if ( false === $wpdb->query("ALTER TABLE $Tabella CHANGE $Campo $Campo $Tipo $Parametro")){
		return new WP_Error('db_insert_error', 'Non sono riuscito a modificare il campo '.$Campo.' Nella Tabella '.$Tabella.' Errore '.$wpdb->last_error, $wpdb->last_error);
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
		$filename=substr($baseName,0,strrpos($baseName,".")).$inc.substr($baseName,strrpos($baseName,"."),strlen($baseName)-strrpos($baseName,"."));
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
	return mktime(0,0,0,$rsl[1], $rsl[2],$rsl[0]);
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
return implode($rsl,'-');
}
function ap_VisualizzaData($dataDB){
	$dataDB=substr($dataDB,0,10);
	$rsl = explode ('-',$dataDB);
	$rsl = array_reverse($rsl);
	return implode($rsl,'/');
}
function ap_VisualizzaOra($dataDB){
return substr($dataDB,10);
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
	
TipoOperazione int(1)
	1=> Inserimento
	2=> Modifica
	3=> Cancellazione
	4=> Pubblicazione
	5=> Incremento (solo per le statistiche)
	6=> Annullamento
*/
				  
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
	return $wpdb->get_results("SELECT date( `Data` ) AS Data, count( `Data` ) AS Accessi
							   FROM $wpdb->table_name_Log
							   WHERE `Oggetto` =5
							   AND `IdOggetto` =".(int)$IdAtto."
							   GROUP BY date( `Data` )
							   ORDER BY Data DESC;");	
}
function ap_get_Stat_Num_log($IdAtto,$Oggetto){
global $wpdb;
	switch ($Oggetto){
		case 5:
			return (int)($wpdb->get_var( $wpdb->prepare( "SELECT COUNT(IdOggetto) FROM $wpdb->table_name_Log WHERE Oggetto = %d AND IdOggetto = %d",(int) $Oggetto,(int)$IdAtto)));	
			break;
		case 6:
			return (int)($wpdb->get_var( $wpdb->prepare( "SELECT COUNT(IdOggetto) FROM $wpdb->table_name_Log WHERE Oggetto = %d AND IdAtto = %d",(int) $Oggetto,(int)$IdAtto)));	
			break;
	}	
}

function ap_get_Stat_Download($IdAtto){
global $wpdb;


	return $wpdb->get_results("SELECT date( `Data` ) AS Data, TitoloAllegato, Allegato, count( `Data` ) AS Accessi
							   FROM `wp_albopretorio_log`
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
                    <label for=\"newMetaName[".$i."]\">Nome Meta: </label><input name=\"newMetaName[".$i."]\" id=\"newMetaName[".$i."]\" value=\"".$Rec->Meta."\"/>
                    <label for=\"newValue[".$i."]\">Valore Meta</label><input name=\"newValue[".$i."]\" id=\"newValue[".$i."]\" value=\"".$Rec->Value."\">
                    <button type=\"button\" class=\"setta-def-data EliminaRiga\">Elimina riga</button>
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
	$Sql="SELECT $wpdb->table_name_Atti.* FROM $wpdb->table_name_Atti INNER JOIN $wpdb->table_name_Attimeta ON"
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
	if ( false === $wpdb->insert($wpdb->table_name_Categorie,array('Nome' => $cat_name,
																   'Genitore' => $cat_parente,
																   'Descrizione' => $cat_descrizione,
																   'Giorni' => $cat_durata),
															 array('%s',
															 	   '%d',
															 	   '%s',
															 	   '%d')))	
        return new WP_Error('db_insert_error', 'Non sono riuscito ad inserire la Nuova Categoria'.$wpdb->last_error, $wpdb->last_error);
    else{
    	$NomeCategoria=ap_get_categoria($cat_parente);
    	if(is_array($NomeCategoria)and count($NomeCategoria)>0){
			$NomeCategoria=$NomeCategoria[0];
			$CatGenitore=$NomeCategoria->Nome;
		}else{
			$CatGenitore="Non Specificato";
		}
		ap_insert_log(2,1,$wpdb->insert_id,"{IdCategoria}==> $wpdb->insert_id
		                                    {Nome}==> $cat_name 
		                                    {Descrizione}==> $cat_descrizione 
											{Durata}==> $cat_durata
											{IdGenitore}==> $cat_parente
											{Genitore}==> $CatGenitore");
	}
}

function is_array_di_categorie($Categorie){
	$ArrCategorie=explode(",",$Categorie);
	$Esito=false;
	foreach($ArrCategorie as $Cate){
		if(ap_get_categoria($Cate)){
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
		$Log.='{Nome}==> '.$cat_name.' ';
	if ($Categoria->Genitore!=$cat_parente){
		$Log.='{IdGenitore}==> '.$cat_parente.' ';
		$CategoriaPadre=ap_get_categoria($cat_parente);
		$CategoriaPadre=$CategoriaPadre[0];
		$Log.='{Genitore}==> '.$CategoriaPadre->Nome.' ';
	}
	if ($Categoria->Descrizione!=$cat_descrizione)
		$Log.='{Descrizione}==> '.$cat_descrizione.' ';
	if ($Categoria->Giorni!=$cat_durata)
		$Log.='{Giorni}==> '.$cat_durata.' ';
	if ( false === $wpdb->update($wpdb->table_name_Categorie,
					array('Nome' => $cat_name,
						  'Genitore' => $cat_parente,
						  'Descrizione' => $cat_descrizione,
						  'Giorni' => $cat_durata),
						  array( 'IdCategoria' => $id ),
						  array('%s',
								'%d',
								'%s',
								'%d'),
						  array('%d')		 
						  ))
    	return new WP_Error('db_update_error', 'Non sono riuscito a modifire la Categoria'.$wpdb->last_error, $wpdb->last_error);
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
	if ($default=="Nessuno"){
		$output .= "\t<option value='0' selected='selected'>Nessuno</option>\n";
	}else{
		$output .= "\t<option value='0' >Nessuno</option>\n";
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
			$Sql.=" And Numero >0 AND DataFine <= '".ap_oggi()."'";
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
	if ($default=="Nessuno"){
		$output .= "\t<option value='0' selected='selected'>Nessuno</option>\n";
	}else{
		$output .= "\t<option value='0' >Nessuno</option>\n";
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
				$output .= "<a href='".$link."=".$c->IdCategoria."' title='Ci sono ".$numAtti." Atti nella Categoria ".$c->Nome."'><span style='font-size:".$pix."em;'>".$c->Nome."</span></a><br />\n";	
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
		ap_insert_log(2,3,$id,"Cancellazione Categoria");

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
	$Sql="SELECT $wpdb->table_name_Atti.Numero,$wpdb->table_name_Atti.Anno, $wpdb->table_name_Atti.IdCategoria 
	      FROM $wpdb->table_name_Atti left join  $wpdb->table_name_Categorie on 
		  		$wpdb->table_name_Atti.IdCategoria =  $wpdb->table_name_Categorie.IdCategoria 
		  WHERE $wpdb->table_name_Categorie.IdCategoria is null";
	return $wpdb->get_results($Sql);
}
################################################################################
// Funzioni Atti
################################################################################
function ap_UpdateSoggetti(){
	global $wpdb;
	$Sql="SELECT IdAtto,RespProc FROM $wpdb->table_name_Atti where Soggetti='';";
	$Atti = $wpdb->get_results($Sql);
	foreach($Atti as $Atto){
		if($Atto->Soggetti=="" And $Atto->RespProc>0){
			$Soggetti=array("RP"=>$Atto->RespProc);
			$wpdb->update($wpdb->table_name_Atti,
							array('Soggetti' => serialize($Soggetti)),
							array( 'IdAtto' => $Atto->IdAtto,Soggetti=>""),
							array( '%s'),
							array( '%d','%s'));		
		}
	
	}
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
	$Sql="SELECT $wpdb->table_name_Enti.Nome as NomeEnte,Numero,Anno,Riferimento,Oggetto,DataInizio,DataFine,$wpdb->table_name_Categorie.Nome as Categoria, DataAnnullamento, MotivoAnnullamento,Informazioni
		FROM $wpdb->table_name_Atti inner join $wpdb->table_name_Categorie on ($wpdb->table_name_Atti.IdCategoria =$wpdb->table_name_Categorie.IdCategoria) inner join $wpdb->table_name_Enti on ($wpdb->table_name_Atti.Ente=$wpdb->table_name_Enti.IdEnte) 
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
					<td>".wp_strip_all_tags($Atto->Informazioni)."</td>
					<td>$Atto->Categoria</td>
					<td $Annullato>$Atto->DataAnnullamento</td>
					<td $Annullato>".wp_strip_all_tags($Atto->MotivoAnnullamento)."</td>
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
				ap_insert_log(1,2,$Atto->IdAtto,"{DataOblio}==> ".$DataOblio);
			}		
		}			
	}
}
function ap_insert_atto($Ente,$Data,$Riferimento,$Oggetto,$DataInizio,$DataFine,$DataOblio,$Note,$Categoria,$Responsabile,$Soggetti){
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
				'Ente' => $Ente,
				'Numero' => $Numero,
				'Anno' =>  $Anno,
				'Data' => $Data,
				'Riferimento' => $Riferimento,
				'Oggetto' => $Oggetto,
				'DataInizio' => $DataInizio,
				'DataFine' => $DataFine,
				'DataOblio' => $DataOblio,
				'Informazioni' => $Note,
				'IdCategoria' => $Categoria,
				'RespProc' => $Responsabile,
				'Soggetti' => $Soggetti),
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
				'%s')))	{
// echo "Sql==".$wpdb->last_query ."    Ultimo errore==".$wpdb->last_error;exit;
          return 'Non sono riuscito ad inserire il nuovo Atto Sql=='.$wpdb->last_query .' Ultimo errore=='.$wpdb->last_error;
    }else{
		$newIDAtto=$wpdb->insert_id;
//    	echo "Sql==".$wpdb->last_query;exit;
	  	$NomeCategoria=ap_get_categoria($Categoria);
    	$NomeCategoria=$NomeCategoria[0];
		$NomeResponsabile=ap_get_responsabile($Responsabile);
		$NomeResponsabile=$NomeResponsabile[0];
		$NomeEnte=ap_get_ente($Ente);
		$NomeEnte=$NomeEnte->Nome;
		ap_insert_log(1,1,$wpdb->insert_id,"{IdAtto}==> $wpdb->insert_id
											{IdEnte} $Ente
											{Ente} $NomeEnte
											{Numero} $Numero/$Anno 
											{Data}==> $Data 
						                    {Riferimento}==> $Riferimento 
											{Oggetto}==> $Oggetto 
											{IdOggetto}==> $wpdb->insert_id
											{DataInizio}==> $DataInizio
											{DataFine}==> $DataFine
											{DataOblio}==> $DataOblio
											{Note}=> $Note
											{Categoria}==> $NomeCategoria->Nome
											{IdCategoria}==> $Categoria
											{Responsabile}==> $NomeResponsabile->Cognome $NomeResponsabile->Nome
											{IdResponsabile}==>$Responsabile"
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
		ap_insert_log(1,3,$id,"Cancellazione Atto",(int)$id);

		return True;
	}
}

function ap_memo_atto($id,$Ente,$Data,$Riferimento,$Oggetto,$DataInizio,$DataFine,$DataOblio,$Note,$Categoria,$Responsabile,$Soggetti){
	global $wpdb;
	$Atto=ap_get_atto($id);
	$Atto=$Atto[0];
	$Log='' ;
	if ($Atto->Ente!=$Ente){
    	$NEnte=ap_get_ente($Ente);
		$Log.='{IdEnte}==> '.$Ente.' ';
		$Log.='{Ente}==> '.$NEnte->Nome.' ';		
	}
	if ($Atto->Data!=$Data)
		$Log.='{Data}==> '.$Data.' ';
	if ($Atto->Riferimento!=$Riferimento)
		$Log.='{Riferimento}==> '.$Riferimento.' ';
	if ($Atto->Oggetto!=$Oggetto)
		$Log.='{Oggetto}==> '.$Oggetto.' ';
	if ($Atto->DataInizio!=$DataInizio)
		$Log.='{DataInizio}==> '.$DataInizio.' ';
	if ($Atto->DataFine!=$DataFine)
		$Log.='{DataFine}==> '.$DataFine.' ';
	if ($Atto->DataOblio!=$DataOblio)
		$Log.='{DataOblio}==> '.$DataOblio.' ';
	if ($Atto->Informazioni!=$Note)
		$Log.='{Informazioni}==> '.$Note.' ';
	if ($Atto->IdCategoria!=$Categoria){
    	$NomeCategoria=ap_get_categoria($Categoria);
    	$NomeCategoria=$NomeCategoria[0];
		$Log.='{IdCategoria}==> '.$Categoria.' ';
		$Log.='{Categoria}==> '.$NomeCategoria->Nome.' ';
	}
	if($Atto->Soggetti!=$Soggetti){
		$Responsabili="";
		foreach($Soggetti as $Soggetto){
			$NomeResponsabile=ap_get_responsabile($Soggetto);
			$Responsabili.="(".$Soggetto.") ".$NomeResponsabile[0]->Nome." ".$NomeResponsabile[0]->Cognome." <strong>".ap_get_Funzione_Responsabile($NomeResponsabile[0]->Funzione,"Descrizione")."</strong> ";
		}
		$Log.='{Soggetti}==> '.$Responsabili.' ';
	}
	$Soggetti=serialize($Soggetti);
	$Data=ap_convertiData($Data);
	$DataInizio=ap_convertiData($DataInizio);
	$DataFine=ap_convertiData($DataFine);
	$DataOblio=ap_convertiData($DataOblio);
	if ( false === $wpdb->update($wpdb->table_name_Atti,
					array('Ente' => $Ente,
						  'Data' => $Data,
						  'Riferimento' => $Riferimento,
						  'Oggetto' => $Oggetto,
						  'DataInizio' => $DataInizio,
						  'DataFine' => $DataFine,
						  'DataOblio' => $DataOblio,
						  'Informazioni' => $Note,
						  'IdCategoria' => $Categoria,
						  'RespProc' => $Responsabile,
						  'Soggetti' => $Soggetti),
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
								'%s'),
						  array('%d')))
    	return new WP_Error('db_update_error', 'Non sono riuscito a modifire l\' Atto'.$wpdb->last_error, $wpdb->last_error);
    else
    	ap_insert_log(1,2,$id,$Log);
	
}

function ap_update_selettivo_atto($id,$ArrayCampiValori,$ArrayTipi,$TestaMsg){
	global $wpdb;
	if ( false === $wpdb->update($wpdb->table_name_Atti,$ArrayCampiValori,array( 'IdAtto' => $id ),$ArrayTipi))
    	return new WP_Error('db_update_error', 'Non sono riuscito a modifire l\' Atto'.$wpdb->last_error, $wpdb->last_error);
    else{
		ap_insert_log(1,2,(int)$id,$TestaMsg.ap_ListaElementiArray($ArrayCampiValori));
		return 'Atto Aggiornato: %%br%%'.ap_ListaElementiArray($ArrayCampiValori);	
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
		return "Atto gia' PUBBLICATO con Numero Progressivo ".$risultato->Numero;
	if (($NumeroDaDb!=$NumeroOpzione) And ap_get_all_atti(9,0,0,0,"",0,0,"",0,0,TRUE)>0){
		return "Atto non PUBBLICATO:%%br%%Progressivo da ultima pubblicazione=$NumeroDaDb%%br%% Progressivo da parametri=$NumeroOpzione";
	}else{
		$x=$wpdb->update($wpdb->table_name_Atti,
									 array('Numero' => $NumeroOpzione),
									 array( 'IdAtto' => $IdAtto ),
									 array('%d'),
									 array('%d'));
	//  visualizza Sql Updateecho $wpdb->print_error();exit;
	 	if ($x==0){
	    	return 'Atto non PUBBLICATO:%%br%%Errore: '.$wpdb->last_error;
	    }
	    else{
			ap_insert_log( 1,4,$IdAtto,"{Stato Atto}==> Pubblicato 
			 							{Numero Assegnato}==> $NumeroOpzione ");	
			$NumeroOpzione+=1;
			update_option('opt_AP_NumeroProgressivo',$NumeroOpzione );
			return 'Atto PUBBLICATO';
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
		ap_insert_log(1,6,$IdAtto,"{Stato Atto}==> Annullato");
		if (!empty($Allegati))
			foreach($Allegati as $Allegato)
				ap_del_allegato_atto($Allegato,$IdAtto,"","S");
		return 9;
	}else{
	return 8;				
	}
}

function ap_get_dropdown_anni_atti($select_name,$id_name,$class,$tab_index_attribute, $default="Nessuno",$Stato=0) {
/*
 $Stato 
 	0 tutti
 	1 attivi
 	2 storici
*/
	global $wpdb;
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
	if ($default=="Nessuno"){
		$output .= "\t<option value='0' selected='selected'>Nessuno</option>\n";
	}else{
		$output .= "\t<option value='0' >Nessuno</option>\n";
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
	return (int)($wpdb->get_var( $wpdb->prepare( "SELECT Numero FROM $wpdb->table_name_Atti WHERE IdAtto=%d",$IdAtto)));
}

function ap_get_all_atti($Stato=0,$Numero=0,$Anno=0,$Categoria=0,$Oggetto='',$Dadata=0,$Adata=0,$OrderBy="",$DaRiga=0,$ARiga=20,$Conteggio=false,$Annullati=true,$Riferimento='',$Ente=-1){
/* Stato:
		 0 - tutti
		 1 - in corso di validit�
		 2 - scaduti
		 3 - da pubblicare
		 4 - da cancellare
		 5 - cerca
		 9 - tutti tranne quelli da pubblicare
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
				$Selezione.=' AND DataFine<="'.ap_convertiData($Adata);
			else
				$Selezione.=' AND DataFine<="'.ap_oggi();
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
	if (!$Annullati)
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
		return $wpdb->get_results("SELECT * FROM $wpdb->table_name_Atti $Selezione $OrderBy $Limite;");	
	}
	
}	

function ap_get_atto($id){
	global $wpdb;
	$id=(int)$id;
	return $wpdb->get_results("SELECT * FROM $wpdb->table_name_Atti Where IdAtto=$id;");
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
function ap_get_lista_atti($select_name,$id_name,$class,$tab_index_attribute,$default="Nessuno",$Stato=0,$Style="") {
	global $wpdb;
	$atti =ap_get_all_atti( $Stato,0,0,0,'',0,0,"Numero Desc");
	$output = "<select name='$select_name' id='$id_name' class='$class' $tab_index_attribute style='$Style'>\n";
	if ($default=="Nessuno"){
		$output .= "\t<option value='0' selected='selected'>Nessuno</option>\n";
	}else{
		$output .= "\t<option value='0' >Nessuno</option>\n";
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
function ap_get_dropdown_atti($select_name,$id_name,$class,$tab_index_attribute,$default="Nessuno") {
	global $wpdb;
	$atti =ap_get_all_atti( 0,0,0,0,'',0,0,"Numero Desc");
	$output = "<select name='$select_name' id='$id_name' class='$class' $tab_index_attribute>\n";
	if ($default=="Nessuno"){
		$output .= "\t<option value='0' selected='selected'>Nessuno</option>\n";
	}else{
		$output .= "\t<option value='0' >Nessuno</option>\n";
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

function ap_ripubblica_atti_correnti(){
	global $wpdb;
	$SqlAttiDaR='SELECT IdAtto, Numero, Anno, Data, DataInizio, DataFine  
				 FROM '.$wpdb->table_name_Atti.' 
				 WHERE DataInizio <= curdate() AND DataFine >= curdate() AND DataAnnullamento="0000-00-00" AND Numero>0 
				 order by Anno, Numero';
	$SqlDuplicaAtto='INSERT INTO '.$wpdb->table_name_Atti.' ( Data, Riferimento, Oggetto, DataInizio, DataFine, Informazioni, IdCategoria, RespProc, Ente)
					 SELECT Data, Riferimento, Oggetto, curdate(), adddate(curdate(),datediff(DataFine,DataInizio)), Informazioni, IdCategoria, RespProc, Ente FROM '.$wpdb->table_name_Atti.' 
					 WHERE IdAtto='; 
	$AttiDaR = $wpdb->get_results($SqlAttiDaR);
	if(get_option('opt_AP_AnnoProgressivo')!=date("Y")){
		update_option('opt_AP_AnnoProgressivo',date("Y") );
	  	update_option('opt_AP_NumeroProgressivo',1 );
		$Anno=get_option('opt_AP_AnnoProgressivo');
	}else{
		$Anno=get_option('opt_AP_AnnoProgressivo');
	}
	$StatoOperazioni='';
	foreach($AttiDaR as $AttoDaR){
		$wpdb->query($SqlDuplicaAtto.$AttoDaR->IdAtto.';');
		$StatoOperazioni.='Atto Originale Id '.$AttoDaR->IdAtto.' Numero '.$AttoDaR->Numero.'/'.$AttoDaR->Anno.' del '.$AttoDaR->Data.' Pubblicazione dal '.$AttoDaR->DataInizio.' al '.$AttoDaR->DataFine.'%%br%%';
		$IdNewAtto=$wpdb->insert_id;
		ap_insert_log(1,1,$IdNewAtto,"{IdAtto}==> $IdNewAtto
		                              {AttoOriginale}==>$AttoDaR->IdAtto
									  {Motivo}==>Ripubblicazione Atto");		
		ap_update_selettivo_atto($IdNewAtto,array('Anno' => $Anno),array('%s'),"Modifica in Ripubblicazione Atto\n");
		$RisApprovazione=ap_approva_atto($IdNewAtto);
		$Atto=ap_get_atto($IdNewAtto);
		$Atto=$Atto[0];
		$StatoOperazioni.='Atto Duplicato Id '.$IdNewAtto.' Numero '.$Atto->Numero.'/'.$Atto->Anno.' del '.$Atto->Data.' Pubblicazione dal '.$Atto->DataInizio.' al '.$Atto->DataFine.'%%br%%';
		$StatoOperazioni.=$RisApprovazione.' %%br%%';
		if ($RisApprovazione!='Atto PUBBLICATO'){
			ap_del_atto($IdNewAtto);
		}else{
			$SqlDuplicaAllegato='INSERT INTO '.$wpdb->table_name_Allegati.' ( TitoloAllegato,Allegato,IdAtto)
						 SELECT TitoloAllegato,Allegato,'.$IdNewAtto.' as IdNuovoAtto FROM '.$wpdb->table_name_Allegati.'
						 WHERE IdAllegato=';
			$AllegatiAtto=ap_get_all_allegati_atto($AttoDaR->IdAtto);
			foreach ($AllegatiAtto as $AllegatoAtto) {
				$wpdb->query($SqlDuplicaAllegato.$AllegatoAtto->IdAllegato.';');
				$IdNewAllegato=$wpdb->insert_id;
				ap_insert_log(3,1,$wpdb->insert_id,"{IdAllegato}==> $IdNewAllegato
												{VecchioAtto}==> $AllegatoAtto->IdAtto 
												{Allegato}==> $Allegato 
												{IdAtto}==> $IdNewAtto
												{Motivo}==>Ripubblicazione Atto", $IdNewAtto);
				$StatoOperazioni.='    Allegato Originale Id '.$AllegatoAtto->IdAllegato.' Duplicato Id '.$IdNewAllegato.' Allegato '.$Allegato.' %%br%%';
			}
			$StatoOperazioni.='Atto Id '.$AttoDaR->IdAtto.' Numero '.$AttoDaR->Numero.'/'.$AttoDaR->Anno.' del '.$AttoDaR->Data.' '.ap_annulla_atto($AttoDaR->IdAtto,"Annullamento per interruzione del sevizio di pubblicazione").'%%br%%';		
		}
	}
	if ($wpdb->last_error==''){
		return $StatoOperazioni."Ripubblicazione effettuata con successo";
	}else{
		return $StatoOperazioni."Ripubblicazione non effettuata a causa del seguente errore:".$wpdb->last_error;
	}
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
		if(substr($name,0,1)!="." And in_array($ext,$TipiAmmessi))
			$AllegatiF[]=basename($key);
	}
	$AllegatiDB=array();
	foreach($Records as $Record){
		$AllegatiDB[]=basename($Record->Allegato);
	}
	$NonAssegnati=array_diff($AllegatiF,$AllegatiDB);
	if($TipoRet=="Array"){
		return $NonAssegnati;
	}
	$output = "<select name='$select_name' id='$id_name' class='$class'>\n";
	if ( ! empty( $NonAssegnati ) ) {	
		foreach ($NonAssegnati as $a) {
			$output .= "\t<option value='$a'>$a</option>\n";
		}
	}
	$output .= "</select>\n";
	return $output; 
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
function ap_get_all_allegati_atto($idAtto){
	global $wpdb;
	return $wpdb->get_results("SELECT * FROM $wpdb->table_name_Allegati WHERE IdAtto=". (int)$idAtto." ORDER BY IdAllegato;");
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

function ap_memo_allegato($idAllegato,$Titolo,$idAtto){
	global $wpdb;
//		echo "Sql==".$wpdb->last_query ."    Ultimo errore==".$wpdb->last_error;
	if ($num=$wpdb->update($wpdb->table_name_Allegati,
					array('TitoloAllegato' => $Titolo),
						  array( 'IdAllegato' => $idAllegato ),
						  array('%s'),
						  array('%d'))){
		ap_insert_log(3,2,(isset($idAllegato)?$idAllegato:0),"{Titolo Allegato}==> $Titolo",(int)$idAtto);
		return true;
	}else{
		return new WP_Error('db_update_error', "Allegato non modificato ".$wpdb->last_error, $wpdb->last_error);
	}
}

function ap_insert_allegato($TitoloAllegato,$Allegato,$IdAtto){
global $wpdb;
	$IdAtto=(int)$IdAtto;
	if ( false === $wpdb->insert(
		$wpdb->table_name_Allegati,array(
				'TitoloAllegato' => $TitoloAllegato,
				'Allegato' =>  $Allegato,
				'IdAtto' => $IdAtto),array(
				'%s',
				'%s',
				'%d')))	
        return new WP_Error('db_insert_error', 'Non sono riuscito ad inserire il nuovo allegato'.$wpdb->last_error, $wpdb->last_error);
    else
    	ap_insert_log(3,1,$wpdb->insert_id,"{IdAllegato}==> $wpdb->insert_id
											{Titolo}==> $TitoloAllegato 
											{Allegato}==> $Allegato 
											{IdAtto}==> $IdAtto", $IdAtto);
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
				ap_insert_log(3,3,$allegato[0]->IdAllegato,"{Nome Allegato}==> ".$allegato[0]->TitoloAllegato." ",$idAtto);
			}else{
				ap_insert_log(3,3,$allegato[0]->IdAllegato,"{Nome Allegato}==> ".$allegato[0]->TitoloAllegato." ancellato solo il file per VIOLAZIONE di LEGGE",$idAtto);
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
			ap_insert_log(3,3,$allegato->IdAllegato,"{Nome Allegato}==> ".$allegato->Allegato,$idAtto);
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
	$DirLog=str_replace("\\","/",Albo_DIR.'/BackupDatiAlbo/log');
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
				ap_insert_log(3,10,$allegato['IdAllegato'] ,"{Errore nello spostamento Allegato}==> ".$allegato['Allegato']." => $NewAllegato",0);	
				$msg.='<spam style="color:red;">Errore</spam> nello spostamento dell\'Allegato '.$allegato['Allegato'].' in '. $NewAllegato."%%br%%";
				fwrite($fplog,"Non sono riuscito a copiare il file ".$allegato['Allegato']." in ". $NewAllegato."\n");
			}
			else{
				if (!unlink($allegato['Allegato'])){
					ap_insert_log(3,10,$allegato['IdAllegato'] ,"{Errore nella cancellazione Allegato}==> ".$allegato['Allegato'],0);
					$msg.='<spam style="color:red;">Errore</spam> errata cancellazione dell\'Allegato </spam>'.$allegato['Allegato']."%%br%%";
					fwrite($fplog,"Non sono riuscito a cancelalre il file ".$allegato['Allegato']."\n");
			}
			$msg.='<spam style="color:green;">File</spam> '.$allegato['Allegato'].' <spam style="color:green;">spostato in</spam> '.$NewAllegato.'%%br%%';
			fwrite($fplog,"File ".$allegato['Allegato']." spostato in ".$NewAllegato."\n");
			if ($wpdb->update($wpdb->table_name_Allegati,
									array('Allegato' => $NewAllegato),
									array('IdAllegato' => $allegato['IdAllegato'] ),
									array('%s'),
									array('%d'))>0){
				ap_insert_log(3,9,$allegato['IdAllegato'] ,"{Allegato}==> ".$allegato['Allegato']." spostato in $NewAllegato",0);
				$msg.='<spam style="color:green;">Aggiornamento Link Allegato</spam> '.$allegato['Allegato']."%%br%%";
				fwrite($fplog,"Aggiornato il link nel Data Base per ".$allegato['Allegato']." in ".$NewAllegato."\n");
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
				fwrite($fplog,"Errore nella Cancellazione del file ".$fName."\n");
		else
			fwrite($fplog,"File ".$fName." inesistente\n");
		$fName=str_replace("\\","/",$OldPathAllegati)."/".ap_decodenamefile();
		if (is_file($fName))
			if (unlink($fName))
				fwrite($fplog,"File ".$fName." Cancellato\n");
			else
				fwrite($fplog,"Errore nella Cancellazione del file ".$fName."\n");
		else
			fwrite($fplog,"File ".$fName." inesistente\n");
		if($tmpdir==AP_BASE_DIR){
			$msg.="Directory ".$tmpdir." non cancellata%%br%%";
			fwrite($fplog,"Directory ".$tmpdir." non cancellata \n");	
		}else{
			if (is_dir($tmpdir)){
				if (!ap_is_dir_empty($tmpdir)){
					$msg.="La directory ".$tmpdir." non vuota%%br%%";
					fwrite($fplog,"La directory ".$tmpdir." non vuota \n");					
				}else{
					if (rmdir($tmpdir)){
						$msg.="Directory ".$tmpdir." cancellata%%br%%";
						fwrite($fplog,"Directory ".$tmpdir." cancellata \n");	
					}else{
						$msg.="La directory ".$tmpdir." non e' stata cancellata%%br%%";
						fwrite($fplog,"La directory ".$tmpdir." non e' stata cancellata \n");
					}
				}
			}else{
					$msg.="La directory ".$tmpdir." non esiste%%br%%";
					fwrite($fplog,"La directory ".$tmpdir." non esiste \n");		
			}			
		}
	}
	if (!$eliminareOrigine){
		$msg.="La directory ".$tmpdir." non essendo una sottocartella della cartella Uploads di sistema, non deve essere cancellata%%br%%";
		fwrite($fplog,"La directory ".$tmpdir." non essendo una sottocartella della cartella Uploads di sistema, non deve essere cancellata \n");	
	}
	fclose($fplog);
	if (stripslashes(get_option('opt_AP_FolderUpload'))!="wp-content/uploads"){
		ap_NoIndexNoDirectLink(AP_BASE_DIR.get_option('opt_AP_FolderUpload'));
	}
	$fpmsg = @fopen(Albo_DIR."/BackupDatiAlbo/tmp/msg.txt", "wb");
	fwrite($fpmsg,$msg);
	fclose($fpmsg);
}

function ap_allinea_allegati(){
	global $wpdb;
	$msg="";
	$allegati=$wpdb->get_results("SELECT * FROM $wpdb->table_name_Allegati ;",ARRAY_A );
// Nuova directory Allegati Albo Pretorio
	$BaseCurDir=str_replace("\\","/",AP_BASE_DIR.get_option('opt_AP_FolderUpload'));
	foreach( $allegati as $allegato){
		$NewAllegato=$BaseCurDir."/".basename($allegato['Allegato']);
		if ($wpdb->update($wpdb->table_name_Allegati,
									array('Allegato' => $NewAllegato),
									array('IdAllegato' => $allegato['IdAllegato'] ),
									array('%s'),
									array('%d'))){
				ap_insert_log(3,9,$allegato['IdAllegato'] ,"{Allegato}==> ".$allegato['Allegato']." spostato in $NewAllegato",0);
				$msg.='<spam style="color:green;">Aggiornamento Link Allegato</spam> '.$allegato['Allegato']."%%br%%";
			}
//	echo "<p>Sql==".$wpdb->last_query ."    Ultimo errore==".$wpdb->last_error."</p>";
	}

	return $msg;
}

################################################################################
// Funzioni Responsabili
################################################################################
function ap_get_dropdown_responsabili($select_name,$id_name,$class,$tab_index_attribute="", $default="Nessuno",$Funzione="") {
	global $wpdb;
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
	if ($default=="Nessuno" Or $default==0){
		$output .= "\t<option value='0' selected='selected'>Nessuno</option>\n";
	}else{
		$output .= "\t<option value='0' >Nessuno </option>\n";
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

function ap_num_responsabili_atto($id){
	global $wpdb;
	return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->table_name_Atti WHERE RespProc=%d",$id));
}
function ap_num_responsabili(){
	global $wpdb;
	return $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->table_name_RespProc;");	
}
function ap_num_responsabili_inutilizzati(){
	global $wpdb;
	$Sql="SELECT count(*) 
	      FROM $wpdb->table_name_RespProc left join  $wpdb->table_name_Atti on 
		  		$wpdb->table_name_Atti.RespProc =  $wpdb->table_name_RespProc.IdResponsabile
		  WHERE $wpdb->table_name_Atti.IdAtto is null";
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
									array('Cognome' => $resp_cognome,
                                          'Nome' => $resp_nome,
										  'Funzione'=>$resp_funzione,
										  'Email' => $resp_email,
										  'Telefono' => $resp_telefono,
										  'Orario' => $resp_orario,
										  'Note' => $resp_note),
									array('%s',
										  '%s',
										  '%s',
										  '%s',
										  '%s',
										  '%s',
										  '%s')))	
        return new WP_Error('db_insert_error', 'Non sono riuscito ad inserire il Nuovo Responsabile'.$wpdb->last_error, $wpdb->last_error);
    else
    	ap_insert_log(4,1,$wpdb->insert_id,"{IdResponsabile}==> $wpdb->insert_id
		                                    {Cognome}==> $resp_cognome 
		                                    {Nome}==> $resp_nome 
											{Funzione}==> $resp_funzione
											{Email}==> $resp_email
											{Telefono}==> $resp_telefono
											{Orario}==> $resp_orario
											{Note}==> $resp_note");
}
function ap_memo_responsabile($Id,$resp_cognome,$resp_nome,$resp_funzione,$resp_email,$resp_telefono,$resp_orario,$resp_note){
	global $wpdb;
	$Id=(int)$Id;
	$Responsabile=ap_get_responsabile($Id);
	$Responsabile=$Responsabile[0];
	$Log='{Id}==>'.$Id .' ' ;
	if ($Responsabile->Cognome!=$resp_cognome)
		$Log.='{Cognome}==> '.$resp_cognome.' ';
	if ($Responsabile->Nome!=$resp_nome)
		$Log.='{Nome}==> '.$resp_nome.' ';
	if ($Responsabile->Funzione!=$resp_funzione)
		$Log.='{Funzione}==> '.$resp_funzione.' ';
	if ($Responsabile->Email!=$resp_email)
		$Log.='{Email}==> '.$resp_email.' ';
	if ($Responsabile->Telefono!=$resp_telefono)
		$Log.='{Telefono}==> '.$resp_telefono.' ';
	if ($Responsabile->Orario!=$resp_orario)
		$Log.='{Orario}==> '.$resp_orario.' ';
	if ($Responsabile->Note!=$resp_note)
		$Log.='{Note}==> '.$resp_note.' ';
	
	if ( false === $wpdb->update($wpdb->table_name_RespProc,
					array('Cognome' => $resp_cognome,
	                      'Nome' => $resp_nome,
						  'Funzione'=>$resp_funzione,
						  'Email' => $resp_email,
						  'Telefono' => $resp_telefono,
						  'Orario' => $resp_orario,
						  'Note' => $resp_note),
					array('IdResponsabile' => $Id),
					array( '%s',
						   '%s',
						   '%s',
						   '%s',
						   '%s',
						   '%s',
						   '%s'),
					array('%d')))
	    	return new WP_Error('db_update_error', 'Non sono riuscito a modifire il resposnabile del Trattamento'.$wpdb->last_error, $wpdb->last_error);
	else 
		ap_insert_log(4,2,$Id,$Log);
}

function ap_del_responsabile($id) {
	global $wpdb;
	$id=(int)$id;
	$resp=ap_get_responsabile($id);
	$responsabile= "Cancellazione Responsabile {IdResponsabile}==> $id {Cognome}==> ".$resp[0]->Cognome." {Nome}==> ".$resp[0]->Nome; 
	$N_atti=ap_num_responsabili_atto($id);
	if ($N_atti>0){
		return array("atti" => $N_atti);
	}else{
	 	$result=$wpdb->query($wpdb->prepare( "DELETE FROM $wpdb->table_name_RespProc WHERE IdResponsabile=%d",$id));
		ap_insert_log(4,3,$id,$responsabile,$id);
		return $result;
	}
}
function ap_responsabili_orfani(){
	global $wpdb;
	$Sql="SELECT $wpdb->table_name_Atti.Numero,$wpdb->table_name_Atti.Anno, $wpdb->table_name_Atti.RespProc 
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
ap_get_dropdown_enti($select_name,$id_name,$class,$tab_index_attribute="", 
$default=-1) {
     global $wpdb;
     $enti = $wpdb->get_results("SELECT DISTINCT * FROM 
$wpdb->table_name_Enti ORDER BY IdEnte;");
     $output = "<select name='$select_name' id='$id_name' class='$class' 
$tab_index_attribute>\n";
     if ( ! empty( $enti ) ) {
             /* mr modifica per cercare in tutti gli enti */
             $output .= "\t<option value=\"-1\" selected=\"selected\" 
 >".__('Tutti gli Enti', 'digitalpolis')."</option>\n";
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
						array('Nome' => $ente_nome),
						array('IdEnte' => 0),
						array( '%s')))
			ap_insert_log(7,2,0,"Aggiornamento Ente Sito");	
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
	if ( false === $wpdb->insert($wpdb->table_name_Enti,array('Nome' => $ente_nome,
                                                              'Indirizzo' => $ente_indirizzo,
                                                              'Url' => $ente_url,
															  'Email' => $ente_email,
															  'Pec' => $ente_pec,
															  'Telefono' => $ente_telefono,
															  'Fax' => $ente_fax,
															  'Note' => $ente_note),
														array('%s',
															  '%s',
															  '%s',
															  '%s',
															  '%s',
															  '%s',
															  '%s',
															  '%s'))){
//		echo "Sql==".$wpdb->last_query ."    Ultimo errore==".$wpdb->last_error;exit;
        return new WP_Error('db_insert_error', 'Non sono riuscito ad inserire il Nuovo Ente'.$wpdb->last_error, $wpdb->last_error);}
    else
    	ap_insert_log(7,1,$wpdb->insert_id,"{IdEnte}==> $wpdb->insert_id
		                                    {Nome}==> $ente_nome 
											{Indirizzo}=> $ente_indirizzo
											{Url}=> $ente_url
											{Email}==> $ente_email
											{Pec}==> $ente_pec
											{Telefono}==> $ente_telefono
											{fax}==> $ente_fax
											{Note}==> $ente_note");
}

function ap_memo_ente($Id,$ente_nome,$ente_indirizzo,$ente_url,$ente_email,$ente_pec,$ente_telefono,$ente_fax,$ente_note){
	global $wpdb;
	$Id=(int)$Id;
	$EnteL=ap_get_ente($Id);
	$Log='{Id}==>'.$Id .' ' ;
	if ($EnteL->Nome!=$ente_nome)
		$Log.='{Nome}==> '.$ente_nome.' ';
	if ($EnteL->Indirizzo!=$ente_indirizzo)
		$Log.='{Indirizzo}==> '.$ente_indirizzo.' ';
	if ($EnteL->Url!=$ente_url)
		$Log.='{Url}==> '.$ente_url.' ';
	if ($EnteL->Email!=$ente_email)
		$Log.='{Email}==> '.$ente_email.' ';
	if ($EnteL->Pec!=$ente_pec)
		$Log.='{Pec}==> '.$ente_pec.' ';
	if ($EnteL->Telefono!=$ente_telefono)
		$Log.='{Telefono}==> '.$ente_telefono.' ';
	if ($EnteL->Fax!=$ente_fax)
		$Log.='{Fax}==> '.$ente_fax.' ';
	if ($EnteL->Note!=$ente_note)
		$Log.='{Note}==> '.$ente_note.' ';
	
	if ( false === $wpdb->update($wpdb->table_name_Enti,
					array('Nome' => $ente_nome,
						  'Indirizzo' => $ente_indirizzo,
						  'Url' => $ente_url,
						  'Email' => $ente_email,
						  'Pec' => $ente_pec,
						  'Telefono' => $ente_telefono,
						  'Fax' => $ente_fax,
						  'Note' => $ente_note),
					array('IdEnte' => $Id),
					array( '%s',
						   '%s',
						   '%s',
						   '%s',
						   '%s',
						   '%s'),
					array( '%d' )))
	    	return new WP_Error('db_update_error', 'Non sono riuscito a modifire l\'Ente'.$wpdb->last_error, $wpdb->last_error);
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
		ap_insert_log(7,3,$id,"Cancellazione Ente {IdEnte}==> $id",$id);
		return $result;
	}
}
function ap_enti_orfani(){
	global $wpdb;
	$Sql="SELECT $wpdb->table_name_Atti.Numero,$wpdb->table_name_Atti.Anno, $wpdb->table_name_Atti.Ente 
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

/**
* Backup 
*/
function ap_sql_addslashes($a_string = '', $is_like = false) {
	if ($is_like) $a_string = str_replace('\\', '\\\\\\\\', $a_string);
	else $a_string = str_replace('\\', '\\\\', $a_string);
	return str_replace('\'', '\\\'', $a_string);
} 

function ap_backup_table($table,$fp) {
	global $wpdb;
	if($table==$wpdb->table_name_Enti){
		@fwrite($fp,"SET SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO';"."\r\n");
	}
	$table_structure = $wpdb->get_results("DESCRIBE $table");
	if (! $table_structure) {
		echo 'Errore nell\'estrazione della struttura della tabella : '.$table;
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
	@fwrite($fp,"Delete From $table ;"."\r\n");
	$table_data = $wpdb->get_results("SELECT * FROM $table ", ARRAY_A);
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
		fwrite($fplog,"Svuotamento Directory ".$Dir."\n");
	foreach ($iterator as $key=>$value) {
		if (is_file(realpath($key)))
			if (unlink(realpath($key))){
				if(!is_null($fplog))
					fwrite($fplog,"       File ".$key." cancellato\n");			
			}else{
				if(!is_null($fplog))	
					fwrite($fplog,"       File ".$key." non pu&ograve; essere cancellato\n");			
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
					$wpdb->table_name_Attimeta);
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
	$Risultato="Risultato del Backup:<br />";
	if ($Echo){
		echo "<h2>Risultato del Backup:</h2>";
		echo "<h3>Verifica struttura Directory destinazione</h3>"
		. "<ul>";
	}
	if (class_exists('PclZip')) {
//		echo $Dir." <br />".$DirTmp." <br />".$DirAllegati." <br />".$DirLog."";wp_die();
		if (!is_dir ( $Destinazione)){
			if (!mkdir($Destinazione, 0744)){
				if ($Echo){
					echo "<li>Non sono riuscito a creare la directory ".$Destinazione." Fine Operazione</li>";
				}else{
					 echo"<li>Directory ".$Destinazione." Verificata</li>";
				}
				$ControlloDir.="Non sono riuscito a creare la directory ".$Destinazione."\n Fine Operazione";
			}
		}
		if (is_dir($Destinazione)){
			if (!is_dir ( $Dir)){
				if (!mkdir($Dir, 0744)) {
					if ($Echo){
						echo "<li>Non sono riuscito a creare la directory ".$Dir." Fine Operazione</li>";
					}else{
						echo"<li>Directory ".$Dir." Verificata</li>";
					}
					$ControlloDir.="Non sono riuscito a creare la directory ".$Dir."\n Fine Operazione";
				}
			}
		}
		if (!is_dir ( $DirTmp)){	
			if (!mkdir($DirTmp, 0744)){
				if ($Echo){
					echo "<li>Non sono riuscito a creare la directory ".$DirTmp." Fine Operazione</li>";
				}else{
					echo"<li>Directory ".$DirTmp." Verificata</li>";
				}
				$ControlloDir.="Non sono riuscito a creare la directory ".$DirTmp."\n Fine Operazione";
			}
		}
		if (!is_dir ( $DirLog)){							
			if (!mkdir($DirLog, 0744)){
				if ($Echo){
					 echo "<li>Non sono riuscito a creare la directory ".$DirTmp." Fine Operazione</li>";
				}else{
					echo"<li>Directory ".$DirTmp." Verificata</li>";
				}
				$ControlloDir.="Non sono riuscito a creare la directory ".$DirLog."\n Fine Operazione";
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
		fwrite($fplog,"Avvio Backup Dati ed Allegati Albo Pretrorio \n effettuato in data ".date("Ymd_Hi")."\n");
		ap_SvuotaDirectory($DirTmp,$fplog);
		fwrite($fplog,"Svuotamento tabella ".$DirTmp."\n");
		$fp = @fopen($DirTmp."/AlboPretorio".date("Ymd_Hi").".sql", "wb");
		$Risultato="";
		if ($Echo) echo "<h3>Avvio Backup Dati (Tabelle del Data Base)</h3>"
			. "</ul>";
		foreach ($tables as $table) {
			ap_backup_table($table,$fp);
			$Risultato.='<span style="color:green;">Tabella '.ap_backquote($table).' Aggiunta</span> <br />';
			if ($Echo)	echo '<li><span style="color:green;">Tabella '.ap_backquote($table).' Aggiunta</span></li>';
			fwrite($fplog,"Sql Tabella ".ap_backquote($table)." Aggiunta\n");
		}
		$UpdateProgressivo="UPDATE `".$wpdb->options."` SET `option_value` = '".get_option('opt_AP_AnnoProgressivo')."'	WHERE `option_name` ='opt_AP_AnnoProgressivo';\n";
		$UpdateProgressivo.="UPDATE `".$wpdb->options."` SET `option_value` = '".get_option('opt_AP_NumeroProgressivo')."' WHERE `option_name` ='opt_AP_NumeroProgressivo';";
		fwrite($fplog,"Sql Aggiornamento Tabella ".$wpdb->options." per Progressivo ed Anno Progressivo Aggiunti\n");
		fwrite($fp,$UpdateProgressivo);
		fclose($fp);
		if ($Echo)	echo '<li><span style="color:green;">Sql Aggiornamento Tabella '.$wpdb->options.' per Progressivo ed Anno Progressivo Aggiunti</span></li>'
				. '</ul>';
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
					$Risultato.='<span style="color:green;">Aggiunto all\'archivio:</span> '.$key.'<br />';
					fwrite($fplog,"File ".$key." Aggiunto\n");
				}
			}
			$allegati=ap_get_all_allegati();
			if ($Echo) echo "<h3>Avvio Backup Allegati</h3>"
				. "</ul>";
			foreach ($allegati as $allegato) {
			//echo $allegato->Allegato;
				if (substr(basename( $allegato->Allegato ),-4)==".pdf" or 
					substr(basename( $allegato->Allegato ),-4)==".p7m") 
					$zip->add(realpath($allegato->Allegato),PCLZIP_OPT_REMOVE_PATH,dirname($allegato->Allegato));
				$Risultato.='<span style="color:green;">Aggiunto all\'allegato:</span> '.$allegato->Allegato.'<br />';
				if ($Echo)	echo '<li><span style="color:green;">Aggiunto all\'allegato:</span> '.$allegato->Allegato.'</span></li>';
				fwrite($fplog,"File ".$allegato->Allegato." Aggiunto\n");					
			}
			// Chiusura e momorizzazione del del file
			$Risultato.= "Archivio creato con successo: ".$Dir."/".$NomeFile.".zip";
			fwrite($fplog,"Archivio creato con successo: ".$Dir."/".$NomeFile.".zip\n");
			if ($Echo) echo "</ul>"
				. "<h3>Backup Completato</h3>";
		}
	}else{
		$DirLog=str_replace("\\","/",$Destinazione);
		$nomefileLog=$DirLog."/msg.txt";
		$fplog = @fopen($nomefileLog, "wb");
		$Risultato.="Non risulta Installata la libreria per Zippare i files indispensabile per la procedura<br />";
		fwrite($fplog,"Non risulta Installata la libreria per Zippare i files indispensabile per la procedura\n");
		if ($Echo) echo "<h3>Non risulta Installata la libreria per Zippare i files indispensabile per la procedura</h3>";
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
		$Msg=" Non riesco a creare le cartelle necessarie all'operazione";
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
		$MsgAlle=" Allegati";
		ap_BackupFilesAllegatiOblio($riga->IdAtto);
		if (ap_cvdate($riga->DataOblio) <= ap_cvdate(date("Y-m-d"))){
			if(ap_del_allegati_atto((int)$Atto)){
				$MessaggiRitorno["Message2"]=10;// Allegati all'Atto Cancellati
				$MsgAlle.=" all&apos;Atto Cancellati";
			}else{
				$MessaggiRitorno["Message2"]=11;//Allegati all'Atto NON Cancellati
				$MsgAlle.=" all&apos;Atto NON Cancellati";
			}			
			$res=ap_del_atto((int)$Atto);
			if (!is_array($res)){
				$MessaggiRitorno["Message"]= 2;//Atto Cancellato
				$Msg.=" Cancellato ";
			}else{
				if ($res['allegati']>0) {
					$MessaggiRitorno["Message"]= 7;
					$Msg.=" Impossibile cancellare un Atto che contiene Allegati %%br%%Cancellare prima gli Allegati e poi riprovare";
				}else{
					$MessaggiRitorno["Message"]= 6;//Atto non Cancellato
					$Msg.=" NON Cancellato";
				}		
			}
		}else{
			$MessaggiRitorno["Message2"]=99;//OPERAZIONE NON AMMESSA!<br />l'atto non � ancora da eliminare
			$Msg.=" OPERAZIONE NON AMMESSA! %%br%%l&apos;atto non � ancora da eliminare";
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
		foreach ( $TipidiFiles as $key => $value ) {
			if($key!=$ID){
				$NewTipidiFiles[$key]=$value;
				$Trovato=TRUE;
			}
		}
		if($Trovato){
			ap_insert_log(8,2,$ID);
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
			ap_insert_log(8,1,$ID,"{Descrizione}==> $Descrizione 
								  {Icona}==> $Icona
								  {Verifica}==> $Verifica");
		}
		return !$Trovato;
	}
	function ap_memo_tipofiles($ID,$Descrizione,$Icona,$Verifica){
		$ID=trim($ID);
		$TipidiFiles=ap_get_tipidifiles();
		$TipidiFiles[strtolower($ID)]=array("Descrizione"=>$Descrizione,"Icona"=>$Icona,"Verifica"=>htmlspecialchars($Verifica));
		ap_insert_log(8,2,$ID,"{Descrizione}==> $Descrizione 
					  {Icona}==> $Icona
					  {Verifica}==> $Verifica");
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
	$ValoriPost= explode("&", filter_input(INPUT_POST, 'valori'));
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
function ap_LoadDefaultFunzioni(){
	check_ajax_referer('adminsecretAlboOnLine','security');
	$Default='[{"ID":"RP","Funzione":"Responsabile Procedimento","Display":"Si"},{"ID":"OP","Funzione":"Gestore procedura","Display":"Si"},{"ID":"SC","Funzione":"Segretario Comunale","Display":"No"},{"ID":"RB","Funzione":"Responsabile Pubblicazione","Display":"No"},{"ID":"DR","Funzione":"Direttore dei Servizi e Amministrativi","Display":"No"}]';
	update_option('opt_AP_TabResp',$Default ); 
	echo "Caricamento valori di default avvenuto con successo";
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
?>
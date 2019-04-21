<?php
/**
 * WGestione Enti.
 * @link       http://www.eduva.org
 * @since      4.2
 *
 * @package    ALbo On Line
 */

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }


function load_Data_Funzioni(){
	$TabResponsabili=get_option('opt_AP_TabResp');
	if($TabResponsabili){
		$TR=json_decode($TabResponsabili);
	}else{
		$TR=json_decode('[{"ID":"","Funzione":"","Display":"No"}]');
	}
?>	  
<script id="jsSourceRuoli" type="text/javascript">	  
jQuery(document).ready(function($){
	$('#GridFunzioni').appendGrid('load', [
<?php
	  foreach($TR as $Ruolo){
	  	echo "{ 'ID': '".$Ruolo->ID."', 'funzione': '".$Ruolo->Funzione."', 'visualizza': ".($Ruolo->Display=="Si" ? "true" : "false").", 'staincert': ".($Ruolo->StaCert=="Si" ? "true" : "false")." },";
		}
?>
        ]);	  
	});
</script>
<?php	  
		
}
$messages[1] = __('Item added.');
$messages[2] = __('Item deleted.');
$messages[3] = __('Item updated.');
$messages[4] = "Elemento non Memorizzato";
$messages[5] = __('Item not updated.');
$messages[6] = __('Item not deleted.');
$messages[7] = __('Impossibile cancellare Enti che sono collegati ad Atti');
$messages[80] = 'ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l\'operazione &egrave; stata annullata';
load_Data_Funzioni();
?>
<div id="ElaborazioneTabella" style="width: 200px;height: 200px;position: absolute;top: 50%;left: 50%; margin-top: -100px; margin-left: -100px;display:none;" >
	<img src="<?php echo plugin_dir_url( __FILE__ ) . 'css/images/ElaborazioneInCorso.gif'?>" id="ElaborazioneTabella"/>
</div>
<div class="wrap nosubsub">
	<div class="HeadPage">
		<h2 class="wp-heading-inline"><span class="dashicons dashicons-media-spreadsheet" style="font-size: 1.1em;"></span> Tabelle
	</div>

	<div id="config-tabs-container" style="margin-top:20px;">
		<ul>
			<li><a href="#Conf-tab-1">Funzioni</a></li>
		</ul>	 
		<div id="Conf-tab-1">

		  <form action="" method="post" id="FormFunzioni">
		  	<table id="GridFunzioni"></table>
		  	<button type="button" id="MemoFunzioni" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only"><span class="dashicons dashicons-edit"></span> Memorizza Tabella Funzioni</button>
		  	<button type="button" id="LoadDefaultFunzioni" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only"><span class="dashicons dashicons-update"></span> Carica i valori di default</button>
		  </form>
		</div>
	</div>
</div><!-- /wrap -->
<?php
/**
 * Gestione Permessi.
 * @link       http://www.eduva.org
 * @since      4.4.5
 *
 * @package    Albo On Line
 */

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }
if (isset($_REQUEST['action']) And $_REQUEST['action']=="memoPermessi"){
	if (isset($_REQUEST['permessi'])){
		if (wp_verify_nonce($_REQUEST['permessi'],'gestpermessi')){
			$lista=ap_get_users(); 
		// Azzera capacit࠵tenti di gestione ed amministrazione Albo Pretorio
			foreach($lista as $riga){
				if (!(user_can( $riga->ID, 'create_users') or user_can( $riga->ID, 'manage_network'))) {
					$users = new WP_User( $riga->ID);
					$users->remove_cap("gest_atti_albo");
					$users->remove_cap("editore_atti_albo");
					$users->remove_cap("admin_albo");
				}
			}	
		// Crea capacit࠵tenti di gestione ed amministrazione Al Pretorio in base a quanto scelto dall'Utente
			foreach($_REQUEST as $key=>$val){
				$UID=substr($key,1);
				if (is_numeric($UID)){
					$users = new WP_User($UID);
					if ($val=="Amministratore"){
						$users->add_cap("admin_albo");
						$users->add_cap("editore_atti_albo");
						$users->add_cap("gest_atti_albo");
					}
					if ($val=="Editore"){
						$users->add_cap("editore_atti_albo");
						$users->add_cap("gest_atti_albo");
					}
					if ($val=="Gestore")
						$users->add_cap("gest_atti_albo");
				}
			}
		}else{
			$Msg=__("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione è stata annullata","albo-online");
		}
	}else{
		$Msg=__("ATTENZIONE. Rilevato potenziale pericolo di attacco informatico, l'operazione è stata annullata","albo-online");
	}
}

echo '<div class="wrap">
	<div class="HeadPage">
		<h2 class="wp-heading-inline"><span class="dashicons dashicons-groups" style="font-size:1em;"></span> Permessi Utente
	</div>';
if (isset($Msg)) {
	echo '<div id="message" class="updated"><p>'.$Msg.'</p></div>';
}
echo '
		<div class="postbox-container" style="margin-top:20px;">
			<div class="widefat">
			<form id="gestPermessi" method="post" action="?page=permessiAlboP"  >
			<input type="hidden" name="action" value="memoPermessi"/>
			<input type="hidden" name="permessi" value="'.wp_create_nonce("gestpermessi").'" />
				<table class="widefat" style="width:100%;">
					<thead>
					<tr>
						<th>'.__("Utente","albo-online").'</th>
						<th>'.__("Azzera Capacità Utente","albo-online").'</th>
						<th>'.__("Capacità di Amministrare l'Albo","albo-online").'</th>
						<th>'.__("Capacità di Editore dell'Albo","albo-online").'</th>
						<th>'.__("Capacità di Gestire l'Albo","albo-online").'</th>
						<th>'.__("Ruolo Amministratore","albo-online").'</th>
						<th>'.__("Ruolo Editore","albo-online").'</th>
						<th>'.__("Ruolo Gestore","albo-online").'</th>
					</tr>
					</thead>
					<tbody>';
$lista=ap_get_users(); 
foreach($lista as $riga){
 	$users = new WP_User( $riga->ID);
 	$Utente=false;
	if ($users->has_cap('gestore_albo') or $users->has_cap('editore_albo') or $users->has_cap('amministratore_albo'))
		$Utente=true;
 	if (!(user_can( $riga->ID, 'create_users') or user_can( $riga->ID, 'manage_network'))) {
		$Stato='';
		$StatoEditore='';
		$StatoGestore='';
		echo '<tr>
		<td>'.$riga->user_login.'</td>';
	 	if (user_can( $riga->ID, 'gest_atti_albo')){
			$Stato='';
			$StatoEditore='';
	 		$StatoGestore='checked="checked"';	
		}
	 	if (user_can( $riga->ID, 'editore_atti_albo')){
			$Stato='';
			$StatoEditore='checked="checked"';
	 		$StatoGestore='';	
		}
	 	if (user_can( $riga->ID, 'admin_albo')){
			$Stato='checked="checked"';
			$StatoEditore='';
	 		$StatoGestore='';	
		}

		if (!$Utente)
			echo '				  <td><input type="radio" value="Nullo" '.$Stato.' name="U'.$riga->ID.'" /></td>
				  <td><input type="radio" value="Amministratore" '.$Stato.' name="U'.$riga->ID.'" /></td>
				  <td><input type="radio" value="Editore" '.$StatoEditore.' name="U'.$riga->ID.'" /></td>
				  <td><input type="radio" value="Gestore" '.$StatoGestore.' name="U'.$riga->ID.'" /></td>';
		else
			echo '				  <td>&nbsp;</td>
			      <td>&nbsp;</td>
				  <td>&nbsp;</td>';
		if ($users->has_cap('amministratore_albo'))
			echo '<td>si</td>';
		else
			echo '<td>--</td>';
		if ($users->has_cap('editore_albo'))
			echo '<td>si</td>';
		else
			echo '<td>--</td>';
		if ($users->has_cap('gestore_albo'))
			echo '<td>si</td>';
		else
			echo '<td>--</td>';
		echo '	</tr>';
	}
}
echo '					</tbody>
				</table>
				
				<div style="margin-left:auto;width:140px;margin-right:auto;">
					<p>
					<input type="submit" name="memo" id="memo" class="button" value="'.__("Memorizza Permessi","albo-online").'" />
					</p>
				</div>
				</form>
			</div>
		</div>
	</div>
';
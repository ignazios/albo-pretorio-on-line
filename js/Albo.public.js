function format ( d ) {
	return '<table cellpadding="1" cellspacing="0" border="0" style="padding-left:5px;font-size:0.9em;font-weight: bold;">'+
		'<tr>'+
			'<td style="width:5%;text-align: right;">Ente:</td>'+
			'<td>'+d[2]+'</td>'+
		'</tr>'+
		'<tr>'+
			'<td style="text-align: right;">Riferimento:</td>'+
			'<td>'+d[3]+'</td>'+
		'</tr>'+
		'<tr>'+
			'<td style="text-align: right;">Categoria:</td>'+
			'<td>'+d[6]+'</td>'+
		'</tr>'+
	'</table>';
}
jQuery(document).ready(function($){
		$('#paginazione').change(function(){
				location.href=$(this).attr('rel')+$('#paginazione option:selected').text();
		});
		$('#Calendario1').datepicker({dateFormat : 'dd/mm/yy'});
		$('#Calendario2').datepicker({dateFormat : 'dd/mm/yy'});
		$('a.addstatdw').click(function() {
				jQuery.ajax({type: 'get',url: $(this).attr('rel')}); //close jQuery.ajax
			return true;		 
			});
	$('#pp-tabs-container').tabs();
	$('#fe-tabs-container').tabs();
	$('#maxminfiltro').on('click',function(){
		if($('#maxminfiltro').attr('class')==='s'){
			$('#fe-tabs-container').hide();
			$('#maxminfiltro').attr('class','h');
			$('#maxminfiltro').html('<span class=\"dashicons dashicons-filter\"></span> Apri Ricerca atti mediante filtri');
			
		}else{
			$('#fe-tabs-container').show();
			$('#maxminfiltro').attr('class','s');
			$('#maxminfiltro').html('<span class=\"dashicons dashicons-filter\"></span> Chiudi Ricerca atti mediante filtri');
		}
	});
	$('a.numero-pagina').click(function(){
		location.href=$(this).attr('href')+'&vf='+$('#maxminfiltro').attr('class')+'#dati';
		return false;
	});
}); 
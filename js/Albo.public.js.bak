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
function valida_data( data ){
	    var errors = '';
	    if (!/^\d{2}\/\d{2}\/\d{4}$/.test(data)) {
	        errors += 'Data non valida';
	    } else {

	        var parts = data.split('/');
	        var day = parts[0];
	        var month = parts[1];
	        var year = parts[2];
	        var $day = (day.charAt(0) == '0') ? day.charAt(1) : day;
	        var $month = (month.charAt(0) == '0') ? month.charAt(1) : month;

	        if ($day > 31 || $day < 1) {
	            errors += 'Giorno non valido';
	        }

	        if ($month > 12 || $month < 1) {
	            errors += 'Mese non valido';
	        }

	        var now = new Date();
	        var currentYear = now.getFullYear();

	        if (year > currentYear || year < (currentYear - 100)) {
	            errors += 'Anno non valido';
	        }
	    }	
	    return errors;
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
	$('#filtro-atti').submit(function(e) {
		var Pass=true;
		 if($('#Calendario1').val()!=""){
		 	var datetime = $('#Calendario1').val();
		 	var errore=valida_data(datetime);
		 	if(errore!=""){
		 		alert("Da data: "+errore);
		 		Pass=false;
		 		e.preventDefault();	
		 	}
		}
		 if($('#Calendario2').val()!=""){
		 	var datetime = $('#Calendario2').val();
		 	var errore=valida_data(datetime);
		 	if(errore!=""){
		 		alert("A data: "+errore);
		 		Pass=false;
		 		e.preventDefault();	
		 	}
		}
		var rif= $('#riferimento').val();
		 if(rif!=rif.replace(/(<([^>]+)>)/ig,"")){
		 	alert("Errore: campo Riferimento contiene testo non idoneo");
		 	e.preventDefault();	
		 	Pass=false;
		 }
		var rif= $('#oggetto').val();
		 if(rif!=rif.replace(/(<([^>]+)>)/ig,"")){
		 	alert("Errore: campo Oggetto contiene testo non idoneo");
		 	e.preventDefault();	
		 	Pass=false;
		 }
		 var Numero=$("#numero").val();
		 if (isNaN(Numero)){
 		 	alert("Errore: campo Atto N° non è di tipo numerico");
		 	e.preventDefault();	
		 	Pass=false;
		}
	 	return Pass;
	});
}); 
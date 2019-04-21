jQuery(document).ready(function($){
	$('#pp-tabs-container').tabs();});
jQuery(document).ready(function($){
		$('#fe-tabs-container').tabs();});

jQuery(document).ready(function($){
	$('a.numero-pagina').click(function(){
		location.href=$(this).attr('href')+'&vf='+$('#maxminfiltro').attr('class')+'#dati';
		return false;
	});
});
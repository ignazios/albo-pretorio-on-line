jQuery(document).ready(function($){
    $('#color').wpColorPicker();
	$('#colorp').wpColorPicker();
    $('#colord').wpColorPicker();
	$('#Calendario1').datepicker({
        dateFormat : 'dd/mm/yy'
    });
	$('#Calendario2').datepicker({dateFormat : 'dd/mm/yy'});
	$('#Calendario3').datepicker({dateFormat : 'dd/mm/yy'});
	$('#Calendario4').datepicker({dateFormat : 'dd/mm/yy'});
        $('#CalendarioMO').datepicker({dateFormat : 'dd/mm/yy',setDate : $(this).attr('value'), maxDate: "0D"});
    $('#setta-def-data-o').click(function() {
 	 $('#Calendario4').datepicker('setDate', $(this).attr('name') );
 	});
});
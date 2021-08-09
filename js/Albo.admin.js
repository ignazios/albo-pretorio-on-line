jQuery.noConflict();
(function($) {
       $(document).delegate('.EliminaRiga', 'click', function(e){
             e.preventDefault();
            $(this).parent().remove();
        });
       $(document).delegate('.albo-notice-dismis .notice-dismiss', 'click', function(e){
               jQuery.ajax({url: ajaxurl,data: {action: 'dismiss_alboonline_notice',security:myajaxsec}});
            });
		$(function() {  
			$('span.CancellaAllegato').click(function(){
				var motivo=$('#motivo'+$(this).attr('id')).val();
				var azione=$(this).attr('rel')+'\n con la motivazione: \n'+motivo;
				var answer = confirm('Confermi la cancellazione dell\'allegato: \n' +azione);
				var ID=$(this).attr('id');
			if (answer){
				$.ajax({type: 'POST',
						url: ajaxurl,
					  	data: {
					  	action: 	'rimuoviAllegato',
					  	idAllegato:	$(this).attr('id'),
					  	idAtto: 	$('#IdAtto').val(),
					  	desmotivo:	motivo,
					  	security:	myajaxsec
					  	},
                        success: function(risposta){
                            $('#IDA'+ID).remove();
                            $('#LblIDA'+ID).remove();
                            $('#file'+ID).remove();
                            $('#motivo'+ID).remove();
                            $('#SR'+ID).remove();
                            $('#'+ID).remove();
                            $('#Note'+ID).html("<strong> "+motivo+"</strong>");
                            alert("Allegato "+azione+" "+risposta);
                         },                   
                        error: function(error) { 
                             alert(error);
                        }
				});
			}
			else{
				return false;
			}					
		});
	        $('#AddMeta').click(function(){
                 $('#newMeta').css('display', 'inline');
             });
            $('#UndoNewMedia').click(function(){
                 $('#newMeta').css('display', 'none');
             });
            $('#listaAttiMeta').change(function(){
                $('#newMetaName').val($('#listaAttiMeta').val());
            }); 
            $('#AddNewMeta').click(function(e){
                var numItems = $('.meta').length;
                e.preventDefault();
                var CampoTextNome=$('#newMetaName').val();
                if(CampoTextNome===''){
                    CampoTextNome=$( '#listaAttiMeta option:selected' ).text();
                }
                $('#MetaDati').append('<div id="Meta['+numItems+']" class="meta">\n'+
                    '<blockquote>\n'+
                    '<label for="newMetaName['+numItems+']">Nome Meta: </label><input name="newMetaName['+numItems+']" id="newMetaName['+numItems+']" value="'+CampoTextNome+'"/>\n'+
                    '<label for="newValue['+numItems+']">Valore Meta</label><input name="newValue['+numItems+']" id="newValue['+numItems+']" value="'+$('#newValue').val()+'"> \n'+
                    '<button type="button" class="EliminaRiga setta-def-data">Elimina riga</button>\n'+
                    '</blockquote>\n'+
                '</div>');
                $('#newMeta').css('display', 'none');
                $('#newValue').val('');
                $('#newMetaName').val('');
              });
          $( '#errori' ).dialog({
	      autoOpen: false,
	      show: {
	        effect: 'blind',
	        duration: 1000
	      },
	      hide: {
	        effect: 'explode',
	        duration: 1000
	      }
	    });
		$('#ConfermaCancellazione').dialog({
	      autoOpen: false,
	      show: {
	        effect: 'blind',
	        duration: 1000
	      },
	      hide: {
	        effect: 'explode',
	        duration: 1000
	      },
	      modal: true,
	      buttons: {
	        'Conferma': function() {
	          $( this ).dialog( 'close' );
	          location.href=$('#UrlDest').val();
	          return true;
	        },
	        'Annula': function() {
	          $( this ).dialog( 'close' );
	          return true;
	        }
	    	}		      
	    });
		$('#MemorizzaDati').click(function(){	
				var myList = document.getElementsByClassName('richiesto');
				var ListaErrori='';
				for(var i=0;i<myList.length;i++){
					var Classi = myList[i].classList;
					var Condizione=true;
					if (myList[i].value){
						for(var ic=0;ic<Classi.length;ic++){
							if(Classi[ic].slice(0, 8)==='ValValue'){
								console.log(Classi[ic].slice(0, 8));
								Condizione=eval(myList[i].value+Classi[ic].slice(9, Classi[ic].length-1));
							}				
						}
					}
					if (!myList[i].value){
						ListaErrori+=myList[i].id+' '+NonVal+' ('+myList[i].value+')<br />';
					}
					if ( !Condizione)
						{
						ListaErrori+=myList[i].id+' '+NonValAmm+' ('+myList[i].value+')<br />';
					}
				}
/*				if($('input[name="Soggetto[]"]:checked').length==0){
					ListaErrori+='<br />';
				}
*/				if(ListaErrori){
					document.getElementById('ElencoCampiConErrori').innerHTML=ListaErrori;		
					$( '#errori' ).dialog( 'open' );
					return false;
				}else{
					return true;	
				}	
		});
		$('#SaveData').click(function(){	
			var myList = document.getElementsByClassName('richiesto');
			var ListaErrori='';
			for(var i=0;i<myList.length;i++){
				var Classi = myList[i].classList;
				var Condizione=true;
				if (myList[i].value){
					for(var ic=0;ic<Classi.length;ic++){
						if(Classi[ic].slice(0, 8)==='ValValue'){
							console.log(Classi[ic].slice(0, 8));
							Condizione=eval(myList[i].value+Classi[ic].slice(9, Classi[ic].length-1));
						}				
					}
				}
				if (!myList[i].value){
					ListaErrori+=myList[i].id+' '+NonVal+' ('+myList[i].value+')<br />';
				}
				if ( !Condizione)
					{
					ListaErrori+=myList[i].id+' '+NonValAmm+' ('+myList[i].value+')<br />';
				}
			}
			if(ListaErrori){
				document.getElementById('ElencoCampiConErrori').innerHTML=ListaErrori;		
				$( '#errori' ).dialog( 'open' );
				return false;
			}else{
				return true;	
			}	
		});
		$('a.ac').click(function(){
			document.getElementById('oggetto').innerHTML=$(this).attr('rel');
			$('#UrlDest').val($(this).attr('href'));
			$('#ConfermaCancellazione').dialog( 'open' );
			return false;
		});		
		$('a.ripubblica').click(function(){
			var answer = confirm('Confermi la ripubblicazione dei ' + $(this).attr('rel') + ' atti in corso di validita?');
			if (answer){
				return true;
			}
			else{
				return false;
			}					
		});
		
		$('a.eliminaatto').click(function(){
			var answer = confirm('Confermi l\'eliminazione dell\'atto ' + $(this).attr('rel') + ' ?\nATTENZIONE L\'OPERAZIONE E\' IRREVERSIBILE!!!!!');
			if (answer){
				answer = confirm('Prima di procedere ti ricordo che l\'ELIMINAZIONE degli atti dall\'Albo sono regolati dalla normativa\nTranne che in casi particolari gli atti devono rimanere nell\'Albo Storico almeno CINQUE ANNI');
				if (answer){
					location.href=$(this).attr('href')+'&sgs=ok';
					return false;
				}else{
					return false;
				}
			}else{
				return false;
			}					
		});
		$('a.confdel').click(function(){
			var answer = confirm($(this).attr('rel'));
			if (answer){
				return true;
			}
			else{
				return false;
			}					
		});
		$('a.ap').click(function(){
			var answer = confirm('approvazione Atto: `' + $(this).attr('rel') + '`\nAttenzione la Data Pubblicazione verra` impostata ad oggi ?');
			if (answer){
				return true;
			}
			else{
				return false;
			}					
		});
		$('input.update').click(function(){
			var answer = confirm('confermi la modifica della Categoria ' + $(this).attr('rel') + '?');
			if (answer){
				return true;
			}
			else{
				return false;
			}					
		});
		$('a.addstatdw').click(function() {
		 var link=$(this).attr('rel');
		 $.get(link,function(data){
		$('#DatiLog').html(data);
			}, 'json');
		});
    var Pagina=$('#Pagina').val();
    $('#utility-tabs-container').tabs({ active: Pagina });
    $('#edit-atti-tabs').tabs();
    $('#repertori-tabs-container').tabs();
    $('#utility-tabs-container').tabs();	
    $('#config-tabs-container').tabs();	
 });
})(jQuery);
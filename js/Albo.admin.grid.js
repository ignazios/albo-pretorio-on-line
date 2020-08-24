jQuery(document).ready(function($){
    function isLunghezza(Campo,Min,Max){
      Campo=Campo.toString();
      if (Max===0){
           if (Campo.length<Min ){
               return false;
           }else{
               return true;
           }
      }else{
            if(Campo.length<Min || Campo.lenght>Max){
                return false;
            }else{
                return true;
            }    
      }
     }		
    $('#GridFunzioni').appendGrid({
 	caption: TabFunSog,
    initRows: 1,
    columns: [
			{ name: 'ID', display: Codice, type: 'text', ctrlAttr: { maxlength: 3 }, ctrlCss: { width: '50px'},
          onChange: function (evt, rowIndex) {
                   if( !isLunghezza($("#GridFunzioni_ID_"+(rowIndex+1)).val(),2,3)){
                       $("#GridFunzioni_ID_"+(rowIndex+1)).focus();
                        alert(ValidateCodice);
                    }
                }},
			{ name: 'funzione', display: Funzione, type: 'text', ctrlAttr: { maxlength: 100 }, ctrlCss: { width: '250px'},
          onChange: function (evt, rowIndex) {
                   if( !isLunghezza($("#GridFunzioni_Funzione_"+(rowIndex+1)).val(),5,0)){
                       $("#GridFunzioni_Funzione_"+(rowIndex+1)).focus();
                        alert(ValidateFunzione);
                    }
                }},
 			{ name: 'visualizza', display:Visualizza,
 			        displayCss: { 'cursor': 'pointer','text-decoration':'underline' },
 				displayTooltip: {items: 'td',
                    		   content: ValidateVisualizza,
                    			  show: {effect: 'slideDown', delay: 250}
								} , type: 'checkbox'},
 			{ name: 'staincert', display: Stampa,
 			        displayCss: { 'cursor': 'pointer','text-decoration':'underline' },
 				displayTooltip: {items: 'td',
                    		   content: ValidateStampa,
                    			  show: {effect: 'slideDown', delay: 250}
								} ,  type: 'checkbox'}
        ],
        customGridButtons: {
            append: { label: '<span class="dashicons dashicons-plus"></span>' },
            removeLast: { label: '<span class="dashicons dashicons-no"></span>' },
            insert: { label: '<span class="dashicons dashicons-welcome-add-page"></span>' },
            remove: { label: '<span class="dashicons dashicons-editor-removeformatting"></span>' },
            moveUp: { label: '<span class="dashicons dashicons-arrow-up-alt2"></span>' },
            moveDown: { label: '<span class="dashicons dashicons-arrow-down-alt2"></span>' }
		}
	});
	$("#MemoFunzioni").button().click(function () {
       $.ajax({type: 'POST', url: ajaxurl, 
	        data:{
	            action:'MemoFunzioni',
	            security: myajaxsec,
	            valori:$(document.forms['0']).serialize()
	        },
			beforeSend: function() {
            	$("#ElaborazioneTabella").show("slow");
            },
	        success: function(risposta){
	        	$("#ElaborazioneTabella").hide("slow");
	        	alert(risposta);
	        },                   
	        error: function (xhr, ajaxOptions, thrownError) {
	        	$("#ElaborazioneTabella").hide("slow");
        		alert(xhr.status+ " "+thrownError);
	        }
        }); 				
	});
	$("#LoadDefaultFunzioni").button().click(function () {
        $.ajax({type: 'POST', url: ajaxurl, 
	        data:{
	            action:'LoadDefaultFunzioni',
	            security: myajaxsec
	        },
			beforeSend: function() {
            	$("#ElaborazioneTabella").show("slow");
            },
	        success: function(risposta){
	        	$("#ElaborazioneTabella").hide("slow");
	        	var NRows=$('#GridFunzioni').appendGrid('getRowCount');
	        	for(var i=1;i<=NRows;i++){
	        		$('#GridFunzioni').appendGrid('removeRow',0);					
				}
				$('#GridFunzioni').appendGrid('appendRow', [
				{ID:'RP',funzione:'Responsabile Procedimento',visualizza:1},
		   		{ID:'OP',funzione:'Gestore procedura',visualizza:1},
		   		{ID:'SC',funzione:'Segretario Comunale',visualizza:0},
		   		{ID:'RB',funzione:'Responsabile Pubblicazione',visualizza:0},
		   		{ID:'DR',funzione:'Direttore dei Servizi Generali e Ammistrativi',visualizza:0}]);	  
	        	alert(rispsota);},                   
	        error: function(xhr, ajaxOptions, thrownError) {
	        	$("#ElaborazioneTabella").hide("slow");
        		alert(xhr.status+ " "+thrownError);
	        }
        }); 				
	});
});
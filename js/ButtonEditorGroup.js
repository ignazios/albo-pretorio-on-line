(function() {
    tinymce.create('tinymce.plugins.albo_gruppo_atti', {
        init : function(ed, url) {
			var elem = url.split('/');
  			var str = '';
  			for (var i = 0; i < elem.length-1; i++){
				str += elem[i] + '/';		
			}
    		ed.addCommand('frmAlbogrp', function() {
				ed.windowManager.open({
					title : title_button_gruppi,
					file : url + '/buttonEditorGruppiAlbo.php',
					width : 350, 
					height : 200,
					inline : 1
				});
			});
             ed.addButton('albo_gruppo_atti', {
                title : title_button_gruppi,
                image : str+'img/albogroup.png',
                cmd   : 'frmAlbogrp'
            });
        },
        createControl : function() {
            return null;
       }
    });
    tinymce.PluginManager.add('albo_gruppo_atti', tinymce.plugins.albo_gruppo_atti);
})();
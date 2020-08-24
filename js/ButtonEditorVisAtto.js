(function() {
    tinymce.create('tinymce.plugins.albo_visatto', {
        init : function(ed, url) {
			var elem = url.split('/');
  			var str = '';
  			for (var i = 0; i < elem.length-1; i++){
				str += elem[i] + '/';		
			}
    		ed.addCommand('frmAlbovisatto', function() {
				ed.windowManager.open({
					title : title_button_atto,
					file : url + '/buttonEditorVisAtto.php',
					width : 350, 
					height : 200,
					inline : 1
				});
			});
             ed.addButton('albo_visatto', {
                title : title_button_atto,
                image : str+'img/albovisatto.png',
                cmd   : 'frmAlbovisatto'
            });
        },
        createControl : function() {
            return null;
       }
    });
    tinymce.PluginManager.add('albo_visatto', tinymce.plugins.albo_visatto);
})();
(function() {
    tinymce.create('tinymce.plugins.albo', {
        init : function(ed, url) {
			var elem = url.split('/');
  			var str = '';
  			for (var i = 0; i < elem.length-1; i++){
				str += elem[i] + '/';	
			}
    		ed.addCommand('frmAlbo', function() {
				ed.windowManager.open({
					title : title_button_albo,
					file : url + '/gencode.php',
					width : 350, 
					height : 320,
					inline : 1
				});
			});
             ed.addButton('albo', {
                title : title_button_albo,
                image : str+'img/albo.png',
                cmd   : 'frmAlbo'
            });
        },
        createControl : function() {
            return null;
       }
    });
    tinymce.PluginManager.add('albo', tinymce.plugins.albo);
})();
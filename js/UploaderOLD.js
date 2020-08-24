/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
jQuery(document).ready(function($){
 
 
    var custom_uploader;
 
 
    $('#icona_upload').click(function(e) {
 
        e.preventDefault();
 
        //If the uploader object has already been created, reopen the dialog
        if (custom_uploader) {
            custom_uploader.open();
            return;
        }
 
        //Extend the wp.media object
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Icona per il tipo file',
            button: {
                text: 'Scegli un\'icona per il tipo file'
            },
            multiple: false
        });
 
        //When a file is selected, grab the URL and set it as the text field's value
        custom_uploader.on('select', function() {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            $('#icona').val(attachment.url);
            $('#IconaTipoFile').attr('src', attachment.url);
        });
 
        //Open the uploader dialog
        custom_uploader.open();
 
    });
});
<?php
/**
 * Gestione Allegati.
 * @link       http://www.eduva.org
 * @since      4.3
 *
 * @package    Albo On Line
 */
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }
	echo "<h2>Allegati Multipli</h2>";
	$TipiAmmessi=ap_tipiFileAmmessi(TRUE);
//	$TipiAmmessi=implode(",",$TipiAmmessi);
	$TEE="";
	$TE="";
	$AI="";
	foreach ( $TipiAmmessi as $Tipo) {
		$TE.='"'.$Tipo["."].'", ';
		$TEE.='.'.$Tipo["."].', ';
		$AI.='"'.$Tipo["."].'":"'.$Tipo["Icon"].'", ';
	}
	$TA=substr($TA,0,-2);
	$TE=substr($TE,0,-2);
	$AI=substr($AI,0,-2);
?>
<form action="?page=atti" method="post" enctype="multipart/form-data">
	<input type="hidden" name="operazione" value="upload" />
	<input type="hidden" name="action" value="memo-allegati-atto" />
	<input type="hidden" name="uploallegato" value="<?php echo wp_create_nonce('uploadallegati')?>" />
	<input type="hidden" name="id" value="<?php echo (int)$_REQUEST['id']; ?>" />
<div>
  <label for="files" id="pulCar"><span class="dashicons dashicons-portfolio" style="font-size:2em;padding-right:0.5em;margin-top:-7px;"></span> Seleziona gli allegati da caricare</label>
  <input type="file" id="files" name="files[]" accept="<?php echo $TEE;?>" multiple>
</div>
<div class="preview">
  <p>Nessun file selezionato per il caricamento</p>
</div>
<div>
  <button id="pulCar"><span class="dashicons dashicons-upload" style="font-size:2em;padding-right:0.5em;margin-top:-7px;"></span> Carica</button>
</div>
</form>
     <script>
        var input = document.querySelector('#files');
        var preview = document.querySelector('.preview');
        input.style.visibility = 'hidden';
        input.addEventListener('change', caricaDatiAllegati);
        function caricaDatiAllegati() {
          while(preview.firstChild) {
            preview.removeChild(preview.firstChild);
          }
          var curFiles = input.files;
          var list = document.createElement('ol');
          var icone = {<?Php echo $AI;?>};
	        preview.appendChild(list);
	        for(var i = 0; i < curFiles.length; i++) {
	          var icona=IconFileType(curFiles[i]);
	          var listItem = document.createElement('li');
	          listItem.className="elemento";
	          var para = document.createElement('p');
	          var des= document.createElement('input');
	          des.setAttribute("type", "text");
	          des.setAttribute("name", "Descrizione["+ i.toString() +"]");
	          if(validFileType(curFiles[i])) {
	            para.textContent = 'File name ' + curFiles[i].name + ', file size ' + returnFileSize(curFiles[i].size) + '.';
	            var image = document.createElement('img');
	            image.setAttribute("src", icona);
	            listItem.appendChild(image);
	            listItem.appendChild(para);
	            listItem.appendChild(des);
	          } else {
	            para.textContent = 'File name ' + curFiles[i].name + ': Tipo di file non permesso. Riprova selezionando un file con estensione diversa.';
	            listItem.appendChild(para);
	          }
	          list.appendChild(listItem);
	        }
        }
        function getEstensione(filename){
			var parti=filename.split(".");
			return parti[(parti.length) - 1].toLowerCase();
		}
        var fileTypes = [ <?php echo $TE;?> ];
        function validFileType(file) {
          var estensione=getEstensione(file.name);
          for(var i = 0; i < fileTypes.length; i++) {
            if(estensione.toLowerCase() === fileTypes[i].toLowerCase()) {
              return true;
            }
          }
          return false;
        }
       var icone = {<?Php echo $AI;?>};
        function IconFileType(file) {
        	var estensione=getEstensione(file.name);
            for(var i = 0; i < fileTypes.length; i++) {
            if(estensione === fileTypes[i]) {
              return icone[estensione];
            }
          }
          return false;
        }       
        function returnFileSize(number) {
          if(number < 1024) {
            return number + 'bytes';
          } else if(number > 1024 && number < 1048576) {
            return (number/1024).toFixed(1) + 'KB';
          } else if(number > 1048576) {
            return (number/1048576).toFixed(1) + 'MB';
          }
        }
     </script>
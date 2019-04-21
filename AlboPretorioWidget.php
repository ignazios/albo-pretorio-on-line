<?php
/**
 * Widget utilizzato per la pubblicazione degli atti da inserire nell'albo pretorio dell'ente.
 * @link       http://www.eduva.org
 * @since      4.2
 *
 * @package    ALbo On Line
 */

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

class AlboPretorioWidget extends WP_Widget
{
	public function __construct()
	{
	   parent::__construct( 'AlboPretorio', 'Albo On Line', array('description' => 'Grazie a questo widget &eacute; possibile visualizzare sulla sidebar le ultime pubblicazioni dell Albo Pretorio',array( 'width' => 300, 'height' => 350)));
	 }
    
	public function form($instance)
    {
    
	 $defaults = array(
 		'titolo_statistiche' => 'Dati Atti',
        'titolo_elenco' => 'Atti Correnti',
        'numero_atti' => 5,
        'pagina_albo' => NULL,
        'ordine_campo' => NULL,
		'ordinamento' => 'C'
        );
        $instance = wp_parse_args( (array) $instance, $defaults );?>
        <p>
            <label for="<?php echo $this->get_field_id( 'titolo' ); ?>">
                Titolo widget:
            </label>
            <input type="text" id="<?php echo $this->get_field_id( 'titolo' ); ?>" name="<?php echo $this->get_field_name( 'titolo' ); ?>" value="<?php echo $instance['titolo']; ?>" size="30" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'titolo_statistiche' ); ?>">
                Titolo cartella dati atti correnti:
            </label>
            <input type="text" id="<?php echo $this->get_field_id( 'titolo_statistiche' ); ?>" name="<?php echo $this->get_field_name( 'titolo_statistiche' ); ?>" value="<?php echo $instance['titolo_statistiche']; ?>" size="30" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'titolo_elenco' ); ?>">
                Titolo lista atti correnti:
            </label>
             <input type="text" id="<?php echo $this->get_field_id( 'titolo_elenco' ); ?>" name="<?php echo $this->get_field_name( 'titolo_elenco' ); ?>" value="<?php echo $instance['titolo_elenco']; ?>" size="30" />
        </p>        
		<p>
            <label for="<?php echo $this->get_field_id( 'numero_atti' ); ?>">
                Numero Atti da visualizzare:
            </label>
            <input type="text" id="<?php echo $this->get_field_id( 'numero_atti' ); ?>" name="<?php echo $this->get_field_name( 'numero_atti' ); ?>" value="<?php echo $instance['numero_atti']; ?>" size="2"/>

        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'pagina_albo' ); ?>">
               Pagina Albo:
            </label>
		<select id="<?php echo $this->get_field_id( 'pagina_albo' ); ?>" name="<?php echo $this->get_field_name( 'pagina_albo' ); ?>"> 
		 <option value=""><?php echo esc_attr( __( 'Select page' ) ); ?></option> 
		 <?php 
		  $pages = get_pages(); 
		  foreach ( $pages as $pagg ) {
		    if (get_page_link( $pagg->ID ) == $instance['pagina_albo'] ) 
				$Selezionato= 'selected="selected"';
			else
				$Selezionato="";
		  	$option = '<option '.$Selezionato.' value="' . get_page_link( $pagg->ID ) . '">';
			$option .= $pagg->post_title;
			$option .= '</option>';
			echo $option;
		  }
		 ?>
		</select>
        </p>
		<h3>Ordine Elenco</h3>
        <p>
            <label for="<?php echo $this->get_field_id( 'ordine_campo' ); ?>">
               In base a:
            </label>
		<select id="<?php echo $this->get_field_id( 'ordine_campo' ); ?>" name="<?php echo $this->get_field_name( 'ordine_campo' ); ?>"> 
		 <option value="Pubblicazione" <?php if ($instance['ordine_campo']=="Pubblicazione") echo 'selected="selected"'?> >Data Pubblicazione </option>
		 <option value="Scadenza" <?php if ($instance['ordine_campo']=="Scadenza") echo 'selected="selected"'?> >Data Scadenza </option>
		</select>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'ordinamento' ); ?>">
               Ordine:
            </label>
		<select id="<?php echo $this->get_field_id( 'ordinamento' ); ?>" name="<?php echo $this->get_field_name( 'ordinamento' ); ?>"> 
		 <option value="C" <?php if ($instance['ordinamento']=="C") echo 'selected="selected"'?> >Crescente </option>
		 <option value="D" <?php if ($instance['ordinamento']=="D") echo 'selected="selected"'?> >Decrescente </option>
		</select>
        </p>


       <?php
    }


public function widget( $args, $instance )
    {
		global $wpdb;

        extract( $args );

        $titolo = apply_filters('widget_title', $instance['titolo'] );
 		if ($titolo=='')
			$titolo="Albo Pretorio";
		$n_atti_attivi = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->table_name_Atti Where DataInizio <= CURDATE() And DataFine>= CURDATE() And Numero>0;");
		$n_atti_attivi_annullati = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->table_name_Atti Where DataInizio <= now() And DataFine>= now() And Numero>0 And DataAnnullamento<>'0000-00-00';");
        echo $before_widget;
        echo $before_title .$titolo. $after_title;
        echo "<div>";

    if ($instance['ordine_campo']=="Pubblicazione") 
    	$Ordinamento="DataInizio";
    else
    	$Ordinamento="DataFine";
	
    if ($instance['ordinamento']=="C") 
    	$Ordinamento.=" ASC";
    else
    	$Ordinamento.=" DESC";
	$coloreAnnullati=get_option('opt_AP_ColoreAnnullati');
	$lista=ap_get_all_atti(1,0,0,0,'',0,0,$Ordinamento,0,$instance['numero_atti']); 

	$HtmlW='<ul>';
	$CeAnnullato=FALSE;
	if ($lista){
		foreach($lista as $riga){
			if($riga->DataAnnullamento!='0000-00-00'){
				$Annullato='style="background-color: '.$coloreAnnullati.';"';
				$CeAnnullato=true;
			}else
				$Annullato='';
			if (strpos($instance['pagina_albo'],"?")>0)
				$sep="&amp;";
			else
				$sep="?";
			$HtmlW.= '<li '.$Annullato.'> <a href="'.$instance['pagina_albo'].$sep.'action=visatto&amp;id='.$riga->IdAtto.'">'.stripcslashes($riga->Oggetto) .'</a><br />
				</li>'; 
		}
	} else {
			$HtmlW.= '<li>
					Nessun Atto Codificato
				  </li>';
	}
	$HtmlW.= '</ul>';
	if ($CeAnnullato) 
		$HtmlW.= '<p>Le righe evidenziate con questo sfondo <span style="background-color: '.$coloreAnnullati.';">&nbsp;&nbsp;&nbsp;</span> indicano Atti Annullati</p>';
$HtmlW.= '</div>';
?>
			<div id="pp-tabs-container">
				<ul>
					<li><a href="#pp-tab-1"><?php echo $instance['titolo_statistiche']; ?></a></li>
					<li><a href="#pp-tab-2"><?php echo $instance['titolo_elenco']; ?></a></li>
				</ul>
				<div id="pp-tab-1">
                    <p>
				        Atti Correnti <?php echo $n_atti_attivi; ?><br />
				        di cui Annullati <?php echo $n_atti_attivi_annullati; ?>
				    </p>
                </div>
				<div id="pp-tab-2">
                      <?php echo $HtmlW; ?>
				</div>			
			</div>
<?php
	   echo $after_widget;
    }

	public function update( $new_instance, $old_instance )
	{
			$instance = $old_instance;
	
	        $instance['titolo'] = strip_tags( $new_instance['titolo'] );
	        $instance['titolo_statistiche'] = strip_tags( $new_instance['titolo_statistiche'] );
	        $instance['titolo_elenco'] = strip_tags( $new_instance['titolo_elenco'] );
	        $instance['numero_atti'] = strip_tags( $new_instance['numero_atti'] );
	        $instance['pagina_albo'] = strip_tags( $new_instance['pagina_albo'] );
	        $instance['ordine_campo'] = strip_tags( $new_instance['ordine_campo'] );
	        $instance['ordinamento'] = strip_tags( $new_instance['ordinamento'] );
	        
			return $instance;
	}
}	


class AlboPretorioElencoAttiCorrentiWidget extends WP_Widget
{
	public function __construct()
	{
	   parent::__construct( 'AlboOnLineAC', 'Albo On Line Atti Correnti', array('description' => 'Grazie a questo widget &eacute; possibile visualizzare gli atti correnti dell \'Albo Pretorio',array( 'width' => 300, 'height' => 350)));
	 }
    
        public function form($instance)
        {
         $defaults = array(
             'titolo' => 'Albo On Line Ultimi Atti',
            'numero_atti' => 5,
            'pagina_albo' => NULL,
            'ordine_campo' => NULL,
            'ordinamento' => 'C'
            );
            $instance = wp_parse_args( (array) $instance, $defaults );?>
            <p>
                <label for="<?php echo $this->get_field_id( 'titolo' ); ?>">
                    Titolo widget:
                </label>
                <input type="text" id="<?php echo $this->get_field_id( 'titolo' ); ?>" name="<?php echo $this->get_field_name( 'titolo' ); ?>" value="<?php echo $instance['titolo']; ?>" size="30" />
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'numero_atti' ); ?>">
                    Numero Atti da visualizzare:
                </label>
                <input type="text" id="<?php echo $this->get_field_id( 'numero_atti' ); ?>" name="<?php echo $this->get_field_name( 'numero_atti' ); ?>" value="<?php echo $instance['numero_atti']; ?>" size="2"/>

            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'pagina_albo' ); ?>">
                   Pagina Albo:
                </label>
            <select id="<?php echo $this->get_field_id( 'pagina_albo' ); ?>" name="<?php echo $this->get_field_name( 'pagina_albo' ); ?>">
             <option value=""><?php echo esc_attr( __( 'Select page' ) ); ?></option>
             <?php
              $pages = get_pages();
              foreach ( $pages as $pagg ) {
                if (get_page_link( $pagg->ID ) == $instance['pagina_albo'] )
                    $Selezionato= 'selected="selected"';
                else
                    $Selezionato="";
                  $option = '<option '.$Selezionato.' value="' . get_page_link( $pagg->ID ) . '">';
                $option .= $pagg->post_title;
                $option .= '</option>';
                echo $option;
              }
             ?>
            </select>
            </p>
            <h3>Ordine Elenco</h3>
            <p>
                <label for="<?php echo $this->get_field_id( 'ordine_campo' ); ?>">
                   In base a:
                </label>
            <select id="<?php echo $this->get_field_id( 'ordine_campo' ); ?>" name="<?php echo $this->get_field_name( 'ordine_campo' ); ?>">
             <option value="Pubblicazione" <?php if ($instance['ordine_campo']=="Pubblicazione") echo 'selected="selected"'?> >Data Pubblicazione </option>
             <option value="Scadenza" <?php if ($instance['ordine_campo']=="Scadenza") echo 'selected="selected"'?> >Data Scadenza </option>
            </select>
            </p>

            <p>
                <label for="<?php echo $this->get_field_id( 'ordinamento' ); ?>">
                   Ordine:
                </label>
            <select id="<?php echo $this->get_field_id( 'ordinamento' ); ?>" name="<?php echo $this->get_field_name( 'ordinamento' ); ?>">
             <option value="C" <?php if ($instance['ordinamento']=="C") echo 'selected="selected"'?> >Crescente </option>
             <option value="D" <?php if ($instance['ordinamento']=="D") echo 'selected="selected"'?> >Decrescente </option>
            </select>
            </p>
           <?php
        }


    public function widget( $args, $instance )
        {
            global $wpdb;

            extract( $args );

            $titolo = apply_filters('widget_title', $instance['titolo'] );
             if ($titolo=='')
                $titolo="Albo Pretorio";
            echo $before_widget;
            echo $before_title .$titolo. $after_title;
            echo "<div>";

        if ($instance['ordine_campo']=="Pubblicazione")
            $Ordinamento="DataInizio";
        else
            $Ordinamento="DataFine";

        if ($instance['ordinamento']=="C")
            $Ordinamento.=" ASC";
        else
            $Ordinamento.=" DESC";
        $coloreAnnullati=get_option('opt_AP_ColoreAnnullati');
        $lista=ap_get_all_atti(1,0,0,0,'',0,0,$Ordinamento,0,$instance['numero_atti']);
        $HtmlW='<ul>';
        if ($lista){
            foreach($lista as $riga){
                if($riga->DataAnnullamento!='0000-00-00'){
                    $Annullato='style="background-color: '.$coloreAnnullati.';"';
                    $CeAnnullato=true;
                }else
                    $Annullato='';
                if (strpos($instance['pagina_albo'],"?")>0)
                    $sep="&amp;";
                else
                    $sep="?";
                $HtmlW.= '<li><h3><span class="dataAtto">'.date_i18n("j M y", strtotime($riga->DataInizio)).'</span> - <span class="dataAtto">'.date_i18n("j M y", strtotime($riga->DataFine)).'</span> <a href="'.$instance['pagina_albo'].$sep.'action=visatto&amp;id='.$riga->IdAtto.'"'.$Annullato.'>'.stripcslashes($riga->Oggetto) .'</a></h3>
                    </li>';
            }
        } else {
                $HtmlW.= '<li>
                        Nessun Atto Codificato
                      </li>';
        }
        $HtmlW.= '</ul>';
        if ($CeAnnullato)
            $HtmlW.= '<p>Le righe evidenziate con questo sfondo <span style="background-color: '.$coloreAnnullati.';">&nbsp;&nbsp;&nbsp;</span> indicano Atti Annullati</p>';
    $HtmlW.= '</div>';
    ?>
                <div>
                  <?php echo $HtmlW; ?>
                </div>
    <?php
           echo $after_widget;
        }

        public function update( $new_instance, $old_instance )
        {
                $instance = $old_instance;

                $instance['titolo'] = strip_tags( $new_instance['titolo'] );
                $instance['numero_atti'] = strip_tags( $new_instance['numero_atti'] );
                $instance['pagina_albo'] = strip_tags( $new_instance['pagina_albo'] );
                $instance['ordine_campo'] = strip_tags( $new_instance['ordine_campo'] );
                $instance['ordinamento'] = strip_tags( $new_instance['ordinamento'] );

                return $instance;
        }

}	





function AlboWidget_register()
{
    register_widget( 'AlboPretorioWidget' );
    register_widget( 'AlboPretorioElencoAttiCorrentiWidget');
    
}
function AlboWidget_required_scripts()
{

    wp_enqueue_script('AlboPretorio-tabs', Albo_URL . 'js/Albo.jquery.tabs.js', array('jquery-ui-tabs'));
    wp_enqueue_style('AlboPretorio-ui-style', Albo_URL . 'css/jquery-ui-custom.css');
}


add_action('wp_enqueue_scripts', 'AlboWidget_required_scripts');
add_action( 'widgets_init', 'AlboWidget_register' );


?>
<div class="wrap">
	<div class="HeadPage">
		<h2 class="wp-heading-inline"><span class="dashicons dashicons-clipboard" style="font-size:1em;"></span> <?php _e("Albo Online Change log","albo-online");?></h2>
	</div>
	<div class="wp-editor-container" style="margin-top: 2em;">
	<ul style="padding: 0 1em 0 1em;">
		<li class="lista">
			<h3>4.5.7</h3>
			<ol class="lista">
				<li><strong>Risolti</strong> alcuni errori minori.</li>
				<li><strong>Modificato</strong> in meccanismo di gestione dell'interruzione del servizo di pubblicazione. Da questa versione gli atti non vengono più ripubblicati ma viene prorogata la data di scadenza dell'atto in base al numero di giorni di sospensione del servizio di pubblicazione.</li>

			</ol>
		</li>	
		<li class="lista">
			<h3>4.5.6</h3>
			<ol class="lista">
				<li><strong>Risolto</strong>  un errore nell'interfaccia per temi basato su Bootstrap Italia di Designers Italia, non venivano visualizzate le icone.</li>
			</ol>
		</li>	
		<li class="lista">
			<h3>4.5.5</h3>
			<ol class="lista">
				<li><strong>Risolti</strong> alcuni errori sulla gestione dei soggetti </li>
			</ol>
		</li>	
		<li class="lista">
			<h3>4.5.4</h3>
			<ol class="lista">
				<li><strong>Risolti</strong> alcuni errori minori </li>
				<li><strong>Implementata</strong> la possibilità di cancellare cancellare gli allegati degli atti scaduti e di modificare la data dell'oblio per poterli cancellare. Per cancellare gli allegati bisogna inserire la motivazione che verrà riportata nella visualizzazione dell'atto.</li>
			</ol>
		</li>	
		<li class="lista">
			<h3>4.5.3</h3>
			<ol class="lista">
				<li><strong>Risolti</strong> alcuni errori minori della visualizzazione frontend per tema Bootstrap Italia </li>
				<li><strong>Risolto</strong> problema per calcolo impronta Allegati</li>
				<li><strong>Implementata</strong> utility per ricalcolo impronta Allegati</li>
					</ol>
		</li>
		 <li class="lista">
			<h3>4.5.2</h3>
			<ol class="lista">
				<li><strong>Implementata</strong> la visualizzazione del frontend per i temi che utilizzano il template Bootstrap Italia di Designers Italia attivabile apposita opzione da Impostazioni </li>
				<li><strong>Corretti</strong> alcuni bug minori </li>
			</ol>
		</li>
		<li class="lista"><h3>4.5.1</h3>
			<ol class="lista">
				<li><strong>Corretti</strong> un bug in visatto.php e visatto_new.php </li>
			</ol>
		</li>
		<li class="lista"><h3>4.5</h3>
			<ol class="lista">
				<li><span class="listaTitolo">Atti</span>
					<ol class="lista">
						<li>La numerazione viene riportata con 7 cifre con riempimento a sinistra con 0. Questa modifica non comporta modifiche strutturali ai dati.</li>
						<li>Aggiunto, nell’atto, un campo di Testo di massimo 100 caratteri per l’inserimento della denominazione del richiedente la pubblicazione.</li>
						<li>Aggiunto, nell’atto, un campo per inserire il riferimento all’unità organizzativa responsabile (riconducibile all’ufficio dell’Ente o Area Organizzativa Omogenea)</li>
						<li>Aggiunto, nell’atto, un campo per inserire il riferimento al responsabile del procedimento amministrativo. Questa informazione era già presente nella gestione dell’albo nella gestione dei soggetti, però in quel caso non era obbligatorio indicare il responsabile del procedimento ma bastava indicare almeno un soggetto. Il riferimento al responsabile del procedimento amministrativo sarà recuperato dalla tabella dei Soggetti.</li>
					</ol>
				</li>
				<li><span class="listaTitolo">Unità organizzativa responsabile</span>
					<ol class="lista">
						<li>Inserita una tabella per la gestione delle unità organizzative in cui è articolato l’Ente/Area Organizzativa Omogenea</li>
						<li>Creata l’interfaccia per la gestione (Inserimento/Modifica/Cancellazione) delle unità organizzative</li>
						<li>Aggiunti i riferimenti alle unità organizzative nella gestione dell’albo.</li>
					</ol>
				</li>
				<li><span class="listaTitolo">Allegati</span>
					<ol class="lista">
						<li>Aggiunto un flag che indica se l’allegato è Documento (documento informatico sottoscritto con firma digitale</li>
						<li>Aggiunto un flag che indica se l’allegato è pubblicato in forma integrale o per estratto</li>
						<li>Aggiunto un campo in cui viene riportata l’impronta del file calcolata con algoritmo SHA256 calcolato al momento dell’upload del file.</li>
						<li>Modificate tutte le interfacce degli atti in cui vengono visualizzati i files. Adesso i files sono organizzati in documenti ed allegati e vengono riportati tutte le informazioni aggiunte e descritte nei precedenti punti.</li>
					</ol>
				</li>
				<li><span class="listaTitolo">Traduzione</span>
					<ol>
						<li>Implementata la possibilità di tradurre il plugin, sia lato frontend che backend</li>
					</ol>
				</li>
			</ol>
		</li>
	</ul>
	</div>
</div>
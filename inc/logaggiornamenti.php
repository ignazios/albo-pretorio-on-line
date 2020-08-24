<div class="wrap">
	<div class="HeadPage">
		<h2 class="wp-heading-inline"><span class="dashicons dashicons-clipboard" style="font-size:1em;"></span> <?php _e("Albo Online Change log","albo-online");?></h2>
	</div>
	<div class="wp-editor-container" style="margin-top: 2em;">
	<ul style="padding: 0 1em 0 1em;">
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
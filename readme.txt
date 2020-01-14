=== Plugin Name ===
Contributors: Scimone Ignazio
Donate link: 
Tags: Albo Pretorio, Codice Amministrazione Digitale, Upload File
Requires at least: 3.5
Tested up to: 5.3.2
Stable tag: 4.4.4
Albo Pretorio On Line permette la gestione dell'albo pretorio on line in base al nuovo Codice dell'Amministrazione Digitale
== Description ==

Albo Pretorio On Line e' un plugin per WordPress che tenta di dare una risposta all'esigenza delle pubbliche amministrazioni di avere a disposizione uno strumento con il quale pubblicare i propri atti in adempimento dell'art. 32 della LEGGE 18 giugno 2009, n. 69 e successive modifiche.

Questa legge prevede che dal 1 gennaio 2011 gli atti soggetti a pubblicazione devono essere pubblicati sul sito internet istituzionale dell'ente per avere efficacia legale.

Si ringrazia Alessandro Cingolani per la consulenza in ambito sicurezza informatica.

== Installation ==

Di seguito sono riportati i passi necessari per l'installazione del plugin. la procedura dettagliata la potete trovare sul sito dedicato al plugin http://www.albo.eduva.org/

1. Scaricare il plugin dal repository di wordpress o dal sito di riferimento
2. Attivare il plugin dal menu Plugins
3. Inserire gli atti lato amministrazione
4. inserire uno dei tre shortcode attraverso il relativo pulsante dell'editor della pagina/articolo oppure manualmente 
	4.1 [Albo stato="1" per_page="10" cat="1" filtri="si" minfiltri="no"]
	    	<strong>stato</strong> pu&ograve; assumere i seguenti valori
	    		1 - solo gli atti in corso di validit&agrave;
	        	2 - solo gli atti scaduti (storico)
		<strong>per_page</strong> indica il numero massimo di atti che vengono visualizza in ogni pagina
		<strong>cat</strong> opzionale l' ID della categoria di cui verranno visualizzati gli atti 
		<strong>filtri</strong> opzionale indica se deve essere visualizzata la finestra dei filtri. Se non specificato i filtri vengono visualizzati. utile nel caso si utizza lo shortcode nelle pagine dell'Amministrazione Trasparente
		<strong>minfiltri</strong> opzionale indica se deve essere attivata la minimizzazione della finestra dei filtri. Se non specificato viene attivata la minimizzazione della finestra filtri.
	<strong>Uso</strong>: <em>Questo shortcode permette la visualizzazione della tabella degli atti nel frontend</em>
	4.2 [AlboGruppiAtti titolo="" meta="" valore=""]
		<strong>titolo</strong> titolo che viene visualizzato sopra la tabella
		<strong>meta</strong> metadato di raggruppamento
		<strong>valore</strong> valore del metadato per cui raggruppare gli atti
	<strong>Uso</strong>: <em>Questo shortcode permette di raggruppare un gruppo di atti eterogeno in base ad un metadato con uno specifico valore. Viene utilizzato, ad esempio, per raggruppare tutti gli atti che concorrono all'espletamento di una gara di acquisto</em>
	4.3 [AlboAtto titolo="" numero="" anno=""]
		<strong>titolo</strong> titolo che viene visualizzato come intestazione dei dati dell'atto
		<strong>numero</strong> Numero dell'atto da visualizzare
		<strong>anno</strong> Anno a cui si riferisce il numero dell'atto
	<strong>Uso</strong>: <em>Questo shortcode permette di visualizzare i dati dell'atto individuato dal numero/anno indicato nei parametri</em>

Per maggiori informazioni usare il repo si github https://github.com/ignazios/albo-pretorio-on-line
Per segnalare problemi, proposte di modifiche utilizzare l'indirizzo email ignazios@gmail.com
== Changelog ==
= 4.4.4 =
- <strong>Corretti</strong> alcuni bug.
- <strong>Modificato</strong> il sistema di filtro degli atti.
= 4.4.3 =
- <strong>Corretti</strong> alcuni bug di sicurezza nella gestione dei filtri del front-end.
= 4.4.2 =
- <strong>Corretti</strong> alcuni bug di sicurezza.
= 4.4.1 =
- <strong>Corretto</strong> errore caricamento allegati, anche se non attivata l'archiviazione per anno/mese, gli allegati venivano caricati nella cartella anno/mese che viene creata automaticamente in fase di caricamento.
= 4.4 =
- <strong>Modifica</strong> delle REST-API per le Statistiche, per singolo Atto.
- <strong>Implementazione</strong> dell'archiviazione, a richiesta, degli allegati per anno/mese come avviene per i Media.
  <strong>PRIMA DI ESEGURE QUESTA OPERAZIONE BISOGNA FARE IL BACKUP DEGLI ALLEGATI</strong>
- <strong>Revisione</strong> del codice e correzione di ulcuni bug minori
- <strong>Implementato</strong> un sistema che permette di personalizzare l'interfaccia di visualizzazione lato pubblico degli atti all'interno del tema.
	  Per personalizzare l'interfaccia dell'albo bisogna:
		1) creare la seguente struttura di cartelle partendo dalla cartella del tema wp-content/themes/TEMA_CORRENTE/<strong>plugins/albo-pretorio-on-line/admin</strong>
		2) copiare all'interno della cartella i seguenti files:
			- frontend.php
			- frontend_filtro.php
			- visatto.php
		3) personalizzare i files a proprio piacimento.
= 4.3 =
- <strong>Implementazione</strong> delle REST-API dei dati dell'albo, da attivare esplicitamente
- <strong>Implementazione</strong> di un nuovo shortcode che permette la visualizzazione di singoli atti
- <strong>Revisione</strong> del codice e correzione di ulcuni bug minori
- <strong>Riscritta</strong> la procedura di disinstallazione del plugin
- <strong>Adeguamento</strong> dell'interfaccia al design kit di Designers Italia
= 4.2 =
- <strong>Adeguamento</strong> alla versione 5.0 di Wordpress
= 4.1.95 =
- <strong>Corretta</strong> procedura allineamento Enti
= 4.1.94 =
- <strong>Implementata</strong> una Nuova Utility in <strong>Utility/Utility dati</strong> che permette di assegnare un Ente codificato per gli atti con <span style="color:red;">Ente non valido</span>
- <strong>Corretti</strong> alcuni bugs minori riscontrati a seguito degli ultimi aggiornamneti
= 4.1.92 =
- <strong>Imostato</strong> l'Ente come campo obbligatorio in fase di Creazione e Modifica Atto
= 4.1.91 =
- <strong>Corretti</strong> alcuni bugs nella gestione dei soggetti e nella gestione delle tabelle.
= 4.1.9 =
- <strong>Corretto</strong> un bug che non permetteva la corretta visualizzazione dell'atto
= 4.1.8 = 
- <strong>Introdotta</strong> la gestione dei soggetti che permettono di definire le persone che a vario titolo hanno una funzione della gestione dell'albo. Di default i responsabili della vecchia gestione vengono assegnati come "Responsabile Procedimento" agli atti già codificati.
- <strong>Introdotta</strong> la possibilità di stampare: l'Avviso di pubblicazione ed il certificato di Pubblicazione
- <strong>Corretti</strong> bug minori
= 4.1.7 = 
- <strong>Modificata</strong> procedura di visualizzazione degli atti con lo shortcode [AlboGruppiAtti]
- <strong>Inserita</strong> opzione per impostare la pagina degli atti storico
- <strong>GDPR</strong> da questa versione non viene più memorizzato l'IP della connessione nel file di log.
- <strong>GDPR</strong> inserita nelle Utility una cartella per la gestione delle utility inerenti il GDPR, al momento è presente solo la funzione Cancella il campo IP nella tabella di log.
= 4.1.6 = 
- <strong>Modificato</strong> l'upload dei files, adesso è possibile caricare allegati con estensioni sia in maiuscolo che in minuscolo
- <strong>Revisionata</strong> la procedura di download degli allegato nel frontend.
= 4.1.5 = 
- <strong>Modificato</strong> il sistema di caricamento degli allegati, adesso è possibile caricare pi&ugrave; file contemporaneamente.
- <strong>Modificata</strong> l'interfaccia del filtro degli atti nel FrontEnd. Adesso si possono filtrare gli atti anche per Ente ed è stato sostituito l'iconcina per aprire e chiudere la finestra di visualizzazione dei filtri con un pulsante
- <strong>Modificato</strong> il meccanismo di apertura dei file, adesso gli allegati si aprono in una nuova finestra
= 4.1.4 = 
- <strong>Corretto</strong> lo shortcode che non filtrava per categoria
= 4.1.3 = 
- <strong>Modificato</strong> il filtro degli atti nel FrontEnd. Adesso si possono filtrare gli atti anche per Riferimento e Numero/anno atto
- <strong>Inserita</strong> la colonna Data (data creazione atto) nelle colonne disponibili per la visualizzazione nel FrontEnd
- <strong>Sistemato</strong> permessi per Amministratori ed Editori 
- <strong>Corretti</strong> alcuni bug minori
= 4.1.2 = 
- <strong>Corretto</strong> processo di aggiornamento del plugin
= 4.1.1 = 
- <strong>Aggornata</strong> gestione log tipi di files
= 4.1 = 
- <strong>Introdotti</strong> i metadati dell'atto
- <strong>Introdotta</strong> la gestione dei tipi di file
- <strong>Introdotta</strong> la possibilità di modificare l'interfaccia del frontend con le “Linee guida di design per i servizi web della PA”
- <strong>Modificata</strong> l'interfaccia del backend con icone standard Wordpress e sempre visibili negli elenchi degli atti
- <strong>Modificata</strong> la bacheca, sono stati introdotti alcuni controlli sulle impostazioni del plugin
- <strong>Risolti</strong> vari bug minori
= 4.0.4 = 
- <strong>Risolto</strong> Falso positivo segnalato da Maldet Linux Malware Detect (LMD) sulla funzione che permetteva di estrarre i permessi dei files e delle cartelle 
- <strong>Corretti</strong> piccoli errori di codice HTML che non facevano validare le pagine del front end
- <strong>Aggiunto</strong> sistema di ricerca nella pagina di gestione degli atti nel backend (Si ringrazia Rosanna Naccarato per la segnalazione ed il codice)
= 4.0.3 =
- <strong>Risolto</strong> Bug che bloccava la visualizzazione della pagina atti
= 4.0.2 =
- <strong>Risolto</strong> Bug che bloccava l'albo dopo l'aggiornamento
= 4.0.1 =
- <strong>Sistema</strong> problema con sistema di minimizzazione della finestra fei filtri nel FrontEnd
- <strong>Spostate</strong> nella caretella wp-content/AlboOnLine delle cartelle dei Backup generati con l'apposita Utility wp-content/AlboOnLine/BackupDatiAlbo e delle copie di salvataggio pre Oblio nella cartella wp-content/AlboOnLine/OblioDatiAlbo.
- <strong>Sistemato</strong> errore che generava tre voci nell'elenco dei plugin per l'Albo
= 4.0 =
- <strong>revisionato</strong> completamente il backend del plugin riportandolo sia nell'aspetto che nel codice pi&ugrave; uniforme a quello di Wordpress
- <strong>Eliminato</strong> il plugin DataTables di Jquery sia dal backend che dal frontend
- <strong>Eliminate</strong> le librerie Jquery del plugin, da questa versionesi utilizzano quelle di Wordpress
- <strong>Revisionato</strong> parte del codice.
- <strong>Personalizzazione</strong> della tabella che visualizza gli atti nel frontend. Per decidere quali dai devono essere visalizzati bisogna flaggare il nome del campo nel backend in Albo Pretorio=>Parametri=>Colonne Tabella Front End
- <strong>Revisionata</strong> lo splash screen del plugin nel backend.
- da questa versione le modifiche che implicano modifiche nelle modalit&agrave; operative di funzionamento del plugin vengono descritte nel <strong>canale YouTube</strong> dedicato.
= 3.7.4 =
- <strong>Corretto</strong> il link al sito per la segnalazione dell'uso del plugin 
- <strong>Corretti</strong> piccoli bugs 
= 3.7.2 =
- <strong>Corretto</strong> il calcolo della data dell'oblio 
- <strong>Eliminato</strong> il parametro gorni di default oblio, adesso viene impostato di default il 1 Gennaio dell'anno successivo ai 5 anno della scadenza dell'atto
= 3.7.1 =
- <strong>Corretto</strong> errore del calendario nei campi data 
= 3.7 =
- <strong>Corretto</strong> errore su visualizzazione allegati superiori ai 2MB
- <strong>Verificato</strong> il codice e <strong>Corretti</strong> alcuni piccoli errori
= 3.6.2 =
- <strong>Adeguamento</strong> alla versione 4.5 di Wordpress
= 3.6.1 =
- <strong>Aggiunta</strong> la possibilit&agrave; di scaricare gli allegati degli atti oltre che visualizzarli direttamente nel browser
- <strong>Corretti</strong> bug minori; Nella tabella degli atti i testi son stati ripuliti dai caratteri backslashes 
= 3.6 =
- <strong>Creato</strong> nuovo widget che elenca gli ultimi atti sotto forma di elenco degli atti correnti
- <strong>Corretti</strong> bug minori; Nell'elenco degli atti dei widget i testi son stati ripuliti dai caratteri backslashes 
= 3.5 =
- <strong>Inserita</strong> la funzione di stampa de repertorio reperibile in Albo Pretorio/atti cartella Repertorio Atti
- <strong>Inserita</strong> la possibilit&agrave; di rimuovere il file allegato in cao di annullamento dell\'atto
= 3.4.3 =
- <strong>Corretto</strong> bug sul sistema di annullamento degli atti
= 3.4.2 =
- <strong>Corretti</strong> bug minori; In fase di disinsatllazione non venivano cancellate alcuni parametri che in fase di reinstallazione non permettevano la visualizzazione del menu del plugin.
- <strong>Aggiunto</strong> il supporto del protocollo https nel file .htaccess che viene creato ella cartella AllegatiAttiAlboPretorio
= 3.4.1 =
- <strong>Corretti</strong> bug minori; il link Torna indietro che non riportava il link corretto in caso di sito installato in sottocartella. Modificata la stringa di stato che viene ritornata dalla procedura degli allegati.
- <strong>Inseriti</strong> elementi grafici mancanti dei plugin Jquery
= 3.4 =
- <strong>Riorganizzata</strong> l'interfaccia di amministrazione
- <strong>Aggiunta</strong> la data oblio, data da cui l'atto pu&ograve; essere cancellato definitivamente. In una prossima release verr&agrave; implementato un sistema di cancellazione automatica degi atti da cancellare.
= 3.3 =
- <strong>Risolti</strong> vari problemi di sicurezza
= 3.2 =
- <strong>Introdotta</strong> la possibilit&agrave; di disattivare il tracciamento delle operazioni attraverso il log delle operazioni, tutte o solo quelle di gestione degli atti, con la possibilit&agrave; di mantenere il tracciamento delle visite e dei download
- <strong>Introdotta</strong> la possibilit&agrave; di svuotare il file di log, tutte le registrazioni o solo squelle di gestione degli atti, con la possibilit&agrave; di mantenere quelle relative al tracciamento delle visite e dei download
= 3.1.1 =
- <strong>Modificata</strong> la libreria di funzioni per la gestione del diritto all'oblio, nello specifico il file .htaccess, che veniva riconosciuto come virus da alcuni ISP
= 3.1 =
- <strong>Modificata</strong> la visualizzazione dell'elenco degli atti, adesso gli atti vengono visualizzati solo con numero atto ed oggetto con la possibilit&agrave; di visualizzare il resto delle informazioni cliccando su un'iconcinaposta alla sinistra della lista
- <strong>Riorganizzati</strong> i nomi dei files
= 3.0.9 =
- <strong>Corretti</strong> bug sui permessi di accesso alle pagine di configurazione
= 3.0.8 =
- <strong>Corretto</strong> problema sulla visualizzazione delle statistiche
- <strong>Implementata</strong> nuova funzionalit&agrave; in utility <strong>Procedura post trasferimento sito</strong> che permette la rigenerazione dei file .htaccess, index.php e robots.txt ed il riallineamento degli allegati agli atti sul nuovo percorso. 
= 3.0.7 =
- <strong>Corretto</strong> problema quando si memorizzavano i permessi
= 3.0.6 =
- <strong>Corretto</strong> errata dimensione tabella forntend
- <strong>Corretta</strong> doppia visualizzazione nell'elenco plugin della riga Albo Pretorio Online Widget
= 3.0.5 =
- <strong>Corretti</strong> vari Bugs creati dall'introduzione nel FrontEnd delle DataTables che permettono una migliore gestione dell'elenco degli atti
- <strong>Ripristinato</strong> il pulsante per l'inserimento dello shortcode, &egrave; stato riorganizzato, sono stati eliminati gli elementi obsoleti
= 3.0.4 =
- <strong>Corretto</strong> problema di compatibilit&agrave; con Pasw2015
= 3.0.3 =
- <strong>Corretti</strong> vari bugs: Errori di validazione Front End
- <strong>Corretto</strong> errore di visualizzazione allegati con il browser Internet Explorer.
= 3.0.2 =
- <strong>Corretti</strong> vari bugs: Errori di validazione Front End, Errore di paginazione
- <strong>Implementata</strong> opzione nello shortcode che permette di visualizzare la finestra dei filtri fissa come avveniva fino alla versione 2.9
= 3.0.1 =
- <strong>Implementato</strong> diritto all'oblio da attivare dopo l'aggiornamento.
- <strong>Aggiunta</strong> la possibilit&agrave; di cancellare gli atti dopo la loro scadenza
- <strong>Modificata</strong> l'interfaccia degli atti, nella lista ora vengono visualizzati separatamente gli atti da pubblicare da quelli pubblicati,
- <strong>Aggiunta</strong> l'opzione nello shortcode <em>Filtri</em> che permette di escludere dalla pagina la finestra dei filtri
- <strong>Revisionata</strong> l'interfaccia pubblica del plugin 
- <strong>Sostituito</strong> il calendario con il plugin di Ajax
- <strong>Corretti</strong> vari bugs
= 2.9 =
- <strong>Aggiunto</strong> elemento (<strong>OPZIONALE</strong>) dello shortcode che permete di specificare la categoria degli atti da visualizzare.
- <strong>Aggiunto</strong> pulsante, nella barra dell'editor delle pagine e degli articoli, che permette di inserire attraverso la compilazione di un form lo shortcode necessario per la visualizzazione degli atti.
= 2.8 =
- <strong>Risolto</strong> problema mancanza immagini utility
- <strong>Risolto</strong> errore creazione Sql Backup
= 2.7 =
- <strong>Modificato</strong> l'ordine di visualizzazione degli atti nel front end, adesso vengono visualizzati per anno/numero in ordine inverso
- <strong>Eliminato</strong> l'editor del foglio di stile
- <strong>Eliminato</strong> il parametro Effetti Testo Shadow
- <strong>Eliminato</strong> il parametro Effetti CSS3
- <strong>Modificata</strong> la dimensione del riferimento dell'atto, passato da 20 a 100 caratteri 
- <strong>Modificata</strong> la dimensione dell'OGGETTO dell'atto, passato da 150 a 200 caratteri 
- <strong>Modificata</strong> la dimensione del riferimento dell'atto, passato da 20 a 100 caratteri 
- <strong>Modificata</strong> la dimensione del Motivo dell'annullamento dell'atto, passato da 100 a 200 caratteri 
- <strong>Inserita</strong> la funzione di backup dei dati dell'albo accessibile da utility, viene creata una cartella in wp-content/plugins/albo-pretorio-on-line/BackupDatiAlbo in cui vengono posizionati i files zippati il cui nome contiene la date e l'ora di creazione.
Il file zippato contiene uno script Sql per la ricostruzione delle tabelle dell'albo con i dati e le impostazioni delle opzioni dell'albo compreso anche il numero progressivo e tutti gli allegati agli atti.
- <strong>Inserita</strong> la funzione di verifica del Data Base, della struttura e di congruit&agrave; dei dati.
= 2.6 =
- <strong>Corretti</strong> bugs relativi alla visualizzazione nel widget nel momento in cui non ci sono atti pubblicati
- <strong>Corretto</strong> comportamento della procedura di pubblicazione.
- <strong>Corretti</strong> i link Torna Indietro nella gestione degli atti
= 2.5 =
- <strong>Corretti</strong> bugs relativi al passaggio alla versione 3.5 di Wordpress
= 2.4 =
- <strong>Risolto</strong> il problema di validazione del front end della pagina in cui viene visualizzato l'elenco degli atti
- <strong>Risolto</strong> il problema di indicizzazione del sito quando veniva inserita la pagina dell'albo
= 2.3 =
- <strong>Implementata</strong> la gestione degli enti titolari dei singoli Atti. Negli atti verr&agrave; aggiunto un campo in cui specificare l'ente che ha emesso l'atto, mentre per gli atti gi&agrave; codificati verr&agrave; riportato il valore 0 utilizzato per l'ente titoalere del sito. L'ente 0 non si pu&ograve; cancellare, si pu&ograve;solo modificare
- <strong>Implementata</strong> la procedura di ripubblicazione massiva degli atti attivi in caso di interruzione del servizio di pubblicazione, cio&egrave; nel caso in cui il sito va fuori servizio e risulta non accessibile.
- <strong>Revisionata</strong> l'interfaccia pubblica, ora il filtro in base alla categoria &egrave; stato inserito in unacasella di riepilogo.
- <strong>Revisionata</strong> nel back end &egrave; stata inserita la paginazione degli atti.
- <strong>Revisionata</strong> la gestione dei meta tag del front end che prima venivano riportati in tutto il sito, adesso vengono inseriti dal plugin solo nelle pagine dedicate all'albo.  
= 2.2 =
- <strong>Revisionato</strong> il sistema dei log, adesso &egrave; possibile ricostruire cronologicamente tutti gli eventi legati agli oggetti della gestione.
- <strong>Revisionato</strong> il codice XHTML ed il CSS, sono stati eliminati i colori del testo ed altre impostazioni che potevano dare fastidio al template utilizzato.
- <strong>Inserito</strong> in Parametri la gestione del colore di sfondo degli atti Annullati
- <strong>Inserita</strong> la possibilit&agrave; di ANNULLARE un atto nel periodo di pubblicazione
- <strong>Inserito</strong> il Widget per la visualizzazione degli atti in una sidebar
- <strong>Modificati</strong> i valori di default <em>Utilizza effetti testo Shadowin</em> ed <em> Utilizza effetti CSS3</em> che adesso dopo l'attivazione del plugin saranno disattivati
- <strong>Modificato</strong> il comportameto del plugin nella fase di configurazione, quando viene specificata una cartella che non esiste in <em>Cartella Upload</em>, adesso verr&agrave; creata automaticamente
= 2.1 =
- <strong>Inserito</strong> il codice per garantire la non indicizzazione della pagina dell'albo e conseguentemente dei link (allegati) in essa contenuti
= 2.0 =
- <strong>Sistemato</strong> problema con l'upload degli allegati
= 1.9 =
- <strong>Sistemati</strong> alcuni errori di scrittura del codice HTML che non lo rendevano valido in base al DTD XHTML 1.0 strict
- <strong>Modificato</strong> il CSS, sono stati eliminati le ridefinizioni dei titolo h2, h3 ed h4 e le dimensioni dei caratteri sono stati espressi in em;
= 1.8 =
- <strong>Implementato</strong> la statistica sugli accessi ai singoli atti e sui download degli allegati
- <strong>Modificata</strong> la gestione dei log lato amministrazione nella visualizzazione atto
= 1.7 =
- <strong>Sistemato</strong> problema di aggiornamento della tabella atti che rendevano impossibile, dopo l'aggiornamento alla versione 1.6, di memorizzare gli atti
- <strong>Sistemato</strong> problema di impostazione cartella di upload di default, non veniva riportata alcuna cartella, oraviene impostato il valore di wp-content\uploads  
= 1.6 =
- <strong>Aggiunta</strong> la gestione del responsabile del trattamento, che pu&ograve; essere associato ad ogni atto e visualizzato, quando indicato, insieme ai dati dell'atto
= 1.5 =
- <strong>Aggiunta</strong> la gestione dinamica degli effetti nel front end. Si possono attivare e disattivare gli effetti sul testo e gli effetti di smussamento degli angoli delle tabelle di filtro
- <strong>Aggiunta</strong> la possibilita' di gestire il livello dei titoli da h2 ad h4 di:
	- Nome Ente
	- Titolo Pagina
	- Titoli aree filtro
- <strong>Modificata</strong> la gestione della cartella di download, adesso bisogna specificare una cartella del file system partendo dalla cartella root di Wordpress.
= 1.4 =
- <strong>Aggiunto</strong> l'editor per il file CSS
= 1.3 =
- <strong>Risolto</strong> problema cancellazione atto
= 1.2 =
- <strong>Risolti</strong> problemi lato utente nella visualizzazione dell'atto, paginazione e filtri
- <strong>Aggiunto</strong> lato utente ora viene riportato il nome dell'ente
- <strong>Modificato</strong> in fase di attivazione viene ora riportato la cartella di Upload /home/sisvilup/public_html/wp-content/plugins/albo-pretorio-on-line/allegati
= 1.1 =
- <strong>Sistemati</strong> i problemi con le icone lato amministrazione
- <strong>Migliorato</strong> lato utente
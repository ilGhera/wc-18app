=== WooCommerce 18app - Premium ===
Contributors: ghera74
Tags: Woocommerce, e-commerce, shop, orders, payment, payment gateway, payment method, 
Version: 1.2.3
Requires at least: 4.0
Tested up to: 6.0

Description: Abilita in WooCommerce il pagamento con buoni 18app, il Bonus Cultura previsto dallo stato Italiano. 

== Description ==

Il plugin consente di abilitare sul proprio store il pagamento con 18app, il Bonus Cultura previsto dallo sato italiano.
In fase di checkout, il buono inserito dall'utente verrà verificato per validità, credito disponibile e pertinenza in termini di tipologia di prodotto.


= Nore importanti =
Il plugin prevede l'invio di contenuti ad un servizio esterno, in particolare i dati relativi ai prodotti acquistati dall'utente come categoria d'appartenenza e prezzo.

= Indirizzo di destinazione =
[https://ws.18app.italia.it/VerificaVoucherWEB/VerificaVoucher](https://ws.18app.italia.it/VerificaVoucherWEB/VerificaVoucher)

= Maggiori informazioni sul servizio 18app: =
[https://www.18app.italia.it](https://www.18app.italia.it)

= Informativa privacy del servizio: =
[https://www.18app.italia.it/static/18app%20infoprivacy_completa.pdf](https://www.18app.italia.it/static/18app%20infoprivacy_completa.pdf)


= Important notes =
This plugin sends data to an external service, like the categories and the prices of the products bought by the user.

= Service endpoint: =
[https://ws.18app.italia.it/VerificaVoucherWEB/VerificaVoucher](https://ws.18app.italia.it/VerificaVoucherWEB/VerificaVoucher)

= Service informations: =
[https://www.18app.italia.it](https://www.18app.italia.it)

= Service privacy policy: =
[https://www.18app.italia.it/static/18app%20infoprivacy_completa.pdf](https://www.18app.italia.it/static/18app%20infoprivacy_completa.pdf)


= Funzionalità =

* Caricamento certificato (.pem)
* Impostazione categorie prodotti WooCommerce acquistabili
* Generazione richiesta certificato (.der)
* Generazione certificato (.pem)


== Installation ==

= Dalla Bacheca di Wordpress =

* Vai in  Plugin > Aggiungi nuovo.
* Cerca WooCommerce 18app e scaricalo.
* Attiva Woocommerce 18app dalla pagina dei Plugin.
* Una volta attivato, vai in <strong>WooCommerce/ WC 18app</strong> e imposta le tue preferenze.

= Da WordPress.org =

* Scarica WooCommerce 18app
* Carica la cartella wc-18app su /wp-content/plugins/ utilizzando il tuo metodo preferito (ftp, sftp, scp, ecc...)
* Attiva WooCommerce 18app dalla pagina dei Plugin.
* Una volta attivato, vai in <strong>WooCommerce/ WC 18app</strong> e imposta le tue preferenze.


== Changelog ==

= 1.2.3 =
Data di rilascio: 10 Agosto, 2022

* Correzione bug: Errore controllo buoni 18app con ambito formazione 

= 1.2.2 =
Data di rilascio: 22 Giugno, 2022

* Correzione bug: Errore controllo abbinamenti Categorie/ Beni 18app

= 1.2.1 =
Data di rilascio: 16 Giugno, 2022

* Correzione bug: Errore in array_intersect con opzione controllo prodotti a carrello attiva

= 1.2.0 =
Data di rilascio: 1 Giugno, 2022

* Implementazione: Nuova funzionalità sandbox
* Implementazione: Mostra metodo di pagamento solo se consentito dai prodotti presenti a carrello
* Correzione bug: Codice 18app mancante in email di conferma d'ordine

= 1.1.2 =
Data di rilascio: 4 Aprile, 2022

* Correzione bug: Possibile mancato salvataggio singolo abbinamento Categoria/ Bene 18app 

= 1.1.1 =
Data di rilascio: 22 Maggio, 2021

* Correzione bug: Mancata eliminazione ordine temporaneo in caso di conversione buono 18app in codice sconto

= 1.1.0 =
Data di rilascio: 20 Maggio, 2021

* Implementazione: Opzione di conversione buono 18app in codice sconto applicato a carrello nel caso in cui il valore del buono sia inferiore al totale a carrello
* Implementazione: Interfaccia migliorata. 

= 1.0.5 =
Data di rilascio: 28 Aprile, 2020

* Correzione bug: Impossibile eliminare certificato non funzionante
* Correzione bug: Errore salvataggio file .der in presenza del plugin WooCommerce Carta Docente - Premium 

= 1.0.4 =
Data di rilascio: 10 Febbraio, 2020

* Correzione bug: Categorie impostabili limitate

= 1.0.3 =
Data di rilascio: 30 Ottobre, 2019

* Correzione bug: Categorie prodotti WooCommerce non riconosciute e conseguente impossibilità di completare l'acquisto.

= 1.0.2 =
Data di rilascio: 02 Ottobre, 2019

* Implementazione: Possibilità di abbinare differenti categorie WooCommeerce allo stesso "bene" 18app .
* Correzione bug: Categorie beni 18app mancanti.

= 1.0.1 =
Data di rilascio: 27 Giugno, 2019

* Correzione bug: SOAP-ERROR: Parsing WSDL: Couldn't load from .../wp-content/plugins/wc-18app-premium/includes/VerificaVoucher.wsdl' : failed to load external entity .../wp-content/plugins/wc-18app-premium/includes/VerificaVoucher.wsdl

= 1.0.0 =
Data di rilascio: 5 Febbraio, 2019

* Implementazione: Backup di ogni richiesta certificato generato con relativa chiave
* Implementazione: Nuova cartella wc18-private in wp uploads directory
* Correzione bug: Eliminazione contenuto cartella private con aggiornamento 
* Correzione bug: Mancato salvataggio di un singolo abbinamento di categorie prodotti 

= 0.9.1 =
Data di rilascio: 8 Novembre, 2018

* Implementazione: Possibilità di abbinare differenti "beni" 18app alla stessa categoria WooCommeerce.
* Implementazione: Aggiornata gamma "beni" disponibili.

= 0.9.0 =
Data di rilascio: 16 Ottobre, 2018

* Prima release.

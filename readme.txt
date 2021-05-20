=== WooCommerce 18app ===
Contributors: ghera74
Tags: woocommerce, 18app, bonus cultura, e-commerce, shop, orders, payment, payment gateway, payment method, 
Version: 1.1.0
Requires at least: 4.0
Tested up to: 5.7

Description: Abilita in WooCommerce il pagamento con buoni 18app, il Bonus Cultura previsto dallo stato Italiano. 

== Description ==

Il plugin consente di abilitare sul proprio store il pagamento con 18app, il Bonus Cultura previsto dallo stato italiano.
In fase di checkout, il buono inserito dall'utente verrà verificato per validità, credito disponibile e pertinenza in termini di tipologia di prodotto.


= Note importanti =
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
* Generazione richiesta certificato (.der) (Premium)
* Generazione certificato (.pem) (Premium)


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


== Screenshots ==
1. Carica il tuo certificato
2. Genera il file .der per richiedere il tuo certificato (Premium)
3. Crea il tuo certificato (Premium)
4. Imposta le categorie dei prodotti acquistabili
5. Metodo di pagamento in pagina di checkout


== Changelog ==


= 1.1.0 =
Data di rilascio: 20 Maggio, 2021

* Implementazione: Opzione di conversione buono 18app in codice sconto applicato a carrello nel caso il valore del buono non sia inferiore al totale a carrello
* Implementazione: Interfaccia migliorata. 

= 1.0.3 =
Data di rilascio: 10 Febbraio, 2020

* Correzione bug: Categorie impostabili limitate

= 1.0.2 =
Data di rilascio: 30 Ottobre, 2019

* Correzione bug: Categorie prodotti WooCommerce non riconosciute e conseguente impossibilità di completare l'acquisto.

= 1.0.1 =
Data di rilascio: 02 Ottobre, 2019

* Implementazione: Possibilità di abbinare differenti categorie WooCommeerce allo stesso "bene" 18app.
* Correzione bug: Categorie beni 18app mancanti.

= 1.0.0 =
Data di rilascio: 27 Giugno, 2019

* Correzione bug: SOAP-ERROR: Parsing WSDL: Couldn't load from .../wp-content/plugins/wc-18app/includes/VerificaVoucher.wsdl' : failed to load external entity .../wp-content/plugins/wc-18app/includes/VerificaVoucher.wsdl

= 0.9.1 =
Data di rilascio: 8 Novembre, 2018

* Implementazione: Possibilità di abbinare differenti "beni" 18app alla stessa categoria WooCommeerce.
* Implementazione: Aggiornata gamma "beni" disponibili.

= 0.9.0 =
Data di rilascio: 16 Ottobre, 2018

* Prima release.


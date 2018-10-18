=== WooCommerce Carta Docente - Premium ===
Contributors: ghera74
Tags: Woocommerce, e-commerce, shop, orders, payment, payment gateway, payment method, 
Version: 0.9.4
Requires at least: 4.0
Tested up to: 4.9

Abilita in WooCommerce il pagamento con Carta del Docente.

== Description ==

Il plugin consente di abilitare sul proprio store il pagamento con Carta del Docente.
In fase di checkout, il buono inserito dall'utente verrà verificato per validità, credito disponibile e pertinenza in termini di tipologia di prodotto.


= Nore importanti =
Il plugin prevede l'invio di contenuti ad un servizio esterno, in particolare i dati relativi ai prodotti acquistati dall'utente come categoria d'appartenenza e prezzo.

= Indirizzo di destinazione =
[https://ws.cartadeldocente.istruzione.it/VerificaVoucherDocWEB/VerificaVoucher](https://ws.cartadeldocente.istruzione.it/VerificaVoucherDocWEB/VerificaVoucher)

= Maggiori informazioni sul servizio Carta del docente: =
[https://cartadeldocente.istruzione.it/](https://cartadeldocente.istruzione.it/)

= Informativa privacy del servizio: =
[https://cartadeldocente.istruzione.it/static/cartadeldocente_infoprivacy.pdf](https://cartadeldocente.istruzione.it/static/cartadeldocente_infoprivacy.pdf)


= Important notes =
This plugin sends data to an external service, like the categories and the prices of the products bought by the user.

= Service endpoint: =
[https://ws.cartadeldocente.istruzione.it/VerificaVoucherDocWEB/VerificaVoucher](https://ws.cartadeldocente.istruzione.it/VerificaVoucherDocWEB/VerificaVoucher)

= Service informations: =
[https://cartadeldocente.istruzione.it/](https://cartadeldocente.istruzione.it/)

= Service privacy policy: =
[https://cartadeldocente.istruzione.it/static/cartadeldocente_infoprivacy.pdf](https://cartadeldocente.istruzione.it/static/cartadeldocente_infoprivacy.pdf)


= Funzionalità =

* Caricamento certificato (.pem)
* Impostazione categorie prodotti WooCommerce acquistabili
* Generazione richiesta certificato (.der)
* Generazione certificato (.pem)


== Installation ==

= Dalla Bacheca di Wordpress =

* Vai in  Plugin > Aggiungi nuovo.
* Cerca WooCommerce Carta Docente e scaricalo.
* Attiva Woocommerce Carta Docente dalla pagina dei Plugin.
* Una volta attivato, vai in <strong>WooCommerce/ WC Carta Docente</strong> e imposta le tue preferenze.

= Da WordPress.org =

* Scarica WooCommerce Carta Docente
* Carica la cartella wc-carta-docente su /wp-content/plugins/ utilizzando il tuo metodo preferito (ftp, sftp, scp, ecc...)
* Attiva WooCommerce Carta Docente dalla pagina dei Plugin.
* Una volta attivato, vai in <strong>WooCommerce/ WC Carta Docente</strong> e imposta le tue preferenze.


== Changelog ==

= 0.9.3 =
Data di rilascio: 14 Settembre, 2018

* Correzione bug: Correzione richiamo password utente in class-wccd-soap-client.php

= 0.9.2 =
Data di rilascio: 14 Settembre, 2018

* Correzione bug: Errore nella generazione del file wccd-cert.p12

= 0.9.1 =
Data di rilascio: 27 Agosto, 2018

* Implementazione: Attivazione certificato come richiesto dalla piattaforma Carta del Docente.
* Implementazione: Attivazione del sistema di pagamento solo ad attivazione certificato completata.
* Correzione bug: Errato path certificato in istanza classe wccd_soap_client.

= 0.9.0 =
Data di rilascio: 2 Luglio, 2018

* Prima release.
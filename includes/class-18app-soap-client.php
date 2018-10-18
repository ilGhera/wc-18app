<?php
/**
 * Gestice le chiamate del web service 
 * @author ilGhera
 * @package wc-18app/includes
 * @version 0.9.0
 */
class wc18_soap_client {

    public function __construct($codiceVoucher, $import) {
        $this->wsdl = WC18_PRIVATE_URI . 'VerificaVoucher.wsdl';
        $this->local_cert = WC18_PRIVATE . $this->get_local_cert();
        $this->location = 'https://ws.18app.italia.it/VerificaVoucherWEB/VerificaVoucher';
        $this->codiceVoucher = $codiceVoucher;
        $this->import = $import;
        $this->passphrase = $this->get_user_passphrase(); 
    }


    /**
     * Restituisce il nome del certificato presente nella cartella "Private"
     * @return string
     */
    public function get_local_cert() {
        $cert = wc18_admin::get_the_file('.pem');
        if($cert) {
            return esc_html(basename($cert));
        }
    }


    /**
     * Restituisce la password memorizzata dall'utente nella compilazione del form
     * @return string
     */
    public function get_user_passphrase() {
        return base64_decode(get_option('wc18-password'));
    }


    /**
     * Istanzia il SoapClient
     */
    public function soap_client() {
        $soapClient = new SoapClient(
            $this->wsdl, 
            array(
              'local_cert'  => $this->local_cert,
              'location'    => $this->location,
              'passphrase'  => $this->get_user_passphrase()
            )
        );
  
        return $soapClient;
    }


    /**
     * Chiamata Check di tipo 1 e 2
     * @param  integer $value il tipo di operazione da eseguire
     * 1 per solo controllo
     * 2 per scalare direttamente il valore del buono
     */
    public function check($value = 1) {
        $check = $this->soap_client()->Check(array(
            'checkReq' => array(
                'tipoOperazione' => $value,
                'codiceVoucher'  => $this->codiceVoucher
            )
        ));

        return $check;
    }


    /**
     * Chiamata Confirm utile ad utilizzare solo parte del valore del buono
     */
    public function confirm() {
        $confirm = $this->soap_client()->Confirm(array(
            'checkReq' => array(
                'tipoOperazione' => '1',
                'codiceVoucher'  => $this->codiceVoucher,
                'importo'=> $this->import
            )
        ));

        return $confirm;
    }

}

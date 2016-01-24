<?php

/**
 * The MIT License (MIT)
 * Copyright © 2013 Angel Cruz <me@abr4xas.org>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the “Software”), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author Angel Cruz <me@abr4xas.org>
*/


class Instapago
{

    protected 	$keyId;
    protected 	$publicKeyId;
    public 		$CardHolder;
    public 		$CardHolderId;
    public 		$CardNumber;
    public 		$CVC;
    public 		$ExpirationDate;
    public 		$Amount;
    public 		$Description;
    public 		$StatusId;


    public function __construct ($keyId,$publicKeyId)
    {

        try {
            if (empty($keyId) && empty($publicKeyId)) {
                throw new Exception('Los parámetros "keyId" y "publicKeyId" son requeridos para procesar la petición.');
            }elseif (empty($keyId)) {
                throw new Exception('El parámetro "keyId" es requerido para procesar la petición. sss');
            }else{
                $this->keyId = $keyId;
            }
            if (empty($publicKeyId)) {
                throw new Exception('El parámetro "publicKeyId" es requerido para procesar la petición.');
            }else{
                $this->publicKeyId = $publicKeyId;
            }
        } catch (Exception $e) {
            echo '<pre>Message: ' . $e->getMessage() . '</pre>';
        } // end try/catch

    } // end construct
    // https://www.chriswiegman.com/2014/05/getting-correct-ip-address-php/
    public function get_ip()
    {
        if (function_exists( 'apache_request_headers')) {
            $headers = apache_request_headers();
        } else {
            $headers = $_SERVER;
        }
        if (array_key_exists('X-Forwarded-For', $headers) && filter_var($headers['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $the_ip = $headers['X-Forwarded-For'];
        }elseif (array_key_exists( 'HTTP_X_FORWARDED_FOR', $headers) && filter_var($headers['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $the_ip = $headers['HTTP_X_FORWARDED_FOR'];
        }else {
            $the_ip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        }
        return $the_ip;
    } // end get_ip

    public function payment($Amount,$Description,$CardHolder,$CardHolderId,$CardNumber,$CVC,$ExpirationDate,$StatusId)
    {
        try {
            if (empty($Amount) && empty($Description) &&
                empty($CardHolder) && empty($CardHolderId) &&
                empty($CardNumber) && empty($CVC) &&
                empty($ExpirationDate) && empty($StatusId)) {
                throw new Exception('Parámetros faltantes para procesar el pago. Verifique la documentación.');
            }

            $url = 'https://api.instapago.com/payment'; // endpoint

            $this->Amount 		= $Amount;
            $this->Description 	= $Description;
            $this->CardHolder 	= $CardHolder;
            $this->CardHolderId = $CardHolderId;
            $this->CardNumber 	= $CardNumber;
            $this->CVC 			= $CVC;
            $this->ExpirationDate = $ExpirationDate;
            $this->StatusId		= $StatusId;

            $url = 'https://api.instapago.com/payment';
            $fields = [
                "KeyID"             => $this->keyId, //required
                "PublicKeyId"       => $this->publicKeyId, //required
                "Amount"            => $this->Amount, //required
                "Description"       => $this->Description, //required
                "CardHolder"        => $this->CardHolder, //required
                "CardHolderId"      => $this->CardHolderId, //required
                "CardNumber"        => $this->CardNumber, //required
                "CVC"               => $this->CVC, //required
                "ExpirationDate"    => $this->ExpirationDate, //required
                "StatusId"          => $this->StatusId, //required
                "IP"                => $this->get_ip() //required
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$url );
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($fields));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $server_output = curl_exec ($ch);
            curl_close ($ch);
            $obj = json_decode($server_output);
            $code = $obj->code;

            if ($code == 400) {
                throw new Exception('Error al validar los datos enviados.');
            }elseif ($code == 401) {
                throw new Exception('Error de autenticación, ha ocurrido un error con las llaves utilizadas.');
            }elseif ($code == 403) {
                throw new Exception('Pago Rechazado por el banco.');
            }elseif ($code == 500) {
                throw new Exception('Ha Ocurrido un error interno dentro del servidor.');
            }elseif ($code == 503) {
                throw new Exception('Ha Ocurrido un error al procesar los parámetros de entrada. Revise los datos enviados y vuelva a intentarlo.');
            }elseif ($code == 201) {
                $msg_banco  = $obj->message;
                $voucher  = $obj->voucher;
                $voucher = html_entity_decode($voucher);
                $id_pago  = $obj->id;
                $reference  = $obj->reference;
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        } // end try/catch

        return array(
            'msg_banco' => $msg_banco,
            'voucher' 	=> $voucher,
            'id_pago'	=> $id_pago,
            'reference' => $reference
        );

    } // end payment

    public function continuePayment()
    {
        # code...
    } // continuePayment

    public function cancelPayment()
    {
        # code...
    } // cancelPayment

    public function paymentInfo() // este es el unico que sera GET
    {
        # code...
    } // paymentInfo

    public function process()
    {
        # code...
    } // process

} // end class
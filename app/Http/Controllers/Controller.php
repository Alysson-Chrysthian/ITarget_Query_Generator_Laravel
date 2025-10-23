<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public $evento = "";

    protected function addQuotesWhenNotNull($value)
    {
        if ($value != "null")
            $value = "'" . $value . "'";

        return $value;
    }

    public function generateHistoricoQuery($xmlObject, $cnpj)
    {
        $idevento = $this->addQuotesWhenNotNull($xmlObject->retornoProcessamentoDownload->evento->eSocial->evtAdmissao->attributes()['Id'] ?? "null");
        if ($this->evento == 'S1200')
            $idevento = $this->addQuotesWhenNotNull($xmlObject->retornoProcessamentoDownload->evento->eSocial->evtRemun->attributes()['Id'] ?? "null");
        $protocolo = $this->addQuotesWhenNotNull($xmlObject->retornoProcessamentoDownload->recibo->eSocial->retornoEvento->recepcao->protocoloEnvioLote ?? "null");
        $cnpj = "'" . str_replace(['.', '-', '/'], '', $cnpj) . "'";
        $nr_recibo = $this->addQuotesWhenNotNull($xmlObject->retornoProcessamentoDownload->recibo->eSocial->retornoEvento->recibo->nrRecibo ?? "null");
        
        //CAMPOS FIXOS
        $evento = "'". $this->evento ."'";
        $status = "'"."P"."'";
        $criado_por = 1;
        $alterado_por = 1;
        $message = "'201 - Lote processado com sucesso.  - '";

        $insertQuery = "INSERT INTO esocial.historico (idevento, evento, status, criado_por, alterado_por, message, protocolo, cnpj, nr_recibo)\n"
            . "VALUES ($idevento, $evento, $status, $criado_por, $alterado_por, $message, $protocolo, $cnpj, $nr_recibo);";
        $updateQuery = "UPDATE esocial.historico h SET evento_id = s.id FROM esocial.". strtolower($this->evento) ." s WHERE h.evento = '{$this->evento}' AND h.idevento = s.idevento;";
        
        $query = $insertQuery . " " . $updateQuery;

        return $query;
    }
    
}

<?php

namespace App\Http\Controllers;

use App\Rules\Cnpj;
use Illuminate\Http\Request;

class s1200Controller extends Controller
{
    public $evento = 'S1200';

    public function generateQuery(Request $request)
    {
        $request->validate([
            'xmls' => ['required', 'array'],
            'xmls.*' => ['required', 'file'],
            'cnpj' => ['required', new Cnpj],
        ]);

        foreach ($request->file('xmls') as $xml) {
            $xmlString = file_get_contents($xml->getRealPath());
            $xmlObject = simplexml_load_string($xmlString);

            $s1200Query = $this->generateS1200Query($xmlObject);
            $historicoQuery = $this->generateHistoricoQuery($xmlObject, $request->cnpj);
            $s1200DmDevQuery = null;

            if ($xmlObject->retornoProcessamentoDownload->evento->eSocial->evtRemun->dmDev)
                $s1200DmDevQuery = $this->generateS1200DmDevQuery($xmlObject);
        }
    }

    public function generateS1200Query($xmlObject)
    {   
        $evento = $xmlObject->retornoProcessamentoDownload->evento->eSocial->evtRemun->ideEvento;
        $empregador = $xmlObject->retornoProcessamentoDownload->evento->eSocial->evtRemun->ideEmpregador;

        $idevento = $this->addQuotesWhenNotNull($xmlObject->retornoProcessamentoDownload->evento->eSocial->evtRemun->attributes()['Id'] ?? "null");        
        $indretif = $this->addQuotesWhenNotNull($evento->indRetif ?? "null");
        $nrrecibo = "null";
        if ($indretif == 2)
            $nrrecibo = $this->addQuotesWhenNotNull($xmlObject->retornoProcessamentoDownload->recibo->eSocial->retornoEvento->recibo->nrRecibo ?? "null");
        $indapuracao = $this->addQuotesWhenNotNull($evento->indApuracao ?? "null");
        $perapur = $this->addQuotesWhenNotNull($evento->perApur ?? "null");
        $indguia = null;
        $tpamb = $this->addQuotesWhenNotNull($evento->tbAmb ?? "null");
        $procemi = $this->addQuotesWhenNotNull($evento->procEmi ?? "null");
        $verproc = $this->addQuotesWhenNotNull($evento->verProc ?? "null");
        $tpinsc = $this->addQuotesWhenNotNull($empregador->tpInsc ?? "null");
        $nrinsc = $this->addQuotesWhenNotNull($empregador->nrInsc ?? "null");
        $cpftrab = $this->addQuotesWhenNotNull($xmlObject->retornoProcessamentoDownload->evento->eSocial->evtRemun->ideTrabalhador->cpfTrab ?? "null");
        
        //CAMPOS NULOS
        $nmtrab = "null";
        $dtnascto = "null";
        $tpinscsucessaovinc = "null";
        $nrinscsucessaovinc = "null";
        $matricantsucessaovinc = "null";
        $dtadmsucessaovinc = "null";
        $observacaosucessaovinc = "null";
    
        //CAMPOS FIXOS
        $situacao = 1;
        $tipo = "'I'";
        $criado_por = 1;
        $alterado_por = 1;

        $query = "INSERT INTO esocial.s1200(idevento, indretif, nrrecibo, indapuracao, perapur, indguia, tpamb, procemi, verproc, tpinsc, nrinsc, cpftrab, nmtrab, dtnascto, tpinscsucessaovinc, nrinscsucessaovinc, matricantsucessaovinc, dtadmsucessaovinc, observacaosucessaovinc, situacao, tipo, criado_por, alterado_por)"
            . "VALUES ($idevento, $indretif, $nrrecibo, $indapuracao, $perapur, $indguia, $tpamb, $procemi, $verproc, $tpinsc, $nrinsc, $cpftrab, $nmtrab, $dtnascto, $tpinscsucessaovinc, $nrinscsucessaovinc, $matricantsucessaovinc, $dtadmsucessaovinc, $observacaosucessaovinc, $situacao, $tipo, $criado_por, $alterado_por)";

        return $query;
    }

    public function generateS1200DmDevQuery($xmlObject)
    {
        //
    }
}

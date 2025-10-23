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
        }
    }

    public function generateS1200Query($xmlObject)
    {
        $idevento = $this->addQuotesWhenNotNull($xmlObject->retornoProcessamentoDownload->evento->eSocial->evtRemun->attributes()['Id'] ?? "null");        
        $indretif = $this->addQuotesWhenNotNull($xmlObject->retornoProcessamentoDownload->evento->eSocial->evtRemun->ideEvento->indRetif ?? "null");
        $nrrecibo = "null";
        if ($indretif == 2)
            $nrrecibo = $this->addQuotesWhenNotNull($xmlObject->retornoProcessamentoDownload->recibo->eSocial->retornoEvento->recibo->nrRecibo ?? "null");
        $indapuracao = null;
        $perapur = null;
        $indguia = null;
        $tpamb = null;
        $procemi = null;
        $verproc = null;
        $tpinsc = null;
        $nrinsc = null;
        $cpftrab = null;
        $nmtrab = null;
        $dtnascto = null;
        $tpinscsucessaovinc = null;
        $nrinscsucessaovinc = null;
        $matricantsucessaovinc = null;
        $dtadmsucessaovinc = null;
        $observacaosucessaovinc = null;
        $situacao = null;
        $tipo = null;
        $criado_por = null;
        $alterado_por = null;
    }

    public function generateS1200DmDevQuery()
    {
        //
    }
}

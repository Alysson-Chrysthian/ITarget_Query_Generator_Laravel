<?php

namespace App\Http\Controllers;

use App\Rules\Cnpj;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

            $queries = $s1200Query . "\n\n" . $historicoQuery . ($s1200DmDevQuery ? "\n\n" . $s1200DmDevQuery : "");
    
            $queriesFileContent = Storage::disk('public')->get('s1200-queries.txt');
            Storage::disk('public')->put('s1200-queries.txt', $queriesFileContent . "\n\n\n" . $queries);
        }

        return back()
            ->with([
                'message' => 'Queries geradas com sucesso em /storage/s1200-queries.txt'
            ]);
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
        $tpamb = $this->addQuotesWhenNotNull($evento->tpAmb ?? "null");
        $procemi = $this->addQuotesWhenNotNull($evento->procEmi ?? "null");
        $verproc = $this->addQuotesWhenNotNull($evento->verProc ?? "null");
        $tpinsc = $this->addQuotesWhenNotNull($empregador->tpInsc ?? "null");
        $nrinsc = $this->addQuotesWhenNotNull($empregador->nrInsc ?? "null");
        $cpftrab = $this->addQuotesWhenNotNull($xmlObject->retornoProcessamentoDownload->evento->eSocial->evtRemun->ideTrabalhador->cpfTrab ?? "null");
        
        //CAMPOS NULOS
        $indguia = "null";
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
            . "VALUES ($idevento, $indretif, $nrrecibo, $indapuracao, $perapur, $indguia, $tpamb, $procemi, $verproc, $tpinsc, $nrinsc, $cpftrab, $nmtrab, $dtnascto, $tpinscsucessaovinc, $nrinscsucessaovinc, $matricantsucessaovinc, $dtadmsucessaovinc, $observacaosucessaovinc, $situacao, $tipo, $criado_por, $alterado_por);";

        return $query;
    }

    public function generateS1200DmDevQuery($xmlObject)
    {
        $dmDev = $xmlObject->retornoProcessamentoDownload->evento->eSocial->evtRemun->dmDev;

        $idevento = $this->addQuotesWhenNotNull($xmlObject->retornoProcessamentoDownload->evento->eSocial->evtRemun->attributes()['Id'] ?? "null");        
        $idedmdev = $this->addQuotesWhenNotNull($dmDev->ideDmDev ?? "null");
        $codcateg = $this->addQuotesWhenNotNull($dmDev->codCateg ?? "null");

        //CAMPOS NULOS
        $codcbo = "null";
        $natatividade = "null";
        $qtddiastrab = "null";
        $indrra = "null";

        //CAMPOS FIXOS
        $criado_por = 1;
        $alterado_por = 1;

        $query = "INSERT INTO esocial.s1200_dmdev (idedmdev, codcateg, codcbo, natatividade, qtddiastrab, s1200_id, criado_por, alterado_por, indrra)"
            . "VALUES ($idedmdev, $codcateg, $codcbo, $natatividade, $qtddiastrab, (SELECT id FROM esocial.s1200 s WHERE s.idevento = $idevento LIMIT 1), $criado_por, $alterado_por, $indrra);";
    
        return $query;
    }
}

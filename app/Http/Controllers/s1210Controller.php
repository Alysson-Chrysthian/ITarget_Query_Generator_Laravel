<?php

namespace App\Http\Controllers;

use App\Rules\Cnpj;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class s1210Controller extends Controller {

    public $evento = 'S1210';

    public function generateQuery(Request $request)
    {
        $request->validate([
            'cpfs' => ['nullable'],
            'cnpj' => ['required', new Cnpj],
            'xmls' => ['required', 'array'], 
            'xmls.*' => ['required', 'file'], 
            'perApur' => ['nullable'],
        ]);

        $cpfs = $request->cpfs;
        
        if (!empty($cpfs)) {
            $cpfs = explode(',', str_replace(' ', '', $request->cpfs));
            if (is_string($cpfs))
                $cpfs = [$cpfs];
        }

        $perapurs = $request->perApur;

        if (!empty($perapurs)) {
            $perapurs = explode(',', str_replace(' ', '', $request->perApur));
            if (is_string($perapurs))
                $perapurs = [$perapurs];
        }

        foreach ($request->file('xmls') as $xml) {
            $xmlString = file_get_contents($xml->getRealPath());
            $xmlObject = simplexml_load_string($xmlString);   

            if (is_array($cpfs) && !in_array($xmlObject->retornoProcessamentoDownload->evento->eSocial->evtPgtos->ideBenef->cpfBenef, $cpfs))
                continue;
            if (is_array($perapurs) && !in_array($xmlObject->retornoProcessamentoDownload->evento->eSocial->evtPgtos->ideEvento->perApur, $perapurs))
                continue;

            $s1210Query = $this->generateS1210Query($xmlObject);
            $historicoQuery = $this->generateHistoricoQuery($xmlObject, $request->cnpj, $this->addQuotesWhenNotNull($xmlObject->retornoProcessamentoDownload->evento->eSocial->evtPgtos->attributes()['Id'] ?? "null"));
            
            $s1210InfoDepQuery = null;
           
            if ($xmlObject->retornoProcessamentoDownload->evento->eSocial->evtPgtos->ideBenef->infoIRComplem->infoDep) 
                 $s1210InfoDepQuery = $this->genearteS1210InfoDepQuery($xmlObject);

            $queries = $s1210Query . "\n\n" . $historicoQuery . ($s1210InfoDepQuery ? "\n\n" . $s1210InfoDepQuery : "");

            $queriesFileContent = Storage::disk('public')->get('s1210-queries.txt');
            Storage::disk('public')->put('s1210-queries.txt', $queriesFileContent . "\n\n\n" . $queries);
        }
    
        return 'Queries geradas com sucesso em storage/s1210-queries.txt';
    }

    public function generateS1210Query($xmlObject)
    {
        $ideevento = $xmlObject->retornoProcessamentoDownload->evento->eSocial->evtPgtos->ideEvento;
        $recibo = $xmlObject->retornoProcessamentoDownload->recibo->eSocial->retornoEvento->recibo;
        $empregador = $xmlObject->retornoProcessamentoDownload->evento->eSocial->evtPgtos->ideEmpregador;

        $idevento = $this->addQuotesWhenNotNull($xmlObject->retornoProcessamentoDownload->evento->eSocial->evtPgtos->attributes()['Id'] ?? "null");
        $indretif = $this->addQuotesWhenNotNull($ideevento->indRetif ?? "null");
        $nrrecibo = "null";
        if ($indretif == "'2'")
            $nrrecibo = $this->addQuotesWhenNotNull($recibo->nrRecibo ?? "null");
        $tpamb = $this->addQuotesWhenNotNull($ideevento->tpAmb ?? "null");
        $procemi = $this->addQuotesWhenNotNull($ideevento->procEmi ?? "null");
        $verproc = $this->addQuotesWhenNotNull($ideevento->verProc ?? "null");
        $tpinsc = $this->addQuotesWhenNotNull($empregador->tpInsc ?? "null");
        $nrinsc = $this->addQuotesWhenNotNull($empregador->nrInsc ?? "null");
        $cpfbenef = $this->addQuotesWhenNotNull($xmlObject->retornoProcessamentoDownload->evento->eSocial->evtPgtos->ideBenef->cpfBenef ?? "null");
        $perapur = $this->addQuotesWhenNotNull($ideevento->perApur ?? "null");
    
        //CAMPOS NULOS
        $indguia = "null";
        $dtlaudo = "null";

        //CAMPOS FIXOS
        $situacao = 1;
        $tipo = "'I'";
        $criado_por = 1;
        $alterado_por = 1;

        $query = "INSERT INTO esocial.s1210 (idevento, indretif, nrrecibo, perapur, indguia, tpamb, procemi, verproc, tpinsc, nrinsc, cpfbenef, situacao, tipo, criado_por, alterado_por, dtlaudo)"
            . "VALUES ($idevento, $indretif, $nrrecibo, $perapur, $indguia, $tpamb, $procemi, $verproc, $tpinsc, $nrinsc, $cpfbenef, $situacao, $tipo, $criado_por, $alterado_por, $dtlaudo);";
        
        return $query;
    }

    public function genearteS1210InfoDepQuery($xmlObject)
    {
        $dependente = $xmlObject->retornoProcessamentoDownload->evento->eSocial->evtPgtos->ideBenef->infoIRComplem->infoDep;
        $idevento = $this->addQuotesWhenNotNull($xmlObject->retornoProcessamentoDownload->evento->eSocial->evtPgtos->attributes()['Id'] ?? "null");
        
        $query = "";
        $s1210IdQuery = "(SELECT id FROM esocial.s1210 s WHERE s.idevento = $idevento LIMIT 1)";

        if (is_array($dependente)) {
            foreach ($dependente as $dep) {
                $cpfdep = $this->addQuotesWhenNotNull($dep->cpfDep ?? "null");
                $dtnascto = $this->addQuotesWhenNotNull($dep->dtNascto ?? "null");
                $nome = $this->addQuotesWhenNotNull($dep->nome ?? "null");
                $depirrf = $this->addQuotesWhenNotNull($dep->depIRRF ?? "null");
                $tpdep = $this->addQuotesWhenNotNull($dep->tpDep ?? "null");
                $descrdep = "null";
                $criado_por = 1; 
                $alterado_por = 1;

                $query .= "INSERT INTO esocial.s1210_infodep (cpfdep, dtnascto, nome, depirrf, tpdep, descrdep, s1210_id, criado_por, alterado_por)"
                    . "VALUES ($cpfdep, $dtnascto, $nome, $depirrf, $tpdep, $descrdep, $s1210IdQuery, $criado_por, $alterado_por);";
            }
        } else {    
            $cpfdep = $this->addQuotesWhenNotNull($dependente->cpfDep ?? "null");
            $dtnascto = $this->addQuotesWhenNotNull($dependente->dtNascto ?? "null");
            $nome = $this->addQuotesWhenNotNull($dependente->nome ?? "null");
            $depirrf = $this->addQuotesWhenNotNull($dependente->depIRRF ?? "null");
            $tpdep = $this->addQuotesWhenNotNull($dependente->tpDep ?? "null");
            $descrdep = "null";
            $criado_por = 1; 
            $alterado_por = 1;

            $query = "INSERT INTO esocial.s1210_infodep (cpfdep, dtnascto, nome, depirrf, tpdep, descrdep, s1210_id, criado_por, alterado_por)"
                . "VALUES ($cpfdep, $dtnascto, $nome, $depirrf, $tpdep, $descrdep, $s1210IdQuery, $criado_por, $alterado_por);";
        }

        return $query;
    }
}

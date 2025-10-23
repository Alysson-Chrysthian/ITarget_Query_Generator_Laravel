<?php

namespace App\Http\Controllers;

use App\Rules\Cnpj;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class s2200Controller extends Controller
{
    public $evento = 'S2200';

    public function generateQuery(Request $request)
    {
        $request->validate([
            'cpfs' => ['nullable'],
            'cnpj' => ['required', new Cnpj],
            'xmls' => ['required', 'array'], 
            'xmls.*' => ['required', 'file'], 
        ]);

        $cpfs = $request->cpfs;

        if (!empty($cpfs))
            $cpfs = explode(',', str_replace(' ', '', $request->cpfs));

        foreach ($request->file('xmls') as $xml) {
            $xmlString = file_get_contents($xml->getRealPath());
            $xmlObject = simplexml_load_string($xmlString);   

            if (is_array($cpfs) && !in_array($xmlObject->retornoProcessamentoDownload->evento->eSocial->evtAdmissao->trabalhador->cpfTrab, $cpfs))
                continue;

            $s2200Query = $this->generateS2200Query($xmlObject);
            $historicoQuery = $this->generateHistoricoQuery($xmlObject, $request->cnpj);
            $s2200DependenteQuery = null;

            if ($xmlObject->retornoProcessamentoDownload->evento->eSocial->evtAdmissao->trabalhador->dependente)
                $s2200DependenteQuery = $this->generateS2200DependenteQuery($xmlObject);

            $queries = $s2200Query . "\n\n" . $historicoQuery . ($s2200DependenteQuery ? "\n\n" . $s2200DependenteQuery : "");
            
            $queriesFileContent = Storage::disk('public')->get('s2200-queries.txt');
            Storage::disk('public')->put('s2200-queries.txt', $queriesFileContent . "\n\n\n" . $queries);
        }

        return back()
            ->with([
                'message' => 'Queries geradas com sucesso em storage/s2200-queries.txt'
            ]);
    }

    public function generateS2200Query($xmlObject)
    {
        $ideevento = $xmlObject->retornoProcessamentoDownload->evento->eSocial->evtAdmissao->ideEvento;
        $ideempregador = $xmlObject->retornoProcessamentoDownload->evento->eSocial->evtAdmissao->ideEmpregador;
        $trabalhador = $xmlObject->retornoProcessamentoDownload->evento->eSocial->evtAdmissao->trabalhador;
        $vinculo = $xmlObject->retornoProcessamentoDownload->evento->eSocial->evtAdmissao->vinculo;

        // table cols
        $idevento = $this->addQuotesWhenNotNull($xmlObject->retornoProcessamentoDownload->evento->eSocial->evtAdmissao->attributes()['Id'] ?? "null");
        $indretif = $this->addQuotesWhenNotNull($ideevento->indRetif ?? "null");
        $nrrecibo = "null";
        if ($indretif == 2)
            $nrrecibo = $this->addQuotesWhenNotNull($xmlObject->retornoProcessamentoDownload->recibo->eSocial->retornoEvento->recibo->nrRecibo ?? "null");
        $tpamb = $this->addQuotesWhenNotNull($ideevento->tpAmb ?? "null");
        $procemi = $this->addQuotesWhenNotNull($ideevento->procEmi ?? "null");
        $verproc = $this->addQuotesWhenNotNull($ideevento->verProc ?? "null");
        $tpinsc = $this->addQuotesWhenNotNull($ideempregador->tpInsc ?? "null");
        $nrinsc = $this->addQuotesWhenNotNull($ideempregador->nrInsc ?? "null");
        $cpftrab = $this->addQuotesWhenNotNull($trabalhador->cpfTrab ?? "null");
        $nmtrab = $this->addQuotesWhenNotNull($trabalhador->nmTrab ?? "null");
        $sexo = $this->addQuotesWhenNotNull($trabalhador->sexo ?? "null");
        $racacor = $this->addQuotesWhenNotNull($trabalhador->racaCor ?? "null");
        $estciv = $this->addQuotesWhenNotNull($trabalhador->estCiv ?? "null");
        $grauinstr = $this->addQuotesWhenNotNull($trabalhador->grauInstr ?? "null");
        $dtnascto = $this->addQuotesWhenNotNull($trabalhador->nascimento->dtNascto ?? "null");
        $paisnascto = $this->addQuotesWhenNotNull($trabalhador->nascimento->paisNascto ?? "null");
        $paisnac = $this->addQuotesWhenNotNull($trabalhador->nascimento->paisNac ?? "null");
        $tplograd = $this->addQuotesWhenNotNull($trabalhador->endereco->brasil->tpLograd ?? "null");
        $dsclograd = $this->addQuotesWhenNotNull($trabalhador->endereco->brasil->dscLograd ?? "null");
        $nrlograd = $this->addQuotesWhenNotNull($trabalhador->endereco->brasil->nrLograd ?? "null");
        $bairro = $this->addQuotesWhenNotNull($trabalhador->endereco->brasil->bairro ?? "null");
        $cep = $this->addQuotesWhenNotNull($trabalhador->endereco->brasil->cep ?? "null");
        $codmunic = $this->addQuotesWhenNotNull($trabalhador->endereco->brasil->codMunic ?? "null");
        $uf = $this->addQuotesWhenNotNull($trabalhador->endereco->brasil->uf ?? "null");
        $deffisica = $this->addQuotesWhenNotNull($trabalhador->infoDeficiencia->defFisica ?? "null"); 
        $defvisual = $this->addQuotesWhenNotNull($trabalhador->infoDeficiencia->defVisual ?? "null");
        $defauditiva = $this->addQuotesWhenNotNull($trabalhador->infoDeficiencia->defAuditiva ?? "null");
        $defmental = $this->addQuotesWhenNotNull($trabalhador->infoDeficiencia->defMental ?? "null");
        $defintelectual = $this->addQuotesWhenNotNull($trabalhador->infoDeficiencia->defIntelectual ?? "null");
        $reabreadap = $this->addQuotesWhenNotNull($trabalhador->infoDeficiencia->reabReadap ?? "null");
        $matricula = $this->addQuotesWhenNotNull($vinculo->matricula ?? "null");
        $tpregtrab = $this->addQuotesWhenNotNull($vinculo->tpRegTrab ?? "null");
        $tpregprev = $this->addQuotesWhenNotNull($vinculo->tpRegPrev ?? "null");
        $cadini = $this->addQuotesWhenNotNull($vinculo->cadIni ?? "null");
        $tpprov = $this->addQuotesWhenNotNull($vinculo->infoRegimeTrab->infoEstatutario->tpProv ?? "null");
        $dtexercicio = $this->addQuotesWhenNotNull($vinculo->infoRegimeTrab->infoEstatutario->dtExercicio ?? "null");
        $nmcargo = $this->addQuotesWhenNotNull($vinculo->infoContrato->nmCargo ?? "null");
        $cbocargo = $this->addQuotesWhenNotNull($vinculo->infoContrato->CBOCargo ?? "null");
        $nmfuncao = $this->addQuotesWhenNotNull($vinculo->infoContrato->nmFuncao ?? "null");
        $cbofuncao = $this->addQuotesWhenNotNull($vinculo->infoContrato->CBOFuncao ?? "null");
        $acumcargo = $this->addQuotesWhenNotNull($vinculo->infoContrato->acumCargo ?? "null");
        $codcateg = $this->addQuotesWhenNotNull($vinculo->infoContrato->codCateg ?? "null");
        $tpinsc_localtrabgeral = $this->addQuotesWhenNotNull($vinculo->infoContrato->localTrabalho->localTrabGeral->tpInsc ?? "null");
        $nrinsc_localtrabgeral = $this->addQuotesWhenNotNull($vinculo->infoContrato->localTrabalho->localTrabGeral->nrInsc ?? "null");

        $complemento = "null";
        $nmsoc = "null";
        $tmpresid = "null";
        $conding = "null";
        $infocota = "null";
        $observacao_infodeficiencia = "null";
        $foneprinc = "null";
        $emailprinc = "null";
        $dtadm = "null";
        $tpadmissao = "null";
        $indadmissao = "null";
        $nrproctrab = "null";
        $tpregjor = "null";
        $natatividade = "null";
        $dtbase = "null";
        $cnpjsindcategprof = "null";
        $dtopcfgts = "null";
        $hipleg = "null";
        $justcontr = "null";
        $tpinsc_ideestabvinc = "null";
        $nrinsc_ideestabvinc = "null";
        $tpinsc_aprend = "null";
        $nrinsc_aprend = "null";
        $tpplanrp = "null";
        $indtetorgps = "null";
        $indabonoperm = "null";
        $dtiniabono = "null";
        $dtingrcargo = "null";
        $vrsalfx = "null";
        $undsalfixo = "null";
        $dscsalvar = "null";
        $tpcontr = "null";
        $dtterm = "null";
        $clauassec = "null";
        $objdet = "null";
        $desccomp_localtrabgeral = "null";
        $tplograd_localtempdom  = "null";
        $dsclograd_localtempdom = "null";
        $nrlograd_localtempdom = "null";
        $complemento_localtempdom = "null";
        $bairro_localtempdom = "null";
        $cep_localtempdom = "null";
        $codmunic_localtempdom = "null";
        $uf_localtempdom = "null";
        $qtdhrssem = "null";
        $tpjornada = "null";
        $tmpparc = "null";
        $hornoturno = "null";
        $dscjorn = "null";
        $nrprocjud = "null";
        $tpinsc_sucessaovinc = "null";
        $nrinsc_sucessaovinc = "null";
        $matricant_sucessaovinc = "null";
        $dttransf_sucessaovinc = "null";
        $observacao_sucessaovinc = "null";
        $cpfant = "null";
        $matricant = "null";
        $dtaltcpf = "null";
        $observacao_mudancacpf = "null";
        $dtiniafast = "null";
        $codmotafast = "null";
        $dtdeslig = "null";
        $dtinicessao = "null";
        $matanotjud = "null";
        $indaprend = "null";
        $cnpjentqual = "null";
        $cnpjprat = "null";

        //CAMPOS FIXOS
        $situacao = 1;
        $tipo = "'I'";
        $criado_por = 1;
        $alterado_por = 1;

        $query = "INSERT INTO esocial.s2200 (idevento, indretif, nrrecibo, tpamb, procemi, verproc, tpinsc, nrinsc, cpftrab, nmtrab, sexo, racacor, estciv, grauinstr, nmsoc, dtnascto, paisnascto, paisnac, tplograd, dsclograd, nrlograd, complemento, bairro, cep, codmunic, uf, tmpresid, conding, deffisica, defvisual, defauditiva, defmental, defintelectual, reabreadap, infocota, observacao_infodeficiencia, foneprinc, emailprinc, matricula, tpregtrab, tpregprev, cadini, dtadm, tpadmissao, indadmissao, nrproctrab, tpregjor, natatividade, dtbase, cnpjsindcategprof, dtopcfgts, hipleg, justcontr, tpinsc_ideestabvinc, nrinsc_ideestabvinc, tpinsc_aprend, nrinsc_aprend, tpprov, dtexercicio, tpplanrp, indtetorgps, indabonoperm, dtiniabono, nmcargo, cbocargo, dtingrcargo, nmfuncao, cbofuncao, acumcargo, codcateg, vrsalfx, undsalfixo, dscsalvar, tpcontr, dtterm, clauassec, objdet, tpinsc_localtrabgeral, nrinsc_localtrabgeral, desccomp_localtrabgeral, tplograd_localtempdom, dsclograd_localtempdom, nrlograd_localtempdom, complemento_localtempdom, bairro_localtempdom, cep_localtempdom, codmunic_localtempdom, uf_localtempdom, qtdhrssem, tpjornada, tmpparc, hornoturno, dscjorn, nrprocjud, tpinsc_sucessaovinc, nrinsc_sucessaovinc, matricant_sucessaovinc, dttransf_sucessaovinc, observacao_sucessaovinc, cpfant, matricant, dtaltcpf, observacao_mudancacpf, dtiniafast, codmotafast, dtdeslig, dtinicessao, situacao, tipo, criado_por, alterado_por, matanotjud, indaprend, cnpjentqual, cnpjprat)\n"
            . "VALUES($idevento, $indretif, $nrrecibo, $tpamb, $procemi, $verproc, $tpinsc, $nrinsc, $cpftrab, $nmtrab, $sexo, $racacor, $estciv, $grauinstr, $nmsoc, $dtnascto, $paisnascto, $paisnac, $tplograd, $dsclograd, $nrlograd, $complemento, $bairro, $cep, $codmunic, $uf, $tmpresid, $conding, $deffisica, $defvisual, $defauditiva, $defmental, $defintelectual, $reabreadap, $infocota, $observacao_infodeficiencia, $foneprinc, $emailprinc, $matricula, $tpregtrab, $tpregprev, $cadini, $dtadm, $tpadmissao, $indadmissao, $nrproctrab, $tpregjor, $natatividade, $dtbase, $cnpjsindcategprof, $dtopcfgts, $hipleg, $justcontr, $tpinsc_ideestabvinc, $nrinsc_ideestabvinc, $tpinsc_aprend, $nrinsc_aprend, $tpprov, $dtexercicio, $tpplanrp, $indtetorgps, $indabonoperm, $dtiniabono, $nmcargo, $cbocargo, $dtingrcargo, $nmfuncao, $cbofuncao, $acumcargo, $codcateg, $vrsalfx, $undsalfixo, $dscsalvar, $tpcontr, $dtterm, $clauassec, $objdet, $tpinsc_localtrabgeral, $nrinsc_localtrabgeral, $desccomp_localtrabgeral, $tplograd_localtempdom, $dsclograd_localtempdom, $nrlograd_localtempdom, $complemento_localtempdom, $bairro_localtempdom, $cep_localtempdom, $codmunic_localtempdom, $uf_localtempdom, $qtdhrssem, $tpjornada, $tmpparc, $hornoturno, $dscjorn, $nrprocjud, $tpinsc_sucessaovinc, $nrinsc_sucessaovinc, $matricant_sucessaovinc, $dttransf_sucessaovinc, $observacao_sucessaovinc, $cpfant, $matricant, $dtaltcpf, $observacao_mudancacpf, $dtiniafast, $codmotafast, $dtdeslig, $dtinicessao, $situacao, $tipo, $criado_por, $alterado_por, $matanotjud, $indaprend, $cnpjentqual, $cnpjprat);";
    
        return $query;
    }

    public function generateS2200DependenteQuery($xmlObject)
    {
        $dependente = $xmlObject->retornoProcessamentoDownload->evento->eSocial->evtAdmissao->trabalhador->dependente;
        $matricula = "'" . $xmlObject->retornoProcessamentoDownload->evento->eSocial->evtAdmissao->vinculo->matricula . "'";


        $tpdep = $this->addQuotesWhenNotNull($dependente->tpDep ?? "null");
        $nmdep = $this->addQuotesWhenNotNull($dependente->nmDep ?? "null");
        $dtnascto = $this->addQuotesWhenNotNull($dependente->dtNascto ?? "null");
        $cpfdep = $this->addQuotesWhenNotNull($dependente->cpfDep ?? "null");
        $sexodep = "null";
        $descrdep = "null";
        $depirrf = $this->addQuotesWhenNotNull($dependente->depIRRF ?? "null");
        $depsf = $this->addQuotesWhenNotNull($dependente->depSF ?? "null");
        $inctrab = $this->addQuotesWhenNotNull($dependente->incTrab ?? "null");
        
        //CAMPOS FIXOS
        $criado_por = 1;
        $alterado_por = 1;
        
        $query = "INSERT INTO esocial.s2200_dependente (tpdep, nmdep, dtnascto, cpfdep, sexodep, depirrf, depsf, inctrab, s2200_id, criado_por, alterado_por, descrdep) "
            . "VALUES($tpdep, $nmdep, $dtnascto, $cpfdep, $sexodep, $depirrf, $depsf, $inctrab, (SELECT id FROM esocial.s2200 s WHERE s.matricula = $matricula), $criado_por, $alterado_por, $descrdep);";
    
        return $query;
    }
}

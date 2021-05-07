<?php
class archivi_helpers_TruncationUtilityForTree
{
    //Controllo sulla lunghezza del titolo, il quale viene troncato se maggiore di 83 caratteri
    function truncateDenominazioneDoc($denominazione)
    {
        if (strlen($denominazione) > 83) {
            $denominazione = mb_substr($denominazione, 0, 80) . '...';
        }
        return $denominazione;
    }

    //Controllo sulla cronologia per mostrare soltanto uno dei due estremi quando questi concidono
    function truncateCronologiaDoc($cronologia)
    {
        $cronologiaArray = [];
        if (substr_count($cronologia, ', ') === 1) {
            $cronTemp = explode(', ', $cronologia);
            $cronologia = end($cronTemp);
        }
        $cronologiaArray = explode(' - ', $cronologia);
        if (count($cronologiaArray) === 2 && $cronologiaArray[0] === $cronologiaArray[1]) {
            $cronologia = $cronologiaArray[0];
        }

        return $cronologia;
    }

    //Controllo sulla lunghezza del titolo, il quale viene troncato se maggiore di 83 caratteri
    function truncateDenominazioneAr($denominazione, $_denominazione)
    {
        if (strlen($denominazione) > 83) {
            $denominazioneTruncated = mb_substr($denominazione, 0, 80) . '...';
            $_denominazione = str_replace($denominazione, $denominazioneTruncated, $_denominazione);
        }
        return $_denominazione;
    }

    //Controllo sulla cronologia per mostrare soltanto uno dei due estremi quando questi concidono

    function truncateCronologiaAr($cronologia, $_denominazione)
    {
        if (
            is_array($cronologia) && count($cronologia) > 0
            && property_exists($cronologia[0], 'estremoRemoto_data')
            && property_exists($cronologia[0], 'estremoRecente_data')
        ) {
            if ($cronologia[0]->estremoCronologicoTestuale !== '' && $cronologia[0]->estremoRemoto_data === $cronologia[0]->estremoRecente_data) {
                $estremoTestuale = $cronologia[0]->estremoCronologicoTestuale;
                $estremoTestualeMod = $cronologia[0]->estremoRemoto_data;
                $pos = strrpos($_denominazione, $estremoTestuale);
                if ($pos !== false) {
                    $_denominazione = substr_replace($_denominazione, $estremoTestualeMod, $pos, strlen($estremoTestuale));
                }
            }
        }
        return $_denominazione;
    }
}

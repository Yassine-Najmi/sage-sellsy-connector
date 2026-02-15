<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class CategoryMappingService
{
    // From your N8N config

    private const SAGE_CATEGORY_NAMES = [
        '4' => 'CONDUIT DOMESTIQUE',
        '6' => 'ACCESSOIRE CHAUDIERE',
        '7' => 'ACCESSOIRE CHEMINEE',
        '8' => 'ACCESSOIRE FOYER',
        '9' => 'ACCESSOIRE POELE',
        '10' => 'AIR CHAUD/FRAIS',
        '11' => 'CADRE METAL/PIERRE',
        '12' => 'CHAUDIERE BOIS',
        '13' => 'CHAUDIERE GRANULE',
        '14' => 'CHAUDIERE MIXTE',
        '15' => 'CONDUIT DUALIS',
        '16' => 'CONDUIT DUOGAS',
        '17' => 'CONDUIT EFFICIENCE',
        '18' => 'CONDUIT LISS ISO',
        '19' => 'CONDUIT PGI',
        '20' => 'CONDUIT THERMINOX',
        '21' => 'CUISINIERE',
        '22' => 'DESTOCKAGE',
        '23' => 'POLYPROPYLENE',
        '24' => 'DROGUERIE',
        '25' => 'FOYER BOIS',
        '26' => 'FOYER ELECTRIQUE',
        '27' => 'FOYER GRANULE',
        '28' => 'FOYER HYDRO',
        '29' => 'POELE GRANULE',
        '30' => 'POELE GRANULE THERMO',
        '31' => 'POELE HYDROGENE',
        '32' => 'POELE MIXTE',
        '33' => 'POELE MIXTE HYDRO',
        '34' => 'RAMONAGE CONTROLE',
        '35' => 'SAV',
        '36' => 'SODINOX',
        '37' => 'SOLIN & EMBASE',
        '38' => 'TUBAGE FLEXIBLE',
        '39' => 'TUYAU RACCORDEMENT',
        '40' => 'POELE BOIS',
        '41' => 'POELE BOIS HYDRO',
        '42' => 'ISOLATION',

        '54' => 'VENTILATEUR ACCESSOIRE',
        '59' => 'SILENE',
        '62' => 'ARTENSE',
        '70' => 'ARTENSE',
        '71' => 'SILENE',
        '72' => 'ARTENSE',
        '81' => 'ARTENSE',
        '95' => 'ARTENSE',
        '106' => 'ARTENSE',
        '126' => 'TEN',
        '130' => 'TEN',
        '137' => 'ARTENSE',
        '179' => 'ARTENSE',
        '202' => 'SILENE',
        '211' => 'CONDUIT EFFICIENCE',
        '224' => 'SERVITEURS ET RANGEMENTS',
        '230' => 'TEN',
        '242' => 'TEN PRE-ISOLE',
        '302' => 'ARTENSE',
        '303' => 'VENTELIA',
        '304' => 'TD VENTILATION',
        '316' => 'ENM 150',
        '317' => 'ENM 080',
        '318' => 'ENM 100',
        '336' => 'GRILLE AIR FRAIS INFLUENCE',
        '340' => 'GRILLE AIR CHAUD INFLUENCE',
        '349' => 'TEN NM 150',
        '350' => 'TEN NM 180',
        '351' => 'TEN NM 080',
        '352' => 'TEN NM 100',
        '353' => 'TEN NM 200',
        '354' => 'TEN NM 125',
        '355' => 'TEN NM 139',
        '356' => 'TEN NM 153',
        '369' => 'FUMIGENE',
        '404' => 'VENTILATEUR ZEPHIR',
        '405' => 'VENTILATEUR EXTRA',
        '411' => 'DIVERS',
        '414' => 'AIR FRAIS EFFICIENCE',
        '418' => 'TEST REDESCENTE SPEED',
        '424' => 'CLAPET AIR FRAIS INFLUENCE',
        '430' => 'ENM 130',
        '431' => 'ENM 180',
        '432' => 'ENM 200',
        '438' => 'ENMR',
        '440' => 'ENM 125',
        '441' => 'COLLERETTE RENFORCEE SODINOX',
        '452' => 'KIT AIR FRAIS INFLUENCE',
        '453' => 'KIT AIR FRAIS INFLUENCE FLUX AIR',
        '454' => 'ARTENSE',
        '473' => 'ARTENSE',
        '479' => 'TEN NM 130',
        '493' => 'CHARLTON & JENRICK',
        '494' => 'CHARLTON & JENRICK',
        '495' => 'CHARLTON & JENRICK',
        '496' => 'CHARLTON & JENRICK',
        '497' => 'CHARLTON & JENRICK',
        '500' => 'DUOTEN',
        '503' => 'CHARLTON & JENRICK',
        '512' => 'CHARLTON & JENRICK',
        '513' => 'CHARLTON & JENRICK',
        '537' => 'POELE HYDROGENE',
        '539' => 'TEN',
        '552' => 'CHARLTON & JENRICK',
        '555' => 'CHARLTON & JENRICK',
        '571' => 'TEN SEMI-ISOLE',
        '572' => 'TENFLEX-ISO',
    ];


    private const CATEGORY_TO_SELLSY_ID = [
        'CONDUIT DOMESTIQUE' => '4',
        'ACCESSOIRE CHAUDIERE' => '6',
        'ACCESSOIRE CHEMINEE' => '7',
        'ACCESSOIRE FOYER' => '8',
        'ACCESSOIRE POELE' => '9',
        'AIR CHAUD/FRAIS' => '10',
        'CADRE METAL/PIERRE' => '11',
        'CHAUDIERE BOIS' => '12',
        'CHAUDIERE GRANULE' => '13',
        'CHAUDIERE MIXTE' => '14',
        'CONDUIT DUALIS' => '15',
        'CONDUIT DUOGAS' => '16',
        'CONDUIT EFFICIENCE' => '17',
        'CONDUIT LISS ISO' => '18',
        'CONDUIT PGI' => '19',
        'CONDUIT THERMINOX' => '20',
        'CUISINIERE' => '21',
        'DESTOCKAGE' => '22',
        'DIVERS' => '23',
        'DROGUERIE' => '24',
        'FOYER BOIS' => '25',
        'FOYER ELECTRIQUE' => '26',
        'FOYER GRANULE' => '27',
        'FOYER HYDRO' => '28',
        'POELE GRANULE' => '29',
        'POELE GRANULE THERMO' => '30',
        'POELE HYDROGENE' => '31',
        'POELE MIXTE' => '32',
        'POELE MIXTE HYDRO' => '33',
        'RAMONAGE CONTROLE' => '34',
        'SAV' => '35',
        'SODINOX' => '36',
        'SOLIN & EMBASE' => '37',
        'TUBAGE FLEXIBLE' => '38',
        'TUYAU RACCORDEMENT' => '39',
        'POELE BOIS' => '40',
        'POELE BOIS HYDRO' => '41',
        'ISOLATION' => '42',

        'POLYPROPYLENE' => '44',
        'VENTILATEUR ACCESSOIRE' => '46',
        'SILENE' => '47',
        'ARTENSE' => '48',
        'TEN' => '49',
        'SERVITEURS ET RANGEMENTS' => '51',
        'TEN PRE-ISOLE' => '52',
        'VENTELIA' => '53',
        'TD VENTILATION' => '54',
        'ENM 150' => '55',
        'ENM 080' => '56',
        'ENM 100' => '57',
        'GRILLE AIR FRAIS INFLUENCE' => '58',
        'GRILLE AIR CHAUD INFLUENCE' => '59',
        'TEN NM 150' => '60',
        'TEN NM 180' => '61',
        'TEN NM 080' => '62',
        'TEN NM 100' => '63',
        'TEN NM 200' => '64',
        'TEN NM 125' => '65',
        'TEN NM 139' => '66',
        'TEN NM 153' => '67',
        'FUMIGENE' => '68',
        'VENTILATEUR ZEPHIR' => '69',
        'VENTILATEUR EXTRA' => '70',
        'AIR FRAIS EFFICIENCE' => '71',
        'TEST REDESCENTE SPEED' => '72',
        'CLAPET AIR FRAIS INFLUENCE' => '73',
        'ENM 130' => '74',
        'ENM 180' => '75',
        'ENM 200' => '76',
        'ENMR' => '77',
        'ENM 125' => '78',
        'COLLERETTE RENFORCEE SODINOX' => '79',
        'KIT AIR FRAIS INFLUENCE' => '80',
        'KIT AIR FRAIS INFLUENCE FLUX AIR' => '81',
        'DUOTEN' => '82',
        'CHARLTON & JENRICK' => '83',
        'TEN SEMI-ISOLE' => '85',
        'TENFLEX-ISO' => '86',
    ];


    /**
     * Get Sellsy category ID from Sage product data
     */
    public function getSellsyCategoryId(array $sageData): ?string
    {
        // Get the first category from Sage (CL_No1, CL_No2, etc.)
        $sageCategoryId = $sageData['CL_No1'] ?? null;

        if (!$sageCategoryId) {
            Log::warning('No category found in Sage product', [
                'ar_ref' => $sageData['AR_Ref'] ?? 'unknown',
            ]);
            return null;
        }

        // Get category name from Sage category ID
        $categoryName = self::SAGE_CATEGORY_NAMES[$sageCategoryId] ?? null;

        if (!$categoryName) {
            Log::warning('Unknown Sage category ID', [
                'sage_category_id' => $sageCategoryId,
                'ar_ref' => $sageData['AR_Ref'] ?? 'unknown',
            ]);
            return null;
        }

        // Map to Sellsy category ID
        $sellsyCategoryId = self::CATEGORY_TO_SELLSY_ID[$categoryName] ?? null;

        if (!$sellsyCategoryId) {
            Log::warning('No Sellsy mapping for category', [
                'category_name' => $categoryName,
                'ar_ref' => $sageData['AR_Ref'] ?? 'unknown',
            ]);
        }

        return $sellsyCategoryId;
    }
}

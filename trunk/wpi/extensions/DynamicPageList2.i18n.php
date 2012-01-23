<?php
/**
 * Internationalization file for DynamicPageList2 extension.
 *
 * @package MediaWiki
 * @subpackage Extensions
 * @author m:User:Dangerman <cyril.dangerville@gmail.com>
 * @version 1.0.5
 * @version 1.0.8
 * 			removed blank lines at the end of the file
 * @version 1.0.9
 * 			added message: ERR_OpenReferences
*/

class DPL2_i18n
{
    // FATAL
    const FATAL_WRONGNS                  = 1;
    const FATAL_WRONGLINKSTO             = 2;
    const FATAL_TOOMANYCATS              = 3;
    const FATAL_TOOFEWCATS               = 4;
    const FATAL_NOSELECTION              = 5;
    const FATAL_CATDATEBUTNOINCLUDEDCATS = 6;
    const FATAL_CATDATEBUTMORETHAN1CAT   = 7;
    const FATAL_MORETHAN1TYPEOFDATE      = 8;
    const FATAL_WRONGORDERMETHOD         = 9;
    const FATAL_DOMINANTSECTIONRANGE     = 10;
    const FATAL_NOCLVIEW                 = 11;
    const FATAL_OPENREFERENCES           = 12;

    // ERROR

    // WARN
    const WARN_UNKNOWNPARAM                = 13;
    const WARN_WRONGPARAM                  = 14;
    const WARN_WRONGPARAM_INT              = 15;
    const WARN_NORESULTS                   = 16;
    const WARN_CATOUTPUTBUTWRONGPARAMS     = 17;
    const WARN_HEADINGBUTSIMPLEORDERMETHOD = 18;
    const WARN_DEBUGPARAMNOTFIRST          = 19;
    const WARN_TRANSCLUSIONLOOP            = 20;

    // INFO

    // DEBUG
    const DEBUG_QUERY = 21;

    // TRACE

    private static $messages = array();

    public static function getMessages()
    {
        /**
         * To translate messages into your language, create a self::$messages['lang'] array where 'lang' is your language code and take self::$messages['en'] as a model. Replace values with appropriate translations.
         */

         self::$messages['en'] = array(
            /*
                Log
             */
            // FATAL
            /**
             * $0: 'namespace' or 'notnamespace'
             * $1: wrong parameter given by user
             * $3: list of possible titles of namespaces (except pseudo-namespaces: Media, Special)
             */
            'dpl2_log_' . self::FATAL_WRONGNS => "ERROR: Wrong '$0' parameter: '$1'! Help:  <code>$0= <i>empty string</i> (Main)$3</code>.",
            /**
             * $0: 'linksto' (left as $0 just in case the parameter is renamed in the future)
             * $1: wrong parameter given by user
             */
            'dpl2_log_' . self::FATAL_WRONGLINKSTO => "ERROR: Wrong '$0' parameter: '$1'! Help:  <code>$0= <i>full pagename</i></code>.",
            /**
             * $0: max number of categories that can be included
             */
            'dpl2_log_' . self::FATAL_TOOMANYCATS => 'ERROR: Too many categories! Maximum: $0. Help: increase <code>ExtDynamicPageList2::$maxCategoryCount</code> to specify more categories or set <code>ExtDynamicPageList2::$allowUnlimitedCategories=true</code> for no limitation. (Set the variable in <code>LocalSettings.php</code>, after including <code>DynamicPageList2.php</code>.)',
            /**
             * $0: min number of categories that have to be included
             */
            'dpl2_log_' . self::FATAL_TOOFEWCATS => 'ERROR: Too few categories! Minimum: $0. Help: decrease <code>ExtDynamicPageList2::$minCategoryCount</code> to specify fewer categories. (Set the variable preferably in <code>LocalSettings.php</code>, after including <code>DynamicPageList2.php</code>.)',
            'dpl2_log_' . self::FATAL_NOSELECTION => "ERROR: No selection criteria found! You must use at least one of the following parameters: category, namespace, titlematch, linksto, uses, createdby, modifiedby, lastmodifiedby or their 'not' variants",
            'dpl2_log_' . self::FATAL_CATDATEBUTNOINCLUDEDCATS => "ERROR: You need to include at least one category if you want to use 'addfirstcategorydate=true' or 'ordermethod=categoryadd'!",
            'dpl2_log_' . self::FATAL_CATDATEBUTMORETHAN1CAT => "ERROR: If you include more than one category, you cannot use 'addfirstcategorydate=true' or 'ordermethod=categoryadd'!",
            'dpl2_log_' . self::FATAL_MORETHAN1TYPEOFDATE => 'ERROR: You cannot add more than one type of date at a time!',
            /**
             * $0: param=val that is possible only with $1 as last 'ordermethod' parameter
             * $1: last 'ordermethod' parameter required for $0
             */
            'dpl2_log_' . self::FATAL_WRONGORDERMETHOD => "ERROR: You can use '$0' with 'ordermethod=[...,]$1' only!",
            /**
             * $0: the number of arguments in includepage
             */
            'dpl2_log_' . self::FATAL_DOMINANTSECTIONRANGE => "ERROR: the index for the dominant section must be between 1 and the number of arguments of includepage ($0 in this case)",
            /**
             * $0: prefix_dpl_clview where 'prefix' is the prefix of your mediawiki table names
             * $1: SQL query to create the prefix_dpl_clview on your mediawiki DB
             */
            'dpl2_log_' . self::FATAL_NOCLVIEW => "ERROR: Cannot perform logical operations on the Uncategorized pages (e.g. with the 'category' parameter) because the $0 view does not exist on the database! Help: have the DB admin execute this query: <code>$1</code>.",
            'dpl2_log_' . self::FATAL_OPENREFERENCES => 'ERROR: specifying "openreferences" is incompatible with some other option you specified. See the manual for details.',

            // WARN
            /**
             * $0: unknown parameter given by user
             * $1: list of DPL2 available parameters separated by ', '
             */
            'dpl2_log_' . self::WARN_UNKNOWNPARAM => "WARNING: Unknown parameter '$0' is ignored. Help: available parameters: <code>$1</code>.",
            /**
             * $3: list of valid param values separated by ' | '
             */
            'dpl2_log_' . self::WARN_WRONGPARAM => "WARNING: Wrong '$0' parameter: '$1'! Using default: '$2'. Help: <code>$0= $3</code>.",
            /**
             * $0: param name
             * $1: wrong param value given by user
             * $2: default param value used instead by program
             */
	     //AP20080416 removed 'WARNING' from no results message
            'dpl2_log_' . self::WARN_WRONGPARAM_INT => "WARNING: Wrong '$0' parameter: '$1'! Using default: '$2' (no limit). Help: <code>$0= <i>empty string</i> (no limit) | n</code>, with <code>n</code> a positive integer.",
            'dpl2_log_' . self::WARN_NORESULTS => 'No results!',
            'dpl2_log_' . self::WARN_CATOUTPUTBUTWRONGPARAMS => "WARNING: Add* parameters ('adduser', 'addeditdate', etc.)' and 'includepage' have no effect with 'mode=category'. Only the page namespace/title can be viewed in this mode.",
            /**
             * $0: 'headingmode' value given by user
             * $1: value used instead by program (which means no heading)
             */
            'dpl2_log_' . self::WARN_HEADINGBUTSIMPLEORDERMETHOD => "WARNING: 'headingmode=$0' has no effect with 'ordermethod' on a single component. Using: '$1'. Help: you can use not-$1 'headingmode' values with 'ordermethod' on multiple components. The first component is used for headings. E.g. 'ordermethod=category,<i>comp</i>' (<i>comp</i> is another component) for category headings.",
            /**
             * $0: 'log' value
             */
            'dpl2_log_' . self::WARN_DEBUGPARAMNOTFIRST => "WARNING: 'debug=$0' is not in first position in the DPL element. The new debug settings are not applied before all previous parameters have been parsed and checked.",
            /**
             * $0: title of page that creates an infinite transclusion loop
             */
            'dpl2_log_' . self::WARN_TRANSCLUSIONLOOP => "WARNING: An infinite transclusion loop is created by page '$0'.",

            // DEBUG
            /**
             * $0: SQL query executed to generate the dynamic page list
             */
            'dpl2_log_' . self::DEBUG_QUERY => 'QUERY: <code>$0</code>',

            /*
               Output formatting
             */
            /**
             * $1: number of articles
             */
            'dpl2_articlecount' => 'There {{PLURAL:$1|is one article|are $1 articles}} in this heading.'
        );
        self::$messages['he'] = array(
            /*
                Log
             */
            // FATAL
            /**
             * $0: 'namespace' or 'notnamespace'
             * $1: wrong parameter given by user
             * $3: list of possible titles of namespaces (except pseudo-namespaces: Media, Special)
             */
            'dpl2_log_' . self::FATAL_WRONGNS => "?????: ????? '$0' ????: '$1'! ????: <code>$0= <i>?????? ????</i> (????)$3</code>. (???? ?????? ?? ????? ??? ??????? ????? ???.)",
            /**
             * $0: 'linksto' (left as $0 just in case the parameter is renamed in the future)
             * $1: wrong parameter given by user
             */
            'dpl2_log_' . self::FATAL_WRONGLINKSTO => "?????: ????? '$0' ????: '$1'! ????: <code>$0= <i>?? ??? ????</i></code>. (???? ?????? ?????? ???.)",
            /**
             * $0: max number of categories that can be included
             */
            'dpl2_log_' . self::FATAL_TOOMANYCATS => '?????: ???????? ???? ???! ???????: $0. ????: ???? ?? <code>ExtDynamicPageList2::$maxCategoryCount</code> ??? ????? ??? ???????? ?? ?????? <code>ExtDynamicPageList2::$allowUnlimitedCategories=true</code> ??? ???? ?? ??????. (?????? ?? ?????? ????? <code>LocalSettings.php</code>, ???? ????? <code>DynamicPageList2.php</code>.)',
            /**
             * $0: min number of categories that have to be included
             */
            'dpl2_log_' . self::FATAL_TOOFEWCATS => '?????: ???????? ????? ???! ???????: $0. ????: ?????? ?? <code>ExtDynamicPageList2::$minCategoryCount</code> ??? ????? ???? ????????. (?????? ?? ?????? ????? <code>LocalSettings.php</code>, ???? ????? <code>DynamicPageList2.php</code>.)',
            'dpl2_log_' . self::FATAL_NOSELECTION => "ERROR: No selection criteria found! You must use at least one of the following parameters: category, namespace, titlematch, linksto, uses, createdby, modifiedby, lastmodifiedby or their 'not' variants",
            'dpl2_log_' . self::FATAL_CATDATEBUTNOINCLUDEDCATS => "?????: ????? ?????? ????? ??????? ??? ?? ??????? ?????? ??'addfirstcategorydate=true' ?? ??'ordermethod=categoryadd'!",
            'dpl2_log_' . self::FATAL_CATDATEBUTMORETHAN1CAT => "?????: ?? ??? ??????? ???? ???????? ???, ????? ?????? ?????? ??'addfirstcategorydate=true' ?? ??'ordermethod=categoryadd'!",
            'dpl2_log_' . self::FATAL_MORETHAN1TYPEOFDATE => '?????: ????? ?????? ?????? ???? ???? ??? ?? ????? ?? ?????!',
            /**
             * $0: param=val that is possible only with $1 as last 'ordermethod' parameter
             * $1: last 'ordermethod' parameter required for $0
             */
            'dpl2_log_' . self::FATAL_WRONGORDERMETHOD => "?????: ????????? ?????? ??'$0' ?? 'ordermethod=[...,]$1' ????!",
            /**
             * $0: the number of arguments in includepage
             */
            'dpl2_log_' . self::FATAL_DOMINANTSECTIONRANGE => "ERROR: the index for the dominant section must be between 1 and the number of arguments of includepage ($0 in this case)",
            /**
             * $0: prefix_dpl_clview where 'prefix' is the prefix of your mediawiki table names
             * $1: SQL query to create the prefix_dpl_clview on your mediawiki DB
             */
            'dpl2_log_' . self::FATAL_NOCLVIEW => "?????: ?? ???? ???? ?????? ?????? ?? ???? ??? ???????? (????, ?? ?????? '???????') ????? ?????? $0 ???? ????? ???? ???????! ????: ???? ??? ??????? ???? ????? ?? ???????: <code>$1</code>.",
            'dpl2_log_' . self::FATAL_OPENREFERENCES => 'ERROR: specifying "openreferences" is incompatible with some other option you specified. See the manual for details.',

            // WARN
            /**
             * $0: unknown parameter given by user
             * $1: list of DPL2 available parameters separated by ', '
             */
            'dpl2_log_' . self::WARN_UNKNOWNPARAM => "?????: ????? ??????? ??????? ??? ???? '$0'. ????: ??????? ??????: <code>$1</code>.",
            /**
             * $3: list of valid param values separated by ' | '
             */
            'dpl2_log_' . self::WARN_WRONGPARAM => "?????: ????? '$0' ????: '$1'! ????? ?????? ?????: '$2'. ????: <code>$0= $3</code>.",
            /**
             * $0: param name
             * $1: wrong param value given by user
             * $2: default param value used instead by program
             */
            'dpl2_log_' . self::WARN_WRONGPARAM_INT => "?????: ????? '$0' ????: '$1'! ????? ?????? ?????: '$2' (??? ?????). ????: <code>$0= <i>?????? ????</i> (??? ?????) | n</code>, ?? <code>n</code> ????? ??? ??????.",
            'dpl2_log_' . self::WARN_NORESULTS => '?????: ??? ??????!',
            'dpl2_log_' . self::WARN_CATOUTPUTBUTWRONGPARAMS => "?????: ??????* ???????? ('adduser',? 'addeditdate' ??????) ??? ??'includepage' ??? ????? ?? 'mode=category'. ???? ????? ?? ????? ??? ?? ?????? ??? ???? ??.",
            /**
             * $0: 'headingmode' value given by user
             * $1: value used instead by program (which means no heading)
             */
            'dpl2_log_' . self::WARN_HEADINGBUTSIMPLEORDERMETHOD => "?????: ??'headingmode=$0' ??? ????? ?? 'ordermethod' ?? ???? ????. ????? ?: '$1'. ????: ????????? ?????? ?????? ?? 'headingmode' ????? $1 ?? 'ordermethod' ?? ?????? ??????. ??????? ????? ?????? ??????. ????, 'ordermethod=category,<i>comp</i>' (<i>comp</i> ??? ???? ???) ??????? ????????.",
            /**
             * $0: 'debug' value
             */
            'dpl2_log_' . self::WARN_DEBUGPARAMNOTFIRST => "?????: 'debug=$0w ??? ?? ????? ?????? ????? ??DPL. ?????? ????? ??????? ?????? ?? ????? ???? ??? ???????? ??????? ?????? ???????.",
            /**
             * $0: title of page that creates an infinite transclusion loop
             */
            'dpl2_log_' . self::WARN_TRANSCLUSIONLOOP => "?????: ????? ????? ???????? ????? ??? '$0'.",

            // DEBUG
            /**
             * $0: SQL query executed to generate the dynamic page list
             */
            'dpl2_log_' . self::DEBUG_QUERY => '??????: <code>$0</code>',

            /*
               Output formatting
             */
            /**
             * $1: number of articles
             */
            'dpl2_articlecount' => '{{plural:$1|???? $1 ????|???? ?? ???}} ??? ????? ??.'
        );
        self::$messages['it'] = array(
            /*
                Log
             */
            // FATAL
            /**
             * $0: 'namespace' or 'notnamespace'
             * $1: wrong parameter given by user
             * $3: list of possible titles of namespaces (except pseudo-namespaces: Media, Special)
             */
            'dpl2_log_' . self::FATAL_WRONGNS => "ERRORE nel parametro '$0': '$1'. Suggerimento:  <code>$0= <i>stringa vuota</i> (Principale)$3</code>.",
            /**
             * $0: 'linksto' (left as $0 just in case the parameter is renamed in the future)
             * $1: wrong parameter given by user
             */
            'dpl2_log_' . self::FATAL_WRONGLINKSTO => "ERRORE nel parametro '$0': '$1'. Suggerimento:  <code>$0= <i>nome completo della pagina</i></code>.",
            /**
             * $0: max number of categories that can be included
             */
            'dpl2_log_' . self::FATAL_TOOMANYCATS => 'ERRORE: Categorie sovrabbondanti (massimo $0). Suggerimento: aumentare il valore di <code>ExtDynamicPageList2::$maxCategoryCount</code> per indicare un numero maggiore di categorie, oppure impostare <code>ExtDynamicPageList2::$allowUnlimitedCategories=true</code> per non avere alcun limite. (Impostare le variabili nel file <code>LocalSettings.php</code>, dopo l\'inclusione di <code>DynamicPageList2.php</code>.)',
            /**
             * $0: min number of categories that have to be included
             */
            'dpl2_log_' . self::FATAL_TOOFEWCATS => 'ERRORE: Categorie insufficienti (minimo $0). Suggerimento: diminuire il valore di <code>ExtDynamicPageList2::$minCategoryCount</code> per indicare un numero minore di categorie. (Impostare la variabile nel file <code>LocalSettings.php</code>, dopo l\'inclusione di <code>DynamicPageList2.php</code>.)',
            'dpl2_log_' . self::FATAL_NOSELECTION => "ERROR: No selection criteria found! You must use at least one of the following parameters: category, namespace, titlematch, linksto, uses, createdby, modifiedby, lastmodifiedby or their 'not' variants",
            'dpl2_log_' . self::FATAL_CATDATEBUTNOINCLUDEDCATS => "ERRORE: L'uso dei parametri 'addfirstcategorydate=true' e 'ordermethod=categoryadd' richiede l'inserimento di una o pi\F9 categorie.",
            'dpl2_log_' . self::FATAL_CATDATEBUTMORETHAN1CAT => "ERRORE: L'inserimento di pi\F9 categorie impedisce l'uso dei parametri 'addfirstcategorydate=true' e 'ordermethod=categoryadd'.",
            'dpl2_log_' . self::FATAL_MORETHAN1TYPEOFDATE => 'ERRORE: Non \E8 consentito l\'uso contemporaneo di pi\F9 tipi di data.',
            /**
             * $0: param=val that is possible only with $1 as last 'ordermethod' parameter
             * $1: last 'ordermethod' parameter required for $0
             */
            'dpl2_log_' . self::FATAL_WRONGORDERMETHOD => "ERRORE: L'uso del parametro '$0' \E8 consentito unicamente con 'ordermethod=[...,]$1'.",
            /**
             * $0: the number of arguments in includepage
             */
            'dpl2_log_' . self::FATAL_DOMINANTSECTIONRANGE => "ERROR: the index for the dominant section must be between 1 and the number of arguments of includepage ($0 in this case)",
            /**
             * $0: prefix_dpl_clview where 'prefix' is the prefix of your mediawiki table names
             * $1: SQL query to create the prefix_dpl_clview on your mediawiki DB
             */
            'dpl2_log_' . self::FATAL_NOCLVIEW => "ERRORE: Impossibile effettuare operazioni logiche sulle pagine prive di categoria (ad es. con il parametro 'category') in quanto il database non contiene la vista $0. Suggerimento: chiedere all'amministratore del database di eseguire la seguente query: <code>$1</code>.",
            'dpl2_log_' . self::FATAL_OPENREFERENCES => 'ERROR: specifying "openreferences" is incompatible with some other option you specified. See the manual for details.',

            // WARN
            /**
             * $0: unknown parameter given by user
             * $1: list of DPL2 available parameters separated by ', '
             */
            'dpl2_log_' . self::WARN_UNKNOWNPARAM => "ATTENZIONE: Il parametro non riconosciuto '$0' \E8 stato ignorato. Suggerimento: i parametri disponibili sono: <code>$1</code>.",
            /**
             * $3: list of valid param values separated by ' | '
             */
            'dpl2_log_' . self::WARN_WRONGPARAM => "ATTENZIONE: Errore nel parametro '$0': '$1'. \C8 stato usato il valore predefinito '$2'. Suggerimento: <code>$0= $3</code>.",
            /**
             * $0: param name
             * $1: wrong param value given by user
             * $2: default param value used instead by program
             */
            'dpl2_log_' . self::WARN_WRONGPARAM_INT => "ATTENZIONE: errore nel parametro '$0': '$1'. \C8 stato usato il valore predefinito '$2' (nessun limite). Suggerimento: <code>$0= <i>stringa vuota</i> (nessun limite) | n</code>, con <code>n</code> intero positivo.",
            'dpl2_log_' . self::WARN_NORESULTS => 'ATTENZIONE: Nessun risultato.',
            'dpl2_log_' . self::WARN_CATOUTPUTBUTWRONGPARAMS => "ATTENZIONE: I parametri add* ('adduser', 'addeditdate', ecc.)' non hanno alcun effetto quando \E8 specificato 'mode=category'. In tale modalit\E0 vengono visualizzati unicamente il namespace e il titolo della pagina.",
            /**
             * $0: 'headingmode' value given by user
             * $1: value used instead by program (which means no heading)
             */
            'dpl2_log_' . self::WARN_HEADINGBUTSIMPLEORDERMETHOD => "ATTENZIONE: Il parametro 'headingmode=$0' non ha alcun effetto quando \E8 specificato 'ordermethod' su un solo componente. Verr\E0 utilizzato il valore '$1'. Suggerimento: \E8 posibile utilizzare i valori diversi da $1 per il parametro 'headingmode' nel caso di 'ordermethod' su pi\F9 componenti. Il primo componente viene usato per generare i titoli di sezione. Ad es. 'ordermethod=category,<i>comp</i>' (dove <i>comp</i> \E8 un altro componente) per avere titoli di sezione basati sulla categoria.",
            /**
             * $0: 'debug' value
             */
            'dpl2_log_' . self::WARN_DEBUGPARAMNOTFIRST => "ATTENZIONE: Il parametro 'debug=$0' non \E8 il primo elemento della sezione DPL. Le nuove impostazioni di debug non verranno applicate prima di aver completato il parsing e la verifica di tutti i parametri che lo precedono.",

            // DEBUG
            /**
             * $0: SQL query executed to generate the dynamic page list
             */
            'dpl2_log_' . self::DEBUG_QUERY => 'QUERY: <code>$0</code>',

            /*
               Output formatting
             */
            /**
             * $1: number of articles
             */
            'dpl2_articlecount' => 'Questa sezione contiene {{PLURAL:$1|una voce|$1 voci}}.'
        );
        self::$messages['nl'] = array(
            /*
                Log
             */
            // FATAL
            /**
             * $0: 'namespace' or 'notnamespace'
             * $1: wrong parameter given by user
             * $3: list of possible titles of namespaces (except pseudo-namespaces: Media, Special)
             */
            'dpl2_log_' . self::FATAL_WRONGNS => "FOUT: Verkeerde parameter '$0': '$1'! Hulp:  <code>$0= <i>lege string</i> (Main)$3</code>.",
            /**
             * $0: max number of categories that can be included
             */
            'dpl2_log_' . self::FATAL_TOOMANYCATS => 'FOUT: Te veel categori\EBn! Maximum: $0. Hulp: verhoog <code>ExtDynamicPageList2::$maxCategoryCount</code> om meer categorie\EBn op te kunnen geven of stel geen limiet in met <code>ExtDynamicPageList2::$allowUnlimitedCategories=true</code>. (Neem deze variabele op in <code>LocalSettings.php</code>, na het toevoegen van <code>DynamicPageList2.php</code>.)',
            /**
             * $0: min number of categories that have to be included
             */
            'dpl2_log_' . self::FATAL_TOOFEWCATS => 'FOUT: Te weinig categorie\EBn! Minimum: $0. Hulp: verlaag <code>ExtDynamicPageList2::$minCategoryCount</code> om minder categorie\EBn aan te hoeven geven. (Stel de variabele bij voorkeur in via <code>LocalSettings.php</code>, na het toevoegen van <code>DynamicPageList2.php</code>.)',
            'dpl2_log_' . self::FATAL_NOSELECTION => "ERROR: No selection criteria found! You must use at least one of the following parameters: category, namespace, titlematch, linksto, uses, createdby, modifiedby, lastmodifiedby or their 'not' variants",
            'dpl2_log_' . self::FATAL_CATDATEBUTNOINCLUDEDCATS => "FOUT: U dient tenminste \E9\E9n categorie op te nemen als u 'addfirstcategorydate=true' of 'ordermethod=categoryadd' wilt gebruiken!",
            'dpl2_log_' . self::FATAL_CATDATEBUTMORETHAN1CAT => "FOUT: Als u meer dan \E9\E9n categorie opneemt, kunt u 'addfirstcategorydate=true' of 'ordermethod=categoryadd' niet gebruiken!",
            'dpl2_log_' . self::FATAL_MORETHAN1TYPEOFDATE => 'FOUT: U kunt niet meer dan \E9\E9n type of datum tegelijk gebruiken!',
            /**
             * $0: param=val that is possible only with $1 as last 'ordermethod' parameter
             * $1: last 'ordermethod' parameter required for $0
             */
            'dpl2_log_' . self::FATAL_WRONGORDERMETHOD => "FOUT: U kunt '$0' alleen met 'ordermethod=[...,]$1' gebruiken!",
            /**
             * $0: the number of arguments in includepage
             */
            'dpl2_log_' . self::FATAL_DOMINANTSECTIONRANGE => "ERROR: the index for the dominant section must be between 1 and the number of arguments of includepage ($0 in this case)",
            /**
             * $0: prefix_dpl_clview where 'prefix' is the prefix of your mediawiki table names
             * $1: SQL query to create the prefix_dpl_clview on your mediawiki DB
             */
            'dpl2_log_' . self::FATAL_NOCLVIEW =>          self::$messages['en']['dpl2_log_' . self::FATAL_NOCLVIEW],
            'dpl2_log_' . self::FATAL_OPENREFERENCES => 'ERROR: specifying "openreferences" is incompatible with some other option you specified. See the manual for details.',

            // WARN
            /**
             * $0: unknown parameter given by user
             * $1: list of DPL2 available parameters separated by ', '
             */
            'dpl2_log_' . self::WARN_UNKNOWNPARAM =>          self::$messages['en']['dpl2_log_' . self::WARN_UNKNOWNPARAM],
            /**
             * $3: list of valid param values separated by ' | '
             */
            'dpl2_log_' . self::WARN_WRONGPARAM => "WAARSCHUWING: Verkeerde parameter '$0': '$1'! Nu wordt de standaard gebruikt: '$2'. Hulp: <code>$0= $3</code>.",
            /**
             * $0: param name
             * $1: wrong param value given by user
             * $2: default param value used instead by program
             */
            'dpl2_log_' . self::WARN_WRONGPARAM_INT =>          self::$messages['en']['dpl2_log_' . self::WARN_WRONGPARAM_INT],
            'dpl2_log_' . self::WARN_NORESULTS => 'WAARSCHUWING: Geen resultaten!',
            'dpl2_log_' . self::WARN_CATOUTPUTBUTWRONGPARAMS => "WAARSCHUWING: Add* parameters ('adduser', 'addeditdate', etc.)' heeft geen effect bij 'mode=category'. Alleen de paginanaamruimte/titel is in deze modus te bekijken.",
            /**
             * $0: 'headingmode' value given by user
             * $1: value used instead by program (which means no heading)
             */
            'dpl2_log_' . self::WARN_HEADINGBUTSIMPLEORDERMETHOD => "WAARSCHUWING: 'headingmode=$0' heeft geen effect met 'ordermethod' op een enkele component. Nu wordt gebruikt: '$1'. Hulp: u kunt een niet-$1 'headingmode'-waarde gebruiken met 'ordermethod' op meerdere componenten. De eerste component wordt gebruikt als kop. Bijvoorbeeld 'ordermethod=category,<i>comp</i>' (<i>comp</i> is een ander component) voor categoriekoppen.",
            /**
             * $0: 'debug' value
             */
            'dpl2_log_' . self::WARN_DEBUGPARAMNOTFIRST => "WAARSCHUWING: 'debug=$0' is niet de eerste positie in het DPL-element. De nieuwe debuginstellingen zijn niet toegepast voor alle voorgaande parameters zijn verwerkt en gecontroleerd.",

            // DEBUG
            /**
             * $0: SQL query executed to generate the dynamic page list
             */
            'dpl2_log_' . self::DEBUG_QUERY => 'QUERY: <code>$0</code>',

            /*
               Output formatting
             */
            /**
             * $1: number of articles
             */
            'dpl2_articlecount' => 'Er {{PLURAL:$1|is \E9\E9n pagina|zijn $1 pagina\'s}} onder deze kop.'
        );
        self::$messages['ru'] = array(
            /*
                Log
             */
            // FATAL
            /**
             * $0: 'namespacenamespace' or 'notnamespace'
             * $1: wrong parameter given by user
             * $3: list of possible titles of namespaces (except pseudo-namespaces: Media, Special)
             */
            'dpl2_log_' . self::FATAL_WRONGNS => "??????: ???????????? \AB$0\BB-????????: \AB$1\BB! ?????????:  <code>$0= <i>?????? ??????</i> (????????)$3</code>.",
            /**
             * $0: max number of categories that can be included
             */
            'dpl2_log_' . self::FATAL_TOOMANYCATS => '??????: ??????? ????? ?????????! ????????: $0. ?????????: ???????? <code>ExtDynamicPageList2::$maxCategoryCount</code> ????? ????????? ?????? ????????? ??? ?????????? <code>ExtDynamicPageList2::$allowUnlimitedCategories=true</code> ??? ?????? ???????????. (?????????????? ?????????? ? <code>LocalSettings.php</code>, ????? ??????????? <code>DynamicPageList2.php</code>.)',
            /**
             * $0: min number of categories that have to be included
             */
            'dpl2_log_' . self::FATAL_TOOFEWCATS => '??????: ??????? ???? ?????????! ???????: $0. ?????????: ????????? <code>ExtDynamicPageList2::$minCategoryCount</code> ????? ????????? ?????? ?????????. (?????????????? ?????????? ? <code>LocalSettings.php</code>, ????? ??????????? <code>DynamicPageList2.php</code>.)',
            'dpl2_log_' . self::FATAL_NOSELECTION => "ERROR: No selection criteria found! You must use at least one of the following parameters: category, namespace, titlematch, linksto, uses, createdby, modifiedby, lastmodifiedby or their 'not' variants",
            'dpl2_log_' . self::FATAL_CATDATEBUTNOINCLUDEDCATS => "??????: ?? ?????? ???????? ???? ?? ???? ?????????, ???? ?? ?????? ???????????? \ABaddfirstcategorydate=true\BB ??? \ABordermethod=categoryadd\BB!",
            'dpl2_log_' . self::FATAL_CATDATEBUTMORETHAN1CAT => "??????: ???? ?? ????????? ?????? ????? ?????????, ?? ?? ?? ?????? ???????????? \ABaddfirstcategorydate=true\BB ??? \ABordermethod=categoryadd\BB!",
            'dpl2_log_' . self::FATAL_MORETHAN1TYPEOFDATE => '??????: ?? ?? ?????? ???????? ????? ?????? ???? ?????? ?? ???!',
            /**
             * $0: param=val that is possible only with $1 as last 'ordermethod' parameter
             * $1: last 'ordermethod' parameter required for $0
             */
            'dpl2_log_' . self::FATAL_WRONGORDERMETHOD => "??????: ?? ?????? ???????????? \AB$0\BB ?????? ? \ABordermethod=[...,]$1\BB!",
            /**
             * $0: the number of arguments in includepage
             */
            'dpl2_log_' . self::FATAL_DOMINANTSECTIONRANGE => "ERROR: the index for the dominant section must be between 1 and the number of arguments of includepage ($0 in this case)",
            /**
             * $0: prefix_dpl_clview where 'prefix' is the prefix of your mediawiki table names
             * $1: SQL query to create the prefix_dpl_clview on your mediawiki DB
             */
            'dpl2_log_' . self::FATAL_NOCLVIEW =>          self::$messages['en']['dpl2_log_' . self::FATAL_NOCLVIEW],
            'dpl2_log_' . self::FATAL_OPENREFERENCES => 'ERROR: specifying "openreferences" is incompatible with some other option you specified. See the manual for details.',

            // WARN
            /**
             * $0: unknown parameter given by user
             * $1: list of DPL2 available parameters separated by ', '
             */
            'dpl2_log_' . self::WARN_UNKNOWNPARAM => "??????????????: ??????????? ???????? \AB$0\BB ??????????????. ?????????: ????????? ?????????: <code>$1</code>.",
            /**
             * $3: list of valid param values separated by ' | '
             */
            'dpl2_log_' . self::WARN_WRONGPARAM => "??????????????: ???????????? ???????? \AB$0\BB: \AB$1\BB! ????????????? ????????? ?? ?????????: \AB$2\BB. ?????????: <code>$0= $3</code>.",
            /**
             * $0: param name
             * $1: wrong param value given by user
             * $2: default param value used instead by program
             */
            'dpl2_log_' . self::WARN_WRONGPARAM_INT => "??????????????: ???????????? ???????? \AB$0\BB: \AB$1\BB! ????????????? ????????? ?? ?????????: \AB$2\BB (??? ???????????). ?????????: <code>$0= <i>?????? ??????</i> (??? ???????????) | n</code>, ? <code>n</code> ?????? ?????????????? ?????? ?????.",
            'dpl2_log_' . self::WARN_NORESULTS => '??????????????: ?? ???????!',
            'dpl2_log_' . self::WARN_CATOUTPUTBUTWRONGPARAMS => "??????????????: ??????????* ?????????? (\ABadduser\BB, \ABaddeditdate\BB, ? ??.) ?? ????????????? ? \ABmode=category\BB. ?????? ???????????? ???? ??? ???????? ????? ??????????????? ? ???? ??????.",
            /**
             * $0: 'headingmode' value given by user
             * $1: value used instead by program (which means no heading)
             */
            'dpl2_log_' . self::WARN_HEADINGBUTSIMPLEORDERMETHOD => "??????????????: \ABheadingmode=$0\BB ?? ???????????? ? \ABordermethod\BB ? ????? ??????????. ?????????????: \AB$1\BB. ?????????: ?? ?????? ????????????e ??-$1 \ABheadingmode\BB ???????? ? \ABordermethod\BB ?? ????????? ???????????. ?????? ????????? ???????????? ??? ??????????. ????????, \ABordermethod=category,<i>comp</i>\BB (<i>comp</i> ???????? ?????? ???????????) ??? ?????????? ?????????.",
            /**
             * $0: 'debug' value
             */
            'dpl2_log_' . self::WARN_DEBUGPARAMNOTFIRST => "??????????????: \ABdebug=$0\BB ?? ????????? ?? ?????? ????? ? DPL-????????. ????? ????????? ??????? ?? ????? ????????? ???? ??? ?????????? ????????? ?? ????? ????????? ? ?????????.",

            // DEBUG
            /**
             * $0: SQL query executed to generate the dynamic page list
             */
            'dpl2_log_' . self::DEBUG_QUERY => '??????: <code>$0</code>',

            /*
               Output formatting
             */
            /**
             * $1: number of articles
             */
            'dpl2_articlecount' => '? ???? ????????? $1 {{PLURAL:$1|??????|??????|??????}}.'
        );
        self::$messages['sk'] = array(
            /*
                Log
             */
            // FATAL
            /**
             * $0: 'namespace' or 'notnamespace'
             * $1: wrong parameter given by user
             * $3: list of possible titles of namespaces (except pseudo-namespaces: Media, Special)
             */
            'dpl2_log_' . self::FATAL_WRONGNS => "CHYBA: nespr\E1vny parameter '$0': '$1'! Pomocn\EDk <code>$0= <i>pr\E1zdny retazec</i> (Hlavn\FD)$3<code>.",
            /**
             * $0: 'linksto' (left as $0 just in case the parameter is renamed in the future)
             * $1: wrong parameter given by user
             */
            'dpl2_log_' . self::FATAL_WRONGLINKSTO => "CHYBA: Zl\FD parameter '$0': '$1'! Pomocn\EDk <code>$0= <i>pln\FD n\E1zov str\E1nky</i></code>.",
            /**
             * $0: max number of categories that can be included
             */
            'dpl2_log_' . self::FATAL_TOOMANYCATS => 'CHYBA: Pr\EDli\9A vela kateg\F3ri\ED! Maximum: $0. Pomocn\EDk: zv\E4c\9Aite <code>ExtDynamicPageList2::$maxCategoryCount</code>, aby ste mohli \9Apecifikovat viac kateg\F3ri\ED alebo nastavte <code>ExtDynamicPageList2::$allowUnlimitedCategories=true</code> pre vypnutie limitu. (Premenn\FA nastatavte v <code>LocalSettings.php</code>, potom ako bol includovan\FD <code>DynamicPageList2.php</code>.)',
            /**
             * $0: min number of categories that have to be included
             */
            'dpl2_log_' . self::FATAL_TOOFEWCATS => 'CHYBA: Pr\EDli\9A m\E1lo kateg\F3ri\ED! Minimum: $0. Pomocn\EDk: zn\ED\9Ete <code>ExtDynamicPageList2::$minCategoryCount</code>, aby ste mohli \9Apecifikovat menej kateg\F3ri\ED. (Premenn\FA nastavte najlep\9Aie v <code>LocalSettings.php</code> potom, ako v nom bol includovan\FD <code>DynamicPageList2.php</code>.)',
            'dpl2_log_' . self::FATAL_NOSELECTION => "ERROR: No selection criteria found! You must use at least one of the following parameters: category, namespace, titlematch, linksto, uses, createdby, modifiedby, lastmodifiedby or their 'not' variants",
            'dpl2_log_' . self::FATAL_CATDATEBUTNOINCLUDEDCATS => "CHYBA: Mus\EDte uviest aspon jednu kateg\F3riu ak chcete pou\9Eit 'addfirstcategorydate=true' alebo 'ordermethod=categoryadd'!",
            'dpl2_log_' . self::FATAL_CATDATEBUTMORETHAN1CAT => "CHYBA: Ak zahrniete viac ako jednu kateg\F3riu, nem\F4\9Eete pou\9Eit 'addfirstcategorydate=true' alebo 'ordermethod=categoryadd'!",
            'dpl2_log_' . self::FATAL_MORETHAN1TYPEOFDATE => 'CHYBA: Nem\F4\9Eete naraz pridat viac ako jeden typ d\E1tumu!',
            /**
             * $0: param=val that is possible only with $1 as last 'ordermethod' parameter
             * $1: last 'ordermethod' parameter required for $0
             */
            'dpl2_log_' . self::FATAL_WRONGORDERMETHOD => "CHYBA: '$0' m\F4\9Eete pou\9Eit iba s 'ordermethod=[...,]$1'!",
            /**
             * $0: the number of arguments in includepage
             */
            'dpl2_log_' . self::FATAL_DOMINANTSECTIONRANGE => "ERROR: the index for the dominant section must be between 1 and the number of arguments of includepage ($0 in this case)",
            /**
             * $0: prefix_dpl_clview where 'prefix' is the prefix of your mediawiki table names
             * $1: SQL query to create the prefix_dpl_clview on your mediawiki DB
             */
            'dpl2_log_' . self::FATAL_NOCLVIEW => "CHYBA: Nie je momo\9En\E9 vykon\E1vat logick\E9 oper\E1cie na nekategorizovan\FDch kateg\F3ri\E1ch (napr. s parametrom 'Kateg\F3ria') lebo neexistuje na datab\E1zu pohlad $0! Pomocn\EDk: nech admin datab\E1zy vykon\E1 tento dotaz: <code>$1</code>.",
            'dpl2_log_' . self::FATAL_OPENREFERENCES => 'ERROR: specifying "openreferences" is incompatible with some other option you specified. See the manual for details.',

            // WARN
            /**
             * $0: unknown parameter given by user
             * $1: list of DPL2 available parameters separated by ', '
             */
            'dpl2_log_' . self::WARN_UNKNOWNPARAM => "VAROVANIE: Nezn\E1my parameter '$0' ignorovan\FD. Pomocn\EDk: dostupn\E9 parametre: <code>$1</code>.",
            /**
             * $3: list of valid param values separated by ' | '
             */
            'dpl2_log_' . self::WARN_WRONGPARAM => "VAROVANIE: Nespr\E1vny '$0' parameter: '$1'! Pou\9E\EDvam \9Atandardn\FD '$2'. Pomocn\EDk: <code>$0= $3</code>.",
            /**
             * $0: param name
             * $1: wrong param value given by user
             * $2: default param value used instead by program
             */
            'dpl2_log_' . self::WARN_WRONGPARAM_INT => "VAROVANIE: Nespr\E1vny parameter  '$0': '$1'! Pou\9E\EDvam \9Atandardn\FD: '$2' (bez obmedzenia). Pomocn\EDk: <code>$0= <i>pr\E1zdny retazec</i> (bez obmedzenia) | n</code>, s kladn\FDm cel\FDm c\EDslom <code>n</code>.",
            'dpl2_log_' . self::WARN_NORESULTS => 'VAROVANIE: No results!',
            'dpl2_log_' . self::WARN_CATOUTPUTBUTWRONGPARAMS => "VAROVANIE: Parametre Add* ('adduser', 'addeditdate', atd' nepracuj\FA s mode=category'. V tomto re\9Eime je mo\9En\E9 prehliadat iba menn\E1 priestor/titulok str\E1nky.",
            /**
             * $0: 'headingmode' value given by user
             * $1: value used instead by program (which means no heading)
             */
            'dpl2_log_' . self::WARN_HEADINGBUTSIMPLEORDERMETHOD => "VAROVANIE: 'headingmode=$0' nepracuje s 'ordermethod' na jednom komponente. Pou\9Eitie: '$1'. Pomocn\EDk: m\F4\9Eete pou\9Eit not-$1 hodnoty 'headingmode' s 'ordermethod' na viacer\E9 komponenty. Prv\FD komponent sa pou\9E\EDva na nadpisy. Napr. 'ordermethod=category,<i>comp</i>' (<i>comp</i> je in\FD komponent) pre nadpisy kateg\F3ri\ED.",
            /**
             * $0: 'debug' value
             */
            'dpl2_log_' . self::WARN_DEBUGPARAMNOTFIRST => "VAROVANIE: 'debug=$0' nie je na prvej poz\EDcii v prvku DPL. Nov\E9 ladiacie nastavenia nebud\FA pou\9E\EDt\E9 sk\F4r ne\9E bud\FA parsovan\E9 a skontrolovan\E9 v\9Aetky predch\E1dzaj\FAce.",

            // DEBUG
            /**
             * $0: SQL query executed to generate the dynamic page list
             */
            'dpl2_log_' . self::DEBUG_QUERY => 'DOTAZ: <code>$0</code>',

            /*
               Output formatting
             */
            /**
             * $1: number of articles
             */
            'dpl2_articlecount' => 'V tomto nadpise {{PLURAL:$1|je jeden cl\E1nok|s\FA $1 cl\E1nky|je $1 cl\E1nkov}}.'
        );
        self::$messages['zh-cn'] = array(
            /*
                Log
             */
            // FATAL
            /**
             * $0: 'namespace' or 'notnamespace'
             * $1: wrong parameter given by user
             * $3: list of possible titles of namespaces (except pseudo-namespaces: Media, Special)
             */
            'dpl2_log_' . self::FATAL_WRONGNS => "??: ??? '$0' ??: '$1'! ??:  <code>$0= <i>?????</i> (?)$3</code>?",
            /**
             * $0: max number of categories that can be included
             */
            'dpl2_log_' . self::FATAL_TOOMANYCATS => '??: ????! ???: $0? ??: ?? <code>ExtDynamicPageList2::$maxCategoryCount</code> ????????????? <code>ExtDynamicPageList2::$allowUnlimitedCategories=true</code> ?????? (??? <code>DynamicPageList2.php</code>?,?<code>LocalSettings.php</code>??????)',
            /**
             * $0: min number of categories that have to be included
             */
            'dpl2_log_' . self::FATAL_TOOFEWCATS => '??: ????! ???: $0? ??: ?? <code>ExtDynamicPageList2::$minCategoryCount</code> ??????????? (??? <code>DynamicPageList2.php</code>?,?<code>LocalSettings.php</code>???????????)',
            'dpl2_log_' . self::FATAL_NOSELECTION => "ERROR: No selection criteria found! You must use at least one of the following parameters: category, namespace, titlematch, linksto, uses, createdby, modifiedby, lastmodifiedby or their 'not' variants",
            'dpl2_log_' . self::FATAL_CATDATEBUTNOINCLUDEDCATS => "??: ????? 'addfirstcategorydate=true' ? 'ordermethod=categoryadd' ,???????????!",
            'dpl2_log_' . self::FATAL_CATDATEBUTMORETHAN1CAT => "??: ??????????,????? 'addfirstcategorydate=true' ? 'ordermethod=categoryadd'!",
            'dpl2_log_' . self::FATAL_MORETHAN1TYPEOFDATE => '??: ???????????????????!',
            /**
             * $0: param=val that is possible only with $1 as last 'ordermethod' parameter
             * $1: last 'ordermethod' parameter required for $0
             */
            'dpl2_log_' . self::FATAL_WRONGORDERMETHOD => "??: ????? 'ordermethod=[...,]$1' ? '$0' ?!",
            /**
             * $0: the number of arguments in includepage
             */
            'dpl2_log_' . self::FATAL_DOMINANTSECTIONRANGE => "ERROR: the index for the dominant section must be between 1 and the number of arguments of includepage ($0 in this case)",
            /**
             * $0: prefix_dpl_clview where 'prefix' is the prefix of your mediawiki table names
             * $1: SQL query to create the prefix_dpl_clview on your mediawiki DB
             */
            'dpl2_log_' . self::FATAL_NOCLVIEW =>          self::$messages['en']['dpl2_log_' . self::FATAL_NOCLVIEW],
            'dpl2_log_' . self::FATAL_OPENREFERENCES => 'ERROR: specifying "openreferences" is incompatible with some other option you specified. See the manual for details.',

            // WARN
            /**
             * $0: unknown parameter given by user
             * $1: list of DPL2 available parameters separated by ', '
             */
            'dpl2_log_' . self::WARN_UNKNOWNPARAM => "??: ????? '$0' ???? ??: ?????: <code>$1</code>?",
            /**
             * $3: list of valid param values separated by ' | '
             */
            'dpl2_log_' . self::WARN_WRONGPARAM => "??: ??? '$0' ??: '$1'! ???????: '$2'? ??: <code>$0= $3</code>?",
            /**
             * $0: param name
             * $1: wrong param value given by user
             * $2: default param value used instead by program
             */
            'dpl2_log_' . self::WARN_WRONGPARAM_INT => "??: ??? '$0' ??: '$1'! ???????: '$2' (????)? ??: <code>$0= <i>?????</i> (????) | n</code>, <code>n</code>???????",
            'dpl2_log_' . self::WARN_NORESULTS => '??: ???!',
            'dpl2_log_' . self::WARN_CATOUTPUTBUTWRONGPARAMS => "??: ??* ?? ('adduser', 'addeditdate', ?)' ?? 'mode=category' ????????????/??????????????",
            /**
             * $0: 'headingmode' value given by user
             * $1: value used instead by program (which means no heading)
             */
            'dpl2_log_' . self::WARN_HEADINGBUTSIMPLEORDERMETHOD => "??: ??????, 'ordermethod' ? 'headingmode=$0' ??????? ????: '$1'? ??: ?????$1 'headingmode' ??,??????? 'ordermethod' ????????????????????? 'ordermethod=category,<i>comp</i>' (<i>comp</i>???????) ?",
            /**
             * $0: 'debug' value
             */
            'dpl2_log_' . self::WARN_DEBUGPARAMNOTFIRST => "??: 'debug=$0' ??????DPL?????????????????????????????????",

            // DEBUG
            /**
             * $0: SQL query executed to generate the dynamic page list
             */
            'dpl2_log_' . self::DEBUG_QUERY => '??: <code>$0</code>',

            /*
               Output formatting
             */
            /**
             * $1: number of articles
             */
            'dpl2_articlecount' => '???????$1????'
        );
        self::$messages['zh-tw'] = array(
            /*
                Log
             */
            // FATAL
            /**
             * $0: 'namespace' or 'notnamespace'
             * $1: wrong parameter given by user
             * $3: list of possible titles of namespaces (except pseudo-namespaces: Media, Special)
             */
            'dpl2_log_' . self::FATAL_WRONGNS => "??: ??? '$0' ??: '$1'! ??:  <code>$0= <i>????</i> (?)$3</code>?",
            /**
             * $0: max number of categories that can be included
             */
            'dpl2_log_' . self::FATAL_TOOMANYCATS => '??: ????! ???: $0? ??: ?? <code>ExtDynamicPageList2::$maxCategoryCount</code> ????????????? <code>ExtDynamicPageList2::$allowUnlimitedCategories=true</code> ?????? (??? <code>DynamicPageList2.php</code>?,?<code>LocalSettings.php</code>??????)',
            /**
             * $0: min number of categories that have to be included
             */
            'dpl2_log_' . self::FATAL_TOOFEWCATS => '??: ????! ???: $0? ??: ?? <code>ExtDynamicPageList2::$minCategoryCount</code> ??????????? (??? <code>DynamicPageList2.php</code>?,?<code>LocalSettings.php</code>???????????)',
            'dpl2_log_' . self::FATAL_NOSELECTION => "ERROR: No selection criteria found! You must use at least one of the following parameters: category, namespace, titlematch, linksto, uses, createdby, modifiedby, lastmodifiedby or their 'not' variants",
            'dpl2_log_' . self::FATAL_CATDATEBUTNOINCLUDEDCATS => "??: ????? 'addfirstcategorydate=true' ? 'ordermethod=categoryadd' ,???????????!",
            'dpl2_log_' . self::FATAL_CATDATEBUTMORETHAN1CAT => "??: ??????????,????? 'addfirstcategorydate=true' ? 'ordermethod=categoryadd'!",
            'dpl2_log_' . self::FATAL_MORETHAN1TYPEOFDATE => '??: ???????????????????!',
            /**
             * $0: param=val that is possible only with $1 as last 'ordermethod' parameter
             * $1: last 'ordermethod' parameter required for $0
             */
            'dpl2_log_' . self::FATAL_WRONGORDERMETHOD => "??: ????? 'ordermethod=[...,]$1' ? '$0' ?!",
            /**
             * $0: the number of arguments in includepage
             */
            'dpl2_log_' . self::FATAL_DOMINANTSECTIONRANGE => "ERROR: the index for the dominant section must be between 1 and the number of arguments of includepage ($0 in this case)",
            /**
             * $0: prefix_dpl_clview where 'prefix' is the prefix of your mediawiki table names
             * $1: SQL query to create the prefix_dpl_clview on your mediawiki DB
             */
            'dpl2_log_' . self::FATAL_NOCLVIEW =>          self::$messages['en']['dpl2_log_' . self::FATAL_NOCLVIEW],
            'dpl2_log_' . self::FATAL_OPENREFERENCES => 'ERROR: specifying "openreferences" is incompatible with some other option you specified. See the manual for details.',

            // WARN
            /**
             * $0: unknown parameter given by user
             * $1: list of DPL2 available parameters separated by ', '
             */
            'dpl2_log_' . self::WARN_UNKNOWNPARAM => "??: ????? '$0' ???? ??: ?????: <code>$1</code>?",
            /**
             * $3: list of valid param values separated by ' | '
             */
            'dpl2_log_' . self::WARN_WRONGPARAM => "??: ??? '$0' ??: '$1'! ???????: '$2'? ??: <code>$0= $3</code>?",
            /**
             * $0: param name
             * $1: wrong param value given by user
             * $2: default param value used instead by program
             */
            'dpl2_log_' . self::WARN_WRONGPARAM_INT => "??: ??? '$0' ??: '$1'! ???????: '$2' (????)? ??: <code>$0= <i>????</i> (????) | n</code>, <code>n</code>???????",
            'dpl2_log_' . self::WARN_NORESULTS => '??: ???!',
            'dpl2_log_' . self::WARN_CATOUTPUTBUTWRONGPARAMS => "??: ??* ?? ('adduser', 'addeditdate', ?)' ?? 'mode=category' ????????????/??????????????",
            /**
             * $0: 'headingmode' value given by user
             * $1: value used instead by program (which means no heading)
             */
            'dpl2_log_' . self::WARN_HEADINGBUTSIMPLEORDERMETHOD => "??: ??????, 'ordermethod' ? 'headingmode=$0' ??????? ????: '$1'? ??: ?????$1 'headingmode' ??,??????? 'ordermethod' ????????????????????? 'ordermethod=category,<i>comp</i>' (<i>comp</i>???????) ?",
            /**
             * $0: 'debug' value
             */
            'dpl2_log_' . self::WARN_DEBUGPARAMNOTFIRST => "??: 'debug=$0' ??????DPL?????????????????????????????????",

            // DEBUG
            /**
             * $0: SQL query executed to generate the dynamic page list
             */
            'dpl2_log_' . self::DEBUG_QUERY => '??: <code>$0</code>',

            /*
               Output formatting
             */
            /**
             * $1: number of articles
             */
            'dpl2_articlecount' => '???????$1????'
        );
        self::$messages['zh-yue'] = array(
            /*
                Log
             */
            // FATAL
            /**
             * $0: 'namespace' or 'notnamespace'
             * $1: wrong parameter given by user
             * $3: list of possible titles of namespaces (except pseudo-namespaces: Media, Special)
             */
            'dpl2_log_' . self::FATAL_WRONGNS => "??: ?? '$0' ??: '$1'! ??:  <code>$0= <i>???</i> (?)$3</code>?",
            /**
             * $0: max number of categories that can be included
             */
            'dpl2_log_' . self::FATAL_TOOMANYCATS => '??: ????! ???: $0? ??: ?? <code>ExtDynamicPageList2::$maxCategoryCount</code> ?????????????? <code>ExtDynamicPageList2::$allowUnlimitedCategories=true</code> ?????? (??? <code>DynamicPageList2.php</code>??,?<code>LocalSettings.php</code>??????)',
            /**
             * $0: min number of categories that have to be included
             */
            'dpl2_log_' . self::FATAL_TOOFEWCATS => '??: ????! ???: $0. ??: ?? <code>ExtDynamicPageList2::$minCategoryCount</code> ??????????? (??? <code>DynamicPageList2.php</code>??,?<code>LocalSettings.php</code>???????????)',
            'dpl2_log_' . self::FATAL_NOSELECTION => "ERROR: No selection criteria found! You must use at least one of the following parameters: category, namespace, titlematch, linksto, uses, createdby, modifiedby, lastmodifiedby or their 'not' variants",
            'dpl2_log_' . self::FATAL_CATDATEBUTNOINCLUDEDCATS => "??: ?????? 'addfirstcategorydate=true' ?? 'ordermethod=categoryadd' ,???????????!",
            'dpl2_log_' . self::FATAL_CATDATEBUTMORETHAN1CAT => "??: ???????????,????? 'addfirstcategorydate=true' ?? 'ordermethod=categoryadd'!",
            'dpl2_log_' . self::FATAL_MORETHAN1TYPEOFDATE => '??: ???????????????????!',
            /**
             * $0: param=val that is possible only with $1 as last 'ordermethod' parameter
             * $1: last 'ordermethod' parameter required for $0
             */
            'dpl2_log_' . self::FATAL_WRONGORDERMETHOD => "??: ????? 'ordermethod=[...,]$1' ? '$0' ?!",
            /**
             * $0: the number of arguments in includepage
             */
            'dpl2_log_' . self::FATAL_DOMINANTSECTIONRANGE => "ERROR: the index for the dominant section must be between 1 and the number of arguments of includepage ($0 in this case)",
            /**
             * $0: prefix_dpl_clview where 'prefix' is the prefix of your mediawiki table names
             * $1: SQL query to create the prefix_dpl_clview on your mediawiki DB
             */
            'dpl2_log_' . self::FATAL_NOCLVIEW =>          self::$messages['en']['dpl2_log_' . self::FATAL_NOCLVIEW],
            'dpl2_log_' . self::FATAL_OPENREFERENCES => 'ERROR: specifying "openreferences" is incompatible with some other option you specified. See the manual for details.',

            // WARN
            /**
             * $0: unknown parameter given by user
             * $1: list of DPL2 available parameters separated by ', '
             */
            'dpl2_log_' . self::WARN_UNKNOWNPARAM => "??: ????? '$0' ???? ??: ?????: <code>$1</code>?",
            /**
             * $3: list of valid param values separated by ' | '
             */
            'dpl2_log_' . self::WARN_WRONGPARAM => "??: ??? '$0' ??: '$1'! ?????: '$2'? ??: <code>$0= $3</code>?",
            /**
             * $0: param name
             * $1: wrong param value given by user
             * $2: default param value used instead by program
             */
            'dpl2_log_' . self::WARN_WRONGPARAM_INT => "??: ??? '$0' ??: '$1'! ?????: '$2' (???)? ??: <code>$0= <i>???</i> (???) | n</code>, <code>n</code>???????",
            'dpl2_log_' . self::WARN_NORESULTS => '??: ???!',
            'dpl2_log_' . self::WARN_CATOUTPUTBUTWRONGPARAMS => "??: ??* ?? ('adduser', 'addeditdate', ?)' ?? 'mode=category' ???????????/??????????????",
            /**
             * $0: 'headingmode' value given by user
             * $1: value used instead by program (which means no heading)
             */
            'dpl2_log_' . self::WARN_HEADINGBUTSIMPLEORDERMETHOD => "??: ??????, 'ordermethod' ?? 'headingmode=$0' ?????? ??: '$1'? ??: ?????$1 'headingmode' ??,??????? 'ordermethod' ?????????????????????? 'ordermethod=category,<i>comp</i>' (<i>comp</i>???????) ?",
            /**
             * $0: 'debug' value
             */
            'dpl2_log_' . self::WARN_DEBUGPARAMNOTFIRST => "??: 'debug=$0' ??????DPL??????????????????????????????????",

            // DEBUG
            /**
             * $0: SQL query executed to generate the dynamic page list
             */
            'dpl2_log_' . self::DEBUG_QUERY => '??: <code>$0</code>',

            /*
               Output formatting
             */
            /**
             * $1: number of articles
             */
            'dpl2_articlecount' => '???????$1???'
        );
        self::$messages['zh-hk'] = self::$messages['zh-tw'];
        self::$messages['zh-sg'] = self::$messages['zh-cn'];
        return self::$messages;
    }
    
    $messages = DPL2_i18n::getMessages();
}

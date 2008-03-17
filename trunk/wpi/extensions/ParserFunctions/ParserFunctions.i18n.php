<?php
/**
 * Internationalisation file for extension ParserFunctions.
 *
 * @addtogroup Extensions
*/

$messages = array();

$messages['en'] = array(
    'pfunc_desc'                            => 'Enhance parser with logical functions',
    'pfunc_time_error'                      => 'Error: invalid time',
    'pfunc_time_too_long'                   => 'Error: too many #time calls',
    'pfunc_rel2abs_invalid_depth'           => 'Error: Invalid depth in path: "$1" (tried to access a node above the root node)',
    'pfunc_expr_stack_exhausted'            => 'Expression error: Stack exhausted',
    'pfunc_expr_unexpected_number'          => 'Expression error: Unexpected number',
    'pfunc_expr_preg_match_failure'         => 'Expression error: Unexpected preg_match failure',
    'pfunc_expr_unrecognised_word'          => 'Expression error: Unrecognised word "$1"',
    'pfunc_expr_unexpected_operator'        => 'Expression error: Unexpected $1 operator',
    'pfunc_expr_missing_operand'            => 'Expression error: Missing operand for $1',
    'pfunc_expr_unexpected_closing_bracket' => 'Expression error: Unexpected closing bracket',
    'pfunc_expr_unrecognised_punctuation'   => 'Expression error: Unrecognised punctuation character "$1"',
    'pfunc_expr_unclosed_bracket'           => 'Expression error: Unclosed bracket',
    'pfunc_expr_division_by_zero'           => 'Division by zero',
    'pfunc_expr_unknown_error'              => 'Expression error: Unknown error ($1)',
    'pfunc_expr_not_a_number'               => 'In $1: result is not a number',
    'pfunc_ifexist_warning'                 => 'Warning: This page contains too many #ifexist calls. It should have less than $2, there are now $1.',
    'pfunc_max_ifexist_category'            => 'Pages with too many ifexist calls',
);

/** Aragonese (AragonÃ©s)
 * @author Juanpabl
 */
$messages['an'] = array(
    'pfunc_time_error'                      => 'Error: tiempo incorreuto',
    'pfunc_time_too_long'                   => 'Error: masiadas cridas #time',
    'pfunc_rel2abs_invalid_depth'           => 'Error: Fondura incorreuta en o path: "$1" (prebÃ³ d\'azeder ta un nodo por denzima d\'o nodo radiz)',
    'pfunc_expr_stack_exhausted'            => "Error d'espresiÃ³n: Pila acotolada",
    'pfunc_expr_unexpected_number'          => "Error d'espresiÃ³n: numbero no asperato",
    'pfunc_expr_preg_match_failure'         => "Error d'espresiÃ³n: fallo de preg_match no asperato",
    'pfunc_expr_unrecognised_word'          => 'Error d\'espresiÃ³n: palabra "$1" no reconoixita',
    'pfunc_expr_unexpected_operator'        => "Error d'espresiÃ³n: operador $1 no asperato",
    'pfunc_expr_missing_operand'            => "Error d'espresiÃ³n: Ã¡ $1 li falta un operando",
    'pfunc_expr_unexpected_closing_bracket' => "Error d'espresiÃ³n: zarradura d'o gafet no asperata",
    'pfunc_expr_unrecognised_punctuation'   => 'Error d\'espresiÃ³n: carÃ¡uter de puntuaziÃ³n "$1" no reconoixito',
    'pfunc_expr_unclosed_bracket'           => "Error d'espresiÃ³n: gafet sin zarrar",
    'pfunc_expr_division_by_zero'           => 'DibisiÃ³n por zero',
    'pfunc_expr_unknown_error'              => "Error d'espresiÃ³n: error esconoixito ($1)",
    'pfunc_expr_not_a_number'               => 'En $1: o resultau no ye un numero',
    'pfunc_ifexist_warning'                 => 'Pare cuenta: ista pachina contiene masiadas cridas #ifexist. Bi ha $1, y caldrÃ­a que tenese menos de $2',
    'pfunc_max_ifexist_category'            => 'Pachinas con masiadas cridas ifexist',
);

/** Arabic (Ø§ÙØ¹Ø±Ø¨ÙØ©)
 * @author Meno25
 * @author Siebrand
 */
$messages['ar'] = array(
    'pfunc_desc'                            => 'Ø¨Ø§Ø±Ø³Ø± ÙÙØ¯Ø¯ Ø¨Ø¯ÙØ§Ù ÙÙØ·ÙÙØ©',
    'pfunc_time_error'                      => 'Ø®Ø·Ø£: Ø²ÙÙ ØºÙØ± ØµØ­ÙØ­',
    'pfunc_time_too_long'                   => 'Ø®Ø·Ø£: too many #time calls',
    'pfunc_rel2abs_invalid_depth'           => 'Ø®Ø·Ø£: Ø¹ÙÙ ØºÙØ± ØµØ­ÙØ­ ÙÙ Ø§ÙÙØ³Ø§Ø±: "$1" (Ø­Ø§ÙÙ Ø¯Ø®ÙÙ Ø¹ÙØ¯Ø© ÙÙÙ Ø§ÙØ¹ÙØ¯Ø© Ø§ÙØ¬Ø°Ø±ÙØ©)',
    'pfunc_expr_stack_exhausted'            => 'Ø®Ø·Ø£ ÙÙ Ø§ÙØªØ¹Ø¨ÙØ±: Ø³ØªØ§Ù ÙØ¬ÙØ¯',
    'pfunc_expr_unexpected_number'          => 'Ø®Ø·Ø£ ÙÙ Ø§ÙØªØ¹Ø¨ÙØ±: Ø±ÙÙ ØºÙØ± ÙØªÙÙØ¹',
    'pfunc_expr_preg_match_failure'         => 'Ø®Ø·Ø£ ÙÙ Ø§ÙØªØ¹Ø¨ÙØ±: ÙØ´Ù preg_match ØºÙØ± ÙØªÙÙØ¹',
    'pfunc_expr_unrecognised_word'          => 'Ø®Ø·Ø£ ÙÙ Ø§ÙØªØ¹Ø¨ÙØ±: ÙÙÙØ© ØºÙØ± ÙØªØ¹Ø±Ù Ø¹ÙÙÙØ§ "$1"',
    'pfunc_expr_unexpected_operator'        => 'Ø®Ø·Ø£ ÙÙ Ø§ÙØªØ¹Ø¨ÙØ±: Ø¹Ø§ÙÙ $1 ØºÙØ± ÙØªÙÙØ¹',
    'pfunc_expr_missing_operand'            => 'Ø®Ø·Ø£ ÙÙ Ø§ÙØªØ¹Ø¨ÙØ±: operand ÙÙÙÙØ¯ Ù$1',
    'pfunc_expr_unexpected_closing_bracket' => 'Ø®Ø·Ø£ ÙÙ Ø§ÙØªØ¹Ø¨ÙØ±: ÙÙØ³ Ø¥ØºÙØ§Ù ØºÙØ± ÙØªÙÙØ¹',
    'pfunc_expr_unrecognised_punctuation'   => 'Ø®Ø·Ø£ ÙÙ Ø§ÙØªØ¹Ø¨ÙØ±: Ø¹ÙØ§ÙØ© ØªØ±ÙÙÙ ØºÙØ± ÙØªØ¹Ø±Ù Ø¹ÙÙÙØ§ "$1"',
    'pfunc_expr_unclosed_bracket'           => 'Ø®Ø·Ø£ ÙÙ Ø§ÙØªØ¹Ø¨ÙØ±: ÙÙØ³ ØºÙØ± ÙØºÙÙ',
    'pfunc_expr_division_by_zero'           => 'Ø§ÙÙØ³ÙØ© Ø¹ÙÙ ØµÙØ±',
    'pfunc_expr_unknown_error'              => 'Ø®Ø·Ø£ ÙÙ Ø§ÙØªØ¹Ø¨ÙØ±: Ø®Ø·Ø£ ØºÙØ± ÙØ¹Ø±ÙÙ ($1)',
    'pfunc_expr_not_a_number'               => 'ÙÙ $1: Ø§ÙÙØªÙØ¬Ø© ÙÙØ³Øª Ø±ÙÙØ§',
    'pfunc_ifexist_warning'                 => 'ØªØ­Ø°ÙØ±: ÙØ°Ù Ø§ÙÙÙØ§ÙØ© ØªØ­ØªÙÙ Ø¹ÙÙ #ifexist calls ÙØ«ÙØ±Ø© Ø¬Ø¯Ø§. ÙÙØ¨ØºÙ Ø£Ù ØªØ­ØªÙÙ Ø¹ÙÙ Ø£ÙÙ ÙÙ $2Ø ÙÙØ¬Ø¯ Ø§ÙØ¢Ù $1.',
    'pfunc_max_ifexist_category'            => 'Ø§ÙØµÙØ­Ø§Øª Ø§ÙØªÙ ØªØ­ØªÙÙ Ø¹ÙÙ ifexist calls ÙØ«ÙØ±Ø© Ø¬Ø¯Ø§',
);

/** Asturian (Asturianu)
 * @author Esbardu
 */
$messages['ast'] = array(
    'pfunc_time_error'                      => 'Error: tiempu non vÃ¡lidu',
    'pfunc_time_too_long'                   => 'Error: demasiaes llamaes #time',
    'pfunc_rel2abs_invalid_depth'           => 'Error: Nivel de subdireutoriu non vÃ¡lidu: "$1" (intentu d\'accesu penriba del direutoriu raÃ­z)',
    'pfunc_expr_stack_exhausted'            => "Error d'espresiÃ³n: Pila escosada",
    'pfunc_expr_unexpected_number'          => "Error d'espresiÃ³n: NÃºmberu inesperÃ¡u",
    'pfunc_expr_preg_match_failure'         => "Error d'espresiÃ³n: Fallu inesperÃ¡u de preg_match",
    'pfunc_expr_unrecognised_word'          => 'Error d\'espresiÃ³n: Pallabra "$1" non reconocida',
    'pfunc_expr_unexpected_operator'        => "Error d'espresiÃ³n: Operador $1 inesperÃ¡u",
    'pfunc_expr_missing_operand'            => "Error d'espresiÃ³n: Falta operador en $1",
    'pfunc_expr_unexpected_closing_bracket' => "Error d'espresiÃ³n: ParÃ©ntesis final inesperÃ¡u",
    'pfunc_expr_unrecognised_punctuation'   => 'Error d\'espresiÃ³n: CarÃ¡uter de puntuaciÃ³n "$1" non reconocÃ­u',
    'pfunc_expr_unclosed_bracket'           => "Error d'espresiÃ³n: ParÃ©ntesis non zarrÃ¡u",
    'pfunc_expr_division_by_zero'           => 'DivisiÃ³n por cero',
    'pfunc_expr_unknown_error'              => "Error d'espresiÃ³n: Error desconocÃ­u ($1)",
    'pfunc_expr_not_a_number'               => 'En $1: el resultÃ¡u nun ye un nÃºmberu',
    'pfunc_ifexist_warning'                 => 'Avisu: Esta pÃ¡xina contiÃ©n demasiaes llamaes #ifexist. HabrÃ­a tener menos de $2, y tien anguaÃ±o $1.',
    'pfunc_max_ifexist_category'            => 'PÃ¡xines con demasiaes llamaes ifexist',
);

/** Bulgarian (ÐÑÐ»Ð³Ð°ÑÑÐºÐ¸)
 * @author Spiritia
 * @author DCLXVI
 */
$messages['bg'] = array(
    'pfunc_time_error'                      => 'ÐÑÐµÑÐºÐ°: Ð½ÐµÐ²Ð°Ð»Ð¸Ð´Ð½Ð¾ Ð²ÑÐµÐ¼Ðµ',
    'pfunc_time_too_long'                   => 'ÐÑÐµÑÐºÐ°: Ð¢Ð²ÑÑÐ´Ðµ Ð¼Ð½Ð¾Ð³Ð¾ Ð¸Ð·Ð²Ð¸ÐºÐ²Ð°Ð½Ð¸Ñ Ð½Ð° #time',
    'pfunc_expr_stack_exhausted'            => 'ÐÑÐµÑÐºÐ° Ð² Ð·Ð°Ð¿Ð¸ÑÐ°: Ð¡ÑÐµÐºÑÑ Ðµ Ð¸Ð·ÑÐµÑÐ¿Ð°Ð½',
    'pfunc_expr_unexpected_number'          => 'ÐÑÐµÑÐºÐ° Ð² Ð·Ð°Ð¿Ð¸ÑÐ°: ÐÐµÐ¾ÑÐ°ÐºÐ²Ð°Ð½Ð¾ ÑÐ¸ÑÐ»Ð¾',
    'pfunc_expr_unrecognised_word'          => 'ÐÑÐµÑÐºÐ° Ð² Ð·Ð°Ð¿Ð¸ÑÐ°: ÐÐµÑÐ°Ð·Ð¿Ð¾Ð·Ð½Ð°ÑÐ° Ð´ÑÐ¼Ð° "$1"',
    'pfunc_expr_unexpected_operator'        => 'ÐÑÐµÑÐºÐ° Ð² Ð·Ð°Ð¿Ð¸ÑÐ°: ÐÐµÐ¾ÑÐ°ÐºÐ²Ð°Ð½ Ð¾Ð¿ÐµÑÐ°ÑÐ¾Ñ $1',
    'pfunc_expr_missing_operand'            => 'ÐÑÐµÑÐºÐ° Ð² Ð·Ð°Ð¿Ð¸ÑÐ°: ÐÐ¸Ð¿ÑÐ²Ð°Ñ Ð¾Ð¿ÐµÑÐ°Ð½Ð´ Ð² $1',
    'pfunc_expr_unexpected_closing_bracket' => 'ÐÑÐµÑÐºÐ° Ð² Ð·Ð°Ð¿Ð¸ÑÐ°: ÐÐ´Ð½Ð° Ð·Ð°ÑÐ²Ð°ÑÑÑÐ° ÑÐºÐ¾Ð±Ð° Ð² Ð¿Ð¾Ð²ÐµÑÐµ',
    'pfunc_expr_unrecognised_punctuation'   => 'ÐÑÐµÑÐºÐ° Ð² Ð·Ð°Ð¿Ð¸ÑÐ°: ÐÐµÑÐ°Ð·Ð¿Ð¾Ð·Ð½Ð°Ñ Ð¿ÑÐ½ÐºÑÑÐ°ÑÐ¸Ð¾Ð½ÐµÐ½ Ð·Ð½Ð°Ðº "$1"',
    'pfunc_expr_unclosed_bracket'           => 'ÐÑÐµÑÐºÐ° Ð² Ð·Ð°Ð¿Ð¸ÑÐ°: ÐÐµÐ·Ð°ÑÐ²Ð¾ÑÐµÐ½Ð° ÑÐºÐ¾Ð±Ð°',
    'pfunc_expr_division_by_zero'           => 'ÐÐµÐ»ÐµÐ½Ð¸Ðµ Ð½Ð° Ð½ÑÐ»Ð°',
    'pfunc_expr_unknown_error'              => 'ÐÑÐµÑÐºÐ° Ð² Ð·Ð°Ð¿Ð¸ÑÐ°: ÐÐµÑÐ°Ð·Ð¿Ð¾Ð·Ð½Ð°ÑÐ° Ð³ÑÐµÑÐºÐ° ($1)',
    'pfunc_expr_not_a_number'               => 'Ð $1: ÑÐµÐ·ÑÐ»ÑÐ°ÑÑÑ Ð½Ðµ Ðµ ÑÐ¸ÑÐ»Ð¾',
    'pfunc_ifexist_warning'                 => 'ÐÐ½Ð¸Ð¼Ð°Ð½Ð¸Ðµ: Ð¢Ð°Ð·Ð¸ ÑÑÑÐ°Ð½Ð¸ÑÐ° ÑÑÐ´ÑÑÐ¶Ð° ÑÐ²ÑÑÐ´Ðµ Ð¼Ð½Ð¾Ð³Ð¾ Ð¸Ð·Ð²Ð¸ÐºÐ²Ð°Ð½Ð¸Ñ Ð½Ð° #ifexist. ÐÑÐ¾ÑÑ Ð¸Ð¼ ÑÐµÐ³Ð° Ðµ $1, Ð° ÑÑÑÐ±Ð²Ð° Ð´Ð° Ð±ÑÐ´Ð°Ñ Ð½Ðµ Ð¿Ð¾Ð²ÐµÑÐµ Ð¾Ñ $2.',
    'pfunc_max_ifexist_category'            => 'Ð¡ÑÑÐ°Ð½Ð¸ÑÐ¸ Ñ ÑÐ²ÑÑÐ´Ðµ Ð¼Ð½Ð¾Ð³Ð¾ Ð¸Ð·Ð²Ð¸ÐºÐ²Ð°Ð½Ð¸Ñ Ð½Ð° #ifexist',
);

/** Bengali (à¬à¾àà²à¾)
 * @author Zaheen
 * @author Bellayet
 */
$messages['bn'] = array(
    'pfunc_desc'                            => 'à²àà¿àà¾à² à«à¾àà¶à¨ à¦à¿à¯à¼à àªà¾à°àà¸à¾à°àà àà¨àà¨à¤ àà°àà¨',
    'pfunc_time_error'                      => 'à¤àà°ààà¿: àà¬àà§ à¸à®à¯à¼',
    'pfunc_time_too_long'                   => 'à¤àà°ààà¿: àà¤àà¯à§à¿à à¸àààà¯à #time àà²',
    'pfunc_rel2abs_invalid_depth'           => 'à¤àà°ààà¿: àªà¾à¥à àà¬àà§ àà­àà°à¤à¾: "$1" (à®àà² à¨àà¡àà° ààªà°àà° àààà¿ à¨àà¡ ààà¯à¾ààà¸àà¸ àà°à¤à ààà·ààà¾ àà°ààà¿à²)',
    'pfunc_expr_stack_exhausted'            => 'àààà¸àªàà°àà¶à¨ à¤àà°ààà¿: à¸àààà¯à¾à à¶àà· à¹à¯à¼à àààà',
    'pfunc_expr_unexpected_number'          => 'àààà¸àªàà°àà¶à¨ à¤àà°ààà¿: àà¯à¾àà¿à¤ à¸àààà¯à¾',
    'pfunc_expr_preg_match_failure'         => 'àààà¸àªàà°àà¶à¨ à¤àà°ààà¿: àà¯à¾àà¿à¤ preg_match à¬àà¯à°àà¥à¤à¾',
    'pfunc_expr_unrecognised_word'          => 'àààà¸àªàà°àà¶à¨ à¤àà°ààà¿: ààªà°à¿àà¿à¤ à¶à¬àà¦ "$1"',
    'pfunc_expr_unexpected_operator'        => 'àààà¸àªàà°àà¶à¨ à¤àà°ààà¿: àà¯à¾àà¿à¤ $1 ààªà¾à°ààà°',
    'pfunc_expr_missing_operand'            => 'àààà¸àªàà°àà¶à¨ à¤àà°ààà¿: $1-àà° àà¨àà¯ ààªà¾à°àà¨àà¡ à¨ààà¤',
    'pfunc_expr_unexpected_closing_bracket' => 'àààà¸àªàà°àà¶à¨ à¤àà°ààà¿: àà¯à¾àà¿à¤ à¸à®à¾àªàà¤àà¾à°à à¬à¨àà§à¨à',
    'pfunc_expr_unrecognised_punctuation'   => 'àààà¸àªàà°àà¶à¨ à¤àà°ààà¿: ààªà°à¿àà¿à¤ à¬à¿à°à¾à®àà¿à¹àà¨ ààà¯à¾à°ààààà¾à° "$1"',
    'pfunc_expr_unclosed_bracket'           => 'àààà¸àªàà°àà¶à¨ à¤àà°ààà¿: àà¨àà®àààà¤ à¬à¨àà§à¨à',
    'pfunc_expr_division_by_zero'           => 'à¶àà¨àà¯ à¦àà¬à¾à°à¾ à­à¾à àà°à¾ à¹à¯à¼ààà',
    'pfunc_expr_unknown_error'              => 'àààà¸àªàà°àà¶à¨ à¤àà°ààà¿: ààà¾à¨à¾ à¤àà°ààà¿ ($1)',
    'pfunc_expr_not_a_number'               => '$1: à à«à²à¾à«à² ààà¨ à¸àààà¯à¾ à¨à¯à¼',
    'pfunc_ifexist_warning'                 => 'à¸à¤à°ààà¿àà°à£: àà àªà¾à¤à¾à¯à¼ ààà¬ à¬àà¶à¿ ifexist à¡à¾àà¾(call) àà°à¾ à¹à¯à¼àààà¤ à¯à¾à° à¸àààà¯à¾ $2 àà° à¹àà¯à¼à¾ ààà¿à¤ àà¿à², ààà¨ ààà $1à¤',
    'pfunc_max_ifexist_category'            => 'ààà¬ à¬àà¶à¿ ifexist  à¡à¾àà¾(call) àà°à¾ à¹à¯à¼ààà àà®à¨ àªà¾à¤à¾à¸à®àà¹',
);

/** Catalan (CatalÃ )
 * @author SMP
 */
$messages['ca'] = array(
    'pfunc_time_error'                      => 'Error: temps invÃ lid',
    'pfunc_time_too_long'                   => 'Error: massa crides #time',
    'pfunc_rel2abs_invalid_depth'           => "Error: AdreÃ§a invÃ lida al directori: Â«$1Â» (s'intentava accedir a un node superior de l'arrel)",
    'pfunc_expr_stack_exhausted'            => "Error de l'expressiÃ³: Pila exhaurida",
    'pfunc_expr_unexpected_number'          => "Error de l'expressiÃ³: Nombre inesperat",
    'pfunc_expr_preg_match_failure'         => "Error de l'expressiÃ³: Error de funciÃ³ no compresa i inesperada",
    'pfunc_expr_unrecognised_word'          => 'Error de l\'expressiÃ³: Paraula no reconeguda "$1"',
    'pfunc_expr_unexpected_operator'        => "Error de l'expressiÃ³: Operador $1 inesperat",
    'pfunc_expr_missing_operand'            => "Error de l'expressiÃ³: Falta l'operand de $1",
    'pfunc_expr_unexpected_closing_bracket' => "Error de l'expressiÃ³: ParÃ¨ntesi inesperat",
    'pfunc_expr_unrecognised_punctuation'   => 'Error de l\'expressiÃ³: Signe de puntuaciÃ³ no reconegut "$1"',
    'pfunc_expr_unclosed_bracket'           => "Error de l'expressiÃ³: ParÃ¨ntesi no tancat",
    'pfunc_expr_division_by_zero'           => 'DivisiÃ³ entre zero',
    'pfunc_expr_unknown_error'              => "Error de l'expressiÃ³: Desconegut ($1)",
    'pfunc_expr_not_a_number'               => 'A $1: el resultat no Ã©s un nombre',
    'pfunc_ifexist_warning'                 => "Alerta: Aquesta pÃ gina contÃ© massa crides #ifexist. N'hi hauria d'haver menys de $2 mentre que ara n'hi ha $1.",
    'pfunc_max_ifexist_category'            => 'PÃ gines amb massa crides ifexist',
);

/** Czech (Äesky)
 * @author Li-sung
 * @author Danny B.
 * @author Siebrand
 * @author MatÄj GrabovskÃ½
 */
$messages['cs'] = array(
    'pfunc_desc'                            => 'RozÅ¡Ã­ÅenÃ­ syntaktickÃ©ho analyzÃ¡toru o logickÃ© funkce',
    'pfunc_time_error'                      => 'Chyba: neplatnÃ½ Äas',
    'pfunc_time_too_long'                   => 'Chyba: pÅÃ­liÅ¡ mnoho volÃ¡nÃ­ #time',
    'pfunc_rel2abs_invalid_depth'           => 'Chyba: NeplatnÃ¡ hloubka v cestÄ: "$1" (pokus o pÅÃ­stup do uzlu vyÅ¡Å¡Ã­ho neÅ¾ koÅen)',
    'pfunc_expr_stack_exhausted'            => 'Chyba ve vÃ½razu: ZÃ¡sobnÃ­k plnÄ obsazen',
    'pfunc_expr_unexpected_number'          => 'Chyba ve vÃ½razu: OÄekÃ¡vÃ¡no ÄÃ­slo',
    'pfunc_expr_preg_match_failure'         => 'Chyba ve vÃ½razu: NeoÄekÃ¡vanÃ¡ chyba funkce preg_match',
    'pfunc_expr_unrecognised_word'          => 'Chyba ve vÃ½razu: NerozpoznanÃ© slovo Ââ$1â',
    'pfunc_expr_unexpected_operator'        => 'Chyba ve vÃ½razu: NeoÄekÃ¡vanÃ½ operÃ¡tor $1',
    'pfunc_expr_missing_operand'            => 'Chyba ve vÃ½razu: ChybÃ­ operand pro $1',
    'pfunc_expr_unexpected_closing_bracket' => 'Chyba ve vÃ½razu: NeoÄekÃ¡vanÃ¡ uzavÃ­racÃ­ zÃ¡vorka',
    'pfunc_expr_unrecognised_punctuation'   => 'Chyba ve vÃ½razu: NerozpoznanÃ½ interpunkÄnÃ­ znak Ââ$1â',
    'pfunc_expr_unclosed_bracket'           => 'Chyba ve vÃ½razu: NeuzavÅenÃ© zÃ¡vorky',
    'pfunc_expr_division_by_zero'           => 'DÄlenÃ­ nulou',
    'pfunc_expr_unknown_error'              => 'Chyba ve vÃ½razu: NeznÃ¡mÃ¡ chyba ($1)',
    'pfunc_expr_not_a_number'               => 'V $1: vÃ½sledkem nenÃ­ ÄÃ­slo',
    'pfunc_ifexist_warning'                 => 'VarovÃ¡nÃ­: Tato strÃ¡nka obsahuje pÅÃ­liÅ¡ mnoho volÃ¡nÃ­ funkce #ifexist. MÄlo by jich bÃ½t mÃ©nÄ neÅ¾ $2, momentÃ¡lnÄ jich je $1.',
    'pfunc_max_ifexist_category'            => 'StrÃ¡nky s pÅÃ­liÅ¡ mnoha volÃ¡nÃ­mi funkce ifexist',
);

/** Danish (Dansk)
 * @author Morten
 */
$messages['da'] = array(
    'pfunc_desc'                            => 'Udvidet parser med logiske funktioner',
    'pfunc_time_error'                      => 'Fejl: Ugyldig tid',
    'pfunc_time_too_long'                   => 'Felj: for mange #time kald',
    'pfunc_expr_stack_exhausted'            => 'Udtryksfejl: Stack tÃ¸mt',
    'pfunc_expr_unexpected_number'          => 'Fejl: Uventet nummer',
    'pfunc_expr_preg_match_failure'         => 'Udtryksfejl: Uventet preg_match fejl',
    'pfunc_expr_unrecognised_word'          => 'Udtryksfejl: Uventet ord "$1"',
    'pfunc_expr_unexpected_operator'        => 'Udtryksfejl: Uventet $1 operator',
    'pfunc_expr_missing_operand'            => 'Udtryksfejl: Manglende operand til $1',
    'pfunc_expr_unexpected_closing_bracket' => 'Udtryksfejl: Uventet "]"-tegn',
    'pfunc_expr_unrecognised_punctuation'   => 'Udtryksfejl: Uventet tegnsÃ¦tning-tegn: "$1"',
    'pfunc_expr_unclosed_bracket'           => 'Udtryksfejl: Uafsluttet kantet parantes',
    'pfunc_expr_division_by_zero'           => 'Division med nul',
    'pfunc_expr_unknown_error'              => 'Udtryksfejl: Ukendt fejl ($1)',
    'pfunc_expr_not_a_number'               => 'I $1: Resultatet er ikke et tal',
    'pfunc_ifexist_warning'                 => 'Advarsel: Denne side indeholder for mange #ifexist kald. Den skulle have under $2 men den har nu $1.',
    'pfunc_max_ifexist_category'            => 'Sider med for mange ifexist kald',
);

/**  German (Deutsch)
 * @author Raimond Spekking
 */
$messages['de'] = array(
    'pfunc_desc'                            => 'Erweitert den Parser um logische Funktionen',
    'pfunc_time_error'                      => 'Fehler: ungÃ¼ltige Zeitangabe',
    'pfunc_time_too_long'                   => 'Fehler: zu viele #time-Aufrufe',
    'pfunc_rel2abs_invalid_depth'           => 'Fehler: ungÃ¼ltige Tiefe in Pfad: â$1â (Versuch, auf einen Knotenpunkt oberhalb des Hauptknotenpunktes zuzugreifen)',
    'pfunc_expr_stack_exhausted'            => 'Expression-Fehler: StackÃ¼berlauf',
    'pfunc_expr_unexpected_number'          => 'Expression-Fehler: Unerwartete Zahl',
    'pfunc_expr_preg_match_failure'         => 'Expression-Fehler: Unerwartete âpreg_matchâ-Fehlfunktion',
    'pfunc_expr_unrecognised_word'          => 'Expression-Fehler: Unerkanntes Wort â$1â',
    'pfunc_expr_unexpected_operator'        => 'Expression-Fehler: Unerwarteter Operator: <strong><tt>$1</tt></strong>',
    'pfunc_expr_missing_operand'            => 'Expression-Fehler: Fehlender Operand fÃ¼r <strong><tt>$1</tt></strong>',
    'pfunc_expr_unexpected_closing_bracket' => 'Expression-Fehler: Unerwartete schlieÃende eckige Klammer',
    'pfunc_expr_unrecognised_punctuation'   => 'Expression-Fehler: Unerkanntes Satzzeichen â$1â',
    'pfunc_expr_unclosed_bracket'           => 'Expression-Fehler: Nicht geschlossene eckige Klammer',
    'pfunc_expr_division_by_zero'           => 'Expression-Fehler: Division durch Null',
    'pfunc_expr_unknown_error'              => 'Expression-Fehler: Unbekannter Fehler ($1)',
    'pfunc_expr_not_a_number'               => 'Expression-Fehler: In $1: Ergebnis ist keine Zahl',
    'pfunc_ifexist_warning'                 => 'Warnung: Diese Seite enthÃ¤lt zuviele #ifexist-Aufrufe. Es dÃ¼rfen maximal $2 Aufrufe sein, es sind aber $1 Aufrufe.',
    'pfunc_max_ifexist_category'            => 'Seiten mit zuvielen ifexist-Aufrufen',
);

/** Greek (ÎÎ»Î»Î·Î½Î¹ÎºÎ¬)
 * @author ÎÏÎµÏÎ³ÏÏ
 * @author Consta
 */
$messages['el'] = array(
    'pfunc_time_error'           => 'Î£ÏÎ¬Î»Î¼Î±: Î¬ÎºÏÏÎ¿Ï ÏÏÏÎ½Î¿Ï',
    'pfunc_time_too_long'        => 'Î£ÏÎ¬Î»Î¼Î±: ÏÎ¬ÏÎ± ÏÎ¿Î»Î»Î­Ï ÎºÎ»Î®ÏÎµÎ¹Ï ÏÎ·Ï #time',
    'pfunc_ifexist_warning'      => 'Î ÏÎ¿ÎµÎ¹Î´Î¿ÏÎ¿Î¯Î·ÏÎ·: ÎÏÏÎ® Î· ÏÎµÎ»Î¯Î´Î± ÏÎµÏÎ¹Î­ÏÎµÎ¹ ÏÎ¬ÏÎ± ÏÎ¿Î»Î»Î­Ï ÎºÎ»Î®ÏÎµÎ¹Ï ÏÎ·Ï #ifexist.  ÎÎ± Î­ÏÏÎµÏÎµ Î½Î± Î­ÏÎµÎ¹ Î»Î¹Î³ÏÏÎµÏÎµÏ Î±ÏÏ $2, ÎºÎ±Î¸ÏÏ ÏÏÏÎ± Î­ÏÎµÎ¹ $1.',
    'pfunc_max_ifexist_category' => 'Î£ÎµÎ»Î¯Î´ÎµÏ Î¼Îµ ÏÎ¬ÏÎ± ÏÎ¿Î»Î»Î­Ï ÎºÎ»Î®ÏÎµÎ¹Ï ÏÎ·Ï ifexist',
);

/** Esperanto (Esperanto)
 * @author Yekrats
 */
$messages['eo'] = array(
    'pfunc_expr_division_by_zero' => 'Divido per nulo',
);

/** Basque (Euskara)
 * @author SPQRobin
 */
$messages['eu'] = array(
    'pfunc_time_error'            => 'Errorea: baliogabeko ordua',
    'pfunc_time_too_long'         => 'Errorea: #time dei gehiegi',
    'pfunc_rel2abs_invalid_depth' => 'Errorea: Baliogabeko sakonera fitxategi bidean: "$1" (root puntutik gora sartzen saiatu da)',
);

/** ÙØ§Ø±Ø³Û (ÙØ§Ø±Ø³Û)
 * @author Huji
 */
$messages['fa'] = array(
    'pfunc_desc'                            => 'Ø¨Ù ØªØ¬Ø²ÛÙâÚ©ÙÙØ¯ÙØ Ø¯Ø³ØªÙØ±ÙØ§Û ÙÙØ·ÙÛ ÙÛâØ§ÙØ²Ø§ÛØ¯',
    'pfunc_time_error'                      => 'Ø®Ø·Ø§: Ø²ÙØ§Ù ØºÛØ±ÙØ¬Ø§Ø²',
    'pfunc_time_too_long'                   => 'Ø®Ø·Ø§: ÙØ±Ø§Ø®ÙØ§ÙÛ Ø¨ÛØ´ Ø§Ø² Ø­Ø¯ #time',
    'pfunc_rel2abs_invalid_depth'           => 'Ø®Ø·Ø§: Ø¹ÙÙ ØºÛØ± ÙØ¬Ø§Ø² Ø¯Ø± ÙØ´Ø§ÙÛ Â«$1Â» (ØªÙØ§Ø´ Ø¨Ø±Ø§Û Ø¯Ø³ØªØ±Ø³Û Ø¨Ù ÛÚ© ÙØ´Ø§ÙÛ ÙØ±Ø§ØªØ± Ø§Ø² ÙØ´Ø§ÙÛ Ø±ÛØ´Ù)',
    'pfunc_expr_stack_exhausted'            => 'Ø®Ø·Ø§Û Ø¹Ø¨Ø§Ø±Øª: Ù¾Ø´ØªÙ Ø§Ø² Ø¯Ø³Øª Ø±ÙØªÙ',
    'pfunc_expr_unexpected_number'          => 'Ø®Ø·Ø§Û Ø¹Ø¨Ø§Ø±Øª: Ø¹Ø¯Ø¯ Ø¯ÙØ± Ø§Ø² Ø§ÙØªØ¸Ø§Ø±',
    'pfunc_expr_preg_match_failure'         => 'Ø®Ø·Ø§Û Ø¹Ø¨Ø§Ø±Øª: Ø®Ø·Ø§Û preg_match Ø¯ÙØ± Ø§Ø² Ø§ÙØªØ¸Ø§Ø±',
    'pfunc_expr_unrecognised_word'          => 'Ø®Ø·Ø§Û Ø¹Ø¨Ø§Ø±Øª: Ú©ÙÙÙ ÙØ§Ø´ÙØ§Ø®ØªÙ Â«$1Â»',
    'pfunc_expr_unexpected_operator'        => 'Ø®Ø·Ø§Û Ø¹Ø¨Ø§Ø±Øª: Ø¹ÙÙÚ¯Ø± $1 Ø¯ÙØ± Ø§Ø² Ø§ÙØªØ¸Ø§Ø±',
    'pfunc_expr_missing_operand'            => 'Ø®Ø·Ø§Û Ø¹Ø¨Ø§Ø±Øª: Ø¹ÙÙÚ¯Ø± Ú¯ÙØ´Ø¯Ù Ø¨Ø±Ø§Û $1',
    'pfunc_expr_unexpected_closing_bracket' => 'Ø®Ø·Ø§Û Ø¹Ø¨Ø§Ø±Øª: Ù¾Ø±Ø§ÙØªØ² Ø¨Ø³ØªÙ Ø§Ø¶Ø§ÙÛ',
    'pfunc_expr_unrecognised_punctuation'   => 'Ø®Ø·Ø§Û Ø¹Ø¨Ø§Ø±Øª: ÙÙÛØ³Ù ÙÙØ·ÙâÚ¯Ø°Ø§Ø±Û Ø´ÙØ§Ø®ØªÙ ÙØ´Ø¯Ù Â«$1Â»',
    'pfunc_expr_unclosed_bracket'           => 'Ø®Ø·Ø§Û Ø¹Ø¨Ø§Ø±Øª: Ù¾Ø±Ø§ÙØªØ² Ø¨Ø³ØªÙâÙØ´Ø¯Ù',
    'pfunc_expr_division_by_zero'           => 'ØªÙØ³ÛÙ Ø¨Ø± ØµÙØ±',
    'pfunc_expr_unknown_error'              => 'Ø®Ø·Ø§Û Ø¹Ø¨Ø§Ø±Øª: Ø®Ø·Ø§Û ÙØ§Ø´ÙØ§Ø®ØªÙ ($1)',
    'pfunc_expr_not_a_number'               => 'Ø¯Ø± $1: ÙØªÛØ¬Ù Ø¹Ø¯Ø¯ ÙÛØ³Øª',
    'pfunc_ifexist_warning'                 => 'ÙØ´Ø¯Ø§Ø±: Ø§ÛÙ ØµÙØ­Ù Ø­Ø§ÙÛ ÙØ±Ø§Ø®ÙØ§ÙÛâÙØ§Û Ø¨ÛØ´ Ø§Ø² Ø­Ø¯ ifexist Ø§Ø³Øª. Ø­Ø¯Ø§Ú©Ø«Ø± ØªØ¹Ø¯Ø§Ø¯ ÙØ±Ø§Ø®ÙØ§ÙÛ ÙØ¬Ø§Ø² $2 Ø§Ø³ØªØ ØªØ¹Ø¯Ø§Ø¯ Ø¢Ù Ø¯Ø± Ø§ÛÙ ØµÙØ­Ù $1 Ø§Ø³Øª.',
    'pfunc_max_ifexist_category'            => 'ØµÙØ­ÙâÙØ§Û Ø¯Ø§Ø±Ø§Û ÙØ±Ø§Ø®ÙØ§ÙÛ Ø¨ÛØ´ Ø§Ø² Ø­Ø¯ ifexist',

);

/** French (FranÃ§ais)
 * @author Grondin
 * @author Sherbrooke
 * @author Urhixidur
 */
$messages['fr'] = array(
    'pfunc_desc'                            => 'Augmente le parseur avec des fonctions logiques',
    'pfunc_time_error'                      => 'Erreur : durÃ©e invalide',
    'pfunc_time_too_long'                   => 'Erreur : <code>#time</code> appelÃ© trop de fois',
    'pfunc_rel2abs_invalid_depth'           => 'Erreur: niveau de rÃ©pertoire invalide dans le chemin : Â« $1 Â» (a essayÃ© dâaccÃ©der Ã  un niveau au-dessus du rÃ©pertoire racine)',
    'pfunc_expr_stack_exhausted'            => 'Expression erronÃ©e : pile Ã©puisÃ©e',
    'pfunc_expr_unexpected_number'          => 'Expression erronÃ©e : nombre inattendu',
    'pfunc_expr_preg_match_failure'         => 'Expression erronÃ©e : Ã©chec inattendu pour <code>preg_match</code>',
    'pfunc_expr_unrecognised_word'          => "Erreur d'expression : le mot '''$1''' n'est pas reconnu",
    'pfunc_expr_unexpected_operator'        => "Erreur d'expression : l'opÃ©rateur '''$1''' n'est pas reconnu",
    'pfunc_expr_missing_operand'            => "Erreur dâexpression : lâopÃ©rande '''$1''' nâest pas reconnue",
    'pfunc_expr_unexpected_closing_bracket' => 'Erreur dâexpression : parenthÃ¨se fermante inattendue',
    'pfunc_expr_unrecognised_punctuation'   => "Erreur d'expression : caractÃ¨re de ponctuation Â« $1 Â» non reconnu",
    'pfunc_expr_unclosed_bracket'           => 'Erreur dâexpression : parenthÃ¨se non fermÃ©e',
    'pfunc_expr_division_by_zero'           => 'Division par zÃ©ro',
    'pfunc_expr_unknown_error'              => "Erreur d'expression : erreur inconnue ($1)",
    'pfunc_expr_not_a_number'               => "Dans $1 : le rÃ©sultat n'est pas un nombre",
    'pfunc_ifexist_warning'                 => "Attention : Cette page contient trop d'appels Ã  <code>#ifexist</code>. Elle devrait en avoir moins que $2, alors qu'elle en a $1.",
    'pfunc_max_ifexist_category'            => "Pages avec trop d'appels Ã  <code>#ifexist</code>",
);

/** Franco-ProvenÃ§al (Arpetan)
 * @author ChrisPtDe
 */
$messages['frp'] = array(
    'pfunc_desc'                            => 'Ãgmente lo parsor avouÃ©c des fonccions logiques.',
    'pfunc_time_error'                      => 'Ãrror : durÃ¢ envalida',
    'pfunc_time_too_long'                   => 'Ãrror : parsÃ¨r #time apelÃ¢ trop de cÃ´ps',
    'pfunc_rel2abs_invalid_depth'           => 'Ãrror : nivÃ´ de rÃ¨pÃ¨rtouÃ¨ro envalido dens lo chemin : Â« $1 Â» (at tÃ¢chiÃª dâarrevar a un nivÃ´ en-dessus du rÃ¨pÃ¨rtouÃ¨ro racena)',
    'pfunc_expr_stack_exhausted'            => 'ÃxprÃ¨ssion fÃ´ssa : pila Ã¨pouesiÃª',
    'pfunc_expr_unexpected_number'          => 'ÃxprÃ¨ssion fÃ´ssa : nombro emprÃ¨vu',
    'pfunc_expr_preg_match_failure'         => 'ÃxprÃ¨ssion fÃ´ssa : falyita emprÃ¨vua por <code>preg_match</code>',
    'pfunc_expr_unrecognised_word'          => "Ãrror dâÃ¨xprÃ¨ssion : lo mot '''$1''' est pas recognu",
    'pfunc_expr_unexpected_operator'        => "Ãrror dâÃ¨xprÃ¨ssion : lâopÃ¨rator '''$1''' est pas recognu",
    'pfunc_expr_missing_operand'            => "Ãrror dâÃ¨xprÃ¨ssion : lâopÃ¨randa '''$1''' est pas recognua",
    'pfunc_expr_unexpected_closing_bracket' => 'Ãrror dâÃ¨xprÃ¨ssion : parentÃ¨sa cllosenta emprÃ¨vua',
    'pfunc_expr_unrecognised_punctuation'   => 'Ãrror dâÃ¨xprÃ¨ssion : caractÃ¨ro de ponctuacion Â« $1 Â» pas recognu',
    'pfunc_expr_unclosed_bracket'           => 'Ãrror dâÃ¨xprÃ¨ssion : parentÃ¨sa pas cllÃ´sa',
    'pfunc_expr_division_by_zero'           => 'Division per zÃ©rÃ´',
    'pfunc_expr_unknown_error'              => 'Ãrror dâÃ¨xprÃ¨ssion : Ã¨rror encognua ($1)',
    'pfunc_expr_not_a_number'               => 'Dens $1 : lo rÃ¨sultat est pas un nombro',
    'pfunc_ifexist_warning'                 => 'Atencion : ceta pÃ¢ge contint trop dâapÃ¨ls a <code>#ifexist</code>. DevrÃªt nen avÃªr muens que $2, pendent quâel en at $1.',
    'pfunc_max_ifexist_category'            => 'PÃ¢ges avouÃ©c trop dâapÃ¨ls a <code>#ifexist</code>',
);

/** Galician (Galego)
 * @author XosÃ©
 * @author Alma
 * @author Siebrand
 */
$messages['gl'] = array(
    'pfunc_time_error'                      => 'Erro: hora non vÃ¡lida',
    'pfunc_time_too_long'                   => 'Erro: demasiadas chamadas a #time',
    'pfunc_rel2abs_invalid_depth'           => 'Erro: Profundidade da ruta non vÃ¡lida: "$1" (tentouse acceder a un nodo por riba do nodo raÃ­z)',
    'pfunc_expr_stack_exhausted'            => 'Erro de expresiÃ³n: Pila esgotada',
    'pfunc_expr_unexpected_number'          => 'Erro de expresiÃ³n: NÃºmero inesperado',
    'pfunc_expr_preg_match_failure'         => 'Erro de expresiÃ³n: Fallo de preg_match inesperado',
    'pfunc_expr_unrecognised_word'          => 'Erro de expresiÃ³n: Palabra descoÃ±ecida "$1"',
    'pfunc_expr_unexpected_operator'        => 'Erro de expresiÃ³n: Operador $1 inesperado',
    'pfunc_expr_missing_operand'            => 'Erro de expresiÃ³n: Falta un operador para $1',
    'pfunc_expr_unexpected_closing_bracket' => 'ExpresiÃ³n de erro: Inesperado corchete',
    'pfunc_expr_unrecognised_punctuation'   => 'Erro de expresiÃ³n: Signo de puntuaciÃ³n descoÃ±ecido "$1"',
    'pfunc_expr_unclosed_bracket'           => 'Erro de expresiÃ³n: ParÃ©ntese sen pechar',
    'pfunc_expr_division_by_zero'           => 'DivisiÃ³n por cero',
    'pfunc_expr_unknown_error'              => 'Erro de expresiÃ³n: Erro descoÃ±ecido ($1)',
    'pfunc_expr_not_a_number'               => 'En $1: o resultado non Ã© un nÃºmero',
    'pfunc_ifexist_warning'                 => 'Advertencia: Este artigo inclÃºe demasiadas chamadas a #ifexists. DeberÃ­a ter menos de $2 e agora hai $1.',
    'pfunc_max_ifexist_category'            => 'PÃ¡xinas con demasiadas chamadas a ifexists',
);

/** Hebrew (×¢××¨××ª) */
$messages['he'] = array(
    'pfunc_time_error'                      => '×©××××: ××× ×©×××',
    'pfunc_time_too_long'                   => '×©××××: ×©××××© ×"#×××" ×¤×¢××× ×¨×××ª ×××',
    'pfunc_rel2abs_invalid_depth'           => '×©××××: ×¢×××§ ×©××× ×× ×ª××: "$1" (× ××¡××× ×× ××¡× ××¦×××ª ××¢× ×¦×××ª ××©××¨×©)',
    'pfunc_expr_stack_exhausted'            => '×©×××× ××××××: ××××¡× ××ª ××××',
    'pfunc_expr_unexpected_number'          => '×©×××× ××××××: ××¡×¤×¨ ×××ª× ×¦×¤××',
    'pfunc_expr_preg_match_failure'         => '×©×××× ××××××: ×××©××× ×××ª× ×¦×¤×× ×©× ××ª×××ª ××××× ×¨××××¨×',
    'pfunc_expr_unrecognised_word'          => '×©×××× ××××××: ×××× ×××ª× ×××××, "$1"',
    'pfunc_expr_unexpected_operator'        => '×©×××× ××××××: ×××¤×¨× × $1 ×××ª× ×¦×¤××',
    'pfunc_expr_missing_operand'            => '×©×××× ××××××: ××¡×¨ ×××¤×¨× × ×Ö¾$1',
    'pfunc_expr_unexpected_closing_bracket' => '×©×××× ××××××: ×¡×××¨××× ×¡×××¨×× ×××ª× ×¦×¤××××',
    'pfunc_expr_unrecognised_punctuation'   => '×©×××× ××××××: ×ª× ×¤××¡××§ ×××ª× ×××××, "$1"',
    'pfunc_expr_unclosed_bracket'           => '×©×××× ××××××: ×¡×××¨××× ×××ª× ×¡×××¨××',
    'pfunc_expr_division_by_zero'           => '××××§× ×××¤×¡',
    'pfunc_expr_unknown_error'              => '×©×××× ××××××: ×©×××× ×××ª× ××××¢× ($1)',
    'pfunc_expr_not_a_number'               => '××ª××¦×× ×©× $1 ××× × ××¡×¤×¨',
    'pfunc_ifexist_warning'                 => '××××¨×: ××£ ×× ×××× ×××ª×¨ ××× ×§×¨××××ª ×"#×§×××". ××× ×¦×¨×× ××××× ×¤×××ª ×Ö¾$2, ×× ××¢×ª ××© $1.',
    'pfunc_max_ifexist_category'            => '××¤×× ×¢× ×××¨×××ª ×§××× ×¨×××ª ×××',
);

/** Croatian (Hrvatski)
 * @author SpeedyGonsales
 * @author Siebrand
 * @author Dnik
 */
$messages['hr'] = array(
    'pfunc_desc'                            => 'ProÅ¡irite parser logiÄkim funkcijama',
    'pfunc_time_error'                      => 'GreÅ¡ka: oblik vremena nije valjan',
    'pfunc_time_too_long'                   => 'GreÅ¡ka: prevelik broj #time (vremenskih) poziva',
    'pfunc_rel2abs_invalid_depth'           => 'GreÅ¡ka: Nevaljana dubina putanje: "$1" (pokuÅ¡aj pristupanja Ävoru iznad korijenskog)',
    'pfunc_expr_stack_exhausted'            => 'GreÅ¡ka u predloÅ¡ku: prepunjen stog',
    'pfunc_expr_unexpected_number'          => 'GreÅ¡ka u predloÅ¡ku: NeoÄekivan broj',
    'pfunc_expr_preg_match_failure'         => 'GreÅ¡ka u predloÅ¡ku: NeoÄekivana preg_match greÅ¡ka',
    'pfunc_expr_unrecognised_word'          => 'GreÅ¡ka u predloÅ¡ku: Nepoznata rijeÄ "$1"',
    'pfunc_expr_unexpected_operator'        => 'GreÅ¡ka u predloÅ¡ku: NeoÄekivani operator $1',
    'pfunc_expr_missing_operand'            => 'GreÅ¡ka u predloÅ¡ku: Operator $1 nedostaje',
    'pfunc_expr_unexpected_closing_bracket' => 'GreÅ¡ka u predloÅ¡ku: NeoÄekivana zatvorena zagrada',
    'pfunc_expr_unrecognised_punctuation'   => 'GreÅ¡ka u predloÅ¡ku: Nepoznat interpunkcijski znak "$1"',
    'pfunc_expr_unclosed_bracket'           => 'GreÅ¡ka u predloÅ¡ku: Nezatvorene zagrade',
    'pfunc_expr_division_by_zero'           => 'Dijeljenje s nulom',
    'pfunc_expr_unknown_error'              => 'GreÅ¡ka u predloÅ¡ku: Nepoznata greÅ¡ka ($1)',
    'pfunc_expr_not_a_number'               => 'U $1: rezultat nije broj',
    'pfunc_ifexist_warning'                 => 'Upozorenje: Ova stranica sadrÅ¾i previÅ¡e #ifexist poziva. Treba ih biti manje od $2, trenutno ih je $1.',
    'pfunc_max_ifexist_category'            => 'Stranica s previÅ¡e ifexist poziva',
);

/** Upper Sorbian (Hornjoserbsce)
 * @author Michawiki
 */
$messages['hsb'] = array(
    'pfunc_desc'                            => 'Parser wo logiske funkcije rozÅ¡ÄriÄ',
    'pfunc_time_error'                      => 'Zmylk: njepÅaÄiwe Äasowe podaÄe',
    'pfunc_time_too_long'                   => 'Zmylk: pÅewjele zawoÅanjow #time',
    'pfunc_rel2abs_invalid_depth'           => 'Zmylk: NjepÅaÄiwa hÅubokosÄ w puÄiku: "$1" (Pospyt, zo by na suk wyÅ¡e hÅowneho suka dohrabnyÅo)',
    'pfunc_expr_stack_exhausted'            => 'Wurazowy zmylk: Staplowy skÅad wuÄerpany',
    'pfunc_expr_unexpected_number'          => 'Wurazowy zmylk: NjewoÄakowana liÄba',
    'pfunc_expr_preg_match_failure'         => 'Wurazowy zmylk: NjewoÄakowana zmylna funkcija "preg_match"',
    'pfunc_expr_unrecognised_word'          => 'Wurazowy zmylk: NjespÃ³znate sÅowo "$1"',
    'pfunc_expr_unexpected_operator'        => 'Wurazowy zmylk: NjewoÄakowany operator $1',
    'pfunc_expr_missing_operand'            => 'Wurazowy zmylk: Falowacy operand za $1',
    'pfunc_expr_unexpected_closing_bracket' => 'Wurazowy zmylk: NjewoÄakowana kÃ³nÄna rÃ³Å¾kata spinka',
    'pfunc_expr_unrecognised_punctuation'   => 'Wurazowy zmylk: NjespÃ³znate interpunkciske znamjeÅ¡ko "$1"',
    'pfunc_expr_unclosed_bracket'           => 'Wurazowy zmylk: Njewotzamknjena rÃ³Å¾kata spinka',
    'pfunc_expr_division_by_zero'           => 'Diwizija pÅez nulu',
    'pfunc_expr_unknown_error'              => 'Wurazowy zmylk: Njeznaty zmylk ($1)',
    'pfunc_expr_not_a_number'               => 'W $1: WuslÄdk liÄba njeje',
    'pfunc_ifexist_warning'                 => 'Warnowanje: TutÃ³n nastawk wobsahuje pÅewjele zawoÅanjow #ifexist. MÄÅo mjenje haÄ $2 byÄ, nÄtko je $1.',
    'pfunc_max_ifexist_category'            => 'Strony z pÅewjele zawoÅanjow ifexist',
);

/** Hungarian (Magyar)
 * @author Bdanee
 */
$messages['hu'] = array(
    'pfunc_desc'                            => 'Az Ã©rtelmezÅ kiegÃ©szÃ­tÃ©se logikai funkciÃ³kkal',
    'pfunc_time_error'                      => 'Hiba: Ã©rvÃ©nytelen idÅ',
    'pfunc_time_too_long'                   => 'Hiba: a #time tÃºl sokszor lett meghÃ­vva',
    'pfunc_rel2abs_invalid_depth'           => 'Hiba: nem megfelelÅ a mÃ©lysÃ©g az elÃ©rÃ©si Ãºtban: â$1â (egy olyan csomÃ³pontot akartÃ¡l elÃ©rni, amely a gyÃ¶kÃ©rcsomÃ³pont felett van)',
    'pfunc_expr_stack_exhausted'            => 'Hiba a kifejezÃ©sben: a verem kiÃ¼rÃ¼lt',
    'pfunc_expr_unexpected_number'          => 'Hiba a kifejezÃ©sben: nem vÃ¡rt szÃ¡m',
    'pfunc_expr_preg_match_failure'         => 'Hiba a kifejezÃ©sben: a preg_match vÃ¡ratlanul hibÃ¡t jelzett',
    'pfunc_expr_unrecognised_word'          => 'Hiba a kifejezÃ©sben: ismeretlen â$1â szÃ³',
    'pfunc_expr_unexpected_operator'        => 'Hiba a kifejezÃ©sben: nem vÃ¡rt $1 operÃ¡tor',
    'pfunc_expr_missing_operand'            => 'Hiba a kifejezÃ©sben: $1 egyik operandusa hiÃ¡nyzik',
    'pfunc_expr_unexpected_closing_bracket' => 'Hiba a kifejezÃ©sben: nem vÃ¡rt zÃ¡rÃ³jel',
    'pfunc_expr_unrecognised_punctuation'   => 'Hiba a kifejezÃ©sben: ismeretlen â$1â kÃ¶zpontozÃ³ karakter',
    'pfunc_expr_unclosed_bracket'           => 'Hiba a kifejezÃ©sben: lezÃ¡ratlan zÃ¡rÃ³jel',
    'pfunc_expr_division_by_zero'           => 'NullÃ¡val valÃ³ osztÃ¡s',
    'pfunc_expr_unknown_error'              => 'Hiba a kifejezÃ©sben: ismeretlen hiba ($1)',
    'pfunc_expr_not_a_number'               => '$1: az eredmÃ©ny nem szÃ¡m',
    'pfunc_ifexist_warning'                 => 'Figyelem: az oldal tÃºl sok #ifexist hÃ­vÃ¡st tartalmaz. Kevesebb, mint $2 darabnak kellene lennie, most $1 van.',
    'pfunc_max_ifexist_category'            => 'TÃºl sok ifexist hÃ­vÃ¡st tartalmazÃ³ lapok',
);

/** Indonesian (Bahasa Indonesia)
 * @author IvanLanin
 */
$messages['id'] = array(
    'pfunc_desc'                            => 'Mengembangkan parser dengan fungsi logika',
    'pfunc_time_error'                      => 'Kesalahan: time tidak valid',
    'pfunc_time_too_long'                   => 'Kesalahan: Pemanggilan #time terlalu banyak',
    'pfunc_rel2abs_invalid_depth'           => 'Kesalahan: Kedalaman path tidak valid: "$1" (mencoba mengakses simpul di atas simpul akar)',
    'pfunc_expr_stack_exhausted'            => 'Kesalahan ekspresi: Stack habis',
    'pfunc_expr_unexpected_number'          => 'Kesalahan ekspresi: Angka yang tak terduga',
    'pfunc_expr_preg_match_failure'         => 'Kesalahan ekspresi: Kesalah preg_match yang tak terduga',
    'pfunc_expr_unrecognised_word'          => 'Kesalahan ekspresi: Kata "$1" tak dikenal',
    'pfunc_expr_unexpected_operator'        => 'Kesalahan ekspresi: Operator $1 tak terduga',
    'pfunc_expr_missing_operand'            => 'Kesalahan ekspresi: Operand tak ditemukan untuk $1',
    'pfunc_expr_unexpected_closing_bracket' => 'Kesalahan ekspresi: Kurung tutup tak terduga',
    'pfunc_expr_unrecognised_punctuation'   => 'Kesalahan ekspresi: Karakter tanda baca "$1" tak dikenali',
    'pfunc_expr_unclosed_bracket'           => 'Kesalahan ekspresi: Kurung tanpa tutup',
    'pfunc_expr_division_by_zero'           => 'Pembagian oleh nol',
    'pfunc_expr_unknown_error'              => 'Kesalahan ekspresi: Kesalah tak dikenal ($1)',
    'pfunc_expr_not_a_number'               => 'Pada $1: hasilnya bukan angka',
    'pfunc_ifexist_warning'                 => 'Peringatan: Halaman ini mengandung terlalu banyak panggilan #ifexist. Seharusnya lebih sedikit dari $2, sekarang ada $1.',
    'pfunc_max_ifexist_category'            => 'Halaman dengan terlalu banyak panggilan ifexist',
);

/** Italian (Italiano)
 * @author BrokenArrow
 */
$messages['it'] = array(
    'pfunc_desc'                            => 'Aggiunge al parser una serie di funzioni logiche',
    'pfunc_time_error'                      => 'Errore: orario non valido',
    'pfunc_time_too_long'                   => 'Errore: troppe chiamate a #time',
    'pfunc_rel2abs_invalid_depth'           => 'Errore: profonditÃ  non valida nel percorso "$2" (si Ã¨ tentato di accedere a un nodo superiore alla radice)',
    'pfunc_expr_stack_exhausted'            => "Errore nell'espressione: stack esaurito",
    'pfunc_expr_unexpected_number'          => "Errore nell'espressione: numero inatteso",
    'pfunc_expr_preg_match_failure'         => "Errore nell'espressione: errore inatteso in preg_match",
    'pfunc_expr_unrecognised_word'          => 'Errore nell\'espressione: parola "$1" non riconosciuta',
    'pfunc_expr_unexpected_operator'        => "Errore nell'espressione: operatore $1 inatteso",
    'pfunc_expr_missing_operand'            => "Errore nell'espressione: operando mancante per $1",
    'pfunc_expr_unexpected_closing_bracket' => "Errore nell'espressione: parentesi chiusa inattesa",
    'pfunc_expr_unrecognised_punctuation'   => 'Errore nell\'espressione: carattere di punteggiatura "$1" non riconosciuto',
    'pfunc_expr_unclosed_bracket'           => "Errore nell'espressione: parentesi non chiusa",
    'pfunc_expr_division_by_zero'           => 'Divisione per zero',
    'pfunc_expr_unknown_error'              => "Errore nell'espressione: errore sconosciuto ($1)",
    'pfunc_expr_not_a_number'               => 'In $1: il risultato non Ã¨ un numero',
    'pfunc_ifexist_warning'                 => 'Attenzione: in questa pagina sono presenti troppe chiamate alla funzione #ifexist. Il numero massimo Ã¨ $2, al momento ve ne sono $1.',
    'pfunc_max_ifexist_category'            => 'Pagine con troppe chiamate alla funzione ifexist',
);

/** Japanese (æ¥æ¬è)
 * @author JtFuruhata
 */
$messages['ja'] = array(
    'pfunc_desc'                            => 'èçé¢æ°ã«ãããã¼ãµã¼æ¡åµ',
    'pfunc_time_error'                      => 'ã¨ã©ã¼: æå»ãäæ£ã§ã',
    'pfunc_time_too_long'                   => 'ã¨ã©ã¼: #time å¼ã³åºããåããã¾ã',
    'pfunc_rel2abs_invalid_depth'           => 'ã¨ã©ã¼: ãã¹ "$1" ã®éå¤ãäæ£ã§ãïã«ã¼ãéå¤ããã®ã¢ã¯ã»ã¹ããè¦ããã ããï',
    'pfunc_expr_stack_exhausted'            => 'ææã¨ã©ã¼: ã¹ã¿ãã¯ãçºã§ã',
    'pfunc_expr_unexpected_number'          => 'ææã¨ã©ã¼: äæãã¬æ°åã§ã',
    'pfunc_expr_preg_match_failure'         => 'ææã¨ã©ã¼: äæãã¬å¢ã§ preg_match ã«å±æãã¾ãã',
    'pfunc_expr_unrecognised_word'          => 'ææã¨ã©ã¼: "$1" ã¯èèã§ãã¾ãã',
    'pfunc_expr_unexpected_operator'        => 'ææã¨ã©ã¼: äæãã¬æçå $1 ãããã¾ã',
    'pfunc_expr_missing_operand'            => 'ææã¨ã©ã¼: $1 ã®ãªãã©ã³ããããã¾ãã',
    'pfunc_expr_unexpected_closing_bracket' => 'ææã¨ã©ã¼: äæãã¬éãæ¬å§ã§ã',
    'pfunc_expr_unrecognised_punctuation'   => 'ææã¨ã©ã¼: èèã§ããªãåºåãæå "$1" ãããã¾ã',
    'pfunc_expr_unclosed_bracket'           => 'ææã¨ã©ã¼: æ¬å§ãéãããã¦ãã¾ãã',
    'pfunc_expr_division_by_zero'           => '0ã§é¤çãã¾ãã',
    'pfunc_expr_unknown_error'              => 'ææã¨ã©ã¼: äæãã¬ã¨ã©ã¼ï$1ï',
    'pfunc_expr_not_a_number'               => '$1: çæãæ°åã§ã¯ããã¾ãã',
    'pfunc_ifexist_warning'                 => 'è¦å: ãã®ãã¼ã¸ã«ã¯åæ°ã® #ifexist å¼ã³åºããå«ã¾ãã¦ãã¾ããããã¯$2åæªæã§ãªããã°ãªãããç¾å¨ã¯$1åèè°ããã¦ãã¾ãã',
    'pfunc_max_ifexist_category'            => 'ãã®ãã¼ã¸ã¯ #ifexist å¼ã³åºããåããã¾ã',
);

/** â«ÙØ§Ø²Ø§ÙØ´Ø§ (ØªÙ´ÙØªÛ)â¬ (â«ÙØ§Ø²Ø§ÙØ´Ø§ (ØªÙ´ÙØªÛ)â¬) */
$messages['kk-arab'] = array(
    'pfunc_time_error'                      => 'ÙØ§ØªÛ: Ø¬Ø§Ø±Ø§ÙØ³ÙØ² ÛØ§ÙÙØª',
    'pfunc_time_too_long'                   => 'ÙØ§ØªÛ: #time Ø´Ø§ÙÙØ±ÛÙ ØªÙÙ ÙÙÙ¾',
    'pfunc_rel2abs_invalid_depth'           => 'ÙØ§ØªÛ: ÙÙÙØ§ Ø¬ÙÙØ¯ÙÚ­ Ø¬Ø§Ø±Ø§ÙØ³ÙØ² ØªÛØ±ÛÙØ¯ÙÚ¯Ù Â«$1Â» (ØªØ§ÙÙØ± Ù´ØªÛÙÙÙÙÙÚ­ ÛØ³ØªÙÙØ¯ÛÚ¯Ù ØªÛÙÙÙÚ¯Û ÙØ§ØªÙÙØ§Û ØªØ§ÙØ§Ø¨Ù)',
    'pfunc_expr_stack_exhausted'            => 'Ø§ÙØªÙÙÙÙ ÙØ§ØªÛØ³Ù: Ø³ØªÛÙ Ø³Ø§Ø±ÙÙÙØ¯Ù',
    'pfunc_expr_unexpected_number'          => 'Ø§ÙØªÙÙÙÙ ÙØ§ØªÛØ³Ù: ÙÛØªÙÙÙÛÚ¯ÛÙ Ø³Ø§Ù',
    'pfunc_expr_preg_match_failure'         => 'Ø§ÙØªÙÙÙÙ ÙØ§ØªÛØ³Ù: ÙÛØªÙÙÙÛÚ¯ÛÙ preg_match Ø³Ø§ØªØ³ÙØ²Ø¯ÙÚ¯Ù',
    'pfunc_expr_unrecognised_word'          => 'Ø§ÙØªÙÙÙÙ ÙØ§ØªÛØ³Ù: ØªØ§ÙÙÙÙØ§Ø¹Ø§Ù Ù´Ø³ÙØ² Â«$1Â»',
    'pfunc_expr_unexpected_operator'        => 'Ø§ÙØªÙÙÙÙ ÙØ§ØªÛØ³Ù: ÙÛØªÙÙÙÛÚ¯ÛÙ ÙÙ¾ÛØ±Ø§ØªÙØ± $1',
    'pfunc_expr_missing_operand'            => 'Ø§ÙØªÙÙÙÙ ÙØ§ØªÛØ³Ù: $1 Ù´ÛØ´ÙÙ Ø¬ÙØ¹Ø§ÙØ¹Ø§Ù ÙÙ¾ÛØ±Ø§ÙØ¯ ',
    'pfunc_expr_unexpected_closing_bracket' => 'Ø§ÙØªÙÙÙÙ ÙØ§ØªÛØ³Ù: ÙÛØªÙÙÙÛÚ¯ÛÙ Ø¬Ø§Ø¨Ø§ØªÙÙ Ø¬Ø§ÙØ´Ø§',
    'pfunc_expr_unrecognised_punctuation'   => 'Ø§ÙØªÙÙÙÙ ÙØ§ØªÛØ³Ù: ØªØ§ÙÙÙÙØ§Ø¹Ø§Ù ØªÙÙÙØ³ Ø¨ÛÙÚ¯ÙØ³Ù Â«$1Â» ',
    'pfunc_expr_unclosed_bracket'           => 'Ø§ÙØªÙÙÙÙ ÙØ§ØªÛØ³Ù: Ø¬Ø§Ø¨ÙÙÙØ§Ø¹Ø§Ù Ø¬Ø§ÙØ´Ø§',
    'pfunc_expr_division_by_zero'           => 'ÙÙÙÚ¯Û Ù´Ø¨ÙÙÙÙÛÙ',
    'pfunc_expr_unknown_error'              => 'Ø§ÙØªÙÙÙÙ ÙØ§ØªÛØ³Ù: Ø¨ÛÙÚ¯ÙØ³ÙØ² ÙØ§ØªÛ ($1)',
    'pfunc_expr_not_a_number'               => '$1 Ø¯ÛÚ¯ÛÙØ¯Û: ÙØ§ØªÙÙØ¬Û Ø³Ø§Ù ÛÙÛØ³',
    'pfunc_ifexist_warning'                 => 'ÙØ§Ø²Ø§Ø± Ø³Ø§ÙÙÚ­ÙØ²: ÙØ³Ù Ø¨ÛØªØªÛ ØªÙÙ ÙÙÙ¾ #ifexist Ø´Ø§ÙÙØ±ÙÙØ¯Ø§Ø±Ù Ø¨Ø§Ø±. Ø¨ÛÙ $2 Ø³Ø§ÙÙØ§Ù ÙÛÙ Ø¨ÙÙÛÙ ÙÛØ±ÛÙ, ÙÙÙØ¯Ø§ ÙØ§Ø²ÙØ± $1 Ø¨Ø§Ø±.',
    'pfunc_max_ifexist_category'            => 'ØªÙÙ ÙÙÙ¾ ifexist Ø´Ø§ÙÙØ±ÙÙØ¯Ø§Ø±Ù Ø¨Ø§Ø± Ø¨ÛØªØªÛØ±',
);

/** Kazakh (Cyrillic) (ÒÐ°Ð·Ð°ÒÑÐ° (Cyrillic)) */
$messages['kk-cyrl'] = array(
    'pfunc_time_error'                      => 'ÒÐ°ÑÐµ: Ð¶Ð°ÑÐ°Ð¼ÑÑÐ· ÑÐ°ÒÑÑ',
    'pfunc_time_too_long'                   => 'ÒÐ°ÑÐµ: #time ÑÐ°ÒÑÑÑÑ ÑÑÐ¼ ÐºÓ©Ð¿',
    'pfunc_rel2abs_invalid_depth'           => 'ÒÐ°ÑÐµ: ÐÑÐ½Ð° Ð¶Ð¾Ð»Ð´ÑÒ£ Ð¶Ð°ÑÐ°Ð¼ÑÑÐ· ÑÐµÑÐµÐ½Ð´ÑÐ³Ñ Â«$1Â» (ÑÐ°Ð¼ÑÑ ÑÒ¯Ð¹ÑÐ½Ð½ÑÒ£ Ò¯ÑÑÑÐ½Ð´ÐµÐ³Ñ ÑÒ¯Ð¹ÑÐ½Ð³Ðµ ÒÐ°ÑÑÐ½Ð°Ñ ÑÐ°Ð»Ð°Ð±Ñ)',
    'pfunc_expr_stack_exhausted'            => 'ÐÐ¹ÑÑÐ»ÑÐ¼ ÒÐ°ÑÐµÑÑ: Ð¡ÑÐµÐº ÑÐ°ÑÒÑÐ»Ð´Ñ',
    'pfunc_expr_unexpected_number'          => 'ÐÐ¹ÑÑÐ»ÑÐ¼ ÒÐ°ÑÐµÑÑ: ÐÒ¯ÑÑÐ»Ð¼ÐµÐ³ÐµÐ½ ÑÐ°Ð½',
    'pfunc_expr_preg_match_failure'         => 'ÐÐ¹ÑÑÐ»ÑÐ¼ ÒÐ°ÑÐµÑÑ: ÐÒ¯ÑÑÐ»Ð¼ÐµÐ³ÐµÐ½ preg_match ÑÓÑÑÑÐ·Ð´ÑÐ³Ñ',
    'pfunc_expr_unrecognised_word'          => 'ÐÐ¹ÑÑÐ»ÑÐ¼ ÒÐ°ÑÐµÑÑ: Ð¢Ð°Ð½ÑÐ»Ð¼Ð°ÒÐ°Ð½ ÑÓ©Ð· Â«$1Â»',
    'pfunc_expr_unexpected_operator'        => 'ÐÐ¹ÑÑÐ»ÑÐ¼ ÒÐ°ÑÐµÑÑ: ÐÒ¯ÑÑÐ»Ð¼ÐµÐ³ÐµÐ½ Ð¾Ð¿ÐµÑÐ°ÑÐ¾Ñ $1',
    'pfunc_expr_missing_operand'            => 'ÐÐ¹ÑÑÐ»ÑÐ¼ ÒÐ°ÑÐµÑÑ: $1 Ò¯ÑÑÐ½ Ð¶Ð¾ÒÐ°Ð»ÒÐ°Ð½ Ð¾Ð¿ÐµÑÐ°Ð½Ð´ ',
    'pfunc_expr_unexpected_closing_bracket' => 'ÐÐ¹ÑÑÐ»ÑÐ¼ ÒÐ°ÑÐµÑÑ: ÐÒ¯ÑÑÐ»Ð¼ÐµÐ³ÐµÐ½ Ð¶Ð°Ð±Ð°ÑÑÐ½ Ð¶Ð°ÒÑÐ°',
    'pfunc_expr_unrecognised_punctuation'   => 'ÐÐ¹ÑÑÐ»ÑÐ¼ ÒÐ°ÑÐµÑÑ: Ð¢Ð°Ð½ÑÐ»Ð¼Ð°ÒÐ°Ð½ ÑÑÐ½ÑÑ Ð±ÐµÐ»Ð³ÑÑÑ Â«$1Â» ',
    'pfunc_expr_unclosed_bracket'           => 'ÐÐ¹ÑÑÐ»ÑÐ¼ ÒÐ°ÑÐµÑÑ: ÐÐ°Ð±ÑÐ»Ð¼Ð°ÒÐ°Ð½ Ð¶Ð°ÒÑÐ°',
    'pfunc_expr_division_by_zero'           => 'ÐÓ©Ð»Ð³Ðµ Ð±Ó©Ð»ÑÐ½ÑÑ',
    'pfunc_expr_unknown_error'              => 'ÐÐ¹ÑÑÐ»ÑÐ¼ ÒÐ°ÑÐµÑÑ: ÐÐµÐ»Ð³ÑÑÑÐ· ÒÐ°ÑÐµ ($1)',
    'pfunc_expr_not_a_number'               => '$1 Ð´ÐµÐ³ÐµÐ½Ð´Ðµ: Ð½ÓÑÐ¸Ð¶Ðµ ÑÐ°Ð½ ÐµÐ¼ÐµÑ',
    'pfunc_ifexist_warning'                 => 'ÐÐ°Ð·Ð°Ñ ÑÐ°Ð»ÑÒ£ÑÐ·: ÐÑÑ Ð±ÐµÑÑÐµ ÑÑÐ¼ ÐºÓ©Ð¿ #ifexist ÑÐ°ÒÑÑÑÐ¼Ð´Ð°ÑÑ Ð±Ð°Ñ. ÐÒ±Ð» $2 ÑÐ°Ð½Ð½Ð°Ð½ ÐºÐµÐ¼ Ð±Ð¾Ð»ÑÑ ÐºÐµÑÐµÐº, Ð¼ÑÐ½Ð´Ð° ÒÐ°Ð·ÑÑ $1 Ð±Ð°Ñ.',
    'pfunc_max_ifexist_category'            => 'Ð¢ÑÐ¼ ÐºÓ©Ð¿ ifexist ÑÐ°ÒÑÑÑÐ¼Ð´Ð°ÑÑ Ð±Ð°Ñ Ð±ÐµÑÑÐµÑ',
);

/** Kazakh (Latin) (ÒÐ°Ð·Ð°ÒÑÐ° (Latin)) */
$messages['kk-latn'] = array(
    'pfunc_time_error'                      => 'Qate: jaramsÄ±z waqÄ±t',
    'pfunc_time_too_long'                   => 'Qate: #time ÅaqÄ±rwÄ± tÄ±m kÃ¶p',
    'pfunc_rel2abs_invalid_depth'           => 'Qate: MÄ±na joldÄ±Ã± jaramsÄ±z terendigi Â«$1Â» (tamÄ±r tÃ¼Ã½inniÃ± Ã¼stindegi tÃ¼Ã½inge qatÄ±naw talabÄ±)',
    'pfunc_expr_stack_exhausted'            => 'AÃ½tÄ±lÄ±m qatesi: Stek sarqÄ±ldÄ±',
    'pfunc_expr_unexpected_number'          => 'AÃ½tÄ±lÄ±m qatesi: KÃ¼tilmegen san',
    'pfunc_expr_preg_match_failure'         => 'AÃ½tÄ±lÄ±m qatesi: KÃ¼tilmegen preg_match sÃ¤tsizdigi',
    'pfunc_expr_unrecognised_word'          => 'AÃ½tÄ±lÄ±m qatesi: TanÄ±lmaÄan sÃ¶z Â«$1Â»',
    'pfunc_expr_unexpected_operator'        => 'AÃ½tÄ±lÄ±m qatesi: KÃ¼tilmegen operator $1',
    'pfunc_expr_missing_operand'            => 'AÃ½tÄ±lÄ±m qatesi: $1 Ã¼Åin joÄalÄan operand ',
    'pfunc_expr_unexpected_closing_bracket' => 'AÃ½tÄ±lÄ±m qatesi: KÃ¼tilmegen jabatÄ±n jaqÅa',
    'pfunc_expr_unrecognised_punctuation'   => 'AÃ½tÄ±lÄ±m qatesi: TanÄ±lmaÄan tÄ±nÄ±s belgisi Â«$1Â» ',
    'pfunc_expr_unclosed_bracket'           => 'AÃ½tÄ±lÄ±m qatesi: JabÄ±lmaÄan jaqÅa',
    'pfunc_expr_division_by_zero'           => 'NÃ¶lge bÃ¶linwi',
    'pfunc_expr_unknown_error'              => 'AÃ½tÄ±lÄ±m qatesi: Belgisiz qate ($1)',
    'pfunc_expr_not_a_number'               => '$1 degende: nÃ¤tÃ¯je san emes',
    'pfunc_ifexist_warning'                 => 'Nazar salÄ±Ã±Ä±z: OsÄ± bette tÄ±m kÃ¶p #ifexist ÅaqÄ±rÄ±mdarÄ± bar. Bul $2 sannan kem bolwÄ± kerek, mÄ±nda qazir $1 bar.',
    'pfunc_max_ifexist_category'            => 'TÄ±m kÃ¶p ifexist ÅaqÄ±rÄ±mdarÄ± bar better',
);

/** Khmer (áá¶áá¶ááááá)
 * @author Lovekhmer
 */
$messages['km'] = array(
    'pfunc_expr_division_by_zero' => 'ááááá¹ááá¼ááá',
);

/** Latin (Latina)
 * @author UV
 */
$messages['la'] = array(
    'pfunc_ifexist_warning'      => 'Monitio: Haec pagina nimis #ifexist adhibet. Licet uti $2, haec pagina nunc utitur $1.',
    'pfunc_max_ifexist_category' => 'Paginae quae nimis ifexist adhibent',
);

/** Luxembourgish (LÃ«tzebuergesch)
 * @author Robby
 */
$messages['lb'] = array(
    'pfunc_expr_division_by_zero' => 'Divisioun duerch Null',
    'pfunc_expr_not_a_number'     => "An $1: D'Resultat ass keng Zuel",
);

/** Limburgish (Limburgs)
 * @author Matthias
 * @author Ooswesthoesbes
 */
$messages['li'] = array(
    'pfunc_desc'                            => 'Verrijkt de parser met logische functies',
    'pfunc_time_error'                      => 'Fout: ongeldige tied',
    'pfunc_time_too_long'                   => 'Fout: #time te vaok aangerope',
    'pfunc_rel2abs_invalid_depth'           => 'Fout: ongeldige diepte in pad: "$1" (probeerde \'n node bove de stamnode aan te rope)',
    'pfunc_expr_stack_exhausted'            => 'Fout in oetdrukking: stack oetgeput',
    'pfunc_expr_unexpected_number'          => 'Fout in oetdrukking: onverwacht getal',
    'pfunc_expr_preg_match_failure'         => 'Fout in oetdrukking: onverwacht fale van preg_match',
    'pfunc_expr_unrecognised_word'          => 'Fout in oetdrukking: woord "$1" neet herkend',
    'pfunc_expr_unexpected_operator'        => 'Fout in oetdrukking: neet verwachte operator $1',
    'pfunc_expr_missing_operand'            => 'Fout in oetdrukking: operand veur $1 mist',
    'pfunc_expr_unexpected_closing_bracket' => 'Fout in oetdrukking: haakje sloete op onverwachte plaats',
    'pfunc_expr_unrecognised_punctuation'   => 'Fout in oetdrukking: neet herkend leesteke "$1"',
    'pfunc_expr_unclosed_bracket'           => 'Fout in oetdrukking: neet geslote haakje opene',
    'pfunc_expr_division_by_zero'           => 'Deiling door nul',
    'pfunc_expr_unknown_error'              => 'Fout in oetdrukking: Ã³nbekindje fout ($1)',
    'pfunc_expr_not_a_number'               => 'In $1: rezultaot is gein getal',
    'pfunc_ifexist_warning'                 => "Waorsjuwing: dees pazjena gebroek #ifexists euverduk. Det zÃ³w minder es $2 kieÃ«r mÃ³tte zeen en 't is noe $1 kieÃ«r.",
    'pfunc_max_ifexist_category'            => "Pazjena's die iefexist euverduk gebroeke",
);

/** Marathi (à®à°à¾à à)
 * @author Kaustubh
 */
$messages['mr'] = array(
    'pfunc_desc'                            => 'à¤à¾à°ààà¿à àà¾à°àà¯à àµà¾àªà°àà¨ àªà¾à°àà¸à° àµà¾à¢àµà¾',
    'pfunc_time_error'                      => 'à¤àà°ààà: àààààà¾ àµàà³',
    'pfunc_time_too_long'                   => 'à¤àà°ààà: àààª àà¾à¸àà¤ #time ààà²àà¸',
    'pfunc_rel2abs_invalid_depth'           => 'à¤àà°ààà: à®à¾à°ààà¾à®à§àà¯à àààààà àà¹à¨à¤à¾: "$1" (à°àà à¨àà¡ààà¯à¾ àµà°àà² à¨àà¡ à¶àà§à¾à¯àà¾ àªàà°à¯à¤àà¨ ààà²à¾)',
    'pfunc_expr_stack_exhausted'            => 'àààà¸ààªàà°àà¶à¨ à¤àà°ààà: à¸àààà à¸ààªà²à¾',
    'pfunc_expr_unexpected_number'          => 'àààà¸ààªàà°àà¶à¨ à¤àà°ààà: àà¨àªàààà·à¿à¤ ààà°à®à¾àà',
    'pfunc_expr_preg_match_failure'         => 'àààà¸ààªàà°àà¶à¨ à¤àà°ààà: àà¨àªàààà·à¿à¤ preg_match à°à¦àà¦ààà°à£',
    'pfunc_expr_unrecognised_word'          => 'àààà¸ààªàà°àà¶à¨ à¤àà°ààà: àà¨àà³àà à¶à¬àà¦ "$1"',
    'pfunc_expr_unexpected_operator'        => 'àààà¸ààªàà°àà¶à¨ à¤àà°ààà: àà¨àà³àà $1 àà¾à°àà¯àµà¾à¹à',
    'pfunc_expr_missing_operand'            => 'àààà¸ààªàà°àà¶à¨ à¤àà°ààà: $1 àà¾ ààà à¸à¾àªà¡à²à¾ à¨à¾à¹à',
    'pfunc_expr_unexpected_closing_bracket' => 'àààà¸ààªàà°àà¶à¨ à¤àà°ààà: àà¨àªàààà·à¿à¤ à¸à®à¾àªàà¤à ààà¸',
    'pfunc_expr_unrecognised_punctuation'   => 'àààà¸ààªàà°àà¶à¨ à¤àà°ààà: àà¨àà³àà àà¦ààà¾à°àµà¾àà àà¿à¨àà¹ "$1"',
    'pfunc_expr_unclosed_bracket'           => 'àààà¸ààªàà°àà¶à¨ à¤àà°ààà: ààà¸ à¸à®à¾àªàà¤ ààà²àà²à¾ à¨à¾à¹à',
    'pfunc_expr_division_by_zero'           => 'à¶àà¨àà¯ à¨à à­à¾àà¾àà¾à°',
    'pfunc_expr_unknown_error'              => 'àààà¸ààªàà°àà¶à¨ à¤àà°ààà: àà¨àà³àà à¤àà°ààà ($1)',
    'pfunc_expr_not_a_number'               => '$1 à®à§àà¯à: à¨à¿àà¾à² à¸àààà¯àà¤ à¨à¾à¹à',
    'pfunc_ifexist_warning'                 => 'àà¶à¾à°à¾: à¯à¾ àªà¾à¨à¾àµà° àà¾à¸àà¤àà¤ àà¾à¸àà¤ $2 #ifexist ààà²àà¸ àà¸à à¶àà¤à¾à¤, à¸à§àà¯à¾ $1 ààà²àà¸ àà¹àà¤.',
    'pfunc_max_ifexist_category'            => 'àààª à¸à¾à°à ifexist ààà²àà¸ àà¸à£à¾à°à àªà¾à¨à',
);

/** Low German (PlattdÃ¼Ã¼tsch)
 * @author Slomox
 */
$messages['nds'] = array(
    'pfunc_time_error'                      => 'Fehler: mit de Tiet stimmt wat nich',
    'pfunc_time_too_long'                   => 'Fehler: #time warrt to faken opropen',
    'pfunc_rel2abs_invalid_depth'           => 'Fehler: Mit den Padd â$1â stimmt wat nich, liggt nich Ã¼nner den Wuddelorner',
    'pfunc_expr_stack_exhausted'            => 'Fehler inân Utdruck: Stack Ã¶verlopen',
    'pfunc_expr_unexpected_number'          => 'Fehler inân Utdruck: Unverwacht Tall',
    'pfunc_expr_preg_match_failure'         => 'Fehler inân Utdruck: Unverwacht Fehler bi âpreg_matchâ',
    'pfunc_expr_unrecognised_word'          => 'Fehler inân Utdruck: Woort â$1â nich kennt',
    'pfunc_expr_unexpected_operator'        => 'Fehler inân Utdruck: Unverwacht Operator $1',
    'pfunc_expr_missing_operand'            => 'Fehler inân Utdruck: Operand fÃ¶r $1 fehlt',
    'pfunc_expr_unexpected_closing_bracket' => 'Fehler inân Utdruck: Unverwacht Klammer to',
    'pfunc_expr_unrecognised_punctuation'   => 'Fehler inân Utdruck: Satzteken â$1â nich kennt',
    'pfunc_expr_unclosed_bracket'           => 'Fehler inân Utdruck: Nich slatene Klammer',
    'pfunc_expr_division_by_zero'           => 'Delen dÃ¶r Null',
    'pfunc_expr_unknown_error'              => 'Fehler inân Utdruck: Unbekannten Fehler ($1)',
    'pfunc_expr_not_a_number'               => 'In $1: wat rutkamen is, is kene Tall',
    'pfunc_ifexist_warning'                 => 'Wohrschau: Disse Siet bruukt #ifexist to faken. De Siet drÃ¶ff nich mehr as $2 hebben, hett aver $1.',
    'pfunc_max_ifexist_category'            => 'Sieden, de #ifexist to faken bruukt',
);

/** Nepali (à¨ààªà¾à²à)
 * @author SPQRobin
 */
$messages['ne'] = array(
    'pfunc_time_error'            => 'à¤àà°ààà: àà²à¤/àµà¾ à¹àà¦àà¨à¹àà¨à à¸à®à¯',
    'pfunc_time_too_long'         => 'à¤àà°ààà: ààà¦à® à§àà°à #time callà¹à°à',
    'pfunc_rel2abs_invalid_depth' => 'à¤àà°ààà: àªà¾à¥à®à¾ (àà¨à­àà¯à¾à²à¿à¡)àà²à¤ àà¹à¿à°à¾à(à¡ààªàà¥) à­à¯à: "$1" (à²à à°àà à¨àà¡ à­à¨àà¦à¾àªà¨à¿ à®à¾à¥à¿àà à¨àà¡à²à¾à àà²à¾àà¨(ààà¸àà¸) àà°àà¨ ààààà¯à)',
);

/** Dutch (Nederlands)
 * @author SPQRobin
 * @author Siebrand
 */
$messages['nl'] = array(
    'pfunc_desc'                            => 'Verrijkt de parser met logische functies',
    'pfunc_time_error'                      => 'Fout: ongeldige tijd',
    'pfunc_time_too_long'                   => 'Fout: #time te vaak aangeroepen',
    'pfunc_rel2abs_invalid_depth'           => 'Fout: ongeldige diepte in pad: "$1" (probeerde een node boven de stamnode aan te roepen)',
    'pfunc_expr_stack_exhausted'            => 'Fout in uitdrukking: stack uitgeput',
    'pfunc_expr_unexpected_number'          => 'Fout in uitdrukking: onverwacht getal',
    'pfunc_expr_preg_match_failure'         => 'Fout in uitdrukking: onverwacht falen van preg_match',
    'pfunc_expr_unrecognised_word'          => 'Fout in uitdrukking: woord "$1" niet herkend',
    'pfunc_expr_unexpected_operator'        => 'Fout in uitdrukking: niet verwachte operator $1',
    'pfunc_expr_missing_operand'            => 'Fout in uitdrukking: operand voor $1 mist',
    'pfunc_expr_unexpected_closing_bracket' => 'Fout in uitdrukking: haakje sluiten op onverwachte plaats',
    'pfunc_expr_unrecognised_punctuation'   => 'Fout in uitdrukking: niet herkend leesteken "$1"',
    'pfunc_expr_unclosed_bracket'           => 'Fout in uitdrukking: niet gesloten haakje openen',
    'pfunc_expr_division_by_zero'           => 'Deling door nul',
    'pfunc_expr_unknown_error'              => 'Fout in uitdrukking: onbekende fout ($1)',
    'pfunc_expr_not_a_number'               => 'In $1: resultaat is geen getal',
    'pfunc_ifexist_warning'                 => 'Waarschuwing: deze pagina gebruik #ifexists te vaak. Dat zou minder dan $2 keer moeten zijn en is nu $1 keer.',
    'pfunc_max_ifexist_category'            => "Pagina's die ifexist te vaak gebruiken",
);

/** Norwegian Nynorsk (âªNorsk (nynorsk)â¬)
 * @author Eirik
 */
$messages['nn'] = array(
    'pfunc_desc'                            => 'Legg til logiske funksjonar i parseren.',
    'pfunc_time_error'                      => 'Feil: Ugyldig tid',
    'pfunc_time_too_long'                   => 'Feil: #time er kalla for mange gonger',
    'pfunc_rel2abs_invalid_depth'           => 'Feil: Ugyldig djupn i stien: Â«$1Â» (prÃ¸vde Ã¥ nÃ¥ ein node ovanfor rotnoden)',
    'pfunc_expr_stack_exhausted'            => 'Feil i uttrykket: Stacken er tÃ¸md',
    'pfunc_expr_unexpected_number'          => 'Feil i uttrykket: Uventa tal',
    'pfunc_expr_preg_match_failure'         => 'Feil i uttrykket: Uventa feil i preg_match',
    'pfunc_expr_unrecognised_word'          => 'Feil i uttrykket: Ukjent ord, Â«$1Â»',
    'pfunc_expr_unexpected_operator'        => 'Feil i uttrykket: Uventa operatÃ¸r, $1',
    'pfunc_expr_missing_operand'            => 'Feil i uttrykket: Operand for $1 manglar',
    'pfunc_expr_unexpected_closing_bracket' => 'Feil i uttrykket: Uventa avsluttande parentes',
    'pfunc_expr_unrecognised_punctuation'   => 'Feil i uttrykket: Ukjent punktumsteikn, Â«$1Â»',
    'pfunc_expr_unclosed_bracket'           => 'Feil i uttrykket: Ein parentes er ikkje avslutta',
    'pfunc_expr_division_by_zero'           => 'Divisjon med null',
    'pfunc_expr_unknown_error'              => 'Feil i uttrykket: Ukjend feil ($1)',
    'pfunc_expr_not_a_number'               => 'Resultatet i $1 er ikkje eit tal',
    'pfunc_ifexist_warning'                 => 'Ãtvaring: #ifexist er kalla for mange gonger pÃ¥ denne sida. Han kan ikkje vere kalla fleire gonger enn $2, men er no kalla $1 gonger.',
    'pfunc_max_ifexist_category'            => 'Sider med for mange kallingar av #ifexist',
);

/** Norwegian (âªNorsk (bokmÃ¥l)â¬)
 * @author Jon Harald SÃ¸by
 */
$messages['no'] = array(
    'pfunc_desc'                            => 'Utvid parser med logiske funksjoner',
    'pfunc_time_error'                      => 'Feil: ugyldig tid',
    'pfunc_time_too_long'                   => 'Feil: #time brukt for mange ganger',
    'pfunc_rel2abs_invalid_depth'           => 'Feil: Ugyldig dybde i sti: Â«$1Â» (prÃ¸vde Ã¥ fÃ¥ tilgang til en node over rotnoden)',
    'pfunc_expr_stack_exhausted'            => 'Uttrykksfeil: Stakk utbrukt',
    'pfunc_expr_unexpected_number'          => 'Uttrykksfeil: Uventet nummer',
    'pfunc_expr_preg_match_failure'         => 'Uttrykksfeil: Uventet preg_match-feil',
    'pfunc_expr_unrecognised_word'          => 'Uttrykksfeil: Ugjenkjennelig ord Â«$1Â»',
    'pfunc_expr_unexpected_operator'        => 'Uttrykksfeil: Uventet $1-operator',
    'pfunc_expr_missing_operand'            => 'Uttrykksfeil: Mangler operand for $1',
    'pfunc_expr_unexpected_closing_bracket' => 'Uttrykksfeil: Uventet lukkende parentes',
    'pfunc_expr_unrecognised_punctuation'   => 'Uttrykksfeil: Ugjenkjennelig tegn Â«$1Â»',
    'pfunc_expr_unclosed_bracket'           => 'Uttrykksfeil: Ãpen parentes',
    'pfunc_expr_division_by_zero'           => 'Deling pÃ¥ null',
    'pfunc_expr_unknown_error'              => 'Uttrykksfeil: Ukjent feil ($1)',
    'pfunc_expr_not_a_number'               => 'I $1: resultat er ikke et tall',
    'pfunc_ifexist_warning'                 => 'Advarsel: Denne siden bruker #ifexist for mange ganger. Den burde brukes mindre enn $2 ganger, men brukes nÃ¥ $1.',
    'pfunc_max_ifexist_category'            => 'Sider som bruker ifexist for mange ganger',
);

/** Occitan (Occitan)
 * @author Cedric31
 * @author Siebrand
 */
$messages['oc'] = array(
    'pfunc_desc'                            => 'Augmenta lo parsaire amb de foncions logicas',
    'pfunc_time_error'                      => 'Error: durada invalida',
    'pfunc_time_too_long'                   => 'Error: parser #time apelat trÃ²p de cÃ²ps',
    'pfunc_rel2abs_invalid_depth'           => 'Error: nivÃ¨l de repertÃ²ri invalid dins lo camin : "$1" (a ensajat dâaccedir a un nivÃ¨l al-dessÃºs del repertÃ²ri raiÃ§)',
    'pfunc_expr_stack_exhausted'            => 'Expression erronÃ¨a : pila agotada',
    'pfunc_expr_unexpected_number'          => 'Expression erronÃ¨a : nombre pas esperat',
    'pfunc_expr_preg_match_failure'         => 'Expression erronÃ¨a : una expression pas compresa a pas capitat',
    'pfunc_expr_unrecognised_word'          => "Error d'expression : lo mot '''$1''' es pas reconegut",
    'pfunc_expr_unexpected_operator'        => "Error d'expression : l'operator '''$1''' es pas reconegut",
    'pfunc_expr_missing_operand'            => "Error d'expression : l'operanda '''$1''' es pas reconeguda",
    'pfunc_expr_unexpected_closing_bracket' => "Error d'expression : parentÃ¨si tampanta pas prevista",
    'pfunc_expr_unrecognised_punctuation'   => "Error d'expression : caractÃ¨r de ponctuacion Â« $1 Â» pas reconegut",
    'pfunc_expr_unclosed_bracket'           => 'Error dâexpression : parentÃ¨si pas tampada',
    'pfunc_expr_division_by_zero'           => 'Division per zÃ¨ro',
    'pfunc_expr_unknown_error'              => "Error d'expression : error desconeguda ($1)",
    'pfunc_expr_not_a_number'               => 'Dins $1 : lo resultat es pas un nombre',
    'pfunc_ifexist_warning'                 => "Atencion : Aquesta pagina conten trÃ²p d'apÃ¨ls a <code>#ifexist</code>. Ne deuriÃ¡ aver mens que $2, alara que n'a $1.",
    'pfunc_max_ifexist_category'            => "Paginas amb trÃ²p d'apÃ¨ls a <code>#ifexist</code>",
);

/** Polish (Polski)
 * @author Derbeth
 * @author Sp5uhe
 * @author Siebrand
 */
$messages['pl'] = array(
    'pfunc_desc'                            => 'Rozszerza analizator skÅadni o funkcje logiki',
    'pfunc_time_error'                      => 'BÅÄd: niepoprawny czas',
    'pfunc_time_too_long'                   => 'BÅÄd: za duÅ¼o wywoÅaÅ funkcji #time',
    'pfunc_rel2abs_invalid_depth'           => 'BÅÄd: NieprawidÅowa gÅÄbokoÅÄ w ÅcieÅ¼ce: "$1" (prÃ³ba dostÄpu do wÄzÅa powyÅ¼ej korzenia)',
    'pfunc_expr_stack_exhausted'            => 'BÅÄd w wyraÅ¼eniu: Stos wyczerpany',
    'pfunc_expr_unexpected_number'          => 'BÅÄd w wyraÅ¼eniu: Niespodziewana liczba',
    'pfunc_expr_preg_match_failure'         => 'BÅÄd w wyraÅ¼eniu: Niespodziewany bÅÄd w preg_match',
    'pfunc_expr_unrecognised_word'          => 'BÅÄd w wyraÅ¼eniu: Nierozpoznane sÅowo "$1"',
    'pfunc_expr_unexpected_operator'        => 'BÅÄd w wyraÅ¼eniu: Nieoczekiwany operator $1',
    'pfunc_expr_missing_operand'            => 'BÅÄd w wyraÅ¼eniu: BrakujÄcy operand dla $1',
    'pfunc_expr_unexpected_closing_bracket' => 'BÅÄd w wyraÅ¼eniu: Nieoczekiwany nawias zamykajÄcy',
    'pfunc_expr_unrecognised_punctuation'   => 'BÅÄd w wyraÅ¼eniu: Nierozpoznany znak interpunkcyjny "$1"',
    'pfunc_expr_unclosed_bracket'           => 'BÅÄd w wyraÅ¼eniu: NiedomkniÄty nawias',
    'pfunc_expr_division_by_zero'           => 'Dzielenie przez zero',
    'pfunc_expr_unknown_error'              => 'BÅÄd w wyraÅ¼eniu: Nieznany bÅÄd ($1)',
    'pfunc_expr_not_a_number'               => 'W $1: wynik nie jest liczbÄ',
    'pfunc_ifexist_warning'                 => 'Uwaga: Ta strona zawiera zbyt wiele wywoÅaÅ funkcji #ifexist. Nie ich moÅ¼e byÄ wiÄcej niÅ¼ $2, a jest obecnie $1.',
    'pfunc_max_ifexist_category'            => 'Strony ze zbyt duÅ¼Ä iloÅciÄ wywoÅaÅ ifexist',
);

/** PiemontÃ¨is (PiemontÃ¨is)
 * @author BÃ¨rto 'd SÃ¨ra
 * @author Siebrand
 */
$messages['pms'] = array(
    'pfunc_time_error'            => 'Eror: temp nen bon',
    'pfunc_time_too_long'         => 'Eror: #time a ven ciamÃ  trÃ²pe vire',
    'pfunc_rel2abs_invalid_depth' => 'Eror: profonditÃ  nen bon-a ant Ã«l pÃ«rcors: "$1" (a l\'Ã© provasse a ciamÃ© un grop dzora a la rÃ¨is)',
);

/** Pashto (Ù¾ÚØªÙ)
 * @author Ahmed-Najib-Biabani-Ibrahimkhel
 */
$messages['ps'] = array(
    'pfunc_time_error' => 'Ø³ØªÙÙØ²Ù: ÙØ§Ø³Ù ÙØ®Øª',
);

/** Portuguese (PortuguÃªs)
 * @author Malafaya
 */
$messages['pt'] = array(
    'pfunc_desc'                            => 'Melhora o analisador "parser" com funÃ§Ãµes lÃ³gicas',
    'pfunc_time_error'                      => 'Erro: tempo invÃ¡lido',
    'pfunc_time_too_long'                   => 'Erro: demasiadas chamadas a #time',
    'pfunc_rel2abs_invalid_depth'           => 'Erro: Profundidade invÃ¡lida no caminho: "$1" (foi tentado o acesso a um nÃ³ acima do nÃ³ raiz)',
    'pfunc_expr_stack_exhausted'            => 'Erro de expressÃ£o: Pilha esgotada',
    'pfunc_expr_unexpected_number'          => 'Erro de expressÃ£o: NÃºmero inesperado',
    'pfunc_expr_preg_match_failure'         => 'Erro de expressÃ£o: Falha em preg_match inesperada',
    'pfunc_expr_unrecognised_word'          => 'Erro de expressÃ£o: Palavra "$1" nÃ£o reconhecida',
    'pfunc_expr_unexpected_operator'        => 'Erro de expressÃ£o: Operador $1 inesperado',
    'pfunc_expr_missing_operand'            => 'Erro de expressÃ£o: Falta operando para $1',
    'pfunc_expr_unexpected_closing_bracket' => 'Erro de expressÃ£o: ParÃªntese de fecho inesperado',
    'pfunc_expr_unrecognised_punctuation'   => 'Erro de expressÃ£o: Caracter de pontuaÃ§Ã£o "$1" nÃ£o reconhecido',
    'pfunc_expr_unclosed_bracket'           => 'Erro de expressÃ£o: ParÃªntese nÃ£o fechado',
    'pfunc_expr_division_by_zero'           => 'DivisÃ£o por zero',
    'pfunc_expr_unknown_error'              => 'Erro de expressÃ£o: Erro desconhecido ($1)',
    'pfunc_expr_not_a_number'               => 'Em $1: resultado nÃ£o Ã© um nÃºmero',
    'pfunc_ifexist_warning'                 => 'Aviso: Esta pÃ¡gina contÃ©m demasiadas chamadas #ifexist. DeverÃ¡ ter menos de $2, mas neste momento existem $1.',
    'pfunc_max_ifexist_category'            => "PÃ¡ginas com demasiadas chamadas 'ifexist'",
);

/** Russian (Ð ÑÑÑÐºÐ¸Ð¹)
 * @author .:Ajvol:.
 */
$messages['ru'] = array(
    'pfunc_desc'                            => 'Ð£Ð»ÑÑÑÐµÐ½Ð½ÑÐ¹ ÑÐ¸Ð½ÑÐ°ÐºÑÐ¸ÑÐµÑÐºÐ¸Ð¹ Ð°Ð½Ð°Ð»Ð¸Ð·Ð°ÑÐ¾Ñ Ñ Ð»Ð¾Ð³Ð¸ÑÐµÑÐºÐ¸Ð¼Ð¸ ÑÑÐ½ÐºÑÐ¸ÑÐ¼Ð¸',
    'pfunc_time_error'                      => 'ÐÑÐ¸Ð±ÐºÐ°: Ð½ÐµÐ¿ÑÐ°Ð²Ð¸Ð»ÑÐ½Ð¾Ðµ Ð²ÑÐµÐ¼Ñ',
    'pfunc_time_too_long'                   => 'ÐÑÐ¸Ð±ÐºÐ°: ÑÐ»Ð¸ÑÐºÐ¾Ð¼ Ð¼Ð½Ð¾Ð³Ð¾ Ð²ÑÐ·Ð¾Ð²Ð¾Ð² ÑÑÐ½ÐºÑÐ¸Ð¸ #time',
    'pfunc_rel2abs_invalid_depth'           => 'ÐÑÐ¸Ð±ÐºÐ°: Ð¾ÑÐ¸Ð±Ð¾ÑÐ½Ð°Ñ Ð³Ð»ÑÐ±Ð¸Ð½Ð° Ð¿ÑÑÐ¸: Â«$1Â» (Ð¿Ð¾Ð¿ÑÑÐºÐ° Ð´Ð¾ÑÑÑÐ¿Ð° Ðº ÑÐ·Ð»Ñ, Ð½Ð°ÑÐ¾Ð´ÑÑÐµÐ¼ÑÑÑ Ð²ÑÑÐµ, ÑÐµÐ¼ ÐºÐ¾ÑÐ½ÐµÐ²Ð¾Ð¹)',
    'pfunc_expr_stack_exhausted'            => 'ÐÑÐ¸Ð±ÐºÐ° Ð²ÑÑÐ°Ð¶ÐµÐ½Ð¸Ñ: Ð¿ÐµÑÐµÐ¿Ð¾Ð»Ð½ÐµÐ½Ð¸Ðµ ÑÑÐµÐºÐ°',
    'pfunc_expr_unexpected_number'          => 'ÐÑÐ¸Ð±ÐºÐ° Ð²ÑÑÐ°Ð¶ÐµÐ½Ð¸Ñ: Ð½ÐµÐ¾Ð¶Ð¸Ð´Ð°ÐµÐ¼Ð¾Ðµ ÑÐ¸ÑÐ»Ð¾',
    'pfunc_expr_preg_match_failure'         => 'ÐÑÐ¸Ð±ÐºÐ° Ð²ÑÑÐ°Ð¶ÐµÐ½Ð¸Ñ: ÑÐ±Ð¾Ð¹ preg_match',
    'pfunc_expr_unrecognised_word'          => 'ÐÑÐ¸Ð±ÐºÐ° Ð²ÑÑÐ°Ð¶ÐµÐ½Ð¸Ñ: Ð½ÐµÐ¾Ð¿Ð¾Ð·Ð½Ð°Ð½Ð½Ð¾Ðµ ÑÐ»Ð¾Ð²Ð¾ Â«$1Â»',
    'pfunc_expr_unexpected_operator'        => 'ÐÑÐ¸Ð±ÐºÐ° Ð²ÑÑÐ°Ð¶ÐµÐ½Ð¸Ñ: Ð½ÐµÐ¾Ð¶Ð¸Ð´Ð°ÐµÐ¼ÑÐ¹ Ð¾Ð¿ÐµÑÐ°ÑÐ¾Ñ $1',
    'pfunc_expr_missing_operand'            => 'ÐÑÐ¸Ð±ÐºÐ° Ð²ÑÑÐ°Ð¶ÐµÐ½Ð¸Ñ: $1 Ð½Ðµ ÑÐ²Ð°ÑÐ°ÐµÑ Ð¾Ð¿ÐµÑÐ°Ð½Ð´Ð°',
    'pfunc_expr_unexpected_closing_bracket' => 'ÐÑÐ¸Ð±ÐºÐ° Ð²ÑÑÐ°Ð¶ÐµÐ½Ð¸Ñ: Ð½ÐµÐ¾Ð¶Ð¸Ð´Ð°ÐµÐ¼Ð°Ñ Ð·Ð°ÐºÑÑÐ²Ð°ÑÑÐ°Ñ ÑÐºÐ¾Ð±ÐºÐ°',
    'pfunc_expr_unrecognised_punctuation'   => 'ÐÑÐ¸Ð±ÐºÐ° Ð²ÑÑÐ°Ð¶ÐµÐ½Ð¸Ñ: Ð½ÐµÐ¾Ð¿Ð¾Ð·Ð½Ð°Ð½Ð½ÑÐ¹ ÑÐ¸Ð¼Ð²Ð¾Ð» Ð¿ÑÐ½ÐºÑÑÐ°ÑÐ¸Ð¸ Â«$1Â»',
    'pfunc_expr_unclosed_bracket'           => 'ÐÑÐ¸Ð±ÐºÐ° Ð²ÑÑÐ°Ð¶ÐµÐ½Ð¸Ñ: Ð½ÐµÐ·Ð°ÐºÑÑÑÐ°Ñ ÑÐºÐ¾Ð±ÐºÐ°',
    'pfunc_expr_division_by_zero'           => 'ÐÐµÐ»ÐµÐ½Ð¸Ðµ Ð½Ð° Ð½Ð¾Ð»Ñ',
    'pfunc_expr_unknown_error'              => 'ÐÑÐ¸Ð±ÐºÐ° Ð²ÑÑÐ°Ð¶ÐµÐ½Ð¸Ñ: Ð½ÐµÐ¸Ð·Ð²ÐµÑÑÐ½Ð°Ñ Ð¾ÑÐ¸Ð±ÐºÐ° ($1)',
    'pfunc_expr_not_a_number'               => 'Ð $1: ÑÐµÐ·ÑÐ»ÑÑÐ°Ñ Ð½Ðµ ÑÐ²Ð»ÑÐµÑÑÑ ÑÐ¸ÑÐ»Ð¾Ð¼',
    'pfunc_ifexist_warning'                 => 'ÐÐ½Ð¸Ð¼Ð°Ð½Ð¸Ðµ: ÑÑÐ° ÑÑÑÐ°Ð½Ð¸ÑÐ° ÑÐ¾Ð´ÐµÑÐ¶Ð¸Ñ $1 {{PLURAL:$1|Ð²ÑÐ·Ð¾Ð²|Ð²ÑÐ·Ð¾Ð²Ð°|Ð²ÑÐ·Ð¾Ð²Ð¾Ð²}} #ifexist Ð¿ÑÐ¸ Ð¾Ð³ÑÐ°Ð½Ð¸ÑÐµÐ½Ð¸Ð¸ Ð² $2.',
    'pfunc_max_ifexist_category'            => 'Ð¡ÑÑÐ°Ð½Ð¸ÑÑ, Ð² ÐºÐ¾ÑÐ¾ÑÑÑ ÑÐ»Ð¸ÑÐºÐ¾Ð¼ Ð¼Ð½Ð¾Ð³Ð¾ Ð²ÑÐ·Ð¾Ð²Ð¾Ð² ifexist',
);

/** Yakut (Ð¡Ð°ÑÐ° ÑÑÐ»Ð°)
 * @author HalanTul
 */
$messages['sah'] = array(
    'pfunc_desc'                            => 'ÐÐ¾Ð³Ð¸ÑÐµÑÐºÐ°Ð¹ ÑÑÐ½ÐºÑÐ¸ÑÐ»Ð°Ð°Ñ ÑÑÐ¿ÑÐ°ÑÑÐ»Ð»ÑÐ±ÑÑ ÑÐ¸Ð½ÑÐ°ÐºÑÐ¸ÑÐµÑÐºÐ°Ð¹ Ð°Ð½Ð°Ð»Ð¸Ð·Ð°ÑÐ¾Ñ',
    'pfunc_time_error'                      => 'ÐÐ»ÒÐ°Ñ: ÑÑÑÒ»Ð° ÐºÑÐ¼',
    'pfunc_time_too_long'                   => 'ÐÐ»ÒÐ°Ñ: #time ÑÑÐ½ÐºÑÐ¸Ñ Ð½Ð°Ò»Ð°Ð° ÑÐ»Ð±ÑÑÑÐ¸Ðº ÑÐ°ÑÑÐ»Ð°Ð¼Ð¼ÑÑ',
    'pfunc_rel2abs_invalid_depth'           => 'ÐÐ»ÒÐ°Ñ: Ð¾ÑÐ¸Ð±Ð¾ÑÐ½Ð°Ñ Ð³Ð»ÑÐ±Ð¸Ð½Ð° Ð¿ÑÑÐ¸: Â«$1Â» (Ð¿Ð¾Ð¿ÑÑÐºÐ° Ð´Ð¾ÑÑÑÐ¿Ð° Ðº ÑÐ·Ð»Ñ, Ð½Ð°ÑÐ¾Ð´ÑÑÐµÐ¼ÑÑÑ Ð²ÑÑÐµ, ÑÐµÐ¼ ÐºÐ¾ÑÐ½ÐµÐ²Ð¾Ð¹)',
    'pfunc_expr_stack_exhausted'            => 'ÐÑÐ¸Ð±ÐºÐ° Ð²ÑÑÐ°Ð¶ÐµÐ½Ð¸Ñ: Ð¿ÐµÑÐµÐ¿Ð¾Ð»Ð½ÐµÐ½Ð¸Ðµ ÑÑÐµÐºÐ°',
    'pfunc_expr_unexpected_number'          => 'ÐÐ»ÒÐ°Ñ: ÐºÑÑÑÒ»Ð¸Ð»Ð»Ð¸Ð±ÑÑÑÑ ÑÑÑÒ»ÑÐ»Ð°',
    'pfunc_expr_preg_match_failure'         => 'ÐÐ»ÒÐ°Ñ: preg_match Ð¼Ð¾Ò»ÑÐ¾ÐºÑÐ°Ð½Ð½Ð°',
    'pfunc_expr_unrecognised_word'          => 'ÐÐ»ÒÐ°Ñ: Ð±Ð¸Ð»Ð»Ð¸Ð±ÑÑ ÑÑÐ» Â«$1Â»',
    'pfunc_expr_unexpected_operator'        => 'ÐÐ»ÒÐ°Ñ: ÐºÑÑÑÒ»Ð¸Ð»Ð»Ð¸Ð±ÑÑÑÑ Ð¾Ð¿ÐµÑÐ°ÑÐ¾Ñ $1',
    'pfunc_expr_missing_operand'            => 'ÐÐ»ÒÐ°Ñ: $1 Ð¾Ð¿ÐµÑÐ°Ð½Ð´Ð° ÑÐ¸Ð¸Ð¹Ð±ÑÑ',
    'pfunc_expr_unexpected_closing_bracket' => 'ÐÐ»ÒÐ°Ñ: ÐºÑÑÑÒ»Ð¸Ð»Ð»Ð¸Ð±ÑÑÑÑ ÑÐ°Ð±Ð°Ñ ÑÑÐºÑÐ¾Ð¿ÐºÐ°',
    'pfunc_expr_unrecognised_punctuation'   => 'ÐÐ»ÒÐ°Ñ: Ð±Ð¸Ð»Ð»Ð¸Ð±ÑÑ Ð¿ÑÐ½ÐºÑÑÐ°ÑÐ¸Ñ Ð±ÑÐ»Ð¸ÑÑÑ Â«$1Â»',
    'pfunc_expr_unclosed_bracket'           => 'ÐÐ»ÒÐ°Ñ: ÑÐ°Ð±ÑÐ»Ð»ÑÐ±Ð°ÑÐ°Ñ ÑÑÐºÑÐ¾Ð¿ÐºÐ°',
    'pfunc_expr_division_by_zero'           => 'ÐÑÑÐ»Ð³Ð° ÑÒ¯Ò¥ÑÑÑÐ¸Ð¸',
    'pfunc_expr_unknown_error'              => 'Expression error (Ð¾ÑÐ¸Ð±ÐºÐ° Ð²ÑÑÐ°Ð¶ÐµÐ½Ð¸Ñ): ÐÐ¸Ð»Ð»Ð¸Ð±ÑÑ Ð°Ð»ÒÐ°Ñ ($1)',
    'pfunc_expr_not_a_number'               => '$1 Ð¸Ò»Ð¸Ð³ÑÑ: ÑÐ¿Ð¿Ð¸ÑÑÑ ÑÑÑÒ»ÑÐ»Ð° Ð±ÑÐ¾Ð»Ð±Ð°ÑÐ°Ñ',
    'pfunc_ifexist_warning'                 => 'ÐÐ¾Ð»ÒÐ¾Ð¹: Ð±Ñ ÑÐ¸ÑÑÐ¹ $1 {{PLURAL:$1|ÑÒ¥ÑÑÑÑÐ»Ð°Ð°Ñ|ÑÒ¥ÑÑÑÑÐ»Ð°ÑÐ´Ð°Ð°Ñ}}, #ifexist Ð±Ð°ÑÑÐ°Ð½Ð½Ð°Ð½ ÑÐ°Ð°ÑÑÐ°ÑÑÐ°Ð¼Ð¼ÑÑÑÐ½ Ò¯ÑÐ´Ò¯Ð½ÑÐ½ $2.',
    'pfunc_max_ifexist_category'            => 'ifexist ÑÒ¥ÑÑÑÑÐ»Ð°Ñ Ð½Ð°Ò»Ð°Ð° ÑÐ»Ð±ÑÑÑÐ¸Ðº ÐºÓ©ÑÑÓ©Ñ ÑÐ¸ÑÑÐ¹Ð´ÑÑÑ',
);

/** Slovak (SlovenÄina)
 * @author Helix84
 */
$messages['sk'] = array(
    'pfunc_desc'                            => 'RozÅ¡Ã­renie syntaktickÃ©ho analyzÃ¡tora o logickÃ© funkcie',
    'pfunc_time_error'                      => 'Chyba: NeplatnÃ½ Äas',
    'pfunc_time_too_long'                   => 'Chyba: prÃ­liÅ¡ veÄ¾a volanÃ­ #time',
    'pfunc_rel2abs_invalid_depth'           => 'Chyba: NeplatnÃ¡ hÄºbka v ceste: â$1â (pokus o prÃ­stup k uzlu nad koreÅovÃ½m uzlom)',
    'pfunc_expr_stack_exhausted'            => 'Chyba vÃ½razu: ZÃ¡sobnÃ­k vyÄerpanÃ½',
    'pfunc_expr_unexpected_number'          => 'Chyba vÃ½razu: NeoÄakÃ¡vanÃ© ÄÃ­slo',
    'pfunc_expr_preg_match_failure'         => 'Chyba vÃ½razu: NeoÄakÃ¡vanÃ© zlyhanie funkcie preg_match',
    'pfunc_expr_unrecognised_word'          => 'Chyba vÃ½razu: NerozpoznanÃ© slovo â$1â',
    'pfunc_expr_unexpected_operator'        => 'Chyba vÃ½razu: NeoÄakÃ¡vanÃ½ operÃ¡tor $1',
    'pfunc_expr_missing_operand'            => 'Chyba vÃ½razu: ChÃ½bajÃºci operand pre $1',
    'pfunc_expr_unexpected_closing_bracket' => 'Chyba vÃ½razu: NeoÄakÃ¡vanÃ¡ zatvÃ¡rajÃºca hranatÃ¡ zÃ¡tvorka',
    'pfunc_expr_unrecognised_punctuation'   => 'Chyba vÃ½razu: NerozpoznanÃ© diakritickÃ© znamienko â$1â',
    'pfunc_expr_unclosed_bracket'           => 'Chyba vÃ½razu: NeuzavretÃ¡ hranatÃ¡ zÃ¡tvorka',
    'pfunc_expr_division_by_zero'           => 'Chyba vÃ½razu: Delenie nulou',
    'pfunc_expr_unknown_error'              => 'Chyba vÃ½razu: NeznÃ¡ma chyba ($1)',
    'pfunc_expr_not_a_number'               => 'V $1: vÃ½sledok nie je ÄÃ­slo',
    'pfunc_ifexist_warning'                 => 'Upozornenie: TÃ¡to strÃ¡nka obsahuje prÃ­liÅ¡ veÄ¾a volanÃ­ #ifexist. Malo by ich byÅ¥ menej ako $2, momentÃ¡lne ich je $1.',
    'pfunc_max_ifexist_category'            => 'StrÃ¡nky s prÃ­liÅ¡ veÄ¾kÃ½m poÄtom volanÃ­ ifexist',
);

/** Seeltersk (Seeltersk)
 * @author Pyt
 */
$messages['stq'] = array(
    'pfunc_time_error'                      => 'Failer: uungultige Tiedangoawe',
    'pfunc_time_too_long'                   => 'Failer: tou fuul #time-Aproupe',
    'pfunc_rel2abs_invalid_depth'           => 'Failer: uungultige DjÃ¼pte in Paad: â$1â (FersÃ¤ik, ap n KnÃ¤ttepunkt buppe dÃ¤n HaudknÃ¤ttepunkt toutougriepen)',
    'pfunc_expr_stack_exhausted'            => 'Expression-Failer: Stack-Uurloop',
    'pfunc_expr_unexpected_number'          => 'Expression-Failer: Nit ferwachtede Taal',
    'pfunc_expr_preg_match_failure'         => 'Expression-Failer: Uunferwachtede âpreg_matchâ-Failfunktion',
    'pfunc_expr_unrecognised_word'          => 'Expression-Failer: Nit wierkoand Woud â$1â',
    'pfunc_expr_unexpected_operator'        => 'Expression-Failer: Uunferwachteden Operator: <strong><tt>$1</tt></strong>',
    'pfunc_expr_missing_operand'            => 'Expression-Failer: Failenden Operand foar <strong><tt>$1</tt></strong>',
    'pfunc_expr_unexpected_closing_bracket' => 'Expression-Failer: Uunferwachte sluutende kaantige Klammere',
    'pfunc_expr_unrecognised_punctuation'   => 'Expression-Failer: Nit wierkoand Satsteeken â$1â',
    'pfunc_expr_unclosed_bracket'           => 'Expression-Failer: Nit sleetene kaantige Klammer',
    'pfunc_expr_division_by_zero'           => 'Expression-Failer: Division truch Null',
    'pfunc_expr_unknown_error'              => 'Expression-Failer: Uunbekoanden Failer ($1)',
    'pfunc_expr_not_a_number'               => 'Expression-Failer: In $1: Resultoat is neen Taal',
    'pfunc_ifexist_warning'                 => 'Woarschauenge: Disse Siede Ã¤nthaalt toufuul #ifexist-Aproupe. Der duuren maximoal $2 Aproupe weese, der sunt oawers $1 Aproupe.',
    'pfunc_max_ifexist_category'            => 'Sieden mÃ¤d toufuul ifexist-Aproupe',
);

/** Swedish (Svenska)
 * @author Lejonel
 */
$messages['sv'] = array(
    'pfunc_desc'                            => 'LÃ¤gger till logiska funktioner i parsern',
    'pfunc_time_error'                      => 'Fel: ogiltig tid',
    'pfunc_time_too_long'                   => 'Fel: fÃ¶r mÃ¥nga anrop av #time',
    'pfunc_rel2abs_invalid_depth'           => 'Fel: felaktig djup i sÃ¶kvÃ¤g: "$1" (fÃ¶rsÃ¶ker nÃ¥ en nod ovanfÃ¶r rotnoden)',
    'pfunc_expr_stack_exhausted'            => 'Fel i uttryck: Stackutrymmet tog slut',
    'pfunc_expr_unexpected_number'          => 'Fel i uttryck: OvÃ¤ntat tal',
    'pfunc_expr_preg_match_failure'         => 'Fel i uttryck: OvÃ¤ntad fel i preg_match',
    'pfunc_expr_unrecognised_word'          => 'Fel i uttryck: OkÃ¤nt ord "$1"',
    'pfunc_expr_unexpected_operator'        => 'Fel i uttryck: OvÃ¤ntad operator $1',
    'pfunc_expr_missing_operand'            => 'Fel i uttryck: Operand saknas fÃ¶r $1',
    'pfunc_expr_unexpected_closing_bracket' => 'Fel i uttryck: OvÃ¤ntad avslutande parentes',
    'pfunc_expr_unrecognised_punctuation'   => 'Fel i uttryck: OkÃ¤nt interpunktionstecken "$1"',
    'pfunc_expr_unclosed_bracket'           => 'Fel i uttryck: Oavslutad parentes',
    'pfunc_expr_division_by_zero'           => 'Division med noll',
    'pfunc_expr_unknown_error'              => 'Fel i uttryck: OkÃ¤nt fel ($1)',
    'pfunc_expr_not_a_number'               => 'I $1: resultatet Ã¤r inte ett tal',
    'pfunc_ifexist_warning'                 => 'Varning: Den hÃ¤r sidan innehÃ¥ller fÃ¶r mÃ¥nga anrop av #ifexist. Antalet anrop mÃ¥ste vara mindre Ã¤n $2, nu Ã¤r det $1.',
    'pfunc_max_ifexist_category'            => 'Sidor med fÃ¶r mÃ¥nga ifexist-anrop',
);

/** Telugu (à¤àà²ààà)
 * @author Mpradeep
 * @author Veeven
 */
$messages['te'] = array(
    'pfunc_time_error'                      => 'à²ààªà: à¸à®à¯à à¸à°à¿àààà¾ à²àà¦à',
    'pfunc_time_too_long'                   => 'à²ààªà: #timeà¨à àà¾à²à¾ à¸à¾à°àà²à ààªà¯ààà¿ààà¾à°à',
    'pfunc_rel2abs_invalid_depth'           => 'à²ààªà: àªà¾à¤à à¯àààà à¡ààªàà¤à à¸à°à¿àààà¾à²àà¦à: "$1" (à°ààà à¨àà¡à àààà àªàà¨ àà¨àà¨ à¨àà¡à ààªà¯ààà¿àààà¾à¨à¿àà¿ àªàà°à¯à¤àà¨à àà°à¿àà¿àà¦à¿)',
    'pfunc_expr_stack_exhausted'            => 'à¸à®à¾à¸àà²à(Expression) à²ààªà: à¸ààà¾àà à®àà¤àà¤à àà¯à¿àªàà¯à¿àà¦à¿',
    'pfunc_expr_unexpected_number'          => 'à¸à®à¾à¸àà²à(Expression) à²ààªà: àà¹à¿ààà¨à¿ à¸àààà¯ àµàààà¿àà¦à¿',
    'pfunc_expr_preg_match_failure'         => 'à¸à®à¾à¸àà²à(Expression) à²ààªà: preg_matchà²à àà¹à¿ààà¨à¿ àµà¿à«à²à',
    'pfunc_expr_unrecognised_word'          => 'à¸à®à¾à¸àà²à(Expression) à²ààªà: "$1" àà¨à àªà¦à¾à¨àà¨à¿ ààà°àà¤ààªàààà²àààªàà¤àà¨àà¨à¾à¨à',
    'pfunc_expr_unexpected_operator'        => 'à¸à®à¾à¸àà²à(Expression) à²ààªà: $1 àªà°à¿àà°àà¤à¨à(operator) àà¹à¿ààà²àà¦à',
    'pfunc_expr_missing_operand'            => 'à¸à®à¾à¸àà²à(Expression) à²ààªà: $1àà àà ààªà°à¾àà¡àà¨à ààµààµà²àà¦à',
    'pfunc_expr_unexpected_closing_bracket' => 'à¸à®à¾à¸àà²à(Expression) à²ààªà: àà¹à¿ààà¨à¿ à¬àà°à¾àààààà à®ààà¿ààªà',
    'pfunc_expr_unrecognised_punctuation'   => 'à¸à®à¾à¸àà²à(Expression) à²ààªà: "$1" àà¨à àµà¿à°à¾à® àà¿à¹àà¨à¾à¨àà¨à¿ ààà°àà¤à¿ààà²àààªàà¤àà¨àà¨à¾à¨à',
    'pfunc_expr_unclosed_bracket'           => 'à¸à®à¾à¸àà²à(Expression) à²ààªà: à¬àà°à¾ààààààà¨à à®àà¯à²àà¦à',
    'pfunc_expr_division_by_zero'           => 'à¸àà¨àà¨à¾à¤à à­à¾àà¿ààà¾à°à',
    'pfunc_expr_unknown_error'              => 'à¸à®à¾à¸àà²à(Expression) à²ààªà: à¤àà²à¿à¯à¨à¿ à²ààªà ($1)',
    'pfunc_expr_not_a_number'               => '$1à²à: àµàààà¿à¨ à«à²à¿à¤à à¸àààà¯ àà¾à¦à',
    'pfunc_ifexist_warning'                 => 'à¹ààààà°à¿à: à àªàààà²à #ifexistà²à¨à àà¾à²à¾à¸à¾à°àà²à ààªà¯ààà¿ààà¾à°à. àªàà°à¸àà¤àà¤à $1 à¸à¾à°àà²à ààªà¯ààà¿ààà¾à°à, àà¾à¨à $2 à¸à¾à°àà²à àààà ààààààµ à¸à¾à°àà²à ààªà¯ààà¿ààààà¡à¦à.',
    'pfunc_max_ifexist_category'            => 'ifexistà²à¨à à®à°à ààààààµàà¾ ààªà¯ààà¿à¸àà¤àà¨àà¨ àªàààà²à.',
);

/** Tajik (Ð¢Ð¾Ò·Ð¸ÐºÓ£)
 * @author Ibrahim
 */
$messages['tg'] = array(
    'pfunc_desc'                            => 'ÐÐ° ÑÐ°Ò·Ð·ÐµÒ³ÐºÑÐ½Ð°Ð½Ð´Ð°, Ð´Ð°ÑÑÑÑÒ³Ð¾Ð¸ Ð¼Ð°Ð½ÑÐ¸ÒÓ£ Ð¼ÐµÐ°ÑÐ·Ð¾ÑÐ´',
    'pfunc_time_error'                      => 'Ð¥Ð°ÑÐ¾: Ð·Ð°Ð¼Ð¾Ð½Ð¸ ÒÐ°Ð¹ÑÐ¸Ð¼Ð¸Ò·Ð¾Ð·',
    'pfunc_time_too_long'                   => 'Ð¥Ð°ÑÐ¾: #time ÑÐ°ÑÐ¾ÑÐ¾Ð½Ð¸Ð¸ Ð±ÐµÑ Ð°Ð· Ò³Ð°Ð´',
    'pfunc_rel2abs_invalid_depth'           => 'Ð¥Ð°ÑÐ¾: Ð§ÑÒÑÑÐ¸Ð¸ ÒÐ°Ð¹ÑÐ¸Ð¼Ð¸Ò·Ð¾Ð· Ð´Ð°Ñ Ð½Ð¸ÑÐ¾Ð½Ó£: "$1" (ÑÐ°Ð»Ð¾Ñ Ð±Ð°ÑÐ¾Ð¸ Ð´Ð°ÑÑÑÐ°ÑÐ¸ Ð±Ð° ÑÐº Ð½Ð¸ÑÐ¾Ð½Ó£ Ð±Ð¾Ð»Ð¾ÑÐ°Ñ Ð°Ð· Ð½Ð¸ÑÐ¾Ð½Ð¸Ð¸ ÑÐµÑÐ°)',
    'pfunc_expr_stack_exhausted'            => 'Ð¥Ð°ÑÐ¾Ð¸ Ð¸Ð±Ð¾ÑÐ°: ÐÑÑÑÐ° Ð°Ð· Ð´Ð°ÑÑ ÑÐ°ÑÑÐ°Ð°ÑÑ',
    'pfunc_expr_unexpected_number'          => 'Ð¥Ð°ÑÐ¾Ð¸ Ð¸Ð±Ð¾ÑÐ°: ÐÐ´Ð°Ð´Ð¸ ÒÐ°Ð¹ÑÐ¸Ð¼ÑÐ½ÑÐ°Ð·Ð¸Ñ',
    'pfunc_expr_preg_match_failure'         => 'Ð¥Ð°ÑÐ¾Ð¸ Ð¸Ð±Ð¾ÑÐ°: Ð¥Ð°ÑÐ¾Ð¸ ÒÐ°Ð¹ÑÐ¸Ð¼ÑÐ½ÑÐ°Ð·Ð¸ÑÐ¸ preg_match',
    'pfunc_expr_unrecognised_word'          => 'Ð¥Ð°ÑÐ¾Ð¸ Ð¸Ð±Ð¾ÑÐ°: ÐÐ°Ð»Ð¸Ð¼Ð°Ð¸ Ð½Ð¾ÑÐ¸Ð½Ð¾ÑÑÐ° "$1"',
    'pfunc_expr_unexpected_operator'        => 'Ð¥Ð°ÑÐ¾Ð¸ Ð¸Ð±Ð¾ÑÐ°: ÐÐ¼Ð°Ð»Ð³Ð°ÑÐ¸ ÒÐ°Ð¹ÑÐ¸Ð¼ÑÐ½ÑÐ°Ð·Ð¸ÑÐ¸ $1',
    'pfunc_expr_missing_operand'            => 'Ð¥Ð°ÑÐ¾Ð¸ Ð¸Ð±Ð¾ÑÐ°: ÐÐ¼Ð°Ð»Ð³Ð°ÑÐ¸ Ð³ÑÐ¼ÑÑÐ´Ð° Ð±Ð°ÑÐ¾Ð¸  $1',
    'pfunc_expr_unexpected_closing_bracket' => 'Ð¥Ð°ÑÐ¾Ð¸ Ð¸Ð±Ð¾ÑÐ°: ÒÐ°ÑÑÐ¸ Ð±Ð°ÑÑÐ°Ð¸ Ð½Ð¾Ð¼ÑÐ½ÑÐ°Ð·Ð¸Ñ',
    'pfunc_expr_unrecognised_punctuation'   => 'Ð¥Ð°ÑÐ¾Ð¸ Ð¸Ð±Ð¾ÑÐ°: ÐÐ»Ð¾Ð¼Ð°ÑÐ¸ Ð½ÑÒÑÐ°Ð³ÑÐ·Ð¾ÑÐ¸Ð¸ ÑÐ¸Ð½Ð¾ÑÑÐ°Ð½Ð°ÑÑÐ´Ð° "$1"',
    'pfunc_expr_unclosed_bracket'           => 'Ð¥Ð°ÑÐ¾Ð¸ Ð¸Ð±Ð¾ÑÐ°: ÒÐ°ÑÑÐ¸ Ð±Ð°ÑÑÐ°Ð½Ð°ÑÑÐ´Ð°',
    'pfunc_expr_division_by_zero'           => 'Ð¢Ð°ÒÑÐ¸Ð¼ Ð±Ð°Ñ ÑÐ¸ÑÑ',
    'pfunc_expr_unknown_error'              => 'Ð¥Ð°ÑÐ¾Ð¸ Ð¸Ð±Ð¾ÑÐ°: Ð¥Ð°ÑÐ¾Ð¸ Ð½Ð¾ÑÐ¸Ð½Ð¾Ñ ($1)',
    'pfunc_expr_not_a_number'               => 'ÐÐ°Ñ $1: Ð½Ð°ÑÐ¸Ò·Ð° Ð°Ð´Ð°Ð´ Ð½ÐµÑÑ',
    'pfunc_ifexist_warning'                 => 'Ò²ÑÑÐ´Ð¾Ñ: ÐÐ½ ÑÐ°Ò³Ð¸ÑÐ° ÑÐ°ÑÐ¾ÑÐ¾Ð½Ð¸Ò³Ð¾Ð¸ #ifexist Ð±Ð¸ÑÑÑÐµÑÐ¾ Ð´Ð°Ñ Ð±Ð°Ñ Ð¼ÐµÐ³Ð¸ÑÐ°Ð´. ÐÐ¾ÑÐ´ ÐºÐ°Ð¼ Ð°Ð· $2 Ð´Ð¾ÑÑÐ° Ð±Ð¾ÑÐ°Ð´, Ò³Ð°Ð¼Ð°ÐºÐ½ÑÐ½ ÑÐµÑÐ´Ð¾Ð´Ð¸ Ð¾Ð½ $1 Ð°ÑÑ.',
    'pfunc_max_ifexist_category'            => 'Ð¡Ð°Ò³Ð¸ÑÐ°Ò³Ð¾Ð¸ Ð´Ð¾ÑÐ¾Ð¸ Ð±ÐµÑ Ð°Ð· Ò³Ð°Ð´ ÑÐ°ÑÐ¾ÑÐ¾Ð½Ð¸Ò³Ð¾Ð¸ ifexist',
);

/** Vietnamese (Tiá¿ng Viát)
 * @author Vinhtantran
 * @author Minh Nguyen
 */
$messages['vi'] = array(
    'pfunc_desc'                            => 'NÃ¢ng cao bá xá­ lÃ½ vái nhá¯ng hÃ m cÃº phÃ¡p lÃ´gic',
    'pfunc_time_error'                      => 'Lái: thái gian khÃ´ng há£p lá',
    'pfunc_time_too_long'                   => 'Lái: quÃ¡ nhiáu lá§n gái #time',
    'pfunc_rel2abs_invalid_depth'           => 'Lái: Äá sÃ¢u khÃ´ng há£p lá trong ÄÆ°áng dá«n â$1â (do cá gá¯ng truy cá­p nÃºt phÃ­a trÃªn nÃºt gác)',
    'pfunc_expr_stack_exhausted'            => 'Lái biáu thá©c: ÄÃ£ cá¡n stack',
    'pfunc_expr_unexpected_number'          => 'Lái biáu thá©c: DÆ° sá',
    'pfunc_expr_preg_match_failure'         => 'Lái biáu thá©c: HÃ m preg_match thá¥t bá¡i',
    'pfunc_expr_unrecognised_word'          => 'Lái biáu thá©c: Tá« â$1â khÃ´ng rÃµ rÃ ng',
    'pfunc_expr_unexpected_operator'        => "Lái biáu thá©c: DÆ° toÃ¡n tá­ '''$1'''",
    'pfunc_expr_missing_operand'            => 'Lái biáu thá©c: Thiá¿u toÃ¡n há¡ng trong $1',
    'pfunc_expr_unexpected_closing_bracket' => 'Lái biáu thá©c: DÆ° dá¥u ÄÃ³ng ngoá·c',
    'pfunc_expr_unrecognised_punctuation'   => 'Lái biáu thá©c: Dá¥u cÃ¢u â$1â khÃ´ng rÃµ rÃ ng',
    'pfunc_expr_unclosed_bracket'           => 'Lái biáu thá©c: Dá¥u ngoá·c chÆ°a ÄÆ°á£c ÄÃ³ng',
    'pfunc_expr_division_by_zero'           => 'Chia cho zero',
    'pfunc_expr_unknown_error'              => 'Lái biáu thá©c: Lái khÃ´ng rÃµ nguyÃªn nhÃ¢n ($1)',
    'pfunc_expr_not_a_number'               => 'Trong $1: ká¿t quá£ khÃ´ng phá£i lÃ  kiáu sá',
    'pfunc_ifexist_warning'                 => 'Cá£nh bÃ¡o: Trang nÃ y cÃ³ quÃ¡ nhiáu lá§n gái hÃ m #ifexist. Sá lá§n gái nÃªn Ã­t hÆ¡n $2, hián cÃ³ Äá¿n $1 lá§n gái.',
    'pfunc_max_ifexist_category'            => 'Trang cÃ³ quÃ¡ nhiáu hÃ m gái ifexist',
);

/** VolapÃ¼k (VolapÃ¼k)
 * @author Smeira
 */
$messages['vo'] = array(
    'pfunc_time_error'            => 'PÃ¶k: tim no lonÃ¶fÃ¶l',
    'pfunc_expr_division_by_zero' => 'MÃ¼edam dub ser',
    'pfunc_expr_not_a_number'     => 'In $1: sek no binon num',
);

/** çµè (çµè) */
$messages['yue'] = array(
    'pfunc_time_error'            => 'é¯: åå±åæé',
    'pfunc_time_too_long'         => 'é¯: åªå #time å¼å«',
    'pfunc_rel2abs_invalid_depth' => 'é¯: åå±è¯ååæ±å¦: "$1" (å²çè¦éç±é­éè½åéå¦)',
);

/** Simplified Chinese (âªä­æ(çä)â¬) */
$messages['zh-hans'] = array(
    'pfunc_time_error'            => 'éè¯: äæ£ç®çæ¶é´',
    'pfunc_time_too_long'         => 'éè¯: èå #time çå¼å«',
    'pfunc_rel2abs_invalid_depth' => 'éè¯: äæ£ç®çè¯åæ±å¦: "$1" (å²çåèå¨é¶ç¹è¿é®è¥ç¹)',
);

/** Traditional Chinese (âªä­æ(çé)â¬) */
$messages['zh-hant'] = array(
    'pfunc_time_error'            => 'é¯è¤: äæ£çºçæé',
    'pfunc_time_too_long'         => 'é¯è¤: éå #time çå¼å«',
    'pfunc_rel2abs_invalid_depth' => 'é¯è¤: äæ£çºçè¯åæ±å¦: "$1" (å²çåè¦å¨ééååè²é)',
);


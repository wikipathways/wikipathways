<?php

/**
 * Get translated magic words, if available
 *
 * @param string $lang Language code
 * @return array
 */
function efParserFunctionsWords( $lang ) {
    $words = array();

    /**
     * English
     */
    $words['en'] = array(
        'expr'       => array( 0, 'expr' ),
        'if'         => array( 0, 'if' ),
        'ifeq'       => array( 0, 'ifeq' ),
        'ifexpr'     => array( 0, 'ifexpr' ),
        'iferror'    => array( 0, 'iferror' ),
        'switch'     => array( 0, 'switch' ),
        'default'    => array( 0, '#default' ),
        'ifexist'    => array( 0, 'ifexist' ),
        'time'       => array( 0, 'time' ),
        'timel'      => array( 0, 'timel' ),
        'rel2abs'    => array( 0, 'rel2abs' ),
        'titleparts' => array( 0, 'titleparts' ),
    );

    /**
     * Farsi-Persian
     */
    $words['fa'] = array(
        'expr'          => array( 0, 'Ø­Ø³Ø§Ø¨',         'expr' ),
        'if'          => array( 0, 'Ø§Ú¯Ø±',          'if' ),
        'ifeq'          => array( 0, 'Ø§Ú¯Ø±ÙØ³Ø§ÙÛ',     'ifeq' ),
        'iferror'    => array( 0, 'Ø§Ú¯Ø±Ø®Ø·Ø§',       'iferror' ),
        'ifexpr'      => array( 0, 'Ø§Ú¯Ø±Ø­Ø³Ø§Ø¨',      'ifexpr' ),
        'switch'      => array( 0, 'Ú¯Ø²ÛÙÙ',        'switch' ),
        'default'      => array( 0, '#Ù¾ÛØ´âÙØ±Ø¶',      '#default' ),
        'ifexist'      => array( 0, 'Ø§Ú¯Ø±ÙÙØ¬ÙØ¯',     'ifexist' ),
        'time'          => array( 0, 'Ø²ÙØ§Ù',         'time' ),
        'rel2abs'      => array( 0, 'ÙØ³Ø¨ÛâØ¨ÙâÙØ·ÙÙ',   'rel2abs' ),
        'titleparts' => array( 0, 'Ù¾Ø§Ø±ÙâØ¹ÙÙØ§Ù',    'titleparts' ),
    );

    /**
     * Hebrew
     */
    $words['he'] = array(
        'expr'       => array( 0, '××©×',         'expr' ),
        'if'         => array( 0, '×ª× ××',        'if' ),
        'ifeq'       => array( 0, '×©×××',        'ifeq' ),
        'ifexpr'     => array( 0, '××©× ×ª× ××',    'ifexpr' ),
        'iferror'    => array( 0, '×ª× ×× ×©××××',  'iferror' ),
        'switch'     => array( 0, '×××¨',         'switch' ),
        'default'    => array( 0, '#××¨××¨×ª ××××', '#default' ),
        'ifexist'    => array( 0, '×§×××',        'ifexist' ),
        'time'       => array( 0, '×××',         'time' ),
        'timel'      => array( 0, '××××',        'timel' ),
        'rel2abs'    => array( 0, '×××¡× ××××××', 'rel2abs' ),
        'titleparts' => array( 0, '×××§ ××××ª×¨×ª',  'titleparts' ),
    );

    /**
     * Indonesian
     */
    $words['id'] = array(
        'expr'       => array( 0, 'hitung',       'expr' ),
        'if'         => array( 0, 'jika',         'if' ),
        'ifeq'       => array( 0, 'jikasama',     'ifeq' ),
        'ifexpr'     => array( 0, 'jikahitung',   'ifexpr' ),
        'iferror'    => array( 0, 'jikasalah',   'iferror' ),
        'switch'     => array( 0, 'pilih',        'switch' ),
        'default'    => array( 0, '#baku',        '#default' ),
        'ifexist'    => array( 0, 'jikaada',      'ifexist' ),
        'time'       => array( 0, 'waktu',        'time' ),
        'timel'      => array( 0, 'waktu1',       'timel' ),
        'rel2abs'    => array( 0, 'rel2abs' ),
        'titleparts' => array( 0, 'bagianjudul',  'titleparts' ),
    );

    # English is used as a fallback, and the English synonyms are
    # used if a translation has not been provided for a given word
    return ( $lang == 'en' || !isset( $words[$lang] ) )
        ? $words['en']
        : array_merge( $words['en'], $words[$lang] );
}


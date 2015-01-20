<?php
    /*
     * This was written by avipedia and posted at:
     * http://avimedia.livejournal.com/1583.html.
     *
     * It is used as a php implementation of 4chan's
     * tripcode generator.
     *
     * Licence:
     *      "I'm going to follow Utility Mill's lead
     *      and license this under the GNU GPL. Quite
     *      simply, I'm not sure that I didn't borrow
     *      something from the Python code, so I'm more
     *      comfortable doing things this way."
     *
     * This is covered by FROST's GPL licence.
     */
    function mktripcode($pw)
    {
    $pw=mb_convert_encoding($pw,'SJIS','UTF-8');
    $pw=str_replace('&','&amp;',$pw);
    $pw=str_replace('"','&quot;',$pw);
    $pw=str_replace("'",'&#39;',$pw);
    $pw=str_replace('<','&lt;',$pw);
    $pw=str_replace('>','&gt;',$pw);

    $salt=substr($pw.'H.',1,2);
    $salt=preg_replace('/[^.\/0-9:;<=>?@A-Z\[\\\]\^_`a-z]/','.',$salt);
    $salt=strtr($salt,':;<=>?@[\]^_`','ABCDEFGabcdef');

    $trip=substr(crypt($pw,$salt),-10);
    return $trip;
    }
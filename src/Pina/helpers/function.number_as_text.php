<?php

if (!function_exists('semantic')) {

    function semantic($i, &$words, &$many, $f)
    {
        global $_1_2, $_1_19, $des, $hang, $namerub, $nametho, $namemil, $namemrd;
        $words = "";
        $fl = 0;

        if ($i >= 100) {
            $jkl = intval($i / 100);
            $words.=$hang[$jkl];
            $i%=100;
        }

        if ($i >= 20) {
            $jkl = intval($i / 10);
            $words.=$des[$jkl];
            $i%=10;
            $fl = 1;
        }

        switch ($i) {
            case 1: $many = 1;
                break;
            case 2:
            case 3:
            case 4: $many = 2;
                break;
            default: $many = 3;
                break;
        }

        if ($i) {
            if ($i < 3 && $f == 1) {
                $words.=$_1_2[$i];
            } else {
                $words.=$_1_19[$i];
            }
        }
    }

}

function smarty_function_number_as_text($params, &$smarty)
{
    if (!isset($params['value']))
        return '';

    $value = $params['value'];
    $first_upper = true;
    $L = $value;

    $_1_2[1] = "одна ";
    $_1_2[2] = "две ";
    $_1_19[1] = "один ";
    $_1_19[2] = "два ";
    $_1_19[3] = "три ";
    $_1_19[4] = "четыре ";
    $_1_19[5] = "пять ";
    $_1_19[6] = "шесть ";
    $_1_19[7] = "семь ";
    $_1_19[8] = "восемь ";
    $_1_19[9] = "девять ";
    $_1_19[10] = "десять ";
    $_1_19[11] = "одиннацать ";
    $_1_19[12] = "двенадцать ";
    $_1_19[13] = "тринадцать ";
    $_1_19[14] = "четырнадцать ";
    $_1_19[15] = "пятнадцать ";
    $_1_19[16] = "шестнадцать ";
    $_1_19[17] = "семнадцать ";
    $_1_19[18] = "восемнадцать ";
    $_1_19[19] = "девятнадцать ";
    $des[2] = "двадцать ";

    $des[3] = "тридцать ";
    $des[4] = "сорок ";
    $des[5] = "пятьдесят ";
    $des[6] = "шестьдесят ";
    $des[7] = "семьдесят ";
    $des[8] = "восемдесят ";
    $des[9] = "девяносто ";
    $hang[1] = "сто ";
    $hang[2] = "двести ";
    $hang[3] = "триста ";
    $hang[4] = "четыреста ";
    $hang[5] = "пятьсот ";
    $hang[6] = "шестьсот ";
    $hang[7] = "семьсот ";
    $hang[8] = "восемьсот ";
    $hang[9] = "девятьсот ";

    $namerub[1] = "рубль ";
    $namerub[2] = "рубля ";
    $namerub[3] = "рублей ";
    $nametho[1] = "тысяча ";
    $nametho[2] = "тысячи ";
    $nametho[3] = "тысяч ";

    $namemil[1] = "миллион ";
    $namemil[2] = "миллиона ";
    $namemil[3] = "миллионов ";
    $namemrd[1] = "миллиард ";
    $namemrd[2] = "миллиарда ";
    $namemrd[3] = "миллиардов ";
    $kopeek[1] = "копейка ";
    $kopeek[2] = "копейки ";
    $kopeek[3] = "копеек ";

    $s = " ";
    $s1 = " ";
    //считаем количество копеек, т.е. дробной части числа
    $kop = intval(( $L * 100 - intval($L) * 100));
    //отбрасываем дробную часть
    $L = intval($L);

    if ($L >= 1000000000) {
        $many = 0;
        semantic(intval($L / 1000000000), $s1, $many, 3);
        $s.=$s1 . $namemrd[$many];
        $L%=1000000000;
    }

    if ($L >= 1000000) {
        $many = 0;
        semantic(intval($L / 1000000), $s1, $many, 2);
        $s.=$s1 . $namemil[$many];
        $L%=1000000;
        //аналогично если ровно сколько-то миллионов, то хватит считать
    }

    if ($L >= 1000) {
        $many = 0;
        semantic(intval($L / 1000), $s1, $many, 1);
        $s.=$s1 . $nametho[$many];
        $L%=1000;
    }
    if ($L != 0) {
        $many = 0;
        semantic($L, $s1, $many, 0);
        $s.=$s1;
    }


    $result = trim($s);

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $result);
        $result = '';
    }

    return $result;
}
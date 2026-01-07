<?php

if (!function_exists('bn_number')) {
    /**
     * Convert English numbers to Bengali numerals
     *
     * @param mixed $number
     * @return string
     */
    function bn_number($number)
    {
        $bengaliDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        
        return str_replace($englishDigits, $bengaliDigits, (string)$number);
    }
}

if (!function_exists('numberToBengaliWords')) {
    /**
     * Convert a number to Bengali words
     *
     * @param float $number
     * @return string
     */
    function numberToBengaliWords($number)
    {
        $number = (int) $number;
        
        $bengaliDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        $ones = ['', 'এক', 'দুই', 'তিন', 'চার', 'পাঁচ', 'ছয়', 'সাত', 'আট', 'নয়'];
        $tens = ['', '', 'বিশ', 'ত্রিশ', 'চল্লিশ', 'পঞ্চাশ', 'ষাট', 'সত্তর', 'আশি', 'নব্বই'];
        $teens = ['দশ', 'এগারো', 'বারো', 'তেরো', 'চৌদ্দ', 'পনেরো', 'ষোলো', 'সতেরো', 'আঠারো', 'উনিশ'];
        
        if ($number == 0) {
            return 'শূন্য';
        }
        
        $words = '';
        
        // Crore (কোটি)
        if ($number >= 10000000) {
            $crore = (int)($number / 10000000);
            $words .= numberToBengaliWords($crore) . ' কোটি ';
            $number %= 10000000;
        }
        
        // Lakh (লক্ষ)
        if ($number >= 100000) {
            $lakh = (int)($number / 100000);
            $words .= numberToBengaliWords($lakh) . ' লক্ষ ';
            $number %= 100000;
        }
        
        // Thousand (হাজার)
        if ($number >= 1000) {
            $thousand = (int)($number / 1000);
            $words .= numberToBengaliWords($thousand) . ' হাজার ';
            $number %= 1000;
        }
        
        // Hundred (শত)
        if ($number >= 100) {
            $hundred = (int)($number / 100);
            $words .= $ones[$hundred] . ' শত ';
            $number %= 100;
        }
        
        // Tens and ones
        if ($number >= 20) {
            $words .= $tens[(int)($number / 10)] . ' ';
            $number %= 10;
        } elseif ($number >= 10) {
            $words .= $teens[$number - 10] . ' ';
            $number = 0;
        }
        
        if ($number > 0) {
            $words .= $ones[$number] . ' ';
        }
        
        return trim($words);
    }
}

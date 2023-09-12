<?php


if (! function_exists('getOptionsMonthsToSelect')) {
    function getOptionsMonthsToSelect(): string
    {
        $actual_month = date('m/Y');
        $date_start = strtotime('2023-09-01');
        $months = [];



        for ($i = 0; $i < 24; $i++) {
            $month_year =  date('m/Y', strtotime("+$i month", $date_start));
            $selected = $month_year === $actual_month ? 'selected' : '';
            $months[] = "<option value='$month_year' $selected>$month_year</option>";
        }

        return implode('', $months);
    }
}

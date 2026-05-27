<?php
function computeTax($salary, $sss, $ph, $pagibig, $nonTaxable, $taxableAdditional = 0) {
    $salary = (float)$salary;
    $taxableAdditional = (float)$taxableAdditional;
    $taxable = max(0, $salary + $taxableAdditional - (float)$nonTaxable - (float)$sss - (float)$ph - (float)$pagibig);
    if ($taxable <= 0) return 0;
    if ($taxable <= 20833) return 0;
    if ($taxable <= 33333) return ($taxable - 20833) * 0.15;
    if ($taxable <= 66667) return 1875 + ($taxable - 33333) * 0.20;
    if ($taxable <= 166667) return 8541.8 + ($taxable - 66667) * 0.25;
    if ($taxable <= 666667) return 33541.8 + ($taxable - 166667) * 0.30;
    return 183541.8 + ($taxable - 666667) * 0.35;
}

$vals = [1000, 22222, 50000];
foreach ($vals as $v) {
    echo "taxableAdditional={$v} => tax=" . number_format(computeTax(0,0,0,0,0,$v), 2) . "\n";
}

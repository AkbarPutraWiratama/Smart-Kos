<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Manual Transfer Destination Bank Accounts
    |--------------------------------------------------------------------------
    |
    | Bank accounts managed by Kos management for receiving manual rent
    | payments. Automatically discovered from environment variables
    | SMARTKOS_BANK_1_*, SMARTKOS_BANK_2_*, etc.
    |
    */

    'manual_banks' => (function () {
        $banks = [];
        $index = 1;

        // Dynamically discovers all configured bank numbers
        while ($index <= 50) {
            $bankName = env("SMARTKOS_BANK_{$index}_NAME");
            $accountNumber = env("SMARTKOS_BANK_{$index}_NUMBER");
            $accountHolder = env("SMARTKOS_BANK_{$index}_HOLDER");

            if ($bankName || $accountNumber) {
                $banks[] = [
                    'bank'           => $bankName,
                    'account_number' => $accountNumber,
                    'account_name'   => $accountHolder,
                ];
            } elseif ($index > 3) {
                // If past index 3 and both current and next are empty, stop scanning
                $nextBank = env('SMARTKOS_BANK_' . ($index + 1) . '_NAME');
                if (! $nextBank) {
                    break;
                }
            }

            $index++;
        }

        return $banks;
    })(),

];

<?php

namespace Loja\Communities\Enums;

/**
 * README.md RN21: by default the full installment fee is passed to the
 * customer as a surcharge; a community can opt to absorb it instead and
 * offer interest-free installments.
 */
enum InstallmentHandling: string
{
    case PassedToCustomer = 'passed_to_customer';
    case AbsorbedByCommunity = 'absorbed_by_community';
}

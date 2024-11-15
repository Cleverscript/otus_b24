<?php

namespace Otus\Bookingfield\Traits;

trait ModuleTrait
{
    protected static $moduleId = 'otus.bookingfield';

    protected static $reqModOpt = [
        'IBLOCK_PROCEDURES' => 'OTUS_BOOKINGFIELD_IBLOCK_PROCEDURES',
        'IBLOCK_BOOKING' => 'OTUS_BOOKINGFIELD_IBLOCK_BOOKING',
        'IBLOCK_BOOKING_PROP_DATE' => 'OTUS_BOOKINGFIELD_IBLOCK_BOOKING_PROP_DATE',
        'IBLOCK_BOOKING_PROP_PROCEDURE' => 'OTUS_BOOKINGFIELD_IBLOCK_BOOKING_PROP_PROCEDURE'
    ];
}
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DocumentNumberGenerator
{
    public function next(string $type, int $year): string
    {
        if (! in_array($type, ['quote', 'invoice'], true) || $year < 2000 || $year > 9999) {
            throw new InvalidArgumentException('Type ou année de document invalide.');
        }

        $sequence = DB::table('document_sequences')
            ->where('type', $type)
            ->where('year', $year)
            ->lockForUpdate()
            ->first();

        if ($sequence === null) {
            DB::table('document_sequences')->insertOrIgnore([
                'type' => $type,
                'year' => $year,
                'next_number' => 1,
            ]);

            $sequence = DB::table('document_sequences')
                ->where('type', $type)
                ->where('year', $year)
                ->lockForUpdate()
                ->firstOrFail();
        }

        $number = (int) $sequence->next_number;

        DB::table('document_sequences')
            ->where('id', $sequence->id)
            ->update(['next_number' => $number + 1]);

        $prefix = $type === 'quote' ? 'DEV' : 'FAC';

        return sprintf('%s-%04d-%04d', $prefix, $year, $number);
    }
}

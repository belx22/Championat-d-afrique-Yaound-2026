<?php 


namespace App\Exports;

use App\Models\NominativeRegistration;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AccreditationsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return NominativeRegistration::with([
            'delegation',
            'delegation.provisionalRegistration',
            'delegation.definitiveRegistration'
        ])->get()->map(function ($m) {

            return [
                'Country'      => $m->delegation->country ?? '',
                'Federation'   => $m->delegation->federation_name ?? '',
                'Family Name'  => $m->family_name,
                'Given Name'   => $m->given_name,
                'Function'     => ucfirst($m->function),
                'Discipline'   => $m->discipline ?? '',
                'Category'     => $m->category ?? '',
                'FIG ID'       => $m->fig_id ?? '',
                'Provisional'  => optional($m->delegation->provisionalRegistration)->status,
                'Definitive'   => optional($m->delegation->definitiveRegistration)->status,
                'Created At'   => $m->created_at->format('Y-m-d'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Country',
            'Federation',
            'Family Name',
            'Given Name',
            'Function',
            'Discipline',
            'Category',
            'FIG ID',
            'Provisional Status',
            'Definitive Status',
            'Registered At',
        ];
    }
}


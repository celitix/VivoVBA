<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromArray;


class DynamicExport implements FromArray
{
    protected $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $excelData = [];

        $excelData[] = [
            'ID',
            'Token',
            'Consumer Name',
            'Contact Number',
            'Email',
            'Model',
            'Source',
            'Type',
            'Created At',
            'Lead ID',
            'Converted',
            'IMEI',
            'Remarks',
        ];

        foreach ($this->data as $item) {
            $excelData[] = [
                $item->id,
                $item->token->token,
                $item->consumer_name,
                $item->contact_number,
                $item->email,
                $item->model,
                $item->query,
                $item->type,
                $item->created_at,

                // safe relation access
                $item->leads?->id,
                $item->leads?->is_converted,
                $item->leads?->imei,
                $item->leads?->remarks,
            ];
        }

        return $excelData;
    }
}

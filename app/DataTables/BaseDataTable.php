<?php

namespace App\DataTables;

use App\Models\Language;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Str;

class BaseDataTable extends DataTable
{
    /**
     * @var array
     */
    protected array $parameters = [
        'dom' => 'Bfrtip',
        'stateSave' => true,
        'responsive' => true,
        'autoWidth' => false,
        'processing' => true,
        'serverSide' => true,
        'searching' => true,
        'ordering' => true,
        'paging' => true,
        'info' => true,
        'lengthChange' => true,
        'pageLength' => 25,
        'lengthMenu' => [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        'initComplete' => 'function(settings, json) {$(this).removeClass("table-striped");}',
        'select' => [
            'style' => 'multi',
            'selector' => 'td:first-child',
        ],
        'drawCallback' => 'function() {
            if (typeof initializeTooltips === "function") {
                initializeTooltips();
            }
            $(this).closest(".dataTables_wrapper").find(".dataTables_paginate ").addClass("pagination-style-1");
        }',
        'preDrawCallback' => 'function() {
            $(this).closest(".dataTables_wrapper").find(".dataTables_paginate ").addClass("pagination-style-1");
            $(this).closest(".dataTables_wrapper").find(".dt-buttons").removeClass("btn-group").addClass("btn-group-sm");
            $(this).closest(".dataTables_wrapper").find(".dt-buttons").prependTo(".btn-canvas").addClass("me-1");
        }',
    ];

    public function __construct()
    {
        $currentLocale = app()->getLocale();
        
        $language = Cache::rememberForever('current_language', function () use ($currentLocale) {
            return Language::where('code', $currentLocale)->first();
        });

        // Use inline language configuration to avoid CORS issues
        $this->parameters = array_merge($this->parameters, [
            'language' => [
                'processing' => 'Loading...',
                'lengthMenu' => 'Show _MENU_ entries',
                'zeroRecords' => 'No matching records found',
                'info' => 'Showing _START_ to _END_ of _TOTAL_ entries',
                'infoEmpty' => 'Showing 0 to 0 of 0 entries',
                'infoFiltered' => '(filtered from _MAX_ total entries)',
                'search' => 'Search:',
                'paginate' => [
                    'first' => 'First',
                    'last' => 'Last',
                    'next' => 'Next',
                    'previous' => 'Previous'
                ],
                'buttons' => [
                    'copy' => 'Copy',
                    'excel' => 'Excel',
                    'pdf' => 'PDF',
                    'print' => 'Print',
                    'colvis' => 'Column visibility'
                ]
            ],
        ]);
    }

}
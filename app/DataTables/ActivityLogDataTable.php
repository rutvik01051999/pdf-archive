<?php

namespace App\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Spatie\Activitylog\Models\Activity as ActivityLog;

class ActivityLogDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('causer', function ($row) {
                if ($row->causer) {
                    $userName = $row->causer->admin_full_name ?: $row->causer->username;
                    $center = $row->causer->center ? ' (' . $row->causer->center . ')' : '';
                    return $userName . $center;
                }
                
                // For system activities, show IP and user agent info
                $properties = json_decode($row->properties ?? '', true) ?? [];
                if (isset($properties['ip'])) {
                    return 'System (' . $properties['ip'] . ')';
                }
                
                return 'System';
            })
            ->addColumn('source', function ($row) {
                $properties = json_decode($row->properties ?? '', true) ?? [];
                $source = $properties['source'] ?? 'admin';
                
                $sourceLabels = [
                    'admin' => '<span class="badge bg-primary">Admin</span>',
                    'system' => '<span class="badge bg-secondary">System</span>',
                ];
                
                return $sourceLabels[$source] ?? '<span class="badge bg-secondary">Unknown</span>';
            })
            ->addColumn('activity_type', function ($row) {
                $properties = json_decode($row->properties ?? '', true) ?? [];
                $type = $properties['type'] ?? 'admin_activity';
                
                $typeLabels = [
                    'archive_search' => '<span class="badge bg-info">Archive Search</span>',
                    'archive_upload' => '<span class="badge bg-success">Archive Upload</span>',
                    'archive_edit' => '<span class="badge bg-warning">Archive Edit</span>',
                    'archive_delete' => '<span class="badge bg-danger">Archive Delete</span>',
                    'archive_copy' => '<span class="badge bg-primary">Archive Copy</span>',
                    'pdf_download' => '<span class="badge bg-info">PDF Download</span>',
                    'pdf_print' => '<span class="badge bg-secondary">PDF Print</span>',
                    'thumbnail_generation' => '<span class="badge bg-warning">Thumbnail Generation</span>',
                    'category_management' => '<span class="badge bg-primary">Category Management</span>',
                    'center_management' => '<span class="badge bg-success">Center Management</span>',
                    'special_dates_management' => '<span class="badge bg-info">Special Dates Management</span>',
                    'admin_login' => '<span class="badge bg-success">Admin Login</span>',
                    'admin_logout' => '<span class="badge bg-warning">Admin Logout</span>',
                    'failed_login' => '<span class="badge bg-danger">Failed Login</span>',
                    'admin_activity' => '<span class="badge bg-primary">Admin Activity</span>',
                ];
                
                return $typeLabels[$type] ?? '<span class="badge bg-secondary">Unknown</span>';
            })
            ->addColumn('details', function ($row) {
                $properties = json_decode($row->properties ?? '', true) ?? [];
                return view('admin.activitylog.details', ['properties' => $properties, 'activity' => $row])->render();
            })
            ->addColumn('description', function ($row) {
                return view('admin.activitylog.description', ['activity' => $row])->render();
            })
            ->addColumn('action', function ($row) {
                return view('admin.activitylog.actions', ['activity' => $row])->render();
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format('Y-m-d H:i:s');
            })
            ->rawColumns(['source', 'activity_type', 'details', 'description', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ActivityLog $model)
    {
        return $model->newQuery()->with('causer');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('activitylog-table')
            ->columns($this->getColumns())
            ->minifiedAjax(route('admin.activities.activity-logs.index'))
            ->parameters([
                'dom' => 'Bfrtip',
                'buttons' => [
                    'excel',
                    'csv',
                    'pdf',
                    'print'
                ],
                'order' => [[7, 'desc']], // Sort by created_at column (index 7)
                'pageLength' => 25,
                'responsive' => true,
                'autoWidth' => false,
                'scrollX' => true,
            ])
            ->selectStyleSingle()
            ->buttons([]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::computed('DT_RowIndex')->title('#')->width(50)->addClass('text-center')->orderable(false)->searchable(false),
            Column::make('id')->visible(false)->searchable(false)->exportable(false),
            Column::make('causer')->title('User')->orderable(true)->searchable(true),
            Column::make('source')->title('Source')->orderable(false)->searchable(false),
            Column::make('activity_type')->title('Type')->orderable(false)->searchable(false),
            Column::make('description')->title('Description')->orderable(false)->searchable(false),
            Column::make('details')->title('Details')->orderable(false)->searchable(false),
            Column::make('created_at')->title('Date & Time')->orderable(true)->searchable(true),
            Column::computed('action')->title('Action')
                ->exportable(false)
                ->printable(false)
                ->width(100)
                ->addClass('text-center')
                ->orderable(false)
                ->searchable(false),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'ActivityLog_' . date('YmdHis');
    }
}


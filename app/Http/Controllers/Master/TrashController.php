<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\ClientCategory;
use App\Models\DocumentIssuer;
use App\Models\EmailTemplateGroup;
use App\Models\ProjectCategory;
use App\Models\SenderDomain;
use App\Models\Signature;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    private array $registry = [
        'project-categories'   => [ProjectCategory::class,   'Kategori Proyek'],
        'client-categories'    => [ClientCategory::class,    'Kategori Klien'],
        'bank-accounts'        => [BankAccount::class,       'Rekening Bank'],
        'document-issuers'     => [DocumentIssuer::class,    'Penerbit Dokumen'],
        'signatures'           => [Signature::class,         'Tanda Tangan'],
        'email-template-groups'=> [EmailTemplateGroup::class,'Grup Template Email'],
        'sender-domains'       => [SenderDomain::class,      'Sender Domain'],
    ];

    public function restore(Request $request, string $type, int $id)
    {
        $model = $this->resolveModel($type);
        $record = $model::withTrashed()->findOrFail($id);
        $record->restore();
        ActivityLogger::log("{$type}.restored", $record);

        return back()->with('success', 'Data berhasil dipulihkan.');
    }

    public function forceDelete(Request $request, string $type, int $id)
    {
        $model = $this->resolveModel($type);
        $record = $model::withTrashed()->findOrFail($id);
        ActivityLogger::log("{$type}.force_deleted", $record);
        $record->forceDelete();

        return back()->with('success', 'Data berhasil dihapus permanen.');
    }

    public function bulkRestore(Request $request, string $type)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        $model = $this->resolveModel($type);
        $count = $model::withTrashed()->whereIn('id', $request->ids)->restore();

        return back()->with('success', "{$count} data berhasil dipulihkan.");
    }

    public function bulkForceDelete(Request $request, string $type)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        $model = $this->resolveModel($type);

        // Iterate agar model event (forceDeleting) terpanggil untuk hapus file
        $records = $model::withTrashed()->whereIn('id', $request->ids)->get();
        $count = 0;
        foreach ($records as $record) {
            $record->forceDelete();
            $count++;
        }

        return back()->with('success', "{$count} data berhasil dihapus permanen.");
    }

    private function resolveModel(string $type): string
    {
        abort_unless(isset($this->registry[$type]), 404);
        return $this->registry[$type][0];
    }
}

<?php

namespace App\Support;

use App\Models\ActivityLog;

class ActivityLogger
{
    public static function log(string $action, $subject = null, array $detail = []): void
    {
        ActivityLog::create([
            'user_id'       => auth()->id(),
            'action'        => $action,
            'subject_type'  => $subject ? class_basename($subject) : null,
            'subject_id'    => $subject?->getKey(),
            'subject_label' => static::resolveLabel($subject),
            'detail'        => empty($detail) ? null : $detail,
            'ip_address'    => request()->ip(),
            'user_agent'    => request()->userAgent(),
        ]);
    }

    private static function resolveLabel($subject): ?string
    {
        if (! $subject) return null;

        return match (class_basename($subject)) {
            'Invoice'         => $subject->invoice_number,
            'Client'          => $subject->company_name,
            'BankAccount'     => $subject->bank_name . ' – ' . $subject->account_number,
            'ClientCategory'  => $subject->name,
            'ProjectCategory' => $subject->name . ' (' . $subject->code . ')',
            'DocumentIssuer'  => $subject->name,
            'Signature'       => $subject->name,
            'EmailTemplate'   => $subject->name,
            default           => (string) $subject->getKey(),
        };
    }

    public static function actionLabel(string $action): string
    {
        return match ($action) {
            // Invoice
            'invoice.created'           => 'Buat invoice',
            'invoice.updated'           => 'Edit invoice',
            'invoice.deleted'           => 'Hapus invoice',
            'invoice.saved'             => 'Simpan perubahan',
            'invoice.status_changed'    => 'Ubah status',
            'invoice.email_sent'        => 'Kirim email',
            'invoice.marked'            => 'Tandai',
            'invoice.unmarked'          => 'Lepas tanda',
            'invoice.recurring_created' => 'Buat perpanjangan',
            'invoice.downloaded'        => 'Download PDF',
            'invoice.printed'           => 'Print invoice',
            'invoice.interval_changed'  => 'Ubah interval',
            'invoice.meta_updated'      => 'Update meta',
            // Client
            'client.created'            => 'Tambah client',
            'client.updated'            => 'Edit client',
            'client.deleted'            => 'Hapus client',
            // Auth
            'user.login'                => 'Login',
            'user.logout'               => 'Logout',
            // Master – Bank Account
            'bank_account.created'      => 'Tambah bank account',
            'bank_account.updated'      => 'Edit bank account',
            'bank_account.deleted'      => 'Hapus bank account',
            // Master – Client Category
            'client_category.created'   => 'Tambah kategori client',
            'client_category.updated'   => 'Edit kategori client',
            'client_category.deleted'   => 'Hapus kategori client',
            // Master – Project Category
            'project_category.created'  => 'Tambah kategori proyek',
            'project_category.updated'  => 'Edit kategori proyek',
            'project_category.deleted'  => 'Hapus kategori proyek',
            // Master – Document Issuer
            'document_issuer.created'   => 'Tambah document issuer',
            'document_issuer.updated'   => 'Edit document issuer',
            'document_issuer.deleted'   => 'Hapus document issuer',
            // Master – Signature
            'signature.created'         => 'Tambah signature',
            'signature.updated'         => 'Edit signature',
            'signature.deleted'         => 'Hapus signature',
            // Master – Email Template
            'email_template.created'    => 'Tambah email template',
            'email_template.updated'    => 'Edit email template',
            'email_template.deleted'    => 'Hapus email template',
            default                     => $action,
        };
    }

    public static function actionGroup(string $action): string
    {
        return match (true) {
            str_starts_with($action, 'invoice.')          => 'invoice',
            str_starts_with($action, 'client.')           => 'client',
            str_starts_with($action, 'user.')             => 'auth',
            str_starts_with($action, 'bank_account.')     => 'master',
            str_starts_with($action, 'client_category.')  => 'master',
            str_starts_with($action, 'project_category.') => 'master',
            str_starts_with($action, 'document_issuer.')  => 'master',
            str_starts_with($action, 'signature.')        => 'master',
            str_starts_with($action, 'email_template.')   => 'master',
            default                                       => 'other',
        };
    }

    public static function allActions(): array
    {
        return [
            'invoice.created', 'invoice.updated', 'invoice.deleted', 'invoice.saved',
            'invoice.status_changed', 'invoice.email_sent', 'invoice.marked', 'invoice.unmarked',
            'invoice.recurring_created', 'invoice.downloaded', 'invoice.printed',
            'invoice.interval_changed', 'invoice.meta_updated',
            'client.created', 'client.updated', 'client.deleted',
            'user.login', 'user.logout',
            'bank_account.created', 'bank_account.updated', 'bank_account.deleted',
            'client_category.created', 'client_category.updated', 'client_category.deleted',
            'project_category.created', 'project_category.updated', 'project_category.deleted',
            'document_issuer.created', 'document_issuer.updated', 'document_issuer.deleted',
            'signature.created', 'signature.updated', 'signature.deleted',
            'email_template.created', 'email_template.updated', 'email_template.deleted',
        ];
    }
}

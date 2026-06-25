<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PdfService
{
    /**
     * Generate PDF from HTML and return as base64 string.
     * Driver dipilih dari config('pdf.driver') / PDF_DRIVER di .env.
     */
    public function generate(string $html): string
    {
        return match (config('pdf.driver')) {
            'pdfshift' => $this->viaPdfShift($html),
            'dompdf'   => $this->viaDompdf($html),
            default    => $this->viaBrowsershot($html),
        };
    }

    private function viaBrowsershot(string $html): string
    {
        $tmp = storage_path('app/private/pdf_' . uniqid() . '.pdf');

        \Spatie\Browsershot\Browsershot::html($html)
            ->setNodeBinaryPath(config('pdf.browsershot.node_path'))
            ->setChromePath(config('pdf.browsershot.chrome_path'))
            ->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox'])
            ->waitUntilNetworkIdle()
            ->format('A4')
            ->showBackground()
            ->save($tmp);

        $b64 = base64_encode(file_get_contents($tmp));
        @unlink($tmp);

        return $b64;
    }

    private function viaPdfShift(string $html): string
    {
        $response = Http::withBasicAuth('api', config('pdf.pdfshift.api_key'))
            ->post(config('pdf.pdfshift.endpoint'), [
                'source'      => $html,
                'format'      => 'A4',
                'use_print'   => true,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('PDFShift error: ' . $response->body());
        }

        return base64_encode($response->body());
    }

    private function viaDompdf(string $html): string
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4');

        return base64_encode($pdf->output());
    }
}

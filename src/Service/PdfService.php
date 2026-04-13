<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService
{
    private $domPdf;

    public function __construct() {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true); // Pour autoriser les images/logos

        $this->domPdf = new Dompdf($pdfOptions);
    }

    public function generateBinaryPdf($html) {
        $this->domPdf->loadHtml($html);
        $this->domPdf->render();
        $this->domPdf->output();
    }

    public function showPdfFile($html, $filename) {
        $this->domPdf->loadHtml($html);
        $this->domPdf->render();
        $this->domPdf->stream($filename . ".pdf", [
            "Attachment" => false // "false" pour l'ouvrir dans le navigateur, "true" pour forcer le téléchargement
        ]);
    }
}
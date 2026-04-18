<?php

namespace App\Controller\Api;

use App\Repository\PlanningRepository;
use App\Repository\UtilisateurRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/planning/export')]
class ExportExcelController extends AbstractController
{
    public function __construct(
        private PlanningRepository $planningRepository,
        private UtilisateurRepository $utilisateurRepository
    ) {}

    #[Route('/excel', name: 'api_planning_export_excel', methods: ['GET'])]
    public function exportToExcel(Request $request): Response
    {
        $typeShift = $request->query->get('type_shift');
        $employeId = $request->query->get('employe_id');
        
        $plannings = $this->planningRepository->findAll();
        
        if ($typeShift) {
            $plannings = array_filter($plannings, fn($p) => $p->getTypeShift() === $typeShift);
        }
        if ($employeId) {
            $plannings = array_filter($plannings, fn($p) => $p->getEmployeId() == $employeId);
        }
        
        $employes = [];
        foreach ($this->utilisateurRepository->findAll() as $u) {
            $employes[$u->getId()] = $u->getPrenom() . ' ' . $u->getNom();
        }
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Plannings');
        
        // En-têtes
        $sheet->setCellValue('A1', 'Employé');
        $sheet->setCellValue('B1', 'Date');
        $sheet->setCellValue('C1', 'Heure Début');
        $sheet->setCellValue('D1', 'Heure Fin');
        $sheet->setCellValue('E1', 'Type Shift');
        
        $row = 2;
        foreach ($plannings as $p) {
            $sheet->setCellValue('A' . $row, $employes[$p->getEmployeId()] ?? 'Non assigné');
            $sheet->setCellValue('B' . $row, $p->getDate()->format('d/m/Y'));
            $sheet->setCellValue('C' . $row, $p->getHeureDebut()?->format('H:i') ?? '');
            $sheet->setCellValue('D' . $row, $p->getHeureFin()?->format('H:i') ?? '');
            $sheet->setCellValue('E' . $row, $p->getTypeShift());
            $row++;
        }
        
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $fileName = 'plannings_' . date('Y-m-d') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);
        
        return $this->file($tempFile, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
<?php

namespace App\Command;

use PhpImap\Mailbox;
use App\Entity\Offre;
use App\Entity\ImportLog; // AJOUTÉ
use App\Repository\FournisseurRepository;
use App\Repository\RessourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'app:fetch-emails',
    description: 'Récupère les catalogues CSV des fournisseurs par email',
)]
class FetchEmailsCommand extends Command
{
    private $params;
    private $fournisseurRepo;
    private $ressourceRepo;
    private $em;

    public function __construct(ParameterBagInterface $params, FournisseurRepository $fournisseurRepo, RessourceRepository $ressourceRepo, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->params = $params;
        $this->fournisseurRepo = $fournisseurRepo;
        $this->ressourceRepo = $ressourceRepo;
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $uploadDir = $this->params->get('catalogues_directory');

        // VERIFICATION DU DOSSIER
       if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

        // CONFIGURATION MAILBOX
        // Remplace 'ton-code-16-chars' par le mot de passe d'application Google
        $mailbox = new Mailbox(
            '{imap.gmail.com:993/imap/ssl}INBOX', 
            'startix.gestionnaire.ressources@gmail.com', 
            'zghtptzvhfivpzsq' 
        );

        try {
            $mailsIds = $mailbox->searchMailbox('UNSEEN');

            if (empty($mailsIds)) {
                $io->info('Aucun nouvel email trouvé.');
                return Command::SUCCESS;
            }

            foreach ($mailsIds as $mailId) {
                $mail = $mailbox->getMail($mailId);
                $senderEmail = $mail->fromAddress;

                $fournisseur = $this->fournisseurRepo->findOneBy(['email' => $senderEmail]);

                if (!$fournisseur) {
                    $io->warning("Email reçu de $senderEmail (Fournisseur non reconnu).");
                    continue;
                }

                $attachments = $mail->getAttachments();
                foreach ($attachments as $attachment) {
                    if (strtolower(pathinfo($attachment->name, PATHINFO_EXTENSION)) === 'csv') {
                        
                        $newFileName = uniqid() . '_' . $attachment->name;
                        $filePath = $uploadDir . '/' . $newFileName;
                        
                        if ($attachment->saveTo($filePath)) {
                            $io->note("Fichier CSV enregistré : " . $attachment->name);
                            
                            // 1. IMPORT DES DONNÉES DANS LES OFFRES
                            $this->importCsvData($filePath, $fournisseur, $io);

                            // 2. CRÉATION DU LOG (Pour l'interface Ressources)
                            $log = new ImportLog();
                            $log->setFileName($newFileName);
                            $log->setSenderEmail($senderEmail);
                            $log->setStatus('SUCCESS');
                            $this->em->persist($log);
                        }
                    }
                }
                
                $this->em->flush(); // Sauvegarde les offres et le log
                $mailbox->markMailAsRead($mailId);
            }

            $io->success('Récupération et importation terminées !');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Erreur : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function importCsvData(string $filePath, $fournisseur, SymfonyStyle $io): void
    {
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            fgetcsv($handle, 1000, ","); 

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (count($data) < 3) continue;

                $nomRessource = $data[0];
                $prix = (float)$data[1];
                $delai = (int)$data[2];

                $ressource = $this->ressourceRepo->findOneBy(['nom' => $nomRessource]);

                if ($ressource) {
                    $offre = new Offre();
                    $offre->setFournisseur($fournisseur);
                    $offre->setRessource($ressource);
                    $offre->setPrix($prix);
                    $offre->setDelaiTransportJours($delai);
                    $offre->setDateOffre(new \DateTime());

                    $this->em->persist($offre);
                }
            }
            fclose($handle);
        }
    }
}
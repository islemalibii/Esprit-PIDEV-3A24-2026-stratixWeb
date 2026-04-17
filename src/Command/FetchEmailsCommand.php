<?php

namespace App\Command;

use PhpImap\Mailbox;
use App\Repository\FournisseurRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Offre;
use App\Repository\RessourceRepository;
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

        // 1. CONFIGURATION MAILBOX (À adapter avec tes accès Gmail/Outlook)
        // Format: {serveur:port/imap/ssl}Dossier
        $mailbox = new Mailbox(
            '{imap.gmail.com:993/imap/ssl}INBOX', 
            'ton-email-stratix@gmail.com', 
            'ton-mot-de-passe-application'
        );

        try {
            // 2. CHERCHER LES EMAILS NON LUS
            $mailsIds = $mailbox->searchMailbox('UNSEEN');

            if (empty($mailsIds)) {
                $io->info('Aucun nouvel email trouvé.');
                return Command::SUCCESS;
            }

            foreach ($mailsIds as $mailId) {
                $mail = $mailbox->getMail($mailId);
                $senderEmail = $mail->fromAddress;

                // 3. VÉRIFIER SI L'EXPÉDITEUR EST UN FOURNISSEUR ENREGISTRÉ
                $fournisseur = $this->fournisseurRepo->findOneBy(['email' => $senderEmail]);

                if (!$fournisseur) {
                    $io->warning("Email reçu de $senderEmail, mais ce n'est pas un fournisseur enregistré. Ignoré.");
                    continue;
                }

                // 4. RÉCUPÉRER LES PIÈCES JOINTES (CSV)
                $attachments = $mail->getAttachments();
                foreach ($attachments as $attachment) {
                    if (strtolower(pathinfo($attachment->name, PATHINFO_EXTENSION)) === 'csv') {
                        
                        $filePath = $uploadDir . '/' . uniqid() . '_' . $attachment->name;
                        
                        // Sauvegarde physique du fichier
                        if ($attachment->saveTo($filePath)) {
                            $io->note("Fichier CSV enregistré : " . $attachment->name);
                            
                            // 5. ANALYSE ET IMPORTATION AUTOMATIQUE
                            $this->importCsvData($filePath, $fournisseur, $io);
                        }
                    }
                }
                
                // Marquer comme lu
                $mailbox->markMailAsRead($mailId);
            }

            $io->success('Récupération et importation terminées !');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Erreur lors de la connexion IMAP : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function importCsvData(string $filePath, $fournisseur, SymfonyStyle $io): void
    {
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            // On saute la première ligne si c'est une entête
            fgetcsv($handle, 1000, ","); 

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Supposons le format CSV : NomRessource, Prix, Delai
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
            $this->em->flush();
            fclose($handle);
            $io->success("Les données de " . $fournisseur->getNom() . " ont été injectées en base.");
        }
    }
}
<?php

namespace App\Twig\Components;

use App\Entity\Attachment;
use App\Entity\Issue as IssueEntity;
use App\Service\AttachmentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\ValidatableComponentTrait;

#[AsLiveComponent]
class Issue
{
    use ComponentToolsTrait;
    use DefaultActionTrait;
    use ValidatableComponentTrait;

    #[LiveProp(writable: ['description', 'summary'])]
    public IssueEntity $issue;

    /** @var Attachment[] */
    #[LiveProp]
    public array $attachments = [];

    #[LiveProp]
    public bool $isEditingSummary = false;
    
    #[LiveProp]
    public bool $isEditingDescription = false;

    public function __construct(
        private readonly AttachmentService $attachmentService,
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface $validator
    ) {
    }

    #[LiveAction]
    public function activateEditingSummary(): void
    {
        $this->isEditingSummary = true;
    }

    #[LiveAction]
    public function activateEditingDescription(): void
    {
        $this->isEditingDescription = true;
    }

    #[LiveAction]
    public function saveSummary(): void
    {
        $errors = $this->validator->validate($this->issue);
        
        if (count($errors) > 0) {
            // Gérer les erreurs de validation ici
            foreach ($errors as $error) {
                // Vous pouvez ajouter des flashs messages ou d'autres mécanismes pour afficher les erreurs
            }
            return;
        }

        $this->isEditingSummary = false;
        
        $this->em->flush();
    }

    #[LiveAction]
    public function saveDescription(): void
    {
        $errors = $this->validator->validate($this->issue);
        
        if (count($errors) > 0) {
            // Gérer les erreurs de validation ici
            foreach ($errors as $error) {
                // Vous pouvez ajouter des flashs messages ou d'autres mécanismes pour afficher les erreurs
            }
            return;
        }

        $this->isEditingDescription = false;
        
        $this->em->flush();
    }

    #[LiveAction]
    public function addAttachment(Request $request)
    {
        $attachment = $this->attachmentService->handleUploadedAttachment($this->issue, $request);
        
        if ($attachment) {
            // Rafraîchir la liste des pièces jointes
            $this->attachments = $this->issue->getAttachment()->toArray();
        }
    }
    
    #[LiveAction]
    public function deleteAttachment(#[LiveArg] int $attachmentId): void
    {
        $attachment = $this->em->getRepository(Attachment::class)->find($attachmentId);
        
        if (!$attachment) {
            return;
        }
        
        // Vérifiez que l'attachment appartient bien à l'issue courante
        if ($attachment->getIssue()->getId() !== $this->issue->getId()) {
            return;
        }
        
        // Enlever l'attachment de l'issue
        $this->issue->removeAttachment($attachment);
        
        // Supprimer l'attachment de la base de données
        // La suppression du fichier sera gérée par l'EntityListener
        $this->em->remove($attachment);
        $this->em->flush();
        
        // Mettre à jour la liste des attachments affichés
        $this->attachments = $this->issue->getAttachment()->toArray();
    }
}
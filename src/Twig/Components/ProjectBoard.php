<?php

namespace App\Twig\Components;

use App\Entity\Issue;
use App\Enum\IssueStatus;
use App\Service\IssueService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class ProjectBoard
{
    use DefaultActionTrait;

    /** @var Issue[] */    
    #[LiveProp(writable: true)]
    public array $readyIssues = [];

    /** @var Issue[] */
    #[LiveProp(writable: true)]
    public array $inProgressIssues = [];

    /** @var Issue[] */
    #[LiveProp(writable: true)]
    public array $resolvedIssues = [];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly IssueService $issueService
    ){
    }

    public function mount(): void
    {
        $this->getIssues();
    }

    #[LiveAction]
    public function getIssues(): void
    {
        $this->readyIssues = $this->issueService->getReadyIssues();
        $this->inProgressIssues = $this->issueService->getinProgressIssues();
        $this->resolvedIssues = $this->issueService->getresolvedIssues();
    }


    #[LiveAction]
public function updateIssuesStatus(#[LiveArg] string $id, #[LiveArg] string $status): void
{
    // Vérifier si l'ID est valide
    if (!$id) {
        return;
    }
    
    // Récupérer l'issue
    $issue = $this->em->getRepository(\App\Entity\Issue::class)->find($id);
    
    // Vérifier si l'issue existe
    if (!$issue) {
        return;
    }
    
    // Convertir le status string en IssueStatus enum
    // Attention: selon votre implémentation, vous pourriez avoir besoin d'ajuster cette ligne
    $issueStatus = IssueStatus::from($status);
    
    // Mettre à jour le statut
    $issue->setStatus($issueStatus);
    
    // Sauvegarder les changements
    $this->em->flush();
    
    // Rafraîchir les listes
    $this->getIssues();
}
}
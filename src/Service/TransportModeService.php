<?php
// src/Service/TransportModeService.php
namespace App\Service;

use App\Entity\TransportMode;
use App\Repository\TransportModeRepository;
use Doctrine\ORM\EntityManagerInterface;

class TransportModeService
{
    private $entityManager;
    private $repository;

    public function __construct(EntityManagerInterface $entityManager, TransportModeRepository $repository)
    {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function getAllTransportModes()
    {
        $modes = $this->repository->findAll();
        
        // Ensure public_transport is available
        $hasPublicTransport = false;
        foreach ($modes as $mode) {
            if ($mode->getName() === 'public_transport') {
                $hasPublicTransport = true;
                break;
            }
        }
        
        if (!$hasPublicTransport) {
            // Add public transport option
            $mode = new TransportMode();
            $mode->setName('public_transport');
            $this->entityManager->persist($mode);
            $this->entityManager->flush();
            
            // Refresh the list
            $modes = $this->repository->findAll();
        }
        
        return $modes;
    }

    public function createTransportMode(string $name): bool
    {
        try {
            $mode = new TransportMode();
            $mode->setName($name);
            
            $this->entityManager->persist($mode);
            $this->entityManager->flush();
            
            return true;
        } catch (\Exception $e) {
            // Consider logging the error
            return false;
        }
    }

    public function updateTransportMode(int $id, string $name): bool
    {
        $mode = $this->repository->find($id);
        
        if (!$mode) {
            return false;
        }
        
        $mode->setName($name);
        $this->entityManager->flush();
        
        return true;
    }

    public function deleteTransportMode(int $id): bool
    {
        $mode = $this->repository->find($id);
        
        if (!$mode) {
            return false;
        }
        
        $this->entityManager->remove($mode);
        $this->entityManager->flush();
        
        return true;
    }
}
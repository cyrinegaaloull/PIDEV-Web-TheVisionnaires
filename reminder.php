<?php
namespace App\Command;

use App\Entity\ReservationEvent;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'app:send-event-reminders',
    description: 'Send reminder emails for events happening tomorrow to users who requested notifications'
)]
class SendEventRemindersCommand extends Command
{
    private $entityManager;
    private $notifier;
    private $projectDir;

    public function __construct(
        EntityManagerInterface $entityManager,
        NotificationService $notifier,
        ParameterBagInterface $params
    ) {
        $this->entityManager = $entityManager;
        $this->notifier = $notifier;
        $this->projectDir = $params->get('kernel.project_dir');
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logDir = $this->projectDir . '/var/log';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }
        $logFile = $logDir . '/reminder_error.log';

        // Find reservations with notify = true and event date tomorrow
        $tomorrowStart = (new \DateTime())->modify('+1 day')->setTime(0, 0);
        $tomorrowEnd = (new \DateTime())->modify('+1 day')->setTime(23, 59, 59);

        $pendingReservations = $this->entityManager->getRepository(ReservationEvent::class)
            ->createQueryBuilder('r')
            ->where('r.notify = :notify')
            ->andWhere('r.event.eventDate BETWEEN :start AND :end')
            ->setParameter('notify', true)
            ->setParameter('start', $tomorrowStart)
            ->setParameter('end', $tomorrowEnd)
            ->getQuery()
            ->getResult();

        $output->writeln(sprintf('Found %d pending notifications.', count($pendingReservations)));

        foreach ($pendingReservations as $reservation) {
            $user = $reservation->getUser();
            $event = $reservation->getEvent();

            if (!$user || !$event) {
                $output->writeln("Skipping reservation {$reservation->getReservationId()}: Missing user or event.");
                continue;
            }

            try {
                $subject = '🔔 Rappel: "' . $event->getEventname() . '" est prévu demain !';
                $message = "
                    <div style='font-family: Arial, sans-serif;'>
                        <h2 style='color: #4CAF50;'>Rappel d'Événement</h2>
                        <p>Bonjour <strong>{$user->getUsername()}</strong>,</p>
                        <p>Votre événement <strong>{$event->getEventname()}</strong> est prévu pour <strong>{$event->getEventdate()->format('d/m/Y')}</strong>.</p>
                        <p>Nous avons hâte de vous voir !</p>
                    </div>
                ";

                $this->notifier->sendEmail($user->getEmail(), $subject, $message);
                $reservation->setNotify(false);
                $output->writeln("Sent reminder to {$user->getEmail()} for event {$event->getEventname()}");
            } catch (\Exception $e) {
                $errorMessage = "Failed to send reminder for reservation {$reservation->getReservationId()}: {$e->getMessage()}";
                file_put_contents($logFile, $errorMessage . "\n", FILE_APPEND);
                $output->writeln($errorMessage);
            }
        }

        $this->entityManager->flush();
        $output->writeln('✅ Reminders sent successfully.');

        return Command::SUCCESS;
    }
}